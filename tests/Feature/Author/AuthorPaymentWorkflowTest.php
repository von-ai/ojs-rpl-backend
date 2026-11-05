<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\ArticlePayment;
use App\Models\EditorAssignment;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected User $manager;

    protected User $eic;

    protected User $sectionEditor;

    protected Journal $freeJournal;

    protected Journal $paidJournal;

    protected Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('azure');

        $this->author = User::factory()->create([
            'name' => 'Fadli Rahman, S.Sos., M.A.',
            'email' => 'author@makassar.go.id',
        ]);
        UserRole::create([
            'user_id' => $this->author->id,
            'role_code' => 'author',
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Andi M. Yusuf, M.Si.',
            'email' => 'manager@makassar.go.id',
        ]);
        UserRole::create([
            'user_id' => $this->manager->id,
            'role_code' => 'manager',
        ]);

        $this->eic = User::factory()->create([
            'name' => 'Prof. Dr. Ir. Hamka Hasan',
            'email' => 'eic@makassar.go.id',
        ]);
        UserRole::create([
            'user_id' => $this->eic->id,
            'role_code' => 'editor_in_chief',
        ]);

        $this->sectionEditor = User::factory()->create([
            'name' => 'Dr. Suryani, M.T.',
            'email' => 'se@makassar.go.id',
        ]);
        UserRole::create([
            'user_id' => $this->sectionEditor->id,
            'role_code' => 'section_editor',
        ]);

        $this->freeJournal = Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'issn' => '2985-7821',
            'eissn' => '2985-7821',
            'apc_fee' => 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah - Didanai Penuh oleh APBD BRIDA Kota Makassar)',
            'apc_enabled' => false,
            'status' => 'active',
            'version' => 1,
        ]);

        $this->paidJournal = Journal::create([
            'slug' => 'sirida-paid',
            'name' => 'Jurnal Sains Terapan Makassar (Berbayar)',
            'issn' => '2985-9999',
            'eissn' => '2985-9999',
            'apc_fee' => 'Rp 750.000,- per naskah',
            'apc_enabled' => true,
            'apc_amount' => 750000.00,
            'payment_bank_name' => 'Bank Sulselbar',
            'payment_account_number' => '130-001-009876-5',
            'payment_account_holder' => 'Badan Riset dan Inovasi Daerah Kota Makassar',
            'payment_instructions' => 'Sertakan ID Naskah pada berita transfer. Unggah foto atau berkas PDF bukti transfer yang sah.',
            'status' => 'active',
            'version' => 1,
        ]);

        foreach ([$this->freeJournal, $this->paidJournal] as $journal) {
            $this->grantRole($this->manager, 'manager', $journal->id);
            $this->grantRole($this->eic, 'editor_in_chief', $journal->id);
            $this->grantRole($this->sectionEditor, 'section_editor', $journal->id);
        }

        $this->section = Section::create([
            'journal_id' => $this->paidJournal->id,
            'slug' => 'riset-terapan',
            'name' => 'Artikel Riset Terapan',
            'accepting_submissions' => true,
            'position' => 1,
            'version' => 1,
        ]);
    }

    /**
     * Naskah di jurnal gratis (Diamond Open Access) menampilkan informasi bebas biaya tanpa form unggah bukti.
     */
    public function test_naskah_jurnal_gratis_menampilkan_informasi_bebas_biaya(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->freeJournal->id,
            'section_id' => Section::create(['journal_id' => $this->freeJournal->id, 'name' => 'Riset gratis', 'slug' => 'riset-gratis'])->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-FREE01',
            'title' => 'Inovasi Pelayanan Publik Bebas Biaya',
            'abstract' => 'Abstrak pengujian naskah bebas biaya',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $response = $this->actingAs($this->author)
            ->get(route('author.submissions.show', $submission->id));

        $response->assertStatus(200);
        $response->assertSee('Diamond Open Access');
        $response->assertSee('Bebas Biaya');
        $response->assertDontSee('Unggah Bukti Transfer Pembayaran');
    }

    /**
     * Penulis tidak dapat mengunggah bukti bayar jika jurnal bebas biaya.
     */
    public function test_penulis_tidak_dapat_mengunggah_bukti_pada_jurnal_gratis(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->freeJournal->id,
            'section_id' => Section::create(['journal_id' => $this->freeJournal->id, 'name' => 'Riset gratis', 'slug' => 'riset-gratis'])->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-FREE02',
            'title' => 'Studi Efektivitas Kebijakan Daerah',
            'abstract' => 'Abstrak pengujian efektivitas kebijakan',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $file = UploadedFile::fake()->create('bukti.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->author)
            ->post(route('author.submissions.payment.store', $submission->id), [
                'payment_proof' => $file,
            ]);

        $response->assertRedirect(route('author.submissions.show', $submission->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('article_payments', [
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * Penulis tidak dapat membayar sebelum naskah diterima (misal masih in_review).
     */
    public function test_penulis_tidak_dapat_membayar_sebelum_naskah_diterima(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID01',
            'title' => 'Pengembangan Sistem Sensor Kualitas Air',
            'abstract' => 'Abstrak sensor kualitas air',
            'status' => 'in_review',
            'version' => 1,
        ]);

        $file = UploadedFile::fake()->create('bukti.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->author)
            ->post(route('author.submissions.payment.store', $submission->id), [
                'payment_proof' => $file,
            ]);

        $response->assertRedirect(route('author.submissions.show', $submission->id));
        $response->assertSessionHas('error');
    }

    /**
     * Penulis dapat melihat rincian tagihan rekening dan mengunggah bukti pembayaran saat menjelang terbit (accepted / in_production).
     */
    public function test_penulis_dapat_melihat_tagihan_dan_mengunggah_bukti_bayar_pada_jurnal_berbayar(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID02',
            'title' => 'Implementasi Machine Learning pada Mitigasi Banjir Kota',
            'abstract' => 'Abstrak mitigasi banjir machine learning',
            'status' => 'in_production',
            'version' => 1,
        ]);

        // 1. Author melihat rincian rekening tujuan dan nominal tagihan
        $pageResponse = $this->actingAs($this->author)
            ->get(route('author.submissions.show', $submission->id));

        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Bank Sulselbar');
        $pageResponse->assertSee('130-001-009876-5');
        $pageResponse->assertSee('id="modal-upload-pembayaran"', false);
        $this->assertSame(1, substr_count($pageResponse->getContent(), 'id="modal-upload-pembayaran"'));
        $pageResponse->assertSee('Rp 750.000');
        $pageResponse->assertSee('Menunggu Pembayaran');

        // 2. Author mengunggah berkas bukti transfer
        $proofFile = UploadedFile::fake()->create('bukti_transfer_apc.jpg', 800, 'image/jpeg');

        $uploadResponse = $this->actingAs($this->author)
            ->post(route('author.submissions.payment.store', $submission->id), [
                'payment_proof' => $proofFile,
                'sender_bank_name' => 'Bank Mandiri',
                'sender_account_name' => 'Fadli Rahman',
                'transfer_date' => now()->toDateString(),
                'amount_paid' => 750000,
                'author_notes' => 'Pembayaran lunas via Mandiri Livin',
            ]);

        $uploadResponse->assertRedirect(route('author.submissions.show', $submission->id));
        $uploadResponse->assertSessionHas('success');

        // 3. Verifikasi record di basis data
        $payment = ArticlePayment::where('submission_id', $submission->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending_verification', $payment->status);
        $this->assertEquals('Bank Mandiri', $payment->sender_bank_name);
        $this->assertEquals('Fadli Rahman', $payment->sender_account_name);
        $this->assertNotNull($payment->file_entry_id);

        $fileEntry = FileEntry::find($payment->file_entry_id);
        $this->assertNotNull($fileEntry);
        $this->assertTrue(Storage::disk('azure')->exists($fileEntry->storage_path));

        // 4. Author dapat mengunduh kembali berkas bukti transfer
        $this->scanTestUploads();
        $downloadResponse = $this->actingAs($this->author)
            ->get(route('author.submissions.payment.proof', $submission->id));

        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-type', 'image/jpeg');
    }

    /**
     * Manajer Jurnal dapat memverifikasi pembayaran menjadi Lunas (verified).
     */
    public function test_manajer_jurnal_dapat_memverifikasi_pembayaran_menjadi_lunas(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID03',
            'title' => 'Optimalisasi Drainase Perkotaan',
            'abstract' => 'Abstrak drainase perkotaan',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $proof = UploadedFile::fake()->create('bukti.pdf', 300, 'application/pdf');
        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => $proof,
            'sender_bank_name' => 'BCA',
            'sender_account_name' => 'Fadli Rahman',
            'amount_paid' => 750000,
        ]);

        // Manajer Jurnal memverifikasi pembayaran
        $this->scanTestUploads();
        $verifyResponse = $this->actingAs($this->manager)
            ->post(route('journalmanager.submissions.payment.verify', $submission->id), [
                'notes' => 'Telah dicek pada mutasi rekening koran kas jurnal.',
            ]);

        $verifyResponse->assertRedirect();
        $verifyResponse->assertSessionHas('status');

        $payment = ArticlePayment::where('submission_id', $submission->id)->first();
        $this->assertEquals('verified', $payment->status);
        $this->assertEquals($this->manager->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);

        // Naskah kini dianggap payment cleared
        $this->assertTrue($submission->fresh()->isPaymentCleared());

        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => UploadedFile::fake()->create('pengganti.pdf', 1, 'application/pdf'),
            'amount_paid' => 1,
        ])->assertRedirect(route('author.submissions.show', $submission->id))->assertSessionHas('error');

        $this->assertSame($payment->getAttributes(), $payment->fresh()->getAttributes());
        $this->assertTrue($submission->fresh()->isPaymentCleared());
        $this->assertDatabaseCount('files', 1);
        $this->assertCount(1, Storage::disk('azure')->allFiles("payments/{$submission->id}"));
        $this->get(route('author.submissions.show', $submission->id))
            ->assertOk()->assertDontSee('id="modal-upload-pembayaran"', false);
    }

    /**
     * Manajer Jurnal dapat menolak bukti bayar dan Penulis dapat mengunggah perbaikan bukti.
     */
    public function test_manajer_dapat_menolak_bukti_dan_penulis_dapat_unggah_ulang(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID04',
            'title' => 'Audit Tata Ruang Pesisir',
            'abstract' => 'Abstrak audit tata ruang',
            'status' => 'ready_for_issue',
            'version' => 1,
        ]);

        $proof = UploadedFile::fake()->create('bukti_buram.jpg', 200, 'image/jpeg');
        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => $proof,
        ]);

        // Manajer Jurnal menolak bukti
        $this->scanTestUploads();
        $rejectResponse = $this->actingAs($this->manager)
            ->post(route('journalmanager.submissions.payment.reject', $submission->id), [
                'reason' => 'Foto struk terpotong pada bagian nomor referensi dan tanggal transfer.',
            ]);

        $rejectResponse->assertRedirect();
        $payment = ArticlePayment::where('submission_id', $submission->id)->first();
        $this->assertEquals('rejected', $payment->status);
        $this->assertStringContainsString('Foto struk terpotong', $payment->verification_notes);

        // Penulis melihat catatan penolakan dan mengunggah ulang bukti baru
        $detailResponse = $this->actingAs($this->author)
            ->get(route('author.submissions.show', $submission->id));
        $detailResponse->assertSee('Bukti Transfer Perlu Diperbaiki');
        $detailResponse->assertSee('Foto struk terpotong');

        $newProof = UploadedFile::fake()->create('bukti_jelas.pdf', 600, 'application/pdf');
        $reuploadResponse = $this->actingAs($this->author)
            ->post(route('author.submissions.payment.store', $submission->id), [
                'payment_proof' => $newProof,
                'sender_bank_name' => 'Bank Sulselbar',
                'sender_account_name' => 'Fadli Rahman',
                'amount_paid' => 750000,
            ]);

        $reuploadResponse->assertSessionHas('success');
        $payment->refresh();
        $this->assertEquals('pending_verification', $payment->status);
        $this->assertNull($payment->verified_by);
        $this->assertNull($payment->verified_at);
        $this->assertNull($payment->verification_notes);
    }

    /**
     * Manajer Jurnal dapat memberikan pembebasan biaya (Waiver).
     */
    public function test_manajer_jurnal_dapat_memberikan_pembebasan_biaya_waiver(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID05',
            'title' => 'Riset Kolaboratif Komunitas Disabilitas',
            'abstract' => 'Abstrak riset kolaboratif',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $this->scanTestUploads();
        $waiveResponse = $this->actingAs($this->manager)
            ->post(route('journalmanager.submissions.payment.waive', $submission->id), [
                'waiver_reason' => 'Program afirmasi riset inklusif dan kolaborasi pemuda berprestasi.',
            ]);

        $waiveResponse->assertRedirect();
        $waiveResponse->assertSessionHas('status');

        $payment = ArticlePayment::where('submission_id', $submission->id)->first();
        $this->assertEquals('waived', $payment->status);
        $this->assertTrue($submission->fresh()->isPaymentCleared());

        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => UploadedFile::fake()->create('bukti.pdf', 1, 'application/pdf'),
        ])->assertSessionHas('error');
        $this->assertSame($payment->getAttributes(), $payment->fresh()->getAttributes());
        $this->assertDatabaseCount('files', 0);

        // Penulis melihat status pembebasan biaya
        $authorPage = $this->actingAs($this->author)
            ->get(route('author.submissions.show', $submission->id));
        $authorPage->assertSee('Pembebasan Biaya (Waiver)');
    }

    /**
     * Pemimpin Redaksi (EIC) dapat melihat bukti transfer dan memverifikasi pembayaran.
     */
    public function test_eic_dapat_melihat_bukti_dan_memverifikasi_pembayaran(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID06',
            'title' => 'Kajian Resiliensi UMKM Pasca Pandemi',
            'abstract' => 'Abstrak resiliensi UMKM',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $proof = UploadedFile::fake()->create('bukti_eic.pdf', 400, 'application/pdf');
        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => $proof,
            'sender_bank_name' => 'Bank Mandiri',
            'sender_account_name' => 'Fadli Rahman',
            'amount_paid' => 750000,
        ]);

        // EIC membuka halaman submission
        $this->scanTestUploads();
        $eicPage = $this->actingAs($this->eic)
            ->get(route('eic.submissions.show', $submission->id));
        $eicPage->assertStatus(200);
        $eicPage->assertSee('Status Pembiayaan');
        $eicPage->assertSee('Bank Mandiri');

        // EIC mengunduh bukti transfer
        $this->scanTestUploads();
        $eicDownload = $this->actingAs($this->eic)
            ->get(route('eic.submissions.payment.proof', $submission->id));
        $eicDownload->assertStatus(200);

        // EIC memverifikasi pembayaran
        $this->scanTestUploads();
        $eicVerify = $this->actingAs($this->eic)
            ->post(route('eic.submissions.payment.verify', $submission->id));
        $eicVerify->assertRedirect();
        $this->assertEquals('verified', ArticlePayment::where('submission_id', $submission->id)->first()->status);
    }

    /**
     * Manajer Jurnal dapat memperbarui konfigurasi APC dan rekening bank di Pengaturan Jurnal.
     */
    public function test_manajer_jurnal_dapat_mengubah_pengaturan_apc(): void
    {
        $this->scanTestUploads();
        $response = $this->actingAs($this->manager)
            ->post(route('journalmanager.settings.update'), [
                'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'apc_enabled' => '1',
                'apc_amount' => '1000000',
                'apc_fee' => 'Rp 1.000.000,- per naskah',
                'payment_bank_name' => 'Bank BRI',
                'payment_account_number' => '001-234-567-890',
                'payment_account_holder' => 'Bendahara Jurnal Makassar',
                'payment_instructions' => 'Transfer sebelum naskah masuk edisi.',
            ]);

        $response->assertRedirect(route('journalmanager.settings.index'));
        $response->assertSessionHas('status');

        $journal = Journal::where('slug', 'sirida-makassar')->first();
        $this->assertTrue((bool) $journal->apc_enabled);
        $this->assertEquals(1000000.00, (float) $journal->apc_amount);
        $this->assertEquals('Bank BRI', $journal->payment_bank_name);
        $this->assertEquals('001-234-567-890', $journal->payment_account_number);
    }

    /**
     * Pemimpin Redaksi (EIC) dapat menolak bukti transfer tidak sah dengan catatan alasan.
     */
    public function test_eic_dapat_menolak_bukti_pembayaran_dengan_alasan(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID07',
            'title' => 'Kajian Kualitas Air Tanah Pesisir',
            'abstract' => 'Abstrak air tanah pesisir',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $proof = UploadedFile::fake()->create('bukti_palsu.pdf', 300, 'application/pdf');
        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => $proof,
            'amount_paid' => 750000,
        ]);

        // EIC membuka halaman dan melihat tombol tolak bukti transfer
        $eicPage = $this->actingAs($this->eic)->get(route('eic.submissions.show', $submission->id));
        $eicPage->assertStatus(200);
        $eicPage->assertSee('Tolak Bukti Transfer');

        // EIC menolak bukti pembayaran
        $rejectResponse = $this->actingAs($this->eic)
            ->post(route('eic.submissions.payment.reject', $submission->id), [
                'reason' => 'Nomor referensi transfer tidak ditemukan di rekening koran kas jurnal.',
            ]);

        $rejectResponse->assertRedirect();
        $payment = ArticlePayment::where('submission_id', $submission->id)->first();
        $this->assertEquals('rejected', $payment->status);
        $this->assertEquals('Nomor referensi transfer tidak ditemukan di rekening koran kas jurnal.', $payment->verification_notes);
        $this->assertEquals($this->eic->id, $payment->verified_by);

        // Penulis melihat status ditolak beserta alasan penolakan
        $authorPage = $this->actingAs($this->author)->get(route('author.submissions.show', $submission->id));
        $authorPage->assertSee('Bukti Transfer Perlu Diperbaiki');
        $authorPage->assertSee('Nomor referensi transfer tidak ditemukan');
    }

    /**
     * Pemimpin Redaksi (EIC) dapat memberikan pembebasan biaya publikasi (Waiver).
     */
    public function test_eic_dapat_memberikan_pembebasan_biaya_waiver(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID08',
            'title' => 'Riset Dampak Iklim Terhadap Nelayan Tradisional',
            'abstract' => 'Abstrak nelayan tradisional',
            'status' => 'in_production',
            'version' => 1,
        ]);

        $waiveResponse = $this->actingAs($this->eic)
            ->post(route('eic.submissions.payment.waive', $submission->id), [
                'waiver_reason' => 'Naskah kategori undangan khusus dewan riset daerah.',
            ]);

        $waiveResponse->assertRedirect();
        $payment = ArticlePayment::where('submission_id', $submission->id)->first();
        $this->assertEquals('waived', $payment->status);
        $this->assertEquals($this->eic->id, $payment->verified_by);
        $this->assertTrue($submission->fresh()->isPaymentCleared());
    }

    /**
     * Editor Bagian (Section Editor) yang ditugaskan dapat memverifikasi dan menolak pembayaran naskah.
     */
    public function test_section_editor_dapat_memverifikasi_dan_menolak_pembayaran(): void
    {
        $submission = Submission::create([
            'journal_id' => $this->paidJournal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PAID09',
            'title' => 'Studi Transportasi Cerdas Kota Makassar',
            'abstract' => 'Abstrak transportasi cerdas',
            'status' => 'in_production',
            'version' => 1,
        ]);

        // Tugaskan Section Editor ke naskah
        EditorAssignment::create([
            'submission_id' => $submission->id,
            'editor_id' => $this->sectionEditor->id,
            'role' => 'section_editor',
            'assigned_at' => now(),
        ]);

        // Penulis mengunggah bukti transfer
        $proof = UploadedFile::fake()->create('bukti_se.pdf', 300, 'application/pdf');
        $uploadRes = $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => $proof,
            'amount_paid' => 750000,
            'sender_bank_name' => 'Bank Mandiri',
        ]);

        $this->scanTestUploads();

        // Section Editor dapat mengunduh bukti transfer
        $downloadResponse = $this->actingAs($this->sectionEditor)
            ->get(route('section-editor.submissions.payment.proof', $submission->id));
        $downloadResponse->assertStatus(200);

        // Section Editor menolak bukti tidak sah
        $rejectResponse = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.submissions.payment.reject', $submission->id), [
                'reason' => 'Nominal transfer kurang Rp 50.000.',
            ]);
        $rejectResponse->assertRedirect();
        $this->assertEquals('rejected', ArticlePayment::where('submission_id', $submission->id)->first()->status);

        // Penulis mengunggah bukti perbaikan
        $correctedProof = UploadedFile::fake()->create('bukti_se_perbaikan.pdf', 300, 'application/pdf');
        $this->actingAs($this->author)->post(route('author.submissions.payment.store', $submission->id), [
            'payment_proof' => $correctedProof,
            'amount_paid' => 750000,
            'sender_bank_name' => 'Bank Mandiri',
        ]);
        $this->scanTestUploads();

        // Section Editor memverifikasi pembayaran setelah perbaikan
        $verifyResponse = $this->actingAs($this->sectionEditor)
            ->post(route('section-editor.submissions.payment.verify', $submission->id), [
                'notes' => 'Pembayaran telah dikonfirmasi lunas.',
            ]);
        $verifyResponse->assertRedirect();
        $this->assertEquals('verified', ArticlePayment::where('submission_id', $submission->id)->first()->status);
        $this->assertTrue($submission->fresh()->isPaymentCleared());
    }
}
