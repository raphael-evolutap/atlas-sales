<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Models\Vendedor;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class FaturamentoVendedor extends TableWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Faturamento por vendedor';

    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Vendedor::query()
                ->select('vendedores.*')
                ->selectRaw('count(vendas.id) as vendas_qtd')
                ->selectRaw('coalesce(sum(case when vendas.status = ? then vendas.valor_total_int else 0 end), 0) as total_int', [StatusVenda::Fechada->value])
                ->selectSub(fn ($query) => $query
                    ->from('venda_items')
                    ->join('vendas as vendas_comissao', 'vendas_comissao.id', '=', 'venda_items.venda_id')
                    ->whereColumn('vendas_comissao.vendedor_id', 'vendedores.id')
                    ->where('vendas_comissao.status', StatusVenda::Fechada->value)
                    ->whereNull('vendas_comissao.deleted_at')
                    ->selectRaw('coalesce(sum(venda_items.comissao_int), 0)'), 'comissao_int')
                ->join('vendas', 'vendas.vendedor_id', '=', 'vendedores.id')
                ->where('vendas.status', '!=', StatusVenda::Cancelada->value)
                ->groupBy('vendedores.id', 'vendedores.user_id', 'vendedores.telefone', 'vendedores.ativo', 'vendedores.created_at', 'vendedores.updated_at')
                ->orderByDesc('total_int'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Vendedor'),
                TextColumn::make('vendas_qtd')
                    ->label('Vendas')
                    ->numeric(),
                TextColumn::make('total_int')
                    ->label('Total fechado')
                    ->money('BRL', divideBy: 100),
                TextColumn::make('comissao_int')
                    ->label('Comissão')
                    ->money('BRL', divideBy: 100),
            ])
            ->paginated(false);
    }
}
