<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_accounts_seeder_creates_all_demo_users_and_roles_idempotently(): void
    {
        $this->seed(DemoAccountsSeeder::class);
        $this->seed(DemoAccountsSeeder::class);

        $journal = Journal::where('slug', 'sirida-makassar')->firstOrFail();

        $sysAdmin = User::where('email', 'fahri.azis@makassar.go.id')->first();
        $this->assertNotNull($sysAdmin);
        $this->assertTrue(Hash::check('password123', $sysAdmin->getAuthPassword()));
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $sysAdmin->id,
            'journal_id' => null,
            'role_code' => 'sysadmin',
        ]);

        $expectedRoles = [
            'yusuf.manager.test@makassar.go.id' => 'manager',
            'indrabayu.test@unhas.ac.id' => 'editor_in_chief',
            'amil.test@unhas.ac.id' => 'section_editor',
            'adnan.test@unhas.ac.id' => 'reviewer',
            'intan.test@unhas.ac.id' => 'reviewer',
            'armin.test@unhas.ac.id' => 'copyeditor',
            'jeffry.test@unhas.ac.id' => 'layout_editor',
            'sultan.test@unhas.ac.id' => 'proofreader',
            'niswar.test@unhas.ac.id' => 'author',
        ];

        foreach ($expectedRoles as $email => $roleCode) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "Akun demo {$email} tidak ditemukan.");
            $this->assertSame('active', $user->status);
            $this->assertNotNull($user->email_verified_at);
            $this->assertTrue(Hash::check('password123', $user->getAuthPassword()));
            $this->assertDatabaseHas('user_profiles', [
                'user_id' => $user->id,
                'country_code' => 'ID',
            ]);
            $this->assertSame(1, UserRole::where('user_id', $user->id)
                ->where('journal_id', $journal->id)
                ->where('role_code', $roleCode)
                ->count());
        }
    }
}
