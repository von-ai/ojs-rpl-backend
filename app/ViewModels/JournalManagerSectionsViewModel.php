<?php

declare(strict_types=1);

namespace App\ViewModels;

class JournalManagerSectionsViewModel
{
    /**
     * Profil pengguna aktif Journal Manager.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Andi Muhammad Yusuf, M.Si.',
            'role_label' => 'Journal Manager',
            'affiliation' => 'BRIDA Kota Makassar',
            'initials' => 'AY',
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
     * Daftar seluruh bidang naskah / seksi penelitian pada jurnal.
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
                'description' => 'Rekayasa teknik sipil, sistem transportasi publik perkotaan, IoT kota cerdas (smart city), dan mitigasi kebencanaan pesisir.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 9,
            ],
            [
                'id' => 'SEC-01JARK-04',
                'position' => 4,
                'name' => 'Ekonomi Pembangunan & Pemberdayaan Masyarakat',
                'slug' => 'ekonomi-pembangunan-pemberdayaan-masyarakat',
                'description' => 'Kajian UMKM lorong wisata, ekonomi sirkular, pengentasan kemiskinan ekstrem, dan retribusi daerah.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 8,
            ],
            [
                'id' => 'SEC-01JARK-05',
                'position' => 5,
                'name' => 'Kesehatan Masyarakat & Gizi',
                'slug' => 'kesehatan-masyarakat-gizi',
                'description' => 'Pencegahan stunting terpadu, efektivitas posyandu lorong, dan ketahanan sanitasi pemukiman padat.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 7,
            ],
            [
                'id' => 'SEC-01JARK-06',
                'position' => 6,
                'name' => 'Lingkungan & Ketahanan Kota',
                'slug' => 'lingkungan-ketahanan-kota',
                'description' => 'Adaptasi perubahan iklim, pengelolaan sampah berbasis komunitas, dan ruang terbuka hijau perkotaan.',
                'accepting_submissions' => true,
                'status_label' => 'Buka Submission',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submissions_count' => 6,
            ],
            [
                'id' => 'SEC-01JARK-07',
                'position' => 7,
                'name' => 'Arsip Edisi Khusus Riset Maritim (2024)',
                'slug' => 'edisi-khusus-maritim-2024',
                'description' => 'Seksi khusus tertutup untuk artikel riset kemaritiman tahun 2024.',
                'accepting_submissions' => false,
                'status_label' => 'Tutup Submission',
                'status_badge_class' => 'bg-gray-100 text-gray-600 border-gray-200',
                'submissions_count' => 5,
            ],
        ];
    }
}
