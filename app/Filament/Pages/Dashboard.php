<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Clientes\ClienteResource;
use App\Filament\Resources\Despesas\DespesaResource;
use App\Filament\Resources\Fornecedors\FornecedorResource;
use App\Filament\Resources\Movimentacaos\MovimentacaoResource;
use App\Filament\Resources\Produtos\ProdutoResource;
use App\Filament\Resources\Vendas\VendaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected function getHeaderActions(): array
    {
        return [
            self::atalhoCriar(VendaResource::class, 'Nova venda'),
            ActionGroup::make([
                self::atalhoCriar(ClienteResource::class, 'Novo cliente'),
                self::atalhoCriar(ProdutoResource::class, 'Novo produto'),
                self::atalhoCriar(MovimentacaoResource::class, 'Nova movimentação'),
                self::atalhoCriar(DespesaResource::class, 'Nova despesa'),
                self::atalhoCriar(FornecedorResource::class, 'Novo fornecedor'),
            ])
                ->label('Cadastrar')
                ->icon(Heroicon::OutlinedPlus)
                ->button()
                ->color('gray'),
            FilterAction::make()
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Data inicial'),
                    DatePicker::make('endDate')
                        ->label('Data final'),
                ])
                ->extraModalFooterActions(fn (): array => [
                    Action::make('limparFiltros')
                        ->label('Limpar filtros')
                        ->color('gray')
                        ->action(fn (Component $livewire) => $livewire->reset()),
                ]),
        ];
    }

    /**
     * @param  class-string<\Filament\Resources\Resource>  $resource
     */
    private static function atalhoCriar(string $resource, string $label): Action
    {
        return Action::make('criar'.class_basename($resource))
            ->label($label)
            ->icon($resource::getNavigationIcon())
            ->url(fn () => $resource::getUrl('create'))
            ->visible(fn () => $resource::canCreate());
    }
}
