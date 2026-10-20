<?php

namespace Database\Factories;

use App\Models\ProdutoEstoque;
use App\Models\ProdutoLote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdutoEstoque>
 */
class ProdutoEstoqueFactory extends Factory
{
    /**
     * Lote que recebe a quantidade criada pela factory, quando nenhum outro
     * é informado com noLote(), para que o saldo da cidade seja a soma dos lotes.
     *
     * O saldo por lote só é criado em create(). Para mudar a quantidade depois,
     * use EstoqueService::registrar(), que mantém cidade, lote e produto juntos;
     * um update(['quantidade' => ...]) direto deixa os lotes defasados.
     */
    public const LOTE_PADRAO = 'LOTE-PADRAO';

    private ?string $numeroLote = null;

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

    /**
     * Coloca a quantidade criada no lote informado em vez de LOTE_PADRAO.
     */
    public function noLote(string $numero): static
    {
        $factory = clone $this;
        $factory->numeroLote = $numero;

        return $factory;
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ProdutoEstoque $estoque): void {
            if ($estoque->quantidade <= 0) {
                return;
            }

            $estoque->lotes()->create([
                'lote_id' => ProdutoLote::doProduto($estoque->produto_id, $this->numeroLote ?? self::LOTE_PADRAO)->getKey(),
                'quantidade' => $estoque->quantidade,
            ]);
        });
    }
}
