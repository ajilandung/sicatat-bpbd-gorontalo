@php
    $ubah = isset($penyaluran);
    $duplikat = session('duplikat', []);

    // Instansi disiapkan sebagai daftar sederhana agar dapat diolah Alpine.
    // Isian yang baru saja gagal divalidasi didahulukan supaya pilihan admin
    // tidak hilang saat form ditampilkan ulang.
    $opsiInstansi = $daftarInstansi
        ->map(fn ($instansi) => [
            'id' => $instansi->id,
            'nama' => $instansi->nama,
            'aktif' => (bool) $instansi->aktif,
        ])
        ->values();

    $idInstansiTerpilih = collect(old('instansi_id', $ubah ? $penyaluran->instansis->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $instansiTerpilih = $opsiInstansi
        ->whereIn('id', $idInstansiTerpilih)
        ->values();
@endphp

{{-- Form input kegiatan penyaluran (FR-08 sampai FR-14).

     Dua hal membuat form ini berbeda dari form master data:

     1. Penerima berupa beberapa desa sekaligus. Laporan lapangan kerap menulis
        satu angka gabungan untuk beberapa desa, jadi desa dipilih dengan
        centang dan admin boleh berpindah kecamatan tanpa kehilangan pilihan
        sebelumnya (§7).
     2. Kegiatan serupa pada tanggal yang sama tidak dilarang, hanya
        dikonfirmasi. Satu desa memang bisa menerima lebih dari satu kegiatan
        dalam sehari, dan data nyata menunjukkannya. --}}
<div x-data="{
        kabupatenId: '',
        kecamatanId: '',
        kecamatans: [],
        desas: [],
        terpilih: @js($desaTerpilih),
        daftarKabupaten: @js(collect($opsiKabupaten)->map(fn ($nama, $id) => ['id' => (string) $id, 'nama' => $nama])->values()),
        konfirmasiDuplikat: @js($duplikat !== []),

        instansiId: '',
        daftarInstansi: @js($opsiInstansi),
        instansiTerpilih: @js($instansiTerpilih),

        async muatKecamatan() {
            this.kecamatanId = '';
            this.desas = [];

            this.kecamatans = this.kabupatenId
                ? await this.ambil(@js(route('options.kecamatan')) + '?kabupaten_id=' + this.kabupatenId)
                : [];
        },

        async muatDesa() {
            this.desas = this.kecamatanId
                ? await this.ambil(@js(route('options.desa')) + '?kecamatan_id=' + this.kecamatanId + '&hanya_aktif=1')
                : [];
        },

        async ambil(alamat) {
            try {
                const respons = await fetch(alamat, { headers: { Accept: 'application/json' } });

                return respons.ok ? await respons.json() : [];
            } catch (galat) {
                return [];
            }
        },

        namaWilayah() {
            const kecamatan = this.kecamatans.find((k) => String(k.id) === String(this.kecamatanId));
            const kabupaten = this.daftarKabupaten.find((k) => String(k.id) === String(this.kabupatenId));

            return [kecamatan ? 'Kec. ' + kecamatan.nama : null, kabupaten ? kabupaten.nama : null]
                .filter(Boolean).join(', ');
        },

        sudahDipilih(id) {
            return this.terpilih.some((desa) => String(desa.id) === String(id));
        },

        ubahPilihan(desa) {
            this.sudahDipilih(desa.id)
                ? this.hapus(desa.id)
                : this.terpilih.push({ id: desa.id, nama: desa.nama, wilayah: this.namaWilayah() });
        },

        hapus(id) {
            this.terpilih = this.terpilih.filter((desa) => String(desa.id) !== String(id));
        },

        instansiSudahDipilih(id) {
            return this.instansiTerpilih.some((instansi) => String(instansi.id) === String(id));
        },

        instansiTersisa() {
            return this.daftarInstansi.filter((instansi) => ! this.instansiSudahDipilih(instansi.id));
        },

        tambahInstansi() {
            const dipilih = this.daftarInstansi.find((instansi) => String(instansi.id) === String(this.instansiId));

            if (dipilih && ! this.instansiSudahDipilih(dipilih.id)) {
                this.instansiTerpilih.push(dipilih);
            }

            // Dikosongkan lagi supaya dropdown kembali ke ajakan memilih,
            // bukan menampilkan instansi yang barusan ditambahkan.
            this.instansiId = '';
        },

        hapusInstansi(id) {
            this.instansiTerpilih = this.instansiTerpilih.filter((instansi) => String(instansi.id) !== String(id));
        },

        lanjutSimpan() {
            this.$refs.konfirmasi.value = '1';
            this.$refs.form.submit();
        },
     }">

    <x-ui.ringkasan-galat class="mb-6"/>

    <form method="POST" action="{{ $aksi }}" x-ref="form" class="space-y-6">
        @csrf
        @if ($ubah)
            @method('PUT')
        @endif

        {{-- Diisi hanya setelah admin menyetujui peringatan kegiatan serupa. --}}
        <input type="hidden" name="konfirmasi_duplikat" x-ref="konfirmasi" value="">

        <x-ui.kartu judul="Waktu dan Wilayah"
                    deskripsi="Isi tanggal kegiatan berlangsung di lapangan, bukan tanggal data ini dimasukkan. Laporan susulan untuk tanggal yang sudah lewat memang boleh dan seharusnya dimasukkan apa adanya.">
            <div class="space-y-6">
                <x-ui.kolom nama="tanggal_penyaluran" label="Tanggal Penyaluran" wajib
                            petunjuk="Urutannya hari/bulan/tahun. Boleh tanggal yang sudah lewat — yang tidak diperbolehkan hanya tanggal di masa depan."
                            class="max-w-xs">
                    <x-ui.tanggal nama="tanggal_penyaluran" wajib
                                  :nilai="$ubah ? $penyaluran->tanggal_penyaluran?->format('Y-m-d') : ''"
                                  :max="now()->format('Y-m-d')"/>
                </x-ui.kolom>

                <div class="space-y-2">
                    <p class="block text-sm font-medium text-navy-800">
                        Desa/Kelurahan Penerima
                        <span class="text-red-600" aria-hidden="true">*</span>
                        <span class="sr-only">wajib diisi</span>
                    </p>

                    <p class="text-xs leading-relaxed text-slate-500">
                        Pilih kabupaten dan kecamatan lebih dulu, lalu centang desa penerimanya. Satu kegiatan boleh
                        mencakup beberapa desa dari kecamatan yang berbeda — pilihan sebelumnya tidak akan hilang.
                    </p>

                    <div class="rounded-xl border border-tepi bg-permukaan p-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="pemilih_kabupaten" class="mb-1.5 block text-xs font-medium text-slate-500">
                                    Kabupaten/Kota
                                </label>

                                <select id="pemilih_kabupaten" x-model="kabupatenId" @change="muatKecamatan()"
                                        class="block h-11 w-full rounded-lg border-slate-300 bg-white py-0 text-base
                                               text-navy-900 shadow-kartu transition-colors focus:border-air-500 sm:text-sm
                                               focus:ring-1 focus:ring-air-500">
                                    <option value="">Pilih kabupaten/kota</option>
                                    @foreach ($opsiKabupaten as $id => $nama)
                                        <option value="{{ $id }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="pemilih_kecamatan" class="mb-1.5 block text-xs font-medium text-slate-500">
                                    Kecamatan
                                </label>

                                <select id="pemilih_kecamatan" x-model="kecamatanId" @change="muatDesa()"
                                        :disabled="! kabupatenId"
                                        class="block h-11 w-full rounded-lg border-slate-300 bg-white py-0 text-base
                                               text-navy-900 shadow-kartu transition-colors focus:border-air-500 sm:text-sm
                                               focus:ring-1 focus:ring-air-500 disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">Pilih kecamatan</option>
                                    <template x-for="kecamatan in kecamatans" :key="kecamatan.id">
                                        <option :value="kecamatan.id" x-text="kecamatan.nama"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Daftar centang desa pada kecamatan yang sedang dibuka. --}}
                        <div class="mt-3" x-show="kecamatanId" x-cloak>
                            <div class="max-h-56 overflow-y-auto rounded-lg border border-tepi bg-white p-1">
                                <template x-for="desa in desas" :key="desa.id">
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm
                                                  text-navy-900 transition-colors hover:bg-permukaan">
                                        <input type="checkbox" :value="desa.id" :checked="sudahDipilih(desa.id)"
                                               @change="ubahPilihan(desa)"
                                               class="size-4 rounded border-slate-300 text-air-700 focus:ring-air-500">
                                        <span x-text="desa.nama"></span>
                                    </label>
                                </template>

                                <p x-show="desas.length === 0" class="px-3 py-4 text-center text-sm text-slate-400">
                                    Tidak ada desa/kelurahan aktif pada kecamatan ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Desa yang sudah dipilih, lengkap dengan input tersembunyi
                         yang benar-benar dikirim ke server. --}}
                    <div class="mt-3">
                        <p class="text-xs font-medium text-slate-500">
                            Terpilih: <span x-text="terpilih.length"></span> desa/kelurahan
                        </p>

                        <div class="mt-2 flex flex-wrap gap-2" x-show="terpilih.length > 0" x-cloak>
                            <template x-for="desa in terpilih" :key="desa.id">
                                <span class="inline-flex items-center gap-2 rounded-full bg-air-50 py-1 pl-3 pr-1
                                             text-xs font-medium text-air-800 ring-1 ring-inset ring-air-600/20">
                                    <input type="hidden" name="desa_id[]" :value="desa.id">

                                    <span>
                                        <span x-text="desa.nama"></span>
                                        <span class="font-normal text-air-700/70" x-text="desa.wilayah ? '· ' + desa.wilayah : ''"></span>
                                    </span>

                                    <button type="button" @click="hapus(desa.id)"
                                            class="flex size-5 items-center justify-center rounded-full text-air-700
                                                   transition-colors hover:bg-air-100 hover:text-air-900
                                                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500">
                                        <x-ikon nama="silang" class="size-3.5"/>
                                        <span class="sr-only">Hapus dari daftar terpilih</span>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <p x-show="terpilih.length === 0" class="mt-2 text-sm text-slate-400">
                            Belum ada desa/kelurahan yang dipilih.
                        </p>
                    </div>

                    @error('desa_id')
                        <p class="flex items-start gap-1.5 text-sm text-red-700">
                            <x-ikon nama="galat" class="mt-0.5 size-4 shrink-0"/>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>
        </x-ui.kartu>

        <x-ui.kartu judul="Instansi Pelaksana"
                    deskripsi="Satu kegiatan kerap dikerjakan beberapa instansi bersama-sama, misalnya BPBD Provinsi bersama Polsek dan PDAM. Centang semua yang terlibat.">
            @if ($daftarInstansi->isEmpty())
                <x-ui.notifikasi jenis="peringatan">
                    Belum ada instansi aktif. Tambahkan lebih dulu di
                    <a href="{{ route('instansi.index') }}" class="font-semibold underline">Data Instansi</a>.
                </x-ui.notifikasi>
            @else
                {{-- Dipilih lewat dropdown, sama seperti panel filter pada halaman
                     riwayat. Bedanya, di sini pilihan tidak menggantikan pilihan
                     sebelumnya melainkan menambahkannya ke daftar di bawah, karena
                     satu kegiatan boleh dikerjakan beberapa instansi sekaligus. --}}
                <div class="max-w-md">
                    <label for="pemilih_instansi" class="sr-only">Tambahkan instansi pelaksana</label>

                    <select id="pemilih_instansi" x-model="instansiId" @change="tambahInstansi()"
                            :disabled="instansiTersisa().length === 0"
                            class="block h-11 w-full rounded-lg border-slate-300 py-0 text-base text-navy-900
                                   shadow-kartu transition-colors focus:border-air-500 focus:ring-1 sm:text-sm
                                   focus:ring-air-500 disabled:bg-slate-100 disabled:text-slate-400">
                        <option value=""
                                x-text="instansiTersisa().length === 0
                                    ? 'Semua instansi sudah dipilih'
                                    : 'Pilih instansi pelaksana'"></option>

                        <template x-for="instansi in instansiTersisa()" :key="instansi.id">
                            <option :value="instansi.id"
                                    x-text="instansi.nama + (instansi.aktif ? '' : ' (nonaktif)')"></option>
                        </template>
                    </select>
                </div>

                <div class="mt-3">
                    <p class="text-xs font-medium text-slate-500">
                        Terpilih: <span x-text="instansiTerpilih.length"></span> instansi
                    </p>

                    <div class="mt-2 flex flex-wrap gap-2" x-show="instansiTerpilih.length > 0" x-cloak>
                        <template x-for="instansi in instansiTerpilih" :key="instansi.id">
                            <span class="inline-flex items-center gap-2 rounded-full bg-air-50 py-1 pl-3 pr-1
                                         text-xs font-medium text-air-800 ring-1 ring-inset ring-air-600/20">
                                <input type="hidden" name="instansi_id[]" :value="instansi.id">

                                <span>
                                    <span x-text="instansi.nama"></span>
                                    <span class="font-normal text-amber-700" x-show="! instansi.aktif"
                                          x-cloak>· nonaktif</span>
                                </span>

                                <button type="button" @click="hapusInstansi(instansi.id)"
                                        class="flex size-5 items-center justify-center rounded-full text-air-700
                                               transition-colors hover:bg-air-100 hover:text-air-900
                                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500">
                                    <x-ikon nama="silang" class="size-3.5"/>
                                    <span class="sr-only">Hapus dari daftar pelaksana</span>
                                </button>
                            </span>
                        </template>
                    </div>

                    <p x-show="instansiTerpilih.length === 0" class="mt-2 text-sm text-slate-400">
                        Belum ada instansi pelaksana yang dipilih.
                    </p>
                </div>
            @endif

            @error('instansi_id')
                <p class="mt-3 flex items-start gap-1.5 text-sm text-red-700">
                    <x-ikon nama="galat" class="mt-0.5 size-4 shrink-0"/>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </x-ui.kartu>

        <x-ui.kartu judul="Jumlah Bantuan"
                    deskripsi="Angka di bawah ini berlaku untuk seluruh desa pada kegiatan ini, bukan per desa. Jumlah KK dan jiwa boleh dikosongkan bila laporan lapangan tidak mencantumkannya.">
            <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-ui.kolom nama="volume_liter" label="Jumlah Air Tersalur" wajib petunjuk="Dalam liter.">
                        <x-ui.input nama="volume_liter" tipe="number" min="1" required
                                    :nilai="$ubah ? $penyaluran->volume_liter : ''" placeholder="misalnya: 16000"/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="jumlah_kk" label="Jumlah KK Terdampak" petunjuk="Boleh dikosongkan.">
                        <x-ui.input nama="jumlah_kk" tipe="number" min="0"
                                    :nilai="$ubah ? $penyaluran->jumlah_kk : ''" placeholder="opsional"/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="jumlah_jiwa" label="Jumlah Jiwa Terdampak" petunjuk="Boleh dikosongkan.">
                        <x-ui.input nama="jumlah_jiwa" tipe="number" min="0"
                                    :nilai="$ubah ? $penyaluran->jumlah_jiwa : ''" placeholder="opsional"/>
                    </x-ui.kolom>
                </div>

                <x-ui.kolom nama="keterangan" label="Keterangan"
                            petunjuk="Catatan bebas, misalnya titik penyaluran yang bukan desa (sekolah, masjid) atau kendala di lapangan.">
                    <textarea name="keterangan" id="keterangan" rows="3"
                              class="block w-full rounded-lg border-slate-300 text-base text-navy-900 shadow-kartu sm:text-sm
                                     transition-colors placeholder:text-slate-400 focus:border-air-500 focus:ring-1
                                     focus:ring-air-500 {{ $errors->has('keterangan') ? 'border-red-400' : '' }}"
                              placeholder="opsional">{{ old('keterangan', $ubah ? $penyaluran->keterangan : '') }}</textarea>
                </x-ui.kolom>
            </div>

            <x-slot:kaki>
                <x-ui.tombol varian="sekunder" :href="$batal">Batal</x-ui.tombol>
                <x-ui.tombol>{{ $ubah ? 'Simpan Perubahan' : 'Simpan' }}</x-ui.tombol>
            </x-slot:kaki>
        </x-ui.kartu>
    </form>

    {{-- Peringatan kegiatan serupa. Muncul setelah server menemukan kegiatan
         lain pada tanggal yang sama dengan desa yang beririsan. Admin tetap
         boleh melanjutkan — duplikat memang wajar terjadi di lapangan. --}}
    @if ($duplikat !== [])
        <div x-show="konfirmasiDuplikat" x-cloak class="relative z-50" role="dialog" aria-modal="true"
             aria-labelledby="duplikat-judul" @keydown.escape.window="konfirmasiDuplikat = false">
            <div x-show="konfirmasiDuplikat" x-transition.opacity
                 class="fixed inset-0 bg-navy-950/50 backdrop-blur-[1px]"></div>

            <div class="fixed inset-0 flex items-end justify-center p-4 sm:items-center">
                <div x-show="konfirmasiDuplikat" x-transition
                     class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 text-left shadow-naik">
                    <h2 id="duplikat-judul" class="text-base font-semibold text-navy-900">
                        Kegiatan serupa sudah tercatat
                    </h2>

                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Pada tanggal yang sama sudah ada kegiatan untuk desa yang Anda pilih. Periksa dulu daftar
                        berikut agar satu kegiatan tidak tercatat dua kali. Bila memang kegiatan yang berbeda,
                        data ini tetap boleh disimpan.
                    </p>

                    <ul class="mt-4 max-h-56 space-y-2 overflow-y-auto">
                        @foreach ($duplikat as $serupa)
                            <li class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm">
                                <p class="font-medium text-amber-900">
                                    {{ $serupa['tanggal'] }} · {{ $serupa['desa'] }}
                                </p>
                                <p class="mt-0.5 text-amber-800">
                                    {{ $serupa['volume'] }} · {{ $serupa['instansi'] ?: 'tanpa instansi' }}
                                </p>
                                <a href="{{ $serupa['url'] }}" target="_blank"
                                   class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-amber-900 underline">
                                    Lihat data yang sudah ada
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.tombol tipe="button" varian="sekunder" @click="konfirmasiDuplikat = false">
                            Batal, periksa dulu
                        </x-ui.tombol>

                        <x-ui.tombol tipe="button" @click="lanjutSimpan()">Lanjut simpan</x-ui.tombol>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
