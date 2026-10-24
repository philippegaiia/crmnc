<?php

namespace App\Filament\Resources\Contacts\Schemas;

use App\Enums\ContactRole;
use App\Enums\Language;
use App\Enums\UserRole;
use App\Services\CompanyAccessService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact')
                    ->schema([
                        Select::make('company_id')
                            ->label('Société')
                            ->relationship(
                                'company',
                                'legal_name',
                                modifyQueryUsing: fn (Builder $query): Builder => app(CompanyAccessService::class)->scopeCompanies($query, Filament::auth()->user()),
                            )
                            ->default(fn (): ?int => Filament::auth()->user()?->company_id)
                            ->disabled(fn (): bool => Filament::auth()->user()?->role === UserRole::Adherent)
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Select::make('role')
                            ->label('Fonction')
                            ->options(ContactRole::class)
                            ->required(),
                        TextInput::make('email')->label('E-mail')->email(),
                        TextInput::make('phone')->label('Téléphone')->tel(),
                        Select::make('preferred_language')
                            ->label('Langue préférée')
                            ->options(Language::class),
                        Toggle::make('is_primary')
                            ->label('Contact principal'),
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
