<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Models\Produto;
use App\Models\VendaItem;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProdutos extends TableWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Produtos mais vendidos';

    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            // Agrega numa subquery por produto_id: assim o GROUP BY não precisa listar
            // as colunas de produtos (ONLY_FULL_GROUP_BY do MySQL) e não quebra quando surgem colunas novas.
            ->query(fn (): Builder => Produto::query()
                ->select('produtos.*', 'vendido.vendido_qtd', 'vendido.total_int')
                ->joinSub(
                    VendaItem::query()
                        ->join('vendas', 'vendas.id', '=', 'venda_items.venda_id')
                        ->where('vendas.status', StatusVenda::Fechada->value)
                        ->groupBy('venda_items.produto_id')
                        ->select('venda_items.produto_id')
                        ->selectRaw('coalesce(sum(venda_items.quantidade), 0) as vendido_qtd')
                        ->selectRaw('coalesce(sum(venda_items.subtotal_int), 0) as total_int'),
                    'vendido',
                    'vendido.produto_id',
                    '=',
                    'produtos.id',
                )
                ->orderByDesc('vendido.vendido_qtd'))
            ->columns([
                TextColumn::make('nome')
                    ->label('Produto'),
                TextColumn::make('sku')
                    ->label('SKU'),
                TextColumn::make('vendido_qtd')
                    ->label('Qtd vendida')
                    ->numeric(),
                TextColumn::make('total_int')
                    ->label('Total')
                    ->money('BRL', divideBy: 100),
            ])
            ->paginated(false);
    }
}
