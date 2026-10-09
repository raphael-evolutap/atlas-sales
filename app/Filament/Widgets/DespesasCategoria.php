<?php

namespace App\Filament\Widgets;

use App\Models\Despesa;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class DespesasCategoria extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Despesas por categoria';

    protected static ?int $sort = 2;

    protected ?string $description = 'Todas as despesas registradas';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    protected function getData(): array
    {
        $dados = Despesa::query()
            ->join('despesa_categorias', 'despesa_categorias.id', '=', 'despesas.despesa_categoria_id')
            ->selectRaw('despesa_categorias.nome, sum(despesas.valor_int) as total')
            ->groupBy('despesa_categorias.nome')
            ->orderByDesc('total')
            ->get();

        $cores = static::cores(count($dados));

        return [
            'datasets' => [
                [
                    'label' => 'Despesas (R$)',
                    'data' => $dados->map(fn ($item) => $item->total / 100)->all(),
                    'backgroundColor' => $cores,
                    'borderColor' => $cores,
                ],
            ],
            'labels' => $dados->pluck('nome')->all(),
        ];
    }

    /**
     * Paleta fixa de cores por categoria; repete se houver mais
     * categorias do que cores.
     *
     * @return list<string>
     */
    private static function cores(int $quantidade): array
    {
        $paleta = [
            '#f97316', // laranja
            '#ef4444', // vermelho
            '#eab308', // amarelo
            '#22c55e', // verde
            '#3b82f6', // azul
            '#8b5cf6', // violeta
            '#ec4899', // rosa
            '#14b8a6', // turquesa
            '#f59e0b', // âmbar
            '#64748b', // cinza
        ];

        return array_map(
            fn (int $i) => $paleta[$i % count($paleta)],
            range(0, max(0, $quantidade - 1)),
        );
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
