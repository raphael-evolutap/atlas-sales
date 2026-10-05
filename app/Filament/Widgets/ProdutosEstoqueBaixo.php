<?php

namespace App\Filament\Widgets;

use App\Models\ProdutoEstoque;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ProdutosEstoqueBaixo extends TableWidget
{
    protected static ?string $heading = 'Produtos com estoque baixo (por cidade)';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ProdutoEstoque::query()

                ->whereRaw('quantidade <= estoque_minimo + 5')
                ->whereHas('produto', fn (Builder $query) => $query->where('ativo', true))
                ->orderBy('quantidade'))
            ->columns([
                TextColumn::make('produto.nome')
                    ->label('Produto'),
                TextColumn::make('produto.sku')
                    ->label('SKU'),
                TextColumn::make('cidade.nome')
                    ->label('Cidade'),
                TextColumn::make('quantidade')
                    ->label('Estoque')
                    ->numeric()
                    ->color('danger'),
                TextColumn::make('estoque_minimo')
                    ->label('Mínimo')
                    ->numeric(),
            ])
            ->paginated(false);
    }
}
