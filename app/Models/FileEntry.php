<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\ScanFile;
use Database\Factories\FileEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileEntry extends Model
{
    /** @use HasFactory<FileEntryFactory> */
    use HasFactory, HasUlids;

    protected $table = 'files';

    protected $attributes = ['virus_scan_status' => 'pending'];

    protected static function booted(): void
    {
        static::created(function (self $file) {
            if ($file->virus_scan_status === 'pending') {
                ScanFile::dispatch($file->id);
            }
        });
    }

    protected $fillable = [
        'storage_disk',
        'storage_path',
        'original_name',
        'mime_type',
        'byte_size',
        'checksum_sha256',
        'virus_scan_status',
        'created_by',
    ];

    protected $casts = [
        'byte_size' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SubmissionFile, $this>
     */
    public function submissionFiles(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'file_id');
    }

    /**
     * @return HasMany<CopyeditOutput, $this>
     */
    public function copyeditOutputs(): HasMany
    {
        return $this->hasMany(CopyeditOutput::class, 'file_id');
    }

    /**
     * @return HasMany<CopyeditApprovedFile, $this>
     */
    public function copyeditApprovedFiles(): HasMany
    {
        return $this->hasMany(CopyeditApprovedFile::class, 'file_id');
    }
}
