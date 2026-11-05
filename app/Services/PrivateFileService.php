<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FileEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileService
{
    public function download(FileEntry $file, User $user, ?string $role = null): StreamedResponse
    {
        $role ??= request()->attributes->get('portal.role') ?? $user->activeRoleCode();
        abort_unless($user->hasRole($role), 403);
        // Reviewer access uses an exact, accepted assignment and anonymized snapshot in its controller.
        abort_if($role === 'reviewer', 403);
        $submissions = PortalAccess::visibleSubmissions($user, $role);
        $id = $file->id;
        $allowed = DB::table('submission_files')->where('file_id', $id)->whereIn('submission_id', clone $submissions)->exists()
            || DB::table('copyedits')->where('source_file_id', $id)->whereIn('submission_id', clone $submissions)->exists()
            || DB::table('copyedit_outputs')->join('copyedits', 'copyedits.id', '=', 'copyedit_outputs.copyedit_id')->where('file_id', $id)->whereIn('copyedits.submission_id', clone $submissions)->exists()
            || DB::table('galleys')->where('file_id', $id)->whereIn('submission_id', clone $submissions)->when($role === 'proofreader', fn ($q) => $q->whereIn('id', DB::table('proof_assignments')->select('galley_id')->where('proofreader_id', $user->id)->where('file_id', $id)))->exists();
        abort_unless($allowed, 404, 'Berkas tidak ditemukan pada penugasan Anda.');

        return $this->stream($file);
    }

    public function stream(FileEntry $file, ?string $downloadName = null): StreamedResponse
    {
        abort_unless($file->virus_scan_status === 'clean', 423, 'Berkas belum lulus pemindaian keamanan.');
        $path = $file->storage_path;
        abort_if(! is_string($path) || $path === '' || str_contains($path, "\0") || preg_match('~(^|[\\\\/])\.\.([\\\\/]|$)~', $path) || str_starts_with($path, '/'), 404);
        $disk = Storage::disk($file->storage_disk ?: 'azure');
        abort_unless($disk->exists($path), 404, 'Berkas fisik tidak ditemukan.');
        if ($file->checksum_sha256) {
            $stream = $disk->readStream($path);
            abort_unless(is_resource($stream), 404);
            try {
                $hash = hash_init('sha256');
                hash_update_stream($hash, $stream);
                abort_unless(hash_equals(strtolower($file->checksum_sha256), hash_final($hash)), 409, 'Integritas berkas tidak sesuai.');
            } finally {
                fclose($stream);
            }
        }
        $name = basename(str_replace('\\', '/', $downloadName ?? $file->original_name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?: 'dokumen';

        return $disk->download($path, $name, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
            'X-Checksum-SHA256' => (string) $file->checksum_sha256,
        ]);
    }
}
