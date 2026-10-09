<?php

namespace App\Filament\Resources\Vendas\Tables;

use App\Enums\StatusVenda;
use App\Filament\Resources\Vendas\Schemas\VendaForm;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\VendaService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class VendasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data_venda', 'desc')
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
                TextColumn::make('itens_sum_comissao_int')
                    ->label('Comissão')
                    ->sum('itens', 'comissao_int')
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
                ViewAction::make()
                    ->slideOver()
                    ->modalHeading(fn (Venda $record) => "Venda #{$record->getKey()}")
                    ->extraModalFooterActions(fn () => [
                        self::comprovantesAction(),
                        self::fecharAction(),
                        self::cancelarAction(),
                    ]),
                self::comprovantesAction(),
                self::fecharAction(),
                self::cancelarAction(),
                // A policy limita o vendedor a vendas abertas; fechada ninguém exclui.
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status !== StatusVenda::Fechada)
                    ->using(fn (Venda $record) => app(VendaService::class)->excluir($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords(fn (Venda $record) => $record->status !== StatusVenda::Fechada
                            && Gate::allows('delete', $record))
                        ->using(fn (Collection $records) => $records->each(
                            fn (Venda $venda) => app(VendaService::class)->excluir($venda),
                        )),
                ]),
            ]);
    }

    public static function comprovantesAction(): Action
    {
        return Action::make('comprovantes')
            ->label('Enviar Comprovantes')
            ->icon('heroicon-o-paper-clip')
            ->color('gray')
            ->modalHeading('Comprovantes de pagamento')
            ->modalSubmitActionLabel('Salvar')
            ->schema([VendaForm::comprovantes()])
            // Carrega os comprovantes já enviados; sem isso, salvar apagaria os anteriores.
            ->fillForm([])
            ->authorize('enviarComprovantes')
            ->action(fn () => Notification::make()->title('Comprovantes salvos')->success()->send());
    }

    public static function fecharAction(): Action
    {
        return Action::make('fechar')
            ->authorize('fechar')
            ->label('Fechar venda')
            ->icon('heroicon-o-lock-closed')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Fechar a venda confirma a baixa de estoque dos itens.')
            ->action(fn ($record) => app(VendaService::class)->fechar($record))
            ->visible(fn ($record) => $record->status === StatusVenda::Aberta);
    }

    public static function cancelarAction(): Action
    {
        return Action::make('cancelar')
            ->authorize('cancelar')
            ->label('Cancelar venda')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('Cancelar a venda devolve os itens ao estoque.')
            ->action(fn ($record) => app(VendaService::class)->cancelar($record))
            ->visible(fn ($record) => $record->status !== StatusVenda::Cancelada);
    }
}
