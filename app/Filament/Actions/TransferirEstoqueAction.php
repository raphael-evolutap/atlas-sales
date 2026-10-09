<?php

namespace App\Filament\Actions;

use App\Exceptions\AcaoInvalidaException;
use App\Exceptions\EstoqueInsuficienteException;
use App\Filament\Resources\Movimentacaos\MovimentacaoResource;
use App\Models\Cidade;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Services\EstoqueService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class TransferirEstoqueAction
{
    /**
     * Transferência avulsa: escolhe produto e cidade de origem.
     */
    public static function make(): Action
    {
        return self::base('transferirEstoque')
            ->label('Transferir estoque')
            ->schema([
                Select::make('produto_id')
                    ->label('Produto')
                    ->options(fn () => Produto::query()->where('ativo', true)->orderBy('nome')->pluck('nome', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('cidade_origem_id', null)),
                Select::make('cidade_origem_id')
                    ->label('Cidade de origem')
                    ->options(fn (Get $get) => self::origensComSaldo($get('produto_id')))
                    ->required()
                    ->live()
                    ->disabled(fn (Get $get) => blank($get('produto_id'))),
                ...self::camposDestino(
                    origemId: fn (Get $get) => $get('cidade_origem_id'),
                    saldo: fn (Get $get) => self::saldo($get('produto_id'), $get('cidade_origem_id')),
                ),
            ])
            ->action(fn (array $data, Action $action) => self::executar(
                $action,
                Produto::findOrFail($data['produto_id']),
                Cidade::findOrFail($data['cidade_origem_id']),
                $data,
            ));
    }

    /**
     * Transferência a partir do saldo de uma cidade (registro ProdutoEstoque).
     */
    public static function daCidade(): Action
    {
        return self::base('transferir')
            ->label('Transferir')
            ->modalHeading(fn (ProdutoEstoque $record) => "Transferir {$record->produto->nome} de {$record->cidade->nome}")
            ->schema(fn (ProdutoEstoque $record) => self::camposDestino(
                origemId: fn () => $record->cidade_id,
                saldo: fn () => $record->quantidade,
            ))
            ->visible(fn (ProdutoEstoque $record) => $record->quantidade > 0 && MovimentacaoResource::canCreate())
            ->action(fn (array $data, Action $action, ProdutoEstoque $record) => self::executar(
                $action,
                $record->produto,
                $record->cidade,
                $data,
            ));
    }

    private static function base(string $nome): Action
    {
        return Action::make($nome)
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->color('gray')
            ->modalSubmitActionLabel('Transferir')
            ->visible(fn () => MovimentacaoResource::canCreate());
    }

    /**
     * @return array<int, Component>
     */
    private static function camposDestino(\Closure $origemId, \Closure $saldo): array
    {
        return [
            Select::make('cidade_destino_id')
                ->label('Cidade de destino')
                ->options(fn (Get $get) => Cidade::query()
                    ->where('ativo', true)
                    ->whereKeyNot($origemId($get) ?? 0)
                    ->orderBy('nome')
                    ->pluck('nome', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('quantidade')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->maxValue(fn (Get $get) => $saldo($get))
                ->helperText(fn (Get $get) => filled($disponivel = $saldo($get)) ? "Disponível na origem: {$disponivel}" : null)
                ->required(),
            Textarea::make('observacoes')
                ->label('Observações')
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function executar(Action $action, Produto $produto, Cidade $origem, array $data): void
    {
        try {
            app(EstoqueService::class)->transferir(
                produto: $produto,
                origem: $origem,
                destino: Cidade::findOrFail($data['cidade_destino_id']),
                quantidade: (int) $data['quantidade'],
                observacoes: $data['observacoes'] ?? null,
            );
        } catch (AcaoInvalidaException|EstoqueInsuficienteException $e) {
            Notification::make()->title('Transferência não realizada')->body($e->getMessage())->danger()->send();
            $action->halt();
        }

        Notification::make()->title('Estoque transferido')->success()->send();
    }

    /**
     * @return array<int, string>
     */
    private static function origensComSaldo(mixed $produtoId): array
    {
        if (blank($produtoId)) {
            return [];
        }

        return ProdutoEstoque::query()
            ->where('produto_id', $produtoId)
            ->where('quantidade', '>', 0)
            ->with('cidade')
            ->get()
            ->sortBy('cidade.nome')
            ->mapWithKeys(fn (ProdutoEstoque $estoque) => [
                $estoque->cidade_id => "{$estoque->cidade->nome} (saldo: {$estoque->quantidade})",
            ])
            ->all();
    }

    private static function saldo(mixed $produtoId, mixed $cidadeId): ?int
    {
        if (blank($produtoId) || blank($cidadeId)) {
            return null;
        }

        return ProdutoEstoque::query()
            ->where('produto_id', $produtoId)
            ->where('cidade_id', $cidadeId)
            ->value('quantidade');
    }
}
