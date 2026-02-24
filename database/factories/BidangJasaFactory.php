<?php

namespace Database\Factories;

use App\Models\BidangJasa;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidangJasaFactory extends Factory
{
    protected $model = BidangJasa::class;

    private static $sequence = 0;

    public function definition(): array
    {
        self::$sequence++;
        $id = 90 + self::$sequence;
        if ($id > 99) {
            self::$sequence = 1;
            $id = 90;
        }
        
        return [
            'id_bidjasa' => str_pad((string)$id, 2, '0', STR_PAD_LEFT),
            'desc_bidjasa' => fake()->words(3, true),
            'status' => 'A',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'N',
        ]);
    }

    public function sequenceId(string $id): static
    {
        return $this->state(fn (array $attributes) => [
            'id_bidjasa' => $id,
        ]);
    }
}
