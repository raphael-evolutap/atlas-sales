<?php

namespace App\Models;

use Database\Factories\DespesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['despesa_categoria_id', 'descricao', 'valor_int', 'data', 'fornecedor_id', 'pago', 'data_pagamento', 'observacoes'])]
#[Hidden([])]
class Despesa extends Model
{
    /** @use HasFactory<DespesaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'valor_int' => 'integer',
            'data' => 'date',
            'pago' => 'boolean',
            'data_pagamento' => 'date',
        ];
    }

    /**
     * @return BelongsTo<DespesaCategoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(DespesaCategoria::class, 'despesa_categoria_id');
    }

    /**
     * @return BelongsTo<Fornecedor, $this>
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
