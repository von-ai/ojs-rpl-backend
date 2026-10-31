<?php

declare(strict_types=1);

namespace Tests\Feature\SysAdmin;

use App\Models\Journal;
use App\Models\User;
use Database\Seeders\SysAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SysAdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SysAdminSeeder::class);
    }

    /**
     * Halaman direktori pengguna memuat 4 ringkasan metrik statistik.
     */
    public function test_halaman_users_memuat_ringkasan_metrik_pengguna(): void
    {
        $response = $this->get('/sysadmin/users');

        $response->assertStatus(200);
        $response->assertSee('Total Akun Pengguna');
        $response->assertSee('System Administrator');
        $response->assertSee('Dewan Redaksi Aktif');
        $response->assertSee('Reviewer Terdaftar');
    }

    /**
     * Filter tab peran pengguna berfungsi menyaring akun dengan benar.
     */
    public function test_filter_tab_peran_pengguna_berfungsi(): void
    {
        // 1. Tab SysAdmin
        $responseSysadmin = $this->get('/sysadmin/users?role=sysadmin');
        $responseSysadmin->assertStatus(200);
        $responseSysadmin->assertSee('Fahri Azis, S.T.');
        $responseSysadmin->assertSee('Muh. Ilham, S.Kom., M.T.');
        $responseSysadmin->assertDontSee('Dra. Sitti Aminah, M.Hum.');

        // 2. Tab Penulis (author)
        $responseAuthor = $this->get('/sysadmin/users?role=author');
        $responseAuthor->assertStatus(200);
        $responseAuthor->assertSee('Dr. Andi Nurul Hidayah, M.Si.');
        $responseAuthor->assertDontSee('fahri.azis@makassar.go.id');
    }

    /**
     * Pencarian pengguna berdasarkan nama atau email berfungsi.
     */
    public function test_pencarian_pengguna_berfungsi_sesuai_kata_kunci(): void
    {
        $responseSearch = $this->get('/sysadmin/users?q=rita.irmawaty');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.');
        $responseSearch->assertDontSee('fahri.azis@makassar.go.id');
    }

    /**
     * Panel riwayat pengimporan CSV menampilkan data tabel user_imports.
     */
    public function test_riwayat_impor_pengguna_csv_ditampilkan(): void
    {
        $response = $this->get('/sysadmin/users');

        $response->assertStatus(200);
        $response->assertSee('IMPORT-01JARK-01');
        $response->assertSee('Reviewer_Kepakaran_SmartCity_2026.csv');
        $response->assertSee('35 baris');
        $response->assertSee('Selesai Sukses');
    }

    /**
     * Aksi penetapan peran sysadmin dan impor CSV berhasil mengembalikan redirect.
     */
    public function test_aksi_penetapan_sysadmin_dan_impor_berhasil(): void
    {
        // Jadikan SysAdmin
        $resAssign = $this->post('/sysadmin/users/assign-sysadmin', [
            'user_id' => 'USER-01JARK-03',
        ]);
        $resAssign->assertRedirect(route('sysadmin.users.index'));
        $resAssign->assertSessionHas('status');

        // Cabut SysAdmin
        $resRevoke = $this->post('/sysadmin/users/revoke-sysadmin', [
            'user_id' => 'USER-01JARK-02',
        ]);
        $resRevoke->assertRedirect(route('sysadmin.users.index'));
        $resRevoke->assertSessionHas('status');

        // Unggah CSV impor
        $resImport = $this->post('/sysadmin/users/import', [
            'journal_id' => 'JOURNAL-01JARK-01',
        ]);
        $resImport->assertRedirect(route('sysadmin.users.index'));
        $resImport->assertSessionHas('status');
    }

    /**
     * SysAdmin dapat menetapkan peran jurnal dan mencabut peran pengguna.
     */
    public function test_sysadmin_bisa_menetapkan_dan_mencabut_peran_jurnal_pengguna(): void
    {
        $user = User::where('email', 'rita.irmawaty@unhas.ac.id')->firstOrFail();
        $journal = Journal::where('slug', 'jtiik-makassar')->firstOrFail();

        // 1. Tetapkan peran Journal Manager
        $resAssign = $this->post('/sysadmin/users/assign-journal-role', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role_code' => 'manager',
        ]);
        $resAssign->assertRedirect(route('sysadmin.users.index'));
        $resAssign->assertSessionHas('status');

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role_code' => 'manager',
        ]);

        // 2. Cabut peran Journal Manager
        $resRevoke = $this->post('/sysadmin/users/revoke-journal-role', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role_code' => 'manager',
        ]);
        $resRevoke->assertRedirect(route('sysadmin.users.index'));
        $resRevoke->assertSessionHas('status');

        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role_code' => 'manager',
        ]);
    }
}
