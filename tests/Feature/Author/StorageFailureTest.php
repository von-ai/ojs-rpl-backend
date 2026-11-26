<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\SectionSeeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToWriteFile;
use Tests\TestCase;

class StorageFailureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kegagalan menulis ke object storage (mis. container tidak ada) tidak boleh menjadi error 500.
     */
    public function test_kegagalan_storage_saat_submit_mengembalikan_pesan_tanpa_menyimpan_naskah(): void
    {
        $this->seed(SectionSeeder::class);
        $this->actingAs(User::factory()->create());
        $this->breakStorageWrites();

        Exceptions::fake();

        $response = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Naskah Uji Kegagalan Penyimpanan',
            'abstract' => 'Abstrak naskah uji.',
            'keywords' => ['Uji'],
            'manuscript_file' => UploadedFile::fake()->create('naskah.pdf', 100, 'application/pdf'),
            'contributors' => [[
                'given_name' => 'Budi',
                'email' => 'budi@example.test',
                'affiliation' => 'BRIDA',
                'corresponding' => true,
            ]],
        ]);

        $response->assertRedirect(route('author.submissions.create'))->assertSessionHas('error');
        Exceptions::assertReported(UnableToWriteFile::class);
        $this->assertDatabaseCount('submissions', 0);
        $this->assertDatabaseCount('files', 0);
    }

    /**
     * Jalur revisi punya `catch` sendiri; pesan mentah storage tidak boleh sampai ke pengguna.
     */
    public function test_kegagalan_storage_saat_revisi_tidak_membocorkan_pesan_internal(): void
    {
        $this->seed(SectionSeeder::class);
        $author = User::factory()->create();
        $this->actingAs($author);
        $section = Section::where('slug', 'sec-riset-orisinil')->orWhere('id', 'sec-riset-orisinil')->firstOrFail();
        $submission = Submission::create([
            'journal_id' => $section->journal_id,
            'section_id' => $section->id,
            'author_id' => $author->id,
            'submission_number' => 'SUB-2026-STORAGE',
            'title' => 'Naskah Uji Revisi',
            'abstract' => 'Abstrak naskah uji.',
            'status' => 'revisions_requested',
            'version' => 1,
        ]);
        $this->breakStorageWrites();
        Exceptions::fake();

        $this->from(route('author.submissions.show', $submission->id))
            ->post(route('author.submissions.revisions.store', $submission->id), [
                'manuscript_file' => UploadedFile::fake()->create('revisi.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('author.submissions.show', $submission->id))
            ->assertSessionHas('error', fn (string $message) => str_starts_with($message, 'Penyimpanan berkas'));

        Exceptions::assertReported(UnableToWriteFile::class);
        $this->assertDatabaseCount('revisions', 0);
    }

    private function breakStorageWrites(): void
    {
        $adapter = new class(sys_get_temp_dir()) extends LocalFilesystemAdapter
        {
            public function writeStream(string $path, $contents, Config $config): void
            {
                throw UnableToWriteFile::atLocation($path, 'The specified container does not exist.');
            }
        };

        // Memakai konfigurasi disk asli agar opsi `throw` ikut teruji.
        Storage::set('azure', new FilesystemAdapter(new Filesystem($adapter), $adapter, config('filesystems.disks.azure')));
    }
}
