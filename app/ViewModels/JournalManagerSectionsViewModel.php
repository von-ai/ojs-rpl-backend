<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class JournalManagerSectionsViewModel
{
    /**
     * Profil pengguna aktif Journal Manager.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? 'Andi Muhammad Yusuf, M.Si.';
        $affiliation = $user?->profile?->affiliation ?? 'BRIDA Kota Makassar';

        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name) ?: 'AY';
        $initials = strtoupper(substr($cleanName, 0, 2));

        return [
            'name' => $name,
            'role_label' => 'Journal Manager',
            'affiliation' => $affiliation,
            'initials' => $initials,
        ];
    }

    /**
     * Ringkasan metrik seksi bidang naskah.
     *
     * @return array<int, array{title: string, value: int, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        $sections = $this->sections();
        $total = count($sections);
        $active = count(array_filter($sections, fn ($s) => $s['accepting_submissions']));
        $closed = $total - $active;

        return [
            [
                'title' => 'Total Bidang Naskah',
                'value' => $total,
                'badge_text' => 'Semua Seksi',
                'color_class' => 'text-gray-900 bg-gray-50 border-gray-200',
            ],
            [
                'title' => 'Aktif Menerima Naskah',
                'value' => $active,
                'badge_text' => 'Buka Submission',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
            [
                'title' => 'Seksi Dinonaktifkan',
                'value' => $closed,
                'badge_text' => 'Tutup Submission',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
            ],
        ];
    }

    /**
     * Daftar seluruh bidang naskah / seksi penelitian pada jurnal dari basis data atau fallback.
     *
     * @return array<int, array{
     *     id: string,
     *     position: int,
     *     name: string,
     *     slug: string,
     *     description: string,
     *     accepting_submissions: bool,
     *     status_label: string,
     *     status_badge_class: string,
     *     submissions_count: int
     * }>
     */
    public function sections(): array
    {
        if (Schema::hasTable('sections')) {
            $sections = Section::withCount('submissions')
                ->orderBy('position')
                ->get();

            if ($sections->isNotEmpty()) {
                return $sections->map(function (Section $sec) {
                    $accepting = (bool) $sec->accepting_submissions;

                    return [
                        'id' => (string) $sec->id,
                        'position' => (int) $sec->position,
                        'name' => (string) $sec->name,
                        'slug' => (string) $sec->slug,
                        'description' => (string) ($sec->description ?? ''),
                        'accepting_submissions' => $accepting,
                        'status_label' => $accepting ? 'Buka Submission' : 'Tutup Submission',
                        'status_badge_class' => $accepting
                            ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                            : 'bg-amber-50 text-amber-800 border-amber-200',
                        'submissions_count' => (int) ($sec->submissions_count ?? 0),
                    ];
                })->all();
            }
        }

        return [
            [
                'id' => 'SEC-01JARK-01',
                'position' => 1,
                'name' => 'Inovasi Daerah & Kebijakan Publik',
                'slug' => 'inovasi-daerah-kebijakan-publik',
                'description' => 'Riset implementasi kebijakan pemerintah kota, reformasi birokrasi, dan terobosan layanan publik perkotaan.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 14,
            ],
            [
                'id' => 'SEC-01JARK-02',
                'position' => 2,
                'name' => 'Sosial Humaniora & Tata Kelola',
                'slug' => 'sosial-humaniora-tata-kelola',
                'description' => 'Kajian dinamika sosial perkotaan, kearifan lokal, ketahanan sosial masyarakat lorong, dan tata kelola kelurahan.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 11,
            ],
            [
                'id' => 'SEC-01JARK-03',
                'position' => 3,
                'name' => 'Teknologi & Infrastruktur Berkelanjutan',
                'slug' => 'teknologi-infrastruktur-berkelanjutan',
                'description' => 'Riset teknik sipil lingkungan, mitigasi banjir pasang rob, sensor kualitas udara perkotaan, dan smart city.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 8,
            ],
            [
                'id' => 'SEC-01JARK-04',
                'position' => 4,
                'name' => 'Kajian Kebencanaan Pesisir & Maritim',
                'slug' => 'kebencanaan-pesisir-maritim',
                'description' => 'Studi khusus geomorfologi pantai, sedimentasi muara sungai, dan mitigasi gelombang pasang pulau-pulau kecil.',
                'accepting_submissions' => false,
                'status_label' => 'Tutup Submission',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'submissions_count' => 3,
            ],
        ];
    }
}
