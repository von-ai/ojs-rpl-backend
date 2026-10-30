<?php

declare(strict_types=1);

namespace Tests\Feature\Proofreader;

use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProofreaderEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Uji coba alur: Proofreader dapat menambahkan catatan koreksi baru pada galai aktif.
     */
    public function test_proofreader_can_add_note_to_active_galley(): void
    {
        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        $response = $this->post('/proofreader/add-note', [
            'submission_id' => 'SUB-2026-00107',
            'category' => 'typo',
            'page' => 3,
            'severity' => 'minor',
            'description' => 'Salah pengetikan kata "implementasi" menjadi "implmentasi".',
        ]);

        $response->assertRedirect('/proofreader/submissions/SUB-2026-00107');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('proofreading_notes', [
            'category' => 'typo',
            'page' => 3,
            'severity' => 'minor',
            'description' => 'Salah pengetikan kata "implementasi" menjadi "implmentasi".',
            'resolved' => false,
        ]);
    }

    /**
     * Uji coba Invariant / Guard Rail: Proofreader DILARANG melakukan sign-off jika masih ada catatan kritis/mayor yang belum selesai.
     */
    public function test_proofreader_signoff_guard_blocks_completion_when_critical_notes_unresolved(): void
    {
        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        // Tambahkan catatan berkategori critical
        $this->post('/proofreader/add-note', [
            'submission_id' => 'SUB-2026-00107',
            'category' => 'metadata',
            'page' => 1,
            'severity' => 'critical',
            'description' => 'Nama ko-penulis kedua tidak tercantum pada heading galai.',
        ]);

        // Coba lakukan persetujuan akhir
        $response = $this->post('/proofreader/complete', [
            'submission_id' => 'SUB-2026-00107',
        ]);

        $response->assertRedirect('/proofreader/submissions/SUB-2026-00107');
        $response->assertSessionHas('error');

        // Pastikan penugasan TIDAK menjadi completed
        $submission = Submission::where('submission_number', 'SUB-2026-00107')->firstOrFail();
        $activeGalley = Galley::where('submission_id', $submission->id)->where('status', 'active')->firstOrFail();
        $assignment = ProofAssignment::where('galley_id', $activeGalley->id)->firstOrFail();

        $this->assertNotEquals('completed', $assignment->status);
    }

    /**
     * Uji coba alur: Proofreader dapat memverifikasi perbaikan dari Layout Editor.
     */
    public function test_proofreader_can_verify_note_and_resolve_it(): void
    {
        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        $submission = Submission::where('submission_number', 'SUB-2026-00107')->firstOrFail();
        $activeGalley = Galley::where('submission_id', $submission->id)->where('status', 'active')->firstOrFail();
        $assignment = ProofAssignment::where('galley_id', $activeGalley->id)->firstOrFail();

        $note = ProofreadingNote::create([
            'proof_assignment_id' => $assignment->id,
            'galley_id' => $activeGalley->id,
            'file_id' => $activeGalley->file_id,
            'checksum_sha256' => $activeGalley->checksum_sha256,
            'category' => 'layout',
            'page' => 2,
            'severity' => 'major',
            'description' => 'Gambar 2 terpotong margin kanan.',
            'resolved' => false,
        ]);

        $response = $this->post('/proofreader/verify-note', [
            'submission_id' => 'SUB-2026-00107',
            'note_id' => $note->id,
        ]);

        $response->assertRedirect('/proofreader/submissions/SUB-2026-00107');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('proofreading_notes', [
            'id' => $note->id,
            'resolved' => true,
        ]);
    }

    /**
     * Uji coba alur: Proofreader dapat menyelesaikan sign-off saat seluruh catatan kritis/mayor telah terselesaikan.
     */
    public function test_proofreader_can_complete_signoff_when_no_blocking_notes(): void
    {
        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        $submission = Submission::where('submission_number', 'SUB-2026-00107')->firstOrFail();
        $activeGalley = Galley::where('submission_id', $submission->id)->where('status', 'active')->firstOrFail();
        $assignment = ProofAssignment::where('galley_id', $activeGalley->id)->firstOrFail();

        // Pastikan seluruh catatan diselesaikan
        ProofreadingNote::where('proof_assignment_id', $assignment->id)->update(['resolved' => true]);

        $response = $this->post('/proofreader/complete', [
            'submission_id' => 'SUB-2026-00107',
        ]);

        $response->assertRedirect('/proofreader/submissions/SUB-2026-00107');
        $response->assertSessionHas('status');

        $assignment->refresh();
        $this->assertEquals('completed', $assignment->status);

        $this->assertDatabaseHas('galley_approvals', [
            'galley_id' => $activeGalley->id,
            'approved_by' => $proofreader->id,
            'stage' => 'proofreader_signoff',
        ]);
    }

    /**
     * Uji coba alur: Proofreader dapat mengajukan eskalasi naskah ke Editor.
     */
    public function test_proofreader_can_submit_escalation(): void
    {
        $proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($proofreader);

        $response = $this->post('/proofreader/escalate', [
            'submission_id' => 'SUB-2026-00107',
            'reason' => 'Terdapat perubahan teks paragraf pembahasan yang berbeda dari naskah final copyeditor.',
        ]);

        $response->assertRedirect('/proofreader/submissions/SUB-2026-00107');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('production_escalations', [
            'reason' => 'Terdapat perubahan teks paragraf pembahasan yang berbeda dari naskah final copyeditor.',
            'status' => 'open',
            'opened_by' => $proofreader->id,
        ]);
    }
}
