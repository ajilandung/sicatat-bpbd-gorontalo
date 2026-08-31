@props(['data', 'judul' => 'Volume Air Tersalur per Bulan', 'deskripsi' => null])

@php
    $titik = collect($data)->values();
    $adaData = $titik->sum('total_liter') > 0;
@endphp

{{-- Grafik penyaluran air per bulan (FR-21).

     Batangnya mewakili total liter; jumlah kegiatan muncul pada keterangan
     saat kursor diarahkan, sehingga satu grafik sudah menjawab dua pertanyaan
     tanpa memadati halaman.

     Bulan tanpa kegiatan tetap digambar bernilai nol supaya jeda antar musim
     kemarau terlihat apa adanya, bukan tersamarkan oleh sumbu yang melompat. --}}
<x-ui.kartu :judul="$judul" :deskripsi="$deskripsi">
    @if ($adaData)
        <div class="h-72"
             x-data="{
                titik: @js($titik),
                grafik: null,

                init() {
                    this.grafik = new window.Chart(this.$refs.kanvas, {
                        type: 'bar',
                        data: {
                            labels: this.titik.map((bulan) => bulan.label),
                            datasets: [{
                                data: this.titik.map((bulan) => bulan.total_liter),
                                backgroundColor: '#0284c7',
                                hoverBackgroundColor: '#0369a1',
                                borderRadius: 4,
                                maxBarThickness: 48,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                tooltip: {
                                    backgroundColor: '#142639',
                                    padding: 12,
                                    displayColors: false,
                                    callbacks: {
                                        title: (isi) => this.titik[isi[0].dataIndex].judul,
                                        label: (isi) => [
                                            this.angka(isi.raw) + ' liter',
                                            this.angka(this.titik[isi.dataIndex].jumlah_kegiatan) + ' kegiatan',
                                        ],
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#64748b', font: { size: 11 } },
                                },
                                y: {
                                    beginAtZero: true,
                                    border: { display: false },
                                    grid: { color: '#e2e8f0' },
                                    ticks: {
                                        color: '#64748b',
                                        font: { size: 11 },
                                        maxTicksLimit: 6,
                                        callback: (nilai) => this.angka(nilai),
                                    },
                                },
                            },
                        },
                    });
                },

                /** Pemisah ribuan mengikuti kebiasaan penulisan Indonesia. */
                angka(nilai) {
                    return Number(nilai).toLocaleString('id-ID');
                },

                destroy() {
                    this.grafik?.destroy();
                },
             }">
            <canvas x-ref="kanvas" role="img"
                    aria-label="Diagram batang volume air tersalur per bulan selama {{ $titik->count() }} bulan terakhir."></canvas>
        </div>

        {{-- Tabel setara isi grafik, tersembunyi secara visual tetapi terbaca
             pembaca layar — diagram saja tidak menyampaikan angkanya. --}}
        <table class="sr-only">
            <caption>{{ $judul }}</caption>
            <thead>
                <tr>
                    <th scope="col">Bulan</th>
                    <th scope="col">Volume air (liter)</th>
                    <th scope="col">Jumlah kegiatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($titik as $bulan)
                    <tr>
                        <th scope="row">{{ $bulan['judul'] }}</th>
                        <td>{{ number_format($bulan['total_liter'], 0, ',', '.') }}</td>
                        <td>{{ $bulan['jumlah_kegiatan'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <x-ui.kosong ikon="tetesan" judul="Belum ada data untuk digambarkan"
                     deskripsi="Grafik muncul setelah ada kegiatan penyaluran yang tercatat pada 12 bulan terakhir."/>
    @endif
</x-ui.kartu>
