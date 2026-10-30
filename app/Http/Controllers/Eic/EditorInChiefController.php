<?php

declare(strict_types=1);

namespace App\Http\Controllers\Eic;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\User;
use App\Services\Eic\EditorInChiefService;
use App\ViewModels\EicSubmissionDetailViewModel;
use App\ViewModels\EicSubmissionsViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditorInChiefController extends Controller
{
    public function __construct(
        protected EditorInChiefService $eicService
    ) {}

    /**
     * Menampilkan antrean naskah editorial Editor-in-Chief.
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->input('tab', 'all');
        $search = $request->input('q');

        $submissions = $this->eicService->getSubmissionsQueue($tab, $search, 10);
        $viewModel = new EicSubmissionsViewModel(
            activeTab: $tab,
            searchQuery: (string) ($search ?? ''),
            submissions: $submissions
        );

        return view('eic.submissions.index', compact('viewModel', 'submissions'));
    }

    /**
     * Menampilkan ruang kerja telaah naskah bagi Editor-in-Chief.
     */
    public function show(Request $request, string $id): View
    {
        $submission = Submission::with([
            'section',
            'journal',
            'author',
            'contributors',
            'files.file',
            'deskReviews.assessor',
            'activeEditorAssignment.editor',
            'editorialDecisions.decider',
        ])
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $viewModel = new EicSubmissionDetailViewModel($submission);

        return view('eic.submissions.show', compact('submission', 'viewModel'));
    }

    /**
     * Memproses evaluasi awal naskah (Desk Review / UC-10).
     */
    public function storeDeskReview(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();

        $validated = $request->validate([
            'scope_fit' => ['required', 'boolean'],
            'files_complete' => ['required', 'boolean'],
            'guidelines_compliant' => ['required', 'boolean'],
            'similarity_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:passed,rejected,revision_required'],
            'section_editor_id' => ['nullable', 'exists:users,id'],
        ], [
            'status.required' => 'Hasil evaluasi desk review wajib dipilih.',
            'status.in' => 'Pilihan hasil evaluasi desk review tidak valid.',
        ]);

        try {
            $user = $request->user() ?? User::firstOrFail();

            $this->eicService->performDeskReview(
                submission: $submission,
                actor: $user,
                data: $validated
            );

            $statusText = match ($validated['status']) {
                'passed' => 'Lolos Skrining Awal (Diteruskan ke Penelaahan)',
                'rejected' => 'Ditolak Awal (Desk Reject)',
                'revision_required' => 'Diminta Melengkapi/Merevisi Format Berkas',
                default => 'Diproses',
            };

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', "Evaluasi Desk Review berhasil disimpan: {$statusText}.");
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menugaskan Section Editor untuk memimpin proses penelaahan (UC-11).
     */
    public function assignEditor(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();

        $validated = $request->validate([
            'section_editor_id' => ['required', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'section_editor_id.required' => 'Pilih Section Editor yang akan ditugaskan.',
            'section_editor_id.exists' => 'Section Editor yang dipilih tidak terdaftar di sistem.',
        ]);

        try {
            $eic = $request->user() ?? User::firstOrFail();
            $sectionEditor = User::findOrFail($validated['section_editor_id']);

            $this->eicService->assignSectionEditor(
                submission: $submission,
                eic: $eic,
                sectionEditor: $sectionEditor,
                note: $validated['note'] ?? null
            );

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', "Section Editor {$sectionEditor->name} berhasil ditugaskan untuk menangani naskah ini.");
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menerbitkan keputusan editorial resmi atas naskah (UC-12).
     */
    public function storeDecision(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();

        $validated = $request->validate([
            'decision' => ['required', 'in:accept,revisions_required,resubmit,decline'],
            'note_for_author' => ['required', 'string', 'max:5000'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'revision_due_at' => [
                'nullable',
                'required_if:decision,revisions_required,resubmit',
                'date',
            ],
        ], [
            'decision.required' => 'Jenis keputusan editorial wajib dipilih.',
            'note_for_author.required' => 'Surat pengantar / catatan resmi untuk penulis wajib diisi.',
            'revision_due_at.required_if' => 'Batas waktu revisi (due date) wajib ditentukan bila meminta perbaikan naskah.',
        ]);

        try {
            $eic = $request->user() ?? User::firstOrFail();

            $decision = $this->eicService->makeEditorialDecision(
                submission: $submission,
                eic: $eic,
                data: $validated
            );

            $label = match ($decision->decision) {
                'accept' => 'Diterima untuk Publikasi (Tahap Produksi)',
                'revisions_required', 'resubmit' => 'Diminta Revisi Penulis',
                'decline' => 'Naskah Ditolak (Declined)',
                default => 'Keputusan Tercatat',
            };

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', "Keputusan editorial resmi berhasil diterbitkan: {$label}.");
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }
}
