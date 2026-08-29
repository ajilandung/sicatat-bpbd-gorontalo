{{-- Ringkasan kesalahan validasi di atas formulir, supaya pengguna tidak perlu
     memindai sendiri kolom mana yang bermasalah. --}}
@if ($errors->any())
    <x-ui.notifikasi jenis="galat" judul="Periksa kembali isian berikut" {{ $attributes }}>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->unique() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </x-ui.notifikasi>
@endif
