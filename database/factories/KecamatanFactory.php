<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kecamatan>
 */
class KecamatanFactory extends Factory
{
    protected $model = Kecamatan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kabupaten_id' => Kabupaten::factory(),
            'kode' => fake()->unique()->numerify('75.##.##'),
            'nama' => fake()->unique()->citySuffix().' '.fake()->unique()->lastName(),
        ];
    }
}
