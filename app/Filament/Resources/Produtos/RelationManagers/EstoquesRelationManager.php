<?php

namespace App\Filament\Resources\Produtos\RelationManagers;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Filament\Actions\TransferirEstoqueAction;
use App\Models\Cidade;
use App\Services\EstoqueService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
            ->columns([
                TextColumn::make('cidade.nome')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),
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
                        app(EstoqueService::class)->registrar(
                            produto: $livewire->getOwnerRecord(),
                            cidade: Cidade::findOrFail($data['cidade_id']),
                            tipo: TipoMovimentacao::Entrada,
                            quantidade: (int) ($data['quantidade'] ?? 0),
                            motivo: MotivoMovimentacao::Compra,
                            observacoes: 'Entrada manual (estoque por cidade)',
                            estoqueMinimo: (int) ($data['estoque_minimo'] ?? 0),
                        );
                    }),
            ])
            ->recordActions([
                Action::make('ajustar')
                    ->label('Ajustar')
                    ->schema([
                        TextInput::make('quantidade')
                            ->label('Quantidade (negativa remove)')
                            ->numeric()
                            ->required()
                            ->helperText('Ex.: 5 adiciona, -3 remove.'),
                    ])
                    ->action(function (array $data, $record): void {
                        app(EstoqueService::class)->registrar(
                            produto: $record->produto,
                            cidade: $record->cidade,
                            tipo: TipoMovimentacao::Ajuste,
                            quantidade: (int) $data['quantidade'],
                            motivo: MotivoMovimentacao::Ajuste,
                            observacoes: 'Ajuste manual (estoque por cidade)',
                        );
                    }),
                TransferirEstoqueAction::daCidade(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
