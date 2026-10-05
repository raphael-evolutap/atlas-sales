<?php

namespace App\Models;

use Database\Factories\ProdutoEstoqueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['produto_id', 'cidade_id', 'quantidade', 'estoque_minimo'])]
#[Hidden([])]
class ProdutoEstoque extends Model
{
    /** @use HasFactory<ProdutoEstoqueFactory> */
    use HasFactory;

    protected $attributes = [
        'quantidade' => 0,
        'estoque_minimo' => 0,
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'estoque_minimo' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Produto, $this>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * @return BelongsTo<Cidade, $this>
     */
    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }
}
