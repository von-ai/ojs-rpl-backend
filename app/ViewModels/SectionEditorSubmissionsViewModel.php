<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class SectionEditorSubmissionsViewModel
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly User $editor,
        public readonly ?LengthAwarePaginator $submissions = null,
        public readonly array $filters = []
    ) {}

    /**
     * Menghitung jumlah naskah aktif yang ditugaskan ke Section Editor untuk lencana navigasi sidebar.
     */
    public static function getSidebarBadgeCount(?User $editor = null): int
    {
        $user = $editor ?? Auth::user();
        if (! $user) {
            return 0;
        }

        return Submission::query()
            ->whereHas('editorAssignments', function ($q) use ($user) {
                $q->where('editor_id', $user->id)->whereNull('revoked_at');
            })
            ->whereIn('status', ['in_review', 'under_review', 'revisions_requested'])
            ->count();
    }

    public function currentStatus(): string
    {
        return $this->filters['status'] ?? 'all';
    }

    public function searchQuery(): string
    {
        return $this->filters['search'] ?? '';
    }

    public function inReviewCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->whereIn('status', ['in_review', 'under_review'])
            ->count();
    }

    public function revisionsRequestedCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->where('status', 'revisions_requested')
            ->count();
    }

    public function reviewedCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->whereHas('currentReviewRound', function ($q) {
                $q->whereHas('assignments', function ($qa) {
                    $qa->where('status', 'completed');
                });
            })
            ->count();
    }

    public function totalCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->count();
    }

    /**
     * 4 Kartu Metrik Ringkasan untuk Dashboard & Halaman Antrean.
     *
     * @return array<int, array{key: string, title: string, count: int, subtitle: string, icon: string, icon_bg: string, icon_text: string}>
     */
    public function metricCards(): array
    {
        return [
            [
                'key' => 'all',
                'title' => 'Total Ditugaskan',
                'count' => $this->totalCount(),
                'subtitle' => 'Seluruh naskah yang didelegasikan kepada Anda',
                'icon' => 'clipboard',
                'icon_bg' => 'bg-gray-100',
                'icon_text' => 'text-gray-700',
            ],
            [
                'key' => 'in_review',
                'title' => 'Sedang Ditelaah',
                'count' => $this->inReviewCount(),
                'subtitle' => 'Naskah dalam proses evaluasi mitra bestari',
                'icon' => 'users',
                'icon_bg' => 'bg-sky-50',
                'icon_text' => 'text-sky-600',
            ],
            [
                'key' => 'revisions_requested',
                'title' => 'Menunggu Revisi',
                'count' => $this->revisionsRequestedCount(),
                'subtitle' => 'Penulis sedang menyiapkan perbaikan naskah',
                'icon' => 'refresh',
                'icon_bg' => 'bg-amber-50',
                'icon_text' => 'text-amber-600',
            ],
            [
                'key' => 'reviewed',
                'title' => 'Review Selesai',
                'count' => $this->reviewedCount(),
                'subtitle' => 'Laporan telaah lengkap dan siap direkomendasikan',
                'icon' => 'check',
                'icon_bg' => 'bg-emerald-50',
                'icon_text' => 'text-emerald-600',
            ],
        ];
    }

    /**
     * Konfigurasi tab status antrean naskah.
     *
     * @return array<int, array{key: string, label: string, count: int, active: bool}>
     */
    public function tabs(): array
    {
        $status = $this->currentStatus();

        return [
            ['key' => 'all', 'label' => 'Semua Naskah', 'count' => $this->totalCount(), 'active' => $status === 'all'],
            ['key' => 'in_review', 'label' => 'Sedang Ditelaah', 'count' => $this->inReviewCount(), 'active' => $status === 'in_review'],
            ['key' => 'revisions_requested', 'label' => 'Menunggu Revisi', 'count' => $this->revisionsRequestedCount(), 'active' => $status === 'revisions_requested'],
            ['key' => 'reviewed', 'label' => 'Review Selesai', 'count' => $this->reviewedCount(), 'active' => $status === 'reviewed'],
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
            'in_review', 'under_review' => [
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
