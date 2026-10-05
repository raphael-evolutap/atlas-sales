<?php

namespace App\Filament\Resources\Vendedors\Pages;

use App\Filament\Resources\Vendedors\VendedorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVendedors extends ListRecords
{
    protected static string $resource = VendedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
