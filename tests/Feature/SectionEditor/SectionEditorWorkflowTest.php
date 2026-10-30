<?php

declare(strict_types=1);

namespace Tests\Feature\SectionEditor;

use App\Models\EditorAssignment;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

        $this->reviewerA = User::factory()->create([
            'name' => 'Prof. Dr. Ir. Hasanuddin',
            'email' => 'hasanuddin.rev@unhas.ac.id',
        ]);

        $this->reviewerB = User::factory()->create([
            'name' => 'Dr. Fatimah Zuhra, M.Kom.',
            'email' => 'fatimah.rev@unm.ac.id',
        ]);

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

    public function test_section_editor_dapat_membuka_workspace_dan_memastikan_review_round_aktif(): void
    {
        $response = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.show', $this->submission->id));

        $response->assertOk();
        $response->assertSee('SUB-2026-SE-001');
        $response->assertSee('Putaran Telaah Sejawat (Round 1)');
        $response->assertSee('Undang Mitra Bestari');

        $this->assertDatabaseHas('review_rounds', [
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
        ]);
    }

    public function test_section_editor_dapat_mengundang_mitra_bestari_dengan_batas_waktu_valid(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);

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
    }

    public function test_section_editor_dapat_mengirimkan_rekomendasi_editorial_ke_eic(): void
    {
        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'minimum_completed_reviews' => 2,
        ]);

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
    }
}
