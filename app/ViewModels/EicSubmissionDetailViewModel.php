<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EicSubmissionDetailViewModel
{
    public function __construct(
        public readonly Submission $submission
    ) {}

    /**
     * Mengambil daftar calon Section Editor yang dapat ditugaskan.
     *
     * @return Collection<int, User>
     */
    public function availableSectionEditors()
    {
        return User::where('id', '!=', $this->submission->author_id)
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * Informasi ringkas status alur kerja naskah.
     *
     * @return array{label: string, badge_class: string}
     */
    public function statusInfo(): array
    {
        $label = match ($this->submission->status) {
            'draft' => 'Draf Belum Diajukan',
            'submitted' => 'Menunggu Skrining Awal (Desk Review)',
            'in_review' => 'Dalam Penelaahan Dewan Redaksi / Mitra Bestari',
            'revisions_requested' => 'Menunggu Pengunggahan Revisi Penulis',
            'accepted', 'in_production' => 'Naskah Diterima (Tahap Produksi)',
            'published' => 'Telah Terbit Resmi',
            'declined' => 'Naskah Ditolak (Declined)',
            'withdrawn' => 'Naskah Ditarik Penulis',
            default => 'Dalam Proses Editorial',
        };

        $badgeClass = match ($this->submission->status) {
            'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
            'in_review' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'revisions_requested' => 'bg-amber-50 text-amber-700 border-amber-200',
            'accepted', 'in_production' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'declined' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };

        return [
            'label' => $label,
            'badge_class' => $badgeClass,
        ];
    }
}
