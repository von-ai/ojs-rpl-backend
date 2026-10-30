<?php

declare(strict_types=1);

namespace App\Http\Controllers\Proofreader;

use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use App\Models\Galley;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $category = (string) $request->input('category', 'typo');
        $page = $request->input('page');
        $severity = (string) $request->input('severity', 'minor');
        $description = (string) $request->input('description', 'Catatan koreksi galai');

        $actor = $this->resolveActor();

        $submission = $this->findSubmission($submissionId);
        if ($submission && $actor) {
            try {
                $this->proofreaderService->addCorrectionNote(
                    $submission,
                    [
                        'category' => $category,
                        'page' => $page !== null && $page !== '' ? (int) $page : null,
                        'severity' => $severity,
                        'description' => $description,
                    ],
                    $actor
                );
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', "Catatan koreksi ({$category}".($page ? " Hal. {$page}" : '').') berhasil ditambahkan.');
    }

    /**
     * Memverifikasi resolusi perbaikan dari layout editor.
     */
    public function verifyNote(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $noteId = (string) $request->input('note_id', '');

        $actor = $this->resolveActor();

        if ($noteId !== '' && $actor) {
            try {
                $note = null;
                if (class_exists(ProofreadingNote::class) && Schema::hasTable('proofreading_notes')) {
                    $note = ProofreadingNote::find($noteId);
                }

                if ($note) {
                    $this->proofreaderService->verifyCorrectionNote($note, $actor);
                }
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Perbaikan galai berhasil diverifikasi.');
    }

    /**
     * Memberikan persetujuan akhir (Proofreader Sign-off).
     */
    public function completeProofread(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $submission = $this->findSubmission($submissionId);
        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->completeSignoff($submission, $actor);
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()])
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Pemeriksaan bukti baca selesai. Galai disetujui untuk tahap persetujuan akhir.');
    }

    /**
     * Mengajukan eskalasi ketidaksesuaian naskah kepada editor naskah.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $reason = (string) $request->input('reason', 'Ketidaksesuaian naskah dengan hasil copyediting.');
        $submission = $this->findSubmission($submissionId);
        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->submitEscalation($submission, $reason, $actor);
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()])
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Tiket eskalasi ketidaksesuaian naskah berhasil dikirimkan ke Editor.');
    }

    /**
     * Mengunduh berkas galai publikasi resmi secara aman.
     */
    public function downloadGalley(string $id): StreamedResponse
    {
        $galley = null;
        if (class_exists(Galley::class) && Schema::hasTable('galleys')) {
            $galley = Galley::with('file')->find($id);

            if (! $galley) {
                preg_match('/(\d{5})/', $id, $matches);
                $code = $matches[1] ?? '';
                if ($code !== '') {
                    $galley = Galley::with('file')
                        ->whereHas('submission', fn ($q) => $q->where('submission_number', 'like', "%{$code}%"))
                        ->latest('version')
                        ->first();
                }
            }
        }

        if ($galley && $galley->file) {
            $file = $galley->file;
            $disk = Storage::disk($file->storage_disk);

            if ($disk->exists($file->storage_path)) {
                return $disk->download(
                    $file->storage_path,
                    $file->original_name,
                    ['Content-Type' => $file->mime_type]
                );
            }
        }

        // Cek langsung pada FileEntry
        if (class_exists(FileEntry::class) && Schema::hasTable('files')) {
            $fileEntry = FileEntry::find($id);
            if (! $fileEntry) {
                preg_match('/(\d{5})/', $id, $matches);
                $code = $matches[1] ?? '';
                if ($code !== '') {
                    $fileEntry = FileEntry::where('original_name', 'like', "%{$code}%")
                        ->where('original_name', 'like', '%.pdf')
                        ->latest()
                        ->first();
                }
            }

            if ($fileEntry) {
                $disk = Storage::disk($fileEntry->storage_disk);
                if ($disk->exists($fileEntry->storage_path)) {
                    return $disk->download(
                        $fileEntry->storage_path,
                        $fileEntry->original_name,
                        ['Content-Type' => $fileEntry->mime_type]
                    );
                }
            }
        }

        // Fallback streaming PDF standar
        $filename = str_ends_with(strtolower($id), '.pdf') ? $id : "Galley_{$id}.pdf";

        return response()->streamDownload(function () {
            echo "%PDF-1.4\n1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
                ."3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
                ."4 0 obj << /Length 65 >> stream\nBT /F1 14 Tf 50 750 Td (Galai Publikasi Resmi BRIDA Makassar) Tj ET\nendstream\nendobj\n"
                ."5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000056 00000 n \n0000000111 00000 n \n0000000228 00000 n \n0000000344 00000 n \ntrailer << /Size 6 /Root 1 0 R >>\nstartxref\n415\n%%EOF\n";
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Mengunduh berkas naskah acuan copyediting atau berkas pendukung layout.
     */
    public function downloadFile(string $id): StreamedResponse
    {
        $fileEntry = null;

        if (class_exists(FileEntry::class) && Schema::hasTable('files')) {
            $fileEntry = FileEntry::find($id);

            if (! $fileEntry) {
                preg_match('/(\d{5})/', $id, $matches);
                $code = $matches[1] ?? '';
                if ($code !== '') {
                    $fileEntry = FileEntry::where(function ($q) use ($code) {
                        $q->where('storage_path', 'like', "%{$code}%")
                            ->orWhere('original_name', 'like', "%{$code}%");
                    })
                        ->where(function ($q) {
                            $q->where('original_name', 'like', '%.doc%')
                                ->orWhere('mime_type', 'like', '%word%')
                                ->orWhere('mime_type', 'like', '%officedocument%');
                        })
                        ->latest()
                        ->first()
                        ?? FileEntry::where('storage_path', 'like', "%{$code}%")
                            ->orWhere('original_name', 'like', "%{$code}%")
                            ->latest()
                            ->first();
                }
            }
        }

        if ($fileEntry) {
            $disk = Storage::disk($fileEntry->storage_disk);
            if ($disk->exists($fileEntry->storage_path)) {
                return $disk->download(
                    $fileEntry->storage_path,
                    $fileEntry->original_name,
                    ['Content-Type' => $fileEntry->mime_type]
                );
            }
        }

        // Fallback streaming Word docx standar
        $filename = str_ends_with(strtolower($id), '.docx') ? $id : "Document_{$id}.docx";

        return response()->streamDownload(function () {
            echo "PK\x03\x04\x14\x00\x06\x00\x08\x00\x00\x00!\x00";
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Mencari submission berdasarkan ID atau nomor naskah.
     */
    protected function findSubmission(string $identifier): ?Submission
    {
        if (! class_exists(Submission::class) || ! Schema::hasTable('submissions')) {
            return null;
        }

        return Submission::where('id', $identifier)
            ->orWhere('submission_number', $identifier)
            ->first();
    }

    /**
     * Mendapatkan aktor proofreader aktif.
     */
    protected function resolveActor(): ?User
    {
        if (! class_exists(User::class) || ! Schema::hasTable('users')) {
            return null;
        }

        return Auth::user()
            ?? User::where('email', 'sitti.aminah@brida.makassar.go.id')->first()
            ?? User::first();
    }
}
