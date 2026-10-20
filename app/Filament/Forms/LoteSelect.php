<?php

namespace App\Filament\Forms;

use App\Models\ProdutoEstoqueLote;
use App\Models\ProdutoLote;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;

/**
 * Campos de seleção de lote. Os closures recebem Get e devolvem o id do
 * produto (e da cidade) escolhidos no formulário.
 */
class LoteSelect
{
    /**
     * Número do lote para entrada de estoque: sugere os lotes do produto, mas
     * aceita um número novo. O lote só é criado por quem registra a entrada,
     * na mesma transação (ProdutoLote::doProduto), para não sobrar lote vazio
     * se o formulário for cancelado ou falhar.
     *
     * @param  Closure(Get): mixed  $produtoId
     */
    public static function paraEntrada(Closure $produtoId, string $nome = 'lote_numero'): TextInput
    {
        return TextInput::make($nome)
            ->label('Lote')
            ->datalist(fn (Get $get): array => blank($id = $produtoId($get))
                ? []
                : ProdutoLote::query()->where('produto_id', $id)->orderBy('numero')->pluck('numero')->all())
            ->required()
            ->maxLength(255)
            ->helperText('Escolha um lote existente ou digite um número novo.')
            ->disabled(fn (Get $get): bool => blank($produtoId($get)));
    }

    /**
     * Lote existente do produto para ajuste, com o saldo na cidade no rótulo.
     * Inclui lotes sem saldo na cidade, já que o ajuste pode ser positivo,
     * a menos que $apenasComSaldo diga o contrário (ex.: saída).
     *
     * @param  Closure(Get): mixed  $produtoId
     * @param  Closure(Get): mixed  $cidadeId
     * @param  (Closure(Get): bool)|null  $apenasComSaldo
     */
    public static function paraAjuste(Closure $produtoId, Closure $cidadeId, ?Closure $apenasComSaldo = null, string $nome = 'lote_id'): Select
    {
        return Select::make($nome)
            ->label('Lote')
            ->options(function (Get $get) use ($produtoId, $cidadeId, $apenasComSaldo): array {
                if (blank($id = $produtoId($get))) {
                    return [];
                }

                $saldos = self::saldos($id, $cidadeId($get))->pluck('quantidade', 'lote_id');

                return ProdutoLote::query()
                    ->where('produto_id', $id)
                    ->when($apenasComSaldo !== null && $apenasComSaldo($get), fn ($query) => $query->whereKey($saldos->keys()))
                    ->orderBy('numero')
                    ->pluck('numero', 'id')
                    ->map(fn (string $numero, int $loteId) => "{$numero} (saldo: ".($saldos[$loteId] ?? 0).')')
                    ->all();
            })
            ->required()
            ->searchable()
            ->live()
            ->disabled(fn (Get $get): bool => blank($produtoId($get)) || blank($cidadeId($get)));
    }

    /**
     * Lote com saldo na cidade, para saídas (venda, transferência).
     *
     * @param  Closure(Get): mixed  $produtoId
     * @param  Closure(Get): mixed  $cidadeId
     */
    public static function comSaldo(Closure $produtoId, Closure $cidadeId, string $nome = 'lote_id'): Select
    {
        return Select::make($nome)
            ->label('Lote')
            ->options(fn (Get $get): array => self::saldos($produtoId($get), $cidadeId($get))
                ->mapWithKeys(fn (ProdutoEstoqueLote $saldo) => [
                    $saldo->lote_id => "{$saldo->lote->numero} (saldo: {$saldo->quantidade})",
                ])
                ->all())
            ->required()
            ->searchable()
            ->live()
            ->disabled(fn (Get $get): bool => blank($produtoId($get)) || blank($cidadeId($get)));
    }

    /**
     * Saldo do lote na cidade, ou null se algum dos três não foi escolhido.
     * Lote sem saldo na cidade conta como 0.
     */
    public static function saldo(mixed $produtoId, mixed $cidadeId, mixed $loteId): ?int
    {
        if (blank($loteId)) {
            return null;
        }

        if (blank($produtoId) || blank($cidadeId)) {
            return null;
        }

        return self::saldos($produtoId, $cidadeId)->firstWhere('lote_id', (int) $loteId)?->quantidade ?? 0;
    }

    /**
     * Saldos positivos dos lotes na cidade. Memoizado por requisição (once), pois
     * opções, maxValue e helperText pedem o mesmo saldo a cada render.
     *
     * @return Collection<int, ProdutoEstoqueLote>
     */
    private static function saldos(mixed $produtoId, mixed $cidadeId): Collection
    {
        if (blank($produtoId) || blank($cidadeId)) {
            return collect();
        }

        return once(fn () => ProdutoEstoqueLote::query()
            ->where('quantidade', '>', 0)
            ->whereHas('estoque', fn ($query) => $query
                ->where('produto_id', $produtoId)
                ->where('cidade_id', $cidadeId))
            ->with('lote')
            ->orderBy(ProdutoLote::query()->select('numero')->whereColumn('produto_lotes.id', 'produto_estoque_lotes.lote_id'))
            ->get());
    }
}
