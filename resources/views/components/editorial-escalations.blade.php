@props(['submission'])
@php $routePrefix = request()->attributes->get('portal.role') === 'editor_in_chief' ? 'eic' : 'section-editor'; @endphp
@foreach($submission->escalations->whereIn('status', ['open', 'investigating']) as $escalation)
<section class="rounded-xl border border-amber-200 bg-white p-5 space-y-3">
    <h2 class="text-sm font-bold text-gray-900">Mediasi produksi menunggu penyelesaian</h2>
    <p class="text-sm text-gray-700">{{ $escalation->reason }}</p>
    <form method="POST" action="{{ route($routePrefix.'.submissions.escalations.resolve', [$submission->id, $escalation->id]) }}" class="space-y-3">
        @csrf
        <label for="resolution-{{ $escalation->id }}" class="block text-xs font-semibold text-gray-700">Catatan penyelesaian editor</label>
        <textarea id="resolution-{{ $escalation->id }}" name="resolution" required minlength="5" maxlength="4000" rows="3" class="w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-brand-maroon"></textarea>
        <x-button>Selesaikan mediasi</x-button>
    </form>
</section>
@endforeach
