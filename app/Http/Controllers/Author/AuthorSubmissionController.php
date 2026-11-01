<?php

declare(strict_types=1);

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StoreSubmissionRequest;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\Author\AuthorSubmissionService;
use App\Services\Payment\ArticlePaymentService;
use App\ViewModels\AuthorSubmissionDetailViewModel;
use App\ViewModels\AuthorSubmissionsViewModel;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorSubmissionController extends Controller
{
    public function __construct(
        protected AuthorSubmissionService $authorSubmissionService,
        protected ArticlePaymentService $articlePaymentService
    ) {}

    /**
     * Menampilkan formulir wizard pengajuan naskah baru.
     */
    public function create(): View
    {
        $viewModel = new AuthorSubmissionsViewModel;

        return view('author.submissions.create', compact('viewModel'));
    }

    /**
     * API pencarian dan lazy-load pagination seksi jurnal.
     */
    public function sectionsApi(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        $perPage = min(max((int) $request->input('per_page', 8), 1), 30);
        $page = max((int) $request->input('page', 1), 1);

        try {
            if (Schema::hasTable('sections')) {
                $builder = Section::where('accepting_submissions', true);

                if ($query !== '') {
                    $builder->where(function ($sub) use ($query): void {
                        $sub->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    });
                }

                $paginator = $builder->orderBy('position', 'asc')
                    ->orderBy('name', 'asc')
                    ->paginate($perPage, ['id', 'slug', 'name', 'description', 'policy'], 'page', $page);

                return response()->json([
                    'data' => collect($paginator->items())->map(fn (Section $s): array => [
                        'id' => $s->slug ?: $s->id,
                        'raw_id' => $s->id,
                        'name' => $s->name,
                        'description' => $s->description ?? '',
                        'policy' => $s->policy ?? '',
                    ]),
                    'current_page' => $paginator->currentPage(),
                    'has_more' => $paginator->hasMorePages(),
                    'total' => $paginator->total(),
                ]);
            }
        } catch (\Throwable) {
            // Fallback graceful jika terjadi kendala DB
        }

        $viewModel = new AuthorSubmissionsViewModel(request: $request);
        $allSections = collect($viewModel->wizardJournalSections());

        if ($query !== '') {
            $qLower = strtolower($query);
            $allSections = $allSections->filter(function (array $item) use ($qLower): bool {
                return str_contains(strtolower($item['name']), $qLower)
                    || str_contains(strtolower($item['description'] ?? ''), $qLower);
            })->values();
        }

        $total = $allSections->count();
        $slice = $allSections->slice(($page - 1) * $perPage, $perPage)->values();
        $hasMore = ($page * $perPage) < $total;

        return response()->json([
            'data' => $slice,
            'current_page' => $page,
            'has_more' => $hasMore,
            'total' => $total,
        ]);
    }

    /**
     * Menyimpan pengajuan naskah baru (sebagai draf parsial atau pengajuan resmi) ke database.
     */
    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isDraft = ($validated['action'] ?? 'submit') === 'draft';

        $submission = DB::transaction(function () use ($request, $validated, $isDraft): Submission {
            // 1. Dapatkan atau inisialisasi Journal default
            $journal = Journal::first() ?? Journal::create([
                'slug' => 'sirida-makassar',
                'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'issn' => '2985-7821',
                'eissn' => '2985-7821',
                'status' => 'active',
                'version' => 1,
            ]);

            // 2. Dapatkan Section berdasarkan id ULID atau slug
            $section = Section::where('id', $validated['section_id'])
                ->orWhere('slug', $validated['section_id'])
                ->first();

            if (! $section) {
                $section = Section::firstOrCreate(
                    [
                        'journal_id' => $journal->id,
                        'slug' => 'sec-riset-orisinil',
                    ],
                    [
                        'name' => 'Artikel Riset Orisinil',
                        'description' => 'Laporan hasil penelitian empiris atau teoritis primer.',
                        'policy' => 'Wajib memuat metodologi riset yang jelas.',
                        'accepting_submissions' => true,
                        'position' => 1,
                        'version' => 1,
                    ]
                );
            }

            // 3. Dapatkan author aktif atau default user
            $author = $request->user() ?? User::first();
            $authorId = $author?->id;

            // 4. Nomor pengajuan unik berformat SUB-YYYY-XXXXX
            $year = date('Y');
            $count = Submission::whereYear('created_at', $year)->count() + 1;
            $submissionNumber = 'SUB-'.$year.'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);

            // 5. Simpan record pengajuan (Submission)
            $submission = Submission::create([
                'journal_id' => $journal->id,
                'section_id' => $section->id,
                'author_id' => $authorId,
                'submission_number' => $submissionNumber,
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],
                'keywords' => $validated['keywords'] ?? [],
                'references' => $validated['references'] ?? null,
                'subject_area' => $validated['subject_area'] ?? null,
                'comments_for_editor' => $validated['comments_for_editor'] ?? null,
                'status' => $isDraft ? 'draft' : 'submitted',
                'guideline_acceptance' => ! empty($validated['author_guidelines_accepted'])
                    ? ['accepted' => true, 'timestamp' => now()->toIso8601String()]
                    : null,
                'submitted_at' => $isDraft ? null : now(),
                'version' => 1,
            ]);

            // 6. Simpan daftar kontributor naskah
            $contributors = $validated['contributors'] ?? [];
            if (! empty($contributors)) {
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

            // 7. Simpan Berkas Manuskrip utama jika diunggah
            if ($request->hasFile('manuscript_file') && $request->file('manuscript_file')->isValid()) {
                $file = $request->file('manuscript_file');
                $storageDisk = config('filesystems.default', 'local');
                $path = $file->store('manuscripts', $storageDisk);
                $sha256 = hash_file('sha256', $file->getRealPath());

                $fileEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                    'byte_size' => $file->getSize(),
                    'checksum_sha256' => $sha256,
                    'virus_scan_status' => 'clean',
                    'created_by' => $authorId,
                ]);

                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_id' => $fileEntry->id,
                    'genre' => 'manuscript',
                    'version' => 1,
                ]);
            }

            return $submission;
        });

        if ($isDraft) {
            $message = "Draf pengajuan naskah berhasil disimpan (ID: {$submission->submission_number}). Anda dapat melanjutkan pengisian kapan saja.";
        } else {
            $message = "Naskah ilmiah Anda (ID: {$submission->submission_number}) berhasil diajukan dan saat ini berada pada tahap Pemeriksaan Awal redaksi.";
        }

        return redirect()
            ->route('author.submissions.index')
            ->with('success', $message);
    }

    /**
     * Menampilkan halaman Naskah Saya untuk peran Author.
     */
    public function index(Request $request): View|JsonResponse
    {
        $viewModel = new AuthorSubmissionsViewModel;

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
        $viewModel = new AuthorSubmissionsViewModel;

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
                fputcsv($handle, [
                    $item['id'],
                    $item['title'],
                    $item['type'],
                    $item['submit_date'],
                    $item['status_label'],
                    $item['status_subtitle'],
                    $item['doi'],
                    $item['updated_at_raw'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Menampilkan halaman detail naskah/artikel ilmiah untuk peran Author.
     */
    public function show(Request $request, string $id): View
    {
        $detailViewModel = new AuthorSubmissionDetailViewModel;
        $submission = $detailViewModel->find($id, $request->user());

        if (! $submission) {
            abort(404, 'Naskah ilmiah yang Anda cari tidak ditemukan atau Anda tidak memiliki hak akses.');
        }

        $viewModel = new AuthorSubmissionsViewModel;

        return view('author.submissions.show', compact('submission', 'viewModel'));
    }

    /**
     * Memproses pengajuan penarikan naskah oleh penulis.
     * Naskah hanya dapat ditarik jika masih dalam tahap awal (submitted) dan belum ada tindak lanjut dari peran lain.
     */
    public function withdraw(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })
            ->when($user, fn ($q) => $q->where('author_id', $user->id))
            ->first();

        $detailViewModel = new AuthorSubmissionDetailViewModel;

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
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'author_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_proof.required' => 'Berkas bukti pembayaran wajib diunggah.',
            'payment_proof.mimes' => 'Format berkas bukti pembayaran harus berupa PDF, JPG, JPEG, atau PNG.',
            'payment_proof.max' => 'Ukuran berkas bukti pembayaran tidak boleh melebihi 5 MB.',
        ]);

        $user = $request->user() ?? User::firstOrFail();

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

        $fileEntry = $payment->fileEntry;
        $disk = Storage::disk($fileEntry->storage_disk);

        if (! $disk->exists($fileEntry->storage_path)) {
            abort(404, 'Berkas fisik bukti transfer tidak ditemukan di penyimpanan server.');
        }

        return $disk->download(
            $fileEntry->storage_path,
            $fileEntry->original_name,
            ['Content-Type' => $fileEntry->mime_type]
        );
    }
}
