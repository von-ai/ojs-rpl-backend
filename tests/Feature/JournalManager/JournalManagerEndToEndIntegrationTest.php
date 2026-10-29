<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use App\Mail\JournalManager\IssuePublishedMail;
use App\Mail\JournalManager\PasswordResetRequestedMail;
use App\Mail\JournalManager\RoleAssignedMail;
use App\Models\Issue;
use App\Models\IssueArticle;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JournalManagerEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memastikan data hasil seeding realistis mengisi katalog edisi, pengguna, dan pengaturan jurnal.
     */
    public function test_seeded_database_populates_journal_manager_models(): void
    {
        $journal = Journal::where('slug', 'sirida-makassar')->firstOrFail();
        $this->assertNotNull($journal->current_issue_id);

        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->assertTrue(UserRole::where('user_id', $manager->id)->where('role_code', 'manager')->exists());

        $currentIssue = Issue::where('id', $journal->current_issue_id)->firstOrFail();
        $this->assertTrue($currentIssue->is_current);
        $this->assertTrue($currentIssue->isPublished());
        $this->assertGreaterThan(0, $currentIssue->issueArticles()->count());
    }

    /**
     * Skenario 1: Pembuatan nomor terbitan baru dan invariant guard pencegah duplikasi.
     */
    public function test_pembuatan_edisi_baru_dan_guard_duplikasi(): void
    {
        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        // Buat edisi Vol. 7 No. 1 (2027) baru
        $response = $this->post('/journalmanager/issues', [
            'volume' => '7',
            'number' => '1',
            'year' => '2027',
            'title' => 'Edisi Khusus Riset Kelautan dan Pesisir',
            'description' => 'Edisi masa depan berfokus pada mitigasi abrasi dan kemaritiman.',
        ]);

        $response->assertRedirect('/journalmanager/issues');
        $response->assertSessionHas('status');

        $issue = Issue::where('volume', '7')->where('number', '1')->where('year', 2027)->firstOrFail();
        $this->assertSame('draft', $issue->status);
        $this->assertFalse($issue->is_current);
        $this->assertStringContainsString('Edisi Khusus Riset Kelautan', $issue->title);
        $this->assertSame('10.61234/sirida.v7i1', $issue->doi_prefix);

        // Uji Invariant Guard: Upaya duplikasi volume dan nomor pada tahun yang sama ditolak
        $duplicateResponse = $this->post('/journalmanager/issues', [
            'volume' => '7',
            'number' => '1',
            'year' => '2027',
        ]);

        $duplicateResponse->assertRedirect('/journalmanager/issues');
        $duplicateResponse->assertSessionHasErrors(['issue']);
    }

    /**
     * Skenario 2: Penyusunan Table of Contents, penomoran urut, rentang halaman, dan DOI otomatis.
     */
    public function test_penyusunan_daftar_isi_edisi_dan_kalkulasi_halaman(): void
    {
        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $journal = Journal::firstOrFail();

        // Buat edisi draf baru yang masih kosong
        $issue = Issue::create([
            'journal_id' => $journal->id,
            'volume' => '8',
            'number' => '1',
            'year' => 2028,
            'title' => 'Vol. 8 No. 1 (2028)',
            'status' => 'draft',
            'is_current' => false,
            'doi_prefix' => '10.61234/sirida.v8i1',
        ]);

        // Ambil naskah yang belum masuk edisi
        $availableSubmissions = Submission::where('journal_id', $journal->id)
            ->whereDoesntHave('issueArticle')
            ->get();

        $this->assertGreaterThanOrEqual(1, $availableSubmissions->count());
        $sub1 = $availableSubmissions[0];

        // Masukkan naskah 1
        $resAdd1 = $this->post("/journalmanager/issues/{$issue->id}/add-article", [
            'article_id' => $sub1->id,
            'article_title' => $sub1->title,
        ]);

        $resAdd1->assertRedirect("/journalmanager/issues/{$issue->id}");
        $resAdd1->assertSessionHas('status');

        $art1 = IssueArticle::where('issue_id', $issue->id)->where('submission_id', $sub1->id)->firstOrFail();
        $this->assertSame(1, $art1->position);
        $this->assertSame('1-14', $art1->pages);
        $this->assertSame('10.61234/sirida.v8i1.01', $art1->doi);

        // Uji Invariant Guard: Naskah tidak dapat ditambahkan ganda ke edisi yang sama
        $resDup = $this->post("/journalmanager/issues/{$issue->id}/add-article", [
            'article_id' => $sub1->id,
            'article_title' => $sub1->title,
        ]);
        $resDup->assertRedirect("/journalmanager/issues/{$issue->id}");
        $resDup->assertSessionHasErrors(['article']);

        // Jika ada naskah kedua, masukkan dan verifikasi kelanjutan halaman 15-28
        if ($availableSubmissions->count() >= 2) {
            $sub2 = $availableSubmissions[1];
            $this->post("/journalmanager/issues/{$issue->id}/add-article", [
                'article_id' => $sub2->id,
                'article_title' => $sub2->title,
            ]);

            $art2 = IssueArticle::where('issue_id', $issue->id)->where('submission_id', $sub2->id)->firstOrFail();
            $this->assertSame(2, $art2->position);
            $this->assertSame('15-28', $art2->pages);
            $this->assertSame('10.61234/sirida.v8i1.02', $art2->doi);
        }
    }

    /**
     * Skenario 3: Invariant Guard Publikasi — Edisi kosong ditolak, edisi bernaskah sukses terbit & kirim email.
     */
    public function test_publikasi_edisi_dengan_empty_guard_dan_email(): void
    {
        Mail::fake();

        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $journal = Journal::firstOrFail();

        // 1. Uji Empty Guard: Buat edisi tanpa artikel
        $emptyIssue = Issue::create([
            'journal_id' => $journal->id,
            'volume' => '9',
            'number' => '1',
            'year' => 2029,
            'title' => 'Vol. 9 No. 1 (2029)',
            'status' => 'draft',
            'is_current' => false,
        ]);

        $resEmptyPublish = $this->post("/journalmanager/issues/{$emptyIssue->id}/publish");
        $resEmptyPublish->assertRedirect("/journalmanager/issues/{$emptyIssue->id}");
        $resEmptyPublish->assertSessionHasErrors(['publish']);

        $emptyIssue->refresh();
        $this->assertSame('draft', $emptyIssue->status);
        Mail::assertNothingSent();

        // 2. Tambahkan 1 naskah ke edisi
        $sub = Submission::where('journal_id', $journal->id)->firstOrFail();
        IssueArticle::firstOrCreate(
            ['issue_id' => $emptyIssue->id, 'submission_id' => $sub->id],
            ['position' => 1, 'pages' => '1-12', 'doi' => '10.61234/sirida.v9i1.01']
        );

        // 3. Publikasikan edisi setelah berisi naskah
        $resValidPublish = $this->post("/journalmanager/issues/{$emptyIssue->id}/publish");
        $resValidPublish->assertRedirect("/journalmanager/issues/{$emptyIssue->id}");
        $resValidPublish->assertSessionHas('status');

        $emptyIssue->refresh();
        $this->assertSame('published', $emptyIssue->status);
        $this->assertNotNull($emptyIssue->published_at);

        // Verifikasi pengiriman Mailable IssuePublishedMail
        Mail::assertSent(IssuePublishedMail::class, function (IssuePublishedMail $mail) use ($emptyIssue) {
            return $mail->issue->id === $emptyIssue->id;
        });
    }

    /**
     * Skenario 4: Penetapan Current Issue dan Invariant Guard status published.
     */
    public function test_penetapan_current_issue_dan_guard_status(): void
    {
        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $journal = Journal::firstOrFail();

        // 1. Guard: Edisi draf tidak dapat dijadikan Current Issue
        $draftIssue = Issue::create([
            'journal_id' => $journal->id,
            'volume' => '10',
            'number' => '1',
            'year' => 2030,
            'status' => 'draft',
            'is_current' => false,
        ]);

        $resDraft = $this->post("/journalmanager/issues/{$draftIssue->id}/set-current");
        $resDraft->assertRedirect('/journalmanager/issues');
        $resDraft->assertSessionHasErrors(['current']);

        // 2. Edisi published sah dapat ditetapkan sebagai Current Issue
        $publishedIssue = Issue::create([
            'journal_id' => $journal->id,
            'volume' => '10',
            'number' => '2',
            'year' => 2030,
            'status' => 'published',
            'is_current' => false,
            'published_at' => now(),
        ]);

        $resPublished = $this->post("/journalmanager/issues/{$publishedIssue->id}/set-current");
        $resPublished->assertRedirect('/journalmanager/issues');
        $resPublished->assertSessionHas('status');

        $publishedIssue->refresh();
        $this->assertTrue($publishedIssue->is_current);

        $journal->refresh();
        $this->assertSame($publishedIssue->id, $journal->current_issue_id);

        // Pastikan edisi lain tidak lagi aktif sebagai current
        $oldCurrent = Issue::where('id', '!=', $publishedIssue->id)->where('is_current', true)->count();
        $this->assertSame(0, $oldCurrent);
    }

    /**
     * Skenario 5: Manajemen Bidang Naskah (Sections) dan pencegahan slug duplikat.
     */
    public function test_manajemen_seksi_bidang_naskah_baru(): void
    {
        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $journal = Journal::firstOrFail();

        $response = $this->post('/journalmanager/sections', [
            'name' => 'Kajian Transformasi Smart City Makassar',
            'description' => 'Fokus pada inovasi integrasi IoT dan tata kelola digital lorong.',
            'accepting_submissions' => '1',
        ]);

        $response->assertRedirect('/journalmanager/sections');
        $response->assertSessionHas('status');

        $section = Section::where('name', 'Kajian Transformasi Smart City Makassar')->firstOrFail();
        $this->assertSame($journal->id, $section->journal_id);
        $this->assertTrue($section->accepting_submissions);
        $this->assertNotEmpty($section->slug);
    }

    /**
     * Skenario 6: Konfigurasi pengaturan metadata dan kebijakan editorial jurnal.
     */
    public function test_perbaruan_pengaturan_dan_kebijakan_jurnal(): void
    {
        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $journal = Journal::firstOrFail();

        $response = $this->post('/journalmanager/settings', [
            'name' => 'Jurnal Riset dan Inovasi Daerah Terpadu',
            'initials' => 'SIRIDA-PLUS',
            'issn_print' => '9999-1111',
            'issn_online' => '9999-2222',
            'editorial_email' => 'redaksi.baru@makassar.go.id',
            'contact_phone' => '+62 411 999-000',
            'focus_scope' => 'Fokus kajian empiris kebijakan strategis.',
            'frequency' => '3 Kali Setahun',
            'apc_fee' => 'Rp 0,- Bebas Biaya',
            'open_access_policy' => 'Gold Open Access Penuh.',
            'peer_review_policy' => 'Triple-Blind Review.',
            'license' => 'CC BY-SA 4.0',
        ]);

        $response->assertRedirect('/journalmanager/settings');
        $response->assertSessionHas('status');

        $journal->refresh();
        $this->assertSame('Jurnal Riset dan Inovasi Daerah Terpadu', $journal->name);
        $this->assertSame('SIRIDA-PLUS', $journal->initials);
        $this->assertSame('9999-1111', $journal->issn);
        $this->assertSame('9999-2222', $journal->eissn);
        $this->assertSame('redaksi.baru@makassar.go.id', $journal->editorial_email);
        $this->assertSame('CC BY-SA 4.0', $journal->license);
    }

    /**
     * Skenario 7: Penetapan Peran Pengguna (Role Assignment) dan pengiriman notifikasi email.
     */
    public function test_penetapan_peran_pengguna_dan_notifikasi_email(): void
    {
        Mail::fake();

        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $targetUser = User::where('email', 'andi.ilyas@unhas.ac.id')->firstOrFail();
        $journal = Journal::firstOrFail();

        $response = $this->post('/journalmanager/users/assign-role', [
            'user_id' => $targetUser->id,
            'role_code' => 'reviewer',
            'expertise_keywords' => 'Tata Kelola Kota, Mitigasi Risiko Kebijakan',
        ]);

        $response->assertRedirect('/journalmanager/users');
        $response->assertSessionHas('status');

        $role = UserRole::where('user_id', $targetUser->id)
            ->where('journal_id', $journal->id)
            ->where('role_code', 'reviewer')
            ->firstOrFail();

        $this->assertContains('Tata Kelola Kota', $role->expertise_keywords);

        Mail::assertSent(RoleAssignedMail::class, function (RoleAssignedMail $mail) use ($targetUser) {
            return $mail->user->email === $targetUser->email;
        });
    }

    /**
     * Skenario 8: Pemicuan Reset Kata Sandi Akun Pengguna via Mailpit.
     */
    public function test_pemicuan_reset_kata_sandi_pengguna(): void
    {
        Mail::fake();

        $manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($manager);

        $user = User::where('email', 'budi.hartono@eng.unhas.ac.id')->firstOrFail();

        // 1. Sukses kirim ke pengguna sah
        $response = $this->post('/journalmanager/users/reset-password', [
            'email' => $user->email,
        ]);

        $response->assertRedirect('/journalmanager/users');
        $response->assertSessionHas('status');

        Mail::assertSent(PasswordResetRequestedMail::class, function (PasswordResetRequestedMail $mail) use ($user) {
            return $mail->user->email === $user->email;
        });

        // 2. Gagal jika email tidak terdaftar
        $resNotFound = $this->post('/journalmanager/users/reset-password', [
            'email' => 'email.tidak.ada@unhas.ac.id',
        ]);

        $resNotFound->assertRedirect('/journalmanager/users');
        $resNotFound->assertSessionHasErrors(['email']);
    }
}
