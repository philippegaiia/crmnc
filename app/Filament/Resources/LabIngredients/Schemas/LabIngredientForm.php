<?php

namespace App\Filament\Resources\LabIngredients\Schemas;

use App\Enums\DocumentType;
use App\Enums\OrganicCertificationStatus;
use App\Enums\UserRole;
use App\Models\Document;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\User;
use App\Services\CompanyAccessService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class LabIngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification fournisseur')
                    ->schema([
                        Select::make('company_id')
                            ->label('Société')
                            ->relationship('company', 'legal_name', modifyQueryUsing: fn (Builder $query): Builder => app(CompanyAccessService::class)->scopeCompanies($query, Filament::auth()->user()))
                            ->default(fn (): ?int => ($user = Filament::auth()->user()) instanceof User ? $user->company_id : null)
                            ->disabled(fn (): bool => Filament::auth()->user()?->role === UserRole::Adherent)
                            ->dehydrated()
                            ->searchable()->preload()->required(),
                        Select::make('ingredient_ref_id')
                            ->label('Ingrédient générique')
                            ->relationship('ingredientRef', 'display_name')
                            ->searchable(['display_name', 'normalized_inci'])
                            ->preload()
                            ->required()
                            ->live(),
                        Select::make('supplier_id')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'legal_name')
                            ->searchable()->preload()->required(),
                        TextInput::make('commercial_name')->label('Nom commercial')->required()->maxLength(255),
                        TextInput::make('supplier_reference')->label('Référence fournisseur')->maxLength(255),
                        TextInput::make('grade')->label('Grade')->maxLength(255),
                        Textarea::make('notes')->label('Notes')->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Caractéristiques biologiques')
                    ->description("L'origine décrit l'INCI générique. Le statut et le pourcentage bio décrivent cette matière commerciale et doivent être justifiés par les documents.")
                    ->schema([
                        Placeholder::make('generic_origin')
                            ->label("Origine de l'ingrédient générique")
                            ->content(fn (Get $get): string => IngredientRef::query()
                                ->whereKey($get('ingredient_ref_id'))
                                ->first()
                                ?->origin
                                ?->getLabel() ?? 'À déterminer'),
                        Placeholder::make('generic_organic_eligibility')
                            ->label('Éligibilité bio générique')
                            ->content(fn (Get $get): string => IngredientRef::query()
                                ->whereKey($get('ingredient_ref_id'))
                                ->first()
                                ?->organic_eligibility
                                ?->getLabel() ?? 'À déterminer'),
                        Select::make('organic_certification_status')
                            ->label('Statut biologique de la matière')
                            ->options(OrganicCertificationStatus::class)
                            ->default(OrganicCertificationStatus::Unknown)
                            ->required(),
                        TextInput::make('organic_content_percentage')
                            ->label('Part biologique certifiée')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Valeur issue du certificat ou de la fiche fournisseur. Elle servira au futur calcul de la formule.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Documents justificatifs')
                    ->description('Ajoutez les fiches techniques, certificats ou déclarations fournisseur. Les fichiers sont conservés dans le stockage privé.')
                    ->schema([
                        Repeater::make('documents')
                            ->label('Documents')
                            ->relationship()
                            ->helperText('Les documents déjà examinés sont figés ; ajoutez un nouveau document pour les remplacer.')
                            ->schema([
                                Select::make('type')
                                    ->label('Type')
                                    ->options(DocumentType::class)
                                    ->disabled(fn (?Document $record, mixed $livewire): bool => self::isReviewedDocument($record, $livewire))
                                    ->required(),
                                FileUpload::make('file_path')
                                    ->label('Fichier')
                                    ->disabled(fn (?Document $record, mixed $livewire): bool => self::isReviewedDocument($record, $livewire))
                                    ->disk('local')
                                    ->directory(fn (): string => 'documents/'.now()->format('Y/m'))
                                    ->visibility('private')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->rules(['mimes:pdf,jpg,jpeg,png,webp'])
                                    ->maxSize(1024)
                                    ->previewable(false)
                                    ->panelLayout('compact')
                                    ->storeFileNamesIn('original_name')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('PDF, JPG, PNG ou WebP — 1 Mo maximum.')
                                    ->required()
                                    ->columnSpanFull(),
                                DatePicker::make('received_date')
                                    ->label('Reçu / émis le')
                                    ->disabled(fn (?Document $record, mixed $livewire): bool => self::isReviewedDocument($record, $livewire))
                                    ->default(today())
                                    ->required(),
                                TextInput::make('issuer')
                                    ->label('Émetteur')
                                    ->disabled(fn (?Document $record, mixed $livewire): bool => self::isReviewedDocument($record, $livewire))
                                    ->maxLength(255),
                                DatePicker::make('valid_from')
                                    ->label('Valide du')
                                    ->disabled(fn (?Document $record, mixed $livewire): bool => self::isReviewedDocument($record, $livewire)),
                                DatePicker::make('valid_until')
                                    ->label("Valide jusqu'au")
                                    ->disabled(fn (?Document $record, mixed $livewire): bool => self::isReviewedDocument($record, $livewire))
                                    ->afterOrEqual('valid_from'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Ajouter un document')
                            ->deleteAction(fn (Action $action): Action => $action->visible(
                                function (array $arguments, Repeater $component, mixed $livewire): bool {
                                    $record = $component->getCachedExistingRecords()->get($arguments['item'] ?? '');

                                    return ! self::isReviewedDocument($record instanceof Document ? $record : null, $livewire);
                                },
                            ))
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Check a repeater document against the owner ingredient's reviewed IDs, loaded once per request.
     */
    private static function isReviewedDocument(?Document $document, mixed $livewire): bool
    {
        if ($document === null || ! $document->exists) {
            return false;
        }

        $ingredient = $livewire instanceof EditRecord ? $livewire->getRecord() : null;

        if (! $ingredient instanceof LabIngredient) {
            return $document->isReviewed();
        }

        return in_array($document->getKey(), $ingredient->reviewedDocumentIds(), true);
    }
}
