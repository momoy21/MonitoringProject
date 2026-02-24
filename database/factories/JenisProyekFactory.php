<?php

namespace Database\Factories;

use App\Models\JenisProyek;
use Illuminate\Database\Eloquent\Factories\Factory;

class JenisProyekFactory extends Factory
{
    protected $model = JenisProyek::class;

    public function definition(): array
    {
        return [
            'kode_jenis' => strtoupper(fake()->unique()->lexify('??')),
            'nama_jenis' => fake()->words(3, true),
            'status' => 'A',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'N',
        ]);
    }
}
