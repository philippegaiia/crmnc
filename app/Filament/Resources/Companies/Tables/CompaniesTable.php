<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Enums\CompanyCommercialStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legal_name')
                    ->label('Raison sociale')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usual_name')
                    ->label("Nom d'usage")
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('commercial_status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('country.name_fr')
                    ->label('Pays')
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('commercial_status')
                    ->label('Statut')
                    ->options(CompanyCommercialStatus::class),
                SelectFilter::make('country')
                    ->label('Pays')
                    ->relationship('country', 'name_fr'),
            ])
            ->defaultSort('created_at', 'desc')
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
