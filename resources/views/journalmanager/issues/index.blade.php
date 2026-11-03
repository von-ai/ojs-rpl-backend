@extends('layouts.journalmanager')

@section('title', 'Katalog Edisi & Terbitan — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman Simpel & Tombol Aksi Utama -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Edisi & Terbitan Berkala
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola nomor terbitan jurnal (Volume, Nomor, Tahun), penyusunan daftar isi naskah, dan otorisasi publikasi.
            </p>
        </div>

        <button 
            type="button" 
            onclick="openCreateIssueModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition self-start sm:self-center"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Edisi Baru</span>
        </button>
    </div>

    <!-- 4 Metrik Ringkas & Ergonomis -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 truncate">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border tabular-nums {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-gray-900 tabular-nums tracking-tight">
                        {{ $metric['value'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">edisi</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Container: Filter Tabs, Pencarian & Tabel Edisi -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
        
        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Status -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Status Edisi">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a 
                        href="{{ route('journalmanager.issues.index', ['tab' => $tabKey, 'q' => request('q')]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] rounded-full tabular-nums {{ $isActive ? 'bg-white/20 text-white font-bold' : 'bg-gray-200/70 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Cepat -->
            <form action="{{ route('journalmanager.issues.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="tab" value="{{ $tabs['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="search" 
                    name="q" 
                    value="{{ request('q') }}"
                    placeholder="Cari volume, nomor, atau judul edisi..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a 
                        href="{{ route('journalmanager.issues.index', ['tab' => $tabs['active']]) }}" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                        title="Hapus pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Daftar Edisi Terbitan -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 sm:px-5">Nomor & Judul Edisi</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 whitespace-nowrap">Tanggal Terbit</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-center">Naskah</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Status</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->issues() as $issue)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- Kolom Edisi & Judul -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-sm sm:max-w-md">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-mono font-bold text-brand-maroon">
                                        Vol. {{ $issue['volume'] }} No. {{ $issue['number'] }} ({{ $issue['year'] }})
                                    </span>
                                    @if($issue['is_current'])
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            <span>Current Issue</span>
                                        </span>
                                    @endif
                                </div>
                                <a 
                                    href="{{ $issue['url_workspace'] }}" 
                                    class="font-serif font-bold text-gray-900 group-hover:text-brand-maroon transition leading-snug line-clamp-1"
                                    title="{{ $issue['title'] }}"
                                >
                                    {{ $issue['title'] }}
                                </a>
                                @if(!empty($issue['description']))
                                    <p class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">
                                        {{ $issue['description'] }}
                                    </p>
                                @endif
                            </td>

                            <!-- Kolom Tanggal Terbit -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap text-xs text-gray-600">
                                @if($issue['published_at'])
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="font-medium text-gray-800">{{ $issue['published_at'] }}</span>
                                    </div>
                                @elseif($issue['scheduled_at'])
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium text-blue-700">Jadwal: {{ $issue['scheduled_at'] }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-[11px]">Belum Dijadwalkan</span>
                                @endif
                            </td>

                            <!-- Kolom Jumlah Naskah -->
                            <td class="py-3.5 px-4 sm:px-5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                    {{ $issue['articles_count'] }} Artikel
                                </span>
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $issue['status_badge_class'] }}">
                                    {{ $issue['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-3.5 px-4 sm:px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($issue['status'] === 'published' && !$issue['is_current'])
                                        <form action="{{ route('journalmanager.issues.set-current', $issue['id']) }}" method="POST" class="inline">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                class="px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition"
                                                title="Tetapkan sebagai Terbitan Berjalan"
                                            >
                                                Jadikan Current
                                            </button>
                                        </form>
                                    @endif

                                    <a 
                                        href="{{ $issue['url_workspace'] }}" 
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-brand-maroon bg-brand-maroon/5 hover:bg-brand-maroon hover:text-white rounded-lg transition border border-brand-maroon/20"
                                        title="Buka Ruang Kerja Daftar Isi Edisi"
                                    >
                                        <span>Daftar Isi</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada edisi terbitan</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada edisi nomor terbitan yang cocok dengan filter atau kata kunci ini.
                                    </p>
                                    @if(request('q') || request('tab') !== 'all')
                                        <a href="{{ route('journalmanager.issues.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
                                            Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Simpel -->
        @php
            $pagination = $viewModel->pagination();
        @endphp
        <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span>
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> edisi
            </span>

            <div class="flex items-center gap-1">
                <button type="button" disabled class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-400 cursor-not-allowed">
                    Sebelumnya
                </button>
                <button type="button" disabled class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-400 cursor-not-allowed">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dialog: Buat Edisi Baru -->
<div id="modal-create-issue" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-create-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeCreateIssueModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-create-title">
                            Buat Edisi Nomor Terbitan Baru
                        </h3>
                        <p class="text-[11px] text-gray-500">Buka edisi baru untuk kurasi naskah publikasi</p>
                    </div>
                </div>

                <button type="button" onclick="closeCreateIssueModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.issues.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label for="volume" class="block text-xs font-semibold text-gray-700 mb-1">Volume <span class="text-red-500">*</span></label>
                        <input type="text" name="volume" id="volume" value="6" required class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="number" class="block text-xs font-semibold text-gray-700 mb-1">Nomor <span class="text-red-500">*</span></label>
                        <input type="text" name="number" id="number" value="3" required class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="year" class="block text-xs font-semibold text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                        <input type="number" name="year" id="year" value="2026" required class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                </div>

                <div>
                    <label for="title" class="block text-xs font-semibold text-gray-700 mb-1">Judul Khusus Edisi (Opsional)</label>
                    <input type="text" name="title" id="title" placeholder="Contoh: Edisi Khusus Riset Inovasi Maritim" class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-gray-700 mb-1">Kata Pengantar / Deskripsi Edisi</label>
                    <textarea name="description" id="description" rows="3" placeholder="Deskripsi ringkas fokus dan pengantar dewan redaksi untuk edisi ini..." class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCreateIssueModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition">
                        Simpan Draf Edisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCreateIssueModal() {
        const modal = document.getElementById('modal-create-issue');
        if (modal) modal.classList.remove('hidden');
    }
    function closeCreateIssueModal() {
        const modal = document.getElementById('modal-create-issue');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection

