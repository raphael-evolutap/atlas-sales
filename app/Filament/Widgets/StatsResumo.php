<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Filament\Widgets\Concerns\EscopoVendedor;
use App\Models\Despesa;
use App\Models\ProdutoEstoque;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsResumo extends BaseWidget
{
    use EscopoVendedor;

    protected ?string $heading = 'Resumo';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $inicioMes = now()->startOfMonth();

        $vendasMes = (int) $this->vendasQuery()
            ->where('status', StatusVenda::Fechada->value)
            ->where('data_venda', '>=', $inicioMes)
            ->sum('valor_total_int');

        $vendasAbertas = $this->vendasQuery()
            ->where('status', StatusVenda::Aberta->value)
            ->count();

        $produtosEmFalta = ProdutoEstoque::query()
            ->whereColumn('quantidade', '<=', 'estoque_minimo')
            ->whereHas('produto', fn ($query) => $query->where('ativo', true))
            ->count();

        $stats = [
            Stat::make('Vendas do mês', Number::currency($vendasMes / 100, 'BRL'))
                ->description('Vendas fechadas desde '.$inicioMes->format('d/m'))
                ->color('success'),
            Stat::make('Vendas abertas', $vendasAbertas)
                ->description('Aguardando fechamento')
                ->color('warning'),
            Stat::make('Produtos em falta', $produtosEmFalta)
                ->description('Estoque abaixo do mínimo')
                ->color($produtosEmFalta > 0 ? 'danger' : 'success'),
        ];

        if ($this->ehAdmin()) {
            $aPagar = (int) Despesa::query()->where('pago', false)->sum('valor_int');

            $stats[] = Stat::make('Despesas a pagar', Number::currency($aPagar / 100, 'BRL'))
                ->description('Total pendente')
                ->color($aPagar > 0 ? 'danger' : 'success');
        }

        return $stats;
    }
}
