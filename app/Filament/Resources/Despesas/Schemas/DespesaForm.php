<?php

namespace App\Filament\Resources\Despesas\Schemas;

use App\Support\Money;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money as FilamentPtbrFormFieldsMoney;

class DespesaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('despesa_categoria_id')
                    ->label('Categoria')
                    ->relationship('categoria', 'nome')
                    ->createOptionForm([
                        TextInput::make('nome')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Toggle::make('ativo')
                            ->default(true)
                            ->required(),
                    ])
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('descricao')
                    ->required()
                    ->maxLength(255),
                FilamentPtbrFormFieldsMoney::make('valor_int')
                    ->label('Valor')
                    ->prefix('R$')
                    ->formatStateUsing(fn ($state) => Money::toView($state))
                    ->dehydrateStateUsing(fn ($state) => Money::fromView($state))
                    ->required(),
                DatePicker::make('data')
                    ->required()
                    ->default(now()),
                Select::make('fornecedor_id')
                    ->label('Fornecedor')
                    ->relationship('fornecedor', 'nome')
                    ->searchable()
                    ->preload(),
                Toggle::make('pago')
                    ->live()
                    ->default(false),
                DatePicker::make('data_pagamento')
                    ->visible(fn (callable $get) => (bool) $get('pago')),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
