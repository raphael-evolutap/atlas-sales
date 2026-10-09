<?php

namespace App\Models;

use Database\Factories\ProdutoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['fornecedor_id', 'grupo_id', 'nome', 'sku', 'unidade', 'preco_custo_int', 'preco_venda_int', 'comissao_pct', 'estoque_qtd', 'estoque_minimo', 'ativo'])]
#[Hidden([])]
class Produto extends Model
{
    /** @use HasFactory<ProdutoFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'estoque_qtd' => 0,
    ];

    protected function casts(): array
    {
        return [
            'preco_custo_int' => 'integer',
            'preco_venda_int' => 'integer',
            'comissao_pct' => 'decimal:2',
            'estoque_qtd' => 'integer',
            'estoque_minimo' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Fornecedor, $this>
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    /**
     * @return BelongsTo<Grupo, $this>
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * @return HasMany<Movimentacao, $this>
     */
    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class);
    }

    /**
     * @return HasMany<ProdutoEstoque, $this>
     */
    public function estoques(): HasMany
    {
        return $this->hasMany(ProdutoEstoque::class);
    }
}
