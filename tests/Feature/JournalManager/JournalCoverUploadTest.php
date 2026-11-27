<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JournalCoverUploadTest extends TestCase
{
    use RefreshDatabase;

    private Journal $journal;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $this->journal = Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'initials' => 'SIRIDA',
            'issn' => '2746-8194',
            'eissn' => '2746-8208',
            'publisher' => 'BRIDA Kota Makassar',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->manager = User::factory()->create([
            'email' => 'manager.cover@makassar.go.id',
            'name' => 'Manajer Jurnal Cover',
            'status' => 'active',
        ]);

        UserRole::create([
            'user_id' => $this->manager->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'manager',
        ]);
    }

    public function test_journal_manager_can_upload_serve_and_delete_journal_cover(): void
    {
        $png = UploadedFile::fake()->image('sampul-sirida-2026.png', 600, 800);

        $uploadResponse = $this->actingAs($this->manager)
            ->withSession(['active_role' => 'manager', 'active_journal_id' => $this->journal->id])
            ->post(route('journalmanager.settings.cover.upload'), [
                'cover_image' => $png,
            ]);

        $uploadResponse->assertRedirect(route('journalmanager.settings.index'));
        $uploadResponse->assertSessionHas('status');

        $this->journal->refresh();
        $this->assertTrue($this->journal->hasCoverImage());
        $this->assertSame('sampul-sirida-2026.png', $this->journal->cover_image_filename);
        Storage::disk('local')->assertExists((string) $this->journal->cover_image_path);

        // Beranda publik menampilkan tag img sampul jurnal yang diunggah
        $homeResponse = $this->get(route('journal.index'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('Sampul '.$this->journal->name);
        $homeResponse->assertSee('/sirida-makassar/cover');

        // Endpoint sampul publik menyajikan gambar dengan status 200
        $coverResponse = $this->get(route('journal.scoped.cover', ['journalSlug' => $this->journal->slug]));
        $coverResponse->assertOk();

        // Hapus sampul jurnal
        $oldPath = (string) $this->journal->cover_image_path;
        $deleteResponse = $this->actingAs($this->manager)
            ->withSession(['active_role' => 'manager', 'active_journal_id' => $this->journal->id])
            ->delete(route('journalmanager.settings.cover.destroy'));

        $deleteResponse->assertRedirect(route('journalmanager.settings.index'));

        $this->journal->refresh();
        $this->assertFalse($this->journal->hasCoverImage());
        Storage::disk('local')->assertMissing($oldPath);

        // Setelah dihapus, endpoint cover mengembalikan 404 dan beranda tetap tampil normal
        $this->get(route('journal.scoped.cover', ['journalSlug' => $this->journal->slug]))->assertNotFound();
        $this->get(route('journal.index'))->assertOk();
    }

    public function test_journal_manager_cannot_upload_non_image_as_cover(): void
    {
        $pdf = UploadedFile::fake()->create('bukan-gambar.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->manager)
            ->withSession(['active_role' => 'manager', 'active_journal_id' => $this->journal->id])
            ->post(route('journalmanager.settings.cover.upload'), [
                'cover_image' => $pdf,
            ]);

        $response->assertSessionHasErrors('cover_image');
        $this->journal->refresh();
        $this->assertFalse($this->journal->hasCoverImage());
    }

    public function test_non_manager_cannot_upload_journal_cover(): void
    {
        $author = User::factory()->create(['status' => 'active']);
        UserRole::create([
            'user_id' => $author->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'author',
        ]);

        $png = UploadedFile::fake()->image('unauthorized.png', 300, 400);

        $response = $this->actingAs($author)
            ->withSession(['active_role' => 'author', 'active_journal_id' => $this->journal->id])
            ->post(route('journalmanager.settings.cover.upload'), [
                'cover_image' => $png,
            ]);

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));
        $this->journal->refresh();
        $this->assertFalse($this->journal->hasCoverImage());
    }
}
