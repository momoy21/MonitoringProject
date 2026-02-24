<?php

namespace Database\Factories;

use App\Models\SpecRabDetail;
use App\Models\SpesifikasiRAB;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecRabDetailFactory extends Factory
{
    protected $model = SpecRabDetail::class;

    public function definition(): array
    {
        return [
            'id_spec' => SpesifikasiRAB::factory(),
            'cost_element' => $this->generateCostElement(),
            'description_ce' => fake()->sentence(4),
            'status' => 'A',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'N',
        ]);
    }

    private function generateCostElement(): string
    {
        $lastDetail = SpecRabDetail::orderBy('cost_element', 'desc')->first();
        
        if (!$lastDetail) {
            return 'CE-0001';
        }
        
        $lastNumber = (int) str_replace('CE-', '', $lastDetail->cost_element);
        $nextNumber = $lastNumber + 1;
        
        return 'CE-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
