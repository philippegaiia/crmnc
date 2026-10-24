<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\ContactRole;
use App\Enums\Language;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contacts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')->label('Prénom')->required()->maxLength(255),
                TextInput::make('last_name')->label('Nom')->required()->maxLength(255),
                Select::make('role')->label('Fonction')->options(ContactRole::class)->required(),
                TextInput::make('email')->label('E-mail')->email(),
                TextInput::make('phone')->label('Téléphone')->tel(),
                Select::make('preferred_language')->label('Langue préférée')->options(Language::class),
                Toggle::make('is_primary')->label('Contact principal'),
                Toggle::make('is_active')->label('Actif')->default(true),
                Textarea::make('notes')->label('Notes')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')->searchable()->sortable(),
                TextColumn::make('first_name')->label('Prénom')->searchable()->sortable(),
                TextColumn::make('role')->label('Fonction')->badge(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                IconColumn::make('is_primary')->label('Principal')->boolean(),
                IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
