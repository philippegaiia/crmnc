<?php

namespace App\Filament\Resources\IngredientRefs\Schemas;

use App\Enums\IngredientOrigin;
use App\Enums\IngredientRefStatus;
use App\Enums\LabelPosition;
use App\Enums\OrganicEligibility;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IngredientRefForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('display_name')
                    ->required(),
                TextInput::make('normalized_inci')
                    ->required(),
                TagsInput::make('synonyms')
                    ->label('Synonymes')
                    ->columnSpanFull(),
                Select::make('origin')
                    ->options(IngredientOrigin::class)
                    ->required(),
                Select::make('label_position')
                    ->options(LabelPosition::class)
                    ->default('a_statuer')
                    ->required(),
                Textarea::make('conditions')
                    ->columnSpanFull(),
                Select::make('organic_eligibility')
                    ->options(OrganicEligibility::class)
                    ->default('inconnu')
                    ->required(),
                Select::make('status')
                    ->options(IngredientRefStatus::class)
                    ->default('a_valider')
                    ->required(),
                Select::make('ingredient_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
