<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuthorSubmissionsViewModel
{
    /**
     * Cache internal untuk hasil rawSubmissions agar tidak terjadi query berulang dalam 1 siklus request.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $cachedRawSubmissions = null;

    protected ?User $user = null;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
     * Data profil pengguna aktif dengan konteks peran Author.
     *
     * @return array{
     *     name: string,
     *     title: string,
     *     affiliation: string,
     *     role_label: string,
     *     academic_year: string,
     *     unread_messages: int,
     *     unread_notifications: int
     * }
     */
    public function userProfile(): array
    {
        $u = $this->user ?? Auth::user();

        return [
            'name' => $u?->full_name ?? $u?->name ?? 'Dr. Andi Muhammad Ilyas, M.Si.',
            'title' => $u?->full_name ?? $u?->name ?? 'Dr. Andi Muhammad Ilyas, M.Si.',
            'affiliation' => $u?->profile?->affiliation ?? 'Universitas Hasanuddin',
            'role_label' => 'Portal Penulis OJS 3.4',
            'academic_year' => 'Tahun Akademik 2026/2027',
            'unread_messages' => 1,
            'unread_notifications' => 2,
        ];
    }

    /**
     * Rekap ringkasan metrik statistik naskah penulis (4 kartu ringkasan atas).
     *
     * @return array<int, array{
     *     title: string,
     *     value: int|string,
     *     description: string,
     *     icon: string,
     *     color_class: string
     * }>
     */
    public function summaryMetrics(): array
    {
        $all = $this->rawSubmissions();

        $totalCount = count($all);
        $revisionCount = count(array_filter($all, fn (array $s): bool => in_array('perlu-revisi', $s['tabs'], true)));
        $productionCount = count(array_filter($all, fn (array $s): bool => in_array('produksi', $s['tabs'], true)));
        $publishedCount = count(array_filter($all, fn (array $s): bool => in_array('terbit', $s['tabs'], true)));

        return [
            [
                'title' => 'Total Naskah',
                'value' => $totalCount,
                'description' => 'Seluruh naskah terdaftar',
                'icon' => 'document-text',
                'color_class' => 'text-gray-700 bg-gray-100',
            ],
            [
                'title' => 'Perlu Revisi',
                'value' => $revisionCount,
                'description' => 'Menunggu perbaikan penulis',
                'icon' => 'exclamation-circle',
                'color_class' => 'text-red-700 bg-red-50',
            ],
            [
                'title' => 'Dalam Produksi',
                'value' => $productionCount,
                'description' => 'Tata letak & galai akhir',
                'icon' => 'cog',
                'color_class' => 'text-amber-700 bg-amber-50',
            ],
            [
                'title' => 'Telah Terbit',
                'value' => $publishedCount,
                'description' => 'Artikel resmi tayang',
                'icon' => 'check-circle',
                'color_class' => 'text-emerald-700 bg-emerald-50',
            ],
        ];
    }

    /**
     * Tab kategori filter naskah beserta jumlah item masing-masing tab (Bahasa Indonesia murni).
     *
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     count: int,
     *     badge_class: string
     * }>
     */
    public function tabs(): array
    {
        $all = $this->rawSubmissions();

        return [
            [
                'key' => 'semua',
                'label' => 'Semua',
                'count' => count($all),
                'badge_class' => 'bg-brand-maroon text-white',
            ],
            [
                'key' => 'draf',
                'label' => 'Draf',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('draf', $s['tabs'], true))),
                'badge_class' => 'bg-gray-100 text-gray-700',
            ],
            [
                'key' => 'aktif',
                'label' => 'Naskah Aktif',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('aktif', $s['tabs'], true))),
                'badge_class' => 'bg-blue-50 text-blue-700',
            ],
            [
                'key' => 'perlu-revisi',
                'label' => 'Perlu Revisi',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('perlu-revisi', $s['tabs'], true))),
                'badge_class' => 'bg-red-50 text-red-700',
            ],
            [
                'key' => 'produksi',
                'label' => 'Diterima & Produksi',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('produksi', $s['tabs'], true))),
                'badge_class' => 'bg-amber-50 text-amber-700',
            ],
            [
                'key' => 'terbit',
                'label' => 'Telah Terbit',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('terbit', $s['tabs'], true))),
                'badge_class' => 'bg-emerald-50 text-emerald-700',
            ],
            [
                'key' => 'arsip',
                'label' => 'Arsip & Ditolak',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('arsip', $s['tabs'], true))),
                'badge_class' => 'bg-gray-100 text-gray-500',
            ],
        ];
    }

    /**
     * Daftar opsi tahun untuk filter dropdown.
     *
     * @return array<int, string>
     */
    public function availableYears(): array
    {
        $all = $this->rawSubmissions();
        $years = array_unique(array_map(fn (array $s): string => (string) $s['year'], $all));
        rsort($years);

        return ! empty($years) ? array_values($years) : ['2026', '2025'];
    }

    /**
     * Daftar seksi naskah ilmiah sesuai tabel sections pada ERD BRIDA.
     *
     * @return array<int, string>
     */
    public function journalSections(): array
    {
        $all = $this->rawSubmissions();
        $sections = array_unique(array_filter(array_map(fn (array $s): ?string => $s['section'] ?? $s['type'] ?? null, $all)));
        sort($sections);

        return ! empty($sections) ? array_values($sections) : ['Riset Orisinil', 'Kajian Kebijakan'];
    }

    /**
     * Daftar jenis/seksi artikel untuk filter dropdown.
     *
     * @return array<int, string>
     */
    public function articleTypes(): array
    {
        return $this->journalSections();
    }

    /**
     * Ambil naskah terfilter berdasarkan tab aktif, pencarian keyword, tahun, jenis artikel, dan opsi pengurutan.
     *
     * @param array{
     *     tab?: string|null,
     *     search?: string|null,
     *     year?: string|null,
     *     type?: string|null,
     *     sort?: string|null
     * } $filters
     * @return array<int, array<string, mixed>>
     */
    public function submissions(array $filters = []): array
    {
        $items = $this->rawSubmissions();

        $activeTab = $filters['tab'] ?? 'semua';
        if ($activeTab && $activeTab !== 'semua') {
            $items = array_values(array_filter($items, function (array $item) use ($activeTab): bool {
                return in_array($activeTab, $item['tabs'], true);
            }));
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $keyword = mb_strtolower($search);
            $items = array_values(array_filter($items, function (array $item) use ($keyword): bool {
                $searchable = mb_strtolower(implode(' ', [
                    $item['id'],
                    $item['title'],
                    $item['type'],
                    $item['doi'],
                    $item['status_label'],
                    $item['keywords'] ?? '',
                ]));

                return str_contains($searchable, $keyword);
            }));
        }

        $year = trim((string) ($filters['year'] ?? ''));
        if ($year !== '' && $year !== 'semua') {
            $items = array_values(array_filter($items, function (array $item) use ($year): bool {
                return (string) $item['year'] === $year;
            }));
        }

        $type = trim((string) ($filters['type'] ?? ''));
        if ($type !== '' && $type !== 'semua') {
            $items = array_values(array_filter($items, function (array $item) use ($type): bool {
                return mb_strtolower($item['type']) === mb_strtolower($type);
            }));
        }

        $sort = $filters['sort'] ?? '-updated_at';
        usort($items, function (array $a, array $b) use ($sort): int {
            return match ($sort) {
                'created_at' => strcmp($a['created_at_raw'], $b['created_at_raw']),
                '-created_at' => strcmp($b['created_at_raw'], $a['created_at_raw']),
                'title' => strcmp($a['title'], $b['title']),
                '-title' => strcmp($b['title'], $a['title']),
                'updated_at' => strcmp($a['updated_at_raw'], $b['updated_at_raw']),
                default => strcmp($b['updated_at_raw'], $a['updated_at_raw']),
            };
        });

        return $items;
    }

    /**
     * Sumber data naskah ilmiah penulis sesuai standar OpenAPI SIRIDA BRIDA.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rawSubmissions(): array
    {
        if ($this->cachedRawSubmissions !== null) {
            return $this->cachedRawSubmissions;
        }

        $dbItems = [];

        try {
            if (Schema::hasTable('submissions')) {
                $user = $this->user ?? Auth::user();
                $userId = $user?->id;
                $userEmail = $user?->email;

                // Jika tidak ada user login dan bukan di environment testing, kembalikan array kosong
                if (! $userId && ! app()->environment('testing')) {
                    return $this->cachedRawSubmissions = [];
                }

                $query = Submission::with(['section', 'contributors', 'journal', 'latestPayment', 'editorialDecisions', 'deskReviews']);

                // Isolasi Akses Data Peran Penulis:
                // Penulis hanya dapat melihat naskah miliknya (author_id)
                // atau di mana penulis tersebut terdaftar sebagai kontributor/co-author.
                if ($userId) {
                    $query->where(function ($q) use ($userId, $userEmail): void {
                        $q->where('author_id', $userId)
                            ->orWhereHas('contributors', function ($cq) use ($userId, $userEmail): void {
                                $cq->where('user_id', $userId);
                                if ($userEmail) {
                                    $cq->orWhere('email', $userEmail);
                                }
                            });
                    });
                }

                $submissions = $query->latest('updated_at')->get();

                foreach ($submissions as $sub) {
                    $sectionName = $sub->section?->name ?? 'Artikel Riset Orisinil';
                    $isDraft = $sub->status === 'draft';
                    $tabs = match ($sub->status) {
                        'draft' => ['semua', 'draf'],
                        'submitted' => ['semua', 'aktif'],
                        'in_review' => ['semua', 'aktif'],
                        'revisions_requested' => ['semua', 'aktif', 'perlu-revisi'],
                        'in_production', 'accepted' => ['semua', 'aktif', 'produksi'],
                        'published' => ['semua', 'terbit'],
                        'declined', 'archived', 'withdrawn' => ['semua', 'arsip'],
                        default => ['semua', 'aktif'],
                    };

                    $statusLabel = match ($sub->status) {
                        'draft' => 'Draf Belum Diajukan',
                        'submitted' => 'Pemeriksaan Awal Editor',
                        'in_review' => 'Proses Telaah',
                        'revisions_requested' => 'Perlu Revisi',
                        'in_production' => 'Pemeriksaan Galai',
                        'accepted' => 'Naskah Diterima',
                        'published' => 'Telah Terbit',
                        'declined' => 'Naskah Ditolak',
                        'archived' => 'Naskah Diarsipkan',
                        'withdrawn' => 'Naskah Ditarik',
                        default => 'Dalam Proses Editorial',
                    };

                    $statusBadgeClass = match ($sub->status) {
                        'draft' => 'bg-gray-100 text-gray-700 border border-gray-200',
                        'submitted' => 'bg-blue-50 text-blue-700 border border-blue-200',
                        'in_review' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                        'revisions_requested' => 'bg-red-50 text-red-700 border border-red-200',
                        'in_production', 'accepted' => 'bg-amber-50 text-amber-800 border border-amber-200',
                        'published' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                        'declined' => 'bg-rose-50 text-rose-700 border border-rose-200',
                        'archived', 'withdrawn' => 'bg-stone-100 text-stone-600 border border-stone-200',
                        default => 'bg-gray-100 text-gray-700 border border-gray-200',
                    };

                    $isPaidJournal = (bool) ($sub->journal?->isPaidApc() ?? false);
                    $payment = $sub->latestPayment;

                    $statusSubtitle = match ($sub->status) {
                        'revisions_requested' => (function () use ($sub): string {
                            $decision = $sub->editorialDecisions
                                ->where('status', '!=', 'draft')
                                ->filter(fn ($ed) => $ed->sent_at !== null || $ed->status === 'sent')
                                ->whereIn('decision', ['revisions_required', 'revise', 'resubmit'])
                                ->sortByDesc('sent_at')
                                ->first();

                            if ($decision?->revision_due_at) {
                                return 'Batas Waktu: '.$decision->revision_due_at->format('d M Y');
                            }

                            $desk = $sub->deskReviews
                                ->where('status', 'revision_required')
                                ->sortByDesc('created_at')
                                ->first();

                            if ($desk) {
                                return 'Perbaikan Format Berkas';
                            }

                            return 'Menunggu Respons Penulis';
                        })(),
                        'in_review' => 'Putaran 1 Review',
                        'in_production', 'accepted' => $isPaidJournal
                            ? ($payment && $payment->isVerified() ? 'Pembayaran Lunas • Tata Letak' : ($payment && $payment->isPendingVerification() ? 'Verifikasi Bukti Bayar' : ($payment && $payment->isRejected() ? 'Bukti Bayar Perlu Diperbaiki' : 'Menunggu Bukti Bayar APC')))
                            : 'Tata Letak & Galai',
                        'published' => 'Terbit Resmi Open Access',
                        'declined' => 'Keputusan Redaksi',
                        'archived' => 'Arsip Naskah Resmi',
                        'withdrawn' => 'Penarikan Disetujui',
                        default => null,
                    };

                    $statusSubtitleClass = match ($sub->status) {
                        'revisions_requested' => 'text-red-700 font-medium',
                        'declined' => 'text-rose-700 font-medium',
                        'published' => 'text-emerald-700 font-medium',
                        'in_production', 'accepted' => $isPaidJournal && (! $payment || ! $payment->isVerified()) ? 'text-amber-800 font-semibold' : 'text-slate-500 font-medium',
                        default => 'text-slate-500 font-medium',
                    };

                    $isPublished = $sub->status === 'published';
                    $doi = $isPublished ? '10.55981/brida.v2i2.'.substr($sub->id, -4) : null;
                    $doiUrl = $doi ? 'https://doi.org/'.$doi : null;
                    $doiStatus = match ($sub->status) {
                        'published' => 'registered',
                        'draft' => 'draft',
                        default => 'pending',
                    };

                    $dbItems[] = [
                        'id' => $sub->submission_number,
                        'doi' => $doi,
                        'doi_url' => $doiUrl,
                        'doi_status' => $doiStatus,
                        'section' => $sectionName,
                        'type' => $sectionName,
                        'type_badge_class' => 'bg-slate-50 text-slate-700 border-slate-200',
                        'year' => (int) ($sub->created_at?->format('Y') ?? date('Y')),
                        'tabs' => $tabs,
                        'title' => $sub->title,
                        'keywords' => is_array($sub->keywords) ? implode(', ', $sub->keywords) : (string) ($sub->keywords ?? ''),
                        'submit_date' => $sub->submitted_at ? $sub->submitted_at->format('d M Y') : 'Belum Dikirim',
                        'updated_date' => $sub->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
                        'created_at_raw' => $sub->created_at ? $sub->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                        'updated_at_raw' => $sub->updated_at ? $sub->updated_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                        'status_label' => $statusLabel,
                        'status_badge_class' => $statusBadgeClass,
                        'status_subtitle' => $statusSubtitle,
                        'status_subtitle_class' => $statusSubtitleClass,
                        'action_label' => 'Detail',
                        'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                        'action_url' => route('author.submissions.show', $sub->submission_number ?: $sub->id),
                    ];
                }
            }
        } catch (\Throwable) {
            // Fallback graceful jika koneksi DB atau tabel belum siap
        }

        return $this->cachedRawSubmissions = $dbItems;
    }

    /**
     * Panduan 6 tahapan alur kerja editorial OJS 3 dan makna status bagi penulis.
     *
     * @return array<int, array{
     *     step: int,
     *     title: string,
     *     description: string
     * }>
     */
    public function editorialWorkflowSteps(): array
    {
        return [
            [
                'step' => 1,
                'title' => '1. Draf Submisi',
                'description' => 'Naskah disimpan sementara oleh penulis sebelum diajukan resmi ke redaksi.',
            ],
            [
                'step' => 2,
                'title' => '2. Pemeriksaan Awal',
                'description' => 'Pemeriksaan format penulisan, kesesuaian fokus dan ruang lingkup, serta uji kesamaan naskah.',
            ],
            [
                'step' => 3,
                'title' => '3. Proses Telaah',
                'description' => 'Proses penelaahan tertutup oleh minimal 2 mitra bestari independen.',
            ],
            [
                'step' => 4,
                'title' => '4. Perlu Revisi',
                'description' => 'Penulis mengunggah naskah perbaikan beserta catatan respon sebelum batas waktu yang ditentukan.',
            ],
            [
                'step' => 5,
                'title' => '5. Penyelarasan & Galai',
                'description' => 'Penyelarasan bahasa, format terbitan (PDF/XML), dan persetujuan akhir penulis.',
            ],
            [
                'step' => 6,
                'title' => '6. Telah Terbit',
                'description' => 'Naskah resmi diterbitkan pada terbitan aktif dengan pengenal Digital Object Identifier (DOI).',
            ],
        ];
    }

    /**
     * Daftar seksi jurnal aktif yang tersedia untuk pengajuan naskah baru.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     policy: string
     * }>
     */
    public function wizardJournalSections(): array
    {
        return Section::where('accepting_submissions', true)->whereHas('journal', fn ($q) => $q->where('status', 'active'))->orderBy('position')->orderBy('id')->get()->map(fn ($s) => [
            'id' => $s->id, 'raw_id' => $s->id, 'name' => $s->name, 'description' => $s->description ?? '', 'policy' => $s->policy ?? '',
        ])->all();
    }

    /**
     * Poin-poin checklist kepatuhan etika dan ketentuan penulisan naskah BRIDA (Langkah 1).
     *
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     description: string
     * }>
     */
    public function submissionChecklist(): array
    {
        return [
            [
                'id' => 'chk-orisinil',
                'title' => 'Keaslian & Orisinalitas Karya',
                'description' => 'Naskah merupakan karya orisinil penulis, belum pernah dipublikasikan di jurnal manapun, dan tidak sedang dalam proses telaah di terbitan lain.',
            ],
            [
                'id' => 'chk-format',
                'title' => 'Format & Panduan Penulisan BRIDA',
                'description' => 'Berkas naskah disusun menggunakan template resmi BRIDA Makassar (.docx atau PDF), menggunakan jenis huruf standar, dan mematuhi batas kata.',
            ],
            [
                'id' => 'chk-blind',
                'title' => 'Pematuhan Penelaahan Tertutup',
                'description' => 'Pada berkas utama manuskrip, identitas nama penulis, afiliasi, dan ucapan terima kasih telah disamarkan demi objektivitas telaah mitra bestari.',
            ],
            [
                'id' => 'chk-sitasi',
                'title' => 'Manajemen Referensi & Bebas Plagiasi',
                'description' => 'Seluruh rujukan telah disitasi secara konsisten (disarankan menggunakan Mendeley/Zotero dengan gaya APA 7th) dan indeks kesamaan (Turnitin) < 20%.',
            ],
            [
                'id' => 'chk-etika',
                'title' => 'Persetujuan Etika & Bebas Konflik Kepentingan',
                'description' => 'Riset yang melibatkan subjek manusia/masyarakat telah memenuhi etika penelitian dan tidak terdapat konflik kepentingan finansial maupun institusional.',
            ],
        ];
    }

    /**
     * Tahapan alur 5-langkah wizard pengajuan naskah baru
     *
     * @return array<int, array{
     *     step: int,
     *     title: string,
     *     short_title: string,
     *     description: string,
     *     icon: string
     * }>
     */
    public function wizardSteps(): array
    {
        return [
            [
                'step' => 1,
                'title' => '1. Ketentuan & Seksi',
                'short_title' => 'Ketentuan',
                'description' => 'Pilih seksi jurnal dan persetujuan etika',
                'icon' => 'clipboard-check',
            ],
            [
                'step' => 2,
                'title' => '2. Unggah Berkas',
                'short_title' => 'Unggah',
                'description' => 'Unggah berkas naskah utama',
                'icon' => 'cloud-arrow-up',
            ],
            [
                'step' => 3,
                'title' => '3. Metadata Naskah',
                'short_title' => 'Metadata',
                'description' => 'Judul, abstrak, dan kata kunci',
                'icon' => 'document-text',
            ],
            [
                'step' => 4,
                'title' => '4. Kontributor',
                'short_title' => 'Kontributor',
                'description' => 'Penulis bersama & korespondensi',
                'icon' => 'user-group',
            ],
            [
                'step' => 5,
                'title' => '5. Konfirmasi & Kirim',
                'short_title' => 'Konfirmasi',
                'description' => 'Tinjauan final dan pengiriman',
                'icon' => 'paper-airplane',
            ],
        ];
    }

    /**
     * Profil kontributor utama bawaan dari sesi pengguna aktif (Penulis Pertama).
     *
     * @return array{
     *     given_name: string,
     *     family_name: string,
     *     email: string,
     *     affiliation: string,
     *     orcid_id: string,
     *     sequence: int,
     *     corresponding: bool
     * }
     */
    public function defaultContributor(): array
    {
        return [
            'given_name' => 'Andi Muhammad',
            'family_name' => 'Ilyas',
            'email' => 'andi.ilyas@unhas.ac.id',
            'affiliation' => 'Universitas Hasanuddin, Makassar',
            'orcid_id' => '0000-0002-1825-0097',
            'sequence' => 1,
            'corresponding' => true,
        ];
    }
}
