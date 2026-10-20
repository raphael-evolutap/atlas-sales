<?php

namespace App\Filament\Resources\Produtos\RelationManagers;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Exceptions\EstoqueInsuficienteException;
use App\Filament\Actions\TransferirEstoqueAction;
use App\Filament\Forms\LoteSelect;
use App\Models\Cidade;
use App\Models\ProdutoEstoque;
use App\Models\ProdutoEstoqueLote;
use App\Models\ProdutoLote;
use App\Services\EstoqueService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EstoquesRelationManager extends RelationManager
{
    protected static string $relationship = 'estoques';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cidade_id')
                    ->label('Cidade')
                    ->relationship('cidade', 'nome')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('estoque_minimo')
                    ->label('Mínimo')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cidade.nome')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cidade', 'lotes.lote']))
            ->columns([
                TextColumn::make('cidade.nome')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('saldos_por_lote')
                    ->label('Lotes')
                    ->state(fn (ProdutoEstoque $record): array => $record->lotes
                        ->where('quantidade', '>=', 0)
                        ->sortBy('lote.numero')
                        ->map(fn (ProdutoEstoqueLote $saldo) => "{$saldo->lote->numero}: {$saldo->quantidade}")
                        ->values()
                        ->all())
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('quantidade')
                    ->label('Estoque')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->quantidade <= $record->estoque_minimo ? 'danger' : 'success'),
                TextColumn::make('estoque_minimo')
                    ->label('Mínimo')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('adicionarEstoque')
                    ->label('Adicionar estoque')
                    ->schema([
                        Select::make('cidade_id')
                            ->label('Cidade')
                            ->options(Cidade::query()->where('ativo', true)->orderBy('nome')->pluck('nome', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nome')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('uf')
                                    ->label('UF')
                                    ->maxLength(2),
                            ])
                            ->createOptionUsing(fn (array $data): int => Cidade::create($data)->getKey()),
                        LoteSelect::paraEntrada(fn () => $this->getOwnerRecord()->getKey()),
                        TextInput::make('quantidade')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('estoque_minimo')
                            ->label('Mínimo')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        // Lote novo só é criado junto com a entrada: se ela falhar, nada fica para trás.
                        DB::transaction(fn () => app(EstoqueService::class)->registrar(
                            lote: ProdutoLote::doProduto($livewire->getOwnerRecord(), $data['lote_numero']),
                            cidade: Cidade::findOrFail($data['cidade_id']),
                            tipo: TipoMovimentacao::Entrada,
                            quantidade: (int) ($data['quantidade'] ?? 0),
                            motivo: MotivoMovimentacao::Compra,
                            observacoes: 'Entrada manual (estoque por cidade e lote)',
                            estoqueMinimo: (int) ($data['estoque_minimo'] ?? 0),
                        ));
                    }),
            ])
            ->recordActions([
                Action::make('ajustar')
                    ->label('Ajustar')
                    ->schema(fn (ProdutoEstoque $record): array => [
                        LoteSelect::paraAjuste(fn () => $record->produto_id, fn () => $record->cidade_id),
                        TextInput::make('quantidade')
                            ->label('Quantidade (negativa remove)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(fn (Get $get) => filled($saldo = LoteSelect::saldo($record->produto_id, $record->cidade_id, $get('lote_id'))) ? -$saldo : null)
                            ->validationMessages([
                                'min' => fn (Get $get) => 'Não é possível remover mais do que o saldo do lote nesta cidade ('
                                    .LoteSelect::saldo($record->produto_id, $record->cidade_id, $get('lote_id')).').',
                            ])
                            ->helperText(fn (Get $get) => 'Ex.: 5 adiciona, -3 remove.'
                                .(filled($saldo = LoteSelect::saldo($record->produto_id, $record->cidade_id, $get('lote_id'))) ? " Saldo do lote nesta cidade: {$saldo}" : '')),
                    ])
                    ->action(function (array $data, ProdutoEstoque $record, Action $action): void {
                        // O saldo pode ter mudado desde que o formulário foi aberto.
                        try {
                            app(EstoqueService::class)->registrar(
                                lote: ProdutoLote::doProdutoOuFalha($record->produto_id, $data['lote_id']),
                                cidade: $record->cidade,
                                tipo: TipoMovimentacao::Ajuste,
                                quantidade: (int) $data['quantidade'],
                                motivo: MotivoMovimentacao::Ajuste,
                                observacoes: 'Ajuste manual (estoque por cidade e lote)',
                            );
                        } catch (EstoqueInsuficienteException $e) {
                            Notification::make()->title('Ajuste não realizado')->body($e->getMessage())->danger()->send();
                            $action->halt();
                        }

                        Notification::make()->title('Estoque ajustado')->success()->send();
                    }),
                TransferirEstoqueAction::daCidade(),
                EditAction::make(),
            ])
            // Só cidades zeradas podem ser excluídas: excluir uma com saldo apagaria os
            // saldos por lote sem movimentação e sem baixar produtos.estoque_qtd.
            ->checkIfRecordIsSelectableUsing(fn (ProdutoEstoque $record): bool => $record->quantidade <= 0)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
