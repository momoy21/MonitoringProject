<?php

namespace Database\Factories;

use App\Models\SpesifikasiRAB;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpesifikasiRABFactory extends Factory
{
    protected $model = SpesifikasiRAB::class;

    public function definition(): array
    {
        return [
            'id_spec' => $this->generateNextId(),
            'spec_rab' => fake()->sentence(3),
            'norutspec' => rand(1, 10),
            'kategori' => fake()->randomElement(['PDP', 'HPP']),
            'status' => 'A',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'N',
        ]);
    }

    public function pendapatan(): static
    {
        return $this->state(fn (array $attributes) => [
            'kategori' => 'PDP',
        ]);
    }

    public function hpp(): static
    {
        return $this->state(fn (array $attributes) => [
            'kategori' => 'HPP',
        ]);
    }

    private function generateNextId(): string
    {
        $lastSpec = SpesifikasiRAB::orderBy('id_spec', 'desc')->first();
        
        if (!$lastSpec) {
            return '0001';
        }
        
        $lastNumber = intval($lastSpec->id_spec);
        $nextNumber = $lastNumber + 1;
        
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
