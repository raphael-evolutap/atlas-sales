<?php

namespace App\Filament\Resources\DespesaCategorias\Pages;

use App\Filament\Resources\DespesaCategorias\DespesaCategoriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDespesaCategorias extends ListRecords
{
    protected static string $resource = DespesaCategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
