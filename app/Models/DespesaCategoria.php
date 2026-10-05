<?php

namespace App\Models;

use Database\Factories\DespesaCategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'ativo'])]
#[Hidden([])]
class DespesaCategoria extends Model
{
    /** @use HasFactory<DespesaCategoriaFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Despesa, $this>
     */
    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class);
    }
}
