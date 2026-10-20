<?php

namespace Database\Factories;

use App\Models\ProdutoEstoqueLote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdutoEstoqueLote>
 */
class ProdutoEstoqueLoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'produto_estoque_id' => null,
            'lote_id' => null,
            'quantidade' => $this->faker->numberBetween(0, 100),
        ];
    }
}
