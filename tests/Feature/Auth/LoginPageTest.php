<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Halaman login dapat diakses dengan respons 200 OK.
     */
    public function test_halaman_login_dapat_diakses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('BRIDA Makassar');
        $response->assertSee('Masuk ke portal jurnal');
        $response->assertSee('Riset dan inovasi untuk Kota Makassar.');
    }

    /**
     * Halaman utama dapat diakses dengan respons 200 OK.
     */
    public function test_halaman_utama_dapat_diakses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Pengguna dapat login dengan email dan password yang valid.
     */
    public function test_pengguna_dapat_login_dengan_kredensial_valid(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    /**
     * Pengguna tidak dapat login dengan password yang salah.
     */
    public function test_pengguna_gagal_login_dengan_kata_sandi_salah(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'salah-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Percobaan login terkunci (rate limited) setelah 5 kali gagal.
     */
    public function test_login_terkunci_jika_terlalu_banyak_percobaan_gagal(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password-salah',
            ]);
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Pengguna dapat login dengan username dan password yang valid.
     */
    public function test_pengguna_dapat_login_dengan_username_yang_valid(): void
    {
        $user = User::factory()->create([
            'username' => 'penulis_hebat',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => 'penulis_hebat',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    /**
     * Pengguna gagal login jika email belum diverifikasi.
     */
    public function test_pengguna_gagal_login_jika_email_belum_diverifikasi(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    /**
     * Pengguna gagal login jika akun nonaktif atau disabled.
     */
    public function test_pengguna_gagal_login_jika_akun_dinonaktifkan(): void
    {
        $user = User::factory()->disabled()->create([
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    /**
     * Lima kegagalan login berturut-turut mencatat counter dan lockout pada tabel database users.
     */
    public function test_kegagalan_login_mencatat_counter_dan_lockout_di_database(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => $user->email,
                'password' => 'salah-terus',
            ]);
        }

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_count);
        $this->assertEquals('locked', $user->status);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());

        // Memastikan outbox email keamanan terbit
        $this->assertDatabaseHas('outbox_messages', [
            'type' => 'security.account_locked_email',
        ]);
    }

    /**
     * Pengguna dapat logout dari aplikasi.
     */
    public function test_pengguna_dapat_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
