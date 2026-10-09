<?php

namespace App\Filament\Resources\Vendas\Schemas;

use App\Enums\StatusVenda;
use App\Models\Venda;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VendaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Venda')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('cliente.nome')
                            ->label('Cliente'),
                        TextEntry::make('vendedor.user.name')
                            ->label('Vendedor'),
                        TextEntry::make('data_venda')
                            ->label('Data')
                            ->date(),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (StatusVenda $state) => ucfirst($state->value))
                            ->color(fn (StatusVenda $state) => match ($state) {
                                StatusVenda::Aberta => 'warning',
                                StatusVenda::Fechada => 'success',
                                StatusVenda::Cancelada => 'danger',
                            }),
                        TextEntry::make('desconto_int')
                            ->label('Desconto')
                            ->money('BRL', divideBy: 100),
                        TextEntry::make('valor_total_int')
                            ->label('Total')
                            ->money('BRL', divideBy: 100)
                            ->weight('bold'),
                        TextEntry::make('comissao_total')
                            ->label('Comissão')
                            ->state(fn (Venda $record) => $record->itens->sum('comissao_int'))
                            ->money('BRL', divideBy: 100)
                            ->color('success'),
                        TextEntry::make('observacoes')
                            ->label('Observações')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Itens')
                    ->schema([
                        RepeatableEntry::make('itens')
                            ->hiddenLabel()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('produto.nome')
                                    ->label('Produto')
                                    ->columnSpan(2),
                                TextEntry::make('cidade.nome')
                                    ->label('Cidade'),
                                TextEntry::make('quantidade')
                                    ->label('Qtd.')
                                    ->numeric(),
                                TextEntry::make('preco_unit_int')
                                    ->label('Preço unit.')
                                    ->money('BRL', divideBy: 100),
                                TextEntry::make('subtotal_int')
                                    ->label('Subtotal')
                                    ->money('BRL', divideBy: 100),
                                TextEntry::make('comissao_int')
                                    ->label('Comissão')
                                    ->money('BRL', divideBy: 100)
                                    ->suffix(fn ($record) => " ({$record->comissao_pct}%)")
                                    ->color('success')
                                    ->columnSpan(3),
                            ]),
                    ]),
                Section::make('Comprovantes de pagamento')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('arquivos_comprovantes')
                            ->hiddenLabel()
                            ->state(fn (Venda $record) => $record->getMedia(Venda::COLECAO_COMPROVANTES))
                            ->placeholder('Nenhum comprovante enviado.')
                            ->contained(false)
                            ->alignment(Alignment::Justify)
                            ->extraAttributes([
                                'class' => 'justify-center',
                            ])
                            ->grid(4)
                            ->schema([
                                ImageEntry::make('file_name')
                                    ->maxWidth(Width::Full)
                                    ->imageWidth('100%')
                                    ->hiddenLabel()
                                    ->state(fn (Media $record) => $record->getTemporaryUrl(now()->addMinutes(30)))
                                    ->url(fn (Media $record) => $record->getTemporaryUrl(now()->addMinutes(30)))
                                    ->openUrlInNewTab(),
                            ]),
                    ]),
            ]);
    }

    private static function eImagem(Media $media): bool
    {
        return str_starts_with($media->mime_type, 'image/');
    }
}
