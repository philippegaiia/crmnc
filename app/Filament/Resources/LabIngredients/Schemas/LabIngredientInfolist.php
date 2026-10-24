<?php

namespace App\Filament\Resources\LabIngredients\Schemas;

use App\Models\Document;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class LabIngredientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification fournisseur')
                    ->schema([
                        TextEntry::make('company.legal_name')
                            ->label('Société'),
                        TextEntry::make('ingredientRef.display_name')
                            ->label('Ingrédient générique'),
                        TextEntry::make('supplier.legal_name')
                            ->label('Fournisseur'),
                        TextEntry::make('commercial_name')
                            ->label('Nom commercial'),
                        TextEntry::make('supplier_reference')
                            ->label('Référence fournisseur')
                            ->placeholder('-'),
                        TextEntry::make('grade')
                            ->label('Grade')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Caractéristiques biologiques')
                    ->schema([
                        TextEntry::make('latestReview.status')
                            ->label('Statut de la revue')
                            ->badge()
                            ->placeholder('Brouillon'),
                        TextEntry::make('ingredientRef.origin')
                            ->label('Origine générique')
                            ->badge(),
                        TextEntry::make('ingredientRef.organic_eligibility')
                            ->label('Éligibilité bio générique')
                            ->badge(),
                        TextEntry::make('organic_certification_status')
                            ->label('Statut biologique de la matière')
                            ->badge(),
                        TextEntry::make('organic_content_percentage')
                            ->label('Part biologique certifiée')
                            ->suffix(' %')
                            ->placeholder('À déterminer'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Documents justificatifs')
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->label('Documents')
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge(),
                                TextEntry::make('file_path')
                                    ->label('Fichier')
                                    ->formatStateUsing(fn (Document $record): string => $record->original_name)
                                    ->url(fn (Document $record): string => Storage::disk('local')->temporaryUrl(
                                        $record->file_path,
                                        now()->addMinutes(5),
                                    ))
                                    ->openUrlInNewTab(),
                                TextEntry::make('received_date')
                                    ->label('Reçu / émis le')
                                    ->date(),
                                TextEntry::make('issuer')
                                    ->label('Émetteur')
                                    ->placeholder('-'),
                                TextEntry::make('valid_until')
                                    ->label("Valide jusqu'au")
                                    ->date()
                                    ->placeholder('-'),
                                TextEntry::make('control_status')
                                    ->label('Contrôle')
                                    ->badge(),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
