<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Issue;
use App\Models\IssueArticle;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JournalManagerSeeder extends Seeder
{
    /**
     * Menjalankan seeding komprehensif untuk Portal Journal Manager.
     */
    public function run(): void
    {
        // 1. Inisialisasi Jurnal Utama dengan Metadata Lengkap
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            [
                'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'initials' => 'SIRIDA',
                'issn' => '2746-8194',
                'eissn' => '2746-8208',
                'publisher' => 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
                'editorial_email' => 'jurnal.brida@makassar.go.id',
                'contact_phone' => '+62 411 873-456',
                'focus_scope' => 'Jurnal mempublikasikan naskah ilmiah hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, ketahanan lingkungan pesisir, dan pembangunan daerah terpadu.',
                'frequency' => '2 Kali Setahun (Nomor 1 terbit pada bulan Juni, Nomor 2 terbit pada bulan Desember)',
                'apc_fee' => 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah - Didanai Penuh oleh APBD BRIDA Kota Makassar)',
                'open_access_policy' => 'Jurnal ini menyediakan akses terbuka langsung (Gold Open Access) ke seluruh isi naskah dengan prinsip bahwa membuat hasil penelitian dapat diakses secara bebas oleh publik demi mendukung pertukaran pengetahuan global.',
                'peer_review_policy' => 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan selama proses penelaahan).',
                'license' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
                'status' => 'active',
                'version' => 1,
            ]
        );

        $journal->update([
            'initials' => 'SIRIDA',
            'issn' => '2746-8194',
            'eissn' => '2746-8208',
            'publisher' => 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
            'editorial_email' => 'jurnal.brida@makassar.go.id',
            'contact_phone' => '+62 411 873-456',
            'focus_scope' => 'Jurnal mempublikasikan naskah ilmiah hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, ketahanan lingkungan pesisir, dan pembangunan daerah terpadu.',
            'frequency' => '2 Kali Setahun (Nomor 1 terbit pada bulan Juni, Nomor 2 terbit pada bulan Desember)',
            'apc_fee' => 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah - Didanai Penuh oleh APBD BRIDA Kota Makassar)',
            'open_access_policy' => 'Jurnal ini menyediakan akses terbuka langsung (Gold Open Access) ke seluruh isi naskah dengan prinsip bahwa membuat hasil penelitian dapat diakses secara bebas oleh publik demi mendukung pertukaran pengetahuan global.',
            'peer_review_policy' => 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan selama proses penelaahan).',
            'license' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
        ]);

        // 2. Daftar Pengguna Inti & Peran Jurnal
        $usersData = [
            [
                'email' => 'yusuf.brida@makassar.go.id',
                'username' => 'yusuf.brida',
                'name' => 'Andi Muhammad Yusuf, M.Si.',
                'affiliation' => 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                'roles' => ['manager'],
                'expertise' => ['Kebijakan Publik', 'Manajemen Riset Daerah'],
            ],
            [
                'email' => 'nasaruddin.eic@unhas.ac.id',
                'username' => 'nasaruddin.eic',
                'name' => 'Prof. Dr. Ir. H. Nasaruddin, M.T.',
                'affiliation' => 'Universitas Hasanuddin',
                'roles' => ['editor_in_chief'],
                'expertise' => ['Infrastruktur Perkotaan', 'Teknologi Lingkungan'],
            ],
            [
                'email' => 'prof.hamka@unhas.ac.id',
                'username' => 'prof.hamka',
                'name' => 'Prof. Hamka, Ph.D.',
                'affiliation' => 'Fakultas Ilmu Sosial & Ilmu Politik UNHAS',
                'roles' => ['section_editor'],
                'expertise' => ['Inovasi Pemerintahan', 'Sosial Humaniora'],
            ],
            [
                'email' => 'rohani.editor@unm.ac.id',
                'username' => 'rohani.editor',
                'name' => 'Dr. Hj. St. Rohani, M.Si.',
                'affiliation' => 'Universitas Negeri Makassar',
                'roles' => ['section_editor'],
                'expertise' => ['Pemberdayaan Masyarakat', 'Ekonomi Kerakyatan'],
            ],
            [
                'email' => 'rahmat.hidayat@brida.makassar.go.id',
                'username' => 'rahmat.hidayat',
                'name' => 'Rahmat Hidayat, S.Kom.',
                'affiliation' => 'Bagian Publikasi Ilmiah BRIDA Makassar',
                'roles' => ['layout_editor'],
                'expertise' => ['Tipografi Ilmiah', 'Format Galai PDF/HTML'],
            ],
            [
                'email' => 'andi.kurniawan@brida.makassar.go.id',
                'username' => 'andi.kurniawan',
                'name' => 'Andi Kurniawan, S.Hum., M.Pd.',
                'affiliation' => 'Pusat Bahasa & Budaya Sulawesi Selatan',
                'roles' => ['copyeditor'],
                'expertise' => ['EYD Edisi V', 'Tata Bahasa Akademik'],
            ],
            [
                'email' => 'sitti.aminah@brida.makassar.go.id',
                'username' => 'sitti.aminah',
                'name' => 'Dra. Sitti Aminah, M.Hum.',
                'affiliation' => 'Pusat Bahasa & Budaya Sulawesi Selatan',
                'roles' => ['proofreader'],
                'expertise' => ['Koreksi Galai', 'Tipografi'],
            ],
            [
                'email' => 'budi.hartono@eng.unhas.ac.id',
                'username' => 'budi.hartono',
                'name' => 'Dr. Ir. Budi Hartono, M.Eng.',
                'affiliation' => 'Fakultas Teknik Geologi UNHAS',
                'roles' => ['reviewer'],
                'expertise' => ['Geoteknik Pesisir', 'Mitigasi Abrasi Pantai', 'Mekanika Tanah'],
            ],
            [
                'email' => 'nurfadillah.bahar@unhas.ac.id',
                'username' => 'nurfadillah.bahar',
                'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                'affiliation' => 'Fakultas Kesehatan Masyarakat UNHAS',
                'roles' => ['reviewer', 'author'],
                'expertise' => ['Kesehatan Lingkungan', 'Urban Heat Island', 'Epidemiologi'],
            ],
            [
                'email' => 'andi.ilyas@unhas.ac.id',
                'username' => 'andi.ilyas',
                'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'affiliation' => 'Departemen Ilmu Administrasi Publik UNHAS',
                'roles' => ['author'],
                'expertise' => ['Administrasi Publik', 'Kebijakan Daerah'],
            ],
        ];

        foreach ($usersData as $ud) {
            $user = User::firstOrCreate(
                ['email' => $ud['email']],
                [
                    'username' => $ud['username'],
                    'name' => $ud['name'],
                    'full_name' => $ud['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'affiliation' => $ud['affiliation'],
                    'country_code' => 'ID',
                ]
            );

            foreach ($ud['roles'] as $roleCode) {
                UserRole::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'journal_id' => $journal->id,
                        'role_code' => $roleCode,
                    ],
                    [
                        'expertise_keywords' => ($roleCode === 'reviewer') ? $ud['expertise'] : null,
                    ]
                );
            }
        }

        // 2b. Akun Pengguna Biasa / Global (Belum Terhubung ke Jurnal atau Tenant Manapun)
        $unassignedUsers = [
            [
                'email' => 'fajar.pratama@gmail.com',
                'username' => 'fajar.pratama',
                'name' => 'Fajar Pratama, S.T.',
                'affiliation' => 'Peneliti Terapan & Alumni UNHAS',
            ],
            [
                'email' => 'dewi.lestari@unhas.ac.id',
                'username' => 'dewi.lestari',
                'name' => 'Dr. Dewi Lestari, M.Pd.',
                'affiliation' => 'Fakultas Ilmu Budaya Universitas Hasanuddin',
            ],
            [
                'email' => 'hendra.wijaya@makassar.go.id',
                'username' => 'hendra.wijaya',
                'name' => 'Hendra Wijaya, S.Sos.',
                'affiliation' => 'Dinas Komunikasi dan Informatika Pemerintah Kota Makassar',
            ],
            [
                'email' => 'maya.safitri@brida.makassar.go.id',
                'username' => 'maya.safitri',
                'name' => 'Maya Safitri, S.Si.',
                'affiliation' => 'Badan Riset dan Inovasi Daerah Kota Makassar',
            ],
        ];

        foreach ($unassignedUsers as $uud) {
            $user = User::firstOrCreate(
                ['email' => $uud['email']],
                [
                    'username' => $uud['username'],
                    'name' => $uud['name'],
                    'full_name' => $uud['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'affiliation' => $uud['affiliation'],
                    'country_code' => 'ID',
                ]
            );
            // Catatan: Sengaja tidak dibuatkan entri UserRole agar berstatus pengguna biasa/non-tenant
        }

        // 3. Edisi Terbitan (Issues)
        // Edisi 1: Terbit Resmi (Current Issue: Vol. 6 No. 1, 2026)
        $issueCurrent = Issue::firstOrCreate(
            [
                'journal_id' => $journal->id,
                'volume' => '6',
                'number' => '1',
                'year' => 2026,
            ],
            [
                'title' => 'Vol. 6 No. 1 (2026): Transformasi Digital dan Pembangunan Daerah Berkelanjutan',
                'description' => 'Edisi terbitan berjalan resmi semester pertama tahun 2026 yang memuat riset transformasi digital dan tata kelola lingkungan terpadu.',
                'status' => 'published',
                'is_current' => true,
                'doi_prefix' => '10.61234/sirida.v6i1',
                'published_at' => now()->subMonths(4),
                'version' => 1,
            ]
        );

        $journal->update(['current_issue_id' => $issueCurrent->id]);

        // Edisi 2: Draf Kurasi Naskah (Vol. 6 No. 2, 2026)
        $issueDraft = Issue::firstOrCreate(
            [
                'journal_id' => $journal->id,
                'volume' => '6',
                'number' => '2',
                'year' => 2026,
            ],
            [
                'title' => 'Vol. 6 No. 2 (2026): Inovasi Kebijakan Publik & Ketahanan Perkotaan',
                'description' => 'Edisi reguler semester kedua 2026 yang memuat riset inovasi pelayanan publik dan mitigasi lingkungan perkotaan.',
                'status' => 'draft',
                'is_current' => false,
                'doi_prefix' => '10.61234/sirida.v6i2',
                'scheduled_at' => now()->addDays(30),
                'version' => 1,
            ]
        );

        // Edisi 3: Terbit Arsip (Vol. 5 No. 2, 2025)
        $issueArchive = Issue::firstOrCreate(
            [
                'journal_id' => $journal->id,
                'volume' => '5',
                'number' => '2',
                'year' => 2025,
            ],
            [
                'title' => 'Vol. 5 No. 2 (2025): Tata Kelola Lingkungan dan Adaptasi Perubahan Iklim',
                'description' => 'Edisi reguler kedua tahun 2025.',
                'status' => 'published',
                'is_current' => false,
                'doi_prefix' => '10.61234/sirida.v5i2',
                'published_at' => now()->subMonths(10),
                'version' => 1,
            ]
        );

        // 4. Masukkan Naskah Submisi ke dalam Daftar Isi Edisi
        $allSubmissions = Submission::where('journal_id', $journal->id)->get();

        if ($allSubmissions->isNotEmpty()) {
            // Masukkan artikel pertama ke Current Issue
            $sub1 = $allSubmissions->first();
            IssueArticle::firstOrCreate(
                [
                    'issue_id' => $issueCurrent->id,
                    'submission_id' => $sub1->id,
                ],
                [
                    'position' => 1,
                    'pages' => '1-14',
                    'doi' => '10.61234/sirida.v6i1.01',
                    'version' => 1,
                ]
            );

            // Masukkan artikel kedua dan ketiga ke Draft Issue
            if ($allSubmissions->count() >= 2) {
                $sub2 = $allSubmissions->skip(1)->first();
                IssueArticle::firstOrCreate(
                    [
                        'issue_id' => $issueDraft->id,
                        'submission_id' => $sub2->id,
                    ],
                    [
                        'position' => 1,
                        'pages' => '1-14',
                        'doi' => '10.61234/sirida.v6i2.01',
                        'version' => 1,
                    ]
                );
            }

            if ($allSubmissions->count() >= 3) {
                $sub3 = $allSubmissions->skip(2)->first();
                IssueArticle::firstOrCreate(
                    [
                        'issue_id' => $issueDraft->id,
                        'submission_id' => $sub3->id,
                    ],
                    [
                        'position' => 2,
                        'pages' => '15-28',
                        'doi' => '10.61234/sirida.v6i2.02',
                        'version' => 1,
                    ]
                );
            }
        }

        // 5. Warta Pengumuman Jurnal (Announcements)
        Announcement::firstOrCreate(
            [
                'journal_id' => $journal->id,
                'title' => 'Panggilan Pengajuan Naskah (Call for Papers) Vol. 6 No. 2 Tahun 2026',
            ],
            [
                'body' => 'Redaksi mengundang para akademisi, peneliti, dan praktisi kebijakan daerah untuk mengirimkan naskah hasil riset terbaik bertema Inovasi Kebijakan Publik dan Ketahanan Perkotaan. Batas akhir pengiriman naskah adalah 15 November 2026.',
                'is_published' => true,
                'published_at' => now()->subDays(20),
            ]
        );

        Announcement::firstOrCreate(
            [
                'journal_id' => $journal->id,
                'title' => 'Lokakarya Penulisan Artikel Ilmiah dan Tata Bahasa Akademik',
            ],
            [
                'body' => 'Badan Riset dan Inovasi Daerah (BRIDA) bekerjasama dengan Universitas Hasanuddin menyelenggarakan lokakarya penulisan naskah berkualitas bagi peneliti muda Pemerintah Kota Makassar.',
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ]
        );
    }
}
