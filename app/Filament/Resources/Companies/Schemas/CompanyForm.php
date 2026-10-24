<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\CompanyCommercialStatus;
use App\Enums\CompanyOrigin;
use App\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        TextInput::make('legal_name')
                            ->label('Raison sociale')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('usual_name')
                            ->label("Nom d'usage")
                            ->maxLength(255),
                        Select::make('commercial_status')
                            ->label('Statut commercial')
                            ->options(CompanyCommercialStatus::class)
                            ->default(CompanyCommercialStatus::DemandeInfo)
                            ->required(),
                        Select::make('origin')
                            ->label('Origine du contact')
                            ->options(CompanyOrigin::class),
                        Select::make('preferred_language')
                            ->label('Langue préférée')
                            ->options(Language::class)
                            ->default(Language::French)
                            ->required(),
                        Select::make('country_code')
                            ->label('Pays')
                            ->relationship('country', 'name_fr')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Coordonnées')
                    ->schema([
                        TextInput::make('address_line_1')->label('Adresse'),
                        TextInput::make('address_line_2')->label("Complément d'adresse"),
                        TextInput::make('postal_code')->label('Code postal'),
                        TextInput::make('city')->label('Ville'),
                        TextInput::make('region')->label('Région'),
                        TextInput::make('phone')->label('Téléphone')->tel(),
                        TextInput::make('email')->label('E-mail')->email(),
                        TextInput::make('website')->label('Site web')->url(),
                        TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                        TextInput::make('instagram_url')->label('Instagram')->url(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Notes internes')
                    ->schema([
                        Textarea::make('notes_internal')
                            ->label('Notes')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
