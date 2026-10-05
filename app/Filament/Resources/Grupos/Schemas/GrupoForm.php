<?php

namespace App\Filament\Resources\Grupos\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GrupoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('link')
                    ->url()
                    ->nullable(),
                Textarea::make('descricao')
                    ->columnSpanFull(),
                Toggle::make('ativo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
