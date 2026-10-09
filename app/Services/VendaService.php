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
     * Cria a venda com itens (snapshot de preço, comissão e cidade), calcula o total
     * e já baixa o estoque dos itens.
     *
     * @param  array<string, mixed>  $dados  cliente_id, vendedor_id, data_venda, desconto_int, observacoes, status
     * @param  array<int, array{produto_id: int, cidade_id: int, quantidade: int}>  $itens
     *
     * @throws EstoqueInsuficienteException
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
                $subtotal = $produto->preco_venda_int * $item['quantidade'];

                $venda->itens()->create([
                    'produto_id' => $produto->getKey(),
                    'cidade_id' => $item['cidade_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unit_int' => $produto->preco_venda_int,
                    'subtotal_int' => $subtotal,
                    'comissao_pct' => $produto->comissao_pct,
                    'comissao_int' => (int) round($subtotal * (float) $produto->comissao_pct / 100),
                ]);
            }

            $this->recalcularTotal($venda);
            $this->baixarEstoque($venda, $usuario);

            return $venda->refresh();
        });
    }

    /**
     * Fecha a venda, confirmando a baixa de estoque feita na criação.
     *
     * @throws AcaoInvalidaException se a venda não estiver aberta
     */
    public function fechar(Venda $venda): Venda
    {
        if ($venda->status !== StatusVenda::Aberta) {
            throw new AcaoInvalidaException('Apenas vendas abertas podem ser fechadas.');
        }

        $venda->update(['status' => StatusVenda::Fechada]);

        return $venda->refresh();
    }

    /**
     * Cancela uma venda aberta ou fechada: devolve estoque e registra estorno.
     *
     * @throws AcaoInvalidaException se a venda já estiver cancelada
     */
    public function cancelar(Venda $venda, ?User $usuario = null): Venda
    {
        if ($venda->status === StatusVenda::Cancelada) {
            throw new AcaoInvalidaException('A venda já está cancelada.');
        }

        DB::transaction(function () use ($venda, $usuario) {
            $this->devolverEstoque($venda, $usuario, "Estorno da venda #{$venda->getKey()}");

            $venda->update(['status' => StatusVenda::Cancelada]);
        });

        return $venda->refresh();
    }

    /**
     * Exclui a venda. Se ainda estiver aberta, devolve o estoque antes;
     * cancelada já teve o estoque devolvido.
     *
     * @throws AcaoInvalidaException se a venda estiver fechada
     */
    public function excluir(Venda $venda, ?User $usuario = null): void
    {
        if ($venda->status === StatusVenda::Fechada) {
            throw new AcaoInvalidaException('Vendas fechadas não podem ser excluídas.');
        }

        DB::transaction(function () use ($venda, $usuario) {
            if ($venda->status === StatusVenda::Aberta) {
                $this->devolverEstoque($venda, $usuario, "Estorno por exclusão da venda #{$venda->getKey()}");
            }

            $venda->delete();
        });
    }

    private function devolverEstoque(Venda $venda, ?User $usuario, string $observacoes): void
    {
        foreach ($venda->itens()->with(['produto', 'cidade'])->get() as $item) {
            $this->estoque->registrar(
                produto: $item->produto,
                cidade: $item->cidade,
                tipo: TipoMovimentacao::Entrada,
                quantidade: $item->quantidade,
                motivo: MotivoMovimentacao::Estorno,
                venda: $venda,
                observacoes: $observacoes,
                user: $usuario,
            );
        }
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
