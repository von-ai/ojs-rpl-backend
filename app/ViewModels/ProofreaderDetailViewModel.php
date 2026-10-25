<?php

declare(strict_types=1);

namespace App\ViewModels;

class ProofreaderDetailViewModel
{
    private string $submissionId;

    public function __construct(string $submissionId = 'SUB-2026-00106')
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Mendapatkan ID naskah aktif.
     */
    public function submissionId(): string
    {
        return $this->submissionId;
    }

    /**
     * Data naskah dan penugasan proofreading spesifik.
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
     *     due_at: string,
     *     assigned_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     is_completed: bool,
     *     active_galley: array{
     *         id: string,
     *         version: int,
     *         version_label: string,
     *         file_name: string,
     *         size_formatted: string,
     *         checksum_sha256: string,
     *         download_url: string,
     *         uploaded_at: string
     *     },
     *     copyedit_reference: array{
     *         id: string,
     *         file_name: string,
     *         size_formatted: string,
     *         checksum_sha256: string,
     *         download_url: string,
     *         description: string
     *     },
     *     notes: array<int, array{
     *         id: string,
     *         page: ?int,
     *         category: string,
     *         category_label: string,
     *         severity: string,
     *         severity_badge: string,
     *         severity_label: string,
     *         description: string,
     *         resolved: bool,
     *         resolution: ?string,
     *         resolved_at: ?string,
     *         verified: bool
     *     }>,
     *     summary: array{
     *         total_notes: int,
     *         unresolved_notes: int,
     *         resolved_notes: int,
     *         can_signoff: bool,
     *         signoff_guard_reason: ?string
     *     },
     *     escalation: array{
     *         has_active: bool,
     *         ticket_id: ?string,
     *         reason: ?string,
     *         status_label: ?string
     *     },
     *     activity_timeline: array<int, array{
     *         title: string,
     *         actor: string,
     *         date: string,
     *         description: string,
     *         badge_class: string
     *     }>
     * }
     */
    public function submission(): array
    {
        $registry = $this->registry();

        return $registry[$this->submissionId] ?? $registry['SUB-2026-00106'];
    }

    /**
     * Kumpulan data naskah uji coba realistis untuk ruang kerja proofreading.
     *
     * @return array<string, array<string, mixed>>
     */
    private function registry(): array
    {
        return [
            'SUB-2026-00106' => [
                'id' => 'SUB-2026-00106',
                'number' => 'SUB-2026-00106',
                'assignment_code' => '#PR-01JARK949Y',
                'title' => 'Strategi Pengembangan Destinasi Wisata Sejarah Benteng Somba Opu Berbasis Smart Tourism',
                'section_title' => 'Inovasi Pelayanan Publik & Pariwisata',
                'corresponding_author' => [
                    'name' => 'Dr. Andi Nurul Hidayah, M.Si.',
                    'email' => 'andi.nurul@unm.ac.id',
                    'affiliation' => 'Fakultas Ilmu Sosial dan Politik, Universitas Negeri Makassar',
                ],
                'section_editor' => 'Dr. Ir. Rusman, M.T. (Editor Bidang)',
                'layout_editor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                'due_at' => '28 Okt 2026',
                'assigned_at' => '21 Okt 2026, 10:15 WITA',
                'is_urgent' => false,
                'status' => 'in_review',
                'status_label' => 'Verifikasi Revisi Galai (v2)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'is_completed' => false,
                'active_galley' => [
                    'id' => 'GAL-01JARK-02',
                    'version' => 2,
                    'version_label' => 'Versi 2 (Revisi)',
                    'file_name' => 'SUB-2026-00106_Galley_v2.pdf',
                    'size_formatted' => '3.8 MB',
                    'checksum_sha256' => '9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b',
                    'download_url' => '#',
                    'uploaded_at' => '24 Okt 2026, 14:30 WITA',
                ],
                'copyedit_reference' => [
                    'id' => 'FILE-CE-FINAL-106',
                    'file_name' => 'SUB-2026-00106_Copyedited_Final.docx',
                    'size_formatted' => '642 KB',
                    'checksum_sha256' => '3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d',
                    'download_url' => '#',
                    'description' => 'Naskah teks bersih hasil copyediting final untuk rujukan verifikasi tata letak teks.',
                ],
                'notes' => [
                    [
                        'id' => 'NOTE-01JARK-01',
                        'page' => 2,
                        'category' => 'typo',
                        'category_label' => 'Tipografi / Ejaan',
                        'severity' => 'minor',
                        'severity_badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'severity_label' => 'Minor',
                        'description' => 'Kesalahan penulisan kata "pariwista" seharusnya "pariwisata" pada alinea kedua paragraf pembuka.',
                        'resolved' => true,
                        'resolution' => 'Sudah diperbaiki pada draf galai v2 sesuai teks copyediting final.',
                        'resolved_at' => '24 Okt 2026, 14:15 WITA',
                        'verified' => true,
                    ],
                    [
                        'id' => 'NOTE-01JARK-02',
                        'page' => 4,
                        'category' => 'layout',
                        'category_label' => 'Tata Letak / Format',
                        'severity' => 'major',
                        'severity_badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'severity_label' => 'Major',
                        'description' => 'Keterangan Gambar 3 (Peta Kawasan Benteng Somba Opu) terpotong margin bawah kolom sebelah kanan.',
                        'resolved' => true,
                        'resolution' => 'Ukuran skala gambar 3 diperkecil 10% dan posisi keterangan dipindahkan ke baris penuh.',
                        'resolved_at' => '24 Okt 2026, 14:20 WITA',
                        'verified' => false,
                    ],
                    [
                        'id' => 'NOTE-01JARK-03',
                        'page' => 7,
                        'category' => 'reference',
                        'category_label' => 'Sitasi & Pustaka',
                        'severity' => 'critical',
                        'severity_badge' => 'bg-red-50 text-red-700 border-red-200',
                        'severity_label' => 'Kritis',
                        'description' => 'Sitasi (BPS Kota Makassar, 2025) pada halaman 3 belum tercantum dalam daftar pustaka.',
                        'resolved' => false,
                        'resolution' => null,
                        'resolved_at' => null,
                        'verified' => false,
                    ],
                ],
                'summary' => [
                    'total_notes' => 3,
                    'unresolved_notes' => 1,
                    'resolved_notes' => 2,
                    'can_signoff' => false,
                    'signoff_guard_reason' => 'Terdapat 1 catatan berkategori Kritis yang belum diselesaikan oleh Layout Editor.',
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status_label' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Galai Revisi v2 Diunggah',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '24 Okt 2026, 14:30 WITA',
                        'description' => 'Telah menindaklanjuti 2 catatan koreksi dan mengunggah berkas PDF galai revisi v2.',
                        'badge_class' => 'bg-blue-100 text-blue-700',
                    ],
                    [
                        'title' => 'Catatan Koreksi Bukti Baca Dikirim',
                        'actor' => 'Dra. Sitti Aminah, M.Hum. (Proofreader)',
                        'date' => '22 Okt 2026, 16:45 WITA',
                        'description' => 'Mengirimkan 3 catatan koreksi terkait typo, layout gambar, dan kelengkapan daftar pustaka.',
                        'badge_class' => 'bg-amber-100 text-amber-700',
                    ],
                    [
                        'title' => 'Penugasan Bukti Baca Resmi Diterbitkan',
                        'actor' => 'Sistem OJS BRIDA',
                        'date' => '21 Okt 2026, 10:15 WITA',
                        'description' => 'Naskah dialokasikan kepada Dra. Sitti Aminah, M.Hum. dengan batas waktu 28 Okt 2026.',
                        'badge_class' => 'bg-gray-100 text-gray-700',
                    ],
                ],
            ],
            'SUB-2026-00107' => [
                'id' => 'SUB-2026-00107',
                'number' => 'SUB-2026-00107',
                'assignment_code' => '#PR-01JARL018Z',
                'title' => 'Analisis Spasial Kerentanan Banjir Rob Kawasan Pesisir Kota Makassar Menggunakan Sentinel-1A',
                'section_title' => 'Kebijakan Lingkungan & Mitigasi Bencana',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                    'email' => 'saleh.pallu@unhas.ac.id',
                    'affiliation' => 'Departemen Teknik Sipil, Universitas Hasanuddin',
                ],
                'section_editor' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'due_at' => '27 Okt 2026',
                'assigned_at' => '23 Okt 2026, 09:00 WITA',
                'is_urgent' => true,
                'status' => 'in_review',
                'status_label' => 'Perlu Pemeriksaan Galai (v1)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'is_completed' => false,
                'active_galley' => [
                    'id' => 'GAL-01JARL-01',
                    'version' => 1,
                    'version_label' => 'Versi 1 (Draf Awal)',
                    'file_name' => 'SUB-2026-00107_Galley_v1.pdf',
                    'size_formatted' => '5.2 MB',
                    'checksum_sha256' => 'b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2',
                    'download_url' => '#',
                    'uploaded_at' => '23 Okt 2026, 08:30 WITA',
                ],
                'copyedit_reference' => [
                    'id' => 'FILE-CE-FINAL-107',
                    'file_name' => 'SUB-2026-00107_Copyedited_Final.docx',
                    'size_formatted' => '820 KB',
                    'checksum_sha256' => '4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c',
                    'download_url' => '#',
                    'description' => 'Naskah teks bersih rujukan copyediting final dengan rumus dan persamaan hidro-oseanografi.',
                ],
                'notes' => [],
                'summary' => [
                    'total_notes' => 0,
                    'unresolved_notes' => 0,
                    'resolved_notes' => 0,
                    'can_signoff' => true,
                    'signoff_guard_reason' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status_label' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Draf Galai v1 Diunggah',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '23 Okt 2026, 08:30 WITA',
                        'description' => 'Berkas PDF galai versi 1 siap dilakukan pemeriksaan bukti baca.',
                        'badge_class' => 'bg-blue-100 text-blue-700',
                    ],
                ],
            ],
            'SUB-2026-00103' => [
                'id' => 'SUB-2026-00103',
                'number' => 'SUB-2026-00103',
                'assignment_code' => '#PR-01JARK110X',
                'title' => 'Persepsi Masyarakat Terhadap Penerapan Transportasi Publik Teman Bus di Kawasan Aglomerasi Mamminasata',
                'section_title' => 'Teknologi & Tata Kelola Perkotaan',
                'corresponding_author' => [
                    'name' => 'Ir. Muhammad Arsyad, Ph.D.',
                    'email' => 'arsyad@unhas.ac.id',
                    'affiliation' => 'Fakultas Teknik, Universitas Hasanuddin',
                ],
                'section_editor' => 'Dr. Ir. Rusman, M.T.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'due_at' => '24 Okt 2026',
                'assigned_at' => '19 Okt 2026, 11:00 WITA',
                'is_urgent' => false,
                'status' => 'completed',
                'status_label' => 'Proofreading Selesai Disetujui',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                'is_completed' => true,
                'active_galley' => [
                    'id' => 'GAL-01JARK-03B',
                    'version' => 2,
                    'version_label' => 'Versi 2 (Final Disetujui)',
                    'file_name' => 'SUB-2026-00103_Galley_Final.pdf',
                    'size_formatted' => '2.9 MB',
                    'checksum_sha256' => '5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d',
                    'download_url' => '#',
                    'uploaded_at' => '23 Okt 2026, 11:00 WITA',
                ],
                'copyedit_reference' => [
                    'id' => 'FILE-CE-FINAL-103',
                    'file_name' => 'SUB-2026-00103_Copyedited_Final.docx',
                    'size_formatted' => '512 KB',
                    'checksum_sha256' => '7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f',
                    'download_url' => '#',
                    'description' => 'Naskah teks bersih copyediting final.',
                ],
                'notes' => [
                    [
                        'id' => 'NOTE-01JARK-99',
                        'page' => 1,
                        'category' => 'metadata',
                        'category_label' => 'Metadata / Penulis',
                        'severity' => 'minor',
                        'severity_badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'severity_label' => 'Minor',
                        'description' => 'Penulisan gelar penulis korespondensi diselaraskan dengan data OJS.',
                        'resolved' => true,
                        'resolution' => 'Telah diperbarui di galai v2.',
                        'resolved_at' => '23 Okt 2026, 15:00 WITA',
                        'verified' => true,
                    ],
                ],
                'summary' => [
                    'total_notes' => 1,
                    'unresolved_notes' => 0,
                    'resolved_notes' => 1,
                    'can_signoff' => true,
                    'signoff_guard_reason' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status_label' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Sign-off Bukti Baca Diberikan',
                        'actor' => 'Dra. Sitti Aminah, M.Hum. (Proofreader)',
                        'date' => '24 Okt 2026, 10:00 WITA',
                        'description' => 'Galai PDF versi 2 dinyatakan bebas kesalahan tipografi dan siap untuk persetujuan akhir.',
                        'badge_class' => 'bg-emerald-100 text-emerald-800',
                    ],
                ],
            ],
        ];
    }
}
