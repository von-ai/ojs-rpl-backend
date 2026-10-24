<?php

declare(strict_types=1);

namespace App\ViewModels;

class LayoutEditorDetailViewModel
{
    private string $submissionId;

    public function __construct(string $submissionId = 'SUB-2026-00106')
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Data lengkap naskah untuk ruang kerja Layout Editor.
     * Mengonsumsi data relasi layout_assignments, galleys, proof_assignments,
     * proofreading_notes, galley_approvals, dan production_escalations.
     *
     * @return array{
     *     id: string,
     *     number: string,
     *     assignment_code: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, email: string, affiliation: string},
     *     section_editor: string,
     *     proofreader: string,
     *     assigned_at: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     is_completed: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     copyedit_final_file: array{
     *         id: string,
     *         name: string,
     *         size_formatted: string,
     *         checksum_sha256: string,
     *         download_url: string,
     *         notes_from_copyeditor: ?string
     *     },
     *     galleys: array<int, array{
     *         id: string,
     *         version: int,
     *         label: string,
     *         format: string,
     *         file_name: string,
     *         size_formatted: string,
     *         checksum_sha256: string,
     *         stage: string,
     *         stage_label: string,
     *         stage_badge_class: string,
     *         status: string,
     *         is_active: bool,
     *         uploaded_at: string,
     *         download_url: string
     *     }>,
     *     proofreading: array{
     *         has_assignment: bool,
     *         proofreader_name: string,
     *         status: string,
     *         status_label: string,
     *         total_notes_count: int,
     *         unresolved_count: int,
     *         can_request_proofread: bool,
     *         notes: array<int, array{
     *             id: string,
     *             page: ?int,
     *             category: string,
     *             category_label: string,
     *             severity: string,
     *             severity_badge: string,
     *             description: string,
     *             resolved: bool,
     *             resolution: ?string,
     *             resolved_at: ?string
     *         }>
     *     },
     *     approval: array{
     *         author_signoff: array{
     *             approved: bool,
     *             approved_at: ?string,
     *             notes: ?string
     *         },
     *         editor_final: array{
     *             approved: bool,
     *             approved_at: ?string,
     *             ready_for_publication: bool
     *         }
     *     },
     *     escalation: array{
     *         has_active: bool,
     *         ticket_id: ?string,
     *         reason: ?string,
     *         status: ?string,
     *         status_label: ?string,
     *         resolution: ?string,
     *         resolved_at: ?string,
     *         created_at: ?string
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
        $dataset = $this->registry();

        if (isset($dataset[$this->submissionId])) {
            return $dataset[$this->submissionId];
        }

        // Fallback dinamis jika ID tidak ditemukan di simulasi dataset
        return array_merge($dataset['SUB-2026-00106'], [
            'id' => $this->submissionId,
            'number' => $this->submissionId,
        ]);
    }

    /**
     * Kumpulan dataset skenario realistis ruang kerja layout editor.
     *
     * @return array<string, array<string, mixed>>
     */
    private function registry(): array
    {
        return [
            // 1. Skenario Awal: Baru diserahkan dari Copy Editor, belum ada berkas Galai
            'SUB-2026-00106' => [
                'id' => 'SUB-2026-00106',
                'number' => 'SUB-2026-00106',
                'assignment_code' => '#LE-01JARK948X',
                'title' => 'Analisis Spasial Ruang Terbuka Hijau Terhadap Efek Urban Heat Island Kota Makassar',
                'section_title' => 'Inovasi Daerah & Kebijakan Publik',
                'corresponding_author' => [
                    'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                    'email' => 'nurfadillah.bahar@unhas.ac.id',
                    'affiliation' => 'Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'assigned_at' => '24 Okt 2026 15:30 WITA',
                'due_at' => '02 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'is_completed' => false,
                'status' => 'drafting',
                'status_label' => 'Perlu Draf Galai PDF',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'copyedit_final_file' => [
                    'id' => 'FILE-CE-FINAL-00106',
                    'name' => 'SUB2026-00106_Copyedit_Final_Approved.docx',
                    'size_formatted' => '2.05 MB',
                    'checksum_sha256' => 'a93b48f95c80dbb5a452ef32dc0e6e7890efcd12f451486791b40285a210b4f8',
                    'download_url' => '#unduh-naskah-final-copyedit',
                    'notes_from_copyeditor' => 'Naskah telah selesai disunting sesuai EYD Edisi V dan format APA 7th Edition. Seluruh gambar beresolusi tinggi (300 DPI) telah tersemat rapi.',
                ],
                'galleys' => [],
                'proofreading' => [
                    'has_assignment' => true,
                    'proofreader_name' => 'Dra. Sitti Aminah, M.Hum.',
                    'status' => 'waiting_galley',
                    'status_label' => 'Menunggu Draf Galai Pertama',
                    'total_notes_count' => 0,
                    'unresolved_count' => 0,
                    'can_request_proofread' => false,
                    'notes' => [],
                ],
                'approval' => [
                    'author_signoff' => [
                        'approved' => false,
                        'approved_at' => null,
                        'notes' => null,
                    ],
                    'editor_final' => [
                        'approved' => false,
                        'approved_at' => null,
                        'ready_for_publication' => false,
                    ],
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status' => null,
                    'status_label' => null,
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Naskah Diterima dari Copyeditor',
                        'actor' => 'Sistem OJS (Serah Terima Copyeditor)',
                        'date' => '24 Okt 2026 15:30 WITA',
                        'description' => 'Naskah final copyediting selesai disetujui penulis dan dialihkan ke tahap produksi tata letak.',
                        'badge_class' => 'bg-emerald-500',
                    ],
                    [
                        'title' => 'Penugasan Layout Editor',
                        'actor' => 'Prof. Hamka, Ph.D. (Section Editor)',
                        'date' => '24 Okt 2026 15:35 WITA',
                        'description' => 'Layout editor ditugaskan untuk menyusun berkas galai PDF 2-kolom standar jurnal BRIDA.',
                        'badge_class' => 'bg-brand-maroon',
                    ],
                ],
            ],

            // 2. Skenario Proofreading Aktif: Galai v1 terunggah, ada catatan koreksi
            'SUB-2026-00107' => [
                'id' => 'SUB-2026-00107',
                'number' => 'SUB-2026-00107',
                'assignment_code' => '#LE-01JARK949Y',
                'title' => 'Transformasi Digital Pelayanan Publik Kelurahan di Kota Makassar: Evaluasi Implementasi Aplikasi Lorong',
                'section_title' => 'Sosial Humaniora & Tata Kelola',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. Muhammad Basri, M.T.',
                    'email' => 'm.basri@poliupg.ac.id',
                    'affiliation' => 'Politeknik Negeri Ujung Pandang',
                ],
                'section_editor' => 'Dr. Hj. St. Rohani, M.Si.',
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'assigned_at' => '20 Okt 2026 09:15 WITA',
                'due_at' => '28 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'is_completed' => false,
                'status' => 'proofreading',
                'status_label' => '2 Catatan Proofreading',
                'status_badge_class' => 'bg-blue-50 text-blue-800 border-blue-200',
                'copyedit_final_file' => [
                    'id' => 'FILE-CE-FINAL-00107',
                    'name' => 'SUB2026-00107_Copyedit_Final_Approved.docx',
                    'size_formatted' => '1.88 MB',
                    'checksum_sha256' => 'b18e7623a8e321cb821389bc714f3319028aefc2918bb1293a1098471bce43a1',
                    'download_url' => '#unduh-naskah-final-copyedit',
                    'notes_from_copyeditor' => 'Gaya sitasi telah distandarisasi.',
                ],
                'galleys' => [
                    [
                        'id' => 'GALLEY-00107-V1',
                        'version' => 1,
                        'label' => 'PDF Full Text (Draf v1)',
                        'format' => 'pdf',
                        'file_name' => 'SUB2026-00107_Galley_v1.pdf',
                        'size_formatted' => '2.45 MB',
                        'checksum_sha256' => '9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b',
                        'stage' => 'proofreading',
                        'stage_label' => 'Tahap Proofreading',
                        'stage_badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'status' => 'active',
                        'is_active' => true,
                        'uploaded_at' => '22 Okt 2026 11:20 WITA',
                        'download_url' => '#unduh-galley-v1',
                    ],
                ],
                'proofreading' => [
                    'has_assignment' => true,
                    'proofreader_name' => 'Dra. Sitti Aminah, M.Hum.',
                    'status' => 'notes_received',
                    'status_label' => 'Catatan Koreksi Diterima',
                    'total_notes_count' => 2,
                    'unresolved_count' => 2,
                    'can_request_proofread' => false,
                    'notes' => [
                        [
                            'id' => 'NOTE-01JARK-01',
                            'page' => 4,
                            'category' => 'layout',
                            'category_label' => 'Tata Letak / Tabel',
                            'severity' => 'major',
                            'severity_badge' => 'bg-purple-50 text-purple-800 border-purple-200',
                            'description' => 'Tabel 2 (Matriks Evaluasi Fitur Aplikasi Lorong) terpotong secara canggung di antara kolom halaman 4 dan 5. Harap dibuat selebar 2-kolom di bagian atas halaman 5.',
                            'resolved' => false,
                            'resolution' => null,
                            'resolved_at' => null,
                        ],
                        [
                            'id' => 'NOTE-01JARK-02',
                            'page' => 8,
                            'category' => 'typo',
                            'category_label' => 'Tipografi',
                            'severity' => 'minor',
                            'severity_badge' => 'bg-blue-50 text-blue-800 border-blue-200',
                            'description' => 'Daftar Pustaka ke-12: nama jurnal "Jurnal Administrasi Publik" belum dicetak miring (italic).',
                            'resolved' => false,
                            'resolution' => null,
                            'resolved_at' => null,
                        ],
                    ],
                ],
                'approval' => [
                    'author_signoff' => [
                        'approved' => false,
                        'approved_at' => null,
                        'notes' => null,
                    ],
                    'editor_final' => [
                        'approved' => false,
                        'approved_at' => null,
                        'ready_for_publication' => false,
                    ],
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status' => null,
                    'status_label' => null,
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Catatan Proofreading Diterbitkan',
                        'actor' => 'Dra. Sitti Aminah, M.Hum. (Proofreader)',
                        'date' => '23 Okt 2026 16:45 WITA',
                        'description' => 'Proofreader telah memasukkan 2 catatan koreksi tata letak dan tipografi pada berkas Galai v1.',
                        'badge_class' => 'bg-amber-500',
                    ],
                    [
                        'title' => 'Draf Galai v1 Diunggah',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '22 Okt 2026 11:20 WITA',
                        'description' => 'Berkas draf awal galai PDF selesai disusun dan diteruskan ke putaran proofreading.',
                        'badge_class' => 'bg-blue-500',
                    ],
                ],
            ],

            // 3. Skenario Eskalasi Kendala Tata Letak Aktif
            'SUB-2026-00108' => [
                'id' => 'SUB-2026-00108',
                'number' => 'SUB-2026-00108',
                'assignment_code' => '#LE-01JARK950Z',
                'title' => 'Kajian Karakteristik Geoteknik Sedimen Pesisir Pantai Losari untuk Mitigasi Erosi Gelombang Pasang',
                'section_title' => 'Teknologi & Infrastruktur Berkelanjutan',
                'corresponding_author' => [
                    'name' => 'Dr. Eng. Ir. Faisal Syarif, S.T., M.Eng.',
                    'email' => 'faisal.syarif@eng.unhas.ac.id',
                    'affiliation' => 'Fakultas Teknik Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'proofreader' => 'Drs. H. Anwar Kasim, M.Pd.',
                'assigned_at' => '19 Okt 2026 14:00 WITA',
                'due_at' => '31 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'is_completed' => false,
                'status' => 'escalated',
                'status_label' => 'Dalam Eskalasi Tata Letak',
                'status_badge_class' => 'bg-purple-50 text-purple-800 border-purple-200',
                'copyedit_final_file' => [
                    'id' => 'FILE-CE-FINAL-00108',
                    'name' => 'SUB2026-00108_Copyedit_Final_Approved.docx',
                    'size_formatted' => '3.40 MB',
                    'checksum_sha256' => 'c8914bca82103810f928130491823bcdae123984124971249817298371928471',
                    'download_url' => '#unduh-naskah-final-copyedit',
                    'notes_from_copyeditor' => 'Peta batimetri resolusi tinggi disertakan.',
                ],
                'galleys' => [
                    [
                        'id' => 'GALLEY-00108-V1',
                        'version' => 1,
                        'label' => 'PDF Full Text (Draf v1)',
                        'format' => 'pdf',
                        'file_name' => 'SUB2026-00108_Galley_v1.pdf',
                        'size_formatted' => '3.80 MB',
                        'checksum_sha256' => '7182937192847192837192837192837192837192837192837192837192837192',
                        'stage' => 'draft',
                        'stage_label' => 'Draf Layout',
                        'stage_badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'status' => 'active',
                        'is_active' => true,
                        'uploaded_at' => '21 Okt 2026 10:10 WITA',
                        'download_url' => '#unduh-galley-v1',
                    ],
                ],
                'proofreading' => [
                    'has_assignment' => true,
                    'proofreader_name' => 'Drs. H. Anwar Kasim, M.Pd.',
                    'status' => 'waiting_review',
                    'status_label' => 'Tertahan Eskalasi Redaksi',
                    'total_notes_count' => 1,
                    'unresolved_count' => 1,
                    'can_request_proofread' => false,
                    'notes' => [
                        [
                            'id' => 'NOTE-01JARK-03',
                            'page' => 6,
                            'category' => 'layout',
                            'category_label' => 'Format Gambar / Grafik',
                            'severity' => 'critical',
                            'severity_badge' => 'bg-red-50 text-red-800 border-red-200',
                            'description' => 'Gambar peta batimetri Pantai Losari berorientasi lanskap melebar, koordinat UTM tidak terbaca jika dipaksa ke format potret 2-kolom.',
                            'resolved' => false,
                            'resolution' => null,
                            'resolved_at' => null,
                        ],
                    ],
                ],
                'approval' => [
                    'author_signoff' => [
                        'approved' => false,
                        'approved_at' => null,
                        'notes' => null,
                    ],
                    'editor_final' => [
                        'approved' => false,
                        'approved_at' => null,
                        'ready_for_publication' => false,
                    ],
                ],
                'escalation' => [
                    'has_active' => true,
                    'ticket_id' => 'ESC-2026-0071',
                    'reason' => 'Gambar peta kontur batimetri pantai berdimensi lanskap sangat lebar. Memohon arahan Section Editor apakah diperkenankan menyisipkan 1 halaman lampiran khusus berorientasi lanskap dalam terbitan berkala.',
                    'status' => 'in_review',
                    'status_label' => 'Menunggu Arahan Section Editor',
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => '23 Okt 2026 14:00 WITA',
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Tiket Eskalasi Tata Letak Diterbitkan',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '23 Okt 2026 14:00 WITA',
                        'description' => 'Tiket ESC-2026-0071 diajukan ke Section Editor terkait halaman lanskap khusus peta batimetri.',
                        'badge_class' => 'bg-purple-600',
                    ],
                ],
            ],

            // 4. Skenario Selesai & Disetujui Siap Terbit
            'SUB-2026-00109' => [
                'id' => 'SUB-2026-00109',
                'number' => 'SUB-2026-00109',
                'assignment_code' => '#LE-01JARK951A',
                'title' => 'Efektivitas Program Lorong Wisata dalam Peningkatan Pendapatan Rumah Tangga Berpenghasilan Rendah',
                'section_title' => 'Ekonomi Pembangunan & Pemberdayaan Masyarakat',
                'corresponding_author' => [
                    'name' => 'Hj. Fatmawati, S.E., M.Si.',
                    'email' => 'fatmawati@unifa.ac.id',
                    'affiliation' => 'Universitas Fajar Makassar',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'assigned_at' => '15 Okt 2026 08:30 WITA',
                'due_at' => '05 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'is_completed' => true,
                'status' => 'ready',
                'status_label' => 'Galai Disetujui EIC (Siap Terbit)',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'copyedit_final_file' => [
                    'id' => 'FILE-CE-FINAL-00109',
                    'name' => 'SUB2026-00109_Copyedit_Final_Approved.docx',
                    'size_formatted' => '1.75 MB',
                    'checksum_sha256' => 'd819238471928471928371928471928471928471928471928471928471928471',
                    'download_url' => '#unduh-naskah-final-copyedit',
                    'notes_from_copyeditor' => 'Seluruh kutipan telah diverifikasi.',
                ],
                'galleys' => [
                    [
                        'id' => 'GALLEY-00109-V2',
                        'version' => 2,
                        'label' => 'PDF Full Text (v2 Final Terbit)',
                        'format' => 'pdf',
                        'file_name' => 'SUB2026-00109_Galley_v2_Final.pdf',
                        'size_formatted' => '2.10 MB',
                        'checksum_sha256' => '6543210fedcba9876543210fedcba9876543210fedcba9876543210fedcba98',
                        'stage' => 'approved',
                        'stage_label' => 'Disetujui EIC',
                        'stage_badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'status' => 'active',
                        'is_active' => true,
                        'uploaded_at' => '23 Okt 2026 10:15 WITA',
                        'download_url' => '#unduh-galley-v2-final',
                    ],
                    [
                        'id' => 'GALLEY-00109-V1',
                        'version' => 1,
                        'label' => 'PDF Full Text (v1 Draf Awal)',
                        'format' => 'pdf',
                        'file_name' => 'SUB2026-00109_Galley_v1.pdf',
                        'size_formatted' => '2.08 MB',
                        'checksum_sha256' => '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
                        'stage' => 'draft',
                        'stage_label' => 'Tergantikan (Superseded)',
                        'stage_badge_class' => 'bg-gray-100 text-gray-600 border-gray-200',
                        'status' => 'superseded',
                        'is_active' => false,
                        'uploaded_at' => '18 Okt 2026 14:20 WITA',
                        'download_url' => '#unduh-galley-v1',
                    ],
                ],
                'proofreading' => [
                    'has_assignment' => true,
                    'proofreader_name' => 'Dra. Sitti Aminah, M.Hum.',
                    'status' => 'all_resolved',
                    'status_label' => 'Semua Catatan Selesai',
                    'total_notes_count' => 1,
                    'unresolved_count' => 0,
                    'can_request_proofread' => false,
                    'notes' => [
                        [
                            'id' => 'NOTE-01JARK-04',
                            'page' => 2,
                            'category' => 'metadata',
                            'category_label' => 'Metadata / DOI',
                            'severity' => 'minor',
                            'severity_badge' => 'bg-blue-50 text-blue-800 border-blue-200',
                            'description' => 'Penulisan DOI pada header halaman ganjil belum sinkron dengan tahun terbit 2026.',
                            'resolved' => true,
                            'resolution' => 'Header running DOI telah disesuaikan menjadi 10.61234/sirida.v6i2.2026 pada Galley v2.',
                            'resolved_at' => '23 Okt 2026 10:00 WITA',
                        ],
                    ],
                ],
                'approval' => [
                    'author_signoff' => [
                        'approved' => true,
                        'approved_at' => '23 Okt 2026 14:20 WITA',
                        'notes' => 'Tata letak sudah sangat rapi dan sesuai dengan format publikasi kami.',
                    ],
                    'editor_final' => [
                        'approved' => true,
                        'approved_at' => '24 Okt 2026 09:30 WITA',
                        'ready_for_publication' => true,
                    ],
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status' => null,
                    'status_label' => null,
                    'resolution' => null,
                    'resolved_at' => null,
                    'created_at' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Persetujuan Final Editor in Chief',
                        'actor' => 'Prof. Dr. Ir. H. Nasaruddin, M.T. (EIC)',
                        'date' => '24 Okt 2026 09:30 WITA',
                        'description' => 'Galai PDF v2 disetujui untuk dimasukkan dalam jadwal penerbitan Volume 6 Nomor 2 Tahun 2026.',
                        'badge_class' => 'bg-emerald-600',
                    ],
                    [
                        'title' => 'Persetujuan Akhir Penulis (Sign-off)',
                        'actor' => 'Hj. Fatmawati, S.E., M.Si. (Penulis)',
                        'date' => '23 Okt 2026 14:20 WITA',
                        'description' => 'Penulis menyetujui galai PDF v2 tanpa catatan revisi tambahan.',
                        'badge_class' => 'bg-blue-600',
                    ],
                    [
                        'title' => 'Galai v2 Final Diunggah',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '23 Okt 2026 10:15 WITA',
                        'description' => 'Galai versi 2 diterbitkan menggantikan versi 1 setelah seluruh catatan proofreading diselesaikan.',
                        'badge_class' => 'bg-brand-maroon',
                    ],
                ],
            ],
        ];
    }
}
