<?php

namespace App\Filament\Resources\Cidades;

use App\Filament\Resources\Cidades\Pages\CreateCidade;
use App\Filament\Resources\Cidades\Pages\EditCidade;
use App\Filament\Resources\Cidades\Pages\ListCidades;
use App\Filament\Resources\Cidades\Schemas\CidadeForm;
use App\Filament\Resources\Cidades\Tables\CidadesTable;
use App\Models\Cidade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CidadeResource extends Resource
{
    protected static ?string $model = Cidade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    public static function getModelLabel(): string
    {
        return 'cidade';
    }

    public static function getPluralModelLabel(): string
    {
        return 'cidades';
    }

    public static function form(Schema $schema): Schema
    {
        return CidadeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CidadesTable::configure($table);
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
            'index' => ListCidades::route('/'),
            'create' => CreateCidade::route('/create'),
            'edit' => EditCidade::route('/{record}/edit'),
        ];
    }
}
