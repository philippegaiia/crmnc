<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductCommercialStatus;
use App\Enums\UserRole;
use App\Models\FormulaVersion;
use App\Models\User;
use App\Services\CompanyAccessService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Produit')
                    ->schema([
                        Select::make('company_id')->label('Société')->relationship('company', 'legal_name', modifyQueryUsing: fn (Builder $query): Builder => app(CompanyAccessService::class)->scopeCompanies($query, Filament::auth()->user()))->searchable()->preload()->required()->live()
                            ->default(fn (): ?int => ($user = Filament::auth()->user()) instanceof User ? $user->company_id : null)
                            ->disabled(fn (): bool => Filament::auth()->user()?->role === UserRole::Adherent)->dehydrated(),
                        Select::make('product_category_id')->label('Catégorie')->relationship('category', 'name', modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query->whereHas('companies', fn (Builder $companies): Builder => $companies->whereKey($get('company_id'))))->searchable()->preload()->required(),
                        TextInput::make('name')->label('Nom')->required()->maxLength(255),
                        TextInput::make('brand')->label('Marque')->required()->maxLength(255),
                        TextInput::make('white_label_client')->label('Client marque blanche')->maxLength(255),
                        Select::make('current_formula_version_id')->label('Version de formule actuelle')->options(fn (Get $get): array => FormulaVersion::query()->whereHas('formula', fn (Builder $query): Builder => $query->where('company_id', $get('company_id')))->with('formula')->get()->mapWithKeys(fn (FormulaVersion $version): array => [$version->id => $version->formula->internal_code.' · v'.$version->version_number])->all())->searchable(),
                        Select::make('commercial_status')->label('Statut commercial')->options(ProductCommercialStatus::class)->required(),
                        Textarea::make('notes')->label('Notes')->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
