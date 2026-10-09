<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Models\Produto;
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
            ->query(fn (): Builder => Produto::query()
                ->select('produtos.*')
                ->selectRaw('coalesce(sum(venda_items.quantidade), 0) as vendido_qtd')
                ->selectRaw('coalesce(sum(venda_items.subtotal_int), 0) as total_int')
                ->join('venda_items', 'venda_items.produto_id', '=', 'produtos.id')
                ->join('vendas', 'vendas.id', '=', 'venda_items.venda_id')
                ->where('vendas.status', StatusVenda::Fechada->value)
                ->groupBy('produtos.id', 'produtos.fornecedor_id', 'produtos.grupo_id', 'produtos.nome', 'produtos.sku', 'produtos.unidade', 'produtos.preco_custo_int', 'produtos.preco_venda_int', 'produtos.estoque_qtd', 'produtos.estoque_minimo', 'produtos.ativo', 'produtos.created_at', 'produtos.updated_at', 'produtos.deleted_at')
                ->orderByDesc('vendido_qtd'))
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
