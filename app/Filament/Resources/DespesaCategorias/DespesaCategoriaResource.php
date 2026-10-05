<?php

namespace App\Filament\Resources\DespesaCategorias;

use App\Filament\Resources\DespesaCategorias\Pages\CreateDespesaCategoria;
use App\Filament\Resources\DespesaCategorias\Pages\EditDespesaCategoria;
use App\Filament\Resources\DespesaCategorias\Pages\ListDespesaCategorias;
use App\Filament\Resources\DespesaCategorias\Schemas\DespesaCategoriaForm;
use App\Filament\Resources\DespesaCategorias\Tables\DespesaCategoriasTable;
use App\Models\DespesaCategoria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DespesaCategoriaResource extends Resource
{
    protected static ?string $model = DespesaCategoria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'categoria de despesa';
    }

    public static function getPluralModelLabel(): string
    {
        return 'categorias de despesa';
    }

    public static function form(Schema $schema): Schema
    {
        return DespesaCategoriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DespesaCategoriasTable::configure($table);
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
            'index' => ListDespesaCategorias::route('/'),
            'create' => CreateDespesaCategoria::route('/create'),
            'edit' => EditDespesaCategoria::route('/{record}/edit'),
        ];
    }
}
