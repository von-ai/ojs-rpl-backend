<!-- Tampilan Desktop & Tablet (≥ 640px): Tabel Penuh dalam Kontainer Horizontal -->
<div class="hidden sm:block overflow-x-auto">
    <table class="w-full text-left border-collapse text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">ID NASKAH</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-70">JUDUL ARTIKEL</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">SEKSI JURNAL</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">TANGGAL SUBMIT</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">PEMBARUAN</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">STATUS NASKAH</th>
                <th scope="col" class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">AKSI</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($submissions as $item)
                @php
                    $rowNumber = ($submissions instanceof \Illuminate\Contracts\Pagination\Paginator)
                        ? ($submissions->firstItem() ?? 1) + $loop->index
                        : ((request()->integer('page', 1) - 1) * 10) + $loop->iteration;
                @endphp
                <tr class="hover:bg-gray-50/70 transition duration-150">
                    <!-- Kolom 0: Nomor Urut (Dinamis & Optimal dengan Paginasi) -->
                    <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-semibold text-gray-400 tabular-nums">
                        {{ $rowNumber }}
                    </td>

                    <!-- Kolom 1: ID Naskah & DOI -->
                    <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                        <div class="text-sm font-normal text-gray-700 tabular-nums">
                            {{ $item['id'] }}
                        </div>
                        @if(!empty($item['doi_url']))
                            <div class="mt-0.5">
                                <a href="{{ $item['doi_url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-gray-500 hover:text-gray-700 hover:underline block tabular-nums" title="Digital Object Identifier">
                                     {{ $item['doi'] }}
                                </a>
                            </div>
                        @endif
                    </td>

                    <!-- Kolom 2: Judul Artikel Saja (Line-Clamp 2, Lebar Proporsional & Tooltip saat Hover) -->
                    <td class="py-4 px-4 sm:px-6 align-top min-w-70">
                        <div class="relative group block max-w-full">
                            <a 
                                href="{{ $item['action_url'] }}" 
                                aria-describedby="tooltip-desktop-{{ $item['id'] }}"
                                class="block font-normal text-gray-900 leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon hover:underline decoration-brand-maroon/40 underline-offset-2 transition duration-150 cursor-pointer"
                            >
                                {{ $item['title'] }}
                            </a>
                            
                            <!-- Tooltip Judul Lengkap Tunggal saat Hover (Accessible ARIA role="tooltip") -->
                            <div 
                                id="tooltip-desktop-{{ $item['id'] }}" 
                                role="tooltip" 
                                class="absolute left-0 top-full mt-1.5 z-30 hidden group-hover:block w-72 sm:w-80 p-2.5 bg-gray-900 text-white text-xs font-normal rounded-md pointer-events-none border border-gray-700 leading-relaxed whitespace-normal wrap-break-word shadow-lg"
                            >
                                {{ $item['title'] }}
                            </div>
                        </div>
                    </td>

                    <!-- Kolom 3: Seksi Jurnal (Tabel sections pada ERD) -->
                    <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $item['type_badge_class'] }} border shrink-0">
                            {{ $item['section'] ?? $item['type'] }}
                        </span>
                    </td>

                    <!-- Kolom 4: Tanggal Submit -->
                    <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap text-sm text-gray-700">
                        <span class="font-medium tabular-nums">{{ $item['submit_date'] }}</span>
                    </td>

                    <!-- Kolom 5: Tanggal Pembaruan -->
                    <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap text-sm text-gray-700">
                        <span class="font-medium tabular-nums" title="{{ $item['updated_at_raw'] }}">{{ $item['updated_date'] ?? $item['updated_at_raw'] }}</span>
                    </td>

                    <!-- Kolom 6: Status Naskah Sesuai ERD -->
                    <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $item['status_badge_class'] }} shrink-0">
                                {{ $item['status_label'] }}
                            </span>
                        </div>
                        @if(!empty($item['status_subtitle']))
                            <div class="mt-1 text-xs {{ $item['status_subtitle_class'] }}">
                                {{ $item['status_subtitle'] }}
                            </div>
                        @endif
                    </td>

                    <!-- Kolom 7: Aksi -->
                    <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                        <a 
                            href="{{ $item['action_url'] }}" 
                            class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                        >
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-12 px-4 text-center">
                        <div class="max-w-sm mx-auto space-y-3">
                            <div class="w-10 h-10 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">Belum Ada Naskah Ditemukan</h3>
                            <p class="text-xs text-gray-500">
                                Tidak ada naskah yang cocok dengan filter atau kata kunci pencarian yang Anda pilih.
                            </p>
                            <div class="pt-1 flex items-center justify-center gap-2">
                                <a 
                                    href="{{ route('author.submissions.index') }}" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-md transition btn-reset-filter-trigger"
                                >
                                    Reset Filter
                                </a>
                                <a 
                                    href="{{ route('author.submissions.create') }}" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-md transition"
                                >
                                    Mulai Kirim Naskah Baru
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Tampilan Mobile (< 640px) -->
<div class="block sm:hidden divide-y divide-gray-100">
    @forelse($submissions as $item)
        @php
            $rowNumber = ($submissions instanceof \Illuminate\Contracts\Pagination\Paginator)
                ? ($submissions->firstItem() ?? 1) + $loop->index
                : ((request()->integer('page', 1) - 1) * 10) + $loop->iteration;
        @endphp
        <div class="p-4 space-y-3 hover:bg-gray-50/70 transition duration-150">
            <!-- Baris Atas: Nomor Urut, ID Naskah, Jenis & Tanggal -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs font-semibold text-gray-400 tabular-nums">#{{ $rowNumber }}</span>
                    <span class="font-normal text-gray-600 tabular-nums text-xs">
                        {{ $item['id'] }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $item['type_badge_class'] }} border shrink-0">
                        {{ $item['section'] ?? $item['type'] }}
                    </span>
                </div>
                <span class="text-xs text-gray-500 tabular-nums shrink-0">
                    {{ $item['submit_date'] }}
                </span>
            </div>

            <!-- Judul Naskah Mobile -->
            <div class="min-w-0">
                <a 
                    href="{{ $item['action_url'] }}" 
                    class="block font-normal text-gray-900 text-sm leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon transition duration-150"
                    title="{{ $item['title'] }}"
                >
                    {{ $item['title'] }}
                </a>
                @if($item['doi_url'])
                    <a href="{{ $item['doi_url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-gray-500 hover:text-gray-700 hover:underline block mt-0.5 tabular-nums">
                        {{ $item['doi'] }}
                    </a>
                @endif
            </div>

            <!-- Status & Pembaruan Naskah Mobile -->
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px] font-semibold uppercase tracking-wider">Status Naskah</span>
                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $item['status_badge_class'] }}">
                            {{ $item['status_label'] }}
                        </span>
                        @if(!empty($item['status_subtitle']))
                            <span class="text-xs {{ $item['status_subtitle_class'] }}">{{ $item['status_subtitle'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-gray-400 block text-[11px] font-semibold uppercase tracking-wider">Pembaruan</span>
                    <span class="mt-1 block font-medium text-gray-700 tabular-nums" title="{{ $item['updated_at_raw'] }}">
                        {{ $item['updated_date'] ?? $item['updated_at_raw'] }}
                    </span>
                </div>
            </div>

            <!-- Baris Bawah: Tombol Aksi Detail -->
            <div class="pt-2 flex items-center justify-end border-t border-gray-100">
                <a 
                    href="{{ $item['action_url'] }}" 
                    class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                >
                    Detail
                </a>
            </div>
        </div>
    @empty
        <div class="py-12 px-4 text-center">
            <div class="max-w-sm mx-auto space-y-3">
                <div class="w-10 h-10 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Belum Ada Naskah Ditemukan</h3>
                <p class="text-xs text-gray-500">
                    Tidak ada naskah yang cocok dengan filter atau kata kunci pencarian yang Anda pilih.
                </p>
                <div class="pt-1 flex items-center justify-center gap-2">
                    <a 
                        href="{{ route('author.submissions.index') }}" 
                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-md transition btn-reset-filter-trigger"
                    >
                        Reset Filter
                    </a>
                    <a 
                        href="{{ route('author.submissions.create') }}" 
                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-md transition"
                    >
                        Mulai Kirim Naskah Baru
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Bagian Bawah Tabel / Paginasi -->
<div class="px-4 sm:px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
    <div id="submissions-pagination-info">
        Menampilkan <span class="font-bold text-gray-900 tabular-nums">{{ count($submissions) > 0 ? 1 : 0 }}</span> - <span class="font-bold text-gray-900 tabular-nums">{{ count($submissions) }}</span> dari <span class="font-bold text-gray-900 tabular-nums">{{ count($submissions) }}</span> naskah terdaftar
    </div>
    <div class="flex items-center gap-1">
        <button type="button" disabled class="min-w-9 min-h-9 p-1.5 flex items-center justify-center rounded border border-gray-200 bg-white text-gray-300 cursor-not-allowed" aria-label="Halaman sebelumnya">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="min-w-9 min-h-9 flex items-center justify-center px-2.5 py-1 text-xs font-bold text-white bg-brand-maroon rounded tabular-nums">1</span>
        <button type="button" disabled class="min-w-9 min-h-9 p-1.5 flex items-center justify-center rounded border border-gray-200 bg-white text-gray-300 cursor-not-allowed" aria-label="Halaman selanjutnya">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>
