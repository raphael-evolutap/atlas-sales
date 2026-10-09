<?php

namespace App\Filament\Widgets;

use App\Enums\StatusVenda;
use App\Filament\Widgets\Concerns\EscopoVendedor;
use App\Models\Cliente;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class VendasPorCliente extends TableWidget
{
    use EscopoVendedor;
    use HasWidgetShield;

    protected static ?string $heading = 'Vendas por cliente';

    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Cliente::query()
                ->select('clientes.*')
                ->selectRaw('count(vendas.id) as vendas_qtd')
                ->selectRaw('coalesce(sum(case when vendas.status = ? then vendas.valor_total_int else 0 end), 0) as total_int', [StatusVenda::Fechada->value])
                ->join('vendas', 'vendas.cliente_id', '=', 'clientes.id')
                ->whereIn('vendas.status', [StatusVenda::Fechada->value, StatusVenda::Aberta->value])
                ->when(! $this->isAdmin(), function (Builder $query) {
                    $vendedorId = auth()->user()?->vendedor?->getKey();

                    return $query->where('vendas.vendedor_id', $vendedorId);
                })
                ->groupBy('clientes.id', 'clientes.grupo_id', 'clientes.nome', 'clientes.email', 'clientes.telefone', 'clientes.cpf_cnpj', 'clientes.endereco', 'clientes.cidade', 'clientes.uf', 'clientes.cep', 'clientes.observacoes', 'clientes.ativo', 'clientes.created_at', 'clientes.updated_at', 'clientes.deleted_at')
                ->orderByDesc('total_int'))
            ->columns([
                TextColumn::make('nome')
                    ->label('Cliente'),
                TextColumn::make('grupo.nome')
                    ->label('Grupo')
                    ->badge(),
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
