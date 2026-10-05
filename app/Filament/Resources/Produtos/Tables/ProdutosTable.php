<?php

namespace App\Filament\Resources\Produtos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProdutosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('fornecedor.nome')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('preco_custo_int')
                    ->label('Custo')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),
                TextColumn::make('preco_venda_int')
                    ->label('Venda')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),
                TextColumn::make('estoque_qtd')
                    ->label('Estoque')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->estoque_qtd <= $record->estoque_minimo ? 'danger' : 'success'),
                IconColumn::make('ativo')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
