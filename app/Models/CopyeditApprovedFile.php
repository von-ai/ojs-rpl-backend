<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopyeditApprovedFile extends Model
{
    use HasFactory;

    protected $table = 'copyedit_approved_files';

    public $incrementing = false;

    protected $fillable = [
        'approval_id',
        'file_id',
        'checksum_sha256',
    ];

    /**
     * @return BelongsTo<CopyeditApproval, $this>
     */
    public function approval(): BelongsTo
    {
        return $this->belongsTo(CopyeditApproval::class, 'approval_id');
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'file_id');
    }
}
