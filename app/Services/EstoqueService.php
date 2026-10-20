<?php

namespace App\Services;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\AcaoInvalidaException;
use App\Exceptions\EstoqueInsuficienteException;
use App\Models\Cidade;
use App\Models\Movimentacao;
use App\Models\ProdutoEstoque;
use App\Models\ProdutoEstoqueLote;
use App\Models\ProdutoLote;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * Registra uma movimentação do lote na cidade informada e atualiza, em uma
     * única transação, o saldo do lote, o saldo da cidade e o total do produto.
     *
     * @throws EstoqueInsuficienteException
     */
    public function registrar(
        ProdutoLote $lote,
        Cidade $cidade,
        TipoMovimentacao $tipo,
        int $quantidade,
        MotivoMovimentacao $motivo,
        ?Venda $venda = null,
        ?string $observacoes = null,
        ?User $user = null,
        ?int $estoqueMinimo = null,
    ): Movimentacao {
        return DB::transaction(function () use ($lote, $cidade, $tipo, $quantidade, $motivo, $venda, $observacoes, $user, $estoqueMinimo) {
            $produto = $lote->produto;
            $estoque = $this->estoqueDaCidade($lote, $cidade, $estoqueMinimo);
            $saldoLote = $this->saldoDoLote($estoque, $lote);
            $delta = $this->delta($tipo, $quantidade);

            if ($delta < 0 && $saldoLote->quantidade + $delta < 0) {
                throw new EstoqueInsuficienteException(
                    "{$produto->nome} ({$cidade->nome}, lote {$lote->numero})",
                    $saldoLote->quantidade,
                    abs($delta),
                );
            }

            $saldoLote->increment('quantidade', $delta);
            $estoque->increment('quantidade', $delta);
            $produto->increment('estoque_qtd', $delta);

            return $produto->movimentacoes()->create([
                'cidade_id' => $cidade->getKey(),
                'lote_id' => $lote->getKey(),
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'motivo' => $motivo,
                'venda_id' => $venda?->getKey(),
                'observacoes' => $observacoes,
                'user_id' => $user?->getKey() ?? auth()->id(),
            ]);
        });
    }

    /**
     * Transfere saldo de um lote entre cidades: saída na origem e entrada no
     * destino, no mesmo lote e na mesma transação. O total do produto não muda.
     *
     * @return array{saida: Movimentacao, entrada: Movimentacao}
     *
     * @throws AcaoInvalidaException
     * @throws EstoqueInsuficienteException
     */
    public function transferir(
        ProdutoLote $lote,
        Cidade $origem,
        Cidade $destino,
        int $quantidade,
        ?string $observacoes = null,
        ?User $user = null,
    ): array {
        if ($origem->is($destino)) {
            throw new AcaoInvalidaException('A cidade de destino deve ser diferente da origem.');
        }

        if ($quantidade <= 0) {
            throw new AcaoInvalidaException('A quantidade transferida deve ser maior que zero.');
        }

        $log = "Transferência de {$origem->nome} para {$destino->nome}"
            .(filled($observacoes) ? ": {$observacoes}" : '');

        return DB::transaction(fn () => [
            'saida' => $this->registrar($lote, $origem, TipoMovimentacao::Saida, $quantidade, MotivoMovimentacao::Transferencia, observacoes: $log, user: $user),
            'entrada' => $this->registrar($lote, $destino, TipoMovimentacao::Entrada, $quantidade, MotivoMovimentacao::Transferencia, observacoes: $log, user: $user),
        ]);
    }

    private function estoqueDaCidade(ProdutoLote $lote, Cidade $cidade, ?int $estoqueMinimo): ProdutoEstoque
    {
        $estoque = ProdutoEstoque::query()
            ->where('produto_id', $lote->produto_id)
            ->where('cidade_id', $cidade->getKey())
            ->lockForUpdate()
            ->first();

        return $estoque ?? ProdutoEstoque::query()->create([
            'produto_id' => $lote->produto_id,
            'cidade_id' => $cidade->getKey(),
            'quantidade' => 0,
            'estoque_minimo' => $estoqueMinimo ?? 0,
        ]);
    }

    private function saldoDoLote(ProdutoEstoque $estoque, ProdutoLote $lote): ProdutoEstoqueLote
    {
        $saldo = $estoque->lotes()
            ->where('lote_id', $lote->getKey())
            ->lockForUpdate()
            ->first();

        return $saldo ?? $estoque->lotes()->create([
            'lote_id' => $lote->getKey(),
            'quantidade' => 0,
        ]);
    }

    private function delta(TipoMovimentacao $tipo, int $quantidade): int
    {
        return match ($tipo) {
            TipoMovimentacao::Entrada => abs($quantidade),
            TipoMovimentacao::Saida => -abs($quantidade),
            TipoMovimentacao::Ajuste => $quantidade,
        };
    }
}
