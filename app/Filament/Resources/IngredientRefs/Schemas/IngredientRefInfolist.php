<?php

namespace App\Filament\Resources\IngredientRefs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class IngredientRefInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('display_name'),
                TextEntry::make('normalized_inci'),
                TextEntry::make('synonyms')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('origin')
                    ->badge(),
                TextEntry::make('label_position')
                    ->badge(),
                TextEntry::make('conditions')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('organic_eligibility')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ingredient_category_id')
                    ->numeric(),
            ]);
    }
}
