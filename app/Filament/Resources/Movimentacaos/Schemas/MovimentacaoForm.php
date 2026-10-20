<?php

namespace App\Filament\Resources\Movimentacaos\Schemas;

use App\Enums\MotivoMovimentacao;
use App\Enums\TipoMovimentacao;
use App\Filament\Forms\LoteSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MovimentacaoForm
{
    public static function configure(Schema $schema): Schema
    {
        $produtoId = fn (Get $get) => $get('produto_id');
        $cidadeId = fn (Get $get) => $get('cidade_id');
        $tipo = fn (Get $get): ?TipoMovimentacao => TipoMovimentacao::tryFrom((string) $get('tipo'));
        $saldo = fn (Get $get): ?int => LoteSelect::saldo($get('produto_id'), $get('cidade_id'), $get('lote_id'));

        return $schema
            ->components([
                Select::make('produto_id')
                    ->relationship('produto', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('lote_id', null)),
                Select::make('cidade_id')
                    ->label('Cidade')
                    ->relationship('cidade', 'nome')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('lote_id', null))
                    ->createOptionForm([
                        TextInput::make('nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('uf')
                            ->label('UF')
                            ->maxLength(2),
                    ]),
                Select::make('tipo')
                    ->options(collect(TipoMovimentacao::cases())->mapWithKeys(fn ($case) => [$case->value => ucfirst($case->value)]))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('lote_id', null)),
                LoteSelect::paraEntrada($produtoId)
                    ->visible(fn (Get $get) => $tipo($get) === TipoMovimentacao::Entrada),
                LoteSelect::paraAjuste($produtoId, $cidadeId, apenasComSaldo: fn (Get $get) => $tipo($get) === TipoMovimentacao::Saida)
                    ->visible(fn (Get $get) => in_array($tipo($get), [TipoMovimentacao::Saida, TipoMovimentacao::Ajuste], true)),
                Select::make('motivo')
                    ->options(collect(MotivoMovimentacao::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))
                    ->required(),
                TextInput::make('quantidade')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(fn (Get $get) => match ($tipo($get)) {
                        TipoMovimentacao::Ajuste => filled($disponivel = $saldo($get)) ? -$disponivel : null,
                        default => 1,
                    })
                    ->maxValue(fn (Get $get) => $tipo($get) === TipoMovimentacao::Saida ? $saldo($get) : null)
                    ->validationMessages([
                        'min' => fn (Get $get) => $tipo($get) === TipoMovimentacao::Ajuste
                            ? "Não é possível remover mais do que o saldo do lote na cidade ({$saldo($get)})."
                            : 'A quantidade deve ser maior que zero.',
                        'max' => fn (Get $get) => "Não é possível retirar mais do que o saldo do lote na cidade ({$saldo($get)}).",
                    ])
                    ->helperText(fn (Get $get) => collect([
                        $tipo($get) === TipoMovimentacao::Ajuste ? 'Negativo remove (ex.: -3).' : null,
                        filled($disponivel = $saldo($get)) ? "Saldo do lote na cidade: {$disponivel}" : null,
                    ])->filter()->implode(' ')),
                Textarea::make('observacoes')
                    ->columnSpanFull(),
            ]);
    }
}
