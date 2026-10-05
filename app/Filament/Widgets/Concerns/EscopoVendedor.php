<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\StatusVenda;
use App\Models\Venda;
use Illuminate\Database\Eloquent\Builder;

trait EscopoVendedor
{
    /**
     * Query de vendas (excluindo canceladas) com escopo do vendedor logado.
     */
    protected function vendasQuery(): Builder
    {
        $user = auth()->user();

        $query = Venda::query()
            ->where('status', '!=', StatusVenda::Cancelada->value);

        if ($user && ! $user->hasRole('Admin') && $user->vendedor) {
            $query->where('vendedor_id', $user->vendedor->getKey());
        }

        return $query;
    }

    protected function ehAdmin(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('Admin');
    }
}
