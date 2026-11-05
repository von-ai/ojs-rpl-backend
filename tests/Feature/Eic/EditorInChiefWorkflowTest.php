<?php

declare(strict_types=1);

namespace Tests\Feature\Eic;

use App\Models\DeskReview;
use App\Models\EditorialDecision;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\Journal;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use App\Services\Payment\ArticlePaymentService;
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

        UserRole::create([
            'user_id' => $this->eic->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'editor_in_chief',
        ]);

        UserRole::create([
            'user_id' => $this->sectionEditor->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'section_editor',
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
        $this->completeReviewRound($this->submission);
        $this->grantRole(User::factory()->create(), 'copyeditor', $this->submission->journal_id);
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

    /**
     * EIC dapat menjalankan desk review lolos sekaligus langsung menugaskan Section Editor (UC-10 & UC-11).
     */
    public function test_eic_dapat_desk_review_lolos_sekaligus_assign_section_editor(): void
    {
        $this->actingAs($this->eic);

        $response = $this->post(route('eic.submissions.desk-review', $this->submission->id), [
            'scope_fit' => 1,
            'files_complete' => 1,
            'guidelines_compliant' => 1,
            'similarity_score' => 10.2,
            'notes' => 'Lolos skrining awal dan langsung didelegasikan ke Section Editor.',
            'status' => 'passed',
            'section_editor_id' => $this->sectionEditor->id,
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('editor_assignments', [
            'submission_id' => $this->submission->id,
            'editor_id' => $this->sectionEditor->id,
            'role' => 'section_editor',
        ]);
    }

    /**
     * Halaman detail ruang kerja EIC menampilkan ringkasan rekomendasi SE, hasil review, dan riwayat audit.
     */
    public function test_halaman_detail_eic_menampilkan_ringkasan_review_dan_audit(): void
    {
        $this->actingAs($this->eic);

        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'minimum_completed_reviews' => 2,
            'status' => 'active',
            'review_type' => 'double_blind',
            'se_recommendation' => 'accept',
            'se_recommendation_note' => 'Kedua reviewer sepakat naskah sangat berkualitas.',
            'se_recommended_at' => now(),
            'version' => 1,
        ]);

        $response = $this->get(route('eic.submissions.show', $this->submission->id));

        $response->assertStatus(200);
        $response->assertSee('Analisis Kebijakan Smart City Makassar');
        $response->assertSee('Kedua reviewer sepakat naskah sangat berkualitas.');
        $response->assertSee('Skrining Awal');
        $response->assertSee('Penugasan Section Editor');
        $response->assertSee('Keputusan Editorial Resmi');
        $response->assertDontSee('UC-10');
        $response->assertDontSee('UC-11');
        $response->assertDontSee('UC-12');
        $response->assertDontSee('text-justify');
        $response->assertDontSee('font-serif');
    }

    /**
     * Ringkasan telaah sejawat dan rekomendasi editor mendukung toggle lihat detail.
     */
    public function test_ringkasan_telaah_eic_mendukung_lihat_detail_dan_toggle_review(): void
    {
        $this->actingAs($this->eic);

        $round = ReviewRound::create([
            'submission_id' => $this->submission->id,
            'round' => 1,
            'minimum_completed_reviews' => 2,
            'status' => 'active',
            'review_type' => 'double_blind',
            'se_recommendation' => 'accept',
            'se_recommendation_note' => 'Catatan rekomendasi editor yang cukup panjang untuk menguji fitur lihat detail pada antarmuka pengguna SIRIDA Kota Makassar.',
            'se_recommended_at' => now(),
            'version' => 1,
        ]);

        $reviewer = User::factory()->create([
            'name' => 'Prof. Dr. Ir. Reviewer Handal',
            'email' => 'reviewer@unhas.ac.id',
        ]);

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'status' => 'completed',
            'response_due_at' => now()->addDays(7),
            'review_due_at' => now()->addDays(14),
            'responded_at' => now(),
            'completed_at' => now(),
            'deadline_version' => 1,
            'version' => 1,
        ]);

        Review::create([
            'assignment_id' => $assignment->id,
            'scores' => [
                'originality' => 85,
                'methodology' => 90,
            ],
            'comments_for_author' => 'Metodologi dan temuan empiris sudah sangat baik.',
            'confidential_comments_for_editor' => 'Rekomendasi kuat untuk diterbitkan segera.',
            'recommendation' => 'accept',
            'certify_no_conflict' => true,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->get(route('eic.submissions.show', $this->submission->id));

        $response->assertOk();
        $response->assertSee('Ringkasan Telaah Sejawat & Rekomendasi Editor Bagian', false);
        $response->assertSee('Laporan Evaluasi Substantif');
        $response->assertSee('Lihat Detail');
        $response->assertSee('eic-rev-detail-'.$assignment->id);
    }

    /**
     * Jejak audit skrining awal dan keputusan editorial mendukung lihat detail catatan dan riwayat sebelumnya.
     */
    public function test_jejak_audit_skrining_dan_keputusan_editorial_mendukung_lihat_detail(): void
    {
        $this->actingAs($this->eic);

        // Buat 2 rekaman skrining awal (skrining awal sebelumnya dan terbaru)
        DeskReview::create([
            'submission_id' => $this->submission->id,
            'assessed_by' => $this->eic->id,
            'status' => 'revision_required',
            'notes' => 'Catatan revisi format awal pertama sebelum penulis mengirim ulang perbaikan naskah.',
            'scope_fit' => true,
            'files_complete' => false,
            'guidelines_compliant' => false,
            'similarity_score' => 15.0,
            'created_at' => now()->subDays(5),
        ]);

        DeskReview::create([
            'submission_id' => $this->submission->id,
            'assessed_by' => $this->eic->id,
            'status' => 'passed',
            'notes' => 'Catatan skrining awal kedua yang sangat panjang dan komprehensif untuk memastikan seluruh aspek format, kepatuhan selingkung, dan relevansi fokus telah memenuhi standar baku penerbitan SIRIDA Kota Makassar.',
            'scope_fit' => true,
            'files_complete' => true,
            'guidelines_compliant' => true,
            'similarity_score' => 12.5,
            'created_at' => now()->subDays(2),
        ]);

        // Buat 2 rekaman keputusan editorial resmi
        EditorialDecision::create([
            'submission_id' => $this->submission->id,
            'decided_by' => $this->eic->id,
            'review_round_id' => null,
            'decision' => 'revisions_required',
            'note_for_author' => 'Surat keputusan revisi pertama yang panjang dan mendalam, menginstruksikan penulis untuk memperkaya metodologi penelitian, menambahkan referensi jurnal bereputasi terbaru, dan memperbaiki tata tulis ilmiah secara tuntas.',
            'internal_note' => 'Catatan internal rahasia dewan redaksi untuk memantau perbaikan.',
            'revision_due_at' => now()->addDays(14),
            'sent_at' => now()->subDay(),
        ]);

        EditorialDecision::create([
            'submission_id' => $this->submission->id,
            'decided_by' => $this->eic->id,
            'review_round_id' => null,
            'decision' => 'accept',
            'note_for_author' => 'Selamat, naskah Anda dinyatakan diterima resmi untuk penerbitan.',
            'internal_note' => null,
            'sent_at' => now(),
        ]);

        $response = $this->get(route('eic.submissions.show', $this->submission->id));

        $response->assertOk();
        $response->assertSee('Jejak Audit Skrining Awal & Keputusan Editorial', false);
        $response->assertSee('Riwayat Skrining Awal');
        $response->assertSee('Lihat Detail Catatan');
        $response->assertSee('Lihat Riwayat Skrining Sebelumnya');
        $response->assertSee('Lihat Detail Surat Keputusan');
        $response->assertSee('Lihat Keputusan Editorial Sebelumnya');
    }

    /**
     * EIC dapat mengakses halaman Dashboard Pemimpin Redaksi.
     */
    public function test_eic_dapat_mengakses_halaman_dashboard(): void
    {
        $this->actingAs($this->eic);

        $response = $this->get(route('eic.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Pemimpin Redaksi');
        $response->assertSee('Naskah Prioritas Menunggu Tindakan');
        $response->assertSee('Analisis Kebijakan Smart City Makassar');
    }

    /**
     * Tahap yang sudah dilewati tidak dapat diubah kembali (penguncian tahapan alur kerja).
     */
    public function test_tahap_yang_sudah_dilewati_tidak_dapat_diubah_kembali(): void
    {
        $this->actingAs($this->eic);

        // Naskah sudah berada di tahap telaah (in_review) -> Skrining Awal terkunci
        $this->submission->update(['status' => 'in_review']);

        $deskResponse = $this->post(route('eic.submissions.desk-review', $this->submission->id), [
            'scope_fit' => 1,
            'files_complete' => 1,
            'guidelines_compliant' => 1,
            'similarity_score' => 10.0,
            'status' => 'rejected',
        ]);

        $deskResponse->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $deskResponse->assertSessionHas('error');
        $this->assertSame('in_review', $this->submission->fresh()->status);

        // Naskah sudah berada di tahap diterima/produksi -> Penugasan SE & Keputusan Akhir terkunci
        $this->submission->update(['status' => 'accepted']);

        $assignResponse = $this->post(route('eic.submissions.assign-editor', $this->submission->id), [
            'section_editor_id' => $this->sectionEditor->id,
        ]);
        $assignResponse->assertSessionHas('error');

        $decisionResponse = $this->post(route('eic.submissions.decision', $this->submission->id), [
            'decision' => 'decline',
            'note_for_author' => 'Percobaan ubah keputusan final.',
        ]);
        $decisionResponse->assertSessionHas('error');
        $this->assertSame('accepted', $this->submission->fresh()->status);
    }

    /**
     * EIC dapat mengakses halaman Pedoman Keputusan Redaksi & COPE.
     */
    public function test_eic_dapat_mengakses_halaman_pedoman_redaksi(): void
    {
        $this->actingAs($this->eic);

        $response = $this->get(route('eic.guidelines'));

        $response->assertStatus(200);
        $response->assertSee('Pedoman Keputusan Redaksi');
        $response->assertSee('Matriks Standar Pengambilan Keputusan Editorial Akhir');
    }

    /**
     * EIC dapat mengesahkan galai final dan sistem mengotomasi status naskah ke ready_for_issue.
     */
    public function test_eic_dapat_mengesahkan_galai_final_dan_mengotomasi_status_ke_ready_for_issue(): void
    {
        $this->actingAs($this->eic);

        // Siapkan naskah pada tahap in_production
        $this->submission->update(['status' => 'in_production']);

        $file = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => 'galleys/test-sample.pdf',
            'original_name' => 'Galley-Final.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'sample-pdf-content'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->eic->id,
        ]);

        $galley = Galley::create([
            'submission_id' => $this->submission->id,
            'file_id' => $file->id,
            'format' => 'pdf',
            'stage' => 'proofreading',
            'status' => 'active',
            'checksum_sha256' => $file->checksum_sha256,
            'version' => 1,
            'label' => 'PDF Full Text',
        ]);

        // Buat penugasan proofreading yang sudah diselesaikan
        ProofAssignment::create([
            'galley_id' => $galley->id,
            'proofreader_id' => $this->sectionEditor->id,
            'due_at' => now()->addDays(7),
            'status' => 'completed',
            'assigned_at' => now()->subDays(2),
            'file_id' => $file->id,
            'checksum_sha256' => $file->checksum_sha256,
        ]);

        // Post pengesahan galai oleh EIC
        $response = $this->post(route('eic.submissions.approve-galley', $this->submission->id), [
            'note' => 'Galai telah diperiksa dan disetujui untuk penjadwalan terbitan berkala.',
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('success');

        // Verifikasi otomatisasi status naskah berubah ke ready_for_issue
        $freshSubmission = $this->submission->fresh();
        $this->assertSame('ready_for_issue', $freshSubmission->status);

        // Verifikasi rekod GalleyApproval dengan stage editor_final
        $this->assertDatabaseHas('galley_approvals', [
            'galley_id' => $galley->id,
            'approved_by' => $this->eic->id,
            'stage' => 'editor_final',
            'checksum_sha256' => $galley->checksum_sha256,
        ]);

        // Verifikasi status galai terkunci menjadi approved
        $this->assertSame('approved', $galley->fresh()->stage);
        $this->assertSame($galley->checksum_sha256, $galley->fresh()->approved_checksum);
    }

    /**
     * EIC gagal mengesahkan galai jika masih ada catatan proofreading yang belum diselesaikan.
     */
    public function test_eic_gagal_mengesahkan_galai_jika_catatan_proofreading_belum_selesai(): void
    {
        $this->actingAs($this->eic);

        $this->submission->update(['status' => 'in_production']);

        $file = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => 'galleys/test-sample-unresolved.pdf',
            'original_name' => 'Galley-Unresolved.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'sample-unresolved-content'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->eic->id,
        ]);

        $galley = Galley::create([
            'submission_id' => $this->submission->id,
            'file_id' => $file->id,
            'format' => 'pdf',
            'stage' => 'proofreading',
            'status' => 'active',
            'checksum_sha256' => $file->checksum_sha256,
            'version' => 1,
            'label' => 'PDF Full Text',
        ]);

        $proofAssignment = ProofAssignment::create([
            'galley_id' => $galley->id,
            'proofreader_id' => $this->sectionEditor->id,
            'due_at' => now()->addDays(7),
            'status' => 'in_progress',
            'assigned_at' => now()->subDays(2),
            'file_id' => $file->id,
            'checksum_sha256' => $file->checksum_sha256,
        ]);

        // Tambahkan catatan koreksi yang belum diselesaikan (resolved = false)
        ProofreadingNote::create([
            'proof_assignment_id' => $proofAssignment->id,
            'galley_id' => $galley->id,
            'file_id' => $file->id,
            'checksum_sha256' => $file->checksum_sha256,
            'category' => 'typo',
            'severity' => 'major',
            'description' => 'Kesalahan penulisan nama instansi pada halaman 2.',
            'resolved' => false,
        ]);

        $response = $this->post(route('eic.submissions.approve-galley', $this->submission->id), [
            'note' => 'Coba sahkan galai yang belum tuntas.',
        ]);

        $response->assertRedirect(route('eic.submissions.show', $this->submission->id));
        $response->assertSessionHas('error');

        // Status naskah tetap in_production, tidak beralih ke ready_for_issue
        $this->assertSame('in_production', $this->submission->fresh()->status);
        $this->assertDatabaseMissing('galley_approvals', [
            'galley_id' => $galley->id,
            'stage' => 'editor_final',
        ]);
    }

    /**
     * EIC dapat melakukan pencarian asynchronous calon Editor Bagian untuk combobox.
     */
    public function test_eic_can_search_editors_asynchronously_for_combobox(): void
    {
        $this->actingAs($this->eic);

        // Buat satu editor tambahan dengan assignment aktif
        $anotherEditor = User::factory()->create([
            'name' => 'Dr. Hasan Basri, S.Kom., M.T.',
            'email' => 'hasan.basri@makassar.go.id',
        ]);
        UserRole::create([
            'user_id' => $anotherEditor->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'section_editor',
        ]);

        $response = $this->getJson(route('eic.submissions.editors.search', [
            'id' => $this->submission->id,
            'q' => 'Ridwan',
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'active_assignments',
                        'role_label',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'has_more',
                    'total',
                    'per_page',
                ],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertSame($this->sectionEditor->id, $data[0]['id']);

        // Pastikan penulis naskah tidak muncul dalam hasil pencarian untuk mencegah conflict of interest
        $allIds = collect($data)->pluck('id');
        $this->assertFalse($allIds->contains($this->author->id));
    }

    /**
     * Pengguna tanpa role EIC tidak dapat mengakses endpoint pencarian editor.
     */
    public function test_unauthorized_user_cannot_access_editor_search(): void
    {
        $this->actingAs($this->author);

        $response = $this->getJson(route('eic.submissions.editors.search', [
            'id' => $this->submission->id,
        ]));

        $response->assertForbidden();
    }

    public function test_eic_search_excludes_eic_without_section_editor_role(): void
    {
        $this->actingAs($this->eic)->getJson(route('eic.submissions.editors.search', [
            'id' => $this->submission->id,
            'q' => $this->eic->email,
        ]))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_eic_with_second_section_editor_role_can_waive_a_payment_as_eic(): void
    {
        $this->journal->update(['apc_enabled' => true, 'apc_amount' => 250000]);
        $this->submission->update(['status' => 'accepted']);
        $this->grantRole($this->eic, 'section_editor', $this->journal->id);
        $payment = app(ArticlePaymentService::class)->resolveOrCreatePayment($this->submission);
        $this->actingAs($this->eic)->from(route('eic.submissions.show', $this->submission->id))
            ->post(route('eic.submissions.payment.waive', $this->submission->id), [
                'waiver_reason' => 'Approved reduction by the editorial office.',
            ])->assertRedirect();
        $this->assertSame('waived', $payment->fresh()->status);
    }
}
