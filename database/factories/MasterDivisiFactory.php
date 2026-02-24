<?php

namespace Database\Factories;

use App\Models\MasterDivisi;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterDivisiFactory extends Factory
{
    protected $model = MasterDivisi::class;

    public function definition(): array
    {
        return [
            'kode_divisi' => strtoupper(fake()->unique()->lexify('DIV-??')),
            'nama_divisi' => fake()->words(2, true),
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
