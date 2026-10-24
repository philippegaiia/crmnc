<?php

namespace App\Filament\Resources\LabIngredients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LabIngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('latestReview.status')
                    ->label('Revue')
                    ->badge()
                    ->placeholder('Brouillon'),
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
