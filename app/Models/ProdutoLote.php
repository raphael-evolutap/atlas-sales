<?php

namespace App\Models;

use Database\Factories\ProdutoLoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['produto_id', 'numero'])]
#[Hidden([])]
class ProdutoLote extends Model
{
    /** @use HasFactory<ProdutoLoteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'produto_id' => 'integer',
        ];
    }

    /**
     * Lote do produto pelo id; falha se o lote for de outro produto.
     */
    public static function doProdutoOuFalha(Produto|int $produto, mixed $loteId): self
    {
        return self::query()
            ->where('produto_id', $produto instanceof Produto ? $produto->getKey() : $produto)
            ->findOrFail($loteId);
    }

    /**
     * Busca o lote do produto pelo número, criando-o se ainda não existir.
     */
    public static function doProduto(Produto|int $produto, string $numero): self
    {
        return self::query()->firstOrCreate([
            'produto_id' => $produto instanceof Produto ? $produto->getKey() : $produto,
            'numero' => trim($numero),
        ]);
    }

    /**
     * @return BelongsTo<Produto, $this>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * @return HasMany<ProdutoEstoqueLote, $this>
     */
    public function saldos(): HasMany
    {
        return $this->hasMany(ProdutoEstoqueLote::class, 'lote_id');
    }
}
