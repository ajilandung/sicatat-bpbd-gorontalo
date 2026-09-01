@props(['nama', 'nilai' => null, 'max' => null, 'wajib' => false])

@php
    $galat = $errors->has($nama);

    // Nilai dari server selalu berformat baku (Y-m-d); yang diubah hanya
    // tampilannya di layar.
    $awal = (string) old($nama, $nilai);
@endphp

{{-- Kolom tanggal dengan urutan hari/bulan/tahun.

     Dibuat sendiri karena urutan pada `<input type="date">` ditentukan bahasa
     browser, bukan oleh kode halaman — atribut `lang="id"` pun diabaikan Chrome.
     Padahal dd/mm/yyyy adalah urutan yang dipakai seluruh dokumen kantor, dan
     salah baca tanggal pada data penyaluran berakibat langsung ke laporan.

     Yang dikirim ke server tetap format baku `Y-m-d` lewat input tersembunyi,
     sehingga validasi, filter, dan query tidak perlu berubah sama sekali.
     Pemilih tanggal bawaan browser tetap tersedia lewat tombol kalender. --}}
<div class="relative"
     x-data="{
        iso: @js($awal),
        tampil: '',
        galat: false,

        init() {
            this.tampil = this.keTampilan(this.iso);
        },

        keTampilan(iso) {
            const bagian = String(iso || '').split('-');

            return bagian.length === 3 ? bagian[2] + '/' + bagian[1] + '/' + bagian[0] : '';
        },

        /** Menyisipkan pemisah otomatis sambil admin mengetik. */
        saatKetik() {
            const angka = this.tampil.replace(/\D/g, '').slice(0, 8);

            this.tampil = [angka.slice(0, 2), angka.slice(2, 4), angka.slice(4, 8)]
                .filter((bagian) => bagian.length > 0)
                .join('/');

            this.perbarui();
        },

        perbarui() {
            const cocok = this.tampil.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

            if (! cocok) {
                this.iso = '';
                this.galat = false;

                return;
            }

            const [, hari, bulan, tahun] = cocok;
            const tanggal = new Date(tahun + '-' + bulan + '-' + hari + 'T00:00:00');

            // Pemeriksaan ulang hari dan bulan menangkap tanggal yang tidak
            // pernah ada, mis. 31/02/2026 yang oleh Date digeser ke bulan Maret.
            const sah = ! isNaN(tanggal)
                && tanggal.getMonth() + 1 === Number(bulan)
                && tanggal.getDate() === Number(hari);

            this.galat = ! sah;
            this.iso = sah ? tahun + '-' + bulan + '-' + hari : '';
        },

        dariKalender(nilai) {
            this.iso = nilai;
            this.tampil = this.keTampilan(nilai);
            this.galat = false;
        },

        bukaKalender() {
            const kalender = this.$refs.kalender;

            // `showPicker` tersedia di peramban modern; sisanya cukup difokuskan.
            try {
                kalender.showPicker();
            } catch (kendala) {
                kalender.focus();
                kalender.click();
            }
        },
     }">

    <input type="text"
           id="{{ $attributes->get('id', $nama) }}"
           :value="tampil"
           @input="tampil = $event.target.value; saatKetik()"
           @blur="perbarui()"
           inputmode="numeric"
           maxlength="10"
           autocomplete="off"
           placeholder="dd/mm/yyyy"
           @if ($wajib) required @endif
           @if ($galat) aria-invalid="true" aria-describedby="{{ $nama }}-galat" @endif
           :aria-invalid="galat ? 'true' : null"
           {{ $attributes->except('id')->merge([
               'class' => 'block h-11 w-full rounded-lg border-slate-300 pr-11 text-base text-navy-900 shadow-kartu sm:text-sm
                           transition-colors placeholder:text-slate-400 focus:border-air-500 focus:ring-1
                           focus:ring-air-500 disabled:bg-slate-100 disabled:text-slate-500'
                   .($galat ? ' border-red-400 focus:border-red-500 focus:ring-red-500' : ''),
           ]) }}
           :class="galat ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">

    {{-- Nilai yang benar-benar dikirim ke server. --}}
    <input type="hidden" name="{{ $nama }}" :value="iso">

    {{-- Pemilih tanggal bawaan browser. Tetap dirender (bukan `display:none`)
         supaya `showPicker()` boleh dipanggil, tetapi tidak terlihat dan tidak
         dapat diklik langsung — tombol di sebelahnyalah yang membukanya. --}}
    <input type="date"
           x-ref="kalender"
           tabindex="-1"
           aria-hidden="true"
           @if ($max) max="{{ $max }}" @endif
           :value="iso"
           @change="dariKalender($event.target.value)"
           class="pointer-events-none absolute bottom-0 right-3 size-px opacity-0">

    <button type="button" @click="bukaKalender()"
            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400
                   transition-colors hover:text-air-700 focus-visible:outline-none focus-visible:ring-2
                   focus-visible:ring-air-500">
        <x-ikon nama="kalender" class="size-[18px]"/>
        <span class="sr-only">Buka pemilih tanggal</span>
    </button>

    <p x-show="galat" x-cloak class="mt-2 flex items-start gap-1.5 text-sm text-red-700">
        <x-ikon nama="galat" class="mt-0.5 size-4 shrink-0"/>
        <span>Tanggal tidak ada pada kalender. Gunakan urutan hari/bulan/tahun.</span>
    </p>
</div>
