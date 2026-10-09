<?php

namespace App\Filament\Resources\Vendas;

use App\Filament\Resources\Vendas\Pages\CreateVenda;
use App\Filament\Resources\Vendas\Pages\ListVendas;
use App\Filament\Resources\Vendas\Schemas\VendaForm;
use App\Filament\Resources\Vendas\Schemas\VendaInfolist;
use App\Filament\Resources\Vendas\Tables\VendasTable;
use App\Models\Venda;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class VendaResource extends Resource
{
    protected static ?string $model = Venda::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Operação';

    public static function getModelLabel(): string
    {
        return 'venda';
    }

    public static function getPluralModelLabel(): string
    {
        return 'vendas';
    }

    public static function form(Schema $schema): Schema
    {
        return VendaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VendaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendasTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole('Admin') && $user->vendedor) {
            return parent::getEloquentQuery()
                ->where('vendedor_id', $user->vendedor->getKey());
        }

        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendas::route('/'),
            'create' => CreateVenda::route('/create'),
        ];
    }
}
