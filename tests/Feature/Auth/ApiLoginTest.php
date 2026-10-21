<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * POST /api/v1/auth/login berhasil dan mengembalikan token bearer serta profil sesuai OpenAPI AuthTokenResponse.
     */
    public function test_login_berhasil_mengembalikan_token_bearer_dan_user_resource(): void
    {
        $user = User::factory()->create([
            'email' => 'author@makassarkota.go.id',
            'username' => 'ahmad_ridwan',
            'full_name' => 'Dr. Ahmad Ridwan',
            'password' => 'PasswordKuat123!',
            'status' => 'active',
            'email_verified_at' => now(),
            'version' => 1,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'affiliation' => 'Universitas Hasanuddin',
            'country_code' => 'ID',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'author@makassarkota.go.id',
            'password' => 'PasswordKuat123!',
        ]);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));

        $response->assertJsonStructure([
            'data' => [
                'tokens' => [
                    'token_type',
                    'access_token',
                    'access_token_expires_at',
                    'refresh_token',
                    'refresh_token_expires_at',
                ],
                'user' => [
                    'id',
                    'type',
                    'attributes' => [
                        'full_name',
                        'email',
                        'username',
                        'affiliation',
                        'status',
                        'email_verified',
                        'version',
                    ],
                ],
                'memberships',
                'system_roles',
            ],
            'meta' => [
                'request_id',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'tokens' => [
                    'token_type' => 'Bearer',
                ],
                'user' => [
                    'id' => $user->id,
                    'type' => 'user',
                    'attributes' => [
                        'full_name' => 'Dr. Ahmad Ridwan',
                        'email' => 'author@makassarkota.go.id',
                        'username' => 'ahmad_ridwan',
                        'affiliation' => 'Universitas Hasanuddin',
                        'status' => 'active',
                        'email_verified' => true,
                        'version' => 1,
                    ],
                ],
                'memberships' => [],
                'system_roles' => [],
            ],
        ]);

        // Verifikasi persistensi sesi dan refresh token di DB
        $this->assertDatabaseHas('auth_sessions', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('refresh_tokens', [
            'revoked_at' => null,
        ]);

        // Verifikasi audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user.login.success',
        ]);
    }

    /**
     * Login REST API menggunakan alias username berhasil.
     */
    public function test_login_api_dengan_username_berhasil(): void
    {
        $user = User::factory()->create([
            'username' => 'penulis_makassar',
            'password' => 'PasswordKuat123!',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'penulis_makassar',
            'password' => 'PasswordKuat123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.attributes.username', 'penulis_makassar');
    }

    /**
     * Kredensial tidak cocok mengembalikan HTTP 401 dengan format RFC 7807 Problem Details.
     */
    public function test_login_api_gagal_kredensial_salah_mengembalikan_401_problem(): void
    {
        $user = User::factory()->create([
            'password' => 'PasswordBenar123!',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'PasswordSalah123!',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $response->assertJson([
            'status' => 401,
            'code' => 'INVALID_CREDENTIALS',
            'instance' => '/api/v1/auth/login',
        ]);
    }

    /**
     * Akun yang belum diverifikasi ditolak dengan HTTP 401 EMAIL_NOT_VERIFIED.
     */
    public function test_login_api_gagal_jika_email_belum_diverifikasi(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => 'PasswordBenar123!',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'PasswordBenar123!',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 401,
            'code' => 'EMAIL_NOT_VERIFIED',
        ]);
    }

    /**
     * Akun yang dinonaktifkan ditolak dengan HTTP 401 ACCOUNT_INACTIVE.
     */
    public function test_login_api_gagal_jika_status_tidak_aktif(): void
    {
        $user = User::factory()->disabled()->create([
            'password' => 'PasswordBenar123!',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'PasswordBenar123!',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 401,
            'code' => 'ACCOUNT_INACTIVE',
        ]);
    }

    /**
     * Lima kegagalan berturut-turut mengunci akun 15 menit dan mengembalikan HTTP 423 dengan Retry-After.
     */
    public function test_login_api_5_kali_gagal_mengembalikan_423_locked_dengan_retry_after(): void
    {
        $user = User::factory()->create([
            'password' => 'PasswordBenar123!',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'login' => $user->email,
                'password' => 'Salah!',
            ]);
        }

        // Percobaan ke-5 memicu penguncian
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'Salah!',
        ]);

        $response->assertStatus(423);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $this->assertNotEmpty($response->headers->get('Retry-After'));

        $response->assertJson([
            'status' => 423,
            'code' => 'ACCOUNT_LOCKED',
        ]);

        // Verifikasi database terkunci
        $user->refresh();
        $this->assertEquals(5, $user->failed_login_count);
        $this->assertEquals('locked', $user->status);
        $this->assertNotNull($user->locked_until);
    }

    /**
     * Validasi input kosong mengembalikan HTTP 422 VALIDATION_ERROR sesuai schema OpenAPI.
     */
    public function test_login_api_validasi_input_kosong_mengembalikan_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 422,
            'code' => 'VALIDATION_ERROR',
        ]);
        $response->assertJsonCount(2, 'errors');
    }
}
