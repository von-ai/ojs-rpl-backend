<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\JournalFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    /** @use HasFactory<JournalFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'slug',
        'name',
        'issn',
        'eissn',
        'status',
        'current_issue_id',
        'version',
    ];

    /**
     * @return HasMany<Section, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('position');
    }

    /**
     * @return HasMany<Submission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
