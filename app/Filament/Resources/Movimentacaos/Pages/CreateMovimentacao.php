<?php

namespace App\Filament\Resources\Movimentacaos\Pages;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\EstoqueInsuficienteException;
use App\Filament\Resources\Movimentacaos\MovimentacaoResource;
use App\Models\Cidade;
use App\Models\Movimentacao;
use App\Models\ProdutoLote;
use App\Services\EstoqueService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateMovimentacao extends CreateRecord
{
    protected static string $resource = MovimentacaoResource::class;

    protected function handleRecordCreation(array $data): Movimentacao
    {
        $tipo = TipoMovimentacao::from($data['tipo']);

        // Lote novo só é criado junto com a movimentação: se ela falhar, nada fica para trás.
        try {
            return DB::transaction(fn () => app(EstoqueService::class)->registrar(
                lote: $tipo === TipoMovimentacao::Entrada
                    ? ProdutoLote::doProduto((int) $data['produto_id'], $data['lote_numero'])
                    : ProdutoLote::doProdutoOuFalha((int) $data['produto_id'], $data['lote_id']),
                cidade: Cidade::findOrFail($data['cidade_id']),
                tipo: $tipo,
                quantidade: (int) $data['quantidade'],
                motivo: MotivoMovimentacao::from($data['motivo']),
                observacoes: $data['observacoes'] ?? null,
            ));
        } catch (EstoqueInsuficienteException $e) {
            Notification::make()->title('Movimentação não registrada')->body($e->getMessage())->danger()->send();
            $this->halt();
        }
    }
}
