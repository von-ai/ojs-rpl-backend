<?php

declare(strict_types=1);

namespace Tests\Feature\Proofreader;

use App\Mail\Proofreader\CorrectionNotesAddedMail;
use App\Mail\Proofreader\ProofreaderEscalationSubmittedMail;
use App\Mail\Proofreader\ProofreadingNoteVerifiedMail;
use App\Mail\Proofreader\ProofreadingSignoffCompletedMail;
use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofreaderEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memastikan data hasil seeding realistis mengisi tampilan katalog penugasan dan detail ruang kerja.
     */
    public function test_seeded_proofreader_populates_index_and_detail_views(): void
    {
        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        // Akses katalog penugasan proofreader
        $indexResponse = $this->get('/proofreader/submissions');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('SUB-2026-00106');
        $indexResponse->assertSee('SUB-2026-00107');
        $indexResponse->assertSee('SUB-2026-00103');
        $indexResponse->assertSee('Dra. Sitti Aminah, M.Hum.');

        // Sidebar badge dinamis dari basis data
        $indexResponse->assertSee('sidebar-badge-full', false);

        // Akses detail naskah SUB-2026-00106
        $detailResponse = $this->get('/proofreader/submissions/SUB-2026-00106');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('SUB-2026-00106');
        $detailResponse->assertSee('Benteng Somba Opu');
        $detailResponse->assertSee('SUB-2026-00106_Galley_v2.pdf');
        $detailResponse->assertSee('SUB-2026-00106_Copyedited_Final.docx');
        $detailResponse->assertSee('Tipografi / Ejaan');
        $detailResponse->assertSee('Tata Letak / Format');
        $detailResponse->assertSee('Sitasi & Pustaka');

        // Unduh berkas galai PDF naskah 106
        $galley106 = Galley::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00106'))->firstOrFail();
        $downloadGalley = $this->get(route('proofreader.galleys.download', $galley106->id));
        $downloadGalley->assertStatus(200);
        $downloadGalley->assertHeader('Content-Type', 'application/pdf');

        // Unduh berkas acuan copyediting naskah 106
        $downloadDocx = $this->get(route('proofreader.files.download', 'SUB-2026-00106'));
        $downloadDocx->assertStatus(200);
        $downloadDocx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    /**
     * Menguji penambahan catatan koreksi baru pada galai aktif dan pengiriman notifikasi email ke layout editor.
     */
    public function test_add_correction_note_creates_database_record_and_dispatches_email(): void
    {
        Mail::fake();

        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        $submission = Submission::where('submission_number', 'SUB-2026-00107')->firstOrFail();

        $response = $this->post('/proofreader/add-note', [
            'submission_id' => 'SUB-2026-00107',
            'category' => 'typo',
            'page' => 3,
            'severity' => 'minor',
            'description' => 'Kesalahan pengetikan kata "komparasi" tertulis "komprasi" pada alinea ke-2.',
        ]);

        $response->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00107'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('proofreading_notes', [
            'category' => 'typo',
            'page' => 3,
            'severity' => 'minor',
            'description' => 'Kesalahan pengetikan kata "komparasi" tertulis "komprasi" pada alinea ke-2.',
            'resolved' => false,
            'verified' => false,
        ]);

        Mail::assertSent(CorrectionNotesAddedMail::class, function ($mail) {
            return $mail->submission->submission_number === 'SUB-2026-00107'
                && $mail->note->category === 'typo';
        });
    }

    /**
     * Menguji verifikasi perbaikan galai oleh proofreader dan pengiriman notifikasi email.
     */
    public function test_verify_note_updates_database_and_dispatches_email(): void
    {
        Mail::fake();

        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        // Catatan 2 pada SUB-2026-00106 telah diselesaikan layout editor namun belum diverifikasi
        $note = ProofreadingNote::whereHas('galley.submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00106'))
            ->where('resolved', true)
            ->where('verified', false)
            ->firstOrFail();

        $response = $this->post('/proofreader/verify-note', [
            'submission_id' => 'SUB-2026-00106',
            'note_id' => $note->id,
        ]);

        $response->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00106'));
        $response->assertSessionHas('status', 'Perbaikan galai berhasil diverifikasi.');

        $freshNote = $note->fresh();
        $this->assertTrue($freshNote->verified);
        $this->assertNotNull($freshNote->verified_at);
        $this->assertSame($proofreader->id, $freshNote->verified_by);

        Mail::assertSent(ProofreadingNoteVerifiedMail::class, function ($mail) use ($note) {
            return $mail->note->id === $note->id;
        });
    }

    /**
     * Guardrail sign-off: Mencegah persetujuan sign-off apabila masih ada catatan koreksi yang belum tuntas.
     */
    public function test_guardrail_prevents_signoff_when_open_notes_exist(): void
    {
        Mail::fake();

        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        // SUB-2026-00106 memiliki catatan kritis yang belum diselesaikan
        $response = $this->post('/proofreader/complete', [
            'submission_id' => 'SUB-2026-00106',
        ]);

        $response->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00106'));
        $response->assertSessionHasErrors(['general']);

        // Pastikan status penugasan tetap in_progress (tidak berubah jadi completed)
        $assignment = ProofAssignment::whereHas('galley.submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00106'))
            ->firstOrFail();
        $this->assertSame('in_progress', $assignment->status);

        Mail::assertNotSent(ProofreadingSignoffCompletedMail::class);
    }

    /**
     * Persetujuan sign-off berhasil ketika seluruh catatan bersih/terverifikasi.
     */
    public function test_complete_signoff_succeeds_when_clean_and_dispatches_email(): void
    {
        Mail::fake();

        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        // Pada skenario ini, seluruh catatan koreksi telah dituntaskan layout editor dan diverifikasi proofreader
        $galley107 = Galley::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00107'))->firstOrFail();
        ProofreadingNote::where('galley_id', $galley107->id)->update([
            'resolved' => true,
            'resolution' => 'Seluruh format tata letak dan sitasi telah diperbaiki pada draf galai.',
            'resolved_at' => now(),
            'verified' => true,
            'verified_at' => now(),
            'verified_by' => $proofreader->id,
        ]);

        $response = $this->post('/proofreader/complete', [
            'submission_id' => 'SUB-2026-00107',
        ]);

        $response->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00107'));
        $response->assertSessionHas('status');

        $assignment = ProofAssignment::whereHas('galley.submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00107'))
            ->latest()
            ->firstOrFail();
        $this->assertSame('completed', $assignment->status);

        $galley = Galley::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00107'))->firstOrFail();
        $this->assertSame('approved', $galley->stage);

        Mail::assertSent(ProofreadingSignoffCompletedMail::class, function ($mail) {
            return $mail->submission->submission_number === 'SUB-2026-00107';
        });
    }

    /**
     * Pengajuan eskalasi ketidaksesuaian naskah menerbitkan tiket eskalasi dan mengirimkan email.
     */
    public function test_submit_escalation_creates_escalation_ticket_and_dispatches_email(): void
    {
        Mail::fake();

        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        $response = $this->post('/proofreader/escalate', [
            'submission_id' => 'SUB-2026-00107',
            'reason' => 'Terdapat tabel data hasil pengujian yang hilang pada halaman 6 galai PDF.',
        ]);

        $response->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00107'));
        $response->assertSessionHas('status', 'Tiket eskalasi ketidaksesuaian naskah berhasil dikirimkan ke Editor.');

        $this->assertDatabaseHas('production_escalations', [
            'opened_by' => $proofreader->id,
            'reason' => 'Terdapat tabel data hasil pengujian yang hilang pada halaman 6 galai PDF.',
            'status' => 'open',
            'copyedit_id' => null,
        ]);

        Mail::assertSent(ProofreaderEscalationSubmittedMail::class, function ($mail) {
            return $mail->submission->submission_number === 'SUB-2026-00107';
        });
    }

    /**
     * Guard rail eskalasi: Mencegah duplikasi tiket eskalasi aktif pada naskah yang sama.
     */
    public function test_guardrail_prevents_duplicate_active_escalations(): void
    {
        Mail::fake();

        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        // SUB-2026-00108 sudah memiliki tiket eskalasi berstatus 'open'
        $response = $this->post('/proofreader/escalate', [
            'submission_id' => 'SUB-2026-00108',
            'reason' => 'Mencoba membuat eskalasi kedua pada naskah yang sama.',
        ]);

        $response->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00108'));
        $response->assertSessionHasErrors(['general']);
    }
}
