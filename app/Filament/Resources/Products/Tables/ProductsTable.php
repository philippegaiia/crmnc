<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductCommercialStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                TextColumn::make('brand')->label('Marque')->searchable(),
                TextColumn::make('company.legal_name')->label('Société')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Catégorie')->sortable(),
                TextColumn::make('commercial_status')->label('Statut')->badge()->sortable(),
                TextColumn::make('currentFormulaVersion.version_number')->label('Version')->prefix('v'),
            ])
            ->filters([
                SelectFilter::make('commercial_status')->label('Statut')->options(ProductCommercialStatus::class),
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
