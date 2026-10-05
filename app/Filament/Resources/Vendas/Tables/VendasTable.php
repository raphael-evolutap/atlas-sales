<?php

namespace App\Filament\Resources\Vendas\Tables;

use App\Enums\StatusVenda;
use App\Models\Vendedor;
use App\Services\VendaService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_venda')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                TextColumn::make('cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendedor.user.name')
                    ->label('Vendedor')
                    ->sortable(),
                TextColumn::make('valor_total_int')
                    ->label('Total')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (StatusVenda $state) => ucfirst($state->value))
                    ->color(fn (StatusVenda $state) => match ($state) {
                        StatusVenda::Aberta => 'warning',
                        StatusVenda::Fechada => 'success',
                        StatusVenda::Cancelada => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status'),
                SelectFilter::make('vendedor')
                    ->label('Vendedor')
                    ->options(Vendedor::query()->with('user')->get()->mapWithKeys(
                        fn (Vendedor $vendedor) => [$vendedor->getKey() => $vendedor->user->name],
                    ))
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $value) => $query->where('vendedor_id', $value),
                        );
                    }),
            ])
            ->recordActions([
                Action::make('fechar')
                    ->label('Fechar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Fechar a venda baixa o estoque dos itens.')
                    ->action(fn ($record) => app(VendaService::class)->fechar($record))
                    ->visible(fn ($record) => $record->status === StatusVenda::Aberta),
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Cancelar a venda devolve os itens ao estoque.')
                    ->action(fn ($record) => app(VendaService::class)->cancelar($record))
                    ->visible(fn ($record) => $record->status === StatusVenda::Fechada),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status !== StatusVenda::Fechada),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
