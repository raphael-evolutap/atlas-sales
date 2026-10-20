<?php

namespace App\Models;

use Database\Factories\ProdutoEstoqueLoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Saldo de um lote dentro do estoque de uma cidade. A soma dos lotes
 * é mantida em ProdutoEstoque::quantidade.
 */
#[Fillable(['produto_estoque_id', 'lote_id', 'quantidade'])]
#[Hidden([])]
class ProdutoEstoqueLote extends Model
{
    /** @use HasFactory<ProdutoEstoqueLoteFactory> */
    use HasFactory;

    protected $attributes = [
        'quantidade' => 0,
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ProdutoEstoque, $this>
     */
    public function estoque(): BelongsTo
    {
        return $this->belongsTo(ProdutoEstoque::class, 'produto_estoque_id');
    }

    /**
     * @return BelongsTo<ProdutoLote, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(ProdutoLote::class, 'lote_id');
    }
}
