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
     * Profil Editor in Chief.
     *
     * @return array{name: string, role: string, initials: string}
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Prof. Dr. H. Syahrul, M.Pd.',
            'role' => 'Editor-in-Chief (Pemimpin Redaksi)',
            'initials' => 'PR',
        ];
    }

    /**
     * Ringkasan 4 kartu metrik editorial.
     *
     * @return array<int, array{title: string, count: int, subtitle: string, badge_class: string}>
     */
    public function metricCards(): array
    {
        $needDeskReview = Submission::where('status', 'submitted')->count();
        $inReview = Submission::where('status', 'in_review')->count();
        $needRevision = Submission::where('status', 'revisions_requested')->count();
        $decided = Submission::whereIn('status', ['accepted', 'declined', 'in_production', 'published'])->count();

        return [
            [
                'title' => 'Menunggu Desk Review',
                'count' => $needDeskReview,
                'subtitle' => 'Naskah baru masuk perlu skrining awal',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            [
                'title' => 'Sedang Dalam Telaah',
                'count' => $inReview,
                'subtitle' => 'Ditangani Section Editor & Reviewer',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            [
                'title' => 'Perlu Revisi Penulis',
                'count' => $needRevision,
                'subtitle' => 'Menunggu perbaikan dari penulis',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            [
                'title' => 'Keputusan Selesai',
                'count' => $decided,
                'subtitle' => 'Naskah diterima atau ditolak resmi',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab status antrean naskah.
     *
     * @return array<int, array{key: string, label: string, active: bool}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'all', 'label' => 'Semua Naskah', 'active' => $this->activeTab === 'all'],
            ['key' => 'submitted', 'label' => 'Perlu Desk Review', 'active' => $this->activeTab === 'submitted'],
            ['key' => 'in_review', 'label' => 'Dalam Penelaahan', 'active' => $this->activeTab === 'in_review'],
            ['key' => 'revisions', 'label' => 'Proses Revisi', 'active' => $this->activeTab === 'revisions'],
            ['key' => 'decided', 'label' => 'Selesai Diputuskan', 'active' => $this->activeTab === 'decided'],
        ];
    }
}
