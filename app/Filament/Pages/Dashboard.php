<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Livewire\Component;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected function getHeaderActions(): array
    {
        return [
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
}
