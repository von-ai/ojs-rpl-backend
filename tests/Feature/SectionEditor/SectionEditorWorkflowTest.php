<?php

declare(strict_types=1);

namespace Tests\Feature\SectionEditor;

use App\Jobs\SendReviewNotice;
use App\Models\EditorAssignment;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Models\UserRole;
use App\Services\Reviewer\ReviewerService;
use App\Services\SectionEditor\SectionEditorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SectionEditorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $sectionEditor;

    protected User $otherEditor;

    protected User $author;

    protected User $reviewerA;

    protected User $reviewerB;

    protected Journal $journal;

    protected Section $section;

    protected Submission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->journal = Journal::create([
            'name' => 'Jurnal Inovasi Makassar',
            'slug' => 'jurnal-inovasi-makassar',
            'description' => 'Jurnal ilmiah BRIDA Kota Makassar',
        ]);

        $this->section = Section::create([
            'journal_id' => $this->journal->id,
            'name' => 'Teknologi dan Smart City',
            'slug' => 'teknologi-dan-smart-city',
        ]);

        $this->author = User::factory()->create([
            'name' => 'Dr. Muhammad Ilham',
            'email' => 'ilham.author@makassarkota.go.id',
        ]);

        $this->sectionEditor = User::factory()->create([
            'name' => 'Dr. Andi Nurul, M.T.',
            'email' => 'nurul.se@makassarkota.go.id',
        ]);

        $this->otherEditor = User::factory()->create([
            'name' => 'Fahri Editor, M.Si.',
            'email' => 'fahri.se@makassarkota.go.id',
        ]);

        UserRole::create([
            'user_id' => $this->sectionEditor->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'section_editor',
        ]);

        UserRole::create([
            'user_id' => $this->otherEditor->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'section_editor',
        ]);

        $this->reviewerA = User::factory()->create([
            'name' => 'Prof. Dr. Ir. Hasanuddin',
            'email' => 'hasanuddin.rev@unhas.ac.id',
        ]);

        UserRole::create([
            'user_id' => $this->reviewerA->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'reviewer',
            'expertise_keywords' => ['IoT', 'Sensor Cerdas', 'Mitigasi Banjir'],
        ]);

        $this->reviewerB = User::factory()->create([
            'name' => 'Dr. Fatimah Zuhra, M.Kom.',
            'email' => 'fatimah.rev@unm.ac.id',
        ]);

        UserRole::create([
            'user_id' => $this->reviewerB->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'reviewer',
            'expertise_keywords' => ['Kecerdasan Buatan', 'Smart City'],
        ]);

        $this->grantRole($this->reviewerA, 'reviewer', $this->journal->id);
        $this->grantRole($this->reviewerB, 'reviewer', $this->journal->id);
        $this->submission = Submission::create([
            'submission_number' => 'SUB-2026-SE-001',
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'title' => 'Optimalisasi Jaringan Sensor IoT untuk Mitigasi Banjir Kanal Makassar',
            'abstract' => 'Penelitian implementasi sistem sensor cerdas berbasis LoRaWAN di sepanjang kanal Kota Makassar.',
            'status' => 'in_review',
        ]);

        // Tugaskan $sectionEditor menangani $submission
        EditorAssignment::create([
            'submission_id' => $this->submission->id,
            'editor_id' => $this->sectionEditor->id,
            'role' => 'section_editor',
            'assigned_at' => now(),
        ]);
    }

    public function test_section_editor_dapat_melihat_antrean_naskah_yang_ditugaskan(): void
    {
        // Buat submission lain yang ditugaskan ke otherEditor
        $otherSubmission = Submission::create([
            'submission_number' => 'SUB-2026-SE-002',
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'title' => 'Naskah Lain Milik Editor Lain',
            'abstract' => 'Abstrak naskah kedua.',
            'status' => 'in_review',
        ]);

        EditorAssignment::create([
            'submission_id' => $otherSubmission->id,
            'editor_id' => $this->otherEditor->id,
            'role' => 'section_editor',
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.index'));

        $response->assertOk();
        $response->assertSee('SUB-2026-SE-001');
        $response->assertSee('Optimalisasi Jaringan Sensor IoT');
        $response->assertDontSee('Naskah Lain Milik Editor Lain');
    }

    public function test_section_editor_dilarang_mengakses_workspace_naskah_editor_lain(): void
    {
        $otherSubmission = Submission::create([
            'submission_number' => 'SUB-2026-SE-099',
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'title' => 'Naskah Rahasia Editor Lain',
            'abstract' => 'Abstrak naskah rahasia.',
            'status' => 'in_review',
        ]);

        EditorAssignment::create([
            'submission_id' => $otherSubmission->id,
            'editor_id' => $this->otherEditor->id,
            'role' => 'section_editor',
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.show', $otherSubmission->id));

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_section_editor_dapat_membuka_workspace_dan_memastikan_review_round_aktif(): void
    {
        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.show', $this->submission->id));

        $response->assertOk();
        $response->assertSee('SUB-2026-SE-001');
        $response->assertSee('Putaran Telaah Sejawat');
        $response->assertSee('Undang Mitra Bestari');

        $this->assertDatabaseCount('review_rounds', 0);

    }

    public function test_round_rejects_a_renamed_copy_of_the_original_manuscript(): void
    {
        Storage::fake('azure');
        $this->submission->update(['status' => 'submitted']);
        $original = $this->cleanTestFile('asli.pdf');
        $this->submission->files()->create(['file_id' => $original->id, 'genre' => 'manuscript', 'version' => 1]);

        $this->actingAs($this->sectionEditor)->post(route('section-editor.rounds.store', $this->submission->id), [
            'anonymous_file' => UploadedFile::fake()->createWithContent('anonim.pdf', Storage::disk('azure')->get($original->storage_path)),
            'reviewer_title' => 'Judul anonim',
            'reviewer_abstract' => 'Abstrak anonim',
            'anonymity_confirmed' => 1,
        ])->assertSessionHasErrors(['round' => 'Berkas anonim harus berbeda isinya dari naskah acuan.']);

        $this->assertDatabaseCount('review_rounds', 0);
        $this->assertDatabaseCount('files', 1);
        $this->assertSame([], Storage::disk('azure')->allFiles('review-anonymous'));
        $this->assertSame('submitted', $this->submission->fresh()->status);

        $this->post(route('section-editor.rounds.store', $this->submission->id), [
            'anonymous_file' => UploadedFile::fake()->createWithContent('anonim.pdf', "%PDF-1.4\n% Isi anonim\n%%EOF"),
            'reviewer_title' => 'Judul anonim',
            'reviewer_abstract' => 'Abstrak anonim',
            'anonymity_confirmed' => 1,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('in_review', $this->submission->fresh()->status);
    }

    public function test_editor_can_replace_a_legacy_round_with_an_identical_copy(): void
    {
        Storage::fake('azure');
        $original = $this->cleanTestFile('asli.pdf');
        $copy = $this->cleanTestFile('asli.pdf');
        $this->submission->files()->create(['file_id' => $original->id, 'genre' => 'manuscript', 'version' => 1]);
        $round = $this->submission->reviewRounds()->create([
            'round' => 1, 'status' => 'active',
            'manuscript_file_id' => $original->id, 'manuscript_checksum' => $original->checksum_sha256,
            'anonymized_file_id' => $copy->id, 'anonymized_checksum' => $copy->checksum_sha256,
        ]);
        $assignment = $round->assignments()->create([
            'reviewer_id' => $this->reviewerA->id, 'status' => 'accepted',
            'response_due_at' => now()->addDays(7), 'review_due_at' => now()->addDays(21),
        ]);

        $this->actingAs($this->reviewerA)
            ->get(route('reviewer.files.download', [$assignment->id, $copy->id]))
            ->assertStatus(409);
        $this->get(route('reviewer.assignments.show', $assignment->id))
            ->assertOk()->assertDontSee(route('reviewer.files.download', [$assignment->id, $copy->id]));

        $this->actingAs($this->sectionEditor)->post(route('section-editor.reviewers.assign', $round->id), [
            'reviewer_id' => $this->reviewerB->id,
            'response_due_at' => now()->addDays(7)->toDateString(),
            'review_due_at' => now()->addDays(21)->toDateString(),
        ])->assertSessionHasErrors(['reviewer_id' => 'Siapkan putaran dengan berkas anonim yang berbeda dari naskah acuan.']);
        $this->assertDatabaseCount('review_assignments', 1);

        $this->get(route('section-editor.submissions.show', $this->submission->id))
            ->assertOk()->assertSee('Siapkan putaran baru');
        $this->post(route('section-editor.rounds.store', $this->submission->id), [
            'anonymous_file' => UploadedFile::fake()->createWithContent('anonim.pdf', "%PDF-1.4\n% Isi anonim\n%%EOF"),
            'reviewer_title' => 'Judul anonim',
            'reviewer_abstract' => 'Abstrak anonim',
            'anonymity_confirmed' => 1,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('closed', $round->fresh()->status);
        $this->assertSame('cancelled', $assignment->fresh()->status);
        $this->assertDatabaseHas('review_rounds', ['submission_id' => $this->submission->id, 'round' => 2, 'status' => 'active']);
    }

    public function test_section_editor_dapat_mengundang_mitra_bestari_dengan_batas_waktu_valid(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);

        $original = $this->cleanTestFile('asli.pdf');
        $anonymous = $this->cleanTestFile('anonim.pdf');
        $round->update(['manuscript_file_id' => $original->id, 'anonymized_file_id' => $anonymous->id, 'anonymized_checksum' => $anonymous->checksum_sha256, 'reviewer_title' => 'Judul anonim', 'reviewer_abstract' => 'Abstrak anonim', 'rubric_snapshot' => app(ReviewerService::class)->getDefaultRubricCriteria()]);
        $responseDue = Carbon::now()->addDays(7)->format('Y-m-d');
        $reviewDue = Carbon::now()->addDays(21)->format('Y-m-d');

        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.reviewers.assign', $round->id), [
                'reviewer_id' => $this->reviewerA->id,
                'response_due_at' => $responseDue,
                'review_due_at' => $reviewDue,
                'invitation_message' => 'Mohon kesediaannya menelaah naskah IoT mitigasi banjir ini.',
            ]);

        $response->assertRedirect(route('section-editor.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('review_assignments', [
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewerA->id,
            'status' => 'invited',
        ]);

        // Status naskah berubah menjadi under_review
        $this->assertEquals('under_review', $this->submission->fresh()->status);
    }

    public function test_penugasan_reviewer_menolak_penulis_naskah_sendiri_mencegah_konflik_kepentingan(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        // Menugaskan $author sebagai reviewer pada naskahnya sendiri
        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.reviewers.assign', $round->id), [
                'reviewer_id' => $this->author->id,
                'response_due_at' => now()->addDays(7)->format('Y-m-d'),
                'review_due_at' => now()->addDays(21)->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('reviewer_id');
        $this->assertDatabaseMissing('review_assignments', [
            'review_round_id' => $round->id,
            'reviewer_id' => $this->author->id,
        ]);
    }

    public function test_penugasan_reviewer_menolak_penugasan_ganda_aktif_pada_putaran_yang_sama(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        // Assignment pertama
        ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewerA->id,
            'status' => 'invited',
            'response_due_at' => now()->addDays(7),
            'review_due_at' => now()->addDays(21),
        ]);

        // Percobaan mengundang reviewerA untuk kedua kalinya di putaran yang sama
        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.reviewers.assign', $round->id), [
                'reviewer_id' => $this->reviewerA->id,
                'response_due_at' => now()->addDays(7)->format('Y-m-d'),
                'review_due_at' => now()->addDays(21)->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('reviewer_id');
        $this->assertEquals(1, ReviewAssignment::where('review_round_id', $round->id)->where('reviewer_id', $this->reviewerA->id)->count());
    }

    public function test_penugasan_reviewer_menolak_tanggal_telaah_sebelum_tanggal_respon(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        // Tanggal telaah (7 hari) lebih cepat daripada respon (14 hari)
        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.reviewers.assign', $round->id), [
                'reviewer_id' => $this->reviewerB->id,
                'response_due_at' => now()->addDays(14)->format('Y-m-d'),
                'review_due_at' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('review_due_at');
    }

    public function test_section_editor_dapat_membatalkan_undangan_yang_masih_berstatus_invited(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewerA->id,
            'status' => 'invited',
            'response_due_at' => now()->addDays(7),
            'review_due_at' => now()->addDays(21),
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.assignments.cancel', $assignment->id));

        $response->assertRedirect(route('section-editor.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->assertEquals('cancelled', $assignment->fresh()->status);
    }

    public function test_section_editor_dapat_menyetujui_permohonan_perpanjangan_waktu_reviewer(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        $oldDueAt = now()->addDays(5);
        $newDueAt = now()->addDays(14);

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewerA->id,
            'status' => 'accepted',
            'response_due_at' => now()->subDay(),
            'review_due_at' => $oldDueAt,
            'deadline_version' => 1,
        ]);

        $extension = ReviewExtension::create([
            'assignment_id' => $assignment->id,
            'requested_due_at' => $newDueAt,
            'reason' => 'Perlu waktu tambahan untuk validasi dataset lapangan di kanal Panampu.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.extensions.decide', $extension->id), [
                'decision' => 'approved',
                'decision_reason' => 'Disetujui demi kualitas analisis empiris.',
            ]);

        $response->assertRedirect(route('section-editor.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->assertEquals('approved', $extension->fresh()->status);
        $this->assertEquals(2, $assignment->fresh()->deadline_version);
        $this->assertEquals($newDueAt->format('Y-m-d H:i'), $assignment->fresh()->review_due_at->format('Y-m-d H:i'));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'review.extension.approved',
            'resource_id' => $extension->id,
            'actor_id' => $this->sectionEditor->id,
        ]);
    }

    public function test_section_editor_tidak_dapat_mengirim_rekomendasi_jika_kuorum_telaah_belum_terpenuhi(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);

        // Coba kirim rekomendasi saat kuorum belum tercapai (0/2 selesai)
        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.rounds.recommendation', $round->id), [
                'recommendation' => 'revisions_required',
                'note' => 'Catatan telaah yang seharusnya ditolak karena kuorum belum cukup.',
            ]);

        $response->assertSessionHasErrors('recommendation');
        $this->assertNull($round->fresh()->se_recommendation);
    }

    public function test_section_editor_dapat_mengirim_rekomendasi_dengan_alasan_override_kuorum(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);

        // Belum ada review selesai (0/2), tetapi Section Editor menyertakan alasan override kuorum resmi
        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.rounds.recommendation', $round->id), [
                'recommendation' => 'decline',
                'note' => 'Ditemukan indikasi plagiarisme berat dan naskah ditarik atas permintaan resmi.',
                'is_override' => '1',
                'override_reason' => 'Naskah ditarik secara tertulis oleh penulis utama dan ditemukan indikasi fabrikasi data.',
            ]);

        $response->assertRedirect(route('section-editor.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $freshRound = $round->fresh();
        $this->assertEquals('decline', $freshRound->se_recommendation);
        $this->assertNotNull($freshRound->se_recommended_at);

        // Memastikan draft EditorialDecision tersimpan dengan minimum_review_override_reason
        $this->assertDatabaseHas('editorial_decisions', [
            'submission_id' => $this->submission->id,
            'review_round_id' => $round->id,
            'decision' => 'decline',
            'status' => 'draft',
            'minimum_review_override_reason' => 'Naskah ditarik secara tertulis oleh penulis utama dan ditemukan indikasi fabrikasi data.',
        ]);
    }

    public function test_section_editor_dapat_mengirimkan_rekomendasi_editorial_ke_eic(): void
    {
        Queue::fake([SendReviewNotice::class]);
        $editor = User::factory()->create();
        $this->grantRole($editor, 'editor_in_chief', $this->submission->journal_id);

        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);

        $this->completeReviewRound($this->submission, $round);
        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.rounds.recommendation', $round->id), [
                'recommendation' => 'revisions_required',
                'note' => 'Kedua reviewer sepakat naskah memiliki kebaruan tinggi namun membutuhkan kalibrasi sensor IoT yang lebih detail.',
            ]);

        $response->assertRedirect(route('section-editor.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $freshRound = $round->fresh();
        $this->assertEquals('revisions_required', $freshRound->se_recommendation);
        $this->assertNotNull($freshRound->se_recommended_at);

        // Memastikan draft EditorialDecision terbentuk untuk Pemimpin Redaksi (EIC)
        $this->assertDatabaseHas('editorial_decisions', [
            'submission_id' => $this->submission->id,
            'review_round_id' => $round->id,
            'decision' => 'revisions_required',
            'status' => 'draft',
        ]);
        Queue::assertPushed(SendReviewNotice::class, fn (SendReviewNotice $job) => $job->email === $editor->email
            && $job->subject === 'Rekomendasi telaah SIRIDA');
    }

    public function test_section_editor_dapat_mengakses_halaman_dashboard(): void
    {
        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Editor Bagian');
        $response->assertSee('Naskah Prioritas Menunggu Tindakan');
        $response->assertSee('Optimalisasi Jaringan Sensor IoT');
    }

    public function test_section_editor_dapat_mengakses_halaman_pedoman_telaah(): void
    {
        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.guidelines'));

        $response->assertOk();
        $response->assertSee('Pedoman Telaah Sejawat');
        $response->assertSee('Matriks Standar Rekomendasi Editorial ke Pemimpin Redaksi');
    }

    public function test_section_editor_dapat_mengunduh_berkas_manuskrip(): void
    {
        $fileEntry = FileEntry::create([
            'original_name' => 'Naskah_IoT_Banjir.pdf',
            'storage_path' => 'submissions/test-manuscript.pdf',
            'byte_size' => 10240,
            'mime_type' => 'application/pdf',
            'checksum_sha256' => hash('sha256', 'dummy content'),
            'virus_scan_status' => 'clean',
        ]);

        Storage::disk('azure')->put($fileEntry->storage_path, 'dummy content');
        $subFile = SubmissionFile::create([
            'submission_id' => $this->submission->id,
            'file_id' => $fileEntry->id,
            'genre' => 'manuscript',
            'version' => 1,
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.files.download', [
                'id' => $this->submission->id,
                'fileId' => $subFile->id,
            ]));

        $response->assertOk();
        $this->assertTrue($response->headers->get('content-disposition') !== null);
    }

    public function test_halaman_detail_section_editor_mematuhi_standar_kebersihan_ui(): void
    {
        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.show', $this->submission->id));

        $response->assertOk();
        $response->assertDontSee('font-serif');
        $response->assertDontSee('text-justify');
        $response->assertDontSee('UC-11');
        $response->assertDontSee('UC-08');
    }

    public function test_get_available_reviewers_hanya_menampilkan_mitra_bestari_sah_sesuai_jurnal(): void
    {
        // 1. Buat pengguna tanpa peran reviewer (misal: hanya penulis atau staf)
        $nonReviewer = User::factory()->create([
            'name' => 'Staf Tata Usaha',
            'email' => 'tu@makassarkota.go.id',
        ]);

        // 2. Buat reviewer dari jurnal lain
        $otherJournal = Journal::create([
            'name' => 'Jurnal Ekologi Maritim',
            'slug' => 'jurnal-ekologi-maritim',
        ]);
        $otherJournalReviewer = User::factory()->create([
            'name' => 'Dr. Reviewer Ekologi',
            'email' => 'ekologi.rev@unhas.ac.id',
        ]);
        UserRole::create([
            'user_id' => $otherJournalReviewer->id,
            'journal_id' => $otherJournal->id,
            'role_code' => 'reviewer',
        ]);

        // 3. Buat reviewer C di jurnal ini
        $reviewerC = User::factory()->create([
            'name' => 'Zulfa Reviewer, M.Si.',
            'email' => 'zulfa.rev@unhas.ac.id',
        ]);
        UserRole::create([
            'user_id' => $reviewerC->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'reviewer',
        ]);

        /** @var SectionEditorService $service */
        $service = app(SectionEditorService::class);
        $reviewers = $service->getAvailableReviewers($this->submission);

        // Hanya reviewer terdaftar di jurnal ini yang muncul
        $reviewerIds = $reviewers->pluck('id')->all();
        $this->assertContains($this->reviewerA->id, $reviewerIds);
        $this->assertContains($this->reviewerB->id, $reviewerIds);
        $this->assertContains($reviewerC->id, $reviewerIds);
        $this->assertNotContains($this->author->id, $reviewerIds); // Penulis harus dikecualikan
        $this->assertNotContains($nonReviewer->id, $reviewerIds); // Bukan reviewer
        $this->assertNotContains($otherJournalReviewer->id, $reviewerIds); // Reviewer jurnal lain
    }

    public function test_halaman_detail_section_editor_merender_combobox_mitra_bestari(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);
        $original = $this->cleanTestFile('asli.pdf');
        $anonymous = $this->cleanTestFile('anonim.pdf');
        $round->update([
            'manuscript_file_id' => $original->id,
            'anonymized_file_id' => $anonymous->id,
            'anonymized_checksum' => $anonymous->checksum_sha256,
            'reviewer_title' => 'Judul anonim',
            'reviewer_abstract' => 'Abstrak anonim',
            'rubric_snapshot' => app(ReviewerService::class)->getDefaultRubricCriteria(),
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.show', $this->submission->id));

        $response->assertOk();
        $response->assertSee('reviewer-combobox-container');
        $response->assertSee('reviewer-combobox-input');
        $response->assertSee('Kategori:');
        $response->assertSee($this->section->name);
        $response->assertSee($this->reviewerA->name);
    }

    public function test_section_editor_dapat_mengirim_pengingat_dan_memicu_email_ke_mitra_bestari(): void
    {
        Queue::fake();

        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $this->reviewerA->id,
            'status' => 'accepted',
            'response_due_at' => Carbon::now()->subDay(),
            'review_due_at' => Carbon::now()->addDays(5),
            'version' => 1,
        ]);

        $response = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.assignments.reminder', $assignment->id), [
                'message' => 'Segera selesaikan telaah.',
            ]);

        $response->assertRedirect(route('section-editor.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        // Pastikan record pengingat tercatat di basis data
        $this->assertDatabaseHas('review_reminders', [
            'assignment_id' => $assignment->id,
            'sent_by' => $this->sectionEditor->id,
            'delivery_status' => 'queued',
        ]);

        Queue::assertPushed(SendReviewNotice::class, function (SendReviewNotice $job) {
            return $job->email === $this->reviewerA->email;
        });

        // Pastikan tampilan memuat jejak pengingat
        $showResponse = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.show', $this->submission->id));

        $showResponse->assertOk();
        $showResponse->assertSee('Pengingat terakhir dikirim:');
        $showResponse->assertSee('Kirim Pengingat Ulang');
    }

    public function test_cannot_invite_global_reviewer_who_cannot_access_the_submission(): void
    {
        Storage::fake('azure');
        $file = $this->cleanTestFile('anonymous.pdf');
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'review_type' => 'double_blind',
            'status' => 'active',
            'anonymized_file_id' => $file->id,
            'anonymized_checksum' => $file->checksum_sha256,
        ]);
        $reviewer = User::factory()->create();
        UserRole::create(['user_id' => $reviewer->id, 'role_code' => 'reviewer']);
        $this->actingAs($this->sectionEditor)->post(route('section-editor.reviewers.assign', $round->id), [
            'reviewer_id' => $reviewer->id,
            'response_due_at' => now()->addDays(3)->toDateString(),
            'review_due_at' => now()->addDays(5)->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseMissing('review_assignments', ['reviewer_id' => $reviewer->id, 'review_round_id' => $round->id]);
    }
}
