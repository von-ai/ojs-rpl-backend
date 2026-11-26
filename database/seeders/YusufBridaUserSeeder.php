<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class YusufBridaUserSeeder extends Seeder
{
    /**
     * Seed user Andi Muhammad Yusuf, M.Si. (Manajer Jurnal & Administrator Sistem).
     */
    public function run(): void
    {
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            [
                'name' => 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'initials' => 'SIRIDA',
                'issn' => '2746-8194',
                'eissn' => '2746-8208',
                'publisher' => 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
                'editorial_email' => 'jurnal.brida@makassar.go.id',
                'status' => 'active',
                'version' => 1,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'yusuf.brida@makassar.go.id'],
            [
                'username' => 'yusuf.brida',
                'name' => 'Andi Muhammad Yusuf, M.Si.',
                'full_name' => 'Andi Muhammad Yusuf, M.Si.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'locked_until' => null,
                'failed_login_count' => 0,
                'last_failed_login_at' => null,
                'version' => 1,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'affiliation' => 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                'country_code' => 'ID',
            ]
        );

        // Peran Manajer Jurnal (scoped ke jurnal utama)
        UserRole::firstOrCreate(
            [
                'user_id' => $user->id,
                'journal_id' => $journal->id,
                'role_code' => 'manager',
            ],
            [
                'expertise_keywords' => ['Kebijakan Publik', 'Manajemen Riset Daerah'],
            ]
        );

        // Peran Administrator Sistem (global, journal_id = null)
        UserRole::firstOrCreate(
            [
                'user_id' => $user->id,
                'journal_id' => null,
                'role_code' => 'sysadmin',
            ],
            [
                'expertise_keywords' => ['Administrator Sistem', 'Tata Kelola Multi-Jurnal'],
            ]
        );
    }
}
