<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Revision;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorRevisionAndDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected User $otherUser;

    protected Journal $journal;

    protected Section $section;

    protected Submission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('azure');

        $this->author = User::factory()->create([
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'email' => 'author@makassar.go.id',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Peneliti Luar',
            'email' => 'other@unhas.ac.id',
        ]);

        $this->journal = Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'issn' => '2985-7821',
            'eissn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->section = Section::create([
            'journal_id' => $this->journal->id,
            'slug' => 'artikel-riset-orisinil',
            'name' => 'Artikel Riset Orisinil',
            'description' => 'Laporan hasil penelitian empiris',
            'accepting_submissions' => true,
            'position' => 1,
            'version' => 1,
        ]);

        $this->submission = Submission::create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-TEST01',
            'title' => 'Evaluasi Kebijakan Inovasi Daerah Makassar',
            'abstract' => 'Abstrak pengujian revisi naskah',
            'status' => 'revisions_requested',
            'version' => 1,
        ]);
    }

    /**
     * Penulis dapat mengunggah berkas revisi naskah dan response letter (UC-07).
     */
    public function test_penulis_dapat_mengunggah_revisi_dan_response_letter(): void
    {
        $this->actingAs($this->author);

        $manuscript = UploadedFile::fake()->create('manuskrip_revisi_r1.pdf', 1024, 'application/pdf');
        $responseLetter = UploadedFile::fake()->create('response_letter_r1.docx', 512, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->post(route('author.submissions.revisions.store', $this->submission->id), [
            'manuscript_file' => $manuscript,
            'response_letter_file' => $responseLetter,
            'note' => 'Berikut adalah revisi naskah putaran 1 sesuai catatan reviewer.',
        ]);

        $response->assertRedirect(route('author.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        // Status submission kembali ke in_review
        $this->submission->refresh();
        $this->assertSame('in_review', $this->submission->status);
        $this->assertSame(2, $this->submission->version);

        // Record revisi tercatat di database sesuai ERD Tabel 22
        $this->assertDatabaseHas('revisions', [
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'submitted',
            'note' => 'Berikut adalah revisi naskah putaran 1 sesuai catatan reviewer.',
        ]);

        $revision = Revision::where('submission_id', $this->submission->id)->first();
        $this->assertNotNull($revision);
        $this->assertNotNull($revision->manuscript_file_id);
        $this->assertNotNull($revision->response_letter_file_id);

        // Relasi FileEntry tersimpan
        $manuscriptFile = FileEntry::find($revision->manuscript_file_id);
        $this->assertNotNull($manuscriptFile);
        $this->assertSame('manuskrip_revisi_r1.pdf', $manuscriptFile->original_name);
        $this->assertNotEmpty($manuscriptFile->checksum_sha256);
        $this->assertSame('pending', $manuscriptFile->virus_scan_status);

        // Relasi submission_files tercatat
        $this->assertDatabaseHas('submission_files', [
            'submission_id' => $this->submission->id,
            'file_id' => $revision->manuscript_file_id,
            'genre' => 'revision',
        ]);
        $this->assertDatabaseHas('submission_files', [
            'submission_id' => $this->submission->id,
            'file_id' => $revision->response_letter_file_id,
            'genre' => 'response_letter',
        ]);
    }

    /**
     * Pengguna lain tidak memiliki akses untuk mengunggah revisi pada naskah orang lain.
     */
    public function test_pengguna_lain_tidak_dapat_mengunggah_revisi_naskah_orang_lain(): void
    {
        $this->actingAs($this->otherUser);

        $manuscript = UploadedFile::fake()->create('revisi_ilegal.pdf', 500, 'application/pdf');

        $response = $this->post(route('author.submissions.revisions.store', $this->submission->id), [
            'manuscript_file' => $manuscript,
        ]);

        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('error');

        // Status naskah tidak berubah
        $this->submission->refresh();
        $this->assertSame('revisions_requested', $this->submission->status);
        $this->assertDatabaseMissing('revisions', [
            'submission_id' => $this->submission->id,
        ]);
    }

    /**
     * Berkas revisi yang melebihi batas 20 MB (20480 KB) ditolak validasi.
     */
    public function test_ukuran_berkas_revisi_melebihi_20_mb_ditolak(): void
    {
        $this->actingAs($this->author);

        // 25 MB > 20 MB
        $oversizedFile = UploadedFile::fake()->create('manuskrip_terlalu_besar.pdf', 25600, 'application/pdf');

        $response = $this->post(route('author.submissions.revisions.store', $this->submission->id), [
            'manuscript_file' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors(['manuscript_file']);
        $this->submission->refresh();
        $this->assertSame('revisions_requested', $this->submission->status);
    }

    /**
     * Format berkas selain PDF, DOC, atau DOCX ditolak.
     */
    public function test_format_berkas_revisi_harus_pdf_doc_docx(): void
    {
        $this->actingAs($this->author);

        $invalidFile = UploadedFile::fake()->create('malicious_file.exe', 100, 'application/x-msdownload');

        $response = $this->post(route('author.submissions.revisions.store', $this->submission->id), [
            'manuscript_file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['manuscript_file']);
    }

    /**
     * Penulis dapat mengunduh berkas manuskrip miliknya sendiri (UC-06).
     */
    public function test_penulis_dapat_mengunduh_berkas_manuskrip(): void
    {
        $this->actingAs($this->author);

        $path = 'submissions/'.$this->submission->id.'/test_manuscript.pdf';
        Storage::disk('azure')->put($path, 'Konten manuskrip asli PDF pengujian');

        $fileEntry = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => $path,
            'original_name' => 'test_manuscript.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 32,
            'checksum_sha256' => hash('sha256', 'Konten manuskrip asli PDF pengujian'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->author->id,
        ]);

        SubmissionFile::create([
            'submission_id' => $this->submission->id,
            'file_id' => $fileEntry->id,
            'genre' => 'manuscript',
            'version' => 1,
        ]);

        $response = $this->get(route('author.submissions.files.download', [
            'id' => $this->submission->id,
            'fileId' => $fileEntry->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('X-Checksum-SHA256', $fileEntry->checksum_sha256);
    }

    /**
     * Pengguna lain dilarang mengunduh berkas privat milik penulis lain.
     */
    public function test_pengguna_lain_dilarang_mengunduh_berkas_penulis_lain(): void
    {
        $this->actingAs($this->otherUser);

        $path = 'submissions/'.$this->submission->id.'/private_manuscript.pdf';
        Storage::disk('azure')->put($path, 'Konten rahasia naskah penulis');

        $fileEntry = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => $path,
            'original_name' => 'private_manuscript.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 30,
            'checksum_sha256' => hash('sha256', 'Konten rahasia naskah penulis'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->author->id,
        ]);

        $response = $this->get(route('author.submissions.files.download', [
            'id' => $this->submission->id,
            'fileId' => $fileEntry->id,
        ]));

        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('error');
    }
}
