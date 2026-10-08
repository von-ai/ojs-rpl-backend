<?php

namespace App\ViewModels;

class AuthorDashboardViewModel
{
    /**
     * Get user profile details for the active author context.
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'affiliation' => 'Universitas Hasanuddin',
            'role_label' => 'PORTAL PENULIS OJS 3',
            'academic_year' => 'Tahun Akademik 2026/2027',
            'unread_messages' => 1,
            'unread_notifications' => 2,
        ];
    }

    /**
     * Get summary counts for submission workflow stages.
     */
    public function submissionStats(): array
    {
        return [
            'total_registered' => 7,
            'draft' => 1,
            'submitted' => 1,
            'under_review' => 1,
            'revision_required' => 1,
            'accepted' => 1,
            'published' => 2,
        ];
    }

    /**
     * Get urgent action-required tasks for the author.
     */
    public function actionRequiredTasks(): array
    {
        return [
            [
                'id' => 'SUB-2026-00124',
                'type' => 'revision',
                'type_badge' => 'PERLU REVISI (REVISION REQUIRED)',
                'deadline' => '15 Oktober 2026',
                'days_left' => 'Tersisa 8 Hari',
                'category' => 'Jurnal Riset Kebijakan Publik',
                'round' => 'Round 1 (Minor Revision)',
                'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Pemberdayaan Ekonomi Kreatif di Kota Makassar',
                'editor_note' => [
                    'author' => 'Prof. Hendra Saputra',
                    'text' => 'Dewan Editor telah menerima 2 laporan mitra bestari (peer reviewer). Diperlukan revisi minor pada metodologi sampling wilayah pesisir serta penyesuaian format sitasi IEEE sesuai pedoman edisi terbaru jurnal BRIDA.',
                ],
                'actions' => [
                    'submit_revision_url' => '#',
                    'view_reviews_url' => '#',
                    'message_editor_url' => '#',
                ],
            ],
            [
                'id' => 'SUB-2026-00098',
                'type' => 'proofreading',
                'type_badge' => 'BUKTI CETAK (PROOFREADING)',
                'deadline' => '10 Oktober 2026',
                'days_left' => null,
                'category' => 'Rekayasa & Teknologi Informasi',
                'round' => 'Final Galley Approval',
                'title' => 'Model Prediksi Kualitas Udara Perkotaan Berbasis Jaringan Sensor IoT di Makassar',
                'editor_note' => [
                    'author' => 'Layout Editor BRIDA',
                    'text' => 'Tata letak PDF galai akhir telah siap. Mohon periksa kesalahan pengetikan nama penulis, afiliasi, penomoran tabel/grafik, serta pengesahan akhir sebelum penerbitan volume digital.',
                ],
                'actions' => [
                    'view_galley_url' => '#',
                ],
            ],
        ];
    }

    /**
     * Get all active & archived submissions table list.
     */
    public function submissions(): array
    {
        return [
            [
                'id' => 'SUB-2026-00124',
                'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Pemberdayaan Ekonomi Kreatif di Kota Makassar',
                'section' => 'Kebijakan Publik & Inovasi Sosial',
                'submitted_date' => '12 Sep 2026',
                'stage' => 'Telaah Sejawat (Peer Review)',
                'status_label' => 'Perlu revisi',
                'status_class' => 'bg-red-100 text-red-800 border-red-200',
                'updated_date' => '18 Sep 2026',
                'action_label' => 'Revisi Naskah',
                'action_class' => 'bg-brand-maroon text-white hover:bg-brand-maroon-hover',
                'action_url' => '#',
            ],
            [
                'id' => 'SUB-2026-000142',
                'title' => 'Analisis Ketahanan Pangan Rumah Tangga Pesisir di Kecamatan Tallo',
                'section' => 'Sosial Ekonomi & Kelautan',
                'submitted_date' => '28 Sep 2026',
                'stage' => 'Pemeriksaan Awal (Initial Check)',
                'status_label' => 'Dalam telaah',
                'status_class' => 'bg-blue-100 text-blue-800 border-blue-200',
                'updated_date' => '30 Sep 2026',
                'action_label' => 'Lihat Progres',
                'action_class' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 border border-gray-300',
                'action_url' => '#',
            ],
            [
                'id' => 'SUB-2026-00098',
                'title' => 'Model Prediksi Kualitas Udara Perkotaan Berbasis Jaringan Sensor IoT di Makassar',
                'section' => 'Rekayasa & Teknologi Informasi',
                'submitted_date' => '15 Jul 2026',
                'stage' => 'Produksi (Production)',
                'status_label' => 'Proofreading',
                'status_class' => 'bg-amber-100 text-amber-800 border-amber-200',
                'updated_date' => '02 Okt 2026',
                'action_label' => 'Tinjau Galai',
                'action_class' => 'bg-slate-700 text-white hover:bg-slate-800',
                'action_url' => '#',
            ],
            [
                'id' => 'SUB-2026-00155',
                'title' => 'Kajian Implementasi Geodatabase untuk Pemetaan Ruang Terbuka Hijau',
                'section' => 'Tata Kelola Smart City',
                'submitted_date' => '-',
                'stage' => 'Pengajuan (Submission)',
                'status_label' => 'Draft',
                'status_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'updated_date' => '03 Okt 2026',
                'action_label' => 'Lanjutkan',
                'action_class' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 border border-gray-300',
                'action_url' => '#',
            ],
        ];
    }

    /**
     * Get recent editorial discussions & communications.
     */
    public function discussions(): array
    {
        return [
            [
                'sender' => 'Prof. Dr. Ir. Hendra Saputra',
                'sender_role' => 'Editor Bagian',
                'submission_id' => 'SUB-2026-00124',
                'subject' => 'Pemberitahuan Hasil Telaah Naskah SUB-2026-00124',
                'snippet' => 'Yth. Dr. Andi Muhammad Ilyas, bersama ini kami sampaikan bahwa...',
                'date' => '18 Sep, 14:20 WITA',
                'is_read' => false,
                'url' => '#',
            ],
            [
                'sender' => 'Sekretariat Redaksi BRIDA',
                'sender_role' => 'Redaksi Pelaksana',
                'submission_id' => 'SUB-2026-00142',
                'subject' => 'Konfirmasi Penerimaan Naskah SUB-2026-00142',
                'snippet' => 'Naskah Anda telah berhasil tercatat dalam sistem OJS kami dan...',
                'date' => '28 Sep, 09:15 WITA',
                'is_read' => true,
                'url' => '#',
            ],
        ];
    }

    /**
     * Get author help & guidelines sidebar resources.
     */
    public function helpResources(): array
    {
        return [
            'email' => 'jurnal.brida@makassarkota.go.id',
            'office_hours' => 'Senin - Jumat: 08.00 - 16.00 WITA',
            'guide_links' => [
                ['title' => 'Checklist Kesiapan Naskah', 'url' => '#'],
                ['title' => 'Etika Publikasi & Plagiasi Turnitin', 'url' => '#'],
            ],
        ];
    }
}
