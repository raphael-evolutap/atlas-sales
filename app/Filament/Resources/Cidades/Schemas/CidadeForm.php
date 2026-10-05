<?php

namespace App\Filament\Resources\Cidades\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CidadeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('uf')
                    ->label('UF')
                    ->maxLength(2),
                Toggle::make('ativo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
