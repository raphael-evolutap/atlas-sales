<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Filament\Widgets\Concerns\EscopoVendedor;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class VendasPorPeriodo extends ChartWidget
{
    use EscopoVendedor;
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected ?string $heading = 'Vendas por período';

    protected ?string $description = 'Valor fechado por dia (vendas não canceladas)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        [$inicio, $fim] = $this->periodo();

        $dados = $this->vendasQuery()
            ->where('status', StatusVenda::Fechada->value)
            ->whereBetween('data_venda', [$inicio->toDateString(), $fim->toDateString()])
            ->selectRaw('data_venda, sum(valor_total_int) as total')
            ->groupBy('data_venda')
            ->orderBy('data_venda')
            ->pluck('total', 'data_venda');

        $labels = [];
        $valores = [];

        for ($dia = $inicio->copy(); $dia->lte($fim); $dia->addDay()) {
            $chave = $dia->toDateString();
            $labels[] = $dia->format('d/m');
            $valores[] = ((int) ($dados[$chave] ?? 0)) / 100;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Vendas (R$)',
                    'data' => $valores,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodo(): array
    {
        $inicio = isset($this->pageFilters['startDate'])
            ? Carbon::parse($this->pageFilters['startDate'])
            : now()->subDays(29)->startOfDay();
        $fim = isset($this->pageFilters['endDate'])
            ? Carbon::parse($this->pageFilters['endDate'])
            : now()->endOfDay();

        return [$inicio->startOfDay(), $fim->endOfDay()];
    }
}
