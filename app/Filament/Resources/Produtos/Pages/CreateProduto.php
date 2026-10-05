<?php

namespace App\Filament\Resources\Produtos\Pages;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Filament\Resources\Produtos\ProdutoResource;
use App\Models\Cidade;
use App\Models\Produto;
use App\Services\EstoqueService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduto extends CreateRecord
{
    protected static string $resource = ProdutoResource::class;

    protected function handleRecordCreation(array $data): Produto
    {
        $estoques = $data['estoques'] ?? [];
        unset($data['estoques']);

        $produto = static::getModel()::create($data);

        $estoqueService = app(EstoqueService::class);

        foreach ($estoques as $linha) {
            $estoqueService->registrar(
                produto: $produto,
                cidade: Cidade::findOrFail($linha['cidade_id']),
                tipo: TipoMovimentacao::Entrada,
                quantidade: (int) ($linha['quantidade'] ?? 0),
                motivo: MotivoMovimentacao::Compra,
                observacoes: 'Estoque inicial (cadastro do produto)',
                estoqueMinimo: (int) ($linha['estoque_minimo'] ?? 0),
            );
        }

        return $produto;
    }
}
