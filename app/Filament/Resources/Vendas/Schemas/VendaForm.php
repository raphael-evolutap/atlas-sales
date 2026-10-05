<?php

namespace App\Filament\Resources\Vendas\Schemas;

use App\Enums\StatusVenda;
use App\Models\Cidade;
use App\Models\Produto;
use App\Models\ProdutoEstoque;
use App\Models\Vendedor;
use App\Support\Money;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money as FilamentPtbrFormFieldsMoney;

class VendaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cliente_id')
                    ->relationship('cliente', 'nome')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('vendedor_id')
                    ->label('Vendedor')
                    ->options(Vendedor::query()->with('user')->get()->mapWithKeys(fn ($v) => [$v->id => $v->user->name]))
                    ->default(fn () => auth()->user()?->vendedor?->id)
                    ->disabled(fn () => auth()->user()?->isVendedor())
                    ->required(),
                DatePicker::make('data_venda')
                    ->required()
                    ->default(now()),
                Select::make('status')
                    ->options(collect(StatusVenda::cases())->mapWithKeys(fn ($case) => [$case->value => ucfirst($case->value)]))
                    ->default(StatusVenda::Aberta->value)
                    ->required()
                    ->helperText('Fechada baixa o estoque; aberta não.'),
                FilamentPtbrFormFieldsMoney::make('desconto_int')
                    ->label('Desconto')
                    ->prefix('R$')
                    ->formatStateUsing(fn ($state) => Money::toView($state))
                    ->dehydrateStateUsing(fn ($state) => Money::fromView($state)),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
                Repeater::make('itens')
                    ->schema([
                        Select::make('produto_id')
                            ->label('Produto')
                            ->options(Produto::query()->where('ativo', true)->orderBy('nome')->pluck('nome', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpan(1),
                        Select::make('cidade_id')
                            ->label('Cidade')
                            ->options(function (Get $get): array {
                                $produtoId = $get('produto_id');

                                if (! $produtoId) {
                                    return Cidade::query()->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all();
                                }

                                return ProdutoEstoque::query()
                                    ->where('produto_id', $produtoId)
                                    ->whereHas('cidade', fn ($query) => $query->where('ativo', true))
                                    ->with('cidade')
                                    ->get()
                                    ->sortBy('cidade.nome')
                                    ->mapWithKeys(fn (ProdutoEstoque $estoque) => [
                                        $estoque->cidade_id => "{$estoque->cidade->nome} (saldo: {$estoque->quantidade})",
                                    ])
                                    ->all();
                            })
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
                            ->createOptionUsing(fn (array $data): int => Cidade::create($data)->getKey())
                            ->columnSpan(1),
                        TextInput::make('quantidade')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
