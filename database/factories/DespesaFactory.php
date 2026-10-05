<?php

namespace Database\Factories;

use App\Models\Despesa;
use App\Models\DespesaCategoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Despesa>
 */
class DespesaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'despesa_categoria_id' => DespesaCategoria::factory(),
            'descricao' => $this->faker->sentence(3),
            'valor_int' => $this->faker->numberBetween(1000, 500000),
            'data' => $this->faker->date(),
            'fornecedor_id' => null,
            'pago' => false,
            'data_pagamento' => null,
            'observacoes' => null,
        ];
    }

    /**
     * Despesa paga.
     */
    public function paga(): static
    {
        return $this->state(fn (array $attributes) => [
            'pago' => true,
            'data_pagamento' => $this->faker->date(),
        ]);
    }
}
