<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'journal_id',
        'role_code',
        'expertise_keywords',
    ];

    protected $casts = [
        'expertise_keywords' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public static function roleLabel(string $roleCode): string
    {
        return match ($roleCode) {
            'sysadmin' => 'System Administrator',
            'manager' => 'Journal Manager',
            'editor_in_chief' => 'Editor in Chief',
            'section_editor' => 'Section Editor',
            'copyeditor' => 'Copy Editor',
            'layout_editor' => 'Layout Editor',
            'proofreader' => 'Proofreader',
            'reviewer' => 'Peer Reviewer',
            'author' => 'Author',
            default => ucfirst(str_replace('_', ' ', $roleCode)),
        };
    }

    public static function roleBadgeClass(string $roleCode): string
    {
        return match ($roleCode) {
            'sysadmin' => 'bg-purple-50 text-purple-800 border-purple-200',
            'manager' => 'bg-purple-50 text-purple-800 border-purple-200',
            'editor_in_chief' => 'bg-brand-maroon text-white border-brand-maroon',
            'section_editor' => 'bg-blue-50 text-blue-800 border-blue-200',
            'copyeditor' => 'bg-sky-50 text-sky-800 border-sky-200',
            'layout_editor' => 'bg-amber-50 text-amber-800 border-amber-200',
            'proofreader' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
            'reviewer' => 'bg-blue-50 text-blue-700 border-blue-200',
            'author' => 'bg-gray-100 text-gray-700 border-gray-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}
