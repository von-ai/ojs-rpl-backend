<?php

namespace App\Jobs;

use App\Models\FileEntry;
use App\Models\User;
use App\Models\UserImport;
use App\Services\SysAdmin\SysAdminService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessUserImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 120;

    public int $timeout = 120;

    public function __construct(public string $importId)
    {
        $this->onConnection('database')->onQueue('imports')->beforeCommit();
    }

    public function handle(): void
    {
        $import = UserImport::findOrFail($this->importId);
        if ($import->status === 'completed' || $import->status === 'failed') {
            return;
        }
        $file = FileEntry::findOrFail($import->file_id);
        if (in_array($file->virus_scan_status, ['pending', 'error'], true)) {
            $this->release(30);

            return;
        }
        if ($file->virus_scan_status !== 'clean') {
            throw new RuntimeException('Berkas impor tidak lulus pemindaian keamanan.');
        }
        $actor = User::find($import->imported_by);
        if (! $actor || $actor->status !== 'active' || ! $actor->isSysAdmin()) {
            throw new RuntimeException('Hak administrator untuk impor tidak lagi berlaku.');
        }
        $disk = Storage::disk($file->storage_disk);
        $content = $disk->get($file->storage_path);
        if (! hash_equals($file->checksum_sha256, hash('sha256', $content))) {
            throw new RuntimeException('Integritas berkas impor tidak sesuai.');
        }
        $temporary = tmpfile();
        if (! $temporary) {
            throw new RuntimeException('Berkas impor tidak dapat dibaca.');
        }
        try {
            fwrite($temporary, $content);
            $path = stream_get_meta_data($temporary)['uri'];
            DB::transaction(function () use ($import, $path, $actor) {
                $import = UserImport::query()->lockForUpdate()->findOrFail($import->id);
                if ($import->status === 'completed') {
                    return;
                }
                app(SysAdminService::class)->processUsersFromCsv($path, $import->journal_id, $import->send_invitation_email, $actor, $import);
            }, 3);
        } finally {
            fclose($temporary);
        }
    }

    public function failed(?Throwable $exception): void
    {
        UserImport::whereKey($this->importId)->update(['status' => 'failed', 'error_log' => ['Impor gagal. Periksa berkas, pemindai, dan hak administrator, lalu unggah ulang.']]);
    }
}
