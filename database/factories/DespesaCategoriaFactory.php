<?php

namespace Database\Factories;

use App\Models\DespesaCategoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DespesaCategoria>
 */
class DespesaCategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->unique()->word(),
            'ativo' => true,
        ];
    }
}
