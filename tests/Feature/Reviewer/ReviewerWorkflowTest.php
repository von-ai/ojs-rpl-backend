<?php

declare(strict_types=1);

namespace Tests\Feature\Reviewer;

use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReviewerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $reviewer;

    protected User $otherReviewer;

    protected User $author;

    protected Journal $journal;

    protected Section $section;

    protected Submission $submission;

    protected ReviewRound $reviewRound;

    protected ReviewAssignment $assignment;

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
            'name' => 'Kajian Kebijakan Publik',
            'slug' => 'kajian-kebijakan-publik',
        ]);

        $this->author = User::factory()->create([
            'name' => 'Prof. Rahmawati Bahar, Ph.D.',
            'email' => 'rahmawati.author@unhas.ac.id',
        ]);

        $this->reviewer = User::factory()->create([
            'name' => 'Dr. Andi Bau Sawa, M.Si.',
            'email' => 'bausawa.reviewer@makassarkota.go.id',
        ]);

        $this->otherReviewer = User::factory()->create([
            'name' => 'Dr. Hendra Wijaya, M.Sc.',
            'email' => 'hendra.rev@itb.ac.id',
        ]);

        UserRole::create([
            'user_id' => $this->reviewer->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'reviewer',
        ]);

        UserRole::create([
            'user_id' => $this->otherReviewer->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'reviewer',
        ]);

        $this->submission = Submission::create([
            'submission_number' => 'SUB-2026-REV-001',
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'title' => 'Evaluasi Partisipasi Publik dalam Perencanaan Tata Ruang Pesisir Makassar',
            'abstract' => 'Penelitian ini menganalisis efektivitas pelibatan masyarakat pesisir dalam perumusan kebijakan zonasi.',
            'status' => 'under_review',
        ]);

        $this->reviewRound = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'status' => 'active',
            'reviewer_title' => 'Evaluasi Partisipasi Publik dalam Perencanaan Tata Ruang Pesisir Makassar',
            'reviewer_abstract' => 'Penelitian ini menganalisis efektivitas pelibatan masyarakat pesisir dalam perumusan kebijakan zonasi.',
            'minimum_completed_reviews' => 2,
        ]);

        $this->assignment = ReviewAssignment::create([
            'review_round_id' => $this->reviewRound->id,
            'reviewer_id' => $this->reviewer->id,
            'status' => 'invited',
            'response_due_at' => Carbon::now()->addDays(7),
            'review_due_at' => Carbon::now()->addDays(21),
            'deadline_version' => 1,
            'invitation_message' => 'Mohon bantuan telaah saintifik atas naskah kebijakan pesisir ini.',
        ]);
    }

    public function test_reviewer_dapat_melihat_daftar_penugasan_telaah_miliknya(): void
    {
        // Penugasan naskah lain untuk otherReviewer
        $otherSubmission = Submission::create([
            'submission_number' => 'SUB-2026-REV-002',
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'title' => 'Naskah Khusus Reviewer Lain',
            'abstract' => 'Abstrak rahasia reviewer lain.',
            'status' => 'under_review',
        ]);

        $otherRound = ReviewRound::create([
            'submission_id' => $otherSubmission->id,
            'round' => 1,
            'status' => 'active',
        ]);

        ReviewAssignment::create([
            'review_round_id' => $otherRound->id,
            'reviewer_id' => $this->otherReviewer->id,
            'status' => 'invited',
            'response_due_at' => now()->addDays(7),
            'review_due_at' => now()->addDays(21),
        ]);

        $response = $this->actingAs($this->reviewer)
            ->get(route('reviewer.assignments.index'));

        $response->assertOk();
        $response->assertSee('SUB-2026-REV-001');
        $response->assertSee('Evaluasi Partisipasi Publik dalam Perencanaan Tata Ruang Pesisir Makassar');
        $response->assertDontSee('Naskah Khusus Reviewer Lain');
    }

    public function test_reviewer_melihat_proyeksi_double_blind_tanpa_identitas_penulis(): void
    {
        $this->submission->update([
            'keywords' => ['Tata Ruang', 'Pesisir Makassar', 'Partisipasi Publik'],
        ]);

        $response = $this->actingAs($this->reviewer)
            ->get(route('reviewer.assignments.show', $this->assignment->id));

        $response->assertOk();
        $response->assertSee('Double-Blind');
        $response->assertSee('SUB-2026-REV-001');
        $response->assertSee('Kajian Kebijakan Publik');
        $response->assertSee('Tata Ruang');
        $response->assertSee('Pesisir Makassar');

        // Memastikan nama dan email penulis TIDAK bocor ke halaman penelaah
        $response->assertDontSee('Prof. Rahmawati Bahar, Ph.D.');
        $response->assertDontSee('rahmawati.author@unhas.ac.id');
    }

    public function test_reviewer_dapat_menerima_undangan_penugasan_telaah(): void
    {
        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.respond', $this->assignment->id), [
                'response' => 'accepted',
            ]);

        $response->assertRedirect(route('reviewer.assignments.show', $this->assignment->id));
        $response->assertSessionHas('success');

        $fresh = $this->assignment->fresh();
        $this->assertEquals('accepted', $fresh->status);
        $this->assertNotNull($fresh->responded_at);
    }

    public function test_reviewer_dapat_menolak_undangan_penugasan_telaah(): void
    {
        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.respond', $this->assignment->id), [
                'response' => 'declined',
                'reason' => 'Sedang fokus memimpin riset lapangan di luar kota.',
            ]);

        $response->assertRedirect(route('reviewer.assignments.show', $this->assignment->id));
        $response->assertSessionHas('success');

        $this->assertEquals('declined', $this->assignment->fresh()->status);
    }

    public function test_konfirmasi_undangan_ditolak_jika_sudah_melewati_batas_waktu_respon(): void
    {
        // Set response_due_at di masa lampau (overdue response)
        $this->assignment->update([
            'response_due_at' => Carbon::now()->subHours(2),
        ]);

        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.respond', $this->assignment->id), [
                'response' => 'accepted',
            ]);

        $response->assertSessionHasErrors('response');
        $this->assertEquals('no_response', $this->assignment->fresh()->status);
    }

    public function test_reviewer_dapat_mengajukan_permohonan_perpanjangan_waktu_telaah(): void
    {
        $this->assignment->update(['status' => 'accepted']);

        $newDueAt = Carbon::now()->addDays(30)->format('Y-m-d');

        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.extension', $this->assignment->id), [
                'requested_due_at' => $newDueAt,
                'reason' => 'Perlu waktu tambahan untuk mengonfirmasi data kualitatif wawancara pesisir.',
            ]);

        $response->assertRedirect(route('reviewer.assignments.show', $this->assignment->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('review_extensions', [
            'assignment_id' => $this->assignment->id,
            'status' => 'pending',
            'reason' => 'Perlu waktu tambahan untuk mengonfirmasi data kualitatif wawancara pesisir.',
        ]);
    }

    public function test_reviewer_dapat_menyimpan_draf_evaluasi_rubrik(): void
    {
        $this->assignment->update(['status' => 'accepted']);

        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.draft', $this->assignment->id), [
                'scores' => [
                    'originality' => 85,
                    'methodology' => 78,
                ],
                'comments_for_author' => 'Draf catatan awal: perkuat metodologi triangulasi data.',
                'recommendation' => 'revisions_required',
            ]);

        $response->assertRedirect(route('reviewer.assignments.show', $this->assignment->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'assignment_id' => $this->assignment->id,
            'status' => 'draft',
            'recommendation' => 'revisions_required',
        ]);
    }

    public function test_reviewer_dapat_mengirimkan_hasil_telaah_resmi_dan_wajib_sertifikasi_bebas_konflik(): void
    {
        $this->assignment->update(['status' => 'accepted']);

        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.submit', $this->assignment->id), [
                'scores' => [
                    'originality' => 90,
                    'methodology' => 85,
                    'presentation' => 88,
                    'policy_relevance' => 95,
                ],
                'comments_for_author' => 'Naskah ini sangat baik dan relevan untuk kebijakan pembangunan pesisir Makassar. Disarankan menambah analisis komparatif.',
                'confidential_comments_for_editor' => 'Rekomendasi kuat untuk diterima dengan revisi minor.',
                'recommendation' => 'accept',
                'certify_no_conflict' => '1',
            ]);

        $response->assertRedirect(route('reviewer.assignments.show', $this->assignment->id));
        $response->assertSessionHas('success');

        $freshAssignment = $this->assignment->fresh();
        $this->assertEquals('completed', $freshAssignment->status);
        $this->assertNotNull($freshAssignment->completed_at);

        $this->assertDatabaseHas('reviews', [
            'assignment_id' => $this->assignment->id,
            'status' => 'submitted',
            'recommendation' => 'accept',
            'certify_no_conflict' => true,
        ]);
    }

    public function test_pengiriman_telaah_ditolak_jika_reviewer_tidak_mencentang_pernyataan_bebas_konflik(): void
    {
        $this->assignment->update(['status' => 'accepted']);

        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.submit', $this->assignment->id), [
                'scores' => ['originality' => 80],
                'comments_for_author' => 'Catatan telaah...',
                'recommendation' => 'accept',
                // certify_no_conflict dikosongkan
            ]);

        $response->assertSessionHasErrors('certify_no_conflict');
        $this->assertEquals('accepted', $this->assignment->fresh()->status);
    }

    public function test_hasil_telaah_yang_sudah_disubmit_bersifat_immutable(): void
    {
        $this->assignment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Review::create([
            'assignment_id' => $this->assignment->id,
            'scores' => ['originality' => 90],
            'comments_for_author' => 'Hasil final.',
            'recommendation' => 'accept',
            'certify_no_conflict' => true,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->reviewer)
            ->post(route('reviewer.assignments.submit', $this->assignment->id), [
                'comments_for_author' => 'Mencoba mengubah review yang sudah disubmit...',
                'recommendation' => 'decline',
                'certify_no_conflict' => '1',
            ]);

        $response->assertSessionHasErrors('submit');
    }

    public function test_reviewer_dapat_mengakses_dashboard_dan_melihat_metrik_statis(): void
    {
        $response = $this->actingAs($this->reviewer)
            ->get(route('reviewer.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Mitra Bestari');
        $response->assertSee('Total Penugasan');
        $response->assertSee('Undangan Masuk');
        $response->assertSee('Sedang Ditelaah');
        $response->assertSee('Telaah Selesai');
    }

    public function test_reviewer_dapat_mengakses_halaman_pedoman_telaah(): void
    {
        $response = $this->actingAs($this->reviewer)
            ->get(route('reviewer.guidelines'));

        $response->assertOk();
        $response->assertSee('Pedoman & Etika Telaah Sejawat');
        $response->assertSee('Standar Rubrik Evaluasi Ilmiah');
        $response->assertSee('Matriks Standar Rekomendasi Mitra Bestari ke Dewan Redaksi');
    }

    public function test_reviewer_dapat_mengunduh_berkas_anonim_secara_aman_jika_undangan_diterima(): void
    {
        $file = FileEntry::create([
            'original_name' => 'Naskah-Anonim-Kebijakan-Pesisir.pdf',
            'storage_path' => 'submissions/files/anon-001.pdf',
            'storage_disk' => 'local',
            'mime_type' => 'application/pdf',
            'byte_size' => 102400,
            'checksum_sha256' => hash('sha256', 'dummy content'),
            'virus_scan_status' => 'clean',
        ]);

        $this->reviewRound->update([
            'anonymized_file_id' => $file->id,
        ]);

        // 1. Saat status masih 'invited', unduh berkas ditolak (guard ERD baris 1571)
        $invitedResponse = $this->actingAs($this->reviewer)
            ->get(route('reviewer.files.download', [
                'id' => $this->assignment->id,
                'fileId' => $file->id,
            ]));

        $invitedResponse->assertRedirect(route('reviewer.assignments.show', $this->assignment->id));
        $invitedResponse->assertSessionHas('error');

        // 2. Saat status diubah menjadi 'accepted', unduh berhasil dialirkan secara aman
        $this->assignment->update(['status' => 'accepted']);

        $acceptedResponse = $this->actingAs($this->reviewer)
            ->get(route('reviewer.files.download', [
                'id' => $this->assignment->id,
                'fileId' => $file->id,
            ]));

        $acceptedResponse->assertOk();
        $acceptedResponse->assertHeader('Content-Type', 'application/pdf');
    }
}
