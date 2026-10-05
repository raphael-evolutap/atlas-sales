<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Models\Vendedor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class FaturamentoVendedor extends TableWidget
{
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
                ->join('vendas', 'vendas.vendedor_id', '=', 'vendedores.id')
                ->where('vendas.status', '!=', StatusVenda::Cancelada->value)
                ->groupBy('vendedores.id', 'vendedores.user_id', 'vendedores.telefone', 'vendedores.comissao_pct', 'vendedores.ativo', 'vendedores.created_at', 'vendedores.updated_at')
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
            ])
            ->paginated(false);
    }
}
