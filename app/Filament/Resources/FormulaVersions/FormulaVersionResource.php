<?php

namespace App\Filament\Resources\FormulaVersions;

use App\Enums\UserRole;
use App\Filament\Resources\FormulaVersions\Pages\EditFormulaVersion;
use App\Filament\Resources\FormulaVersions\Pages\ListFormulaVersions;
use App\Filament\Resources\FormulaVersions\Pages\ViewFormulaVersion;
use App\Filament\Resources\FormulaVersions\Schemas\FormulaVersionForm;
use App\Filament\Resources\FormulaVersions\Schemas\FormulaVersionInfolist;
use App\Filament\Resources\FormulaVersions\Tables\FormulaVersionsTable;
use App\Models\FormulaVersion;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FormulaVersionResource extends Resource
{
    protected static ?string $model = FormulaVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'version de formule';

    protected static ?string $pluralModelLabel = 'versions de formule';

    protected static string|UnitEnum|null $navigationGroup = 'Produits';

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = parent::getEloquentQuery();

        if (! $user instanceof User) {
            return $query->whereKey(null);
        }

        return $user->role === UserRole::Adherent
            ? $query->whereHas('formula', fn (Builder $formulaQuery): Builder => $formulaQuery->where('company_id', $user->company_id))
            : $query;
    }

    public static function form(Schema $schema): Schema
    {
        return FormulaVersionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FormulaVersionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormulaVersionsTable::configure($table);
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
            'index' => ListFormulaVersions::route('/'),
            'view' => ViewFormulaVersion::route('/{record}'),
            'edit' => EditFormulaVersion::route('/{record}/edit'),
        ];
    }
}
