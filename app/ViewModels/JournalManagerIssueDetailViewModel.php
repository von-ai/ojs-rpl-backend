<?php

declare(strict_types=1);

namespace App\ViewModels;

class JournalManagerIssueDetailViewModel
{
    private string $issueId;

    public function __construct(string $issueId = 'ISSUE-01JARK960A')
    {
        $this->issueId = $issueId;
    }

    /**
     * Data lengkap edisi terbitan untuk ruang kerja penyusunan daftar isi (Table of Contents).
     *
     * @return array{
     *     id: string,
     *     volume: string,
     *     number: string,
     *     year: int,
     *     title: string,
     *     description: ?string,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     is_current: bool,
     *     scheduled_at: ?string,
     *     publication_date: ?string,
     *     published_at: ?string,
     *     doi_prefix: string,
     *     can_publish: bool,
     *     empty_guard_violated: bool,
     *     articles: array<int, array{
     *         id: string,
     *         submission_number: string,
     *         position: int,
     *         title: string,
     *         section_title: string,
     *         authors_string: string,
     *         pages: string,
     *         galley_pdf_url: string,
     *         doi: string
     *     }>,
     *     ready_articles: array<int, array{
     *         id: string,
     *         number: string,
     *         title: string,
     *         section_title: string,
     *         author_name: string,
     *         galley_format: string,
     *         approved_at: string
     *     }>,
     *     activity_timeline: array<int, array{
     *         title: string,
     *         actor: string,
     *         date: string,
     *         description: string,
     *         badge_class: string
     *     }>
     * }
     */
    public function issue(): array
    {
        $dataset = $this->registry();

        if (isset($dataset[$this->issueId])) {
            return $dataset[$this->issueId];
        }

        return array_merge($dataset['ISSUE-01JARK960A'], [
            'id' => $this->issueId,
        ]);
    }

    /**
     * Kumpulan dataset skenario edisi terbitan.
     *
     * @return array<string, array<string, mixed>>
     */
    private function registry(): array
    {
        return [
            // 1. Skenario Draf Kurasi: Edisi yang sedang disusun daftar isinya
            'ISSUE-01JARK960A' => [
                'id' => 'ISSUE-01JARK960A',
                'volume' => '6',
                'number' => '2',
                'year' => 2026,
                'title' => 'Vol. 6 No. 2 (2026): Inovasi Kebijakan Publik & Ketahanan Perkotaan',
                'description' => 'Edisi reguler semester kedua 2026 yang memuat riset empiris inovasi pelayanan publik, tata kelola lingkungan, dan mitigasi perkotaan BRIDA Kota Makassar.',
                'status' => 'draft',
                'status_label' => 'Draf Kurasi Naskah',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'is_current' => false,
                'scheduled_at' => '30 Nov 2026',
                'publication_date' => null,
                'published_at' => null,
                'doi_prefix' => '10.61234/sirida.v6i2',
                'can_publish' => true,
                'empty_guard_violated' => false,
                'articles' => [
                    [
                        'id' => 'ART-01JARK-01',
                        'submission_number' => 'SUB-2026-00109',
                        'position' => 1,
                        'title' => 'Efektivitas Program Lorong Wisata dalam Peningkatan Pendapatan Rumah Tangga Berpenghasilan Rendah',
                        'section_title' => 'Ekonomi Pembangunan & Pemberdayaan Masyarakat',
                        'authors_string' => 'Hj. Fatmawati, S.E., M.Si.',
                        'pages' => '1-14',
                        'galley_pdf_url' => '#unduh-galley-pdf-01',
                        'doi' => '10.61234/sirida.v6i2.01',
                    ],
                    [
                        'id' => 'ART-01JARK-02',
                        'submission_number' => 'SUB-2026-00111',
                        'position' => 2,
                        'title' => 'Inovasi Pelayanan Kesehatan Terpadu Ibu dan Anak Berbasis Posyandu Lorong di Kota Makassar',
                        'section_title' => 'Kesehatan Masyarakat & Gizi',
                        'authors_string' => 'Dr. dr. Risnah, S.Kep., Ns., M.Kes.',
                        'pages' => '15-28',
                        'galley_pdf_url' => '#unduh-galley-pdf-02',
                        'doi' => '10.61234/sirida.v6i2.02',
                    ],
                    [
                        'id' => 'ART-01JARK-03',
                        'submission_number' => 'SUB-2026-00114',
                        'position' => 3,
                        'title' => 'Evaluasi Kualitas Udara Ambien Kawasan Industri Makassar Menggunakan Sensor IoT Terdistribusi',
                        'section_title' => 'Teknologi & Infrastruktur Berkelanjutan',
                        'authors_string' => 'Ir. Muhammad Akbar, M.T., Ph.D.',
                        'pages' => '29-42',
                        'galley_pdf_url' => '#unduh-galley-pdf-03',
                        'doi' => '10.61234/sirida.v6i2.03',
                    ],
                ],
                'ready_articles' => [
                    [
                        'id' => 'SUB-2026-00115',
                        'number' => 'SUB-2026-00115',
                        'title' => 'Model Partisipasi Pemuda dalam Pengelolaan Sampah Plastik Berbasis Komunitas',
                        'section_title' => 'Sosial Humaniora & Tata Kelola',
                        'author_name' => 'Fadli Rahman, S.Sos., M.A.',
                        'galley_format' => 'PDF Full Text (v2 Final)',
                        'approved_at' => '24 Okt 2026 11:00 WITA',
                    ],
                    [
                        'id' => 'SUB-2026-00116',
                        'number' => 'SUB-2026-00116',
                        'title' => 'Analisis Kebijakan Tarif Air Bersih PDAM Terhadap Daya Beli Masyarakat Pra-Sejahtera',
                        'section_title' => 'Inovasi Daerah & Kebijakan Publik',
                        'author_name' => 'Dr. Ilham Jaya, S.E., M.Si.',
                        'galley_format' => 'PDF Full Text (v2 Final)',
                        'approved_at' => '24 Okt 2026 09:15 WITA',
                    ],
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Artikel #3 Ditambahkan ke Daftar Isi',
                        'actor' => 'Andi M. Yusuf, M.Si. (Journal Manager)',
                        'date' => '24 Okt 2026 14:30 WITA',
                        'description' => 'Artikel SUB-2026-00114 dimasukkan ke posisi nomor 3 dengan rentang halaman 29-42.',
                        'badge_class' => 'bg-brand-maroon',
                    ],
                    [
                        'title' => 'Draf Edisi Dibuat',
                        'actor' => 'Andi M. Yusuf, M.Si. (Journal Manager)',
                        'date' => '20 Okt 2026 09:00 WITA',
                        'description' => 'Nomor terbitan Vol. 6 No. 2 (2026) dibuka untuk kurasi naskah siap terbit.',
                        'badge_class' => 'bg-blue-500',
                    ],
                ],
            ],

            // 2. Skenario Edisi Terbit Resmi (Current Issue)
            'ISSUE-01JARK961B' => [
                'id' => 'ISSUE-01JARK961B',
                'volume' => '6',
                'number' => '1',
                'year' => 2026,
                'title' => 'Vol. 6 No. 1 (2026): Transformasi Digital dan Pembangunan Daerah Berkelanjutan',
                'description' => 'Edisi terbitan berjalan resmi semester pertama tahun 2026.',
                'status' => 'published',
                'status_label' => 'Terbit (Current Issue)',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'is_current' => true,
                'scheduled_at' => null,
                'publication_date' => '30 Jun 2026',
                'published_at' => '30 Jun 2026 10:00 WITA',
                'doi_prefix' => '10.61234/sirida.v6i1',
                'can_publish' => false,
                'empty_guard_violated' => false,
                'articles' => [
                    [
                        'id' => 'ART-PUB-01',
                        'submission_number' => 'SUB-2026-00080',
                        'position' => 1,
                        'title' => 'Studi Komparasi Efisiensi Pelayanan Perizinan Terpadu Satu Pintu',
                        'section_title' => 'Inovasi Daerah & Kebijakan Publik',
                        'authors_string' => 'Dr. H. Rusdianto, M.Si.',
                        'pages' => '1-16',
                        'galley_pdf_url' => '#unduh-galley-pdf',
                        'doi' => '10.61234/sirida.v6i1.01',
                    ],
                ],
                'ready_articles' => [],
                'activity_timeline' => [
                    [
                        'title' => 'Edisi Resmi Dipublikasikan',
                        'actor' => 'Andi M. Yusuf, M.Si. (Journal Manager)',
                        'date' => '30 Jun 2026 10:00 WITA',
                        'description' => 'Edisi Vol. 6 No. 1 (2026) dipublikasikan dan ditetapkan sebagai Terbitan Berjalan (Current Issue).',
                        'badge_class' => 'bg-emerald-600',
                    ],
                ],
            ],
        ];
    }
}
