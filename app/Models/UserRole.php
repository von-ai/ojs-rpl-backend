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

    protected $touches = ['user'];

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
                'badge_class' => 'bg-indigo-700 text-white border-indigo-700',
            ],
            'section_editor' => [
                'code' => 'section_editor',
                'label' => 'Editor Bagian',
                'portal_label' => 'Portal Editor Bagian',
                'description' => 'Penugasan mitra bestari, pemantauan telaah, dan rekomendasi',
                'route' => 'section-editor.submissions.index',
                'prefix' => 'section-editor',
                'badge_class' => 'bg-blue-700 text-white border-blue-700',
            ],
            'reviewer' => [
                'code' => 'reviewer',
                'label' => 'Mitra Bestari',
                'portal_label' => 'Portal Mitra Bestari',
                'description' => 'Evaluasi substansi naskah ilmiah dan pengiriman rekomendasi telaah',
                'route' => 'reviewer.assignments.index',
                'prefix' => 'reviewer',
                'badge_class' => 'bg-teal-700 text-white border-teal-700',
            ],
            'copyeditor' => [
                'code' => 'copyeditor',
                'label' => 'Penyunting Naskah',
                'portal_label' => 'Portal Penyunting Naskah',
                'description' => 'Penyuntingan tata bahasa, gaya selingkung, dan naskah bersih',
                'route' => 'copyeditor.submissions.index',
                'prefix' => 'copyeditor',
                'badge_class' => 'bg-cyan-700 text-white border-cyan-700',
            ],
            'layout_editor' => [
                'code' => 'layout_editor',
                'label' => 'Penata Letak',
                'portal_label' => 'Portal Penata Letak',
                'description' => 'Produksi galai publikasi dan tata letak artikel siap terbit',
                'route' => 'layouteditor.submissions.index',
                'prefix' => 'layouteditor',
                'badge_class' => 'bg-amber-700 text-white border-amber-700',
            ],
            'proofreader' => [
                'code' => 'proofreader',
                'label' => 'Korektor Galai',
                'portal_label' => 'Portal Korektor Galai',
                'description' => 'Pemeriksaan bukti baca akhir dan verifikasi catatan koreksi',
                'route' => 'proofreader.submissions.index',
                'prefix' => 'proofreader',
                'badge_class' => 'bg-violet-700 text-white border-violet-700',
            ],
            'author' => [
                'code' => 'author',
                'label' => 'Penulis',
                'portal_label' => 'Portal Penulis',
                'description' => 'Pengajuan naskah penelitian, pemantauan status, dan unggah revisi',
                'route' => 'author.submissions.index',
                'prefix' => 'author',
                'badge_class' => 'bg-gray-700 text-white border-gray-700',
            ],
            'sysadmin' => [
                'code' => 'sysadmin',
                'label' => 'Administrator Sistem',
                'portal_label' => 'Portal Administrator Sistem',
                'description' => 'Tata kelola multi-jurnal, hak akses global, dan kesehatan platform',
                'route' => 'sysadmin.journals.index',
                'prefix' => 'sysadmin',
                'badge_class' => 'bg-slate-900 text-white border-slate-900',
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
        $catalog = self::roleCatalog();

        if (isset($catalog[$roleCode])) {
            return $catalog[$roleCode]['label'];
        }

        return match ($roleCode) {
            'sysadmin' => 'Administrator Sistem',
            'manager' => 'Manajer Jurnal',
            'editor_in_chief' => 'Pemimpin Redaksi',
            'section_editor' => 'Editor Bagian',
            'copyeditor' => 'Penyunting Naskah',
            'layout_editor' => 'Penata Letak',
            'proofreader' => 'Korektor Galai',
            'reviewer' => 'Mitra Bestari',
            'author' => 'Penulis',
            default => ucfirst(str_replace('_', ' ', $roleCode)),
        };
    }

    public static function roleBadgeClass(string $roleCode): string
    {
        return match ($roleCode) {
            'sysadmin' => 'bg-slate-900 text-white border-slate-900',
            'manager' => 'bg-indigo-700 text-white border-indigo-700',
            'editor_in_chief' => 'bg-brand-maroon text-white border-brand-maroon',
            'section_editor' => 'bg-blue-700 text-white border-blue-700',
            'copyeditor' => 'bg-cyan-700 text-white border-cyan-700',
            'layout_editor' => 'bg-amber-700 text-white border-amber-700',
            'proofreader' => 'bg-violet-700 text-white border-violet-700',
            'reviewer' => 'bg-teal-700 text-white border-teal-700',
            'author' => 'bg-gray-700 text-white border-gray-700',
            default => 'bg-gray-700 text-white border-gray-700',
        };
    }
}
