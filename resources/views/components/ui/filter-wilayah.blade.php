@props([
    'opsiKabupaten' => [],
    'kabupaten' => '',
    'kecamatan' => '',
    'desa' => '',
])

{{-- Tiga pilihan wilayah bertingkat untuk panel filter (§7): kabupaten/kota →
     kecamatan → desa/kelurahan. Kecamatan dan desa diambil lewat endpoint JSON
     internal begitu induknya dipilih, karena memuat 77 kecamatan dan 729 desa
     sekaligus membuat daftar sulit ditelusuri.

     Desa nonaktif sengaja tetap ditawarkan di sini: riwayat lama yang menyebut
     wilayah tersebut masih harus bisa dicari.

     `class="contents"` dipakai supaya ketiga kolom tetap mengikuti grid milik
     panel filter, bukan membentuk kotak sendiri. --}}
<div class="contents"
     x-data="{
        kabupatenId: @js((string) $kabupaten),
        kecamatanId: @js((string) $kecamatan),
        desaId: @js((string) $desa),
        kecamatans: [],
        desas: [],
        init() {
            if (this.kabupatenId) this.muatKecamatan(false);
            if (this.kecamatanId) this.muatDesa(false);
        },
        async muatKecamatan(kosongkan = true) {
            const pilihan = kosongkan ? '' : this.kecamatanId;

            this.kecamatans = this.kabupatenId
                ? await this.ambil(@js(route('options.kecamatan')) + '?kabupaten_id=' + this.kabupatenId)
                : [];

            this.$nextTick(() => { this.kecamatanId = pilihan; });

            if (kosongkan) { this.desas = []; this.desaId = ''; }
        },
        async muatDesa(kosongkan = true) {
            const pilihan = kosongkan ? '' : this.desaId;

            this.desas = this.kecamatanId
                ? await this.ambil(@js(route('options.desa')) + '?kecamatan_id=' + this.kecamatanId)
                : [];

            this.$nextTick(() => { this.desaId = pilihan; });
        },
        async ambil(alamat) {
            try {
                const respons = await fetch(alamat, { headers: { Accept: 'application/json' } });

                return respons.ok ? await respons.json() : [];
            } catch (galat) {
                return [];
            }
        },
     }">

    <div>
        <label for="kabupaten_id" class="mb-1.5 block text-xs font-medium text-slate-500">Kabupaten/Kota</label>

        <select name="kabupaten_id" id="kabupaten_id" x-model="kabupatenId" @change="muatKecamatan()"
                class="block h-11 w-full rounded-lg border-slate-300 py-0 text-base text-navy-900 shadow-kartu sm:text-sm
                       transition-colors focus:border-air-500 focus:ring-1 focus:ring-air-500">
            <option value="">Semua kabupaten/kota</option>
            @foreach ($opsiKabupaten as $id => $nama)
                <option value="{{ $id }}">{{ $nama }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="kecamatan_id" class="mb-1.5 block text-xs font-medium text-slate-500">Kecamatan</label>

        <select name="kecamatan_id" id="kecamatan_id" x-model="kecamatanId" @change="muatDesa()"
                :disabled="! kabupatenId"
                class="block h-11 w-full rounded-lg border-slate-300 py-0 text-base text-navy-900 shadow-kartu sm:text-sm
                       transition-colors focus:border-air-500 focus:ring-1 focus:ring-air-500
                       disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">Semua kecamatan</option>
            <template x-for="kecamatan in kecamatans" :key="kecamatan.id">
                <option :value="kecamatan.id" x-text="kecamatan.nama"></option>
            </template>
        </select>
    </div>

    <div>
        <label for="desa_id" class="mb-1.5 block text-xs font-medium text-slate-500">Desa/Kelurahan</label>

        <select name="desa_id" id="desa_id" x-model="desaId" :disabled="! kecamatanId"
                class="block h-11 w-full rounded-lg border-slate-300 py-0 text-base text-navy-900 shadow-kartu sm:text-sm
                       transition-colors focus:border-air-500 focus:ring-1 focus:ring-air-500
                       disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">Semua desa/kelurahan</option>
            <template x-for="desa in desas" :key="desa.id">
                <option :value="desa.id" x-text="desa.nama + (desa.aktif ? '' : ' (nonaktif)')"></option>
            </template>
        </select>
    </div>
</div>
