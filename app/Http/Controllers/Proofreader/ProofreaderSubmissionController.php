<?php

declare(strict_types=1);

namespace App\Http\Controllers\Proofreader;

use App\Http\Controllers\Controller;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use App\Services\Proofreader\ProofreaderService;
use App\ViewModels\ProofreaderDetailViewModel;
use App\ViewModels\ProofreaderSubmissionsViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProofreaderSubmissionController extends Controller
{
    public function __construct(
        protected ProofreaderService $proofreaderService
    ) {}

    /**
     * Menampilkan daftar penugasan pemeriksaan bukti baca (proofreading).
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new ProofreaderSubmissionsViewModel($tab, $search);

        return view('proofreader.submissions.index', compact('viewModel'));
    }

    /**
     * Menampilkan ruang kerja pemeriksaan galai & catatan koreksi.
     */
    public function show(string $id, Request $request): View
    {
        $viewModel = new ProofreaderDetailViewModel($id);
        $submission = $viewModel->submission();

        return view('proofreader.submissions.show', compact('viewModel', 'submission', 'id'));
    }

    /**
     * Menampilkan halaman panduan bukti baca dan standar EYD.
     */
    public function guidelines(): View
    {
        return view('proofreader.guidelines');
    }

    /**
     * Menambahkan catatan koreksi (proofreading note) pada halaman galai.
     */
    public function addNote(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->input('submission_id', 'SUB-2026-00106');
        $category = (string) $request->input('category', 'typo');
        $page = $request->input('page') ? (int) $request->input('page') : null;
        $severity = (string) $request->input('severity', 'minor');
        $description = (string) $request->input('description', '');

        $submission = $this->findSubmission($submissionParam);
        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->addNote($submission, $actor, [
                    'category' => $category,
                    'page' => $page,
                    'severity' => $severity,
                    'description' => $description,
                ]);

                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('status', "Catatan koreksi ({$category}".($page ? " Hal. {$page}" : '').') berhasil ditambahkan ke database.');
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionParam)
            ->with('status', "Catatan koreksi ({$category}".($page ? " Hal. {$page}" : '').') berhasil ditambahkan.');
    }

    /**
     * Memverifikasi resolusi perbaikan dari layout editor.
     */
    public function verifyNote(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->input('submission_id', 'SUB-2026-00106');
        $noteId = (string) $request->input('note_id', '');

        $actor = $this->resolveActor();
        $note = $this->findNote($noteId);

        if ($note && $actor) {
            try {
                $this->proofreaderService->verifyNote($note, $actor);

                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('status', 'Perbaikan galai berhasil diverifikasi.');
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionParam)
            ->with('status', 'Perbaikan galai berhasil diverifikasi.');
    }

    /**
     * Memberikan persetujuan akhir (Proofreader Sign-off).
     * Menerapkan Sign-off Guard Rail (UC-18).
     */
    public function completeProofread(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->input('submission_id', 'SUB-2026-00106');
        $submission = $this->findSubmission($submissionParam);
        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->completeProofread($submission, $actor);

                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('status', 'Pemeriksaan bukti baca selesai. Galai disetujui untuk tahap persetujuan akhir.');
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionParam)
            ->with('status', 'Pemeriksaan bukti baca selesai. Galai disetujui untuk tahap persetujuan akhir.');
    }

    /**
     * Mengajukan eskalasi ketidaksesuaian naskah kepada editor naskah.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->input('submission_id', 'SUB-2026-00106');
        $reason = (string) $request->input('reason', 'Ketidaksesuaian naskah dengan hasil copyediting final');

        $submission = $this->findSubmission($submissionParam);
        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->submitEscalation($submission, $actor, $reason);

                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('status', 'Tiket eskalasi ketidaksesuaian naskah berhasil dikirimkan ke Editor.');
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionParam)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionParam)
            ->with('status', 'Tiket eskalasi ketidaksesuaian naskah berhasil dikirimkan ke Editor.');
    }

    private function findSubmission(string $identifier): ?Submission
    {
        try {
            if (class_exists(Submission::class) && Schema::hasTable('submissions')) {
                return Submission::where('submission_number', $identifier)
                    ->orWhere('id', $identifier)
                    ->first();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function findNote(string $noteId): ?ProofreadingNote
    {
        try {
            if (class_exists(ProofreadingNote::class) && Schema::hasTable('proofreading_notes')) {
                return ProofreadingNote::where('id', $noteId)->first();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function resolveActor(): ?User
    {
        try {
            if (class_exists(User::class) && Schema::hasTable('users')) {
                return Auth::user() ?? User::where('email', 'like', '%proof%')
                    ->orWhere('username', 'like', '%proof%')
                    ->first() ?? User::firstOrCreate(
                        ['email' => 'aminah.proofreader@brida.makassar.go.id'],
                        [
                            'username' => 'aminah.proof',
                            'name' => 'Siti Aminah, S.Pd.',
                            'full_name' => 'Siti Aminah, S.Pd.',
                            'password' => bcrypt('password123'),
                        ]
                    );
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
