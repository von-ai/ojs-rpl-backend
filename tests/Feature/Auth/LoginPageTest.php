<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

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
