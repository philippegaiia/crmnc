<?php

namespace App\Filament\Resources\FormulaVersions\Schemas;

use App\Models\Formula;
use App\Models\FormulaLine;
use App\Models\LabIngredient;
use App\Models\Supplier;
use App\Services\CompanyAccessService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Number;

class FormulaVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Version')
                    ->schema([
                        Select::make('formula_id')->label('Formule')
                            ->relationship('formula', 'internal_code', modifyQueryUsing: fn (Builder $query): Builder => app(CompanyAccessService::class)->scopeToUserCompany($query, Filament::auth()->user()))
                            ->required()->searchable()->preload()->disabledOn('edit'),
                        TextInput::make('version_number')->label('Version')->numeric()->disabled()->hiddenOn('create'),
                        Textarea::make('version_note')->label('Note')->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
                Section::make('Composition')
                    ->schema([
                        Repeater::make('lines')
                            ->label('Ingrédients')
                            ->relationship()
                            ->schema([
                                Select::make('ingredient_ref_id')
                                    ->label('Ingrédient générique')
                                    ->relationship('ingredient', 'display_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set): mixed => $set('lab_ingredient_id', null))
                                    ->columnSpan(2),
                                Select::make('lab_ingredient_id')
                                    ->label('Ingrédient laboratoire')
                                    ->relationship(
                                        'labIngredient',
                                        'commercial_name',
                                        modifyQueryUsing: fn (Builder $query, Get $get, ?FormulaLine $record): Builder => $query
                                            ->where('company_id', self::lineCompanyId($get, $record))
                                            ->where('ingredient_ref_id', $get('ingredient_ref_id')),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (LabIngredient $record): string => collect([
                                        $record->commercial_name,
                                        $record->supplier_reference,
                                        $record->grade,
                                    ])->filter()->join(' · '))
                                    ->searchable(['commercial_name', 'supplier_reference'])
                                    ->preload()
                                    ->helperText('Facultatif : les ingrédients à soumettre ou en cours de validation restent sélectionnables.')
                                    ->createOptionForm([
                                        Select::make('supplier_id')
                                            ->label('Fournisseur')
                                            ->options(fn (): array => Supplier::query()->where('is_active', true)->orderBy('legal_name')->pluck('legal_name', 'id')->all())
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('commercial_name')->label('Nom commercial')->required()->maxLength(255),
                                        TextInput::make('supplier_reference')->label('Référence fournisseur')->maxLength(255),
                                        TextInput::make('grade')->label('Grade')->maxLength(255),
                                        Textarea::make('notes')->label('Notes')->columnSpanFull(),
                                    ])
                                    ->createOptionUsing(function (array $data, Get $get, ?FormulaLine $record): int {
                                        $labIngredient = LabIngredient::query()->create([
                                            ...$data,
                                            'company_id' => self::lineCompanyId($get, $record) ?? throw new ModelNotFoundException,
                                            'ingredient_ref_id' => $get('ingredient_ref_id'),
                                        ]);

                                        return $labIngredient->getKey();
                                    })
                                    ->columnSpan(2),
                                TextInput::make('percentage')
                                    ->label('%')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->live(onBlur: true),
                            ])->columns(5)->orderColumn('position')->columnSpanFull(),
                        TextEntry::make('formula_total')
                            ->label('Total de la formule')
                            ->state(fn (Get $get): string => Number::format(
                                self::percentageTotal($get->array('lines')),
                                precision: 0,
                                maxPrecision: 4,
                                locale: 'fr',
                            ).' %')
                            ->badge()
                            ->color(fn (Get $get): string => match (true) {
                                self::percentageTotal($get->array('lines')) > 100 => 'danger',
                                abs(self::percentageTotal($get->array('lines')) - 100) < 0.0001 => 'success',
                                default => 'warning',
                            })
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }

    /**
     * Resolve the line's company through the user's accessible formulas only.
     */
    private static function lineCompanyId(Get $get, ?FormulaLine $record): ?int
    {
        $formulaId = $record?->version->formula_id ?? $get('../../formula_id');

        if (blank($formulaId)) {
            return null;
        }

        $companyId = app(CompanyAccessService::class)
            ->scopeToUserCompany(Formula::query(), Filament::auth()->user())
            ->whereKey($formulaId)
            ->value('company_id');

        return $companyId === null ? null : (int) $companyId;
    }

    /** @param array<array-key, mixed> $lines */
    private static function percentageTotal(array $lines): float
    {
        $total = 0.0;

        foreach ($lines as $line) {
            if (is_array($line)) {
                $total += (float) ($line['percentage'] ?? 0);
            }
        }

        return $total;
    }
}
