<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\ProdutoLote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdutoLote>
 */
class ProdutoLoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'produto_id' => Produto::factory(),
            'numero' => $this->faker->unique()->bothify('L####-??'),
        ];
    }
}
