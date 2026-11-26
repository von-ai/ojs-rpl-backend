<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthorJournalWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected Journal $journalA;

    protected Journal $journalB;

    protected Section $sectionA;

    protected Section $sectionB;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->journalA = Journal::create([
            'name' => 'Jurnal Penelitian dan Inovasi Daerah (SIRIDA)',
            'slug' => 'sirida-makassar',
            'initials' => 'SIRIDA',
            'issn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->journalB = Journal::create([
            'name' => 'Jurnal Teknologi Informasi dan Komunikasi (JTIK)',
            'slug' => 'jtik-makassar',
            'initials' => 'JTIK',
            'issn' => '2985-9999',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->sectionA = Section::create([
            'journal_id' => $this->journalA->id,
            'name' => 'Seksi Inovasi SIRIDA',
            'slug' => 'seksi-inovasi-sirida',
            'accepting_submissions' => true,
            'version' => 1,
        ]);

        $this->sectionB = Section::create([
            'journal_id' => $this->journalB->id,
            'name' => 'Seksi Komputasi JTIK',
            'slug' => 'seksi-komputasi-jtik',
            'accepting_submissions' => true,
            'version' => 1,
        ]);

        $this->author = User::factory()->create([
            'full_name' => 'Dr. Penulis Multi Jurnal',
            'email' => 'penulis.multijurnal@makassar.go.id',
        ]);

        // Berikan peran author di sistem
        UserRole::create([
            'user_id' => $this->author->id,
            'journal_id' => $this->journalA->id,
            'role_code' => 'author',
        ]);
        UserRole::create([
            'user_id' => $this->author->id,
            'journal_id' => $this->journalB->id,
            'role_code' => 'author',
        ]);
    }

    /**
     * Halaman Naskah Saya (index) hanya menampilkan naskah dari jurnal aktif.
     */
    public function test_author_submissions_index_only_displays_submissions_for_active_journal(): void
    {
        $subA1 = Submission::create([
            'journal_id' => $this->journalA->id,
            'section_id' => $this->sectionA->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-SIRIDA-001',
            'title' => 'Artikel Tata Kelola Inovasi SIRIDA',
            'abstract' => 'Abstrak naskah tata kelola inovasi.',
            'status' => 'submitted',
            'version' => 1,
        ]);

        $subA2 = Submission::create([
            'journal_id' => $this->journalA->id,
            'section_id' => $this->sectionA->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-SIRIDA-002',
            'title' => 'Artikel Kebijakan Publik SIRIDA',
            'abstract' => 'Abstrak naskah kebijakan publik.',
            'status' => 'in_review',
            'version' => 1,
        ]);

        $subB1 = Submission::create([
            'journal_id' => $this->journalB->id,
            'section_id' => $this->sectionB->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-JTIK-001',
            'title' => 'Artikel Jaringan Sensor Nirkabel JTIK',
            'abstract' => 'Abstrak naskah jaringan sensor.',
            'status' => 'submitted',
            'version' => 1,
        ]);

        // 1. Akses saat aktif di Jurnal A
        $responseA = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->get(route('author.submissions.index'));

        $responseA->assertOk();
        $responseA->assertSee('SUB-SIRIDA-001');
        $responseA->assertSee('Artikel Tata Kelola Inovasi SIRIDA');
        $responseA->assertSee('SUB-SIRIDA-002');
        $responseA->assertDontSee('SUB-JTIK-001');
        $responseA->assertDontSee('Artikel Jaringan Sensor Nirkabel JTIK');

        // Uji pula respons AJAX table
        $ajaxResponseA = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->getJson(route('author.submissions.index'));

        $ajaxResponseA->assertOk();
        $this->assertSame(2, $ajaxResponseA->json('total'));
        $this->assertStringContainsString('SUB-SIRIDA-001', $ajaxResponseA->json('table_html'));
        $this->assertStringNotContainsString('SUB-JTIK-001', $ajaxResponseA->json('table_html'));

        // 2. Akses saat aktif di Jurnal B
        $responseB = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->get(route('author.submissions.index'));

        $responseB->assertOk();
        $responseB->assertSee('SUB-JTIK-001');
        $responseB->assertSee('Artikel Jaringan Sensor Nirkabel JTIK');
        $responseB->assertDontSee('SUB-SIRIDA-001');
        $responseB->assertDontSee('SUB-SIRIDA-002');

        $ajaxResponseB = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->getJson(route('author.submissions.index'));

        $ajaxResponseB->assertOk();
        $this->assertSame(1, $ajaxResponseB->json('total'));
        $this->assertStringContainsString('SUB-JTIK-001', $ajaxResponseB->json('table_html'));
        $this->assertStringNotContainsString('SUB-SIRIDA-001', $ajaxResponseB->json('table_html'));
    }

    /**
     * Penulis dapat berganti jurnal lewat journal switcher dan workspace naskah berpindah.
     */
    public function test_author_can_switch_journal_and_see_submissions_of_switched_journal(): void
    {
        Submission::create([
            'journal_id' => $this->journalA->id,
            'section_id' => $this->sectionA->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-SIRIDA-099',
            'title' => 'Naskah Awal SIRIDA',
            'abstract' => 'Abstrak naskah awal sirida.',
            'status' => 'submitted',
            'version' => 1,
        ]);

        Submission::create([
            'journal_id' => $this->journalB->id,
            'section_id' => $this->sectionB->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-JTIK-099',
            'title' => 'Naskah Khusus JTIK',
            'abstract' => 'Abstrak naskah khusus jtik.',
            'status' => 'submitted',
            'version' => 1,
        ]);

        // Awalnya di Jurnal A
        $switchResponse = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->post(route('journal.switch'), [
                'journal_id' => $this->journalB->id,
                'current_role' => 'author',
            ]);

        $switchResponse->assertRedirect(route('author.submissions.index'));
        $switchResponse->assertSessionHas('active_journal_id', $this->journalB->id);
        $switchResponse->assertSessionHas('journal_switched_toast', "Anda sekarang bekerja di {$this->journalB->name}.");

        // Setelah beralih, buka halaman index
        $indexResponse = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->get(route('author.submissions.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('SUB-JTIK-099');
        $indexResponse->assertSee('Naskah Khusus JTIK');
        $indexResponse->assertDontSee('SUB-SIRIDA-099');
    }

    /**
     * Penulis tidak dapat mengakses halaman detail naskah dari jurnal lain (404 Not Found).
     */
    public function test_author_cannot_access_submission_detail_from_another_journal(): void
    {
        $subA = Submission::create([
            'journal_id' => $this->journalA->id,
            'section_id' => $this->sectionA->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-SIRIDA-777',
            'title' => 'Naskah Rahasia SIRIDA',
            'abstract' => 'Abstrak naskah rahasia sirida.',
            'status' => 'submitted',
            'version' => 1,
        ]);

        $subB = Submission::create([
            'journal_id' => $this->journalB->id,
            'section_id' => $this->sectionB->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-JTIK-888',
            'title' => 'Naskah Rahasia JTIK',
            'abstract' => 'Abstrak naskah rahasia jtik.',
            'status' => 'submitted',
            'version' => 1,
        ]);

        // Aktif di Jurnal A: Buka naskah Jurnal A -> 200 OK
        $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->get(route('author.submissions.show', $subA->id))
            ->assertOk()
            ->assertSee('SUB-SIRIDA-777');

        // Aktif di Jurnal A: Buka naskah Jurnal B -> 404 Not Found
        $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->get(route('author.submissions.show', $subB->id))
            ->assertNotFound();

        // Aktif di Jurnal B: Buka naskah Jurnal A -> 404 Not Found
        $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->get(route('author.submissions.show', $subA->id))
            ->assertNotFound();

        // Aktif di Jurnal B: Buka naskah Jurnal B -> 200 OK
        $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->get(route('author.submissions.show', $subB->id))
            ->assertOk()
            ->assertSee('SUB-JTIK-888');
    }

    /**
     * Aksi mutasi seperti kirim revisi atau tarik naskah ditolak jika naskah milik jurnal lain.
     */
    public function test_author_cannot_perform_mutations_on_submission_from_another_journal(): void
    {
        $subB = Submission::create([
            'journal_id' => $this->journalB->id,
            'section_id' => $this->sectionB->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-JTIK-999',
            'title' => 'Naskah Mutasi JTIK',
            'abstract' => 'Abstrak naskah mutasi jtik.',
            'status' => 'revisions_requested',
            'version' => 1,
        ]);

        // Ketika aktif di Jurnal A, mencoba submit revisi pada naskah Jurnal B
        $revisionFile = UploadedFile::fake()->create('revisi.pdf', 500, 'application/pdf');
        $responseRev = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->post(route('author.submissions.revisions.store', $subB->id), [
                'manuscript_file' => $revisionFile,
                'note' => 'Catatan revisi',
            ]);

        $responseRev->assertRedirect(route('author.submissions.index'));
        $responseRev->assertSessionHas('error', 'Naskah ilmiah tidak ditemukan atau Anda tidak memiliki hak akses.');

        // Ketika aktif di Jurnal A, mencoba menarik naskah Jurnal B
        $responseWithdraw = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->post(route('author.submissions.withdraw', $subB->id));

        $responseWithdraw->assertRedirect(route('author.submissions.index'));
        $responseWithdraw->assertSessionHas('error', 'Naskah ilmiah tidak ditemukan atau Anda tidak memiliki hak akses.');
    }

    /**
     * API Seksi jurnal pada wizard pengajuan naskah dibatasi sesuai jurnal aktif.
     */
    public function test_wizard_sections_api_is_scoped_to_active_journal(): void
    {
        // 1. Seksi API saat aktif di Jurnal A
        $responseA = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->getJson(route('author.submissions.sections'));

        $responseA->assertOk();
        $sectionsDataA = collect($responseA->json('data'));
        $this->assertTrue($sectionsDataA->contains('name', 'Seksi Inovasi SIRIDA'));
        $this->assertFalse($sectionsDataA->contains('name', 'Seksi Komputasi JTIK'));

        // 2. Seksi API saat aktif di Jurnal B
        $responseB = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->getJson(route('author.submissions.sections'));

        $responseB->assertOk();
        $sectionsDataB = collect($responseB->json('data'));
        $this->assertTrue($sectionsDataB->contains('name', 'Seksi Komputasi JTIK'));
        $this->assertFalse($sectionsDataB->contains('name', 'Seksi Inovasi SIRIDA'));
    }

    /**
     * Melanjutkan draf tersimpan hanya berlaku untuk draf pada jurnal aktif.
     */
    public function test_author_cannot_continue_draft_from_another_journal(): void
    {
        $draftA = Submission::create([
            'journal_id' => $this->journalA->id,
            'section_id' => $this->sectionA->id,
            'author_id' => $this->author->id,
            'submission_number' => 'DRAFT-SIRIDA-001',
            'title' => 'Draf Riset SIRIDA',
            'abstract' => 'Abstrak draf sirida.',
            'status' => 'draft',
            'version' => 1,
        ]);

        $draftB = Submission::create([
            'journal_id' => $this->journalB->id,
            'section_id' => $this->sectionB->id,
            'author_id' => $this->author->id,
            'submission_number' => 'DRAFT-JTIK-001',
            'title' => 'Draf Riset JTIK',
            'abstract' => 'Abstrak draf jtik.',
            'status' => 'draft',
            'version' => 1,
        ]);

        // Saat di Jurnal A, mencoba membuka draf JTIK
        $response = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->get(route('author.submissions.create', ['draft_id' => $draftB->id]));

        $response->assertOk();
        // Draf tidak dimuat (draft bernilai null pada view)
        $this->assertNull($response->viewData('draft'));

        // Saat di Jurnal A, membuka draf SIRIDA
        $responseValid = $this->actingAs($this->author)
            ->withSession(['active_journal_id' => $this->journalA->id])
            ->get(route('author.submissions.create', ['draft_id' => $draftA->id]));

        $responseValid->assertOk();
        $this->assertNotNull($responseValid->viewData('draft'));
        $this->assertSame($draftA->id, $responseValid->viewData('draft')->id);
    }
}
