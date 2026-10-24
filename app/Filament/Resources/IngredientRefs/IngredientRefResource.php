<?php

namespace App\Filament\Resources\IngredientRefs;

use App\Filament\Resources\IngredientRefs\Pages\CreateIngredientRef;
use App\Filament\Resources\IngredientRefs\Pages\EditIngredientRef;
use App\Filament\Resources\IngredientRefs\Pages\ListIngredientRefs;
use App\Filament\Resources\IngredientRefs\Pages\ViewIngredientRef;
use App\Filament\Resources\IngredientRefs\Schemas\IngredientRefForm;
use App\Filament\Resources\IngredientRefs\Schemas\IngredientRefInfolist;
use App\Filament\Resources\IngredientRefs\Tables\IngredientRefsTable;
use App\Models\IngredientRef;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IngredientRefResource extends Resource
{
    protected static ?string $model = IngredientRef::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'display_name';

    protected static ?string $modelLabel = 'ingrédient générique';

    protected static ?string $pluralModelLabel = 'ingrédients génériques';

    protected static string|UnitEnum|null $navigationGroup = 'Référentiels';

    public static function form(Schema $schema): Schema
    {
        return IngredientRefForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IngredientRefInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IngredientRefsTable::configure($table);
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
            'index' => ListIngredientRefs::route('/'),
            'create' => CreateIngredientRef::route('/create'),
            'view' => ViewIngredientRef::route('/{record}'),
            'edit' => EditIngredientRef::route('/{record}/edit'),
        ];
    }
}
