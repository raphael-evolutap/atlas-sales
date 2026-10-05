<?php

namespace App\Filament\Resources\Movimentacaos;

use App\Filament\Resources\Movimentacaos\Pages\CreateMovimentacao;
use App\Filament\Resources\Movimentacaos\Pages\ListMovimentacaos;
use App\Filament\Resources\Movimentacaos\Schemas\MovimentacaoForm;
use App\Filament\Resources\Movimentacaos\Tables\MovimentacaosTable;
use App\Models\Movimentacao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MovimentacaoResource extends Resource
{
    protected static ?string $model = Movimentacao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Operação';

    public static function getModelLabel(): string
    {
        return 'movimentação';
    }

    public static function getPluralModelLabel(): string
    {
        return 'movimentações';
    }

    public static function form(Schema $schema): Schema
    {
        return MovimentacaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovimentacaosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimentacaos::route('/'),
            'create' => CreateMovimentacao::route('/create'),
        ];
    }
}
