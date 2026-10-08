@props(['tasks' => []])

<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
            <span>Perlu Tindakan Penulis (Action Required)</span>
        </h2>
        <span class="text-xs font-bold text-red-800 bg-red-100 border border-red-200 px-2.5 py-0.5 rounded-full">
            {{ count($tasks) }} Tugas Mendesak
        </span>
    </div>

    <!-- Urgent Action Task Cards List -->
    <div class="space-y-4">
        @forelse ($tasks as $task)
            @if ($task['type'] === 'revision')
                <!-- Task 1: Minor Revision -->
                <div class="bg-white rounded-xl border-2 border-red-300 p-5 sm:p-6 shadow-sm space-y-4">
                    <!-- Top Meta Info -->
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs border-b border-gray-100 pb-3">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-brand-maroon text-white uppercase tracking-wider">
                                {{ $task['type_badge'] }}
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="font-bold text-red-700 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Batas Waktu: {{ $task['deadline'] }} ({{ $task['days_left'] }})
                            </span>
                        </div>
                        <span class="font-mono text-xs font-bold text-gray-500">ID: {{ $task['id'] }}</span>
                    </div>

                    <!-- Title & Categories -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="space-y-1 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-snug hover:text-brand-maroon transition-colors">
                                <a href="#">{{ $task['title'] }}</a>
                            </h3>
                        </div>
                        <div class="text-right text-xs space-y-0.5 shrink-0">
                            <span class="text-gray-500 block">Kategori Publikasi:</span>
                            <strong class="text-gray-800 block font-bold">{{ $task['category'] }}</strong>
                            <span class="text-[11px] text-gray-400 block">Riwayat Versi: {{ $task['round'] }}</span>
                        </div>
                    </div>

                    <!-- Editor Feedback Box -->
                    @if (!empty($task['editor_note']))
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-xs space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-gray-800">
                                <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                <span>Catatan Dewan Editor ({{ $task['editor_note']['author'] }}):</span>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                {{ $task['editor_note']['text'] }}
                            </p>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2.5 pt-2">
                        <a href="{{ $task['actions']['submit_revision_url'] ?? '#' }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg shadow-2xs transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Unggah Revisi & Tanggapan Reviewer</span>
                        </a>

                        <a href="{{ $task['actions']['view_reviews_url'] ?? '#' }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Lihat Catatan Mitra Bestari (Reviewer 1 & 2)</span>
                        </a>

                        <a href="{{ $task['actions']['message_editor_url'] ?? '#' }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-brand-maroon hover:underline">
                            <span>Kirim Pesan ke Editor Bagian</span>
                        </a>
                    </div>
                </div>
            @elseif ($task['type'] === 'proofreading')
                <!-- Task 2: Proofreading Galley Check -->
                <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-white uppercase tracking-wider">
                                {{ $task['type_badge'] }}
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="font-bold text-gray-700">Batas Waktu: {{ $task['deadline'] }}</span>
                        </div>
                        <span class="font-mono text-xs font-bold text-gray-500">ID: {{ $task['id'] }}</span>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-snug hover:text-brand-maroon transition-colors">
                                <a href="#">{{ $task['title'] }}</a>
                            </h3>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                {{ $task['editor_note']['text'] ?? 'Tata letak PDF galai akhir telah siap. Mohon periksa kesalahan pengetikan nama penulis, afiliasi, penomoran tabel/grafik, serta pengesahan akhir sebelum penerbitan volume digital.' }}
                            </p>
                        </div>

                        <a href="{{ $task['actions']['view_galley_url'] ?? '#' }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-2xs transition-all shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Tinjau Bukti Cetak (Galley PDF)</span>
                        </a>
                    </div>
                </div>
            @endif
        @empty
            <div class="p-6 bg-white rounded-xl border border-gray-200 text-center text-xs text-gray-500">
                Semua tugas telah diselesaikan. Tidak ada tindakan yang diperlukan saat ini.
            </div>
        @endforelse
    </div>
</div>
