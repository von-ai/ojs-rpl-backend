<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use App\Models\Copyedit;
use App\Models\FileEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CopyeditingEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Menguji bahwa DatabaseSeeder menghasilkan dataset copyediting nyata yang tampil di dashboard.
     */
    public function test_seeded_copyedits_populate_index_and_detail_views(): void
    {
        $copyEditor = User::where('email', 'andi.kurniawan@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($copyEditor);

        // Akses daftar naskah
        $indexResponse = $this->get('/copyeditor/submissions');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('SUB-2026-00106');
        $indexResponse->assertSee('SUB-2026-00107');
        $indexResponse->assertSee('SUB-2026-00108');
        $indexResponse->assertSee('SUB-2026-00109');
        $indexResponse->assertSee('SUB-2026-00110');
        $indexResponse->assertSee('SUB-2026-00111');

        // Pastikan sidebar badge menampilkan jumlah penugasan naskah secara dinamis (6)
        $indexResponse->assertSee('sidebar-badge-full', false);
        $indexResponse->assertSee('6');

        // Akses naskah 106
        $detailResponse = $this->get('/copyeditor/submissions/SUB-2026-00106');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Unduh Naskah Asli (.docx)');
        $detailResponse->assertSee('SUB2026-00106_Copyedit_Final_Clean.docx');

        // Uji unduh naskah sumber dan pastikan Content-Length cocok dengan byte_size database & disk
        $downloadSrc = $this->get(route('copyeditor.files.download', 'SUB2026-00106_Source_Accepted.docx'));
        $downloadSrc->assertStatus(200);
        $downloadSrc->assertHeader('Content-Length', '2244000');

        // Uji unduh naskah hasil suntingan dan pastikan Content-Length cocok
        $downloadClean = $this->get(route('copyeditor.files.download', 'SUB2026-00106_Copyedit_Final_Clean.docx'));
        $downloadClean->assertStatus(200);
        $downloadClean->assertHeader('Content-Length', '2149580');
    }

    /**
     * Menguji siklus hidup lengkap (End-to-End Workflow):
     * Unggah revisi -> Invalidation Guard -> Minta Persetujuan -> Persetujuan Penulis & Checksum Freeze -> Serah Terima Layout.
     */
    public function test_complete_copyediting_lifecycle_workflow(): void
    {
        $copyEditor = User::where('email', 'andi.kurniawan@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($copyEditor);

        $copyedit = Copyedit::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00106'))->firstOrFail();

        // 1. Copyeditor mengunggah revisi naskah baru
        $revisedFile = UploadedFile::fake()->create('SUB2026-00106_Copyedit_v2.docx', 512);
        $uploadResp = $this->post('/copyeditor/upload', [
            'submission_id' => 'SUB-2026-00106',
            'copyedit_file' => $revisedFile,
        ]);
        $uploadResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');

        // Pastikan file baru tercatat di database dengan byte_size dan SHA-256 yang presisi sesuai disk
        $newFileEntry = FileEntry::where('original_name', 'SUB2026-00106_Copyedit_v2.docx')->firstOrFail();
        $disk = Storage::disk('local');
        $this->assertSame((int) $disk->size($newFileEntry->storage_path), $newFileEntry->byte_size);
        $this->assertSame(hash('sha256', (string) $disk->get($newFileEntry->storage_path)), $newFileEntry->checksum_sha256);

        // Pastikan berkas hasil unggahan dapat diunduh dengan Content-Length yang cocok persis
        $downloadUploaded = $this->get(route('copyeditor.files.download', $newFileEntry->id));
        $downloadUploaded->assertStatus(200);
        $downloadUploaded->assertHeader('Content-Length', (string) $newFileEntry->byte_size);

        // Pastikan approval lama berstatus invalidated (Invalidation Guard)
        $invalidatedApprovals = $copyedit->fresh()->approvals()->whereNotNull('invalidated_at')->count();
        $this->assertGreaterThanOrEqual(1, $invalidatedApprovals);

        // 2. Copyeditor meminta persetujuan penulis ulang
        $reqResp = $this->post('/copyeditor/request-approval', [
            'submission_id' => 'SUB-2026-00106',
            'note' => 'Mohon persetujuan naskah versi 2 setelah perbaikan sitasi.',
        ]);
        $reqResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');

        // 3. Penulis menyetujui naskah (Author Approval & Checksum Freeze)
        $author = $copyedit->submission->author ?? $copyEditor;
        $this->actingAs($author);

        $decisionResp = $this->post('/copyeditor/author-decision', [
            'submission_id' => 'SUB-2026-00106',
            'decision' => 'approved',
            'note' => 'Disetujui, teks dan tabel sudah tepat.',
        ]);
        $decisionResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');

        $activeApproval = $copyedit->fresh()->approvals()->whereNull('invalidated_at')->firstOrFail();
        $this->assertEquals('approved', $activeApproval->decision);
        $this->assertGreaterThan(0, $activeApproval->approvedFiles()->count());

        // 4. Copyeditor melakukan serah terima naskah ke Layout Editor
        $this->actingAs($copyEditor);
        $handoffResp = $this->post('/copyeditor/complete-handoff', [
            'submission_id' => 'SUB-2026-00106',
            'notes' => 'Naskah siap dilayout dengan format 2 kolom margin standar BRIDA.',
        ]);
        $handoffResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');
        $handoffResp->assertSessionHas('status');

        // 5. Verifikasi status akhir di database
        $this->assertEquals('completed', $copyedit->fresh()->status);
        $this->assertEquals('layout', $copyedit->submission->fresh()->status);
        $this->assertDatabaseHas('layout_assignments', [
            'submission_id' => $copyedit->submission_id,
        ]);

        // 6. Verifikasi halaman naskah menampilkan mode arsip terkunci
        $finalShowResp = $this->get('/copyeditor/submissions/SUB-2026-00106');
        $finalShowResp->assertStatus(200);
        $finalShowResp->assertSee('Mode Arsip');
        $finalShowResp->assertSee('Serah Terima Selesai');
    }

    /**
     * Menguji alur permintaan mediasi dari naskah berstatus revisions_requested (SUB-2026-00111):
     * Copyeditor melihat catatan revisi penulis -> tombol minta mediasi -> kirim tiket mediasi -> status menjadi escalated & handoff terkunci.
     */
    public function test_copyeditor_can_request_mediation_for_revisions_requested_submission(): void
    {
        $copyEditor = User::where('email', 'andi.kurniawan@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($copyEditor);

        // 1. Buka naskah 111 yang berstatus revisions_requested
        $detailResp = $this->get('/copyeditor/submissions/SUB-2026-00111');
        $detailResp->assertStatus(200);
        $detailResp->assertSee('Penulis Mengajukan Catatan Revisi');
        $detailResp->assertSee('Minta Mediasi Editor');
        $detailResp->assertSee('Hub-and-Spoke Maritim');

        // 2. Kirim formulir permohonan mediasi
        $escalateResp = $this->post('/copyeditor/escalate', [
            'submission_id' => 'SUB-2026-00111',
            'category' => 'Ketidaksesuaian Terminologi Teknis',
            'reason' => 'Perbedaan istilah teknis maritim IMO dengan pedoman baku.',
        ]);
        $escalateResp->assertRedirect('/copyeditor/submissions/SUB-2026-00111');
        $escalateResp->assertSessionHas('status');

        // 3. Verifikasi tiket eskalasi aktif di database
        $copyedit = Copyedit::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00111'))->firstOrFail();
        $this->assertTrue($copyedit->escalations()->where('status', 'open')->exists());

        // 4. Buka kembali halaman naskah dan pastikan status telah berubah ke mediasi aktif
        $afterResp = $this->get('/copyeditor/submissions/SUB-2026-00111');
        $afterResp->assertStatus(200);
        $afterResp->assertSee('Tiket Mediasi Redaksi Aktif');
        $afterResp->assertDontSee('Minta Mediasi Editor');
    }
}
