<?php

namespace Database\Factories;

use App\Models\Kota;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

class KotaFactory extends Factory
{
    protected $model = Kota::class;

    public function definition(): array
    {
        return [
            'kode_kota' => str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'nama' => fake()->city(),
            'provinsi_id' => Provinsi::factory(),
        ];
    }
}
