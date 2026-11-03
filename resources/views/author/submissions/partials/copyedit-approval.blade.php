@if($copyeditApproval ?? null)
<section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6 space-y-4">
    <h2 class="text-sm font-bold text-gray-900">Tinjau hasil copyediting</h2>
    <p class="text-xs text-gray-600">Periksa seluruh berkas suntingan sebelum memberikan persetujuan atau meminta perbaikan.</p>
    @foreach($copyeditApproval->copyedit->outputs as $output)
        @if($output->file)
            <a href="{{ route('copyeditor.files.download', $output->file_id) }}" class="block text-sm font-semibold text-brand-maroon hover:underline">{{ $output->file->original_name }}</a>
        @endif
    @endforeach
    <form method="POST" action="{{ route('copyeditor.author-decision') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
        <label for="copyedit-decision" class="block text-xs font-semibold text-gray-700">Tanggapan Anda</label>
        <x-dropdown name="decision" id="copyedit-decision" :options="['approved' => 'Setujui hasil suntingan', 'revisions_requested' => 'Minta perbaikan']" required />
        <label for="copyedit-reason" class="block text-xs font-semibold text-gray-700">Alasan perbaikan (wajib jika meminta perbaikan)</label>
        <textarea name="reason" id="copyedit-reason" maxlength="4000" rows="3" class="w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-brand-maroon">{{ old('reason') }}</textarea>
        <x-button>Kirim tanggapan</x-button>
    </form>
</section>
@endif
