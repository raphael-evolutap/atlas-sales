<?php

namespace App\Models;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use Database\Factories\MovimentacaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['produto_id', 'cidade_id', 'tipo', 'quantidade', 'motivo', 'venda_id', 'observacoes', 'user_id'])]
#[Hidden([])]
class Movimentacao extends Model
{
    /** @use HasFactory<MovimentacaoFactory> */
    use HasFactory;

    protected $table = 'movimentacoes';

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimentacao::class,
            'motivo' => MotivoMovimentacao::class,
            'quantidade' => 'integer',
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
     * @return BelongsTo<Venda, $this>
     */
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Cidade, $this>
     */
    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }
}
