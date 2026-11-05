<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\EditorialDecision;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Review;
use App\Models\ReviewAnnotation;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorDoubleBlindPeerReviewConfidentialityTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected User $reviewer1;

    protected User $reviewer2;

    protected Submission $submission;

    protected FileEntry $annotationFile;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // 1. Setup Author
        $this->author = User::create([
            'username' => 'andi.ilyas',
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'full_name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'email' => 'andi.ilyas@unhas.ac.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        UserRole::create(['user_id' => $this->author->id, 'role_code' => 'author']);

        // 2. Setup Reviewer 1 (Data Rahasia)
        $this->reviewer1 = User::create([
            'username' => 'budi.santoso',
            'name' => 'Prof. Dr. Ir. Budi Santoso, M.Eng.',
            'full_name' => 'Prof. Dr. Ir. Budi Santoso, M.Eng.',
            'email' => 'budi.santoso@ugm.ac.id',
            'affiliation' => 'Fakultas Teknik Universitas Gadjah Mada',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        UserRole::create(['user_id' => $this->reviewer1->id, 'role_code' => 'reviewer']);

        // 3. Setup Reviewer 2 (Data Rahasia)
        $this->reviewer2 = User::create([
            'username' => 'siti.aminah',
            'name' => 'Dr. Siti Aminah Marzuki, S.Si., M.Sc.',
            'full_name' => 'Dr. Siti Aminah Marzuki, S.Si., M.Sc.',
            'email' => 'siti.aminah@itb.ac.id',
            'affiliation' => 'Institut Teknologi Bandung',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        UserRole::create(['user_id' => $this->reviewer2->id, 'role_code' => 'reviewer']);

        // 4. Setup Jurnal & Seksi
        $journal = Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar',
            'issn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);
        $section = Section::create([
            'journal_id' => $journal->id,
            'slug' => 'inovasi-daerah',
            'name' => 'Inovasi Daerah',
            'version' => 1,
        ]);

        // 5. Setup Submission Milik Author
        $this->submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-00099',
            'title' => 'Analisis Kebijakan Pengelolaan Air Bersih Perkotaan',
            'abstract' => 'Abstrak penelitian air bersih perkotaan.',
            'keywords' => ['Air Bersih', 'Kebijakan'],
            'status' => 'revisions_requested',
            'submitted_at' => now()->subDays(10),
            'version' => 1,
        ]);

        // 6. Setup ReviewRound
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'review_type' => 'double_blind',
            'status' => 'active',
            'minimum_completed_reviews' => 2,
            'version' => 1,
        ]);

        // 7. Berkas Anotasi Reviewer 1 (nama file di disk memuat nama reviewer)
        $this->annotationFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'review-annotations/annotation_rev1.docx',
            'original_name' => 'Budi_Santoso_Catatan_Revisi_Unhas.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'Catatan telaah reviewer 1'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->reviewer1->id,
        ]);
        Storage::disk('local')->put($this->annotationFile->storage_path, 'Catatan telaah reviewer 1');

        // 8. Assignment & Review Reviewer 1
        $assignment1 = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewer1->id,
            'status' => 'completed',
            'response_due_at' => now()->addDays(3),
            'review_due_at' => now()->addDays(14),
            'responded_at' => now()->subDays(5),
            'completed_at' => now()->subDays(2),
            'version' => 1,
        ]);
        $review1 = Review::create([
            'assignment_id' => $assignment1->id,
            'comments_for_author' => 'Kajian metodologi perlu dilengkapi dengan perbandingan algoritma lain.',
            'confidential_comments_for_editor' => 'Data numerik penulis meragukan, mohon dewan redaksi cek plagiarisme internal.',
            'recommendation' => 'revisions_required',
            'status' => 'submitted',
            'submitted_at' => now()->subDays(2),
            'version' => 1,
        ]);
        ReviewAnnotation::create([
            'review_id' => $review1->id,
            'file_id' => $this->annotationFile->id,
        ]);

        // 9. Assignment & Review Reviewer 2
        $assignment2 = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewer2->id,
            'status' => 'completed',
            'response_due_at' => now()->addDays(3),
            'review_due_at' => now()->addDays(14),
            'responded_at' => now()->subDays(4),
            'completed_at' => now()->subDay(),
            'version' => 1,
        ]);
        Review::create([
            'assignment_id' => $assignment2->id,
            'comments_for_author' => 'Perlu menambahkan rujukan jurnal bereputasi dari 5 tahun terakhir.',
            'confidential_comments_for_editor' => 'Penulis ini junior saya di ITB, kualitas tulisan masih perlu pembimbingan.',
            'recommendation' => 'revisions_required',
            'status' => 'submitted',
            'submitted_at' => now()->subDay(),
            'version' => 1,
        ]);

        // 10. Keputusan Editorial Revisi
        $editor = User::create([
            'username' => 'editor.section',
            'name' => 'Prof. Dr. Ir. Hamka, Ph.D.',
            'full_name' => 'Prof. Dr. Ir. Hamka, Ph.D.',
            'email' => 'hamka@unhas.ac.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        UserRole::create(['user_id' => $editor->id, 'role_code' => 'editor_in_chief']);

        EditorialDecision::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'decision' => 'revisions_required',
            'note_for_author' => 'Mohon lakukan revisi naskah sesuai catatan evaluasi para mitra bestari.',
            'revision_due_at' => now()->addDays(14),
            'sent_at' => now()->subHours(5),
            'decided_by' => $editor->id,
            'version' => 1,
        ]);

        $this->actingAs($this->author);
    }

    /**
     * Uji Kerahasiaan Double-Blind pada Tampilan HTML Detail Naskah Penulis.
     */
    public function test_author_melihat_komentar_mitra_bestari_secara_anonim_tanpa_kebocoran_identitas(): void
    {
        $response = $this->get('/author/submissions/'.$this->submission->id);

        $response->assertStatus(200);

        // 1. Author DAPAT melihat label proyektif Reviewer A dan Reviewer B
        $response->assertSee('Reviewer A');
        $response->assertSee('Reviewer B');

        // 2. Author DAPAT melihat komentar terbuka untuk penulis
        $response->assertSee('Kajian metodologi perlu dilengkapi dengan perbandingan algoritma lain.');
        $response->assertSee('Perlu menambahkan rujukan jurnal bereputasi dari 5 tahun terakhir.');

        // Lampiran asli tidak dibagikan ke penulis.
        $response->assertDontSee($this->annotationFile->id);

        // 4. TIDAK BOLEH ADA identitas nama asli reviewer
        $response->assertDontSee('Prof. Dr. Ir. Budi Santoso, M.Eng.');
        $response->assertDontSee('Budi Santoso');
        $response->assertDontSee('Dr. Siti Aminah Marzuki, S.Si., M.Sc.');
        $response->assertDontSee('Siti Aminah Marzuki');

        // 5. TIDAK BOLEH ADA afiliasi reviewer
        $response->assertDontSee('Universitas Gadjah Mada');
        $response->assertDontSee('Institut Teknologi Bandung');

        // 6. TIDAK BOLEH ADA email reviewer
        $response->assertDontSee('budi.santoso@ugm.ac.id');
        $response->assertDontSee('siti.aminah@itb.ac.id');

        // 7. TIDAK BOLEH ADA komentar rahasia untuk editor (confidential comments)
        $response->assertDontSee('Data numerik penulis meragukan');
        $response->assertDontSee('cek plagiarisme internal');
        $response->assertDontSee('junior saya di ITB');

        // 8. TIDAK BOLEH ADA nama file asli yang mengandung identitas reviewer
        $response->assertDontSee('Budi_Santoso_Catatan_Revisi_Unhas.docx');
    }

    /**
     * Uji Kerahasiaan Double-Blind pada Payload API JSON.
     */
    public function test_author_api_json_tidak_membocorkan_identitas_dan_komentar_rahasia(): void
    {
        $response = $this->getJson('/author/submissions/'.$this->submission->id);

        $response->assertStatus(200);

        $jsonContent = $response->getContent();

        // 1. Komentar terbuka untuk penulis ada
        $this->assertStringContainsString('Reviewer A', $jsonContent);
        $this->assertStringContainsString('Reviewer B', $jsonContent);
        $this->assertStringContainsString('Kajian metodologi perlu dilengkapi', $jsonContent);

        // 2. Data sensitif tidak boleh ada sama sekali dalam seluruh payload JSON
        $this->assertStringNotContainsString('Budi Santoso', $jsonContent);
        $this->assertStringNotContainsString('Siti Aminah Marzuki', $jsonContent);
        $this->assertStringNotContainsString('Universitas Gadjah Mada', $jsonContent);
        $this->assertStringNotContainsString('Institut Teknologi Bandung', $jsonContent);
        $this->assertStringNotContainsString('budi.santoso@ugm.ac.id', $jsonContent);
        $this->assertStringNotContainsString('siti.aminah@itb.ac.id', $jsonContent);
        $this->assertStringNotContainsString('Data numerik penulis meragukan', $jsonContent);
        $this->assertStringNotContainsString('junior saya di ITB', $jsonContent);
        $this->assertStringNotContainsString('Budi_Santoso_Catatan_Revisi_Unhas.docx', $jsonContent);
    }

    public function test_author_cannot_download_original_review_annotation_even_after_decision(): void
    {
        $this->get(route('author.submissions.files.download', [
            'id' => $this->submission->id,
            'fileId' => $this->annotationFile->id,
        ]))->assertNotFound();
    }

    public function test_author_cannot_access_unreleased_review_comments_or_annotation(): void
    {
        $this->submission->update(['status' => 'under_review']);
        EditorialDecision::where('submission_id', $this->submission->id)->update(['status' => 'draft', 'sent_at' => null]);

        $this->getJson(route('author.submissions.show', $this->submission->id))
            ->assertOk()
            ->assertDontSee('Kajian metodologi perlu dilengkapi');

        $this->get(route('author.submissions.files.download', [
            'id' => $this->submission->id,
            'fileId' => $this->annotationFile->id,
        ]))->assertNotFound();
    }

    /**
     * Uji Kerahasiaan Deliberasi Editorial: Author TIDAK BOLEH melihat rekomendasi internal
     * Section Editor kepada EIC atau draf keputusan yang belum disahkan/dikirimkan.
     */
    public function test_author_tidak_dapat_melihat_rekomendasi_internal_dan_draf_keputusan_editorial(): void
    {
        $round = ReviewRound::where('submission_id', $this->submission->id)->first();

        // 1. Tambahkan rekomendasi internal SE ke EIC pada putaran telaah
        $round->update([
            'se_recommendation' => 'decline',
            'se_recommendation_note' => 'Catatan internal rahasia untuk EIC: Terindikasi ada plagiat pada metodologi.',
            'se_recommended_at' => now(),
        ]);

        // 2. Buat draf keputusan editorial oleh Section Editor (belum dikirim/disahkan oleh EIC)
        EditorialDecision::create([
            'submission_id' => $this->submission->id,
            'review_round_id' => $round->id,
            'decided_by' => $this->reviewer1->id,
            'decision' => 'decline',
            'status' => 'draft',
            'note_for_author' => 'Menunggu finalisasi keputusan resmi oleh Pemimpin Redaksi.',
            'internal_note' => 'Catatan internal rahasia untuk EIC: Terindikasi ada plagiat pada metodologi.',
            'sent_at' => null,
            'version' => 1,
        ]);

        $response = $this->get('/author/submissions/'.$this->submission->id);

        $response->assertStatus(200);

        // Author TIDAK BOLEH melihat catatan internal evaluasi SE untuk EIC
        $response->assertDontSee('Catatan internal rahasia untuk EIC: Terindikasi ada plagiat pada metodologi.');
        $response->assertDontSee('Terindikasi ada plagiat pada metodologi.');

        // Linimasa Author TIDAK BOLEH memuat keputusan berstatus draft
        $response->assertDontSee('Keputusan Editorial: Naskah Ditolak');
    }
}
