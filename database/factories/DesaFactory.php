<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Desa>
 */
class DesaFactory extends Factory
{
    protected $model = Desa::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kecamatan_id' => Kecamatan::factory(),
            'kode' => fake()->unique()->numerify('75.##.##.####'),
            'nama' => fake()->unique()->streetName(),
            'jenis' => Desa::JENIS_DESA,
            'aktif' => true,
        ];
    }

    public function kelurahan(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis' => Desa::JENIS_KELURAHAN,
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktif' => false,
        ]);
    }
}
