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

    /**
     * Katalog lengkap peran pengguna lintas portal SIRIDA beserta rute dan metadatanya.
     *
     * @return array<string, array{
     *     code: string,
     *     label: string,
     *     portal_label: string,
     *     description: string,
     *     route: string,
     *     prefix: string,
     *     badge_class: string
     * }>
     */
    public static function roleCatalog(): array
    {
        return [
            'editor_in_chief' => [
                'code' => 'editor_in_chief',
                'label' => 'Pemimpin Redaksi',
                'portal_label' => 'Portal Pemimpin Redaksi',
                'description' => 'Skrining awal naskah, penugasan editor, dan keputusan akhir',
                'route' => 'eic.dashboard',
                'prefix' => 'eic',
                'badge_class' => 'bg-brand-maroon text-white border-brand-maroon',
            ],
            'manager' => [
                'code' => 'manager',
                'label' => 'Manajer Jurnal',
                'portal_label' => 'Portal Manajer Jurnal',
                'description' => 'Tata kelola edisi terbitan, anggota redaksi, dan kebijakan jurnal',
                'route' => 'journalmanager.issues.index',
                'prefix' => 'journalmanager',
                'badge_class' => 'bg-purple-50 text-purple-800 border-purple-200',
            ],
            'section_editor' => [
                'code' => 'section_editor',
                'label' => 'Editor Bagian',
                'portal_label' => 'Portal Editor Bagian',
                'description' => 'Penugasan mitra bestari, pemantauan telaah, dan rekomendasi',
                'route' => 'section-editor.submissions.index',
                'prefix' => 'section-editor',
                'badge_class' => 'bg-blue-50 text-blue-800 border-blue-200',
            ],
            'reviewer' => [
                'code' => 'reviewer',
                'label' => 'Mitra Bestari',
                'portal_label' => 'Portal Mitra Bestari',
                'description' => 'Evaluasi substansi naskah ilmiah dan pengiriman rekomendasi telaah',
                'route' => 'reviewer.assignments.index',
                'prefix' => 'reviewer',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            'copyeditor' => [
                'code' => 'copyeditor',
                'label' => 'Penyunting Naskah',
                'portal_label' => 'Portal Penyunting Naskah',
                'description' => 'Penyuntingan tata bahasa, gaya selingkung, dan naskah bersih',
                'route' => 'copyeditor.submissions.index',
                'prefix' => 'copyeditor',
                'badge_class' => 'bg-sky-50 text-sky-800 border-sky-200',
            ],
            'layout_editor' => [
                'code' => 'layout_editor',
                'label' => 'Penata Letak',
                'portal_label' => 'Portal Penata Letak',
                'description' => 'Produksi galai publikasi dan tata letak artikel siap terbit',
                'route' => 'layouteditor.submissions.index',
                'prefix' => 'layouteditor',
                'badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
            ],
            'proofreader' => [
                'code' => 'proofreader',
                'label' => 'Korektor Galai',
                'portal_label' => 'Portal Korektor Galai',
                'description' => 'Pemeriksaan bukti baca akhir dan verifikasi catatan koreksi',
                'route' => 'proofreader.submissions.index',
                'prefix' => 'proofreader',
                'badge_class' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
            ],
            'author' => [
                'code' => 'author',
                'label' => 'Penulis',
                'portal_label' => 'Portal Penulis',
                'description' => 'Pengajuan naskah penelitian, pemantauan status, dan unggah revisi',
                'route' => 'author.submissions.index',
                'prefix' => 'author',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
            'sysadmin' => [
                'code' => 'sysadmin',
                'label' => 'Administrator Sistem',
                'portal_label' => 'Portal Administrator Sistem',
                'description' => 'Tata kelola multi-jurnal, hak akses global, dan kesehatan platform',
                'route' => 'sysadmin.journals.index',
                'prefix' => 'sysadmin',
                'badge_class' => 'bg-rose-50 text-rose-800 border-rose-200',
            ],
        ];
    }

    /**
     * Mengambil metadata lengkap untuk satu kode peran.
     *
     * @return array{
     *     code: string,
     *     label: string,
     *     portal_label: string,
     *     description: string,
     *     route: string,
     *     prefix: string,
     *     badge_class: string
     * }
     */
    public static function roleMeta(string $roleCode): array
    {
        $catalog = self::roleCatalog();

        if (isset($catalog[$roleCode])) {
            return $catalog[$roleCode];
        }

        return $catalog['author'];
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
            'sysadmin' => 'bg-rose-50 text-rose-800 border-rose-200',
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
