@props(['nama', 'opsi' => [], 'nilai' => null, 'kosong' => null])

@php
    $galat = $errors->has($nama);

    $kelas = 'block h-11 w-full rounded-lg border-slate-300 py-0 text-sm text-navy-900 shadow-kartu transition-colors
              focus:border-air-500 focus:ring-1 focus:ring-air-500
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

    {{-- Nilai berupa array diperlakukan sebagai kelompok, mis. daftar kecamatan
         yang dikelompokkan per kabupaten. --}}
    @foreach ($opsi as $kunci => $label)
        @if (is_array($label))
            <optgroup label="{{ $kunci }}">
                @foreach ($label as $subKunci => $subLabel)
                    <option value="{{ $subKunci }}" @selected((string) old($nama, $nilai) === (string) $subKunci)>{{ $subLabel }}</option>
                @endforeach
            </optgroup>
        @else
            <option value="{{ $kunci }}" @selected((string) old($nama, $nilai) === (string) $kunci)>{{ $label }}</option>
        @endif
    @endforeach
</select>
