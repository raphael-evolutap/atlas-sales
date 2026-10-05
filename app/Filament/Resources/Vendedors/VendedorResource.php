<?php

namespace App\Filament\Resources\Vendedors;

use App\Filament\Resources\Vendedors\Pages\CreateVendedor;
use App\Filament\Resources\Vendedors\Pages\EditVendedor;
use App\Filament\Resources\Vendedors\Pages\ListVendedors;
use App\Filament\Resources\Vendedors\Schemas\VendedorForm;
use App\Filament\Resources\Vendedors\Tables\VendedorsTable;
use App\Models\Vendedor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VendedorResource extends Resource
{
    protected static ?string $model = Vendedor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    public static function getModelLabel(): string
    {
        return 'vendedor';
    }

    public static function getPluralModelLabel(): string
    {
        return 'vendedores';
    }

    public static function form(Schema $schema): Schema
    {
        return VendedorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendedorsTable::configure($table);
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
            'index' => ListVendedors::route('/'),
            'create' => CreateVendedor::route('/create'),
            'edit' => EditVendedor::route('/{record}/edit'),
        ];
    }
}
