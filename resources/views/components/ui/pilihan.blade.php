@props(['nama', 'opsi' => [], 'nilai' => null, 'kosong' => null])

@php
    $galat = $errors->has($nama);

    $kelas = 'block h-11 w-full rounded-lg border-slate-300 py-0 text-sm text-navy-900 shadow-kartu transition-colors
              focus:border-brand-500 focus:ring-1 focus:ring-brand-500
              disabled:bg-slate-100 disabled:text-slate-500'
        .($galat ? ' border-red-400 focus:border-red-500 focus:ring-red-500' : '');
@endphp

<select name="{{ $nama }}"
        id="{{ $attributes->get('id', $nama) }}"
        @if ($galat) aria-invalid="true" aria-describedby="{{ $nama }}-galat" @endif
        {{ $attributes->except('id')->merge(['class' => $kelas]) }}>
    @if ($kosong !== null)
        <option value="">{{ $kosong }}</option>
    @endif

    @foreach ($opsi as $kunci => $label)
        <option value="{{ $kunci }}" @selected((string) old($nama, $nilai) === (string) $kunci)>{{ $label }}</option>
    @endforeach
</select>
