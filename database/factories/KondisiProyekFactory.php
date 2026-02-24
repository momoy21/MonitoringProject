<?php

namespace Database\Factories;

use App\Models\KondisiProyek;
use Illuminate\Database\Eloquent\Factories\Factory;

class KondisiProyekFactory extends Factory
{
    protected $model = KondisiProyek::class;

    public function definition(): array
    {
        return [
            'id_kondisi_proyek' => $this->generateNextId(),
            'desc_kondisi_proyek' => fake()->sentence(4),
            'status' => 'A',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'N',
        ]);
    }

    private function generateNextId(): string
    {
        $lastRecord = KondisiProyek::orderBy('id_kondisi_proyek', 'desc')->first();
        
        if (!$lastRecord) {
            return 'K1';
        }
        
        $lastIdNumber = (int) str_replace('K', '', $lastRecord->id_kondisi_proyek);
        $nextIdNumber = $lastIdNumber + 1;
        
        return 'K' . $nextIdNumber;
    }
}
