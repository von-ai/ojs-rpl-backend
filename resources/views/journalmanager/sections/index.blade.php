@extends('layouts.journalmanager')

@section('title', 'Bidang Naskah / Seksi — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman & Tombol Tambah Seksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Bidang Naskah / Seksi Penelitian
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Atur pengelompokan tema riset jurnal, kebijakan penerimaan submission, dan urutan daftar isi.
            </p>
        </div>

        <button 
            type="button" 
            onclick="openCreateSectionModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition self-start sm:self-center"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Bidang Naskah</span>
        </button>
    </div>

    <!-- 3 Metrik Ringkas Seksi -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
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
                    <span class="text-[11px] text-gray-400">bidang riset</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tabel Daftar Seksi -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-gray-200/90 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-xs sm:text-sm font-bold text-gray-800">
                Daftar Seksi & Kebijakan Penerimaan Naskah
            </h2>
            <span class="text-xs text-gray-500">
                Total {{ count($viewModel->sections()) }} Seksi Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 sm:px-5 w-16 text-center">Urutan</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Bidang Riset / Seksi</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Fokus & Ruang Lingkup</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-center">Naskah</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Status Penerimaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($viewModel->sections() as $section)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Kolom Urutan -->
                            <td class="py-3.5 px-4 sm:px-5 text-center font-bold text-gray-400 font-mono text-xs">
                                #{{ $section['position'] }}
                            </td>

                            <!-- Kolom Nama & Slug -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="font-bold text-gray-900 block leading-tight">
                                    {{ $section['name'] }}
                                </span>
                                <span class="font-mono text-[10px] text-gray-400 block mt-0.5">
                                    /{{ $section['slug'] }}
                                </span>
                            </td>

                            <!-- Kolom Deskripsi -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-sm sm:max-w-md">
                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                    {{ $section['description'] }}
                                </p>
                            </td>

                            <!-- Kolom Jumlah Naskah -->
                            <td class="py-3.5 px-4 sm:px-5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                    {{ $section['submissions_count'] }}
                                </span>
                            </td>

                            <!-- Kolom Status Penerimaan -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $section['status_badge_class'] }}">
                                    {{ $section['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Dialog: Tambah Bidang Naskah Baru -->
<div id="modal-create-section" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-section-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeCreateSectionModal()"></div>

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
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-section-title">
                            Tambah Bidang Naskah Baru
                        </h3>
                        <p class="text-[11px] text-gray-500">Buat seksi bidang riset dan kebijakan submission</p>
                    </div>
                </div>

                <button type="button" onclick="closeCreateSectionModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.sections.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="section_name" class="block text-xs font-semibold text-gray-700 mb-1">Nama Bidang Riset / Seksi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="section_name" placeholder="Contoh: Tata Kelola Perkotaan & Smart Governance" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div>
                    <label for="section_desc" class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi & Ruang Lingkup Seksi</label>
                    <textarea name="description" id="section_desc" rows="3" placeholder="Jelaskan topik-topik penelitian yang relevan pada seksi ini..." class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="accepting_submissions" id="accepting_submissions" value="1" checked class="w-4 h-4 text-brand-maroon rounded border-gray-300 focus:ring-brand-maroon">
                    <label for="accepting_submissions" class="text-xs font-semibold text-gray-700">Aktifkan penerimaan naskah baru untuk seksi ini</label>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCreateSectionModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition">
                        Simpan Bidang Naskah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCreateSectionModal() {
        const modal = document.getElementById('modal-create-section');
        if (modal) modal.classList.remove('hidden');
    }
    function closeCreateSectionModal() {
        const modal = document.getElementById('modal-create-section');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection

