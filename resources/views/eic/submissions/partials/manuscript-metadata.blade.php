{{-- resources/views/eic/submissions/partials/manuscript-metadata.blade.php --}}
<!-- Kartu Metadata Naskah, Abstrak & Kontributor -->
<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-6">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3.5">
        <div 
            class="flex items-center gap-2.5 min-w-0 cursor-help"
            data-tooltip="Abstrak & Metadata Naskah"
            data-tooltip-desc="Deskripsi substansi riset, tim penulis, dan parameter taksonomi ilmiah"
        >
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
                Abstrak & Metadata Naskah
            </h2>
        </div>
        @if($submission->subject_area)
            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200 truncate max-w-xs">
                {{ $submission->subject_area }}
            </span>
        @endif
    </div>

    <!-- Daftar Penulis & Kontributor -->
    <div class="space-y-2">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tim Penulis & Afiliasi Institusi</h3>
        @if($submission->contributors->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach($submission->contributors as $contributor)
                    <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/70 flex items-start justify-between gap-2 text-xs">
                        <div class="min-w-0 space-y-0.5">
                            <div class="font-bold text-gray-900 flex items-center gap-1.5 flex-wrap">
                                <span>{{ trim($contributor->given_name . ' ' . $contributor->family_name) }}</span>
                                @if($contributor->is_corresponding)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-maroon/10 text-brand-maroon">
                                        Korespondensi
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-600 break-words">{{ $contributor->affiliation }}</div>
                            <div class="text-[11px] text-gray-500 truncate">{{ $contributor->email }}</div>
                            @if($contributor->orcid)
                                <div class="text-[11px] text-emerald-700 tabular-nums">
                                    ORCID: {{ $contributor->orcid }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/70 text-xs space-y-0.5">
                <div class="font-bold text-gray-900">{{ $submission->author?->name ?? 'Penulis Naskah' }}</div>
                <div class="text-gray-500">{{ $submission->author?->email ?? '-' }}</div>
            </div>
        @endif
    </div>

    <!-- Abstrak Ilmiah (Rata Kiri Sesuai Standar Industri) -->
    <div class="space-y-2">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Abstrak</h3>
        <div class="p-4 sm:p-5 rounded-lg bg-gray-50 border border-gray-200">
            <p class="font-sans text-xs sm:text-sm text-gray-700 leading-relaxed text-left break-words [overflow-wrap:anywhere]">
                {{ $submission->abstract ?? 'Tidak ada abstrak yang disertakan.' }}
            </p>
        </div>
    </div>

    <!-- Kata Kunci -->
    @if(!empty($submission->keywords))
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kata Kunci</h3>
            <div class="flex flex-wrap gap-2">
                @foreach((is_array($submission->keywords) ? $submission->keywords : explode(',', (string) $submission->keywords)) as $kw)
                    <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-200">
                        {{ trim((string) $kw) }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Daftar Pustaka Utama & Catatan Pengantar Penulis (Tersusun Vertikal / Berbeda Baris) -->
    @if($submission->references || $submission->comments_for_editor)
        <div class="space-y-4 pt-3 border-t border-gray-100 text-xs">
            @if($submission->references)
                <div class="space-y-2">
                    <h3 class="font-bold text-gray-500 uppercase tracking-wider text-xs">Daftar Pustaka Utama</h3>
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 text-xs leading-relaxed text-left whitespace-pre-line max-h-56 overflow-y-auto">
                        {{ preg_replace('/^\s*\d+\.\s*/m', '', (string) $submission->references) }}
                    </div>
                </div>
            @endif

            @if($submission->comments_for_editor)
                <div class="space-y-2">
                    <h3 class="font-bold text-gray-500 uppercase tracking-wider text-xs">Catatan Pengantar untuk Redaksi</h3>
                    <div class="p-4 rounded-lg bg-amber-50/60 border border-amber-200/80 text-gray-700 leading-relaxed text-left break-words">
                        {!! nl2br(e(str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $submission->comments_for_editor))) !!}
                    </div>
                </div>
            @endif
        </div>
    @endif
</section>

<!-- Kartu Berkas Manuskrip, Lampiran & Riwayat Revisi Penulis -->
<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-5">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3.5">
        <div 
            class="flex items-center gap-2.5 min-w-0 cursor-help"
            data-tooltip="Berkas Manuskrip & Dokumen Pendukung"
            data-tooltip-desc="Daftar berkas naskah ilmiah dan lampiran yang diunggah oleh penulis"
        >
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
                Berkas Manuskrip & Dokumen Pendukung
            </h2>
        </div>
        <span class="text-xs text-gray-500 font-semibold tabular-nums shrink-0">{{ $submission->files->count() }} Berkas</span>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($submission->files as $subFile)
            @php
                $f = $subFile->file;
                $sizeKb = $f?->byte_size ? number_format($f->byte_size / 1024, 1) . ' KB' : '-';
                $scanStatus = $f?->virus_scan_status ?? 'clean';
                $scanBadge = match($scanStatus) {
                    'clean' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'pending', 'pending_scan' => 'bg-amber-50 text-amber-700 border-amber-200',
                    default => 'bg-rose-50 text-rose-700 border-rose-200',
                };
                $scanLabel = match($scanStatus) {
                    'clean' => 'Terverifikasi Aman',
                    'pending', 'pending_scan' => 'Menunggu Pemindaian',
                    default => 'Dikarantina',
                };
                $genreLabel = match($subFile->genre) {
                    'manuscript' => 'Naskah Utama',
                    'anonymized_manuscript' => 'Naskah Anonim',
                    'supplement' => 'Lampiran Pendukung',
                    'revision' => 'Naskah Revisi',
                    'response_letter' => 'Surat Tanggapan',
                    default => 'Dokumen Naskah',
                };
            @endphp
            <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-9 h-9 rounded-lg bg-red-50 text-brand-maroon flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block font-bold text-gray-900 truncate" title="{{ $f?->original_name ?? 'Berkas Naskah' }}">
                            {{ $f?->original_name ?? 'Berkas Naskah' }}
                        </span>
                        <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-[11px] text-gray-500 mt-0.5 tabular-nums">
                            <span>Jenis: <strong class="text-gray-700">{{ $genreLabel }}</strong></span>
                            <span class="text-gray-300">/</span>
                            <span>Versi {{ $subFile->version }}</span>
                            <span class="text-gray-300">/</span>
                            <span>{{ $sizeKb }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold border {{ $scanBadge }}">
                        {{ $scanLabel }}
                    </span>
                    <a 
                        href="{{ route('eic.submissions.files.download', ['id' => $submission->id, 'fileId' => $subFile->id]) }}" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold transition shrink-0"
                        title="Unduh berkas manuskrip"
                    >
                        <svg class="w-3.5 h-3.5 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh</span>
                    </a>
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-400 py-2">Belum ada berkas manuskrip yang diunggah.</p>
        @endforelse
    </div>

    <!-- Riwayat Revisi Penulis jika tersedia -->
    @if($submission->revisions->isNotEmpty())
        <div class="pt-4 border-t border-gray-100 space-y-2.5">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Riwayat Pengumpulan Revisi Penulis</h3>
            <div class="space-y-2">
                @foreach($submission->revisions as $rev)
                    @php
                        $revStatusLabel = match($rev->status) {
                            'submitted' => 'Telah Diunggah',
                            'pending' => 'Menunggu Unggahan',
                            default => 'Dalam Proses',
                        };
                    @endphp
                    <div class="p-3.5 rounded-lg border border-orange-200 bg-orange-50/50 text-xs space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-orange-950">Revisi Putaran #{{ $rev->round }}</span>
                            <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-orange-100 text-orange-900 tabular-nums">
                                {{ $revStatusLabel }} — {{ $rev->submitted_at ? $rev->submitted_at->format('d M Y, H:i') : 'Menunggu Unggahan' }}
                            </span>
                        </div>
                        @if($rev->note)
                            <p class="text-gray-700 text-left">Surat Tanggapan Penulis: "{{ $rev->note }}"</p>
                        @endif
                        @if($rev->due_at)
                            <div class="text-[11px] text-orange-800 font-medium tabular-nums">
                                Tenggat Waktu Revisi: {{ $rev->due_at->format('d F Y') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
