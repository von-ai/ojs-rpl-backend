<div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 pb-4">
        <div>
            <h2 class="text-base font-bold text-gray-900 tracking-tight">Catatan Koreksi Bukti Baca</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Daftar perbaikan tipografi, tata letak, dan sitasi yang perlu ditindaklanjuti oleh Layout Editor.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                {{ $submission['summary']['unresolved_notes'] }} Terbuka
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ $submission['summary']['resolved_notes'] }} Selesai
            </span>
        </div>
    </div>

    <!-- Formulir Cepat Tambah Catatan Koreksi (Fast Inline Logging) -->
    @if(!$submission['is_completed'])
        <div class="bg-gray-50/70 border border-gray-200 rounded-xl p-4 sm:p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Tambah Catatan Koreksi Galai</span>
            </h3>

            <form action="{{ route('proofreader.add-note') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Kategori Kesalahan -->
                    <div>
                        <label for="category" class="block text-xs font-semibold text-gray-700 mb-1">
                            Kategori Kesalahan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="category" 
                            name="category" 
                            required
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                        >
                            <option value="typo">Tipografi / Ejaan (Typo)</option>
                            <option value="layout">Tata Letak / Format (Layout)</option>
                            <option value="metadata">Metadata / Penulis / Judul</option>
                            <option value="reference">Sitasi &amp; Daftar Pustaka</option>
                        </select>
                    </div>

                    <!-- Nomor Halaman -->
                    <div>
                        <label for="page" class="block text-xs font-semibold text-gray-700 mb-1">
                            Halaman Galai (Opsional)
                        </label>
                        <input 
                            type="number" 
                            id="page" 
                            name="page" 
                            min="1" 
                            max="100" 
                            placeholder="Contoh: 4"
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                        >
                    </div>

                    <!-- Tingkat Keparahan (Severity) -->
                    <div>
                        <label for="severity" class="block text-xs font-semibold text-gray-700 mb-1">
                            Tingkat Urgensi <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="severity" 
                            name="severity" 
                            required
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                        >
                            <option value="minor">Minor (Perbaikan Ringan)</option>
                            <option value="major">Major (Perlu Penyesuaian Kolom/Gambar)</option>
                            <option value="critical">Kritis (Teks Hilang/Sitasi Fatal)</option>
                        </select>
                    </div>
                </div>

                <!-- Deskripsi Koreksi -->
                <div>
                    <label for="description" class="block text-xs font-semibold text-gray-700 mb-1">
                        Instruksi Koreksi Spesifik <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="2" 
                        required
                        placeholder="Jelaskan kata/kalimat/tabel yang perlu diperbaiki oleh Layout Editor..."
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    ></textarea>
                </div>

                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-11 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition duration-150 shadow-2xs cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Kirim Catatan Koreksi</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Daftar Catatan Koreksi -->
    <div class="space-y-4">
        @forelse ($submission['notes'] as $note)
            <div class="border rounded-xl p-4 transition-all {{ $note['resolved'] ? 'border-gray-200 bg-white' : 'border-amber-200/90 bg-amber-50/20 shadow-2xs' }}">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- ID Catatan & Kategori -->
                        <span class="text-[10px] font-mono font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                            {{ $note['id'] }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                            {{ $note['category_label'] }}
                        </span>

                        <!-- Tingkat Keparahan -->
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $note['severity_badge'] }}">
                            {{ $note['severity_label'] }}
                        </span>

                        <!-- Indikator Halaman -->
                        <span class="text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-2 py-0.5 rounded">
                            {{ $note['page'] ? 'Hal. ' . $note['page'] : 'Seluruh Dokumen' }}
                        </span>
                    </div>

                    <!-- Status Penyelesaian -->
                    <div>
                        @if($note['resolved'])
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Telah Diperbaiki</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Menunggu Tindak Lanjut Layout</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Deskripsi Catatan Proofreader -->
                <p class="text-xs sm:text-sm text-gray-800 leading-relaxed font-sans font-medium pl-1">
                    {{ $note['description'] }}
                </p>

                <!-- Tindak Lanjut / Resolusi dari Layout Editor -->
                @if($note['resolved'] && $note['resolution'])
                    <div class="mt-3 pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-emerald-50/50 p-3 rounded-lg border border-emerald-100">
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">
                                Tindakan Layout Editor ({{ $note['resolved_at'] }}):
                            </span>
                            <span class="text-xs text-emerald-950 font-medium block mt-0.5">
                                {{ $note['resolution'] }}
                            </span>
                        </div>

                        <!-- Tombol Verifikasi Proofreader -->
                        @if(!$note['verified'])
                            <form action="{{ route('proofreader.verify-note') }}" method="POST" class="shrink-0">
                                @csrf
                                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
                                <input type="hidden" name="note_id" value="{{ $note['id'] }}">
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 min-h-9 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg transition shadow-2xs cursor-pointer"
                                    title="Konfirmasi bahwa perbaikan telah sesuai"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Verifikasi Sesuai</span>
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terverifikasi Proofreader</span>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="border border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50/50">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-gray-800">Belum Ada Catatan Koreksi</h4>
                <p class="text-[11px] text-gray-500 mt-1 max-w-sm mx-auto">
                    Jika tidak ditemukan kesalahan tipografi atau format pada berkas galai ini, Anda dapat langsung melakukan persetujuan akhir (sign-off).
                </p>
            </div>
        @endforelse
    </div>
</div>
