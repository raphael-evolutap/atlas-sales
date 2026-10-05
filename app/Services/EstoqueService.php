<?php

namespace App\Services;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\EstoqueInsuficienteException;
use App\Models\Cidade;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * Registra uma movimentação na cidade informada, atualiza o saldo da
     * cidade e o cache de total do produto em uma única transação.
     *
     * @throws EstoqueInsuficienteException
     */
    public function registrar(
        Produto $produto,
        Cidade $cidade,
        TipoMovimentacao $tipo,
        int $quantidade,
        MotivoMovimentacao $motivo,
        ?Venda $venda = null,
        ?string $observacoes = null,
        ?User $user = null,
        ?int $estoqueMinimo = null,
    ): Movimentacao {
        return DB::transaction(function () use ($produto, $cidade, $tipo, $quantidade, $motivo, $venda, $observacoes, $user, $estoqueMinimo) {
            $estoque = ProdutoEstoque::query()
                ->where('produto_id', $produto->getKey())
                ->where('cidade_id', $cidade->getKey())
                ->lockForUpdate()
                ->first();

            if (! $estoque) {
                $estoque = ProdutoEstoque::query()->create([
                    'produto_id' => $produto->getKey(),
                    'cidade_id' => $cidade->getKey(),
                    'quantidade' => 0,
                    'estoque_minimo' => $estoqueMinimo ?? 0,
                ]);
            }

            $delta = match ($tipo) {
                TipoMovimentacao::Entrada => abs($quantidade),
                TipoMovimentacao::Saida => -abs($quantidade),
                TipoMovimentacao::Ajuste => $quantidade,
            };

            if ($delta < 0 && $estoque->quantidade + $delta < 0) {
                throw new EstoqueInsuficienteException(
                    "{$produto->nome} ({$cidade->nome})",
                    $estoque->quantidade,
                    abs($delta),
                );
            }

            $estoque->increment('quantidade', $delta);
            $produto->increment('estoque_qtd', $delta);

            return $produto->movimentacoes()->create([
                'cidade_id' => $cidade->getKey(),
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'motivo' => $motivo,
                'venda_id' => $venda?->getKey(),
                'observacoes' => $observacoes,
                'user_id' => $user?->getKey() ?? auth()->id(),
            ]);
        });
    }
}
