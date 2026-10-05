<?php

namespace App\Filament\Resources\Vendas\Pages;

use App\Filament\Resources\Vendas\VendaResource;
use App\Models\Venda;
use App\Services\VendaService;
use Filament\Resources\Pages\CreateRecord;

class CreateVenda extends CreateRecord
{
    protected static string $resource = VendaResource::class;

    protected function handleRecordCreation(array $data): Venda
    {
        $itens = collect($data['itens'] ?? [])
            ->map(fn (array $item) => [
                'produto_id' => (int) $item['produto_id'],
                'cidade_id' => (int) $item['cidade_id'],
                'quantidade' => (int) $item['quantidade'],
            ])
            ->all();
        unset($data['itens']);

        return app(VendaService::class)->criar($data, $itens);
    }
}
