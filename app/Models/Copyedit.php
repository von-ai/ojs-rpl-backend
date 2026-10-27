<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Copyedit extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'copy_editor_id',
        'source_file_id',
        'status',
        'notes',
        'due_at',
        'version',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function copyEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'copy_editor_id');
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function sourceFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'source_file_id');
    }

    /**
     * @return HasMany<CopyeditOutput, $this>
     */
    public function outputs(): HasMany
    {
        return $this->hasMany(CopyeditOutput::class, 'copyedit_id')->orderBy('position');
    }

    /**
     * @return BelongsToMany<FileEntry, $this>
     */
    public function outputFiles(): BelongsToMany
    {
        return $this->belongsToMany(FileEntry::class, 'copyedit_outputs', 'copyedit_id', 'file_id')
            ->withPivot('position')
            ->withTimestamps();
    }

    /**
     * @return HasMany<CopyeditApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(CopyeditApproval::class, 'copyedit_id')->latest('created_at');
    }

    /**
     * @return HasOne<CopyeditApproval, $this>
     */
    public function latestApproval(): HasOne
    {
        return $this->hasOne(CopyeditApproval::class, 'copyedit_id')->latestOfMany('created_at');
    }

    /**
     * @return HasMany<ProductionEscalation, $this>
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(ProductionEscalation::class, 'copyedit_id')->latest('created_at');
    }

    /**
     * @return HasOne<ProductionEscalation, $this>
     */
    public function activeEscalation(): HasOne
    {
        return $this->hasOne(ProductionEscalation::class, 'copyedit_id')
            ->whereIn('status', ['open', 'investigating'])
            ->latestOfMany('created_at');
    }
}
