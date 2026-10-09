<?php

namespace App\Filament\Resources\Vendas\Pages;

use App\Enums\StatusVenda;
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

        // Campos desabilitados para vendedor não são enviados: a venda é
        // sempre dele e entra aberta.
        $user = auth()->user();
        if ($user?->isVendedor()) {
            $data['status'] = StatusVenda::Aberta;

            if ($user->vendedor) {
                $data['vendedor_id'] = $user->vendedor->getKey();
            }
        }

        return app(VendaService::class)->criar($data, $itens);
    }
}
