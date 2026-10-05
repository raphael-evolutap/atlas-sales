<?php

namespace App\Models;

use Database\Factories\CidadeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'uf', 'ativo'])]
#[Hidden([])]
class Cidade extends Model
{
    /** @use HasFactory<CidadeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProdutoEstoque, $this>
     */
    public function estoques(): HasMany
    {
        return $this->hasMany(ProdutoEstoque::class);
    }
}
