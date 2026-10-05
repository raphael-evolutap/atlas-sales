<?php

namespace App\Models;

use Database\Factories\FornecedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nome', 'cnpj', 'contato_nome', 'email', 'telefone', 'endereco', 'cidade', 'uf', 'cep', 'observacoes', 'ativo'])]
#[Hidden([])]
class Fornecedor extends Model
{
    /** @use HasFactory<FornecedorFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'fornecedores';

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Produto, $this>
     */
    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    /**
     * @return HasMany<Despesa, $this>
     */
    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class);
    }
}
