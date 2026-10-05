<?php

namespace App\Filament\Resources\DespesaCategorias\Pages;

use App\Filament\Resources\DespesaCategorias\DespesaCategoriaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDespesaCategoria extends EditRecord
{
    protected static string $resource = DespesaCategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
