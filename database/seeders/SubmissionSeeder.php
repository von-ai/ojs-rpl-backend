<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SubmissionSeeder extends Seeder
{
    /**
     * Menjalankan seeding basis data untuk seluruh 9 skenario alur kerja submisi editorial OJS.
     */
    public function run(): void
    {
        // 1. Inisialisasi User Penulis Utama
        $author = User::firstOrCreate(
            ['email' => 'andi.ilyas@unhas.ac.id'],
            [
                'username' => 'andi.ilyas',
                'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'full_name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Inisialisasi Jurnal Default SIRIDA
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            [
                'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'issn' => '2985-7821',
                'eissn' => '2985-7821',
                'status' => 'active',
                'version' => 1,
            ]
        );

        // 3. Pastikan Seksi Jurnal Tersedia
        $this->call(SectionSeeder::class);

        $sections = Section::where('journal_id', $journal->id)->get()->keyBy('slug');
        $secRiset = $sections->get('sec-riset-orisinil') ?? Section::first();
        $secKebijakan = $sections->get('sec-kajian-kebijakan') ?? $secRiset;
        $secInovasi = $sections->get('sec-studi-kasus') ?? $secRiset;
        $secLiteratur = $sections->get('sec-tinjauan-literatur') ?? $secRiset;
        $secKomunikasi = $sections->get('sec-komunikasi-pendek') ?? $secRiset;
        $secOpini = $sections->get('sec-opini-kebijakan') ?? $secRiset;

        // 4. Definisi 9 Skenario Lengkap Alur Kerja Editorial OJS
        $scenarios = [
            // Skenario 1: Draf Submisi
            [
                'number' => 'SUB-2026-00101',
                'status' => 'draft',
                'section_id' => $secInovasi->id,
                'title' => 'Inovasi Bank Sampah Digital Berbasis Komunitas di Kecamatan Mariso Kota Makassar',
                'abstract' => 'Penerapan platform pencatatan digital transaksi sampah anorganik skala rukun warga untuk meningkatkan partisipasi retribusi mandiri dan sirkularitas ekonomi lokal di Kota Makassar.',
                'keywords' => ['Bank Sampah', 'Ekonomi Sirkular', 'Kecamatan Mariso', 'Inovasi Komunitas', 'Smart Environment'],
                'subject_area' => 'Tata Kelola Persampahan, Lingkungan Hidup, & Inovasi Daerah',
                'references' => "1. Kementerian LHK. (2024). Pedoman Pengelolaan Sampah Berbasis Komunitas.\n2. Dinas Lingkungan Hidup Makassar. (2025). Profil TPA Tamangapa.",
                'comments_for_editor' => null,
                'submitted_at' => null,
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                ],
                'files' => [],
            ],

            // Skenario 2: Pemeriksaan Awal (Submitted / Desk Review)
            [
                'number' => 'SUB-2026-00102',
                'status' => 'submitted',
                'section_id' => $secKebijakan->id,
                'title' => 'Analisis Efektivitas Insentif Pajak Bumi dan Bangunan dalam Mendorong Kepatuhan Wajib Pajak di Kota Makassar',
                'abstract' => 'Penelitian ini mengevaluasi dampak kebijakan relaksasi denda administratif PBB-P2 terhadap tingkat kepatuhan formal dan material wajib pajak perorangan di wilayah Kota Makassar kurun waktu 2023–2025.',
                'keywords' => ['Pajak Daerah', 'Kepatuhan Wajib Pajak', 'Insentif Fiskal', 'Pendapatan Asli Daerah'],
                'subject_area' => 'Kebijakan Fiskal Daerah & Administrasi Keuangan Publik',
                'references' => "1. Bapenda Kota Makassar. (2025). Laporan Realisasi Penerimaan PBB-P2.\n2. Mardiasmo. (2021). Perpajakan Edisi Revisi. Penerbit Andi.",
                'comments_for_editor' => 'Naskah ini diserahkan untuk dipertimbangkan terbit pada edisi kebijakan strategis BRIDA.',
                'submitted_at' => now()->subDays(2),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                    [
                        'given_name' => 'Dra. Hj.',
                        'family_name' => 'Nurhayati, M.Si.',
                        'email' => 'nurhayati@makassar.go.id',
                        'affiliation' => 'Badan Pendapatan Daerah (Bapenda) Kota Makassar',
                        'orcid' => '0000-0003-1290-7714',
                        'is_corresponding' => false,
                        'position' => 2,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-PBB-P2-Makassar-Ilyas.docx',
                        'genre' => 'manuscript',
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'size' => 1845120,
                    ],
                ],
            ],

            // Skenario 3: Proses Telaah (In Review - Double-Blind)
            [
                'number' => 'SUB-2026-00103',
                'status' => 'in_review',
                'section_id' => $secRiset->id,
                'title' => 'Pemodelan Spasial Bahaya Banjir Rob dan Kerentanan Infrastruktur Pesisir Kota Makassar Berbasis GIS dan Sentinel-1',
                'abstract' => 'Penelitian ini memetakan zona risiko banjir rob di kawasan pesisir Kota Makassar dengan memanfaatkan data Synthetic Aperture Radar (SAR) satelit Sentinel-1 dipadukan model elevasi digital resolusi tinggi.',
                'keywords' => ['Banjir Rob', 'Sentinel-1 SAR', 'GIS Pesisir', 'Kerentanan Infrastruktur', 'Mitigasi Bencana'],
                'subject_area' => 'Teknologi Geospasial, Mitigasi Bencana, & Wilayah Pesisir',
                'references' => "1. BMKG Wilayah IV Makassar. (2025). Rekapitulasi Pasang Surut Air Laut.\n2. Copernicus Open Access Hub. (2026). Sentinel-1 SAR GRD Dataset.",
                'comments_for_editor' => 'Seluruh data spasial telah diverifikasi ground-check lapangan.',
                'submitted_at' => now()->subDays(12),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                    [
                        'given_name' => 'Ir.',
                        'family_name' => 'Farhan Syahputra, M.T.',
                        'email' => 'f.syahputra@unhas.ac.id',
                        'affiliation' => 'Departemen Teknik Geologi, Universitas Hasanuddin',
                        'orcid' => '0000-0001-8899-2310',
                        'is_corresponding' => false,
                        'position' => 2,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-Anonim-Banjir-Rob-Makassar.pdf',
                        'genre' => 'manuscript',
                        'mime_type' => 'application/pdf',
                        'size' => 3617587,
                    ],
                    [
                        'name' => 'Dataset-Shapefile-Pesisir-Makassar.zip',
                        'genre' => 'supplementary',
                        'mime_type' => 'application/zip',
                        'size' => 8421376,
                    ],
                ],
            ],

            // Skenario 4: Perlu Tindakan Penulis - Revisi Minor (Revisions Requested)
            [
                'number' => 'SUB-2026-00104',
                'status' => 'revisions_requested',
                'section_id' => $secRiset->id,
                'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Pemberdayaan Ekonomi Kreatif di Kota Makassar',
                'abstract' => 'Penelitian ini bertujuan untuk mengevaluasi efektivitas program Lorong Wisata dalam meningkatkan pendapatan masyarakat berpenghasilan rendah dan memperkuat ketahanan ekonomi lokal di Kota Makassar menggunakan pendekatan mixed-methods.',
                'keywords' => ['Lorong Wisata', 'Ekonomi Kreatif', 'Kebijakan Publik Makassar', 'Pemberdayaan Masyarakat'],
                'subject_area' => 'Kebijakan Publik, Pemberdayaan Sosial, & Ekonomi Kreatif',
                'references' => "1. Kementerian PPN/Bappenas. (2024). Peta Jalan Pengembangan Ekonomi Kreatif Perkotaan.\n2. Pemerintah Kota Makassar. (2023). Peraturan Walikota tentang Lorong Wisata.",
                'comments_for_editor' => '1. Mitra Bestari mengapresiasi sampel survei lapangan yang komprehensif, namun meminta ketajaman analisis statistik inferensial pada sub-bab 4.2.<br>2. Perbaiki konsistensi sitasi kepustakaan 5 tahun terakhir (2021–2026).<br>3. Sertakan file surat tanggapan (Response to Reviewers Letter) bersama naskah perbaikan berformat track-changes.',
                'submitted_at' => now()->subDays(20),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                    [
                        'given_name' => 'Rahmat',
                        'family_name' => 'Hidayat, S.STP., M.Pub.Pol.',
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
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'size' => 1929380,
                    ],
                    [
                        'name' => 'Tabel-Kuesioner-dan-Data-Responden-240-KK.xlsx',
                        'genre' => 'supplementary',
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'size' => 626688,
                    ],
                ],
            ],

            // Skenario 5: Perlu Tindakan Penulis - Revisi Mayor (Revisions Requested - Major)
            [
                'number' => 'SUB-2026-00105',
                'status' => 'revisions_requested',
                'section_id' => $secLiteratur->id,
                'title' => 'Tinjauan Sistematis Integrasi Data Kependudukan dan Pelayanan Sosial Perkotaan Menggunakan Protokol PRISMA',
                'abstract' => 'Sintesis literatur komprehensif mengkaji interoperabilitas sistem identitas digital daerah dengan basis data kesejahteraan sosial nasional.',
                'keywords' => ['Protokol PRISMA', 'Data Kependudukan', 'Pelayanan Sosial', 'Interoperabilitas', 'Smart City'],
                'subject_area' => 'Sistem Informasi Publik & Tata Kelola Data Perkotaan',
                'references' => "1. Page, M. J., et al. (2021). The PRISMA 2020 statement: an updated guideline for reporting systematic reviews. BMJ.\n2. Disdukcapil Makassar. (2025). Integrasi Layanan Kependudukan Satu Pintu.",
                'comments_for_editor' => 'Keputusan: Revisi Mayor (Major Revisions Required). Mitra Bestari meminta penambahan basis data rujukan internasional (Scopus & Web of Science) serta tabel sintesis risiko bias kajian inklusi.',
                'submitted_at' => now()->subDays(25),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                    [
                        'given_name' => 'Siti',
                        'family_name' => 'Aminah, S.Kom., M.T.',
                        'email' => 'siti.aminah@unhas.ac.id',
                        'affiliation' => 'Departemen Ilmu Komputer, Universitas Hasanuddin',
                        'orcid' => '0000-0002-9918-4451',
                        'is_corresponding' => false,
                        'position' => 2,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Manuskrip-PRISMA-Integrasi-Data-Kependudukan.pdf',
                        'genre' => 'manuscript',
                        'mime_type' => 'application/pdf',
                        'size' => 2415120,
                    ],
                ],
            ],

            // Skenario 6: Naskah Diterima & Tahap Produksi / Pemeriksaan Galai (In Production / Accepted)
            [
                'number' => 'SUB-2026-00106',
                'status' => 'in_production',
                'section_id' => $secInovasi->id,
                'title' => 'Penerapan Sensor IoT dan Telemetri Kualitas Udara Real-Time pada Kawasan Industri Biringkanaya Makassar',
                'abstract' => 'Peningkatan konsentrasi polutan partikulat PM2.5 di kawasan industri Biringkanaya menuntut sistem pemantauan kontinu berbiaya efisien melalui jaringan sensor LoRaWAN.',
                'keywords' => ['Kualitas Udara', 'IoT Sensor', 'Wilayah Industri Biringkanaya', 'Machine Learning', 'Smart Environment'],
                'subject_area' => 'Teknologi Informasi, Lingkungan Hidup, & IoT Smart City',
                'references' => "1. WHO. (2023). Air quality guidelines for particulate matter.\n2. Dinas Lingkungan Hidup Kota Makassar. (2025). Stasiun Pemantau Udara.",
                'comments_for_editor' => 'Naskah telah lolos revisi dan diterima untuk penerbitan Volume 3 Nomor 2.',
                'submitted_at' => now()->subDays(45),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
                        'email' => 'andi.ilyas@unhas.ac.id',
                        'affiliation' => 'Universitas Hasanuddin',
                        'orcid' => '0000-0002-1825-0097',
                        'is_corresponding' => true,
                        'position' => 1,
                    ],
                    [
                        'given_name' => 'Budi',
                        'family_name' => 'Santoso, S.T., M.Eng.',
                        'email' => 'budi.santoso@brida.makassar.go.id',
                        'affiliation' => 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                        'orcid' => '0000-0001-7788-9900',
                        'is_corresponding' => false,
                        'position' => 2,
                    ],
                ],
                'files' => [
                    [
                        'name' => 'Galley-Proof-Proofreading-Draft-SUB-00106.pdf',
                        'genre' => 'galley',
                        'mime_type' => 'application/pdf',
                        'size' => 3271557,
                    ],
                    [
                        'name' => 'Manuskrip-Revisi-Final-Disetujui.docx',
                        'genre' => 'manuscript',
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'size' => 2187450,
                    ],
                ],
            ],

            // Skenario 7: Telah Terbit Resmi (Published / Open Access dengan DOI)
            [
                'number' => 'SUB-2026-00107',
                'status' => 'published',
                'section_id' => $secRiset->id,
                'title' => 'Strategi Transformasi Digital Birokrasi Pelayanan Publik Terpadu Pemerintah Daerah Kota Makassar',
                'abstract' => 'Penerapan konsep Smart Governance di Kota Makassar mendorong integrasi layanan perizinan dan kependudukan melalui satu pintu digital. Kajian longitudinal ini mengevaluasi adopsi platform digital terpadu oleh masyarakat dan ASN selama kurun waktu 2023–2025.',
                'keywords' => ['Transformasi Digital', 'Pelayanan Publik', 'Birokrasi Makassar', 'Smart Governance'],
                'subject_area' => 'Reformasi Birokrasi, Administrasi Publik, & Transformasi Digital',
                'references' => "1. Osborne, D., & Gaebler, T. (1992). Reinventing Government.\n2. Pemkot Makassar. (2024). Laporan Kinerja Instansi Pemerintah (LKjIP).",
                'comments_for_editor' => 'Artikel terbit resmi pada Volume 2 Nomor 2 (2025) halaman 104-118 dengan DOI 10.55981/brida.v2i2.88.',
                'submitted_at' => now()->subMonths(6),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
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
                        'mime_type' => 'application/pdf',
                        'size' => 1761607,
                    ],
                ],
            ],

            // Skenario 8: Naskah Ditolak Redaksi (Declined / Desk Reject)
            [
                'number' => 'SUB-2026-00108',
                'status' => 'declined',
                'section_id' => $secOpini->id,
                'title' => 'Analisis Makroekonomi dan Dampak Fluktuasi Nilai Tukar terhadap APBD Kota Makassar Periode 2020-2024',
                'abstract' => 'Kajian ekonometrika transmisi guncangan kurs valuta asing terhadap postur belanja dan penerimaan daerah Kota Makassar.',
                'keywords' => ['Kebijakan Fiskal', 'APBD Makassar', 'Ekonometrika', 'Makroekonomi Daerah'],
                'subject_area' => 'Ekonomi Moneter & Keuangan Daerah',
                'references' => '1. Bank Indonesia. (2024). Laporan Perekonomian Provinsi Sulawesi Selatan.',
                'comments_for_editor' => 'Berdasarkan telaah dewan redaksi, topik makroekonomi moneter nasional yang disajikan berada di luar fokus utama jurnal SIRIDA yang berorientasi pada inovasi terapan kebijakan daerah Kota Makassar.',
                'submitted_at' => now()->subDays(15),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
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
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'size' => 1174405,
                    ],
                ],
            ],

            // Skenario 9: Naskah Diarsipkan Resmi (Archived)
            [
                'number' => 'SUB-2026-00109',
                'status' => 'archived',
                'section_id' => $secKomunikasi->id,
                'title' => 'Observasi Awal Distribusi Mikroplastik pada Sedimen Muara Sungai Jeneberang Makassar',
                'abstract' => 'Kajian eksploratif keberadaan partikel mikroplastik pada zona estuari muara Sungai Jeneberang Kota Makassar.',
                'keywords' => ['Mikroplastik', 'Muara Jeneberang', 'Pencemaran Pesisir', 'Lingkungan Hidup'],
                'subject_area' => 'Sains Lingkungan & Ekologi Perairan',
                'references' => '1. KLHK RI. (2024). Panduan Baku Mutu Mikroplastik Sedimen Muara.',
                'comments_for_editor' => 'Penulis mengajukan permohonan penarikan resmi karena sedang mengembangkan riset kolaboratif multi-pusat.',
                'submitted_at' => now()->subDays(28),
                'contributors' => [
                    [
                        'given_name' => 'Dr. Andi Muhammad',
                        'family_name' => 'Ilyas, M.Si.',
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
                        'mime_type' => 'application/pdf',
                        'size' => 1939865,
                    ],
                ],
            ],
        ];

        // 5. Eksekusi Penyimpanan Idempoten ke Database
        foreach ($scenarios as $item) {
            $submission = Submission::firstOrNew(['submission_number' => $item['number']]);
            $submission->journal_id = $journal->id;
            $submission->section_id = $item['section_id'];
            $submission->author_id = $author->id;
            $submission->title = $item['title'];
            $submission->abstract = $item['abstract'];
            $submission->keywords = $item['keywords'];
            $submission->subject_area = $item['subject_area'];
            $submission->references = $item['references'];
            $submission->comments_for_editor = $item['comments_for_editor'];
            $submission->status = $item['status'];
            $submission->guideline_acceptance = [
                'accepted' => true,
                'timestamp' => now()->toIso8601String(),
                'ip_address' => '127.0.0.1',
            ];
            $submission->submitted_at = $item['submitted_at'];
            $submission->version = 1;
            $submission->save();

            // Refresh kontributor
            SubmissionContributor::where('submission_id', $submission->id)->delete();
            foreach ($item['contributors'] as $c) {
                SubmissionContributor::create([
                    'submission_id' => $submission->id,
                    'user_id' => $c['is_corresponding'] ? $author->id : null,
                    'given_name' => $c['given_name'],
                    'family_name' => $c['family_name'],
                    'email' => $c['email'],
                    'affiliation' => $c['affiliation'],
                    'orcid' => $c['orcid'],
                    'is_corresponding' => $c['is_corresponding'],
                    'position' => $c['position'],
                ]);
            }

            // Simpan Berkas Terkait jika ada
            if (! empty($item['files'])) {
                // Hapus asosiasi lama jika ada
                SubmissionFile::where('submission_id', $submission->id)->delete();

                foreach ($item['files'] as $f) {
                    $fileEntry = FileEntry::create([
                        'storage_disk' => 'local',
                        'storage_path' => 'manuscripts/'.md5($f['name']).'.dat',
                        'original_name' => $f['name'],
                        'mime_type' => $f['mime_type'],
                        'byte_size' => $f['size'],
                        'checksum_sha256' => hash('sha256', $f['name']),
                        'virus_scan_status' => 'clean',
                        'created_by' => $author->id,
                    ]);

                    SubmissionFile::create([
                        'submission_id' => $submission->id,
                        'file_id' => $fileEntry->id,
                        'genre' => $f['genre'],
                        'version' => 1,
                    ]);
                }
            }
        }
    }
}
