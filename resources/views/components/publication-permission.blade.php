@if(filled($license))
<form method="post" action="{{ route($editor ? 'eic.submissions.permission' : 'author.submissions.permission', $id) }}" class="space-y-3 rounded-xl border bg-white p-5">
    @csrf
    <h2 class="font-semibold">Izin penerbitan naskah</h2>
    <input type="hidden" name="license" value="{{ trim($license) }}">
    @if($editor)
        <label>Referensi perjanjian tertulis
            <input required maxlength="255" name="agreement_reference" placeholder="Nomor atau kode arsip perjanjian">
        </label>
    @endif
    <label class="block">
        <input required type="checkbox" name="confirmed" value="1">
        {{ $editor ? 'Saya telah memverifikasi perjanjian penulis untuk menerbitkan naskah ini dengan lisensi' : 'Saya mengizinkan penerbitan naskah ini dengan lisensi' }}
        <strong>{{ $license }}</strong>.
    </label>
    @foreach(['confirmed', 'license', 'agreement_reference'] as $field)
        @error($field)<p role="alert">{{ $message }}</p>@enderror
    @endforeach
    <button class="underline" type="submit">Catat izin penerbitan</button>
</form>
@endif
