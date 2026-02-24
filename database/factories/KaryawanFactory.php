<?php

namespace Database\Factories;

use App\Models\Karyawan;
use Illuminate\Database\Eloquent\Factories\Factory;

class KaryawanFactory extends Factory
{
    protected $model = Karyawan::class;

    public function definition(): array
    {
        return [
            'nik' => $this->generateNik(),
            'nama' => fake()->name(),
            'status' => fake()->randomElement(['T', 'K', 'J']),
            'aktif' => 'Y',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktif' => 'T',
        ]);
    }

    public function tetap(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'T',
        ]);
    }

    public function kontrak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'K',
        ]);
    }

    public function jo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'J',
        ]);
    }

    private function generateNik(): string
    {
        $lastKaryawan = Karyawan::orderBy('nik', 'desc')->first();
        
        if (!$lastKaryawan) {
            return '001';
        }
        
        $lastNik = (int) $lastKaryawan->nik;
        $nextNik = $lastNik + 1;
        
        return str_pad($nextNik, 3, '0', STR_PAD_LEFT);
    }
}
