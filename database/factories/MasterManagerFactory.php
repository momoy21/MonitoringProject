<?php

namespace Database\Factories;

use App\Models\MasterManager;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterManagerFactory extends Factory
{
    protected $model = MasterManager::class;

    public function definition(): array
    {
        return [
            'nik' => $this->generateNik(),
            'nama' => fake()->name(),
            'status' => 'Aktif',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Nonaktif',
        ]);
    }

    private function generateNik(): string
    {
        $lastManager = MasterManager::orderBy('nik', 'desc')->first();
        
        if (!$lastManager) {
            return 'MGR001';
        }
        
        $lastNik = (int) str_replace('MGR', '', $lastManager->nik);
        $nextNik = $lastNik + 1;
        
        return 'MGR' . str_pad($nextNik, 3, '0', STR_PAD_LEFT);
    }
}
