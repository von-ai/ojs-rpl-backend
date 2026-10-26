<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopyeditOutput extends Model
{
    use HasFactory;

    protected $table = 'copyedit_outputs';

    public $incrementing = false;

    protected $fillable = [
        'copyedit_id',
        'file_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * @return BelongsTo<Copyedit, $this>
     */
    public function copyedit(): BelongsTo
    {
        return $this->belongsTo(Copyedit::class);
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'file_id');
    }
}
