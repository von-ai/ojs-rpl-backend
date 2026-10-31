<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Submission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EicSubmissionsViewModel
{
    public function __construct(
        public readonly string $activeTab = 'all',
        public readonly string $searchQuery = '',
        public readonly ?LengthAwarePaginator $submissions = null
    ) {}

    /**
     * Menghitung jumlah naskah aktif yang membutuhkan perhatian EIC untuk badge sidebar.
     */
    public static function getSidebarBadgeCount(): int
    {
        return Submission::whereIn('status', ['submitted', 'in_review', 'revisions_requested'])->count();
    }

    /**
     * Profil Pemimpin Redaksi.
     *
     * @return array{name: string, role: string, initials: string}
     */
    public function userProfile(): array
    {
        $name = auth()->user()?->name ?? 'Prof. Dr. Ir. H. Nasaruddin, M.T.';
        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name) ?: 'PR', 0, 2));

        return [
            'name' => $name,
            'role' => 'Pemimpin Redaksi',
            'initials' => $initials,
        ];
    }

    /**
     * Ringkasan 4 kartu metrik editorial.
     *
     * @return array<int, array{key: string, title: string, count: int, subtitle: string, icon: string, icon_bg: string, icon_text: string}>
     */
    public function metricCards(): array
    {
        $needDeskReview = Submission::where('status', 'submitted')->count();
        $inReview = Submission::where('status', 'in_review')->count();
        $needRevision = Submission::where('status', 'revisions_requested')->count();
        $decided = Submission::whereIn('status', ['accepted', 'declined', 'in_production', 'published'])->count();

        return [
            [
                'key' => 'submitted',
                'title' => 'Menunggu Desk Review',
                'count' => $needDeskReview,
                'subtitle' => 'Naskah baru masuk yang memerlukan skrining awal',
                'icon' => 'clipboard',
                'icon_bg' => 'bg-amber-50',
                'icon_text' => 'text-amber-600',
            ],
            [
                'key' => 'in_review',
                'title' => 'Sedang Dalam Telaah',
                'count' => $inReview,
                'subtitle' => 'Naskah dalam evaluasi editor bagian dan mitra bestari',
                'icon' => 'users',
                'icon_bg' => 'bg-sky-50',
                'icon_text' => 'text-sky-600',
            ],
            [
                'key' => 'revisions',
                'title' => 'Perlu Revisi Penulis',
                'count' => $needRevision,
                'subtitle' => 'Menunggu pengunggahan naskah perbaikan dari penulis',
                'icon' => 'refresh',
                'icon_bg' => 'bg-orange-50',
                'icon_text' => 'text-orange-600',
            ],
            [
                'key' => 'decided',
                'title' => 'Keputusan Selesai',
                'count' => $decided,
                'subtitle' => 'Naskah telah diterima produksi, terbit, atau ditolak',
                'icon' => 'check',
                'icon_bg' => 'bg-emerald-50',
                'icon_text' => 'text-emerald-600',
            ],
        ];
    }

    /**
     * Konfigurasi tab status antrean naskah beserta penghitung jumlah naskah.
     *
     * @return array<int, array{key: string, label: string, count: int, active: bool}>
     */
    public function tabs(): array
    {
        $needDeskReview = Submission::where('status', 'submitted')->count();
        $inReview = Submission::where('status', 'in_review')->count();
        $needRevision = Submission::where('status', 'revisions_requested')->count();
        $decided = Submission::whereIn('status', ['accepted', 'declined', 'in_production', 'published'])->count();
        $all = Submission::where('status', '!=', 'draft')->count();

        return [
            ['key' => 'all', 'label' => 'Semua Naskah', 'count' => $all, 'active' => $this->activeTab === 'all'],
            ['key' => 'submitted', 'label' => 'Perlu Desk Review', 'count' => $needDeskReview, 'active' => $this->activeTab === 'submitted'],
            ['key' => 'in_review', 'label' => 'Dalam Penelaahan', 'count' => $inReview, 'active' => $this->activeTab === 'in_review'],
            ['key' => 'revisions', 'label' => 'Proses Revisi', 'count' => $needRevision, 'active' => $this->activeTab === 'revisions'],
            ['key' => 'decided', 'label' => 'Selesai Diputuskan', 'count' => $decided, 'active' => $this->activeTab === 'decided'],
        ];
    }

    /**
     * Mendapatkan badge kanonis berdasarkan status naskah.
     *
     * @return array{label: string, badge_class: string}
     */
    public function statusMeta(string $status): array
    {
        return match ($status) {
            'submitted' => [
                'label' => 'Menunggu Skrining',
                'badge_class' => 'bg-amber-50 text-amber-800 border border-amber-200',
            ],
            'in_review' => [
                'label' => 'Dalam Penelaahan',
                'badge_class' => 'bg-sky-50 text-sky-800 border border-sky-200',
            ],
            'revisions_requested' => [
                'label' => 'Perlu Revisi',
                'badge_class' => 'bg-orange-50 text-orange-800 border border-orange-200',
            ],
            'accepted' => [
                'label' => 'Diterima',
                'badge_class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
            ],
            'in_production' => [
                'label' => 'Tahap Produksi',
                'badge_class' => 'bg-purple-50 text-purple-800 border border-purple-200',
            ],
            'published' => [
                'label' => 'Telah Terbit',
                'badge_class' => 'bg-green-50 text-green-800 border border-green-200',
            ],
            'declined' => [
                'label' => 'Ditolak',
                'badge_class' => 'bg-red-50 text-red-800 border border-red-200',
            ],
            'archived', 'withdrawn' => [
                'label' => 'Diarsipkan',
                'badge_class' => 'bg-zinc-100 text-zinc-700 border border-zinc-300',
            ],
            default => [
                'label' => 'Draf Naskah',
                'badge_class' => 'bg-gray-100 text-gray-700 border border-gray-200',
            ],
        };
    }
}
