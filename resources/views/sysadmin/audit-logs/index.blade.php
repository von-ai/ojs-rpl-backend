@extends('layouts.sysadmin')

@section('title', 'Jejak Audit & Pemantauan Sistem — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-sans">
                Jejak Audit &amp; Pemantauan Sistem (audit_logs)
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Catatan permanen append-only atas seluruh mutasi konfigurasi, perubahan peran sistem, dan eksekusi tugas latar belakang.
            </p>
        </div>
    </div>

    <!-- 4 Metrik Ringkas Audit Logs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 truncate">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-xl font-bold text-gray-900 tracking-tight">
                        {{ $metric['value'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Container: Filter Tabs, Pencarian & Tabel Log Audit -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">

        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Tipe Aktor -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Aktor Audit">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a
                        href="{{ route('sysadmin.audit-logs.index', ['actor_type' => $tabKey, 'q' => request('q')]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] rounded-full tabular-nums {{ $isActive ? 'bg-white/20 text-white font-bold' : 'bg-gray-200/70 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Cepat Log -->
            <form action="{{ route('sysadmin.audit-logs.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="actor_type" value="{{ $tabs['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari aksi, aktor, atau request ID..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a
                        href="{{ route('sysadmin.audit-logs.index', ['actor_type' => $tabs['active']]) }}"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                        title="Hapus filter pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Catatan Jejak Audit -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold uppercase tracking-wider text-[10px]">
                        <th scope="col" class="py-3 px-4">Waktu &amp; Request ID</th>
                        <th scope="col" class="py-3 px-4">Aktor Pelaksana</th>
                        <th scope="col" class="py-3 px-4">Tindakan / Aksi</th>
                        <th scope="col" class="py-3 px-4">Entitas Sumber Daya</th>
                        <th scope="col" class="py-3 px-4">Alasan &amp; Justifikasi</th>
                        <th scope="col" class="py-3 px-4">Diff Data Mutasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->logs() as $log)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Kolom ID, Waktu & Request ID -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-gray-900 block">
                                    {{ $log['id'] }}
                                </span>
                                <span class="text-xs text-gray-600 block mt-0.5">
                                    {{ $log['occurred_at'] }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-400 block mt-0.5">
                                    {{ $log['request_id'] }}
                                </span>
                            </td>

                            <!-- Kolom Aktor Pelaksana -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-gray-800 block">
                                        {{ $log['actor_name'] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    @if($log['actor_type'] === 'user')
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                            User
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-50 text-purple-800 border border-purple-200 font-mono">
                                            {{ $log['system_actor'] ?? 'System Daemon' }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-gray-500 truncate max-w-36">
                                        {{ $log['effective_role'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Kolom Tindakan / Aksi -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-gray-900 block">
                                    {{ $log['action_label'] }}
                                </span>
                                <code class="text-[10px] font-mono text-brand-maroon block mt-0.5">
                                    {{ $log['action'] }}
                                </code>
                            </td>

                            <!-- Kolom Entitas Sumber Daya -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono text-gray-700 block font-semibold">
                                    {{ $log['resource_type'] }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-400 block">
                                    ID: {{ $log['resource_id'] }}
                                </span>
                            </td>

                            <!-- Kolom Alasan -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-gray-600 line-clamp-2 text-xs leading-relaxed">
                                    {{ $log['reason'] ?? '—' }}
                                </p>
                            </td>

                            <!-- Kolom Diff Data Mutasi -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log['diff'])
                                    <details class="cursor-pointer group">
                                        <summary class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-maroon hover:underline">
                                            <span>Lihat Diff Data</span>
                                            <svg class="w-3 h-3 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </summary>
                                        <div class="mt-2 p-2.5 rounded-lg bg-gray-900 text-gray-100 font-mono text-[10px] max-w-xs overflow-x-auto shadow-md">
                                            <pre>{{ json_encode($log['diff'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    </details>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada log audit</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ditemukan catatan audit yang cocok dengan filter.
                                    </p>
                                    @if(request('q') || request('actor_type') !== 'all')
                                        <a href="{{ route('sysadmin.audit-logs.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
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
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> entri log
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
@endsection
