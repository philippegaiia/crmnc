<?php

use App\Enums\FormulaVersionStatus;
use App\Enums\ProductCommercialStatus;
use App\Filament\Resources\Formulas\Pages\CreateFormula;
use App\Filament\Resources\Formulas\Pages\EditFormula;
use App\Filament\Resources\FormulaVersions\Pages\CreateFormulaVersion;
use App\Filament\Resources\FormulaVersions\Pages\EditFormulaVersion;
use App\Filament\Resources\LabIngredients\Pages\CreateLabIngredient;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Company;
use App\Models\Formula;
use App\Models\FormulaVersion;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->ownCompany = Company::factory()->create(['legal_name' => 'Laboratoire Propre']);
    $this->otherCompany = Company::factory()->create(['legal_name' => 'Laboratoire Concurrent']);
    $this->adherent = User::factory()->adherent($this->ownCompany)->create();

    $this->actingAs($this->adherent);
    Filament::setCurrentPanel('portal');
});

it('does not expose other companies in the formula company select', function () {
    livewire(CreateFormula::class)
        ->assertOk()
        ->assertSee('Laboratoire Propre')
        ->assertDontSee('Laboratoire Concurrent');
});

it('rejects a tampered company on formula creation', function () {
    livewire(CreateFormula::class)
        ->fillForm([
            'company_id' => $this->otherCompany->getKey(),
            'internal_code' => 'TAMPERED-FORMULA',
        ])
        ->call('create')
        ->assertHasFormErrors(['company_id']);

    expect(Formula::query()->where('internal_code', 'TAMPERED-FORMULA')->exists())->toBeFalse();
});

it('rejects moving an own formula to another company', function () {
    $formula = Formula::factory()->for($this->ownCompany)->create();

    livewire(EditFormula::class, ['record' => $formula->getRouteKey()])
        ->fillForm(['company_id' => $this->otherCompany->getKey()])
        ->call('save')
        ->assertHasFormErrors(['company_id']);

    expect($formula->refresh()->company_id)->toBe($this->ownCompany->getKey());
});

it('rejects a tampered company on product creation', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'company_id' => $this->otherCompany->getKey(),
            'name' => 'Produit falsifié',
            'brand' => 'Marque',
            'commercial_status' => ProductCommercialStatus::Brouillon->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['company_id']);

    expect(Product::query()->where('name', 'Produit falsifié')->exists())->toBeFalse();
});

it('rejects a tampered company on laboratory ingredient creation', function () {
    livewire(CreateLabIngredient::class)
        ->assertDontSee('Laboratoire Concurrent')
        ->fillForm([
            'company_id' => $this->otherCompany->getKey(),
            'ingredient_ref_id' => IngredientRef::factory()->create()->getKey(),
            'supplier_id' => Supplier::factory()->create()->getKey(),
            'commercial_name' => 'Ingrédient falsifié',
            'documents' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['company_id']);

    expect(LabIngredient::query()->where('commercial_name', 'Ingrédient falsifié')->exists())->toBeFalse();
});

it('does not expose other companies formulas in the version formula select', function () {
    Formula::factory()->for($this->ownCompany)->create(['internal_code' => 'OWN-FORMULA']);
    Formula::factory()->for($this->otherCompany)->create(['internal_code' => 'FOREIGN-FORMULA']);

    livewire(CreateFormulaVersion::class)
        ->assertOk()
        ->assertSee('OWN-FORMULA')
        ->assertDontSee('FOREIGN-FORMULA');
});

it('rejects creating a version on another company formula', function () {
    $foreignFormula = Formula::factory()->for($this->otherCompany)->create();

    livewire(CreateFormulaVersion::class)
        ->fillForm([
            'formula_id' => $foreignFormula->getKey(),
            'lines' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['formula_id']);

    expect(FormulaVersion::query()->where('formula_id', $foreignFormula->getKey())->exists())->toBeFalse();
});

it('does not allow the workflow status to be edited through the version form', function () {
    $version = FormulaVersion::factory()
        ->for(Formula::factory()->for($this->ownCompany))
        ->create();

    livewire(CreateFormulaVersion::class)
        ->assertFormFieldDoesNotExist('workflow_status');

    livewire(EditFormulaVersion::class, ['record' => $version->getRouteKey()])
        ->assertFormFieldDoesNotExist('workflow_status');
});

it('creates versions as numbered drafts through the creation service', function () {
    $formula = Formula::factory()->for($this->ownCompany)->create();
    FormulaVersion::factory()->for($formula)->create([
        'version_number' => 1,
        'workflow_status' => FormulaVersionStatus::Accepte,
    ]);

    livewire(CreateFormulaVersion::class)
        ->fillForm([
            'formula_id' => $formula->getKey(),
            'workflow_status' => FormulaVersionStatus::Accepte->value,
            'version_number' => 99,
            'lines' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $createdVersion = $formula->versions()->latest('id')->firstOrFail();

    expect($createdVersion->version_number)->toBe(2)
        ->and($createdVersion->workflow_status)->toBe(FormulaVersionStatus::Brouillon)
        ->and($createdVersion->created_by)->toBe($this->adherent->getKey());
});
