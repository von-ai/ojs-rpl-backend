<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Copyedit;

class CopyeditorSubmissionDetailViewModel
{
    private string $submissionId;

    public function __construct(string $submissionId = 'SUB-2026-00106')
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Data lengkap naskah untuk ruang kerja Copy Editor.
     * Mengonsumsi relasi copyedits, copyedit_outputs, copyedit_approvals,
     * copyedit_approved_files, production_escalations, dan layout_assignments.
     *
     * @return array{
     *     id: string,
     *     number: string,
     *     assignment_code: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, email: string, affiliation: string},
     *     section_editor: string,
     *     layout_editor: string,
     *     assigned_at: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     is_completed: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     source_file: array{id: string, name: string, size_formatted: string, download_url: string},
     *     output_files: array<int, array{id: string, name: string, type: string, size_formatted: string, sha256: string, uploaded_at: string, download_url: string}>,
     *     approval: array{status: string, status_label: string, is_approved: bool, is_waiting: bool, has_changes_requested: bool, author_note: ?string, approved_at: ?string, invalidated_at: ?string},
     *     escalation: array{has_active: bool, is_resolved: bool, ticket_id: ?string, category: ?string, reason: ?string, status: ?string, resolution: ?string, resolved_at: ?string, created_at: ?string},
     *     handoff: array{is_ready: bool, is_completed: bool, assigned_layout_editor: string, notes_placeholder: string, submitted_notes: ?string, completed_at: ?string},
     *     notes: ?string,
     *     activity_timeline: array<int, array{title: string, actor: string, date: string, description: string, badge_class: string}>
     * }
     */
    public function submission(): array
    {
        $fromDb = $this->queryCopyedit();
        if ($fromDb) {
            return $this->formatCopyeditDetail($fromDb);
        }

        $dataset = $this->registry();

        if (isset($dataset[$this->submissionId])) {
            return $dataset[$this->submissionId];
        }

        // Default fallback to first submission
        return $dataset['SUB-2026-00106'];
    }

    /**
     * Kumpulan dataset realistis simulasi naskah yang mencerminkan seluruh siklus hidup naskah copyediting.
     *
     * @return array<string, array<string, mixed>>
     */
    private function registry(): array
    {
        return [
            // 1. Skenario Aktif / Menunggu Respon Penulis
            'SUB-2026-00106' => [
                'id' => 'SUB-2026-00106',
                'number' => 'SUB-2026-00106',
                'assignment_code' => '#CE-01JARK948X',
                'title' => 'Analisis Spasial Ruang Terbuka Hijau Terhadap Efek Urban Heat Island Kota Makassar',
                'section_title' => 'Inovasi Daerah & Kebijakan Publik',
                'corresponding_author' => [
                    'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                    'email' => 'nurfadillah.bahar@unhas.ac.id',
                    'affiliation' => 'Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'assigned_at' => '18 Okt 2026',
                'due_at' => '29 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'is_completed' => false,
                'status' => 'in_progress',
                'status_label' => 'Tahap Copyediting Aktif',
                'status_badge_class' => 'bg-sky-50 text-sky-800 border-sky-200',
                'source_file' => [
                    'id' => 'FILE-SRC-00106',
                    'name' => 'SUB2026-00106_Source_Accepted.docx',
                    'size_formatted' => '2.14 MB',
                    'download_url' => '#unduh-naskah-asli',
                ],
                'output_files' => [
                    [
                        'id' => 'FILE-01JARK948X-01',
                        'name' => 'SUB2026-00106_Copyedit_Final_Clean.docx',
                        'type' => 'Naskah Utama',
                        'size_formatted' => '2.05 MB',
                        'sha256' => 'a93b48f95c80dbb5a452ef32dc0e6e7890efcd12f451486791b40285a210b4f8',
                        'uploaded_at' => '24 Okt 2026 14:20 WITA',
                        'download_url' => '#unduh-file-suntingan',
                    ],
                    [
                        'id' => 'FILE-01JARK948X-02',
                        'name' => 'SUB2026-00106_Supplementary_Data.docx',
                        'type' => 'Lampiran Data',
                        'size_formatted' => '1.12 MB',
                        'sha256' => '7f83b1657ff1fc53b92dc18148a1d65dfc2d4b1fa3d677284addd200126d9069',
                        'uploaded_at' => '24 Okt 2026 14:22 WITA',
                        'download_url' => '#unduh-lampiran',
                    ],
                ],
                'approval' => [
                    'status' => 'requested',
                    'status_label' => 'Menunggu Respon Penulis',
                    'is_approved' => false,
                    'is_waiting' => true,
                    'has_changes_requested' => false,
                    'author_note' => null,
                    'approved_at' => null,
                    'invalidated_at' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'is_resolved' => false,
                    'ticket_id' => null,
                    'category' => null,
                    'reason' => null,
                    'status' => null,
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => null,
                ],
                'handoff' => [
                    'is_ready' => false,
                    'is_completed' => false,
                    'assigned_layout_editor' => 'Rahmat Hidayat, S.Kom.',
                    'notes_placeholder' => 'Contoh: Gunakan template 2 kolom, pastikan resolusi peta spasial 300 DPI.',
                    'submitted_notes' => null,
                    'completed_at' => null,
                ],
                'notes' => 'Penyuntingan fokus pada perataan istilah geospasial dan perbaikan format sitasi APA 7th.',
                'activity_timeline' => [
                    [
                        'title' => 'Permintaan Persetujuan Dikirim',
                        'actor' => 'Andi Kurniawan (Copy Editor)',
                        'date' => '24 Okt 2026, 14:30 WITA',
                        'description' => 'Mengirim notifikasi permintaan peninjauan berkas suntingan ke Dr. Nurfadillah Bahar.',
                        'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
                    ],
                    [
                        'title' => 'Berkas Suntingan Diunggah',
                        'actor' => 'Andi Kurniawan (Copy Editor)',
                        'date' => '24 Okt 2026, 14:20 WITA',
                        'description' => 'Mengunggah naskah utama versi suntingan (2.05 MB) dan lampiran data (1.12 MB).',
                        'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                    ],
                    [
                        'title' => 'Penugasan Copyediting Diterbitkan',
                        'actor' => 'Prof. Hamka, Ph.D. (Section Editor)',
                        'date' => '18 Okt 2026, 09:15 WITA',
                        'description' => 'Penugasan penyuntingan bahasa naskah resmi diserahkan ke Copy Editor.',
                        'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    ],
                ],
            ],

            // 2. Skenario Resolusi Mediasi Selesai Diputuskan Section Editor
            'SUB-2026-00107' => [
                'id' => 'SUB-2026-00107',
                'number' => 'SUB-2026-00107',
                'assignment_code' => '#CE-01JARK948W',
                'title' => 'Transformasi Digital Pelayanan Publik Kelurahan di Kota Makassar: Evaluasi Implementasi Aplikasi Lorong',
                'section_title' => 'Sosial Humaniora & Tata Kelola',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. Muhammad Basri, M.T.',
                    'email' => 'm.basri@poliupg.ac.id',
                    'affiliation' => 'Politeknik Negeri Ujung Pandang',
                ],
                'section_editor' => 'Dr. Hj. St. Rohani, M.Si.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'assigned_at' => '19 Okt 2026',
                'due_at' => '02 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'is_completed' => false,
                'status' => 'in_progress',
                'status_label' => 'Mediasi Selesai — Butuh Penyesuaian',
                'status_badge_class' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                'source_file' => [
                    'id' => 'FILE-SRC-00107',
                    'name' => 'SUB2026-00107_Source_Digital_Service.docx',
                    'size_formatted' => '1.95 MB',
                    'download_url' => '#unduh-naskah-asli',
                ],
                'output_files' => [
                    [
                        'id' => 'FILE-01JARK948W-01',
                        'name' => 'SUB2026-00107_Copyedit_Draft1.docx',
                        'type' => 'Naskah Utama',
                        'size_formatted' => '1.90 MB',
                        'sha256' => '99887766554433221100aabbccddeeff99887766554433221100aabbccddeeff',
                        'uploaded_at' => '22 Okt 2026 11:00 WITA',
                        'download_url' => '#unduh-file-suntingan',
                    ],
                ],
                'approval' => [
                    'status' => 'changes_requested',
                    'status_label' => 'Menunggu Perbaikan Berkas Pasca-Mediasi',
                    'is_approved' => false,
                    'is_waiting' => false,
                    'has_changes_requested' => true,
                    'author_note' => 'Penulis mengusulkan nama aplikasi tetap ditulis huruf kapital "APLIKASI LORONG" sesuai regulasi Walikota.',
                    'approved_at' => null,
                    'invalidated_at' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'is_resolved' => true,
                    'ticket_id' => 'ESC-2026-0039',
                    'category' => 'Penulisan Nama Produk Kebijakan',
                    'reason' => 'Perbedaan format penulisan nama program digital pemerintah daerah.',
                    'status' => 'resolved',
                    'resolution' => 'Section Editor memutuskan: Nama aplikasi ditulis "Aplikasi Lorong" (Title Case) pada teks naratif, dan huruf kapital penuh hanya pada kutipan langsung SK Walikota. Silakan Copy Editor perbarui naskah.',
                    'resolved_at' => '24 Okt 2026 15:00 WITA',
                    'created_at' => '23 Okt 2026 09:30 WITA',
                ],
                'handoff' => [
                    'is_ready' => false,
                    'is_completed' => false,
                    'assigned_layout_editor' => 'Rahmat Hidayat, S.Kom.',
                    'notes_placeholder' => 'Contoh: Gunakan template 2 kolom.',
                    'submitted_notes' => null,
                    'completed_at' => null,
                ],
                'notes' => 'Menunggu unggahan revisi baru sesuai keputusan mediasi Dr. Hj. St. Rohani.',
                'activity_timeline' => [
                    [
                        'title' => 'Keputusan Mediasi Diterbitkan',
                        'actor' => 'Dr. Hj. St. Rohani, M.Si. (Section Editor)',
                        'date' => '24 Okt 2026, 15:00 WITA',
                        'description' => 'Menerbitkan arahan penyelesaian nama program Aplikasi Lorong.',
                        'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    ],
                    [
                        'title' => 'Tiket Mediasi Dibuka',
                        'actor' => 'Andi Kurniawan (Copy Editor)',
                        'date' => '23 Okt 2026, 09:30 WITA',
                        'description' => 'Membuka tiket mediasi #ESC-2026-0039.',
                        'badge_class' => 'bg-red-50 text-red-700 border-red-200',
                    ],
                ],
            ],

            // 3. Skenario Eskalasi Mediasi Aktif (Sedang Ditelaah SE)
            'SUB-2026-00108' => [
                'id' => 'SUB-2026-00108',
                'number' => 'SUB-2026-00108',
                'assignment_code' => '#CE-01JARK948Z',
                'title' => 'Kajian Karakteristik Geoteknik Sedimen Pesisir Pantai Losari untuk Mitigasi Erosi Gelombang Pasang',
                'section_title' => 'Teknologi & Infrastruktur Berkelanjutan',
                'corresponding_author' => [
                    'name' => 'Dr. Eng. Ir. Faisal Syarif, S.T., M.Eng.',
                    'email' => 'faisal.syarif@unhas.ac.id',
                    'affiliation' => 'Fakultas Teknik Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'assigned_at' => '20 Okt 2026',
                'due_at' => '30 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'is_completed' => false,
                'status' => 'escalated',
                'status_label' => 'Dalam Mediasi Redaksi',
                'status_badge_class' => 'bg-red-50 text-red-700 border-red-200',
                'source_file' => [
                    'id' => 'FILE-SRC-00108',
                    'name' => 'SUB2026-00108_Accepted_Geoteknik.docx',
                    'size_formatted' => '3.40 MB',
                    'download_url' => '#unduh-naskah-asli',
                ],
                'output_files' => [
                    [
                        'id' => 'FILE-01JARK948Z-01',
                        'name' => 'SUB2026-00108_Copyedit_Geoteknik_v1.docx',
                        'type' => 'Naskah Utama',
                        'size_formatted' => '3.25 MB',
                        'sha256' => 'b1a2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2',
                        'uploaded_at' => '23 Okt 2026 10:15 WITA',
                        'download_url' => '#unduh-file-suntingan',
                    ],
                ],
                'approval' => [
                    'status' => 'changes_requested',
                    'status_label' => 'Penulis Meminta Perubahan',
                    'is_approved' => false,
                    'is_waiting' => false,
                    'has_changes_requested' => true,
                    'author_note' => 'Penulis meminta penyesuaian istilah "Urban Canopy Layer" dan pemodelan hidrologi pada Bab 3 agar tetap mengacu standar teknis WMO 2023 tanpa diubah menjadi istilah bahasa awam.',
                    'approved_at' => null,
                    'invalidated_at' => null,
                ],
                'escalation' => [
                    'has_active' => true,
                    'is_resolved' => false,
                    'ticket_id' => 'ESC-2026-0042',
                    'category' => 'Ketidaksesuaian Terminologi Teknis',
                    'reason' => 'Perbedaan pandangan istilah geoteknik pesisir antara kaidah bahasa baku jurnal dan standar internasional WMO 2023.',
                    'status' => 'Menunggu Mediasi Section Editor',
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => '24 Okt 2026 11:15 WITA',
                ],
                'handoff' => [
                    'is_ready' => false,
                    'is_completed' => false,
                    'assigned_layout_editor' => 'Rahmat Hidayat, S.Kom.',
                    'notes_placeholder' => 'Contoh: Perhatikan tabel matriks butir sedimen.',
                    'submitted_notes' => null,
                    'completed_at' => null,
                ],
                'notes' => 'Alur dijeda sementara menunggu pandangan Section Editor Prof. Hamka.',
                'activity_timeline' => [
                    [
                        'title' => 'Tiket Mediasi Diterbitkan',
                        'actor' => 'Andi Kurniawan (Copy Editor)',
                        'date' => '24 Okt 2026, 11:15 WITA',
                        'description' => 'Menerbitkan tiket eskalasi #ESC-2026-0042 ke Section Editor Prof. Hamka.',
                        'badge_class' => 'bg-red-50 text-red-700 border-red-200',
                    ],
                    [
                        'title' => 'Catatan Revisi dari Penulis',
                        'actor' => 'Dr. Eng. Ir. Faisal Syarif',
                        'date' => '23 Okt 2026, 16:40 WITA',
                        'description' => 'Penulis mengajukan keberatan terhadap penyederhanaan istilah teknis Bab 3.',
                        'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    ],
                ],
            ],

            // 4. Skenario Telah Disetujui Penulis — Siap Serah Terima ke Layout
            'SUB-2026-00109' => [
                'id' => 'SUB-2026-00109',
                'number' => 'SUB-2026-00109',
                'assignment_code' => '#CE-01JARK948Y',
                'title' => 'Efektivitas Program Lorong Wisata dalam Peningkatan Pendapatan Rumah Tangga Berpenghasilan Rendah',
                'section_title' => 'Ekonomi Pembangunan & Pemberdayaan Masyarakat',
                'corresponding_author' => [
                    'name' => 'Hj. Fatmawati, S.E., M.Si.',
                    'email' => 'fatmawati@unifa.ac.id',
                    'affiliation' => 'Universitas Fajar Makassar',
                ],
                'section_editor' => 'Dr. Hj. St. Rohani, M.Si.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'assigned_at' => '22 Okt 2026',
                'due_at' => '05 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'is_completed' => false,
                'status' => 'approved',
                'status_label' => 'Disetujui Penulis — Siap Serah Terima',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'source_file' => [
                    'id' => 'FILE-SRC-00109',
                    'name' => 'SUB2026-00109_Source_Ekonomi.docx',
                    'size_formatted' => '1.80 MB',
                    'download_url' => '#unduh-naskah-asli',
                ],
                'output_files' => [
                    [
                        'id' => 'FILE-01JARK948Y-01',
                        'name' => 'SUB2026-00109_Copyedit_Final.docx',
                        'type' => 'Naskah Utama',
                        'size_formatted' => '1.75 MB',
                        'sha256' => 'c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4',
                        'uploaded_at' => '23 Okt 2026 09:00 WITA',
                        'download_url' => '#unduh-file-suntingan',
                    ],
                ],
                'approval' => [
                    'status' => 'approved',
                    'status_label' => 'Disetujui Penulis',
                    'is_approved' => true,
                    'is_waiting' => false,
                    'has_changes_requested' => false,
                    'author_note' => 'Seluruh penyuntingan tata bahasa dan sitasi telah kami periksa dan kami setujui sepenuhnya tanpa perubahan tambahan.',
                    'approved_at' => '24 Okt 2026 10:00 WITA',
                    'invalidated_at' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'is_resolved' => false,
                    'ticket_id' => null,
                    'category' => null,
                    'reason' => null,
                    'status' => null,
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => null,
                ],
                'handoff' => [
                    'is_ready' => true,
                    'is_completed' => false,
                    'assigned_layout_editor' => 'Rahmat Hidayat, S.Kom.',
                    'notes_placeholder' => 'Contoh: Gunakan template 2 kolom, perhatikan bagan diagram alur program lorong wisata.',
                    'submitted_notes' => null,
                    'completed_at' => null,
                ],
                'notes' => 'Siap diserahterimakan ke tim tata letak.',
                'activity_timeline' => [
                    [
                        'title' => 'Persetujuan Diterima dari Penulis',
                        'actor' => 'Hj. Fatmawati, S.E., M.Si.',
                        'date' => '24 Okt 2026, 10:00 WITA',
                        'description' => 'Penulis menyetujui seluruh berkas hasil suntingan dan checksum SHA-256 dibekukan.',
                        'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    ],
                    [
                        'title' => 'Berkas Suntingan Diunggah',
                        'actor' => 'Andi Kurniawan (Copy Editor)',
                        'date' => '23 Okt 2026, 09:00 WITA',
                        'description' => 'Mengunggah naskah final bersih untuk ditinjau penulis.',
                        'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                    ],
                ],
            ],

            // 5. Skenario Selesai & Diserahkan (Mode Arsip / Read-Only Archive)
            'SUB-2026-00110' => [
                'id' => 'SUB-2026-00110',
                'number' => 'SUB-2026-00110',
                'assignment_code' => '#CE-01JARK948T',
                'title' => 'Strategi Pengelolaan Sampah Berbasis Komunitas di Kawasan Pesisir Tallo',
                'section_title' => 'Lingkungan & Ketahanan Kota',
                'corresponding_author' => [
                    'name' => 'Dr. Muh. Ridwan, S.Kel., M.Si.',
                    'email' => 'm.ridwan@unhas.ac.id',
                    'affiliation' => 'Pusat Riset Kebencanaan BRIDA Makassar',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'assigned_at' => '10 Okt 2026',
                'due_at' => '22 Okt 2026 23:59 WITA',
                'is_urgent' => false,
                'is_completed' => true,
                'status' => 'completed',
                'status_label' => 'Selesai — Telah Diserahkan ke Layout',
                'status_badge_class' => 'bg-gray-100 text-gray-800 border-gray-300',
                'source_file' => [
                    'id' => 'FILE-SRC-00110',
                    'name' => 'SUB2026-00110_Accepted_Sampah_Tallo.docx',
                    'size_formatted' => '2.50 MB',
                    'download_url' => '#unduh-naskah-asli',
                ],
                'output_files' => [
                    [
                        'id' => 'FILE-01JARK948T-01',
                        'name' => 'SUB2026-00110_Copyedit_Final_Approved.docx',
                        'type' => 'Naskah Utama',
                        'size_formatted' => '2.42 MB',
                        'sha256' => 'aabbccddeeff00112233445566778899aabbccddeeff00112233445566778899',
                        'uploaded_at' => '21 Okt 2026 14:00 WITA',
                        'download_url' => '#unduh-file-suntingan',
                    ],
                ],
                'approval' => [
                    'status' => 'approved',
                    'status_label' => 'Telah Disetujui Penulis',
                    'is_approved' => true,
                    'is_waiting' => false,
                    'has_changes_requested' => false,
                    'author_note' => 'Disetujui tanpa koreksi.',
                    'approved_at' => '22 Okt 2026 11:30 WITA',
                    'invalidated_at' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'is_resolved' => false,
                    'ticket_id' => null,
                    'category' => null,
                    'reason' => null,
                    'status' => null,
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => null,
                ],
                'handoff' => [
                    'is_ready' => false,
                    'is_completed' => true,
                    'assigned_layout_editor' => 'Rahmat Hidayat, S.Kom.',
                    'notes_placeholder' => '',
                    'submitted_notes' => 'Template 2 kolom, perhatikan gambar peta sebaran tempat pembuangan sampah di Tallo pada Bab 4 agar resolusi minimal 300 DPI.',
                    'completed_at' => '22 Okt 2026, 16:45 WITA',
                ],
                'notes' => 'Serah terima galai tuntas.',
                'activity_timeline' => [
                    [
                        'title' => 'Serah Terima Selesai',
                        'actor' => 'Andi Kurniawan (Copy Editor)',
                        'date' => '22 Okt 2026, 16:45 WITA',
                        'description' => 'Naskah resmi diteruskan ke Layout Editor Rahmat Hidayat, S.Kom.',
                        'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    ],
                    [
                        'title' => 'Persetujuan Penulis Diterima',
                        'actor' => 'Dr. Muh. Ridwan, S.Kel., M.Si.',
                        'date' => '22 Okt 2026, 11:30 WITA',
                        'description' => 'Persetujuan formal diperoleh dan berkas dikunci.',
                        'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    ],
                ],
            ],
        ];
    }

    private function queryCopyedit(): ?Copyedit
    {
        try {
            if (! class_exists(Copyedit::class)) {
                return null;
            }

            return Copyedit::with([
                'submission.section',
                'submission.journal',
                'submission.author',
                'submission.contributors',
                'submission.activeLayoutAssignment.layoutEditor',
                'sourceFile',
                'outputs.file',
                'approvals.approvedFiles',
                'approvals.author',
                'escalations.openedBy',
            ])
            ->where('id', $this->submissionId)
            ->orWhereHas('submission', function ($q) {
                $q->where('submission_number', $this->submissionId)
                  ->orWhere('id', $this->submissionId);
            })
            ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCopyeditDetail(Copyedit $copyedit): array
    {
        $submission = $copyedit->submission;
        $activeEscalation = $copyedit->escalations->first(fn ($e) => in_array($e->status, ['open', 'investigating'], true));
        $resolvedEscalation = $copyedit->escalations->first(fn ($e) => $e->status === 'resolved');
        $latestEscalation = $copyedit->escalations->first();

        $latestApproval = $copyedit->approvals->first(fn ($a) => is_null($a->invalidated_at))
            ?? $copyedit->approvals->first();

        $approvalStatus = 'not_requested';
        $approvalStatusLabel = 'Belum Diminta';

        if ($latestApproval) {
            if ($latestApproval->invalidated_at !== null) {
                $approvalStatus = 'invalidated';
                $approvalStatusLabel = 'Persetujuan Dibatalkan (Ada Berkas Baru)';
            } elseif ($latestApproval->decision === 'approved') {
                $approvalStatus = 'approved';
                $approvalStatusLabel = 'Telah Disetujui Penulis';
            } elseif ($latestApproval->decision === 'revisions_requested') {
                $approvalStatus = 'changes_requested';
                $approvalStatusLabel = 'Penulis Meminta Perbaikan';
            } elseif ($latestApproval->decision === 'requested') {
                $approvalStatus = 'requested';
                $approvalStatusLabel = 'Menunggu Respon Penulis';
            }
        }

        $isApproved = $approvalStatus === 'approved';
        $isWaiting = $approvalStatus === 'requested';
        $hasChangesRequested = $approvalStatus === 'changes_requested';

        $outputFiles = [];
        foreach ($copyedit->outputs as $output) {
            $file = $output->file;
            if (! $file) {
                continue;
            }
            $isSupplementary = str_contains(mb_strtolower($file->original_name), 'lampiran')
                || str_contains(mb_strtolower($file->original_name), 'supplementary');

            $outputFiles[] = [
                'id' => $file->id,
                'name' => $file->original_name,
                'type' => $isSupplementary ? 'Lampiran Data' : 'Naskah Utama',
                'size_formatted' => $this->formatBytes((int) $file->byte_size),
                'sha256' => $file->checksum_sha256 ?? '',
                'uploaded_at' => $file->created_at ? $file->created_at->format('d M Y H:i \W\I\T\A') : now()->format('d M Y H:i \W\I\T\A'),
                'download_url' => '#unduh-file-suntingan',
            ];
        }

        $handoffReady = $isApproved && ! $activeEscalation && count($outputFiles) > 0;

        $status = 'in_progress';
        $statusLabel = 'Tahap Copyediting Aktif';
        $statusBadgeClass = 'bg-sky-50 text-sky-800 border-sky-200';

        if ($copyedit->status === 'completed') {
            $status = 'completed';
            $statusLabel = 'Selesai — Telah Diserahkan ke Layout';
            $statusBadgeClass = 'bg-gray-100 text-gray-800 border-gray-300';
        } elseif ($activeEscalation) {
            $status = 'escalated';
            $statusLabel = 'Dalam Mediasi Redaksi';
            $statusBadgeClass = 'bg-red-50 text-red-700 border-red-200';
        } elseif ($isApproved) {
            $status = 'approved';
            $statusLabel = 'Disetujui Penulis';
            $statusBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        } elseif ($isWaiting) {
            $status = 'awaiting_author_approval';
            $statusLabel = 'Menunggu Persetujuan Penulis';
            $statusBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
        }

        $corresponding = $submission?->contributors?->firstWhere('is_corresponding', true);
        $authorName = $corresponding?->full_name ?? $submission?->author?->name ?? 'Dr. Nurfadillah Bahar, M.Kes.';
        $authorEmail = $corresponding?->email ?? $submission?->author?->email ?? 'author@journal.org';
        $authorAffiliation = $corresponding?->affiliation ?? 'Universitas Hasanuddin';

        $layoutEditorName = $submission?->activeLayoutAssignment?->layoutEditor?->name ?? 'Rahmat Hidayat, S.Kom.';

        $timeline = [];
        if ($copyedit->status === 'completed') {
            $timeline[] = [
                'title' => 'Serah Terima Selesai',
                'actor' => $copyedit->copyEditor?->name ?? 'Copy Editor',
                'date' => $copyedit->updated_at ? $copyedit->updated_at->format('d M Y, H:i \W\I\T\A') : now()->format('d M Y, H:i \W\I\T\A'),
                'description' => "Naskah resmi diserahkan ke Layout Editor {$layoutEditorName}.",
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        }
        if ($isApproved) {
            $timeline[] = [
                'title' => 'Persetujuan Penulis Diterima',
                'actor' => $authorName,
                'date' => $latestApproval?->approved_at ? $latestApproval->approved_at->format('d M Y, H:i \W\I\T\A') : now()->format('d M Y, H:i \W\I\T\A'),
                'description' => 'Penulis menyetujui seluruh berkas hasil suntingan dan checksum SHA-256 dibekukan.',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        }
        if (count($outputFiles) > 0) {
            $timeline[] = [
                'title' => 'Berkas Suntingan Diunggah',
                'actor' => $copyedit->copyEditor?->name ?? 'Copy Editor',
                'date' => $copyedit->created_at ? $copyedit->created_at->format('d M Y, H:i \W\I\T\A') : now()->format('d M Y, H:i \W\I\T\A'),
                'description' => 'Mengunggah naskah hasil perbaikan tata bahasa dan sitasi.',
                'badge_class' => 'bg-sky-50 text-sky-700 border-sky-200',
            ];
        }

        return [
            'id' => $submission?->submission_number ?: $copyedit->id,
            'number' => $submission?->submission_number ?: $copyedit->id,
            'assignment_code' => '#CE-'.strtoupper(substr($copyedit->id, 0, 10)),
            'title' => $submission?->title ?? 'Naskah Ilmiah',
            'section_title' => $submission?->section?->name ?? 'Inovasi Daerah & Kebijakan Publik',
            'corresponding_author' => [
                'name' => $authorName,
                'email' => $authorEmail,
                'affiliation' => $authorAffiliation,
            ],
            'section_editor' => 'Prof. Hamka, Ph.D.',
            'layout_editor' => $layoutEditorName,
            'assigned_at' => $copyedit->created_at ? $copyedit->created_at->format('d M Y') : now()->format('d M Y'),
            'due_at' => $copyedit->due_at ? $copyedit->due_at->format('d M Y 23:59 \W\I\T\A') : now()->format('d M Y 23:59 \W\I\T\A'),
            'is_urgent' => $copyedit->due_at ? $copyedit->due_at->diffInDays(now()) <= 3 && $copyedit->status !== 'completed' : false,
            'is_completed' => $copyedit->status === 'completed',
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'source_file' => [
                'id' => $copyedit->sourceFile?->id ?? 'FILE-SRC-DEFAULT',
                'name' => $copyedit->sourceFile?->original_name ?? 'Source_Accepted.docx',
                'size_formatted' => $this->formatBytes((int) ($copyedit->sourceFile?->byte_size ?? 0)),
                'download_url' => '#unduh-naskah-asli',
            ],
            'output_files' => $outputFiles,
            'approval' => [
                'status' => $approvalStatus,
                'status_label' => $approvalStatusLabel,
                'is_approved' => $isApproved,
                'is_waiting' => $isWaiting,
                'has_changes_requested' => $hasChangesRequested,
                'author_note' => $latestApproval?->reason ?? $latestApproval?->note,
                'approved_at' => $latestApproval?->approved_at ? $latestApproval->approved_at->format('d M Y H:i \W\I\T\A') : null,
                'invalidated_at' => $latestApproval?->invalidated_at ? $latestApproval->invalidated_at->format('d M Y H:i \W\I\T\A') : null,
            ],
            'escalation' => [
                'has_active' => $activeEscalation !== null,
                'is_resolved' => $resolvedEscalation !== null,
                'ticket_id' => $latestEscalation ? 'ESC-'.strtoupper(substr($latestEscalation->id, 0, 8)) : null,
                'category' => $latestEscalation ? 'Mediasi Sengketa Redaksi' : null,
                'reason' => $latestEscalation?->reason,
                'status' => $latestEscalation ? ($activeEscalation ? 'Menunggu Mediasi Section Editor' : 'Selesai Diputuskan') : null,
                'resolution' => $resolvedEscalation?->resolution,
                'resolved_at' => $resolvedEscalation?->updated_at ? $resolvedEscalation->updated_at->format('d M Y') : null,
                'created_at' => $latestEscalation?->created_at ? $latestEscalation->created_at->format('d M Y H:i \W\I\T\A') : null,
            ],
            'handoff' => [
                'is_ready' => $handoffReady,
                'is_completed' => $copyedit->status === 'completed',
                'assigned_layout_editor' => $layoutEditorName,
                'notes_placeholder' => 'Catatan khusus gaya selingkung atau instruksi penataan untuk Layout Editor...',
                'submitted_notes' => $copyedit->notes,
                'completed_at' => $copyedit->status === 'completed' && $copyedit->updated_at ? $copyedit->updated_at->format('d M Y, H:i \W\I\T\A') : null,
            ],
            'notes' => $copyedit->notes,
            'activity_timeline' => $timeline,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / pow(1024, $i), 2).' '.$units[$i];
    }
}
