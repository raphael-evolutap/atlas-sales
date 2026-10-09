<?php

namespace App\Filament\Resources\Movimentacaos\Schemas;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MovimentacaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('produto_id')
                    ->relationship('produto', 'nome')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('cidade_id')
                    ->label('Cidade')
                    ->relationship('cidade', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('uf')
                            ->label('UF')
                            ->maxLength(2),
                    ]),
                Select::make('tipo')
                    ->options(collect(TipoMovimentacao::cases())->mapWithKeys(fn ($case) => [$case->value => ucfirst($case->value)]))
                    ->required(),
                Select::make('motivo')
                    ->options(collect(MotivoMovimentacao::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))
                    ->required(),
                TextInput::make('quantidade')
                    ->numeric()
                    ->required()
                    ->helperText('Negativo apenas para ajustes (ex.: -3).'),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
