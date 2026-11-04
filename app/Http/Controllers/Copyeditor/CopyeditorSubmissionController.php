<?php

declare(strict_types=1);

namespace App\Http\Controllers\Copyeditor;

use App\Http\Controllers\Controller;
use App\Models\Copyedit;
use App\Models\FileEntry;
use App\Models\Submission;
use App\Models\User;
use App\Services\Copyeditor\CopyeditorService;
use App\Services\PortalAccess;
use App\Services\PrivateFileService;
use App\ViewModels\CopyeditorSubmissionDetailViewModel;
use App\ViewModels\CopyeditorSubmissionsViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CopyeditorSubmissionController extends Controller
{
    public function __construct(
        protected CopyeditorService $copyeditorService
    ) {}

    /**
     * Menampilkan daftar penugasan copyediting naskah.
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new CopyeditorSubmissionsViewModel($tab, $search);

        return view('copyeditor.submissions.index', compact('viewModel'));
    }

    /**
     * Menampilkan ruang kerja naskah copyedit spesifik.
     */
    public function show(string $id, Request $request): View
    {
        $copyedit = $this->findCopyedit($id);
        if ($copyedit) {
            $this->authorizeCopyeditorForCopyedit($copyedit);
        }

        $viewModel = new CopyeditorSubmissionDetailViewModel($id);
        $submission = $viewModel->submission();

        if (! $submission) {
            abort(404, "Naskah penugasan copyediting '{$id}' tidak ditemukan.");
        }

        return view('copyeditor.submissions.show', compact('submission'));
    }

    /**
     * Menampilkan halaman dedikasi Panduan Gaya Selingkung & Format Naskah BRIDA.
     */
    public function guidelines(): View
    {
        return view('copyeditor.guidelines');
    }

    /**
     * Tautan kompatibilitas atau shortcut ke ruang kerja naskah utama.
     */
    public function catalog(Request $request, string $id = 'SUB-2026-00106'): View|RedirectResponse
    {
        return $this->show($id, $request);
    }

    /**
     * Mengunduh berkas fisik (naskah acuan atau berkas hasil copyediting).
     */
    public function downloadFile(string $id): StreamedResponse
    {
        $file = FileEntry::findOrFail($id);

        return app(PrivateFileService::class)->download($file, Auth::user());
    }

    /**
     * Aksi unggah berkas baru ke Output Set naskah.
     */
    public function uploadFile(Request $request): RedirectResponse
    {
        $request->validate([
            'submission_id' => 'required|string',
            'copyedit_file' => 'required|file|mimes:docx,doc,pdf|max:20480',
        ]);

        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $copyedit = $this->findCopyedit($submissionId);
        $this->authorizeCopyeditorForCopyedit($copyedit);

        if ($copyedit && $request->hasFile('copyedit_file') && $request->file('copyedit_file')->isValid()) {
            $user = Auth::user();
            $this->copyeditorService->uploadOutputFile($copyedit, $request->file('copyedit_file'), $user);
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Berkas naskah hasil suntingan berhasil diunggah. Persetujuan penulis otomatis diperbarui untuk meninjau berkas terbaru.');
    }

    /**
     * Permohonan persetujuan penulis atas berkas hasil suntingan.
     */
    public function requestApproval(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $copyedit = $this->findCopyedit($submissionId);
        $this->authorizeCopyeditorForCopyedit($copyedit);

        if ($copyedit) {
            try {
                $this->copyeditorService->requestAuthorApproval($copyedit, $request->input('note'));
            } catch (DomainException $e) {
                return redirect()->route('copyeditor.submissions.show', $submissionId)
                    ->with('error', $e->getMessage());
            }
        }

        $authorName = $copyedit->submission->author?->name ?? 'Penulis korespondensi';

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', "Permintaan persetujuan telah dikirimkan ke Penulis Korespondensi ({$authorName}). Notifikasi telah masuk ke antrean email.");
    }

    /**
     * Pengajuan mediasi ke Section Editor / EIC.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $copyedit = $this->findCopyedit($submissionId);
        $this->authorizeCopyeditorForCopyedit($copyedit);

        if ($copyedit) {
            $user = Auth::user();
            $reason = (string) $request->input('reason', 'Keberatan terhadap hasil suntingan teks');
            $category = $request->input('category');

            $this->copyeditorService->submitEscalation(
                $copyedit,
                $user,
                $reason,
                $category ? (string) $category : null
            );
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Tiket mediasi berhasil diajukan ke Section Editor (editor yang ditugaskan). Tim redaksi akan membantu penyelesaian catatan.');
    }

    /**
     * Penyelesaian copyediting dan serah terima ke Layout Editor.
     */
    public function completeHandoff(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $copyedit = $this->findCopyedit($submissionId);
        $this->authorizeCopyeditorForCopyedit($copyedit);

        if ($copyedit) {
            $user = Auth::user();
            $layoutEditor = User::where('status', 'active')->whereNotNull('email_verified_at')
                ->whereHas('userRoles', fn ($q) => $q->where('role_code', 'layout_editor')->where('journal_id', $copyedit->submission->journal_id))->first();
            if (! $layoutEditor) {
                return back()->withErrors(['handoff' => 'Belum ada Layout Editor aktif pada jurnal ini.']);
            }

            try {
                $this->copyeditorService->completeHandoff(
                    $copyedit,
                    $user,
                    $layoutEditor->id,
                    $request->input('notes') ? (string) $request->input('notes') : null
                );
            } catch (DomainException $e) {
                return redirect()->route('copyeditor.submissions.show', $submissionId)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Naskah dan berkas final berhasil diserahkan ke Layout Editor. Tahap copyediting untuk naskah ini telah selesai.');
    }

    /**
     * Aksi persetujuan atau permintaan perbaikan dari Penulis atas naskah copyediting.
     */
    public function authorDecision(Request $request): RedirectResponse
    {
        $data = $request->validate(['submission_id' => ['required', 'string'], 'decision' => ['required', 'in:approved,revisions_requested'], 'note' => ['nullable', 'string', 'max:4000'], 'reason' => ['required_if:decision,revisions_requested', 'nullable', 'string', 'max:4000']]);
        $copyedit = $this->findCopyedit($data['submission_id']);
        $approval = $copyedit->approvals()->where('author_id', Auth::id())->whereNull('invalidated_at')->where('decision', 'requested')->firstOrFail();
        try {
            if ($data['decision'] === 'approved') {
                $this->copyeditorService->approveByAuthor($approval, Auth::user(), $data['note'] ?? null);
            } else {
                $this->copyeditorService->requestChangesByAuthor($approval, Auth::user(), $data['reason']);
            }
        } catch (DomainException $e) {
            return back()->withErrors(['approval' => $e->getMessage()]);
        }

        return redirect()->route('author.submissions.show', $copyedit->submission_id)->with('success', 'Tanggapan atas hasil copyediting berhasil dicatat.');
    }

    /**
     * Resolusi mediasi eskalasi oleh Section Editor / EIC.
     */
    public function resolveEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $submission = Submission::where('submission_number', $submissionId)->orWhere('id', $submissionId)->firstOrFail();
        $role = Auth::user()->hasRole('editor_in_chief') ? 'editor_in_chief' : 'section_editor';
        abort_unless(PortalAccess::visibleSubmissions(Auth::user(), $role)->whereKey($submission->id)->exists(), 403);
        $copyedit = $submission->copyedit;

        if ($copyedit) {
            $activeEscalation = $copyedit->escalations()->whereIn('status', ['open', 'investigating'])->first();
            if ($activeEscalation) {
                $resolution = (string) $request->input('resolution', 'Mediasi diselesaikan oleh Dewan Redaksi.');
                $this->copyeditorService->resolveEscalation($activeEscalation, $resolution);
            }
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Tiket mediasi eskalasi berhasil diselesaikan.');
    }

    /**
     * Mencari model Copyedit berdasarkan submission ID atau copyedit ID.
     */
    private function findCopyedit(string $identifier): ?Copyedit
    {
        $query = Copyedit::with(['submission.author', 'submission.contributors', 'copyEditor', 'outputs.file'])
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)->orWhereHas('submission', fn ($sq) => $sq->where('submission_number', $identifier)->orWhere('id', $identifier));
            });
        if (request()->routeIs('copyeditor.author-decision')) {
            $query->whereHas('submission', fn ($q) => $q->where('author_id', Auth::id())->orWhereHas('contributors', fn ($cq) => $cq->where('user_id', Auth::id())->where('is_corresponding', true)));
        } else {
            $query->where('copy_editor_id', Auth::id());
        }

        return $query->firstOrFail();
    }

    /**
     * Otorisasi ketat hak akses penugasan copyediting.
     */
    private function authorizeCopyeditorForCopyedit(?Copyedit $copyedit): void
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(401);
        }

        if (! $copyedit || (string) $copyedit->copy_editor_id !== (string) $userId) {
            abort(403, 'Anda tidak ditugaskan sebagai Copy Editor pada naskah ini.');
        }
    }
}
