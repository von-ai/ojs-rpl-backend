<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Menjalankan seeding akun demo end-to-end SIRIDA (Unhas & BRIDA) dengan password `password123`.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan pada production.');
        }

        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            [
                'name' => 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'initials' => 'SIRIDA',
                'issn' => '2746-8194',
                'eissn' => '2746-8208',
                'publisher' => 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
                'editorial_email' => 'jurnal.brida@makassar.go.id',
                'contact_phone' => '+62 411 873-456',
                'focus_scope' => 'Jurnal mempublikasikan naskah ilmiah hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, ketahanan lingkungan pesisir, dan pembangunan daerah terpadu.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        $passwordHash = Hash::make('password123');

        // Akun System Administrator (Tahap 1)
        $sysAdmin = User::updateOrCreate(
            ['email' => 'fahri.azis@makassar.go.id'],
            [
                'username' => 'fahri.azis',
                'name' => 'Fahri Azis, S.T.',
                'full_name' => 'Fahri Azis, S.T.',
                'password' => $passwordHash,
                'email_verified_at' => now(),
                'status' => 'active',
                'version' => 1,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $sysAdmin->id],
            [
                'affiliation' => 'Bidang Aplikasi Informatika & Inovasi BRIDA Makassar',
                'country_code' => 'ID',
            ]
        );

        UserRole::firstOrCreate(
            [
                'user_id' => $sysAdmin->id,
                'journal_id' => null,
                'role_code' => 'sysadmin',
            ],
            [
                'expertise_keywords' => ['Superadmin Platform', 'Sistem Infrastruktur IT'],
            ]
        );

        // 9 Akun Demo End-to-End (Universitas Hasanuddin & BRIDA Kota Makassar)
        $demoAccounts = [
            [
                'email' => 'yusuf.manager.test@makassar.go.id',
                'username' => 'yusuf.manager.test',
                'name' => 'Andi Muhammad Yusuf, M.Si.',
                'affiliation' => 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                'role_code' => 'manager',
                'expertise' => ['Manajemen Riset Daerah', 'Kebijakan Publik'],
            ],
            [
                'email' => 'indrabayu.test@unhas.ac.id',
                'username' => 'indrabayu.test',
                'name' => 'Prof. Dr. Ir. Indrabayu, S.T., M.T., M.Bus.Sys.',
                'affiliation' => 'Departemen Teknik Informatika, Fakultas Teknik, Universitas Hasanuddin',
                'role_code' => 'editor_in_chief',
                'expertise' => ['Artificial Intelligence', 'Computer Vision', 'Smart City'],
            ],
            [
                'email' => 'amil.test@unhas.ac.id',
                'username' => 'amil.test',
                'name' => 'Prof. Dr. Amil Ahmad Ilham, S.T., M.IT.',
                'affiliation' => 'Departemen Teknik Informatika, Fakultas Teknik, Universitas Hasanuddin',
                'role_code' => 'section_editor',
                'expertise' => ['Cloud Computing', 'Big Data', 'Transformasi Digital'],
            ],
            [
                'email' => 'adnan.test@unhas.ac.id',
                'username' => 'adnan.test',
                'name' => 'Prof. Dr. Adnan, S.T., M.T.',
                'affiliation' => 'Departemen Teknik Informatika, Fakultas Teknik, Universitas Hasanuddin',
                'role_code' => 'reviewer',
                'expertise' => ['Sistem Komputer', 'Internet of Things', 'Komputasi Paralel'],
            ],
            [
                'email' => 'intan.test@unhas.ac.id',
                'username' => 'intan.test',
                'name' => 'Prof. Dr. Eng. Intan Sari Areni, S.T., M.T.',
                'affiliation' => 'Departemen Teknik Elektro, Fakultas Teknik, Universitas Hasanuddin',
                'role_code' => 'reviewer',
                'expertise' => ['Pengolahan Sinyal Digital', 'Telekomunikasi', 'Smart City'],
            ],
            [
                'email' => 'armin.test@unhas.ac.id',
                'username' => 'armin.test',
                'name' => 'Prof. Dr. Eng. Armin Lawi, S.Si., M.Eng.',
                'affiliation' => 'Departemen Statistika & Sistem Informasi, FMIPA, Universitas Hasanuddin',
                'role_code' => 'copyeditor',
                'expertise' => ['Tata Bahasa Akademik', 'Statistika Komputasi'],
            ],
            [
                'email' => 'jeffry.test@unhas.ac.id',
                'username' => 'jeffry.test',
                'name' => 'Prof. Dr. Jeffry Kusuma',
                'affiliation' => 'Program Studi Sistem Informasi, FMIPA, Universitas Hasanuddin',
                'role_code' => 'layout_editor',
                'expertise' => ['Tipografi Ilmiah', 'Tata Letak Galai PDF'],
            ],
            [
                'email' => 'sultan.test@unhas.ac.id',
                'username' => 'sultan.test',
                'name' => 'Dr. Sultan, S.E., M.Si.',
                'affiliation' => 'Pusat Pengembangan Kebijakan Pembangunan, LPPM, Universitas Hasanuddin',
                'role_code' => 'proofreader',
                'expertise' => ['Koreksi Bukti Baca Galai', 'Kebijakan Pembangunan'],
            ],
            [
                'email' => 'niswar.test@unhas.ac.id',
                'username' => 'niswar.test',
                'name' => 'Prof. Dr. Eng. Muhammad Niswar, S.T., M.IT.',
                'affiliation' => 'Departemen Teknik Informatika, Fakultas Teknik, Universitas Hasanuddin',
                'role_code' => 'author',
                'expertise' => ['Internet of Things', 'Jaringan Sensor Nirkabel', 'Smart City'],
            ],
        ];

        foreach ($demoAccounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'username' => $account['username'],
                    'name' => $account['name'],
                    'full_name' => $account['name'],
                    'password' => $passwordHash,
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'version' => 1,
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'affiliation' => $account['affiliation'],
                    'country_code' => 'ID',
                ]
            );

            UserRole::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'journal_id' => $journal->id,
                    'role_code' => $account['role_code'],
                ],
                [
                    'expertise_keywords' => $account['expertise'],
                ]
            );
        }
    }
}
