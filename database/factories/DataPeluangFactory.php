<?php

namespace Database\Factories;

use App\Models\DataPeluang;
use App\Models\Konsumen;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class DataPeluangFactory extends Factory
{
    protected $model = DataPeluang::class;

    public function definition(): array
    {
        return [
            'peluang' => fake()->sentence(4),
            'id_konsumen' => Konsumen::factory(),
            'kontak_person' => fake()->name(),
            'no_hp' => fake()->phoneNumber(),
            'lokasi' => fake()->city(),
            'tgl_peluang' => Carbon::now()->subDays(rand(1, 30)),
            'target_peluang' => Carbon::now()->addDays(rand(30, 90)),
            'biaya_peluang' => fake()->randomNumber(8),
            'pagu_peluang' => fake()->randomNumber(8),
            'status' => 'N',
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'I',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'D',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'C',
        ]);
    }

    public function withoutKonsumen(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_konsumen' => null,
        ]);
    }
}
