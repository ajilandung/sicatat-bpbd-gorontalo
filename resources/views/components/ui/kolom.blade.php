@props(['nama', 'label', 'wajib' => false, 'petunjuk' => null])

{{-- Satu kolom formulir: label, kendali, petunjuk, dan pesan galat dalam satu pola. --}}
<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <label for="{{ $nama }}" class="block text-sm font-medium text-navy-800">
        {{ $label }}
        @if ($wajib)
            <span class="text-red-600" aria-hidden="true">*</span>
            <span class="sr-only">wajib diisi</span>
        @endif
    </label>

    {{ $slot }}

    @error($nama)
        <p id="{{ $nama }}-galat" class="flex items-start gap-1.5 text-sm text-red-700">
            <x-ikon nama="galat" class="mt-0.5 size-4 shrink-0"/>
            <span>{{ $message }}</span>
        </p>
    @else
        @if ($petunjuk)
            <p class="text-xs leading-relaxed text-slate-500">{{ $petunjuk }}</p>
        @endif
    @enderror
</div>
