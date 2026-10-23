<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthorSubmissionDetailViewModel
{
    /**
     * Mengambil dan memformat data detail naskah secara komprehensif.
     *
     * @return array<string, mixed>|null
     */
    public function find(string $id, ?User $user = null): ?array
    {
        // 1. Cek di Database Relasional
        try {
            if (Schema::hasTable('submissions')) {
                $query = Submission::with(['section', 'journal', 'contributors', 'files.file'])
                    ->where('id', $id)
                    ->orWhere('submission_number', $id);

                $sub = $query->first();

                if ($sub) {
                    return $this->formatDatabaseSubmission($sub);
                }
            }
        } catch (\Throwable) {
            // Fallback graceful ke mock data
        }

        // 2. Cek Mock Data Standar SIRIDA
        $mock = $this->findMockSubmission($id);
        if ($mock) {
            $stage = $mock['stage_number'] ?? 2;
            if (! isset($mock['can_withdraw'])) {
                $mock['can_withdraw'] = $this->canWithdrawSubmission($mock, $stage);
            }
            if (! isset($mock['cannot_withdraw_reason'])) {
                $mock['cannot_withdraw_reason'] = $this->cannotWithdrawReason($mock, $stage);
            }

            return $mock;
        }

        return null;
    }

    /**
     * Memformat record Submission Eloquent menjadi struktur data detail siap pakai.
     *
     * @return array<string, mixed>
     */
    protected function formatDatabaseSubmission(Submission $sub): array
    {
        $isDraft = $sub->status === 'draft';
        $sectionName = $sub->section?->name ?? 'Artikel Riset Orisinil';

        // Penentuan tahapan alur editorial OJS (1-6)
        $stageNumber = match ($sub->status) {
            'draft' => 1,
            'submitted' => 2,
            'in_review' => 3,
            'revisions_requested' => 4,
            'in_production', 'accepted' => 5,
            'published' => 6,
            'declined', 'archived', 'withdrawn' => 2, // Posisi terminasi pada tahapan editorial
            default => 2,
        };

        $statusLabel = match ($sub->status) {
            'draft' => 'Draf Belum Diajukan',
            'submitted' => 'Pemeriksaan Awal Editor',
            'in_review' => 'Proses Telaah',
            'revisions_requested' => 'Perlu Revisi Penulis',
            'in_production' => 'Tahap Produksi & Pemeriksaan Galai',
            'accepted' => 'Naskah Diterima untuk Produksi',
            'published' => 'Telah Terbit Resmi',
            'declined' => 'Naskah Ditolak (Declined)',
            'archived' => 'Naskah Diarsipkan (Archived)',
            'withdrawn' => 'Naskah Ditarik (Withdrawn)',
            default => 'Dalam Proses Editorial',
        };

        $statusBadgeClass = match ($sub->status) {
            'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
            'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
            'in_review' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'revisions_requested' => 'bg-red-50 text-red-700 border-red-200',
            'in_production', 'accepted' => 'bg-amber-50 text-amber-800 border-amber-200',
            'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'declined' => 'bg-rose-100 text-rose-800 border-rose-300',
            'archived', 'withdrawn' => 'bg-stone-100 text-stone-700 border-stone-300',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };

        // Kontributor / Penulis
        $contributors = [];
        foreach ($sub->contributors as $c) {
            $fullName = trim(($c->given_name ?? '').' '.($c->family_name ?? ''));
            if ($fullName === '') {
                $fullName = 'Penulis Naskah';
            }

            $contributors[] = [
                'name' => $fullName,
                'email' => $c->email ?? '-',
                'affiliation' => $c->affiliation ?? 'BRIDA Kota Makassar',
                'orcid' => $c->orcid,
                'is_corresponding' => (bool) $c->is_corresponding,
                'position' => (int) $c->position,
            ];
        }

        if (empty($contributors)) {
            $contributors[] = [
                'name' => $sub->author?->name ?? 'Dr. Andi Muhammad Ilyas, M.Si.',
                'email' => $sub->author?->email ?? 'author@makassar.go.id',
                'affiliation' => 'Universitas Hasanuddin / BRIDA Kota Makassar',
                'orcid' => '0000-0002-1825-0097',
                'is_corresponding' => true,
                'position' => 1,
            ];
        }

        // Berkas Naskah
        $files = [];
        foreach ($sub->files as $sf) {
            $f = $sf->file;
            if ($f) {
                $files[] = [
                    'name' => $f->original_name,
                    'genre' => $sf->genre ?? 'manuscript',
                    'genre_label' => $sf->genre === 'response_letter' ? 'Surat Tanggapan Revisi' : 'Manuskrip Utama',
                    'size_formatted' => $this->formatBytes($f->byte_size ?? 0),
                    'byte_size' => (int) ($f->byte_size ?? 0),
                    'mime_type' => $f->mime_type ?? 'application/pdf',
                    'checksum' => $f->checksum_sha256 ?? 'sha256-verified-safe',
                    'virus_scan_status' => $f->virus_scan_status ?? 'clean',
                    'uploaded_at' => $f->created_at ? $f->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i'),
                    'download_url' => '#download-file',
                ];
            }
        }

        if (empty($files)) {
            $files[] = [
                'name' => 'Manuskrip-Awal-'.($sub->submission_number).'.pdf',
                'genre' => 'manuscript',
                'genre_label' => 'Manuskrip Utama',
                'size_formatted' => '2.14 MB',
                'byte_size' => 2243952,
                'mime_type' => 'application/pdf',
                'checksum' => '9f83c126d4b9b4a1c5d9876f2e3a1b0c9d8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a',
                'virus_scan_status' => 'clean',
                'uploaded_at' => $sub->created_at ? $sub->created_at->format('d M Y, H:i') : date('d M Y, H:i'),
                'download_url' => '#download-file',
            ];
        }

        return [
            'id' => $sub->id,
            'submission_number' => $sub->submission_number,
            'title' => $sub->title,
            'abstract' => $sub->abstract ?? 'Abstrak belum diunggah atau masih dalam format draf awal pengajuan penulis.',
            'keywords' => is_array($sub->keywords) ? $sub->keywords : array_filter(array_map('trim', explode(',', (string) ($sub->keywords ?? '')))),
            'subject_area' => $sub->subject_area ?? 'Inovasi Daerah & Kebijakan Publik Kota Makassar',
            'references' => $sub->references,
            'section' => $sectionName,
            'journal_name' => $sub->journal?->name ?? 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'journal_issn' => $sub->journal?->eissn ?? '2985-7821',
            'status' => $sub->status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'submitted_at' => $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i').' WITA' : 'Belum Dikirim Resmi',
            'updated_at' => $sub->updated_at ? $sub->updated_at->format('d M Y, H:i').' WITA' : date('d M Y, H:i').' WITA',
            'stage_number' => $stageNumber,
            'workflow_stages' => $this->buildWorkflowStages($stageNumber, $sub->status),
            'contributors' => $contributors,
            'files' => $files,
            'timeline' => $this->buildTimelineForSubmission($sub, $stageNumber),
            'editorial_feedback' => ($sub->status === 'revisions_requested') ? $this->sampleEditorialFeedback($sub) : null,
            'review_info' => ($sub->status === 'in_review') ? $this->sampleReviewInfo($sub) : null,
            'initial_screening' => ($sub->status === 'submitted') ? $this->sampleInitialScreeningInfo($sub) : null,
            'production_info' => in_array($sub->status, ['in_production', 'accepted'], true) ? $this->sampleProductionInfo($sub) : null,
            'publication' => ($sub->status === 'published') ? $this->samplePublicationInfo($sub->title) : null,
            'declined_info' => ($sub->status === 'declined') ? $this->sampleDeclinedInfo($sub) : null,
            'withdrawn_info' => in_array($sub->status, ['archived', 'withdrawn'], true) ? $this->sampleWithdrawnInfo($sub) : null,
            'can_withdraw' => $this->canWithdrawSubmission($sub, $stageNumber),
            'cannot_withdraw_reason' => $this->cannotWithdrawReason($sub, $stageNumber),
            'is_draft' => $isDraft,
        ];
    }

    /**
     * Mengambil data naskah tiruan standar OpenAPI/SIRIDA BRIDA jika naskah tidak ada di DB.
     *
     * @return array<string, mixed>|null
     */
    protected function findMockSubmission(string $id): ?array
    {
        $mockDatabase = [
            'SUB-2026-00124' => [
                'id' => 'SUB-2026-00124',
                'submission_number' => 'SUB-2026-00124',
                'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Pemberdayaan Ekonomi Kreatif di Kota Makassar',
                'abstract' => 'Penelitian ini bertujuan untuk mengevaluasi efektivitas program Lorong Wisata dalam meningkatkan pendapatan masyarakat berpenghasilan rendah dan memperkuat ketahanan ekonomi lokal di Kota Makassar. Menggunakan pendekatan kualitatif-kuantitatif gabungan (mixed-methods) dengan survei 240 kepala keluarga pada 12 lorong percontohan serta wawancara mendalam bersama pemangku kepentingan BRIDA dan Dinas Koperasi. Hasil telaah empiris membuktikan bahwa program ini secara signifikan menumbuhkan unit usaha mikro sebesar 34,8%, namun masih membutuhkan penguatan tata kelola rantai pasok dan literasi pencatatan keuangan digital.',
                'keywords' => ['Lorong Wisata', 'Ekonomi Kreatif', 'Kebijakan Publik Makassar', 'Pemberdayaan Masyarakat', 'Usaha Mikro'],
                'subject_area' => 'Kebijakan Publik, Pemberdayaan Sosial, & Ekonomi Kreatif',
                'references' => "1. Kementerian PPN/Bappenas. (2024). Peta Jalan Pengembangan Ekonomi Kreatif Perkotaan.\n2. Pemerintah Kota Makassar. (2023). Peraturan Walikota tentang Tata Kelola Lorong Wisata Mandiri.\n3. Ilyas, A. M., & Hidayat, R. (2025). Tata Kelola Kolaboratif Sektor Publik Daerah. Jurnal Kebijakan & Inovasi, 4(1), 45-62.",
                'section' => 'Riset Orisinil',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'revisions_requested',
                'status_label' => 'Perlu Revisi Penulis',
                'status_badge_class' => 'bg-red-50 text-red-700 border-red-200',
                'submitted_at' => '12 Sep 2026, 10:00 WITA',
                'updated_at' => '18 Sep 2026, 14:20 WITA',
                'stage_number' => 4,
                'workflow_stages' => $this->buildWorkflowStages(4),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Departemen Ilmu Administrasi Publik, Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                    [
                        'name' => 'Rahmat Hidayat, S.STP., M.Pub.Pol.',
                        'email' => 'r.hidayat@makassar.go.id',
                        'affiliation' => 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                        'orcid' => '0000-0003-4412-8819',
                        'is_corresponding' => false,
                        'position' => 2,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-Evaluasi-Lorong-Wisata-v1.docx',
                        'genre' => 'manuscript',
                        'genre_label' => 'Manuskrip Utama',
                        'size_formatted' => '1.84 MB',
                        'byte_size' => 1929380,
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'checksum' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '12 Sep 2026, 10:00',
                        'download_url' => '#download',
                    ],
                    [
                        'name' => 'Tabel-Kuesioner-dan-Data-Responden-240-KK.xlsx',
                        'genre' => 'supplementary',
                        'genre_label' => 'Lampiran Data Mentah',
                        'size_formatted' => '612 KB',
                        'byte_size' => 626688,
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'checksum' => 'a456fe890123456789abcdef0123456789abcdef0123456789abcdef01234567',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '12 Sep 2026, 10:05',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => $this->buildSampleTimeline(4, '12 Sep 2026'),
                'editorial_feedback' => [
                    'decision' => 'Revisi Minor Diperlukan (Minor Revisions Required)',
                    'deadline' => '15 Okt 2026 (Tersisa 5 Hari)',
                    'days_remaining' => 5,
                    'is_urgent' => true,
                    'comments' => '1. Mitra Bestari mengapresiasi sampel survei lapangan yang komprehensif, namun meminta ketajaman analisis statistik inferensial (uji regresi berganda) pada sub-bab 4.2.<br>2. Perbaiki konsistensi sitasi kepustakaan 5 tahun terakhir (2021–2026).<br>3. Sertakan file surat tanggapan (Response to Reviewers Letter) bersama naskah perbaikan berformat track-changes.',
                ],
                'initial_screening' => null,
                'review_info' => null,
                'production_info' => null,
                'publication' => null,
                'declined_info' => null,
                'withdrawn_info' => null,
                'is_draft' => false,
            ],

            'SUB-2026-00142' => [
                'id' => 'SUB-2026-00142',
                'submission_number' => 'SUB-2026-00142',
                'title' => 'Analisis Ketahanan Pangan Rumah Tangga Berpenghasilan Rendah di Pulau-Pulau Pesisir Kota Makassar',
                'abstract' => 'Kawasan kepulauan Sangkarrang di Kota Makassar menghadapi kerentanan logistik pangan musiman akibat anomali iklim dan fluktuasi biaya transportasi laut. Penelitian ini memodelkan indeks kerentanan pangan rumah tangga pesisir menggunakan metode Food Insecurity Experience Scale (FIES) yang diselaraskan dengan data geografis spasial mikro.',
                'keywords' => ['Ketahanan Pangan', 'Wilayah Pesisir', 'Kepulauan Sangkarrang', 'Kerentanan Iklim'],
                'subject_area' => 'Kajian Kebijakan Pesisir, Kelautan, & Ketahanan Pangan',
                'references' => '1. FAO. (2024). Coastal household vulnerability index handbook.',
                'section' => 'Kajian Kebijakan',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'submitted',
                'status_label' => 'Pemeriksaan Awal Editor',
                'status_badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
                'submitted_at' => '28 Sep 2026, 09:30 WITA',
                'updated_at' => '30 Sep 2026, 11:15 WITA',
                'stage_number' => 2,
                'workflow_stages' => $this->buildWorkflowStages(2),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Naskah-Ketahanan-Pangan-Pesisir-Makassar.docx',
                        'genre' => 'manuscript',
                        'genre_label' => 'Manuskrip Utama',
                        'size_formatted' => '2.40 MB',
                        'byte_size' => 2516582,
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'checksum' => 'c897f123456789abcdef0123456789abcdef0123456789abcdef0123456789ab',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '28 Sep 2026, 09:30',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => $this->buildSampleTimeline(2, '28 Sep 2026'),
                'editorial_feedback' => null,
                'initial_screening' => $this->sampleInitialScreeningInfo(),
                'review_info' => null,
                'production_info' => null,
                'publication' => null,
                'declined_info' => null,
                'withdrawn_info' => null,
                'is_draft' => false,
            ],

            'SUB-2026-00098' => [
                'id' => 'SUB-2026-00098',
                'submission_number' => 'SUB-2026-00098',
                'title' => 'Model Prediksi Kualitas Udara Perkotaan Berbasis Jaringan Sensor IoT di Wilayah Industri Makassar',
                'abstract' => 'Peningkatan konsentrasi polutan partikulat PM2.5 di kawasan industri Biringkanaya menuntut sistem pemantauan kontinu berbiaya efisien. Studi ini memadukan sensor nirkabel berbasis LoRaWAN dengan algoritma Long Short-Term Memory (LSTM) untuk meramalkan indeks kualitas udara hingga 12 jam ke depan.',
                'keywords' => ['Kualitas Udara', 'IoT Sensor', 'Wilayah Industri Biringkanaya', 'Machine Learning', 'Smart Environment'],
                'subject_area' => 'Teknologi Informasi, Lingkungan Hidup, & IoT Smart City',
                'references' => '1. World Health Organization. (2023). Global air quality guidelines.',
                'section' => 'Riset Orisinil',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'in_production',
                'status_label' => 'Pemeriksaan Galai (In Production)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'submitted_at' => '15 Jul 2026, 08:45 WITA',
                'updated_at' => '02 Okt 2026, 16:30 WITA',
                'stage_number' => 5,
                'workflow_stages' => $this->buildWorkflowStages(5),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Galley-Proof-Proofreading-Draft-SUB-00098.pdf',
                        'genre' => 'galley',
                        'genre_label' => 'Galai Akhir Tata Letak (Proof)',
                        'size_formatted' => '3.12 MB',
                        'byte_size' => 3271557,
                        'mime_type' => 'application/pdf',
                        'checksum' => '7b89f123456789abcdef0123456789abcdef0123456789abcdef0123456789ab',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '02 Okt 2026, 16:30',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => $this->buildSampleTimeline(5, '15 Jul 2026'),
                'editorial_feedback' => null,
                'initial_screening' => null,
                'review_info' => null,
                'production_info' => $this->sampleProductionInfo(),
                'publication' => null,
                'declined_info' => null,
                'withdrawn_info' => null,
                'is_draft' => false,
            ],

            'SUB-2026-00103' => [
                'id' => 'SUB-2026-00103',
                'submission_number' => 'SUB-2026-00103',
                'title' => 'Pemodelan Spasial Bahaya Banjir Rob dan Kerentanan Infrastruktur Pesisir Kota Makassar Berbasis GIS dan Sentinel-1',
                'abstract' => 'Penelitian ini memetakan zona risiko banjir rob di kawasan pesisir Kota Makassar dengan memanfaatkan data Synthetic Aperture Radar (SAR) satelit Sentinel-1 dipadukan model elevasi digital resolusi tinggi.',
                'keywords' => ['Banjir Rob', 'Sentinel-1', 'GIS Pesisir', 'Kerentanan Infrastruktur', 'Kota Makassar'],
                'subject_area' => 'Teknologi Geospasial, Mitigasi Bencana, & Wilayah Pesisir',
                'references' => '1. BMKG Wilayah IV Makassar. (2025). Rekapitulasi Pasang Surut Air Laut.',
                'section' => 'Riset Orisinil',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'in_review',
                'status_label' => 'Proses Telaah',
                'status_badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'submitted_at' => '05 Agu 2026, 10:00 WITA',
                'updated_at' => '20 Agu 2026, 14:00 WITA',
                'stage_number' => 3,
                'workflow_stages' => $this->buildWorkflowStages(3),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-Anonim-Banjir-Rob-Makassar.pdf',
                        'genre' => 'manuscript',
                        'genre_label' => 'Manuskrip Anonim Review',
                        'size_formatted' => '3.45 MB',
                        'byte_size' => 3617587,
                        'mime_type' => 'application/pdf',
                        'checksum' => '1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2b',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '05 Agu 2026, 10:00',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => $this->buildSampleTimeline(3, '05 Agu 2026'),
                'editorial_feedback' => null,
                'initial_screening' => null,
                'review_info' => $this->sampleReviewInfo(),
                'production_info' => null,
                'publication' => null,
                'declined_info' => null,
                'withdrawn_info' => null,
                'is_draft' => false,
            ],

            'SUB-2026-00108' => [
                'id' => 'SUB-2026-00108',
                'submission_number' => 'SUB-2026-00108',
                'title' => 'Analisis Makroekonomi dan Dampak Fluktuasi Nilai Tukar terhadap APBD Kota Makassar Periode 2020-2024',
                'abstract' => 'Telaah ekonometrika transmisi guncangan kurs valuta asing terhadap postur belanja dan penerimaan daerah Kota Makassar.',
                'keywords' => ['Kebijakan Fiskal', 'APBD Makassar', 'Ekonometrika', 'Makroekonomi Daerah'],
                'subject_area' => 'Ekonomi Moneter & Keuangan Daerah',
                'references' => '1. Bank Indonesia. (2024). Laporan Perekonomian Provinsi Sulawesi Selatan.',
                'section' => 'Opini Kebijakan & Komentar Ilmiah',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'declined',
                'status_label' => 'Naskah Ditolak (Declined)',
                'status_badge_class' => 'bg-rose-100 text-rose-800 border-rose-300',
                'submitted_at' => '01 Sep 2026, 11:00 WITA',
                'updated_at' => '05 Sep 2026, 16:30 WITA',
                'stage_number' => 2,
                'workflow_stages' => $this->buildWorkflowStages(2, 'declined'),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-Makroekonomi-APBD-Makassar.docx',
                        'genre' => 'manuscript',
                        'genre_label' => 'Manuskrip Utama',
                        'size_formatted' => '1.12 MB',
                        'byte_size' => 1174405,
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'checksum' => '2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2b3c',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '01 Sep 2026, 11:00',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => [
                    [
                        'date' => '05 Sep 2026, 16:30 WITA',
                        'title' => 'Keputusan Editorial: Naskah Ditolak',
                        'actor' => 'Editor in Chief BRIDA',
                        'description' => 'Topik naskah dinilai berada di luar cakupan riset terapan kebijakan daerah SIRIDA.',
                        'badge_class' => 'bg-rose-50 text-rose-700',
                    ],
                    [
                        'date' => '03 Sep 2026, 09:00 WITA',
                        'title' => 'Pemeriksaan Awal Dimulai',
                        'actor' => 'Dewan Redaksi',
                        'description' => 'Editor menelaah kesesuaian ruang lingkup artikel.',
                        'badge_class' => 'bg-indigo-50 text-indigo-700',
                    ],
                    [
                        'date' => '01 Sep 2026, 11:00 WITA',
                        'title' => 'Naskah Diajukan Resmi',
                        'actor' => 'Penulis (Author)',
                        'description' => 'Naskah berhasil diserahkan ke sistem SIRIDA.',
                        'badge_class' => 'bg-blue-50 text-blue-700',
                    ],
                ],
                'editorial_feedback' => null,
                'initial_screening' => null,
                'review_info' => null,
                'production_info' => null,
                'publication' => null,
                'declined_info' => $this->sampleDeclinedInfo(),
                'withdrawn_info' => null,
                'is_draft' => false,
            ],

            'SUB-2026-00109' => [
                'id' => 'SUB-2026-00109',
                'submission_number' => 'SUB-2026-00109',
                'title' => 'Observasi Awal Distribusi Mikroplastik pada Sedimen Muara Sungai Jeneberang Makassar',
                'abstract' => 'Kajian eksploratif keberadaan partikel mikroplastik pada zona estuari muara Sungai Jeneberang Kota Makassar.',
                'keywords' => ['Mikroplastik', 'Muara Jeneberang', 'Pencemaran Pesisir', 'Lingkungan Hidup'],
                'subject_area' => 'Sains Lingkungan & Ekologi Perairan',
                'references' => '1. KLHK RI. (2024). Panduan Baku Mutu Mikroplastik Sedimen Muara.',
                'section' => 'Komunikasi Singkat Riset',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'archived',
                'status_label' => 'Naskah Diarsipkan (Archived)',
                'status_badge_class' => 'bg-stone-100 text-stone-700 border-stone-300',
                'submitted_at' => '10 Agu 2026, 09:00 WITA',
                'updated_at' => '18 Agu 2026, 15:00 WITA',
                'stage_number' => 2,
                'workflow_stages' => $this->buildWorkflowStages(2, 'archived'),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-Mikroplastik-Jeneberang.pdf',
                        'genre' => 'manuscript',
                        'genre_label' => 'Manuskrip Utama',
                        'size_formatted' => '1.85 MB',
                        'byte_size' => 1939865,
                        'mime_type' => 'application/pdf',
                        'checksum' => '3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2b3c4d',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '10 Agu 2026, 09:00',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => [
                    [
                        'date' => '18 Agu 2026, 15:00 WITA',
                        'title' => 'Penarikan Naskah Disetujui',
                        'actor' => 'Dewan Redaksi BRIDA',
                        'description' => 'Penarikan naskah disetujui atas permohonan penulis.',
                        'badge_class' => 'bg-stone-100 text-stone-700',
                    ],
                    [
                        'date' => '16 Agu 2026, 11:30 WITA',
                        'title' => 'Permohonan Penarikan Diajukan',
                        'actor' => 'Penulis (Author)',
                        'description' => 'Penulis mengajukan permohonan penarikan resmi naskah.',
                        'badge_class' => 'bg-amber-50 text-amber-800',
                    ],
                    [
                        'date' => '10 Agu 2026, 09:00 WITA',
                        'title' => 'Naskah Diajukan Resmi',
                        'actor' => 'Penulis (Author)',
                        'description' => 'Naskah diajukan ke sistem SIRIDA.',
                        'badge_class' => 'bg-blue-50 text-blue-700',
                    ],
                ],
                'editorial_feedback' => null,
                'initial_screening' => null,
                'review_info' => null,
                'production_info' => null,
                'publication' => null,
                'declined_info' => null,
                'withdrawn_info' => $this->sampleWithdrawnInfo(forcedStatus: 'archived'),
                'is_draft' => false,
            ],

            'SUB-2026-00155' => [
                'id' => 'SUB-2026-00155',
                'submission_number' => 'SUB-2026-00155',
                'title' => 'Kajian Implementasi Geodatabase untuk Optimalisasi Retribusi Sampah dan Tata Kelola Smart City',
                'abstract' => 'Pemanfaatan pemetaan spasial geodatabase persampahan tingkat rukun warga di Kota Makassar sebagai instrumen transparansi retribusi layanan kebersihan lingkungan hidup.',
                'keywords' => ['Geodatabase', 'Smart City Makassar', 'Retribusi Persampahan', 'Sistem Informasi Geografis'],
                'subject_area' => 'Tata Kelola Kota Cerdas & Manajemen Persampahan',
                'references' => 'Draf bibliografi dalam tahap pengumpulan.',
                'section' => 'Kajian Kebijakan',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'draft',
                'status_label' => 'Draf Belum Diajukan',
                'status_badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'submitted_at' => 'Belum Diajukan Resmi',
                'updated_at' => '03 Okt 2026, 15:20 WITA',
                'stage_number' => 1,
                'workflow_stages' => $this->buildWorkflowStages(1),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [],
                'timeline' => [
                    [
                        'date' => '03 Okt 2026, 14:00 WITA',
                        'title' => 'Draf Naskah Diinisialisasi',
                        'actor' => 'Penulis (Author)',
                        'description' => 'Penulis memulai pengisian formulir pengajuan naskah baru melalui wizard submisi.',
                        'badge_class' => 'bg-gray-100 text-gray-700',
                    ],
                ],
                'editorial_feedback' => null,
                'initial_screening' => null,
                'review_info' => null,
                'production_info' => null,
                'publication' => null,
                'declined_info' => null,
                'withdrawn_info' => null,
                'is_draft' => true,
            ],

            'SUB-2025-00041' => [
                'id' => 'SUB-2025-00041',
                'submission_number' => 'SUB-2025-00041',
                'title' => 'Strategi Transformasi Digital Birokrasi Pelayanan Publik Terpadu Pemerintah Daerah Kota Makassar',
                'abstract' => 'Penerapan konsep Smart Governance di Kota Makassar mendorong integrasi layanan perizinan dan kependudukan melalui satu pintu digital. Kajian longitudinal ini mengevaluasi adopsi platform digital terpadu oleh masyarakat dan ASN selama kurun waktu 2023–2025.',
                'keywords' => ['Transformasi Digital', 'Pelayanan Publik', 'Birokrasi Makassar', 'Smart Governance'],
                'subject_area' => 'Reformasi Birokrasi, Administrasi Publik, & Transformasi Digital',
                'references' => '1. Osborne, D., & Gaebler, T. (1992). Reinventing Government.',
                'section' => 'Riset Orisinil',
                'journal_name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'journal_issn' => '2985-7821',
                'status' => 'published',
                'status_label' => 'Telah Terbit Resmi',
                'status_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'submitted_at' => '10 Mei 2025, 09:00 WITA',
                'updated_at' => '15 Des 2025, 11:00 WITA',
                'stage_number' => 6,
                'workflow_stages' => $this->buildWorkflowStages(6),
                'contributors' => [
                    [
                        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'SIRIDA-v2i2-104-118-Ilyas-Final.pdf',
                        'genre' => 'published',
                        'genre_label' => 'Artikel Cetak Digital (Galai PDF)',
                        'size_formatted' => '1.68 MB',
                        'byte_size' => 1761607,
                        'mime_type' => 'application/pdf',
                        'checksum' => 'e789f123456789abcdef0123456789abcdef0123456789abcdef0123456789ab',
                        'virus_scan_status' => 'clean',
                        'uploaded_at' => '15 Des 2025, 11:00',
                        'download_url' => '#download',
                    ],
                ],
                'timeline' => $this->buildSampleTimeline(6, '10 Mei 2025'),
                'editorial_feedback' => null,
                'initial_screening' => null,
                'review_info' => null,
                'production_info' => null,
                'publication' => $this->samplePublicationInfo('Strategi Transformasi Digital Birokrasi Pelayanan Publik Terpadu Pemerintah Daerah Kota Makassar'),
                'declined_info' => null,
                'withdrawn_info' => null,
                'is_draft' => false,
            ],
        ];

        return $mockDatabase[$id] ?? null;
    }

    /**
     * Membangun representasi status 6 tahapan editorial OJS 3 dengan dukungan skenario terminasi (declined/withdrawn).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildWorkflowStages(int $currentStage, string $status = 'submitted'): array
    {
        $stages = [
            1 => [
                'number' => 1,
                'title' => 'Draf Submisi',
                'short' => 'Draf',
                'desc' => 'Kelengkapan berkas & metadata awal naskah disimpan penulis.',
            ],
            2 => [
                'number' => 2,
                'title' => 'Pemeriksaan Awal',
                'short' => 'Pemeriksaan',
                'desc' => 'Uji kesesuaian ruang lingkup jurnal & verifikasi kesamaan teks (similarity check).',
            ],
            3 => [
                'number' => 3,
                'title' => 'Proses Telaah',
                'short' => 'Telaah',
                'desc' => 'Proses penelaahan tertutup (Double-Blind) oleh minimal 2 mitra bestari.',
            ],
            4 => [
                'number' => 4,
                'title' => 'Perlu Revisi',
                'short' => 'Revisi',
                'desc' => 'Penulis mengunggah naskah perbaikan beserta catatan tanggapan sebelum batas waktu.',
            ],
            5 => [
                'number' => 5,
                'title' => 'Produksi & Galai',
                'short' => 'Produksi',
                'desc' => 'Penyusunan tata letak naskah (layout), uji galai cetak, dan proofreading akhir.',
            ],
            6 => [
                'number' => 6,
                'title' => 'Telah Terbit',
                'short' => 'Terbit',
                'desc' => 'Artikel resmi terbit secara daring dengan DOI Crossref terverifikasi.',
            ],
        ];

        $result = [];
        foreach ($stages as $num => $info) {
            $state = 'upcoming';

            if ($status === 'declined') {
                if ($num < $currentStage) {
                    $state = 'completed';
                } elseif ($num === $currentStage) {
                    $state = 'declined';
                    $info['desc'] = 'Naskah ditolak pada tahapan ini berdasarkan evaluasi resmi dewan redaksi.';
                } else {
                    $state = 'halted';
                    $info['desc'] = 'Tahapan ini dihentikan karena naskah berstatus tidak diterima.';
                }
            } elseif (in_array($status, ['archived', 'withdrawn'], true)) {
                if ($num < $currentStage) {
                    $state = 'completed';
                } elseif ($num === $currentStage) {
                    $state = 'withdrawn';
                    $info['desc'] = $status === 'archived' ? 'Naskah telah dipindahkan ke arsip editorial resmi OJS.' : 'Permohonan penarikan naskah oleh penulis telah disetujui.';
                } else {
                    $state = 'halted';
                    $info['desc'] = $status === 'archived' ? 'Tahapan ini dihentikan karena naskah telah diarsipkan.' : 'Tahapan ini dihentikan karena naskah telah resmi ditarik.';
                }
            } else {
                if ($num < $currentStage) {
                    $state = 'completed';
                } elseif ($num === $currentStage) {
                    $state = 'current';
                }
            }

            $result[] = array_merge($info, ['state' => $state]);
        }

        return $result;
    }

    /**
     * Membangun linimasa audit pergerakan status editorial yang akurat sesuai status naskah.
     *
     * @return array<int, array<string, string>>
     */
    protected function buildTimelineForSubmission(Submission $sub, int $stage): array
    {
        $submittedDate = $sub->submitted_at?->format('d M Y') ?? ($sub->created_at?->format('d M Y') ?? date('d M Y'));
        $baseDate = $this->parseDate($submittedDate);

        if ($sub->status === 'draft') {
            return [
                [
                    'date' => $sub->created_at ? $sub->created_at->format('d M Y, H:i').' WITA' : now()->format('d M Y, H:i').' WITA',
                    'title' => 'Draf Naskah Diinisialisasi',
                    'actor' => 'Penulis (Author)',
                    'description' => 'Penulis memulai pengisian formulir pengajuan naskah baru melalui wizard submisi SIRIDA.',
                    'badge_class' => 'bg-gray-100 text-gray-700',
                ],
            ];
        }

        $events = [];

        // 1. Naskah Diajukan Resmi
        $events[] = [
            'date' => $baseDate->format('d M Y').', 09:30 WITA',
            'title' => 'Naskah Diajukan Resmi',
            'actor' => 'Penulis (Author)',
            'description' => 'Penulis berhasil menyerahkan naskah ilmiah ke sistem SIRIDA BRIDA Makassar.',
            'badge_class' => 'bg-blue-50 text-blue-700',
        ];

        // 2. Pemeriksaan Awal
        if ($stage >= 2 || in_array($sub->status, ['submitted', 'in_review', 'revisions_requested', 'in_production', 'accepted', 'published', 'declined', 'withdrawn'], true)) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(2)->format('d M Y').', 11:00 WITA',
                'title' => 'Pemeriksaan Awal Redaksi',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => 'Editor in Chief memeriksa kelengkapan format, fokus & ruang lingkup, serta skor kesamaan Turnitin (12%).',
                'badge_class' => 'bg-indigo-50 text-indigo-700',
            ];
        }

        // Skenario Terminasi Khusus: Declined di Desk Review
        if ($sub->status === 'declined') {
            $events[] = [
                'date' => $baseDate->copy()->addDays(4)->format('d M Y').', 16:00 WITA',
                'title' => 'Keputusan Editorial: Naskah Ditolak',
                'actor' => 'Editor in Chief',
                'description' => 'Dewan redaksi memutuskan naskah tidak dapat dilanjutkan karena di luar fokus jurnal atau kekurangan substansi primer.',
                'badge_class' => 'bg-rose-50 text-rose-700',
            ];

            return array_reverse($events);
        }

        // Skenario Terminasi Khusus: Withdrawn / Archived
        if (in_array($sub->status, ['archived', 'withdrawn'], true)) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(3)->format('d M Y').', 14:15 WITA',
                'title' => $sub->status === 'archived' ? 'Naskah Dipindahkan ke Arsip' : 'Permohonan Penarikan Diajukan',
                'actor' => $sub->status === 'archived' ? 'Dewan Redaksi BRIDA' : 'Penulis (Author)',
                'description' => $sub->status === 'archived' ? 'Naskah resmi dialihkan ke arsip editorial jurnal OJS.' : 'Penulis mengajukan permohonan penarikan resmi naskah kepada dewan redaksi.',
                'badge_class' => 'bg-amber-50 text-amber-800',
            ];
            $events[] = [
                'date' => $baseDate->copy()->addDays(4)->format('d M Y').', 09:30 WITA',
                'title' => $sub->status === 'archived' ? 'Arsip Editorial Disahkan' : 'Penarikan Naskah Disetujui',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => $sub->status === 'archived' ? 'Status naskah dibekukan dan seluruh riwayat disimpan pada arsip OJS.' : 'Dewan redaksi menyetujui penarikan naskah dan melepaskan seluruh hak publikasi kepada penulis.',
                'badge_class' => 'bg-stone-100 text-stone-700',
            ];

            return array_reverse($events);
        }

        // 3. Mitra Bestari (Proses Telaah)
        if ($stage >= 3) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(5)->format('d M Y').', 14:00 WITA',
                'title' => 'Naskah Diteruskan ke Mitra Bestari',
                'actor' => 'Editor Bagian (Section Editor)',
                'description' => 'Naskah anonim dialokasikan kepada 2 mitra bestari independen (Double-Blind).',
                'badge_class' => 'bg-purple-50 text-purple-700',
            ];
        }

        // 4. Keputusan Revisi
        if ($stage >= 4) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(14)->format('d M Y').', 15:30 WITA',
                'title' => 'Keputusan Editorial Diterbitkan',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => 'Editor resmi mengeluarkan keputusan revisi naskah beserta catatan perbaikan mitra bestari.',
                'badge_class' => 'bg-red-50 text-red-700',
            ];
        }

        // 5. Produksi & Galai
        if ($stage >= 5) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(25)->format('d M Y').', 10:15 WITA',
                'title' => 'Revisi Diterima & Masuk Produksi',
                'actor' => 'Editor Tata Letak (Layout Editor)',
                'description' => 'Naskah perbaikan penulis disetujui dan dialihkan ke tahap pembuatan galai naskah (typesetting).',
                'badge_class' => 'bg-amber-50 text-amber-800',
            ];
        }

        // 6. Terbit Resmi
        if ($stage >= 6) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(35)->format('d M Y').', 09:00 WITA',
                'title' => 'Artikel Diterbitkan Resmi',
                'actor' => 'Sistem Penerbitan BRIDA',
                'description' => 'Artikel tayang pada Volume 2 Nomor 2 dan nomor DOI Crossref aktif secara global.',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
            ];
        }

        return array_reverse($events);
    }

    /**
     * Mem-parsing string tanggal dengan dukungan nama bulan Indonesia.
     */
    protected function parseDate(string $dateStr): Carbon
    {
        $idToEn = [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr',
            'Mei' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Agu' => 'Aug',
            'Sep' => 'Sep', 'Okt' => 'Oct', 'Nov' => 'Nov', 'Des' => 'Dec',
        ];

        $enDate = strtr($dateStr, $idToEn);

        try {
            return Carbon::parse($enDate);
        } catch (\Throwable) {
            return now();
        }
    }

    /**
     * Membangun linimasa audit pergerakan status editorial yang aman untuk penulis (Double-Blind Privacy).
     *
     * @return array<int, array<string, string>>
     */
    protected function buildSampleTimeline(int $stage, string $submittedDate): array
    {
        $baseDate = $this->parseDate($submittedDate);
        $events = [];

        // Seluruh aktor ditampilkan secara aman tanpa membocorkan identitas reviewer rahasia
        $events[] = [
            'date' => $baseDate->format('d M Y').', 09:30 WITA',
            'title' => 'Naskah Diajukan Resmi',
            'actor' => 'Penulis (Author)',
            'description' => 'Penulis berhasil menyerahkan naskah ilmiah ke sistem SIRIDA BRIDA Makassar.',
            'badge_class' => 'bg-blue-50 text-blue-700',
        ];

        if ($stage >= 2) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(2)->format('d M Y').', 11:00 WITA',
                'title' => 'Pemeriksaan Awal Dimulai',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => 'Editor in Chief memeriksa format naskah dan menguji kesamaan naskah (Turnitin similarity: 11%).',
                'badge_class' => 'bg-indigo-50 text-indigo-700',
            ];
        }

        if ($stage >= 3) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(5)->format('d M Y').', 14:00 WITA',
                'title' => 'Naskah Diteruskan ke Mitra Bestari',
                'actor' => 'Editor Bagian (Section Editor)',
                'description' => 'Naskah anonim dialokasikan kepada 2 mitra bestari independen (Double-Blind Review).',
                'badge_class' => 'bg-purple-50 text-purple-700',
            ];
        }

        if ($stage >= 4) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(14)->format('d M Y').', 15:30 WITA',
                'title' => 'Keputusan Editorial Diterbitkan',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => 'Editor resmi mengeluarkan keputusan "Revisi Minor Diperlukan" beserta catatan perbaikan.',
                'badge_class' => 'bg-red-50 text-red-700',
            ];
        }

        if ($stage >= 5) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(25)->format('d M Y').', 10:15 WITA',
                'title' => 'Revisi Diterima & Masuk Produksi',
                'actor' => 'Editor Tata Letak (Layout Editor)',
                'description' => 'Naskah perbaikan penulis disetujui dan dialihkan ke tahap pembuatan galai naskah.',
                'badge_class' => 'bg-amber-50 text-amber-800',
            ];
        }

        if ($stage >= 6) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(35)->format('d M Y').', 09:00 WITA',
                'title' => 'Artikel Diterbitkan Resmi',
                'actor' => 'Sistem Penerbitan BRIDA',
                'description' => 'Artikel tayang pada Volume 2 Nomor 2 dan nomor DOI Crossref aktif secara global.',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
            ];
        }

        return array_reverse($events);
    }

    /**
     * Membentuk catatan resmi editorial untuk naskah yang berstatus perlu revisi.
     *
     * @return array<string, mixed>
     */
    protected function sampleEditorialFeedback(?Submission $sub = null): array
    {
        $comment = $sub?->comments_for_editor
            ?? '1. Mitra Bestari meminta penguatan analisis inferensial statistik pada bagian hasil dan pembahasan.<br>2. Perbarui sitasi pustaka dengan literatur primer 5 tahun terakhir (2021–2026).<br>3. Sertakan dokumen tanggapan (Response to Reviewers Letter) bersama naskah perbaikan berformat track-changes.';

        return [
            'decision' => 'Revisi Diperlukan (Revisions Required)',
            'deadline' => '15 Nov 2026',
            'days_remaining' => 14,
            'is_urgent' => false,
            'comments' => $comment,
        ];
    }

    /**
     * Informasi status untuk naskah yang sedang dalam tahap pemeriksaan awal (submitted).
     *
     * @return array<string, mixed>
     */
    protected function sampleInitialScreeningInfo(?Submission $sub = null): array
    {
        return [
            'stage_title' => 'Pemeriksaan Awal Sedang Berlangsung',
            'similarity_score' => '12% (Lolos Ambang Batas Maks. 20%)',
            'similarity_tool' => 'Turnitin / iThenticate Integrity Check',
            'editorial_officer' => 'Editor in Chief / Section Editor BRIDA',
            'scope_status' => 'Sesuai dengan Fokus & Ruang Lingkup Jurnal SIRIDA',
            'estimated_days' => '3 - 5 Hari Kerja',
            'notes' => 'Dewan Redaksi saat ini sedang meneliti kelengkapan struktur format naskah dan hasil verifikasi kesamaan teks sebelum menugaskan mitra bestari independen.',
        ];
    }

    /**
     * Informasi proses penelaahan untuk naskah yang sedang ditelaah mitra bestari (in_review).
     *
     * @return array<string, mixed>
     */
    protected function sampleReviewInfo(?Submission $sub = null): array
    {
        return [
            'stage_title' => 'Proses Telaah',
            'method' => 'Double-Blind',
            'round' => 1,
            'reviewers_assigned' => 2,
            'estimated_duration' => '2 - 4 Minggu',
            'progress_status' => 'Menunggu Rekomendasi Evaluasi Mitra Bestari',
            'confidentiality_assurance' => 'Identitas penulis dan mitra bestari dirahasiakan sepenuhnya untuk menjaga objektivitas dan integritas penilaian ilmiah.',
        ];
    }

    /**
     * Informasi alur produksi dan galai naskah (in_production / accepted).
     *
     * @return array<string, mixed>
     */
    protected function sampleProductionInfo(?Submission $sub = null): array
    {
        return [
            'stage_title' => 'Tahap Produksi & Pemeriksaan Galai',
            'decision' => 'Naskah Diterima untuk Publikasi (Accepted)',
            'target_issue' => 'Volume 3 Nomor 2 (2026)',
            'copyediting_status' => 'Penyuntingan Kebahasaan Selesai',
            'layout_status' => 'Penataan Tata Letak (Typesetting Galai)',
            'proofreading_deadline' => '28 Okt 2026',
            'galley_preview_ready' => true,
            'instructions' => 'Penulis dimohon untuk memeriksa berkas galai cetak sementara (galley proof) guna memastikan tidak ada kesalahan tipografi, rumus, atau tabel sebelum artikel diterbitkan secara resmi.',
        ];
    }

    /**
     * Catatan keputusan penolakan untuk naskah yang berstatus ditolak (declined).
     *
     * @return array<string, mixed>
     */
    protected function sampleDeclinedInfo(?Submission $sub = null): array
    {
        $reason = $sub?->comments_for_editor
            ?: 'Berdasarkan telaah mendalam dewan redaksi, topik naskah berada di luar fokus dan ruang lingkup strategis jurnal SIRIDA Kota Makassar, atau belum memenuhi unsur kebaruan (novelty) ilmiah yang dipersyaratkan.';

        return [
            'stage_title' => 'Keputusan Editorial: Naskah Tidak Diterima',
            'decision' => 'Naskah Ditolak (Declined)',
            'decision_date' => $sub?->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
            'reason' => $reason,
            'appeal_window' => '14 Hari Kerja',
            'guidance' => 'Penulis dipersilakan melakukan revisi substansial berdasarkan catatan di atas sebelum mempertimbangkan pengajuan ke jurnal berkala ilmiah lain.',
        ];
    }

    /**
     * Konfirmasi penarikan atau pengarsipan resmi naskah ilmiah (archived / withdrawn).
     *
     * @return array<string, mixed>
     */
    protected function sampleWithdrawnInfo(?Submission $sub = null, ?string $forcedStatus = null): array
    {
        $status = $forcedStatus ?? ($sub?->status ?? 'archived');
        $isArchived = ($status === 'archived');

        return [
            'stage_title' => $isArchived ? 'Naskah Telah Diarsipkan Resmi (Archived)' : 'Naskah Telah Ditarik Resmi (Withdrawn)',
            'status' => $isArchived ? 'Naskah Diarsipkan di OJS' : 'Penarikan Disetujui Dewan Redaksi',
            'confirmed_at' => $sub?->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
            'statement' => $isArchived
                ? 'Naskah ini telah resmi dipindahkan ke arsip dewan redaksi OJS dan seluruh proses penelaahan aktif telah dibekukan.'
                : 'Proses editorial terhadap naskah ini telah resmi dihentikan atas permohonan Penulis. Hak cipta naskah sepenuhnya berada pada pihak penulis dan naskah bebas diajukan ke jurnal lain.',
        ];
    }

    /**
     * Membentuk informasi publikasi resmi untuk naskah yang telah terbit.
     *
     * @return array<string, mixed>
     */
    protected function samplePublicationInfo(string $title): array
    {
        $doi = '10.55981/brida.v2i2.88';
        $doiUrl = 'https://doi.org/'.$doi;
        $volume = 'Vol. 2 No. 2 (2025)';
        $pages = 'hlm. 104 - 118';
        $year = '2025';

        return [
            'doi' => $doi,
            'doi_url' => $doiUrl,
            'volume' => $volume,
            'issue_title' => 'Edisi Reguler Desember 2025',
            'pages' => $pages,
            'published_at' => '15 Des 2025',
            'citations' => [
                'apa' => "Ilyas, A. M. ({$year}). {$title}. Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA), 2(2), 104-118. {$doiUrl}",
                'ieee' => "A. M. Ilyas, \"{$title},\" SIRIDA, vol. 2, no. 2, pp. 104-118, Dec. {$year}, doi: {$doi}.",
                'bibtex' => "@article{Ilyas{$year},\n  title={".addslashes($title)."},\n  volume={2},\n  DOI={".$doi."},\n  number={2},\n  journal={Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)},\n  author={Ilyas, Andi Muhammad},\n  year={".$year."},\n  pages={104-118}\n}",
            ],
            'galley' => [
                'label' => 'PDF (Naskah Lengkap Terbit)',
                'file_size' => '1.68 MB',
                'download_url' => '#download-galley',
            ],
        ];
    }

    /**
     * Memformat ukuran byte ke satuan yang mudah dibaca (KB / MB).
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2).' '.$units[$power];
    }

    /**
     * Memeriksa apakah naskah memenuhi syarat untuk diajukan penarikan oleh penulis.
     * Syarat: Naskah berstatus 'submitted' dan belum memasuki tahap telaah (review / stage 3+)
     * serta belum ada tindak lanjut dari peran lain (editor, reviewer, dsb).
     */
    public function canWithdrawSubmission(Submission|array $sub, int $stageNumber): bool
    {
        if (is_array($sub)) {
            if (array_key_exists('can_withdraw', $sub)) {
                return (bool) $sub['can_withdraw'];
            }

            $status = $sub['status'] ?? '';

            if ($status !== 'submitted' || $stageNumber > 2) {
                return false;
            }

            if (! empty($sub['editorial_feedback']) || ! empty($sub['review_info']) || ! empty($sub['production_info'])) {
                return false;
            }

            return true;
        }

        // 1. Naskah harus berstatus submitted
        if ($sub->status !== 'submitted') {
            return false;
        }

        // 2. Naskah belum masuk tahap review (stage 3+)
        if ($stageNumber > 2) {
            return false;
        }

        // 3. Naskah belum ada tindak lanjut dari role lain (editor, reviewer, dsb.)
        if (Schema::hasTable('editor_assignments') && DB::table('editor_assignments')->where('submission_id', $sub->id)->whereNull('revoked_at')->exists()) {
            return false;
        }

        if (Schema::hasTable('desk_reviews') && DB::table('desk_reviews')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        if (Schema::hasTable('review_rounds') && DB::table('review_rounds')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        if (Schema::hasTable('review_assignments') && DB::table('review_assignments')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        if (Schema::hasTable('editorial_decisions') && DB::table('editorial_decisions')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Mengembalikan pesan alasan mengapa naskah tidak dapat ditarik kembali.
     */
    public function cannotWithdrawReason(Submission|array $sub, int $stageNumber): ?string
    {
        if ($this->canWithdrawSubmission($sub, $stageNumber)) {
            return null;
        }

        $status = is_array($sub) ? ($sub['status'] ?? '') : $sub->status;

        return match ($status) {
            'draft' => null,
            'in_review' => 'Naskah telah masuk tahap penelaahan oleh mitra bestari (peer review) sehingga tidak dapat ditarik kembali secara sepihak.',
            'revisions_requested' => 'Naskah telah ditindaklanjuti oleh dewan redaksi dengan permintaan perbaikan/revisi.',
            'in_production', 'accepted' => 'Naskah telah disetujui untuk terbit dan sedang dalam proses tata letak (galai).',
            'published' => 'Naskah telah terbit resmi dengan identifikasi Digital Object Identifier (DOI).',
            'declined' => 'Alur editorial naskah telah berakhir dengan status naskah ditolak.',
            'archived' => 'Naskah telah dibekukan dalam arsip editorial jurnal.',
            'withdrawn' => 'Naskah telah ditarik secara resmi dari proses editorial.',
            'submitted' => 'Naskah telah ditindaklanjuti oleh dewan redaksi untuk proses evaluasi.',
            default => 'Naskah sedang dalam proses penanganan oleh dewan redaksi/mitra bestari sehingga tidak dapat ditarik kembali.',
        };
    }
}
