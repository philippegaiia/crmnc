<?php

use App\Enums\DocumentType;
use App\Enums\IngredientReviewDecision;
use App\Enums\IngredientReviewStatus;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\Document;
use App\Models\Formula;
use App\Models\FormulaLine;
use App\Models\FormulaVersion;
use App\Models\IngredientRef;
use App\Models\IngredientReview;
use App\Models\LabIngredient;
use App\Models\Product;
use App\Models\User;
use App\Services\IngredientReviewSubmissionService;
use App\Services\ProductReadinessService;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

it('defines the product readiness service', function () {
    expect(class_exists(ProductReadinessService::class))->toBeTrue();
});

it('counts unresolved formula lines instead of unrelated pending ingredient reviews', function () {
    $user = User::factory()->adherent()->create();
    $formula = Formula::factory()->for($user->company)->create();
    $version = FormulaVersion::factory()->for($formula)->create();
    $unresolvedReference = IngredientRef::factory()->create();
    $resolvedReference = IngredientRef::factory()->create();

    FormulaLine::factory()->for($version, 'version')->for($unresolvedReference, 'ingredient')->create([
        'percentage' => 40,
        'lab_ingredient_id' => null,
    ]);

    $pendingCandidate = LabIngredient::factory()
        ->for($user->company)
        ->for($unresolvedReference, 'ingredientRef')
        ->create();

    IngredientReview::factory()->for($pendingCandidate)->create([
        'status' => IngredientReviewStatus::UnderReview,
    ]);

    $approvedIngredient = LabIngredient::factory()
        ->for($user->company)
        ->for($resolvedReference, 'ingredientRef')
        ->create();

    Document::factory()
        ->for($approvedIngredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $approvedReview = app(IngredientReviewSubmissionService::class)->submit($approvedIngredient, $user);
    $approvedReview->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
        'valid_until' => now()->addYear(),
    ]);

    FormulaLine::factory()
        ->for($version, 'version')
        ->for($resolvedReference, 'ingredient')
        ->for($approvedIngredient, 'labIngredient')
        ->create(['percentage' => 60]);

    $product = Product::factory()->for($user->company)->create([
        'current_formula_version_id' => $version->getKey(),
    ]);

    $readiness = app(ProductReadinessService::class)->assess($product);
    $checks = collect($readiness['checks'])->keyBy('key');

    expect($checks['ingredient_assignments']['passed'])->toBeFalse()
        ->and($checks['ingredient_assignments']['detail'])
        ->toBe('1 ligne de formule sans ingrédient laboratoire affecté.')
        ->and($checks['ingredient_approvals']['passed'])->toBeTrue()
        ->and($checks['formula_total']['passed'])->toBeTrue();
});

it('blocks readiness when an assigned laboratory ingredient has no valid approval', function () {
    $user = User::factory()->adherent()->create();
    $formula = Formula::factory()->for($user->company)->create();
    $version = FormulaVersion::factory()->for($formula)->create();
    $ingredientReference = IngredientRef::factory()->create();
    $ingredient = LabIngredient::factory()
        ->for($user->company)
        ->for($ingredientReference, 'ingredientRef')
        ->create();

    IngredientReview::factory()->for($ingredient)->create([
        'status' => IngredientReviewStatus::UnderReview,
    ]);

    FormulaLine::factory()
        ->for($version, 'version')
        ->for($ingredientReference, 'ingredient')
        ->for($ingredient, 'labIngredient')
        ->create(['percentage' => 100]);

    $product = Product::factory()->for($user->company)->create([
        'current_formula_version_id' => $version->getKey(),
    ]);

    $readiness = app(ProductReadinessService::class)->assess($product);
    $checks = collect($readiness['checks'])->keyBy('key');

    expect($checks['ingredient_assignments']['passed'])->toBeTrue()
        ->and($checks['ingredient_approvals']['passed'])->toBeFalse()
        ->and($checks['ingredient_approvals']['detail'])
        ->toBe("1 ingrédient affecté n'a pas d'approbation valide.");
});

it('renders the product cockpit with precise unresolved formula lines', function () {
    $user = User::factory()->adherent()->create();
    $formula = Formula::factory()->for($user->company)->create();
    $version = FormulaVersion::factory()->for($formula)->create();

    FormulaLine::factory()->count(2)->for($version, 'version')->create([
        'lab_ingredient_id' => null,
        'percentage' => 50,
    ]);

    $product = Product::factory()->for($user->company)->create([
        'current_formula_version_id' => $version->getKey(),
    ]);

    $this->actingAs($user);
    Filament::setCurrentPanel('portal');

    livewire(ViewProduct::class, ['record' => $product->getRouteKey()])
        ->assertOk()
        ->assertSee('Cockpit produit')
        ->assertSee('Avant de soumettre')
        ->assertSee('2 lignes de formule sans ingrédient laboratoire affecté.')
        ->assertSee("Maquette à plat de l'étiquette");
});

it('blocks readiness when an approved ingredient changed after approval', function () {
    $user = User::factory()->adherent()->create();
    $formula = Formula::factory()->for($user->company)->create();
    $version = FormulaVersion::factory()->for($formula)->create();
    $ingredientReference = IngredientRef::factory()->create();
    $ingredient = LabIngredient::factory()
        ->for($user->company)
        ->for($ingredientReference, 'ingredientRef')
        ->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);
    $review->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
        'valid_until' => now()->addYear(),
    ]);

    FormulaLine::factory()
        ->for($version, 'version')
        ->for($ingredientReference, 'ingredient')
        ->for($ingredient, 'labIngredient')
        ->create(['percentage' => 100]);

    $product = Product::factory()->for($user->company)->create([
        'current_formula_version_id' => $version->getKey(),
    ]);

    $service = app(ProductReadinessService::class);

    expect(collect($service->assess($product)['checks'])->keyBy('key')['ingredient_approvals']['passed'])
        ->toBeTrue();

    $ingredient->update(['grade' => 'Nouveau grade']);

    $freshProduct = Product::query()->findOrFail($product->getKey());

    expect(collect($service->assess($freshProduct)['checks'])->keyBy('key')['ingredient_approvals']['passed'])
        ->toBeFalse();
});
