<?php

namespace Database\Factories;

use App\Models\Penyaluran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Penyaluran>
 */
class PenyaluranFactory extends Factory
{
    protected $model = Penyaluran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Tanggal kegiatan, bukan tanggal input: selalu hari ini atau
            // sebelumnya, persis seperti batas yang dipakai form.
            'tanggal_penyaluran' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'user_id' => User::factory()->admin(),
            'jumlah_kk' => fake()->numberBetween(20, 500),
            'jumlah_jiwa' => fake()->numberBetween(80, 2000),
            'volume_liter' => fake()->numberBetween(2000, 60000),
            'keterangan' => null,
        ];
    }

    /**
     * Kegiatan yang jumlah KK dan jiwanya tidak tercatat di laporan lapangan —
     * kondisi yang justru paling sering muncul pada dokumen asli.
     */
    public function tanpaJumlahWarga(): static
    {
        return $this->state(fn (array $attributes) => [
            'jumlah_kk' => null,
            'jumlah_jiwa' => null,
        ]);
    }

    public function padaTanggal(string $tanggal): static
    {
        return $this->state(fn (array $attributes) => [
            'tanggal_penyaluran' => $tanggal,
        ]);
    }

    public function terhapus(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
