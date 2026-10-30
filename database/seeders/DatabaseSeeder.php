<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Author User (Dr. Andi Muhammad Ilyas)
        User::firstOrCreate(
            ['email' => 'andi.ilyas@unhas.ac.id'],
            [
                'username' => 'andi.ilyas',
                'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'full_name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            JournalSeeder::class,
            SectionSeeder::class,
            SubmissionSeeder::class,
            CopyeditSeeder::class,
            LayoutEditorSeeder::class,
            JournalManagerSeeder::class,
        ]);
    }
}
