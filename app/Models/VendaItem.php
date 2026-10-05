<?php

namespace App\Models;

use Database\Factories\VendaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['venda_id', 'produto_id', 'cidade_id', 'quantidade', 'preco_unit_int', 'subtotal_int'])]
#[Hidden([])]
class VendaItem extends Model
{
    /** @use HasFactory<VendaItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_unit_int' => 'integer',
            'subtotal_int' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Venda, $this>
     */
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
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
