<?php

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\Formulas\FormulaResource;
use App\Filament\Resources\Formulas\RelationManagers\VersionsRelationManager;
use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use App\Filament\Resources\FormulaVersions\Pages\EditFormulaVersion;
use App\Models\Company;
use App\Models\Formula;
use App\Models\FormulaLine;
use App\Models\FormulaVersion;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

use function Pest\Livewire\livewire;

it('provides laboratory ingredients and links them to formula lines', function () {
    expect(Schema::hasTable('lab_ingredients'))->toBeTrue()
        ->and(Schema::hasColumn('formula_lines', 'lab_ingredient_id'))->toBeTrue();

    $company = Company::factory()->create();
    $ingredientRef = IngredientRef::factory()->create();
    $version = FormulaVersion::factory()->for(Formula::factory()->for($company))->create();
    $labIngredient = LabIngredient::factory()
        ->for($company)
        ->for($ingredientRef)
        ->for(Supplier::factory())
        ->create();

    $line = FormulaLine::factory()
        ->for($version, 'version')
        ->for($ingredientRef, 'ingredient')
        ->for($labIngredient, 'labIngredient')
        ->create();

    expect($line->labIngredient->is($labIngredient))->toBeTrue();
});

it('rejects a laboratory ingredient from another company or generic reference', function () {
    $version = FormulaVersion::factory()->create();
    $ingredientRef = IngredientRef::factory()->create();
    $unrelatedLabIngredient = LabIngredient::factory()->create();

    expect(fn () => FormulaLine::factory()
        ->for($version, 'version')
        ->for($ingredientRef, 'ingredient')
        ->for($unrelatedLabIngredient, 'labIngredient')
        ->create())->toThrow(ValidationException::class);
});

it('registers formula versions and company contacts relation managers', function () {
    expect(FormulaResource::getRelations())->toContain(VersionsRelationManager::class)
        ->and(CompanyResource::getRelations())->toContain(ContactsRelationManager::class);
});

it('renders the relation managers on company and formula view pages', function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $company = Company::factory()->create();
    $formula = Formula::factory()->for($company)->create();

    $this->get(CompanyResource::getUrl('view', ['record' => $company]))
        ->assertSuccessful()
        ->assertSeeLivewire(ContactsRelationManager::class);

    $this->get(FormulaResource::getUrl('view', ['record' => $formula]))
        ->assertSuccessful()
        ->assertSeeLivewire(VersionsRelationManager::class);
});

it('renders the laboratory ingredient selector in the formula version editor', function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $version = FormulaVersion::factory()->create();
    $line = FormulaLine::factory()->for($version, 'version')->create(['percentage' => 73.2]);

    $this->get(FormulaVersionResource::getUrl('edit', ['record' => $version]))
        ->assertSuccessful()
        ->assertSee('Ingrédient laboratoire')
        ->assertSee('Total de la formule')
        ->assertSee('73,2 %')
        ->assertDontSee('QSP')
        ->assertDontSee('Commentaire');

    livewire(EditFormulaVersion::class, ['record' => $version->id])
        ->assertSee('73,2 %')
        ->set("data.lines.record-{$line->id}.percentage", 80)
        ->assertSee('80 %');
});
