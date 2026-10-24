<?php

namespace App\Filament\Resources\LabIngredients;

use App\Enums\UserRole;
use App\Filament\Resources\LabIngredients\Pages\CreateLabIngredient;
use App\Filament\Resources\LabIngredients\Pages\EditLabIngredient;
use App\Filament\Resources\LabIngredients\Pages\ListLabIngredients;
use App\Filament\Resources\LabIngredients\Pages\ViewLabIngredient;
use App\Filament\Resources\LabIngredients\Schemas\LabIngredientForm;
use App\Filament\Resources\LabIngredients\Schemas\LabIngredientInfolist;
use App\Filament\Resources\LabIngredients\Tables\LabIngredientsTable;
use App\Models\LabIngredient;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LabIngredientResource extends Resource
{
    protected static ?string $model = LabIngredient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'commercial_name';

    protected static ?string $modelLabel = 'ingrédient laboratoire';

    protected static ?string $pluralModelLabel = 'ingrédients laboratoire';

    protected static string|UnitEnum|null $navigationGroup = 'Ingrédients';

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = parent::getEloquentQuery();

        if (! $user instanceof User) {
            return $query->whereKey(null);
        }

        return $user->role === UserRole::Adherent
            ? $query->where('company_id', $user->company_id)
            : $query;
    }

    public static function form(Schema $schema): Schema
    {
        return LabIngredientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabIngredientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabIngredientsTable::configure($table);
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
            'index' => ListLabIngredients::route('/'),
            'create' => CreateLabIngredient::route('/create'),
            'view' => ViewLabIngredient::route('/{record}'),
            'edit' => EditLabIngredient::route('/{record}/edit'),
        ];
    }
}
