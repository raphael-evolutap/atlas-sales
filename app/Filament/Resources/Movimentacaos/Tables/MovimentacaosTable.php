<?php

namespace App\Filament\Resources\Movimentacaos\Tables;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MovimentacaosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('produto.nome')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cidade.nome')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->formatStateUsing(fn (TipoMovimentacao $state) => ucfirst($state->value))
                    ->color(fn (TipoMovimentacao $state) => match ($state) {
                        TipoMovimentacao::Entrada => 'success',
                        TipoMovimentacao::Saida => 'danger',
                        TipoMovimentacao::Ajuste => 'warning',
                    }),
                TextColumn::make('quantidade')
                    ->numeric(),
                TextColumn::make('motivo')
                    ->badge()
                    ->formatStateUsing(fn (MotivoMovimentacao $state) => ucfirst($state->value)),
                TextColumn::make('venda_id')
                    ->label('Venda')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : '—'),
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->toggleable(),
                TextColumn::make('observacoes')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tipo'),
                SelectFilter::make('motivo'),
                SelectFilter::make('produto')
                    ->relationship('produto', 'nome')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('cidade')
                    ->relationship('cidade', 'nome')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
