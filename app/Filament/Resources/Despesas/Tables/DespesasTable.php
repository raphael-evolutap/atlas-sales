<?php

namespace App\Filament\Resources\Despesas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DespesasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data')
                    ->date()
                    ->sortable(),
                TextColumn::make('descricao')
                    ->searchable(),
                TextColumn::make('categoria.nome')
                    ->label('Categoria')
                    ->badge()
                    ->sortable(),
                TextColumn::make('valor_int')
                    ->label('Valor')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),
                TextColumn::make('fornecedor.nome')
                    ->label('Fornecedor')
                    ->toggleable(),
                IconColumn::make('pago')
                    ->boolean(),
                TextColumn::make('data_pagamento')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->relationship('categoria', 'nome'),
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
