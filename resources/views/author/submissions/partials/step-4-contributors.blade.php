<!-- ========================================== -->
<!-- LANGKAH 4: KONTRIBUTOR & PENULIS BERSAMA -->
<!-- ========================================== -->
<div id="step-panel-4" class="wizard-step-panel hidden bg-white rounded-xl border border-gray-200 p-5 sm:p-7 shadow-2xs space-y-6">
    <div class="border-b border-gray-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-brand-maroon">Langkah 4 dari 5</span>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mt-1">Penulis & Kontributor Naskah</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola seluruh kontributor naskah. Tepat satu penulis wajib ditandai sebagai <span class="font-bold text-gray-700">Penulis Korespondensi</span>.
            </p>
        </div>
        <button 
            type="button" 
            id="btn-open-add-author" 
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Penulis Bersama</span>
        </button>
    </div>

    <!-- Daftar Kontributor (Cards List) -->
    <div id="contributors-list-container" class="space-y-3">
        <!-- Kontributor cards di-render secara dinamis via JavaScript -->
    </div>
</div>
