<?php

namespace App\Models;

use Database\Factories\VendedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'telefone', 'comissao_pct', 'ativo'])]
#[Hidden(['comissao_pct'])]
class Vendedor extends Model
{
    /** @use HasFactory<VendedorFactory> */
    use HasFactory;

    protected $table = 'vendedores';

    protected function casts(): array
    {
        return [
            'comissao_pct' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Venda, $this>
     */
    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }
}
