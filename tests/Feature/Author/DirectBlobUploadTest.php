<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\FileEntry;
use App\Models\User;
use Database\Seeders\SectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectBlobUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::forgetDisk('azure');

        $this->withoutVite();

        $this->user = User::factory()->create([
            'name' => 'Prof. Dr. Ir. Budi Hartono, M.Eng.',
            'email' => 'budi.hartono@makassar.go.id',
        ]);

        $this->seed(SectionSeeder::class);
        $this->actingAs($this->user);
    }

    /**
     * Penulis dapat meminta sesi unggah langsung (Direct SAS Upload) ke Azure Blob Storage.
     * Spesifikasi: SRS NF-01, integration-guide.md Bagian 4.
     */
    public function test_author_dapat_membuat_sesi_upload_sas_ke_azure_blob(): void
    {
        $response = $this->postJson(route('author.submissions.upload-session'), [
            'filename' => 'naskah-riset-inovasi-daerah.docx',
            'byte_size' => 4500000,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'upload_url',
            'headers' => [
                'x-ms-blob-type',
            ],
            'blob_path',
            'file_name',
            'byte_size',
            'mime_type',
            'expires_at',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertStringStartsWith('staging/manuscripts/', $data['blob_path']);
        $this->assertStringContainsString('naskah-riset-inovasi-daerah.docx', $data['blob_path']);
        $this->assertSame('BlockBlob', $data['headers']['x-ms-blob-type']);
        $this->assertStringContainsString('sp=c&', $data['upload_url']);
        $this->assertStringContainsString('sig=', $data['upload_url']);
    }

    /**
     * Sesi unggah menolak berkas dengan ekstensi terlarang atau ukuran melebihi batas 20 MB.
     */
    public function test_sesi_upload_menolak_berkas_ilegal_dan_melebihi_20_mb(): void
    {
        // 1. Ekstensi terlarang (.exe)
        $responseExt = $this->postJson(route('author.submissions.upload-session'), [
            'filename' => 'script-bahaya.exe',
            'byte_size' => 102400,
            'mime_type' => 'application/x-msdownload',
        ]);
        $responseExt->assertUnprocessable();
        $responseExt->assertJsonValidationErrors(['filename']);

        // 2. Ukuran melebihi 20 MB (25 MB)
        $responseSize = $this->postJson(route('author.submissions.upload-session'), [
            'filename' => 'berkas-raksasa.pdf',
            'byte_size' => 26214400, // 25 MB
            'mime_type' => 'application/pdf',
        ]);
        $responseSize->assertUnprocessable();
        $responseSize->assertJsonValidationErrors(['byte_size']);
    }

    /**
     * Tamu tanpa autentikasi tidak dapat membuat sesi upload SAS.
     */
    public function test_tamu_tidak_bisa_membuat_sesi_upload(): void
    {
        Auth::logout();

        $response = $this->postJson(route('author.submissions.upload-session'), [
            'filename' => 'naskah.pdf',
            'byte_size' => 102400,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Pengajuan naskah final berhasil disimpan menggunakan metadata blob_path hasil Direct SAS Upload.
     */
    public function test_pengajuan_naskah_berhasil_menggunakan_blob_path_direct_sas(): void
    {
        $bytes = "%PDF-1.4\n% Manuskrip uji\n%%EOF";
        $path = $this->postJson(route('author.submissions.upload-session'), [
            'filename' => 'naskah-riset.pdf', 'byte_size' => strlen($bytes),
        ])->assertOk()->json('blob_path');
        Storage::fake('azure');

        $payload = [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Optimalisasi Pengelolaan Sampah Terpadu Berbasis IoT di Kota Makassar',
            'abstract' => 'Penelitian ini mendesain arsitektur sensor pintar untuk tempat pembuangan akhir di Makassar guna efisiensi logistik armada kebersihan daerah.',
            'keywords' => ['Smart City', 'Pengelolaan Sampah', 'IoT', 'Makassar'],
            'subject_area' => 'Teknologi Lingkungan Perkotaan',
            'blob_path' => $path,
            'blob_name' => 'naskah-riset.pdf',
            'blob_size' => 3145728,
            'blob_mime' => 'application/pdf',
            'blob_checksum' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            'contributors' => [
                [
                    'given_name' => 'Budi',
                    'family_name' => 'Hartono',
                    'email' => 'budi.hartono@makassar.go.id',
                    'affiliation' => 'BRIDA Kota Makassar',
                    'corresponding' => true,
                ],
            ],
        ];

        $this->postJson(route('author.submissions.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('manuscript_file');
        $this->assertDatabaseCount('submissions', 0);

        Storage::disk('azure')->put($path, $bytes);
        $owner = $this->user;
        $this->actingAs(User::factory()->create())
            ->postJson(route('author.submissions.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('manuscript_file');

        $this->actingAs($owner)->post(route('author.submissions.store'), $payload)
            ->assertRedirect(route('author.submissions.index'))->assertSessionHas('success');

        $file = FileEntry::sole();
        $this->assertSame('pending', $file->virus_scan_status);
        $this->assertSame('naskah-riset.pdf', $file->original_name);
        $this->assertSame(strlen($bytes), $file->byte_size);
        $this->assertSame(hash('sha256', $bytes), $file->checksum_sha256);
        $this->assertStringStartsWith('manuscripts/sealed/', $file->storage_path);
        Storage::disk('azure')->put($path, 'attempted replacement');
        $this->assertSame($bytes, Storage::disk('azure')->get($file->storage_path));
        $this->assertDatabaseHas('jobs', ['queue' => 'file-security']);

        $this->postJson(route('author.submissions.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('manuscript_file');
        $this->assertDatabaseCount('submissions', 1);
    }

    /**
     * Pengajuan gagal jika berkas naskah utama tidak disertakan (baik via file fisik maupun via blob_path).
     */
    public function test_pengajuan_gagal_jika_blob_path_dan_manuscript_file_keduanya_kosong(): void
    {
        $payload = [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Naskah Tanpa Berkas',
            'abstract' => 'Abstrak naskah tanpa berkas lampiran.',
            'keywords' => ['Uji Validasi'],
            'contributors' => [
                [
                    'family_name' => 'Hartono',
                    'email' => 'budi.hartono@makassar.go.id',
                    'affiliation' => 'BRIDA',
                    'corresponding' => true,
                ],
            ],
        ];

        $response = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), $payload);

        $response->assertRedirect(route('author.submissions.create'));
        $response->assertSessionHasErrors(['manuscript_file']);
    }
}
