<?php

namespace Database\Factories;

use App\Models\ProdutoEstoque;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdutoEstoque>
 */
class ProdutoEstoqueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'produto_id' => null,
            'cidade_id' => null,
            'quantidade' => $this->faker->numberBetween(0, 100),
            'estoque_minimo' => $this->faker->numberBetween(0, 10),
        ];
    }
}
