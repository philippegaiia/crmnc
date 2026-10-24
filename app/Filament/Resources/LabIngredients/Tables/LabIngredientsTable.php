<?php

namespace App\Filament\Resources\LabIngredients\Tables;

use App\Enums\LabIngredientStatus;
use App\Models\LabIngredient;
use App\Services\LabIngredientStatusService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LabIngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['latestReview', 'documents']))
            ->columns([
                TextColumn::make('company.legal_name')->label('Société')->searchable()->sortable(),
                TextColumn::make('ingredientRef.display_name')->label('Ingrédient générique')->searchable()->sortable(),
                TextColumn::make('commercial_name')
                    ->label('Nom commercial')->searchable()->sortable(),
                TextColumn::make('supplier.legal_name')->label('Fournisseur')->searchable()->sortable(),
                TextColumn::make('supplier_reference')
                    ->label('Référence')->searchable(),
                TextColumn::make('grade')
                    ->label('Grade')->searchable(),
                TextColumn::make('organic_certification_status')
                    ->label('Statut bio')
                    ->badge()
                    ->sortable(),
                TextColumn::make('organic_content_percentage')
                    ->label('% bio')
                    ->suffix(' %')
                    ->placeholder('À déterminer')
                    ->sortable(),
                TextColumn::make('documents_count')
                    ->label('Documents')
                    ->counts('documents')
                    ->badge(),
                TextColumn::make('validation_status')
                    ->label('Statut de validation')
                    ->badge()
                    ->state(fn (LabIngredient $record): LabIngredientStatus => app(LabIngredientStatusService::class)->status($record)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
