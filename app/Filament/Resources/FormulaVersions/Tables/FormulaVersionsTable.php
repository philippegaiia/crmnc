<?php

namespace App\Filament\Resources\FormulaVersions\Tables;

use App\Enums\FormulaVersionStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FormulaVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('formula.internal_code')->label('Formule')->searchable()->sortable(),
                TextColumn::make('formula.company.legal_name')->label('Société')->searchable(),
                TextColumn::make('version_number')->label('Version')->prefix('v')->sortable(),
                TextColumn::make('workflow_status')->label('Statut')->badge()->sortable(),
                TextColumn::make('lines_count')->label('Lignes')->counts('lines'),
                TextColumn::make('updated_at')->label('Modifiée le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('workflow_status')->label('Statut')->options(FormulaVersionStatus::class),
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
