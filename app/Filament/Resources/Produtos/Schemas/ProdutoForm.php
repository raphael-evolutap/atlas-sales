<?php

namespace App\Filament\Resources\Produtos\Schemas;

use App\Models\Cidade;
use App\Models\Fornecedor;
use App\Models\Grupo;
use App\Support\Money;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money as FilamentPtbrFormFieldsMoney;

class ProdutoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('fornecedor_id')
                    ->label('Fornecedor')
                    ->options(Fornecedor::query()->orderBy('nome')->pluck('nome', 'id'))
                    ->searchable(),
                Select::make('grupo_id')
                    ->label('Grupo')
                    ->options(Grupo::query()->orderBy('nome')->pluck('nome', 'id'))
                    ->searchable(),
                TextInput::make('unidade')
                    ->required()
                    ->default('un')
                    ->maxLength(10),
                FilamentPtbrFormFieldsMoney::make('preco_custo_int')
                    ->label('Preço de custo')
                    ->prefix('R$')
                    ->formatStateUsing(fn ($state) => Money::toView($state))
                    ->dehydrateStateUsing(fn ($state) => Money::fromView($state))
                    ->required(),
                FilamentPtbrFormFieldsMoney::make('preco_venda_int')
                    ->label('Preço de venda')
                    ->prefix('R$')
                    ->formatStateUsing(fn ($state) => Money::toView($state))
                    ->dehydrateStateUsing(fn ($state) => Money::fromView($state))
                    ->required(),
                TextInput::make('comissao_pct')
                    ->label('Comissão do vendedor')
                    ->suffix('%')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->helperText('Percentual sobre o subtotal do item vendido.'),
                TextInput::make('estoque_minimo')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('ativo')
                    ->default(true)
                    ->required(),
                Repeater::make('estoques')
                    ->label('Estoque por cidade')
                    ->schema([
                        Select::make('cidade_id')
                            ->label('Cidade')
                            ->options(Cidade::query()->where('ativo', true)->orderBy('nome')->pluck('nome', 'id'))
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
                            ])
                            ->createOptionUsing(fn (array $data): int => Cidade::create($data)->getKey()),
                        TextInput::make('quantidade')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('estoque_minimo')
                            ->label('Mínimo')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->addActionLabel('Adicionar cidade')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->columnSpanFull(),
            ]);
    }
}
