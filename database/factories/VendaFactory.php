<?php

namespace Database\Factories;

use App\Enums\StatusVenda;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\VendaService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venda>
 */
class VendaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'vendedor_id' => Vendedor::factory(),
            'data_venda' => $this->faker->date(),
            'status' => StatusVenda::Aberta,
            'valor_total_int' => 0,
            'desconto_int' => 0,
            'observacoes' => null,
        ];
    }

    /**
     * Venda fechada com itens e baixa de estoque.
     */
    public function fechada(): static
    {
        return $this->afterCreating(function (Venda $venda) {
            app(VendaService::class)->fechar($venda);
        });
    }
}
