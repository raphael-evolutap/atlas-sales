<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Filament\Widgets\Concerns\EscopoVendedor;
use App\Models\Despesa;
use App\Models\ProdutoEstoque;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class StatsResumo extends BaseWidget
{
    use EscopoVendedor;
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected ?string $heading = 'Resumo';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        [$inicio, $fim] = $this->periodo();
        $temFiltro = $this->temFiltroDeData();
        $descricaoPeriodo = $temFiltro
            ? $inicio->format('d/m/Y').' a '.$fim->format('d/m/Y')
            : 'desde '.$inicio->format('d/m');

        $vendasPeriodo = (int) $this->vendasFechadasNoPeriodo($inicio, $fim)
            ->sum('valor_total_int');

        $vendasAbertas = $this->vendasQuery()
            ->where('status', StatusVenda::Aberta->value)
            ->count();

        $produtosEmFalta = ProdutoEstoque::query()
            ->whereColumn('quantidade', '<=', 'estoque_minimo')
            ->whereHas('produto', fn ($query) => $query->where('ativo', true))
            ->count();

        $stats = [
            Stat::make($temFiltro ? 'Vendas no período' : 'Vendas do mês', Number::currency($vendasPeriodo / 100, 'BRL'))
                ->description('Vendas fechadas '.$descricaoPeriodo)
                ->color('success'),
            Stat::make('Vendas abertas', $vendasAbertas)
                ->description('Aguardando fechamento')
                ->color('warning'),
            Stat::make('Produtos em falta', $produtosEmFalta)
                ->description('Estoque abaixo do mínimo')
                ->color($produtosEmFalta > 0 ? 'danger' : 'success'),
        ];

        if ($this->isAdmin()) {
            $aPagar = (int) Despesa::query()->where('pago', false)->sum('valor_int');

            $stats[] = Stat::make('Despesas a pagar', Number::currency($aPagar / 100, 'BRL'))
                ->description('Total pendente')
                ->color($aPagar > 0 ? 'danger' : 'success');
        }

        if ($this->isVendedor()) {
            $comissao = (int) $this->vendasFechadasNoPeriodo($inicio, $fim)
                ->withSum('itens', 'comissao_int')
                ->get()
                ->sum('itens_sum_comissao_int');

            $stats[] = Stat::make($temFiltro ? 'Comissão no período' : 'Comissão do mês', Number::currency($comissao / 100, 'BRL'))
                ->description('Comissão sobre vendas fechadas '.$descricaoPeriodo)
                ->color('success');
        }

        return $stats;
    }

    private function vendasFechadasNoPeriodo(Carbon $inicio, Carbon $fim): Builder
    {
        return $this->vendasQuery()
            ->where('status', StatusVenda::Fechada->value)
            ->whereBetween('data_venda', [$inicio->toDateString(), $fim->toDateString()]);
    }

    private function temFiltroDeData(): bool
    {
        return filled($this->pageFilters['startDate'] ?? null) || filled($this->pageFilters['endDate'] ?? null);
    }

    /**
     * Período do filtro do dashboard; sem filtro, o mês atual.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodo(): array
    {
        $inicio = filled($this->pageFilters['startDate'] ?? null)
            ? Carbon::parse($this->pageFilters['startDate'])
            : now()->startOfMonth();
        $fim = filled($this->pageFilters['endDate'] ?? null)
            ? Carbon::parse($this->pageFilters['endDate'])
            : now();

        return [$inicio->startOfDay(), $fim->endOfDay()];
    }
}
