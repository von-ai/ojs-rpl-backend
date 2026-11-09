<?php

declare(strict_types=1);

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StoreSubmissionRequest;
use App\Models\CopyeditApproval;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\Author\AuthorSubmissionService;
use App\Services\Author\DirectManuscriptUpload;
use App\Services\LayoutEditor\LayoutEditorService;
use App\Services\Payment\ArticlePaymentService;
use App\Services\PrivateFileService;
use App\ViewModels\AuthorSubmissionDetailViewModel;
use App\ViewModels\AuthorSubmissionsViewModel;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorSubmissionController extends Controller
{
    public function __construct(
        protected AuthorSubmissionService $authorSubmissionService,
        protected ArticlePaymentService $articlePaymentService,
        protected DirectManuscriptUpload $directManuscriptUpload,
    ) {}

    /**
     * Menampilkan formulir wizard pengajuan naskah baru atau melanjutkan draf tersimpan.
     */
    public function create(Request $request): View
    {
        $viewModel = new AuthorSubmissionsViewModel;
        $draft = null;
        $draftId = $request->query('draft_id') ?? $request->query('id');

        if ($draftId && Auth::check()) {
            $draft = Submission::with(['files.file', 'contributors', 'section'])
                ->where('author_id', Auth::id())
                ->where('status', 'draft')
                ->where(function ($q) use ($draftId) {
                    $q->where('id', $draftId)->orWhere('submission_number', $draftId);
                })
                ->first();
        }

        return view('author.submissions.create', compact('viewModel', 'draft'));
    }

    /**
     * API pencarian dan lazy-load pagination seksi jurnal.
     */
    public function sectionsApi(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:200'], 'page' => ['nullable', 'integer', 'min:1'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:30']]);
        $query = Section::where('accepting_submissions', true)->whereHas('journal', fn ($q) => $q->where('status', 'active'));
        if (! empty($data['q'])) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$data['q'].'%')->orWhere('description', 'like', '%'.$data['q'].'%'));
        }
        $page = $query->orderBy('position')->orderBy('id')->paginate($data['per_page'] ?? 8);

        return response()->json([
            'data' => collect($page->items())->map(fn ($section) => ['id' => $section->id, 'raw_id' => $section->id, 'name' => $section->name, 'description' => $section->description ?? '', 'policy' => $section->policy ?? '']),
            'current_page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total(),
        ]);
    }

    /**
     * Menghasilkan SAS PUT URL berdurasi 15 menit untuk Direct Browser Upload ke Azure Blob Storage.
     * Spesifikasi: SRS NF-01, integration-guide.md Bagian 4.
     */
    public function createUploadSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255', 'regex:/\.(pdf|docx?)$/i'],
            'byte_size' => ['required', 'integer', 'min:1', 'max:20971520'], // Max 20 MB
            'mime_type' => ['nullable', 'string', 'max:100'],
        ], [
            'filename.required' => 'Nama berkas manuskrip wajib disertakan.',
            'filename.regex' => 'Format berkas hanya diperbolehkan berekstensi .pdf, .docx, atau .doc.',
            'byte_size.max' => 'Ukuran berkas melebihi batas maksimum 20 MB.',
        ]);

        try {
            return response()->json($this->directManuscriptUpload->issue($request->user(), $validated))
                ->header('Cache-Control', 'no-store');
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sesi unggah langsung ke penyimpanan.',
            ], 503);
        }
    }

    /**
     * Menyimpan pengajuan naskah baru (sebagai draf parsial atau pengajuan resmi) ke database.
     */
    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isDraft = ($validated['action'] ?? 'submit') === 'draft';
        $author = $request->user();
        $draftId = $validated['draft_id'] ?? null;

        $directFile = null;
        $persist = function () use ($request, $validated, $isDraft, $draftId, $author, &$directFile): Submission {
            $authorId = $author->id;
            $existingDraft = null;
            if ($draftId !== null) {
                $existingDraft = Submission::whereKey($draftId)
                    ->where('author_id', $authorId)
                    ->where('status', 'draft')
                    ->lockForUpdate()
                    ->first();

                if (! $existingDraft) {
                    throw ValidationException::withMessages(['draft_id' => 'Draf tidak tersedia untuk diedit.']);
                }
            }

            if (! $isDraft && ! $request->hasFile('manuscript_file') && ! $request->filled('blob_path')
                && (! $existingDraft || $request->boolean('remove_manuscript') || ! $existingDraft->files()->where('genre', 'manuscript')->exists())) {
                throw ValidationException::withMessages(['manuscript_file' => 'Berkas manuskrip wajib diunggah.']);
            }

            $sectionId = $validated['section_id'] ?? $existingDraft?->section_id;
            $sectionQuery = Section::with('journal')
                ->where('accepting_submissions', true)
                ->whereHas('journal', fn ($query) => $query->where('status', 'active'));
            if ($sectionId !== null) {
                $sectionQuery->where(fn ($query) => $query->where('id', $sectionId)->orWhere('slug', $sectionId));
                $matchingSections = $sectionQuery->limit(2)->get();
                $section = $matchingSections->count() === 1 ? $matchingSections->first() : null;
            } else {
                $section = $sectionQuery->orderBy('position')->orderBy('id')->first();
            }

            if (! $section) {
                throw ValidationException::withMessages(['section_id' => 'Seksi tidak tersedia atau sedang ditutup untuk pengajuan.']);
            }
            $journal = $section->journal;

            // 3. Checklist & Guidelines Acceptance
            $checklistItems = $request->input('checklist_items', []);
            $hasAccepted = ! empty($validated['author_guidelines_accepted']) || count($checklistItems) >= 5;
            $guidelineData = $hasAccepted ? [
                'accepted' => true,
                'checklist' => $checklistItems,
                'timestamp' => now()->toIso8601String(),
            ] : (! empty($checklistItems) ? [
                'accepted' => false,
                'checklist' => $checklistItems,
                'timestamp' => now()->toIso8601String(),
            ] : null);

            // 4. Judul dan Abstrak (fleksibel untuk draf tanpa memblokir input parsial)
            $titleInput = trim((string) ($validated['title'] ?? ''));
            $title = $titleInput !== '' ? $titleInput : ($isDraft ? 'Draf Naskah Belum Diberi Judul' : 'Naskah Baru');

            $abstractInput = trim((string) ($validated['abstract'] ?? ''));
            $abstract = $abstractInput !== '' ? $abstractInput : ($isDraft ? 'Draf naskah belum dilengkapi abstrak.' : null);

            if ($existingDraft) {
                $existingDraft->update([
                    'journal_id' => $journal->id,
                    'section_id' => $section->id,
                    'title' => $title,
                    'abstract' => $abstract,
                    'keywords' => $validated['keywords'] ?? ($existingDraft->keywords ?? []),
                    'references' => $validated['references'] ?? $existingDraft->references,
                    'subject_area' => $validated['subject_area'] ?? $existingDraft->subject_area,
                    'comments_for_editor' => $validated['comments_for_editor'] ?? $existingDraft->comments_for_editor,
                    'status' => $isDraft ? 'draft' : 'submitted',
                    'guideline_acceptance' => $guidelineData ?? $existingDraft->guideline_acceptance,
                    'submitted_at' => $isDraft ? null : now(),
                ]);
                $submission = $existingDraft;
            } else {
                // Nomor pengajuan unik berformat SUB-YYYY-XXXXX dengan sequence atomic
                $year = (int) now()->format('Y');
                DB::table('submission_sequences')->insertOrIgnore(['year' => $year, 'last_number' => 0]);
                $sequence = DB::table('submission_sequences')->where('year', $year)->lockForUpdate()->first();
                $number = (int) $sequence->last_number + 1;
                DB::table('submission_sequences')->where('year', $year)->update(['last_number' => $number]);
                $submissionNumber = 'SUB-'.$year.'-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT);

                // Simpan record pengajuan baru (Submission)
                $submission = Submission::create([
                    'journal_id' => $journal->id,
                    'section_id' => $section->id,
                    'author_id' => $authorId,
                    'submission_number' => $submissionNumber,
                    'title' => $title,
                    'abstract' => $abstract,
                    'keywords' => $validated['keywords'] ?? [],
                    'references' => $validated['references'] ?? null,
                    'subject_area' => $validated['subject_area'] ?? null,
                    'comments_for_editor' => $validated['comments_for_editor'] ?? null,
                    'status' => $isDraft ? 'draft' : 'submitted',
                    'guideline_acceptance' => $guidelineData,
                    'submitted_at' => $isDraft ? null : now(),
                    'version' => 1,
                ]);
            }

            // 6. Simpan daftar kontributor naskah
            $contributors = $validated['contributors'] ?? [];
            if (! empty($contributors)) {
                if ($existingDraft) {
                    SubmissionContributor::where('submission_id', $submission->id)->delete();
                }

                foreach ($contributors as $index => $c) {
                    $isCorr = filter_var($c['corresponding'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $givenName = $c['given_name'] ?? ($c['name'] ?? 'Penulis');
                    $familyName = $c['family_name'] ?? null;

                    SubmissionContributor::create([
                        'submission_id' => $submission->id,
                        'user_id' => ($isCorr && $authorId) ? $authorId : null,
                        'given_name' => $givenName,
                        'family_name' => $familyName,
                        'email' => $c['email'] ?? ($author?->email ?? 'author@makassar.go.id'),
                        'affiliation' => $c['affiliation'] ?? 'BRIDA Kota Makassar',
                        'orcid' => $c['orcid_id'] ?? null,
                        'is_corresponding' => $isCorr,
                        'position' => (int) ($c['sequence'] ?? ($index + 1)),
                    ]);
                }
            }

            // 7. Simpan Berkas Manuskrip utama jika diunggah (Direct Azure SAS Upload atau Relayed Upload)
            if ($request->filled('blob_path')) {
                $directFile = $this->directManuscriptUpload->claim($author, $validated['blob_path']);
                if ($existingDraft) {
                    $submission->files()->where('genre', 'manuscript')->delete();
                }

                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_id' => $directFile->id,
                    'genre' => 'manuscript',
                    'version' => 1,
                ]);
            } elseif ($request->hasFile('manuscript_file') && $request->file('manuscript_file')->isValid()) {
                $file = $request->file('manuscript_file');
                $storageDisk = (string) config('filesecurity.disk', 'azure');
                $path = $file->store('manuscripts', $storageDisk);
                $sha256 = hash_file('sha256', $file->getRealPath());

                $fileEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'byte_size' => (int) Storage::disk($storageDisk)->size($path),
                    'checksum_sha256' => $sha256,
                    'virus_scan_status' => 'pending',
                    'created_by' => $authorId,
                ]);

                // Hapus berkas manuskrip utama sebelumnya pada naskah ini agar tidak terjadi duplikat berkas
                if ($existingDraft) {
                    SubmissionFile::where('submission_id', $submission->id)
                        ->where('genre', 'manuscript')
                        ->delete();
                }

                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_id' => $fileEntry->id,
                    'genre' => 'manuscript',
                    'version' => 1,
                ]);
            } elseif ($existingDraft && $request->boolean('remove_manuscript')) {
                SubmissionFile::where('submission_id', $submission->id)
                    ->where('genre', 'manuscript')
                    ->delete();
            }

            return $submission;
        };

        try {
            $submission = DB::transaction($persist);
        } catch (\Throwable $e) {
            if ($directFile !== null) {
                $this->directManuscriptUpload->discard($directFile->storage_path);
            }
            throw $e;
        }

        if ($isDraft) {
            return redirect()
                ->route('author.submissions.index')
                ->with('success', "Draf pengajuan naskah ({$submission->submission_number}) berhasil disimpan.");
        }

        return redirect()
            ->route('author.submissions.index')
            ->with('success', "Naskah ilmiah Anda ({$submission->submission_number}) berhasil diajukan dan saat ini berada pada tahap Pemeriksaan Awal redaksi.");
    }

    /**
     * Menampilkan halaman Naskah Saya untuk peran Author.
     */
    public function index(Request $request): View|JsonResponse
    {
        $viewModel = new AuthorSubmissionsViewModel($request->user());

        $filters = [
            'tab' => $request->query('tab', 'semua'),
            'search' => $request->query('search', ''),
            'year' => $request->query('year', 'semua'),
            'type' => $request->query('type', 'semua'),
            'sort' => $request->query('sort', '-updated_at'),
        ];

        $submissions = $viewModel->submissions($filters);

        if ($request->ajax() || $request->wantsJson()) {
            $exportParams = array_filter($filters, fn ($v): bool => $v !== '' && $v !== 'semua');

            return response()->json([
                'table_html' => view('author.submissions.partials.table', compact('viewModel', 'filters', 'submissions'))->render(),
                'export_url' => route('author.submissions.export', $exportParams),
                'tabs' => $viewModel->tabs(),
                'total' => count($submissions),
                'filters' => $filters,
                'has_filters' => ! empty($filters['search']) || (($filters['year'] ?? 'semua') !== 'semua') || (($filters['type'] ?? 'semua') !== 'semua'),
            ]);
        }

        return view('author.submissions.index', compact('viewModel', 'filters', 'submissions'));
    }

    /**
     * Mengunduh rekap riwayat naskah penulis dalam format file CSV (.csv).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $viewModel = new AuthorSubmissionsViewModel($request->user());

        $filters = [
            'tab' => $request->query('tab', 'semua'),
            'search' => $request->query('search', ''),
            'year' => $request->query('year', 'semua'),
            'type' => $request->query('type', 'semua'),
            'sort' => $request->query('sort', '-updated_at'),
        ];

        $submissions = $viewModel->submissions($filters);

        $filename = 'rekap-naskah-penulis-brida-makassar-'.date('YmdHis').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($submissions): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar terbaca sempurna di Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                'ID Naskah',
                'Judul Artikel',
                'Seksi Jurnal',
                'Tanggal Submit',
                'Status Naskah',
                'Catatan Status',
                'DOI',
                'Pembaruan Terakhir',
            ]);

            foreach ($submissions as $item) {
                fputcsv($handle, array_map(fn ($cell) => preg_match('/^[\s]*[=+@-]/u', (string) $cell) ? "'".$cell : $cell, [
                    $item['id'],
                    $item['title'],
                    $item['type'],
                    $item['submit_date'],
                    $item['status_label'],
                    $item['status_subtitle'],
                    $item['doi'],
                    $item['updated_at_raw'],
                ]));
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Menampilkan halaman detail naskah/artikel ilmiah untuk peran Author.
     */
    public function show(Request $request, string $id): View|JsonResponse
    {
        $user = $request->user();
        $detailViewModel = new AuthorSubmissionDetailViewModel($this->articlePaymentService);
        $submission = $detailViewModel->find($id, $user);

        if (! $submission) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Naskah ilmiah yang Anda cari tidak ditemukan atau Anda tidak memiliki hak akses.'], 404);
            }

            abort(404, 'Naskah ilmiah yang Anda cari tidak ditemukan atau Anda tidak memiliki hak akses.');
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $submission]);
        }

        $copyeditApproval = CopyeditApproval::with('copyedit.outputs.file')
            ->where('author_id', $request->user()->id)
            ->whereNull('invalidated_at')
            ->where('decision', 'requested')
            ->whereHas('copyedit', fn ($q) => $q->where('submission_id', $submission['id']))
            ->latest('created_at')
            ->first();
        $viewModel = new AuthorSubmissionsViewModel($user);

        return view('author.submissions.show', compact('submission', 'viewModel', 'copyeditApproval'));
    }

    /**
     * Memproses pengajuan penarikan naskah oleh penulis.
     * Naskah hanya dapat ditarik jika masih dalam tahap awal (submitted) dan belum ada tindak lanjut dari peran lain.
     */
    public function withdraw(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        $submission = $user ? $this->findAuthorSubmission($id, $user) : null;

        $detailViewModel = new AuthorSubmissionDetailViewModel($this->articlePaymentService);

        if (! $submission) {
            return redirect()
                ->route('author.submissions.index')
                ->with('error', 'Naskah ilmiah tidak ditemukan atau Anda tidak memiliki hak akses.');
        }

        $stageNumber = match ($submission->status) {
            'draft' => 1,
            'submitted' => 2,
            'in_review' => 3,
            'revisions_requested' => 4,
            'in_production', 'accepted' => 5,
            'published' => 6,
            default => 2,
        };

        if (! $detailViewModel->canWithdrawSubmission($submission, $stageNumber)) {
            $reason = $detailViewModel->cannotWithdrawReason($submission, $stageNumber);

            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('error', $reason ?? 'Naskah tidak dapat ditarik kembali karena telah memasuki tahap telaah (review) atau telah ditindaklanjuti oleh dewan redaksi/mitra bestari.');
        }

        $submission->update([
            'status' => 'withdrawn',
        ]);

        return redirect()
            ->route('author.submissions.show', $submission->id)
            ->with('success', 'Naskah ilmiah berhasil diajukan penarikannya dan status naskah telah diperbarui menjadi ditarik.');
    }

    /**
     * Memproses pengunggahan naskah revisi dan response letter oleh Penulis (UC-07).
     */
    public function storeRevision(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'manuscript_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'response_letter_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'manuscript_file.required' => 'Berkas manuskrip revisi wajib diunggah.',
            'manuscript_file.mimes' => 'Format berkas manuskrip harus berupa PDF, DOC, atau DOCX.',
            'manuscript_file.max' => 'Ukuran berkas manuskrip tidak boleh melebihi 20 MB.',
            'response_letter_file.mimes' => 'Format response letter harus berupa PDF, DOC, atau DOCX.',
            'response_letter_file.max' => 'Ukuran response letter tidak boleh melebihi 20 MB.',
        ]);

        $user = $request->user();

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->first();

        if (! $submission) {
            return redirect()
                ->route('author.submissions.index')
                ->with('error', 'Naskah ilmiah tidak ditemukan.');
        }

        try {
            $this->authorSubmissionService->submitRevision(
                submission: $submission,
                actor: $user,
                manuscriptFile: $request->file('manuscript_file'),
                responseLetterFile: $request->file('response_letter_file'),
                note: $validated['note'] ?? null
            );

            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('success', 'Naskah revisi berhasil diunggah. Status naskah kini kembali ditelaah dewan redaksi.');
        } catch (DomainException $e) {
            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('error', 'Terjadi kesalahan sistem saat memproses revisi naskah: '.$e->getMessage());
        }
    }

    /**
     * Memproses pengunduhan berkas privat naskah (UC-06).
     */
    public function downloadFile(Request $request, string $id, string $fileId): BinaryFileResponse|StreamedResponse|RedirectResponse
    {
        $user = $request->user();

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->first();

        if (! $submission) {
            return redirect()
                ->route('author.submissions.index')
                ->with('error', 'Naskah ilmiah tidak ditemukan.');
        }

        try {
            return $this->authorSubmissionService->downloadFile(
                submission: $submission,
                fileId: $fileId,
                actor: $user
            );
        } catch (DomainException $e) {
            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    public function downloadProofreadGalley(Request $request, string $id, string $galleyId): StreamedResponse
    {
        $galley = $this->findGalleyForSignoff($request, $id, $galleyId);

        return app(PrivateFileService::class)->stream($galley->file);
    }

    public function approveProofreadGalley(Request $request, string $id, string $galleyId, LayoutEditorService $layout): RedirectResponse
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:2000']]);
        $galley = $this->findGalleyForSignoff($request, $id, $galleyId);

        try {
            $layout->authorSignoff($galley, $request->user(), $validated['note'] ?? null);

            return redirect()->route('author.submissions.show', $galley->submission_id)
                ->with('success', 'Persetujuan galai Anda telah dicatat.');
        } catch (DomainException $e) {
            return redirect()->route('author.submissions.show', $galley->submission_id)
                ->with('error', $e->getMessage());
        }
    }

    private function findGalleyForSignoff(Request $request, string $id, string $galleyId): Galley
    {
        $submission = $this->findAuthorSubmission($id, $request->user());
        abort_unless($submission && $submission->author_id === $request->user()->id
            && in_array($submission->status, ['accepted', 'in_production'], true), 404);
        $galley = $submission->activeGalley;
        abort_unless($galley && $galley->id === $galleyId && $galley->readyForAuthorSignoff(), 404);

        return $galley;
    }

    /**
     * Mengunggah berkas bukti transfer pembayaran biaya publikasi artikel (APC).
     */
    public function uploadPayment(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'sender_bank_name' => ['nullable', 'string', 'max:100'],
            'sender_account_name' => ['nullable', 'string', 'max:191'],
            'transfer_date' => ['nullable', 'date'],
            'amount_paid' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'author_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_proof.required' => 'Berkas bukti pembayaran wajib diunggah.',
            'payment_proof.mimes' => 'Format berkas bukti pembayaran harus berupa PDF, JPG, JPEG, atau PNG.',
            'payment_proof.max' => 'Ukuran berkas bukti pembayaran tidak boleh melebihi 5 MB.',
        ]);

        $user = $request->user() ?? User::firstOrFail();
        $submission = $this->findAuthorSubmission($id, $user);

        if (! $submission) {
            return redirect()
                ->route('author.submissions.index')
                ->with('error', 'Naskah ilmiah tidak ditemukan atau Anda tidak memiliki hak akses.');
        }

        try {
            $this->articlePaymentService->uploadPaymentProof(
                submission: $submission,
                author: $user,
                file: $request->file('payment_proof'),
                data: $validated
            );

            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('success', 'Bukti pembayaran berhasil diunggah dan sedang dalam proses verifikasi oleh tim redaksi.');
        } catch (DomainException $e) {
            return redirect()
                ->route('author.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mengambil naskah yang dimiliki penulis atau di mana pengguna terdaftar sebagai kontributor.
     */
    private function findAuthorSubmission(string $id, User $user): ?Submission
    {
        return Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })
            ->where(function ($q) use ($user): void {
                $q->where('author_id', $user->id)
                    ->orWhereHas('contributors', function ($cq) use ($user): void {
                        $cq->where('user_id', $user->id)
                            ->orWhere('email', $user->email);
                    });
            })
            ->first();
    }

    public function showPaymentReceipt(Request $request, string $id): Response
    {
        $submission = $this->findAuthorSubmission($id, $request->user());
        $payment = $submission?->latestPayment;
        abort_unless($payment?->isVerified() && $payment->verified_at && $payment->user_id === $request->user()->id, 404);

        return response()->view('author.submissions.payment-receipt', compact('submission', 'payment'))
            ->header('Cache-Control', 'private, no-store');
    }

    /**
     * Mengunduh berkas bukti transfer pembayaran APC naskah.
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
}
