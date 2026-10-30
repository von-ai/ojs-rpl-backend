<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'decision_id',
        'round',
        'status',
        'manuscript_file_id',
        'response_letter_file_id',
        'due_at',
        'grace_policy_snapshot',
        'submitted_at',
        'note',
        'version',
    ];

    protected $casts = [
        'round' => 'integer',
        'version' => 'integer',
        'grace_policy_snapshot' => 'array',
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function manuscriptFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'manuscript_file_id');
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function responseLetterFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'response_letter_file_id');
    }
}
