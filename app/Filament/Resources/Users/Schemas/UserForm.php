<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Utilisateur')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        Select::make('role')
                            ->label('Rôle')
                            ->options(UserRole::class)
                            ->live()
                            ->afterStateUpdated(function (Set $set, UserRole|string|null $state): void {
                                $role = $state instanceof UserRole
                                    ? $state
                                    : UserRole::tryFrom($state ?? '');

                                if ($role !== UserRole::Adherent) {
                                    $set('company_id', null);
                                }
                            })
                            ->required(),
                        Select::make('company_id')
                            ->label('Société')
                            ->relationship('company', 'legal_name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => in_array(
                                $get('role'),
                                [UserRole::Adherent, UserRole::Adherent->value],
                                true,
                            ))
                            ->required(fn (Get $get): bool => in_array(
                                $get('role'),
                                [UserRole::Adherent, UserRole::Adherent->value],
                                true,
                            )),
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
