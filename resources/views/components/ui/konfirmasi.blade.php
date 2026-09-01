@props([
    'judul',
    'pesan',
    'aksi',
    'metode' => 'PATCH',
    'label',
    'ikon' => null,
    'varian' => 'sekunder',
    'labelKonfirmasi' => 'Ya, lanjutkan',
    'varianKonfirmasi' => 'utama',
])

@php
    // Satu halaman bisa memuat banyak dialog sekaligus (misalnya satu per baris
    // tabel), jadi id-nya harus unik agar aria-labelledby menunjuk judul yang benar.
    $idDialog = 'konfirmasi-'.Str::random(8);
@endphp

{{-- Aksi yang meminta konfirmasi lewat dialog sebelum form dikirim.
     Dialognya memakai posisi fixed sehingga tidak terpotong kontainer tabel. --}}
<div x-data="{
        buka: false,
        bukaDialog() {
            this.buka = true;
            // Fokus diarahkan ke tindakan yang paling tidak merusak, bukan ke tombol
            // konfirmasi, supaya Enter tanpa sengaja tidak menjalankan aksinya.
            this.$nextTick(() => this.$refs.tombolBatal.focus());
        },
        tutup() {
            if (! this.buka) return;
            this.buka = false;
            this.$refs.pemicu?.focus();
        },
     }" class="inline-block">
    @if ($ikon)
        <x-ui.tombol-ikon :ikon="$ikon" :label="$label" :varian="$varian === 'bahaya' ? 'bahaya' : 'netral'"
                          x-ref="pemicu" @click="bukaDialog()" {{ $attributes }}/>
    @else
        <x-ui.tombol tipe="button" :varian="$varian" ukuran="kecil"
                     x-ref="pemicu" @click="bukaDialog()" {{ $attributes }}>
            {{ $label }}
        </x-ui.tombol>
    @endif

    <div x-show="buka" x-cloak class="relative z-50" role="dialog" aria-modal="true"
         aria-labelledby="{{ $idDialog }}-judul" aria-describedby="{{ $idDialog }}-pesan"
         @keydown.escape.window="tutup()">
        <div x-show="buka" x-transition.opacity class="fixed inset-0 bg-navy-950/50 backdrop-blur-[1px]"></div>

        <div class="fixed inset-0 flex items-end justify-center p-4 sm:items-center">
            <div x-show="buka" x-transition @click.outside="tutup()"
                 class="max-h-[85vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-6 text-left shadow-naik">
                <h2 id="{{ $idDialog }}-judul" class="text-base font-semibold text-navy-900">{{ $judul }}</h2>
                <p id="{{ $idDialog }}-pesan" class="mt-2 text-sm leading-relaxed text-slate-600">{{ $pesan }}</p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-ui.tombol tipe="button" varian="sekunder" x-ref="tombolBatal" @click="tutup()">Batal</x-ui.tombol>

                    <form method="POST" action="{{ $aksi }}">
                        @csrf
                        @method($metode)
                        <x-ui.tombol :varian="$varianKonfirmasi" class="w-full sm:w-auto">{{ $labelKonfirmasi }}</x-ui.tombol>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
