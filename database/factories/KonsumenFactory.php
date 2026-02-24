<?php

namespace Database\Factories;

use App\Models\Konsumen;
use Illuminate\Database\Eloquent\Factories\Factory;

class KonsumenFactory extends Factory
{
    protected $model = Konsumen::class;

    public function definition(): array
    {
        return [
            'konsumen' => fake()->company(),
            'provinsi_id' => null,
            'kota_id' => null,
            'alamat1' => fake()->address(),
            'alamat2' => null,
            'kode_pos' => fake()->postcode(),
            'telp_kantor' => fake()->phoneNumber(),
            'fax' => null,
            'email' => fake()->companyEmail(),
            'status' => 'A',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'N',
        ]);
    }

    public function withLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'provinsi_id' => \App\Models\Provinsi::factory(),
            'kota_id' => \App\Models\Kota::factory(),
        ]);
    }
}
