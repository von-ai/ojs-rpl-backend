<?php

declare(strict_types=1);

namespace App\Http\Controllers\JournalManager;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use App\Services\JournalManager\JournalManagerService;
use App\Services\Payment\ArticlePaymentService;
use App\Services\PrivateFileService;
use App\ViewModels\JournalManagerIssueDetailViewModel;
use App\ViewModels\JournalManagerIssuesViewModel;
use App\ViewModels\JournalManagerSectionsViewModel;
use App\ViewModels\JournalManagerSettingsViewModel;
use App\ViewModels\JournalManagerUsersViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JournalManagerController extends Controller
{
    public function __construct(
        protected JournalManagerService $journalManagerService,
        protected ArticlePaymentService $articlePaymentService
    ) {}

    /**
     * Menampilkan katalog seluruh edisi terbitan jurnal (Draf, Terjadwal, dan Terbit).
     */
    public function issues(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new JournalManagerIssuesViewModel($tab, $search);

        return view('journalmanager.issues.index', compact('viewModel'));
    }

    /**
     * Menyimpan pembuatan edisi nomor terbitan baru.
     */
    public function storeIssue(Request $request): RedirectResponse
    {
        $request->validate(['volume' => ['required', 'string', 'max:20'], 'number' => ['required', 'string', 'max:20'], 'year' => ['required', 'integer', 'min:1900', 'max:9999'], 'title' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string', 'max:10000'], 'doi_prefix' => ['nullable', 'string', 'max:191', 'regex:~^10\.\d{4,9}/[A-Za-z0-9._;()/:+-]+$~']]);
        $volume = (string) $request->input('volume', '6');
        $number = (string) $request->input('number', '2');
        $year = (string) $request->input('year', '2026');

        try {
            if (Schema::hasTable('journals') && Schema::hasTable('issues')) {
                $journal = $this->resolveJournal();
                $this->journalManagerService->createIssue($journal, $request->all(), Auth::user());
            }

            return redirect()->route('journalmanager.issues.index')
                ->with('status', "Edisi terbitan Vol. {$volume} No. {$number} ({$year}) berhasil dibuat sebagai draf.");
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.issues.index')
                ->withErrors(['issue' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan ruang kerja penyusunan daftar isi edisi (Table of Contents).
     */
    public function showIssue(string $id, Request $request): View
    {
        $viewModel = new JournalManagerIssueDetailViewModel($id);
        $issue = $viewModel->issue();

        if (! $issue) {
            abort(404, 'Edisi terbitan tidak ditemukan.');
        }

        return view('journalmanager.issues.show', compact('issue', 'id'));
    }

    /**
     * Memasukkan artikel berstatus siap terbit ke dalam daftar isi edisi.
     */
    public function addArticleToIssue(string $id, Request $request): RedirectResponse
    {
        $data = $request->validate(['article_id' => ['required', 'string'], 'pages' => ['nullable', 'string', 'max:50']]);
        $issue = Issue::findOrFail($id);
        $submission = Submission::where('id', $data['article_id'])->orWhere('submission_number', $data['article_id'])->firstOrFail();
        try {
            $this->journalManagerService->addArticleToIssue($issue, $submission, $data, Auth::user());
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.issues.show', $id)->withErrors(['article' => $e->getMessage()]);
        }

        return redirect()->route('journalmanager.issues.show', $id)->with('status', "Naskah '{$submission->title}' berhasil ditambahkan ke daftar isi edisi.");
    }

    /**
     * Mempublikasikan edisi terbitan resmi secara instan.
     */
    public function publishIssue(string $id, Request $request): RedirectResponse
    {
        $issue = Issue::findOrFail($id);
        try {
            $this->journalManagerService->publishIssue($issue, Auth::user());
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.issues.show', $id)->withErrors(['publish' => $e->getMessage()]);
        }

        return redirect()->route('journalmanager.issues.show', $id)->with('status', 'Edisi terbitan berkala resmi dipublikasikan ke pembaca dan katalog indeks.');
    }

    /**
     * Menetapkan edisi tertentu sebagai edisi berjalan aktif (Current Issue).
     */
    public function setCurrentIssue(string $id, Request $request): RedirectResponse
    {
        $issue = Issue::findOrFail($id);
        try {
            $this->journalManagerService->setCurrentIssue($issue, Auth::user());
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.issues.index')->withErrors(['current' => $e->getMessage()]);
        }

        return redirect()->route('journalmanager.issues.index')->with('status', 'Edisi berhasil ditetapkan sebagai Terbitan Berjalan (Current Issue) jurnal.');
    }

    /**
     * Menampilkan manajemen pengguna, tim redaksi, dan direktori penelaah (Reviewer Directory).
     */
    public function users(Request $request): View
    {
        $role = (string) $request->query('role', 'all');
        $search = (string) $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));

        $viewModel = new JournalManagerUsersViewModel($role, $search, $page);

        return view('journalmanager.users.index', compact('viewModel'));
    }

    /**
     * Menetapkan peran jurnal baru kepada pengguna.
     */
    public function assignRole(Request $request): RedirectResponse
    {
        $rawRole = (string) $request->input('role_code', 'Section Editor');
        $userId = $request->input('user_id');
        $userEmail = trim((string) ($request->input('email') ?? $request->input('user_email') ?? $request->input('user_name')));
        $expertiseKeywords = $request->input('expertise_keywords');

        // Normalisasi format kode peran (misal: 'Section Editor' -> 'section_editor')
        $roleCode = Str::snake(strtolower(str_replace(' ', '_', $rawRole)));

        try {
            if (Schema::hasTable('users') && Schema::hasTable('journals')) {
                $user = null;
                if (! empty($userId)) {
                    $user = User::find($userId);
                }
                if (! $user && ! empty($userEmail)) {
                    // Pencarian pengguna berdasarkan EMAIL atau USERNAME (tabel users tidak memiliki kolom 'name')
                    $user = User::where('email', $userEmail)
                        ->orWhere('username', $userEmail)
                        ->first();

                    if (! $user) {
                        throw new DomainException("Pengguna dengan alamat email '{$userEmail}' tidak ditemukan dalam sistem.");
                    }
                }
                if (! $user) {
                    throw new DomainException('Pilih pengguna terdaftar yang akan menerima peran.');
                }

                $journal = $this->resolveJournal();

                if ($user && $journal) {
                    $this->journalManagerService->assignUserRole($user, $journal, $roleCode, $expertiseKeywords, Auth::user());
                }
            }

            $displayRole = str_contains($rawRole, '_') ? UserRole::roleLabel($roleCode) : $rawRole;

            return redirect()->route('journalmanager.users.index')
                ->with('status', "Peran '{$displayRole}' berhasil ditetapkan kepada pengguna.");
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.users.index')
                ->withErrors(['role' => $e->getMessage()]);
        }
    }

    /**
     * Memicu pengiriman tautan reset kata sandi ke email pengguna.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $email = (string) $request->input('email', 'pengguna@brida.makassar.go.id');

        try {
            if (Schema::hasTable('users')) {
                $this->journalManagerService->requestPasswordReset($email, Auth::user());
            }

            return redirect()->route('journalmanager.users.index')
                ->with('status', "Tautan reset kata sandi berhasil dikirimkan ke alamat email {$email}.");
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.users.index')
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan daftar dan pengelolaan bidang naskah (Sections).
     */
    public function sections(Request $request): View
    {
        $viewModel = new JournalManagerSectionsViewModel;

        return view('journalmanager.sections.index', compact('viewModel'));
    }

    /**
     * Menyimpan penambahan seksi / bidang naskah baru.
     */
    public function storeSection(Request $request): RedirectResponse
    {
        $name = (string) $request->input('name', 'Bidang Riset Baru');

        try {
            if (Schema::hasTable('journals') && Schema::hasTable('sections')) {
                $journal = $this->resolveJournal();
                $this->journalManagerService->createSection($journal, $request->all(), Auth::user());
            }

            return redirect()->route('journalmanager.sections.index')
                ->with('status', "Bidang naskah '{$name}' berhasil ditambahkan ke jurnal.");
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.sections.index')
                ->withErrors(['name' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan halaman konfigurasi identitas jurnal dan kebijakan editorial.
     */
    public function settings(Request $request): View
    {
        $viewModel = new JournalManagerSettingsViewModel;

        return view('journalmanager.settings.index', compact('viewModel'));
    }

    /**
     * Memperbarui pengaturan identitas dan kebijakan jurnal.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        try {
            if (Schema::hasTable('journals')) {
                $journal = $this->resolveJournal();
                $this->journalManagerService->updateJournalSettings($journal, $request->all(), Auth::user());
            }

            return redirect()->route('journalmanager.settings.index')
                ->with('status', 'Pengaturan identitas dan kebijakan jurnal berhasil disimpan.');
        } catch (DomainException $e) {
            return redirect()->route('journalmanager.settings.index')
                ->withErrors(['settings' => $e->getMessage()]);
        }
    }

    /**
     * Memverifikasi dan menyetujui bukti pembayaran APC naskah (Lunas).
     */
    public function verifyPayment(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment) {
            return back()->withErrors(['payment' => 'Catatan tagihan pembayaran untuk naskah ini tidak ditemukan.']);
        }

        $notes = (string) $request->input('notes', 'Pembayaran diverifikasi sah oleh Manajer Jurnal.');
        $verifier = Auth::user() ?? User::firstOrFail();

        try {
            $this->articlePaymentService->verifyPayment($payment, $verifier, 'manager', $notes);
        } catch (DomainException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return back()->with('status', "Pembayaran untuk naskah '{$submission->title}' berhasil diverifikasi (Status: Lunas).");
    }

    /**
     * Menolak bukti pembayaran naskah yang tidak sesuai.
     */
    public function rejectPayment(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Alasan penolakan bukti pembayaran wajib diisi.',
        ]);

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment) {
            return back()->withErrors(['payment' => 'Catatan tagihan pembayaran untuk naskah ini tidak ditemukan.']);
        }

        $reason = (string) $request->input('reason');
        $verifier = Auth::user() ?? User::firstOrFail();

        try {
            $this->articlePaymentService->rejectPayment($payment, $verifier, 'manager', $reason);
        } catch (DomainException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return back()->with('status', "Bukti transfer naskah '{$submission->title}' berhasil ditolak. Penulis telah diinstruksikan untuk mengirimkan berkas bukti yang valid.");
    }

    /**
     * Memberikan pembebasan biaya publikasi (Waiver).
     */
    public function waivePayment(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'waiver_reason' => ['required', 'string', 'max:1000'],
        ], [
            'waiver_reason.required' => 'Alasan pembebasan biaya wajib diisi.',
        ]);

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment) {
            $payment = $this->articlePaymentService->resolveOrCreatePayment($submission, Auth::user());
        }

        if (! $payment) {
            return back()->withErrors(['payment' => 'Gagal menginisialisasi catatan pembayaran naskah.']);
        }

        $reason = (string) $request->input('waiver_reason');
        $verifier = Auth::user() ?? User::firstOrFail();

        try {
            $this->articlePaymentService->waivePayment($payment, $verifier, 'manager', $reason);
        } catch (DomainException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return back()->with('status', "Biaya pemrosesan naskah '{$submission->title}' berhasil dibebaskan (Waiver).");
    }

    /**
     * Mengunduh berkas bukti transfer pembayaran APC untuk ditinjau redaksi.
     */
    public function downloadPaymentProof(Request $request, string $id): StreamedResponse|BinaryFileResponse
    {
        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment || ! $payment->fileEntry) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        return app(PrivateFileService::class)->stream($payment->fileEntry);
    }

    /**
     * Mengambil entitas jurnal aktif sistem atau inisialisasi default.
     */
    private function resolveJournal(): Journal
    {
        return Journal::query()->firstOrFail();
    }
}
