<?php

namespace Database\Factories;

use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProvinsiFactory extends Factory
{
    protected $model = Provinsi::class;

    public function definition(): array
    {
        return [
            'kode_provinsi' => str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT),
            'nama' => fake()->city(),
        ];
    }
}
