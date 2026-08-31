import Alpine from 'alpinejs';
import { BarController, BarElement, CategoryScale, Chart, LinearScale, Tooltip } from 'chart.js';

// Hanya bagian Chart.js yang benar-benar dipakai grafik dashboard yang
// didaftarkan, supaya berkas yang diunduh peramban tidak membawa jenis grafik
// dan skala yang tidak pernah dipanggil.
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();
