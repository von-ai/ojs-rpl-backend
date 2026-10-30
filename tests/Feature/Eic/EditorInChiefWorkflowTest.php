<?php

declare(strict_types=1);

namespace Tests\Feature\Eic;

use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EditorInChiefWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $eic;

    protected User $author;

    protected User $sectionEditor;

    protected Journal $journal;

    protected Section $section;

    protected Submission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->eic = User::factory()->create([
            'name' => 'Prof. Dr. H. Syahrul, M.Pd.',
            'email' => 'eic@makassar.go.id',
        ]);

        $this->author = User::factory()->create([
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'email' => 'author@unhas.ac.id',
        ]);

        $this->sectionEditor = User::factory()->create([
            'name' => 'Dr. Ir. Ridwan Daud, M.T.',
            'email' => 'section.editor@makassar.go.id',
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
            'slug' => 'inovasi-daerah',
            'name' => 'Inovasi Daerah',
            'description' => 'Seksi inovasi daerah',
            'accepting_submissions' => true,
            'position' => 1,
            'version' => 1,
        ]);

        $this->submission = Submission::create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-EIC01',
            'title' => 'Analisis Kebijakan Smart City Makassar',
            'abstract' => 'Abstrak evaluasi smart city',
            'status' => 'submitted',
            'version' => 1,
        ]);
    }

    /**
     * Tamu tanpa autentikasi dialihkan ke form login.
     */
    public function test_tamu_tidak_dapat_mengakses_portal_eic(): void
    {
        Auth::logout();

        $response = $this->get(route('eic.submissions.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * EIC dapat mengakses antrean naskah editorial.
     */
    public function test_eic_dapat_mengakses_antrean_naskah_editorial(): void
    {
        $this->actingAs($this->eic);

        $response = $this->get(route('eic.submissions.index'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-EIC01');
        $response->assertSee('Analisis Kebijakan Smart City Makassar');
        $response->assertSee('Menunggu Desk Review');
    }

    /**
     * EIC dapat melakukan desk review dengan hasil lolos ke tahap penelaahan (UC-10).
     */
    public function test_eic_dapat_menjalankan_desk_review_lolos(): void
    {
        $this->actingAs($this->eic);

        $response = $this->post(route('eic.submissions.desk-review', $this->submission->id), [
            'scope_fit' => 1,
            'files_complete' => 1,
            'guidelines_compliant' => 1,
            'similarity_score' => 14.5,
            'notes' => 'Naskah memenuhi syarat format dan skop jurnal.',
            'status' => 'passed',
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->submission->refresh();
        $this->assertSame('in_review', $this->submission->status);
        $this->assertSame(2, $this->submission->version);

        $this->assertDatabaseHas('desk_reviews', [
            'submission_id' => $this->submission->id,
            'assessed_by' => $this->eic->id,
            'status' => 'passed',
            'similarity_score' => 14.5,
        ]);
    }

    /**
     * EIC dapat melakukan penolakan naskah di tahap awal / desk reject (UC-10).
     */
    public function test_eic_dapat_menjalankan_desk_review_tolak_awal(): void
    {
        $this->actingAs($this->eic);

        $response = $this->post(route('eic.submissions.desk-review', $this->submission->id), [
            'scope_fit' => 0,
            'files_complete' => 1,
            'guidelines_compliant' => 0,
            'similarity_score' => 45.0,
            'notes' => 'Naskah di luar cakupan fokus jurnal dan tingkat kesamaan teks melebihi ambang batas.',
            'status' => 'rejected',
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->submission->refresh();
        $this->assertSame('declined', $this->submission->status);

        $this->assertDatabaseHas('desk_reviews', [
            'submission_id' => $this->submission->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * EIC dapat menugaskan Section Editor untuk menangani naskah (UC-11).
     */
    public function test_eic_dapat_menugaskan_section_editor(): void
    {
        $this->actingAs($this->eic);

        $response = $this->post(route('eic.submissions.assign-editor', $this->submission->id), [
            'section_editor_id' => $this->sectionEditor->id,
            'note' => 'Mohon ditugaskan 2 reviewer mitra bestari independen.',
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('editor_assignments', [
            'submission_id' => $this->submission->id,
            'editor_id' => $this->sectionEditor->id,
            'role' => 'section_editor',
            'note' => 'Mohon ditugaskan 2 reviewer mitra bestari independen.',
        ]);

        $this->submission->refresh();
        $this->assertSame('in_review', $this->submission->status);
    }

    /**
     * Keputusan permintaan revisi naskah wajib menyertakan batas waktu (due date) (UC-12).
     */
    public function test_keputusan_revisi_wajib_sertakan_due_date(): void
    {
        $this->actingAs($this->eic);

        // Tanpa due date -> ditolak validasi
        $response = $this->post(route('eic.submissions.decision', $this->submission->id), [
            'decision' => 'revisions_required',
            'note_for_author' => 'Silakan perbaiki metodologi riset sesuai masukan reviewer.',
            'revision_due_at' => '',
        ]);

        $response->assertSessionHasErrors(['revision_due_at']);

        // Dengan due date valid -> berhasil
        $dueDate = now()->addDays(14)->format('Y-m-d');
        $validResponse = $this->post(route('eic.submissions.decision', $this->submission->id), [
            'decision' => 'revisions_required',
            'note_for_author' => 'Silakan perbaiki metodologi riset sesuai masukan reviewer.',
            'revision_due_at' => $dueDate,
        ]);

        $validResponse->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $validResponse->assertSessionHas('success');

        $this->submission->refresh();
        $this->assertSame('revisions_requested', $this->submission->status);

        $this->assertDatabaseHas('editorial_decisions', [
            'submission_id' => $this->submission->id,
            'decision' => 'revisions_required',
            'status' => 'sent',
        ]);
    }

    /**
     * EIC dapat menerima naskah untuk lanjut ke tahap produksi (UC-12).
     */
    public function test_eic_dapat_menerima_naskah_untuk_produksi(): void
    {
        $this->actingAs($this->eic);

        $response = $this->post(route('eic.submissions.decision', $this->submission->id), [
            'decision' => 'accept',
            'note_for_author' => 'Selamat, naskah ilmiah Anda telah diterima dan akan segera diproses oleh tim tata bahasa (Copyediting).',
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->submission->refresh();
        $this->assertSame('accepted', $this->submission->status);

        $this->assertDatabaseHas('editorial_decisions', [
            'submission_id' => $this->submission->id,
            'decision' => 'accept',
            'status' => 'sent',
        ]);
    }
}
