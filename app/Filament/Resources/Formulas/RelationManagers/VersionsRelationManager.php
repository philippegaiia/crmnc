<?php

namespace App\Filament\Resources\Formulas\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Versions de la formule';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version_number')
            ->columns([
                TextColumn::make('version_number')
                    ->label('Version')
                    ->prefix('v')
                    ->sortable(),
                TextColumn::make('workflow_status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('lines_count')
                    ->label('Lignes')
                    ->counts('lines'),
                TextColumn::make('submitted_at')
                    ->label('Soumise le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
