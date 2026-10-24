<?php

namespace App\Filament\Resources\IngredientCategories;

use App\Filament\Resources\IngredientCategories\Pages\CreateIngredientCategory;
use App\Filament\Resources\IngredientCategories\Pages\EditIngredientCategory;
use App\Filament\Resources\IngredientCategories\Pages\ListIngredientCategories;
use App\Filament\Resources\IngredientCategories\Pages\ViewIngredientCategory;
use App\Filament\Resources\IngredientCategories\Schemas\IngredientCategoryForm;
use App\Filament\Resources\IngredientCategories\Schemas\IngredientCategoryInfolist;
use App\Filament\Resources\IngredientCategories\Tables\IngredientCategoriesTable;
use App\Models\IngredientCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IngredientCategoryResource extends Resource
{
    protected static ?string $model = IngredientCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = "catégorie d'ingrédient";

    protected static ?string $pluralModelLabel = "catégories d'ingrédients";

    protected static string|UnitEnum|null $navigationGroup = 'Référentiels';

    public static function form(Schema $schema): Schema
    {
        return IngredientCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IngredientCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IngredientCategoriesTable::configure($table);
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
            'index' => ListIngredientCategories::route('/'),
            'create' => CreateIngredientCategory::route('/create'),
            'view' => ViewIngredientCategory::route('/{record}'),
            'edit' => EditIngredientCategory::route('/{record}/edit'),
        ];
    }
}
