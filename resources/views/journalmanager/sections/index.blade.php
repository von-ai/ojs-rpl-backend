@extends('layouts.journalmanager')

@section('title', 'Bidang Naskah / Seksi — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman & Tombol Tambah Seksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Bidang Naskah / Seksi Penelitian
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Atur pengelompokan tema riset jurnal, kebijakan penerimaan naskah baru, dan urutan daftar isi terbitan.
            </p>
        </div>

        <button 
            type="button" 
            onclick="openCreateSectionModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition self-start sm:self-center cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Bidang Naskah</span>
        </button>
    </div>

    @if(isset($journal) && $journal)
        <!-- Tenant Workspace Active Context Bar -->
        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-white border border-gray-200/90 shadow-2xs text-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-brand-maroon border border-rose-200/70 shrink-0">
                    Ruang Kerja Jurnal
                </span>
                <span class="font-semibold text-gray-900 truncate" title="{{ $journal->name }}">
                    {{ $journal->name }}
                </span>
                <span class="text-gray-300 hidden sm:inline">•</span>
                <span class="text-gray-500 font-mono text-[11px] hidden sm:inline">/{{ $journal->slug }}</span>
            </div>
        </div>
    @endif

    @if(session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <!-- 3 Metrik Ringkas Seksi -->
    @php
        $sectionIcons = [
            0 => 'folder',
            1 => 'check-circle',
            2 => 'alert-triangle',
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $idx => $metric)
            <x-kpi-card
                :title="$metric['title']"
                :value="$metric['value']"
                :icon="$sectionIcons[$idx] ?? 'folder'"
            />
        @endforeach
    </div>

    <!-- Tabel Daftar Seksi -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-900">
                    Daftar Seksi & Kebijakan Penerimaan Naskah
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pengelompokan bidang kajian riset untuk proses penelaahan sejawat dan kurasi daftar isi.
                </p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-white text-gray-600 border border-gray-200/90 shadow-2xs self-start sm:self-auto tabular-nums">
                <span>Total {{ count($viewModel->sections()) }} Seksi Terdaftar</span>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase">
                        <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO.</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">BIDANG RISET / SEKSI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-64">FOKUS & RUANG LINGKUP</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">NASKAH</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">STATUS PENERIMAAN</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->sections() as $section)
                        <tr class="hover:bg-gray-50/70 transition duration-150">
                            <!-- Kolom NO (Tanpa Pagar) -->
                            <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-medium text-gray-500 tabular-nums">
                                {{ $section['position'] ?? $loop->iteration }}
                            </td>

                            <!-- Kolom Nama Seksi -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900 block leading-snug">
                                    {{ $section['name'] }}
                                </span>
                            </td>

                            <!-- Kolom Deskripsi Fokus & Ruang Lingkup -->
                            <td class="py-4 px-4 sm:px-6 align-top min-w-64 max-w-lg">
                                @if(!empty($section['description']))
                                    <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                        {{ $section['description'] }}
                                    </p>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum ada deskripsi ruang lingkup</span>
                                @endif
                            </td>

                            <!-- Kolom Jumlah Naskah -->
                            <td class="py-4 px-4 sm:px-6 align-top text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 tabular-nums">
                                    {{ $section['submissions_count'] }}
                                </span>
                            </td>

                            <!-- Kolom Status Penerimaan (Bahasa Indonesia) -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                @if($section['accepting_submissions'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>Menerima Naskah</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        <span>Pengajuan Ditutup</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Aksi (Buka/Tutup Status Cepat & Edit Seksi) -->
                            <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Cepat Buka / Tutup Penerimaan Naskah (Dengan Modal Konfirmasi) -->
                                    <div class="relative group/tooltip inline-block">
                                        <button 
                                            type="button" 
                                            onclick='openToggleSectionStatusModal(@json($section))'
                                            title="{{ $section['accepting_submissions'] ? 'Tutup Penerimaan Naskah' : 'Buka Penerimaan Naskah' }}"
                                            class="w-7 h-7 inline-flex items-center justify-center rounded-lg border {{ $section['accepting_submissions'] ? 'border-amber-200 bg-amber-50/70 text-amber-700 hover:bg-amber-100 hover:border-amber-300 focus:ring-amber-400' : 'border-emerald-200 bg-emerald-50/70 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-300 focus:ring-emerald-400' }} shadow-2xs transition cursor-pointer focus:outline-hidden focus:ring-2"
                                            aria-label="{{ $section['accepting_submissions'] ? 'Tutup Penerimaan Naskah' : 'Buka Penerimaan Naskah' }}"
                                        >
                                            @if($section['accepting_submissions'])
                                                <!-- Icon Gembok Terkunci (Klik untuk menutup naskah) -->
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            @else
                                                <!-- Icon Gembok Terbuka (Klik untuk membuka naskah) -->
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </button>
                                        <!-- Tooltip Pop-up di Atas Tombol -->
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all duration-150 flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                            <div class="px-2.5 py-1 text-[11px] font-semibold text-white bg-gray-900 rounded-md shadow-lg">
                                                {{ $section['accepting_submissions'] ? 'Tutup Penerimaan Naskah' : 'Buka Penerimaan Naskah' }}
                                            </div>
                                            <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45"></div>
                                        </div>
                                    </div>

                                    <!-- Tombol Edit Modal -->
                                    <div class="relative group/tooltip inline-block">
                                        <button 
                                            type="button" 
                                            onclick='openEditSectionModal(@json($section))'
                                            title="Edit Bidang Naskah"
                                            class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50/70 text-gray-700 hover:bg-gray-100 hover:border-gray-300 shadow-2xs transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                                            aria-label="Edit Bidang Naskah"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <!-- Tooltip Pop-up di Atas Tombol -->
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all duration-150 flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                            <div class="px-2.5 py-1 text-[11px] font-semibold text-white bg-gray-900 rounded-md shadow-lg">
                                                Edit Bidang Naskah
                                            </div>
                                            <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Belum Ada Bidang Riset</h3>
                                    <p class="text-xs text-gray-500 mb-4 text-center">Tambahkan seksi bidang naskah untuk mengelompokkan tema penelitian jurnal Anda.</p>
                                    <button type="button" onclick="openCreateSectionModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Tambah Bidang Naskah</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
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
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-section-title">
                            Tambah Bidang Naskah Baru
                        </h3>
                        <p class="text-[11px] text-gray-500">Buat seksi bidang riset dan tetapkan kebijakan penerimaan naskah</p>
                    </div>
                </div>

                <button type="button" onclick="closeCreateSectionModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.sections.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="section_name" class="block text-xs font-semibold text-gray-700 mb-1">
                        Nama Bidang Riset / Seksi <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="section_name" 
                        placeholder="Contoh: Tata Kelola Perkotaan & Smart Governance" 
                        required 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>

                <div>
                    <label for="section_desc" class="block text-xs font-semibold text-gray-700 mb-1">
                        Deskripsi & Ruang Lingkup Seksi
                    </label>
                    <textarea 
                        name="description" 
                        id="section_desc" 
                        rows="3" 
                        placeholder="Jelaskan topik-topik penelitian yang relevan pada seksi bidang riset ini..." 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    ></textarea>
                </div>

                <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-200">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="accepting_submissions" 
                            id="accepting_submissions" 
                            value="1" 
                            checked 
                            class="mt-0.5 w-4 h-4 text-brand-maroon rounded border-gray-300 focus:ring-brand-maroon accent-brand-maroon"
                        >
                        <div>
                            <span class="block text-xs font-semibold text-gray-800">Buka penerimaan naskah baru untuk seksi ini</span>
                            <span class="block text-[11px] text-gray-500 mt-0.5 leading-relaxed">Penulis (Author) dapat memilih seksi ini ketika mengajukan artikel baru. Jika tidak dicentang, seksi ini ditutup untuk pengajuan publik.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCreateSectionModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer">
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

    function openEditSectionModal(section) {
        const modal = document.getElementById('modal-edit-section');
        const form = document.getElementById('form-edit-section');
        const nameInput = document.getElementById('edit_section_name');
        const descInput = document.getElementById('edit_section_desc');
        const acceptInput = document.getElementById('edit_accepting_submissions');

        if (form && section.id) {
            form.action = `/journalmanager/sections/${section.id}`;
        }
        if (nameInput) nameInput.value = section.name || '';
        if (descInput) descInput.value = section.description || '';
        if (acceptInput) acceptInput.checked = Boolean(section.accepting_submissions);

        if (modal) modal.classList.remove('hidden');
    }

    function closeEditSectionModal() {
        const modal = document.getElementById('modal-edit-section');
        if (modal) modal.classList.add('hidden');
    }

    function openToggleSectionStatusModal(section) {
        const modal = document.getElementById('modal-toggle-section-status');
        const form = document.getElementById('form-toggle-section-status');
        const titleEl = document.getElementById('modal-toggle-title');
        const descEl = document.getElementById('modal-toggle-desc');
        const nameEl = document.getElementById('modal-toggle-section-name');
        const currentStatusEl = document.getElementById('modal-toggle-current-status');
        const nextActionEl = document.getElementById('modal-toggle-next-action');
        const iconBox = document.getElementById('toggle-status-icon-box');
        const submitBtn = document.getElementById('modal-toggle-submit-btn');

        if (form && section.id) {
            form.action = `/journalmanager/sections/${section.id}/toggle-status`;
        }
        if (nameEl) nameEl.textContent = section.name || '-';

        const isCurrentlyAccepting = Boolean(section.accepting_submissions);

        if (isCurrentlyAccepting) {
            // Tindakan: Tutup Penerimaan Naskah
            titleEl.textContent = 'Tutup Penerimaan Naskah?';
            descEl.innerHTML = `Seksi <strong>${section.name}</strong> akan ditutup. Penulis tidak akan dapat memilih seksi ini pada formulir pengajuan naskah baru. Naskah yang sedang berjalan tetap dapat diproses oleh dewan redaksi.`;
            currentStatusEl.textContent = 'Menerima Naskah (Aktif)';
            currentStatusEl.className = 'font-semibold text-emerald-600';
            nextActionEl.textContent = 'Tutup Penerimaan Naskah Baru';
            nextActionEl.className = 'font-semibold text-amber-600';

            iconBox.className = 'w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0';
            iconBox.innerHTML = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>`;

            submitBtn.className = 'px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-2xs transition cursor-pointer';
            submitBtn.textContent = 'Ya, Tutup Penerimaan';
        } else {
            // Tindakan: Buka Penerimaan Naskah
            titleEl.textContent = 'Buka Kembali Penerimaan Naskah?';
            descEl.innerHTML = `Seksi <strong>${section.name}</strong> akan dibuka kembali. Penulis akan dapat memilih seksi ini saat mengajukan artikel baru di portal jurnal.`;
            currentStatusEl.textContent = 'Pengajuan Naskah Ditutup';
            currentStatusEl.className = 'font-semibold text-gray-500';
            nextActionEl.textContent = 'Buka Penerimaan Naskah Baru';
            nextActionEl.className = 'font-semibold text-emerald-600';

            iconBox.className = 'w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0';
            iconBox.innerHTML = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>`;

            submitBtn.className = 'px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition cursor-pointer';
            submitBtn.textContent = 'Ya, Buka Penerimaan Naskah';
        }

        if (modal) modal.classList.remove('hidden');
    }

    function closeToggleSectionStatusModal() {
        const modal = document.getElementById('modal-toggle-section-status');
        if (modal) modal.classList.add('hidden');
    }
</script>

<!-- Modal Dialog: Edit Bidang Naskah -->
<div id="modal-edit-section" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-edit-section-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeEditSectionModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-edit-section-title">
                            Edit Bidang Naskah / Seksi
                        </h3>
                        <p class="text-[11px] text-gray-500">Perbarui nama seksi riset dan kelola status penerimaan naskah</p>
                    </div>
                </div>

                <button type="button" onclick="closeEditSectionModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-edit-section" action="" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_section_name" class="block text-xs font-semibold text-gray-700 mb-1">
                        Nama Bidang Riset / Seksi <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="edit_section_name" 
                        required 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>

                <div>
                    <label for="edit_section_desc" class="block text-xs font-semibold text-gray-700 mb-1">
                        Deskripsi & Ruang Lingkup Seksi
                    </label>
                    <textarea 
                        name="description" 
                        id="edit_section_desc" 
                        rows="3" 
                        placeholder="Jelaskan topik-topik penelitian yang relevan pada seksi bidang riset ini..." 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    ></textarea>
                </div>

                <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-200">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="accepting_submissions" 
                            id="edit_accepting_submissions" 
                            value="1" 
                            class="mt-0.5 w-4 h-4 text-brand-maroon rounded border-gray-300 focus:ring-brand-maroon accent-brand-maroon"
                        >
                        <div>
                            <span class="block text-xs font-semibold text-gray-800">Buka penerimaan naskah baru untuk seksi ini</span>
                            <span class="block text-[11px] text-gray-500 mt-0.5 leading-relaxed">
                                Centang untuk membuka penerimaan naskah. Hapus centang untuk <strong>menutup naskah</strong> pada seksi ini agar tidak dapat dipilih oleh penulis saat mengajukan artikel baru.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditSectionModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dialog: Konfirmasi Ubah Status Penerimaan Naskah (Lock / Unlock) -->
<div id="modal-toggle-section-status" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-toggle-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeToggleSectionStatusModal()"></div>

        <!-- Panel Konten Modal -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-start gap-3.5">
                <div id="toggle-status-icon-box" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                    <!-- Icon dinamis -->
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug" id="modal-toggle-title">
                        Konfirmasi Status Seksi
                    </h3>
                    <p id="modal-toggle-desc" class="text-xs text-gray-600 mt-1 leading-relaxed">
                        Deskripsi konfirmasi aksi status seksi...
                    </p>
                </div>
                <button type="button" onclick="closeToggleSectionStatusModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer shrink-0" aria-label="Tutup dialog">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Detail Ringkasan Seksi -->
            <div class="mt-4 p-3.5 bg-gray-50/90 rounded-xl border border-gray-200/80 text-xs space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 font-medium shrink-0">Nama Seksi:</span>
                    <span id="modal-toggle-section-name" class="font-bold text-gray-900 truncate text-right"></span>
                </div>
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-200/60">
                    <span class="text-gray-500 font-medium shrink-0">Status Sekarang:</span>
                    <span id="modal-toggle-current-status" class="font-semibold"></span>
                </div>
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-200/60">
                    <span class="text-gray-500 font-medium shrink-0">Aksi Yang Dilakukan:</span>
                    <span id="modal-toggle-next-action" class="font-semibold"></span>
                </div>
            </div>

            <!-- Form Eksekusi Toggle Status -->
            <form id="form-toggle-section-status" action="" method="POST" class="mt-5">
                @csrf
                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeToggleSectionStatusModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer">
                        Batalkan
                    </button>
                    <button id="modal-toggle-submit-btn" type="submit" class="px-4 py-2 text-xs font-semibold text-white rounded-lg shadow-2xs transition cursor-pointer">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
