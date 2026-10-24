<?php

namespace App\Filament\Resources\Formulas\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\CompanyAccessService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FormulaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Formule')
                    ->schema([
                        Select::make('company_id')
                            ->label('Société')
                            ->relationship('company', 'legal_name', modifyQueryUsing: fn (Builder $query): Builder => app(CompanyAccessService::class)->scopeCompanies($query, Filament::auth()->user()))
                            ->searchable()->preload()->required()
                            ->default(fn (): ?int => ($user = Filament::auth()->user()) instanceof User ? $user->company_id : null)
                            ->disabled(fn (): bool => Filament::auth()->user()?->role === UserRole::Adherent)
                            ->dehydrated(),
                        TextInput::make('internal_code')->label('Code interne')->required()->maxLength(255),
                        Textarea::make('description')->label('Description')->columnSpanFull(),
                        Toggle::make('is_active')->label('Active')->default(true),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
