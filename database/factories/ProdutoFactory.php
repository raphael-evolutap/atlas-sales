<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use App\Models\Grupo;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fornecedor_id' => Fornecedor::factory(),
            'grupo_id' => Grupo::factory(),
            'nome' => $this->faker->words(2, true),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'unidade' => 'un',
            'preco_custo_int' => $this->faker->numberBetween(100, 5000),
            'preco_venda_int' => $this->faker->numberBetween(500, 10000),
            'estoque_qtd' => 0,
            'estoque_minimo' => 5,
            'ativo' => true,
        ];
    }

    /**
     * Produto com saldo de estoque (via movimentação de entrada).
     */
    public function comEstoque(int $quantidade = 100): static
    {
        return $this->afterCreating(function (Produto $produto) use ($quantidade) {
            $produto->movimentacoes()->create([
                'tipo' => 'entrada',
                'quantidade' => $quantidade,
                'motivo' => 'compra',
                'user_id' => User::factory()->create()->id,
            ]);
            $produto->update(['estoque_qtd' => $quantidade]);
        });
    }
}
