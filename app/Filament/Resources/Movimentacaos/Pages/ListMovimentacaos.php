<?php

namespace App\Filament\Resources\Movimentacaos\Pages;

use App\Filament\Resources\Movimentacaos\MovimentacaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMovimentacaos extends ListRecords
{
    protected static string $resource = MovimentacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
