<?php

namespace Database\Factories;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movimentacao>
 */
class MovimentacaoFactory extends Factory
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
            'tipo' => TipoMovimentacao::Entrada,
            'quantidade' => $this->faker->numberBetween(1, 50),
            'motivo' => MotivoMovimentacao::Compra,
            'venda_id' => null,
            'observacoes' => null,
            'user_id' => User::factory(),
        ];
    }
}
