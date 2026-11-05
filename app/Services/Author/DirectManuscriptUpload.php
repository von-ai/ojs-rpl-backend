<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Models\FileEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;
use RuntimeException;
use Throwable;

class DirectManuscriptUpload
{
    public const MAX_BYTES = 20 * 1024 * 1024;

    /** @param array{filename: string, byte_size: int|string, mime_type?: ?string} $data */
    public function issue(User $author, array $data): array
    {
        $name = basename(str_replace('\\', '/', $data['filename']));
        $safeName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $name);
        $path = 'staging/manuscripts/'.Str::uuid().'/'.$safeName;
        $uploadExpiresAt = now()->addMinutes(15);
        $upload = Storage::disk('azure')->temporaryUploadUrl($path, $uploadExpiresAt);

        DB::table('manuscript_upload_sessions')->insert([
            'path' => $path,
            'author_id' => $author->id,
            'original_name' => $name,
            'byte_size' => (int) $data['byte_size'],
            'expires_at' => now()->addDay(),
        ]);

        return [
            'success' => true,
            'upload_url' => $upload['url'],
            'headers' => $upload['headers'],
            'blob_path' => $path,
            'file_name' => $name,
            'byte_size' => (int) $data['byte_size'],
            'mime_type' => $data['mime_type'] ?? 'application/octet-stream',
            'expires_at' => $uploadExpiresAt->toIso8601String(),
        ];
    }

    /** The caller holds the submission transaction until the file is attached. */
    public function claim(User $author, string $path): FileEntry
    {
        $session = DB::table('manuscript_upload_sessions')->where('path', $path)
            ->where('author_id', $author->id)->where('expires_at', '>', now())
            ->whereNull('consumed_at')->lockForUpdate()->first();

        if (! $session || $session->path !== $path) {
            throw ValidationException::withMessages(['manuscript_file' => 'Sesi unggah tidak berlaku. Pilih dan unggah kembali berkas naskah.']);
        }

        $disk = Storage::disk('azure');
        $temporary = tmpfile();
        if (! is_resource($temporary)) {
            throw new RuntimeException('Tidak dapat membuat berkas sementara untuk unggahan.');
        }

        try {
            $source = $disk->readStream($session->path);
            if (! is_resource($source)) {
                throw ValidationException::withMessages(['manuscript_file' => 'Berkas unggahan tidak tersedia. Unggah kembali berkas naskah.']);
            }

            try {
                $size = stream_copy_to_stream($source, $temporary, self::MAX_BYTES + 1);
            } finally {
                fclose($source);
            }

            if ($size !== (int) $session->byte_size || $size < 1 || $size > self::MAX_BYTES) {
                throw ValidationException::withMessages(['manuscript_file' => 'Ukuran berkas berbeda dari sesi unggah atau melebihi 20 MB.']);
            }

            $temporaryPath = stream_get_meta_data($temporary)['uri'];
            $file = new UploadedFile($temporaryPath, $session->original_name, test: true);
            Validator::make(['manuscript_file' => $file], [
                'manuscript_file' => ['required', 'file', 'mimes:pdf,doc,docx'],
            ])->validate();

            $mime = $file->getMimeType();
            $checksum = hash_file('sha256', $temporaryPath);
            if ($mime === null || $checksum === false) {
                throw new RuntimeException('Tidak dapat memverifikasi berkas unggahan.');
            }

            rewind($temporary);
            $sealedPath = 'manuscripts/sealed/'.Str::ulid();
            try {
                if (! $disk->put($sealedPath, $temporary, ['ContentType' => $mime])) {
                    throw new RuntimeException('Berkas unggahan gagal disimpan di penyimpanan privat.');
                }

                $entry = FileEntry::create([
                    'storage_disk' => 'azure',
                    'storage_path' => $sealedPath,
                    'original_name' => $session->original_name,
                    'mime_type' => $mime,
                    'byte_size' => $size,
                    'checksum_sha256' => $checksum,
                    'virus_scan_status' => 'pending',
                    'created_by' => $author->id,
                ]);
                DB::table('manuscript_upload_sessions')->where('path', $session->path)->update(['consumed_at' => now()]);

                return $entry;
            } catch (Throwable $e) {
                $this->discard($sealedPath);
                throw $e;
            }
        } catch (FilesystemException $e) {
            report($e);
            throw ValidationException::withMessages(['manuscript_file' => 'Penyimpanan unggahan tidak dapat diakses. Coba unggah kembali.']);
        } finally {
            fclose($temporary);
        }
    }

    public function discard(string $path): void
    {
        try {
            Storage::disk('azure')->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
