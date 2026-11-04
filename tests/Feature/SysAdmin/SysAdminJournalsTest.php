<?php

declare(strict_types=1);

namespace Tests\Feature\SysAdmin;

use App\Models\Journal;
use App\Models\User;
use Database\Seeders\SysAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SysAdminJournalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SysAdminSeeder::class);

        $admin = User::where('email', 'fahri.azis@makassar.go.id')->firstOrFail();
        $this->actingAs($admin);
    }

    public function test_tamu_tidak_bisa_mengakses_portal_sysadmin_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/sysadmin/journals');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman manajemen jurnal memuat 4 ringkasan metrik statistik.
     */
    public function test_halaman_jurnal_memuat_ringkasan_metrik_multi_tenancy(): void
    {
        $response = $this->get('/sysadmin/journals');

        $response->assertStatus(200);
        $response->assertSee('Total Jurnal Ilmiah');
        $response->assertSee('Jurnal Aktif');
        $response->assertSee('Edisi Terbit Berjalan');
        $response->assertSee('Jurnal Diarsipkan');
        $response->assertSee('Fahri Azis, S.T.');
    }

    /**
     * Filter tab status jurnal menyaring daftar instansi jurnal dengan benar.
     */
    public function test_filter_tab_status_jurnal_berfungsi(): void
    {
        // 1. Tab Aktif Publikasi
        $responseActive = $this->get('/sysadmin/journals?status=active');
        $responseActive->assertStatus(200);
        $responseActive->assertSee('JTIIK');
        $responseActive->assertSee('JIKPM');
        $responseActive->assertDontSee('Buletin Riset Litbang Kota Makassar');

        // 2. Tab Diarsipkan
        $responseArchived = $this->get('/sysadmin/journals?status=archived');
        $responseArchived->assertStatus(200);
        $responseArchived->assertSee('Buletin Riset Litbang Kota Makassar');
        $responseArchived->assertDontSee('JTIIK');
    }

    /**
     * Pencarian jurnal berdasarkan nama atau slug berfungsi.
     */
    public function test_pencarian_jurnal_berfungsi_sesuai_kata_kunci(): void
    {
        // Pencarian spesifik
        $responseSearch = $this->get('/sysadmin/journals?q=Pesisir');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('JRLMBP');
        $responseSearch->assertDontSee('JTIIK');

        // Pencarian tanpa hasil menampilkan empty state
        $responseEmpty = $this->get('/sysadmin/journals?q=JurnalYangTidakPernahAda123');
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertSee('Tidak ada instansi jurnal');
        $responseEmpty->assertSee('Reset Filter');
    }

    /**
     * Pendaftaran jurnal baru dan pembaruan status berjalan sukses.
     */
    public function test_pendaftaran_jurnal_dan_pembaruan_status_berhasil(): void
    {
        // Simpan jurnal
        $responseStore = $this->post('/sysadmin/journals', [
            'name' => 'Jurnal Sains Terapan Makassar',
            'slug' => 'jstm-makassar',
            'primary_contact' => 'Dr. H. Rusdianto, M.Si.',
        ]);
        $responseStore->assertRedirect(route('sysadmin.journals.index'));
        $responseStore->assertSessionHas('status');

        // Ubah status jurnal
        $responseStatus = $this->post('/sysadmin/journals/'.Journal::where('slug', 'jtiik-makassar')->firstOrFail()->id.'/status', [
            'status' => 'archived',
        ]);
        $responseStatus->assertRedirect(route('sysadmin.journals.index'));
        $responseStatus->assertSessionHas('status');
    }

    /**
     * Pendaftaran jurnal baru dengan akun PIC otomatis memberikan hak akses Journal Manager.
     */
    public function test_pendaftaran_jurnal_dengan_akun_pic_menjadikan_journal_manager(): void
    {
        $picUser = User::where('email', 'rita.irmawaty@unhas.ac.id')->firstOrFail();

        $responseStore = $this->post('/sysadmin/journals', [
            'name' => 'Jurnal Rekayasa Maritim Terpadu',
            'slug' => 'jrmt-makassar',
            'primary_contact' => $picUser->name,
            'manager_user_id' => $picUser->id,
        ]);
        $responseStore->assertRedirect(route('sysadmin.journals.index'));
        $responseStore->assertSessionHas('status');

        $journal = Journal::where('slug', 'jrmt-makassar')->firstOrFail();

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $picUser->id,
            'journal_id' => $journal->id,
            'role_code' => 'manager',
        ]);
    }
}
