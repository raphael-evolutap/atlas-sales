<?php

namespace App\Models;

use App\Enums\StatusVenda;
use Database\Factories\VendaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['cliente_id', 'vendedor_id', 'data_venda', 'status', 'valor_total_int', 'desconto_int', 'observacoes'])]
#[Hidden([])]
class Venda extends Model
{
    /** @use HasFactory<VendaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'data_venda' => 'date',
            'status' => StatusVenda::class,
            'valor_total_int' => 'integer',
            'desconto_int' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsTo<Vendedor, $this>
     */
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    /**
     * @return HasMany<VendaItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    /**
     * @return HasMany<Movimentacao, $this>
     */
    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class);
    }
}
