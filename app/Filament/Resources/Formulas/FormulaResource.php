<?php

namespace App\Filament\Resources\Formulas;

use App\Enums\UserRole;
use App\Filament\Resources\Formulas\Pages\CreateFormula;
use App\Filament\Resources\Formulas\Pages\EditFormula;
use App\Filament\Resources\Formulas\Pages\ListFormulas;
use App\Filament\Resources\Formulas\Pages\ViewFormula;
use App\Filament\Resources\Formulas\RelationManagers\VersionsRelationManager;
use App\Filament\Resources\Formulas\Schemas\FormulaForm;
use App\Filament\Resources\Formulas\Schemas\FormulaInfolist;
use App\Filament\Resources\Formulas\Tables\FormulasTable;
use App\Models\Formula;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FormulaResource extends Resource
{
    protected static ?string $model = Formula::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'internal_code';

    protected static ?string $modelLabel = 'formule';

    protected static ?string $pluralModelLabel = 'formules';

    protected static string|UnitEnum|null $navigationGroup = 'Produits';

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
        return FormulaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FormulaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormulasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormulas::route('/'),
            'create' => CreateFormula::route('/create'),
            'view' => ViewFormula::route('/{record}'),
            'edit' => EditFormula::route('/{record}/edit'),
        ];
    }
}
