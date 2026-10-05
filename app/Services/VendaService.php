<?php

namespace App\Services;

use App\Enums\MotivoMovimentacao;
use App\Enums\StatusVenda;
use App\Enums\TipoMovimentacao;
use App\Exceptions\AcaoInvalidaException;
use App\Exceptions\EstoqueInsuficienteException;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;

class VendaService
{
    public function __construct(private EstoqueService $estoque) {}

    /**
     * Cria a venda com itens (snapshot de preço e cidade) e calcula o total.
     *
     * @param  array<string, mixed>  $dados  cliente_id, vendedor_id, data_venda, desconto_int, observacoes, status
     * @param  array<int, array{produto_id: int, cidade_id: int, quantidade: int}>  $itens
     *
     * @throws EstoqueInsuficienteException quando status = fechada e saldo insuficiente
     */
    public function criar(array $dados, array $itens, ?User $usuario = null): Venda
    {
        return DB::transaction(function () use ($dados, $itens, $usuario) {
            $venda = Venda::create([
                ...$dados,
                'status' => $dados['status'] ?? StatusVenda::Aberta,
                'valor_total_int' => 0,
            ]);

            foreach ($itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $venda->itens()->create([
                    'produto_id' => $produto->getKey(),
                    'cidade_id' => $item['cidade_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unit_int' => $produto->preco_venda_int,
                    'subtotal_int' => $produto->preco_venda_int * $item['quantidade'],
                ]);
            }

            $this->recalcularTotal($venda);

            if ($venda->status === StatusVenda::Fechada) {
                $this->baixarEstoque($venda, $usuario);
            }

            return $venda->refresh();
        });
    }

    /**
     * Fecha a venda: valida saldo, baixa estoque e registra movimentações.
     *
     * @throws AcaoInvalidaException se a venda não estiver aberta
     * @throws EstoqueInsuficienteException
     */
    public function fechar(Venda $venda, ?User $usuario = null): Venda
    {
        if ($venda->status !== StatusVenda::Aberta) {
            throw new AcaoInvalidaException('Apenas vendas abertas podem ser fechadas.');
        }

        DB::transaction(function () use ($venda, $usuario) {
            $venda->update(['status' => StatusVenda::Fechada]);
            $this->baixarEstoque($venda, $usuario);
        });

        return $venda->refresh();
    }

    /**
     * Cancela uma venda fechada: devolve estoque e registra estorno.
     *
     * @throws AcaoInvalidaException se a venda não estiver fechada
     */
    public function cancelar(Venda $venda, ?User $usuario = null): Venda
    {
        if ($venda->status !== StatusVenda::Fechada) {
            throw new AcaoInvalidaException('Apenas vendas fechadas podem ser canceladas.');
        }

        DB::transaction(function () use ($venda, $usuario) {
            foreach ($venda->itens()->with(['produto', 'cidade'])->get() as $item) {
                $this->estoque->registrar(
                    produto: $item->produto,
                    cidade: $item->cidade,
                    tipo: TipoMovimentacao::Entrada,
                    quantidade: $item->quantidade,
                    motivo: MotivoMovimentacao::Estorno,
                    venda: $venda,
                    observacoes: "Estorno da venda #{$venda->getKey()}",
                    user: $usuario,
                );
            }

            $venda->update(['status' => StatusVenda::Cancelada]);
        });

        return $venda->refresh();
    }

    /**
     * @throws EstoqueInsuficienteException
     */
    private function baixarEstoque(Venda $venda, ?User $usuario): void
    {
        foreach ($venda->itens()->with(['produto', 'cidade'])->get() as $item) {
            $this->estoque->registrar(
                produto: $item->produto,
                cidade: $item->cidade,
                tipo: TipoMovimentacao::Saida,
                quantidade: $item->quantidade,
                motivo: MotivoMovimentacao::Venda,
                venda: $venda,
                user: $usuario,
            );
        }
    }

    private function recalcularTotal(Venda $venda): void
    {
        $subtotal = (int) $venda->itens()->sum('subtotal_int');
        $venda->update(['valor_total_int' => max(0, $subtotal - (int) $venda->desconto_int)]);
    }
}
