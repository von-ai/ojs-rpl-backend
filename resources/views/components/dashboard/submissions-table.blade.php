@props(['submissions' => []])

<div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
    <!-- Header Row -->
    <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight">
                Naskah Saya (Semua Pengajuan)
            </h2>
            <p class="text-xs text-gray-500">
                Daftar naskah riset aktif dan arsip publikasi di BRIDA Makassar
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter Status</span>
            </button>

            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                <span>Terbaru</span>
            </button>
        </div>
    </div>

    <!-- Table Responsive Container -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50/80 text-[10px] font-bold text-gray-500 border-b border-gray-200">
                <tr>
                    <th scope="col" class="py-3 px-4">ID Naskah</th>
                    <th scope="col" class="py-3 px-4">Judul Artikel</th>
                    <th scope="col" class="py-3 px-4">Tanggal Submit</th>
                    <th scope="col" class="py-3 px-4">Tahapan Alur</th>
                    <th scope="col" class="py-3 px-4">Status</th>
                    <th scope="col" class="py-3 px-4">Pembaruan</th>
                    <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($submissions as $sub)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="py-3.5 px-4 font-mono text-xs font-semibold text-gray-700 whitespace-nowrap">
                            {{ $sub['id'] }}
                        </td>
                        <td class="py-3.5 px-4 max-w-xs sm:max-w-sm">
                            <a href="{{ $sub['action_url'] ?? '#' }}" class="font-bold text-gray-900 hover:text-brand-maroon transition-colors line-clamp-2 leading-snug">
                                {{ $sub['title'] }}
                            </a>
                            <span class="text-[11px] text-gray-500 block mt-0.5">Bagian: {{ $sub['section'] }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                            {{ $sub['submitted_date'] }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-gray-800 whitespace-nowrap">
                            {{ $sub['stage'] }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $sub['status_class'] }}">
                                {{ $sub['status_label'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                            {{ $sub['updated_date'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <a href="{{ $sub['action_url'] ?? '#' }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg transition-all shadow-2xs {{ $sub['action_class'] }}">
                                {{ $sub['action_label'] }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-xs text-gray-400 italic">
                            Belum ada naskah penelitian yang diajukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer & Pagination -->
    <div class="p-4 bg-gray-50/80 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
        <span>Menampilkan <strong>{{ count($submissions) }}</strong> dari 7 naskah penelitian</span>

        <div class="flex items-center gap-1.5">
            <button type="button" class="px-3 py-1 rounded bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 disabled:opacity-50 text-xs font-semibold">
                Sebelumnya
            </button>
            <span class="px-2 py-1 text-xs font-bold text-gray-800">Hal 1 dari 2</span>
            <button type="button" class="px-3 py-1 rounded bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 text-xs font-semibold">
                Selanjutnya
            </button>
        </div>
    </div>
</div>
