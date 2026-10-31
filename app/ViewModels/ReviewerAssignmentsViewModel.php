<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ReviewerAssignmentsViewModel
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly User $reviewer,
        public readonly LengthAwarePaginator $assignments,
        public readonly array $filters = []
    ) {}

    /**
     * Menghitung total penugasan aktif untuk badge sidebar.
     */
    public static function getSidebarBadgeCount(?User $reviewer = null): int
    {
        $user = $reviewer ?? Auth::user();
        if (! $user) {
            return 0;
        }

        return ReviewAssignment::query()
            ->where('reviewer_id', $user->id)
            ->whereIn('status', ['invited', 'accepted', 'overdue'])
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

    public function invitedCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->where('status', 'invited')
            ->count();
    }

    public function activeCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->whereIn('status', ['accepted', 'overdue'])
            ->count();
    }

    public function completedCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->where('status', 'completed')
            ->count();
    }

    public function totalCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->count();
    }

    /**
     * Data 4 kartu metrik KPI (statis murni, non-klik).
     *
     * @return array<int, array{key: string, title: string, count: int, subtitle: string, icon: string, icon_bg: string, icon_text: string}>
     */
    public function metricCards(): array
    {
        return [
            [
                'key' => 'all',
                'title' => 'Total Penugasan',
                'count' => $this->totalCount(),
                'subtitle' => 'Seluruh naskah yang ditugaskan',
                'icon' => 'clipboard',
                'icon_bg' => 'bg-gray-100',
                'icon_text' => 'text-gray-700',
            ],
            [
                'key' => 'invited',
                'title' => 'Undangan Masuk',
                'count' => $this->invitedCount(),
                'subtitle' => 'Menunggu konfirmasi kesediaan',
                'icon' => 'mail',
                'icon_bg' => 'bg-amber-100',
                'icon_text' => 'text-amber-700',
            ],
            [
                'key' => 'active',
                'title' => 'Sedang Ditelaah',
                'count' => $this->activeCount(),
                'subtitle' => 'Dalam proses penelaahan saintifik',
                'icon' => 'document-search',
                'icon_bg' => 'bg-blue-100',
                'icon_text' => 'text-blue-700',
            ],
            [
                'key' => 'completed',
                'title' => 'Telaah Selesai',
                'count' => $this->completedCount(),
                'subtitle' => 'Laporan evaluasi telah terkirim',
                'icon' => 'check-circle',
                'icon_bg' => 'bg-emerald-100',
                'icon_text' => 'text-emerald-700',
            ],
        ];
    }

    /**
     * Daftar tab navigasi filter status penugasan.
     *
     * @return array<int, array{key: string, label: string, count: int, active: bool}>
     */
    public function tabs(): array
    {
        $current = $this->currentStatus();

        return [
            [
                'key' => 'all',
                'label' => 'Semua Penugasan',
                'count' => $this->totalCount(),
                'active' => $current === 'all',
            ],
            [
                'key' => 'invited',
                'label' => 'Undangan Masuk',
                'count' => $this->invitedCount(),
                'active' => $current === 'invited',
            ],
            [
                'key' => 'active',
                'label' => 'Sedang Ditelaah',
                'count' => $this->activeCount(),
                'active' => $current === 'active',
            ],
            [
                'key' => 'completed',
                'label' => 'Telaah Selesai',
                'count' => $this->completedCount(),
                'active' => $current === 'completed',
            ],
        ];
    }

    /**
     * Metadata visual status penugasan reviewer.
     *
     * @return array{label: string, badge_class: string, desc: string}
     */
    public function statusMeta(string $status): array
    {
        return match ($status) {
            'invited' => [
                'label' => 'Undangan Masuk',
                'badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'desc' => 'Menunggu konfirmasi penerimaan atau penolakan undangan telaah',
            ],
            'accepted' => [
                'label' => 'Sedang Ditelaah',
                'badge_class' => 'bg-blue-50 text-blue-800 border-blue-200',
                'desc' => 'Undangan disetujui, ruang telaah aktif dan naskah dapat dievaluasi',
            ],
            'overdue' => [
                'label' => 'Melewati Batas Waktu',
                'badge_class' => 'bg-rose-50 text-rose-800 border-rose-200',
                'desc' => 'Tenggat waktu penelaahan telah berakhir',
            ],
            'completed' => [
                'label' => 'Telaah Selesai',
                'badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'desc' => 'Hasil evaluasi telaah sejawat telah resmi dikirimkan ke dewan redaksi',
            ],
            'declined' => [
                'label' => 'Undangan Ditolak',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-300',
                'desc' => 'Penelaah berhalangan dan telah menolak undangan naskah',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan Editor',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-300',
                'desc' => 'Penugasan dibatalkan oleh Editor Bagian',
            ],
            default => [
                'label' => ucfirst($status),
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'desc' => 'Status dalam proses',
            ],
        };
    }
}
