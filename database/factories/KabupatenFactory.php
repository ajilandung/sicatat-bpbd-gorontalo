<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kabupaten>
 */
class KabupatenFactory extends Factory
{
    protected $model = Kabupaten::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->numerify('75.##'),
            'nama' => fake()->unique()->city(),
            'jenis' => Kabupaten::JENIS_KABUPATEN,
        ];
    }

    public function kota(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis' => Kabupaten::JENIS_KOTA,
        ]);
    }
}
