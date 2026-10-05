<?php

namespace App\Filament\Resources\Movimentacaos\Pages;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Filament\Resources\Movimentacaos\MovimentacaoResource;
use App\Models\Cidade;
use App\Models\Movimentacao;
use App\Models\Produto;
use App\Services\EstoqueService;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimentacao extends CreateRecord
{
    protected static string $resource = MovimentacaoResource::class;

    protected function handleRecordCreation(array $data): Movimentacao
    {
        return app(EstoqueService::class)->registrar(
            produto: Produto::findOrFail($data['produto_id']),
            cidade: Cidade::findOrFail($data['cidade_id']),
            tipo: TipoMovimentacao::from($data['tipo']),
            quantidade: (int) $data['quantidade'],
            motivo: MotivoMovimentacao::from($data['motivo']),
            observacoes: $data['observacoes'] ?? null,
        );
    }
}
