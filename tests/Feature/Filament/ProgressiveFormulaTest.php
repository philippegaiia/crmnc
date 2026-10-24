<?php

use App\Models\Formula;
use App\Models\FormulaLine;
use App\Models\FormulaVersion;
use App\Models\IngredientCategory;
use App\Models\IngredientRef;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\FormulaVersionCreationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

it('provides the progressive formula schema', function () {
    expect(Schema::hasTable('product_categories'))->toBeTrue()
        ->and(Schema::hasTable('company_product_category'))->toBeTrue()
        ->and(Schema::hasTable('ingredient_categories'))->toBeTrue()
        ->and(Schema::hasTable('ingredient_refs'))->toBeTrue()
        ->and(Schema::hasTable('suppliers'))->toBeTrue()
        ->and(Schema::hasTable('formulas'))->toBeTrue()
        ->and(Schema::hasTable('formula_versions'))->toBeTrue()
        ->and(Schema::hasTable('products'))->toBeTrue()
        ->and(Schema::hasTable('formula_lines'))->toBeTrue();
});

it('defines the progressive formula domain models', function () {
    expect(class_exists(ProductCategory::class))->toBeTrue()
        ->and(class_exists(IngredientCategory::class))->toBeTrue()
        ->and(class_exists(IngredientRef::class))->toBeTrue()
        ->and(class_exists(Supplier::class))->toBeTrue()
        ->and(class_exists(Formula::class))->toBeTrue()
        ->and(class_exists(FormulaVersion::class))->toBeTrue()
        ->and(class_exists(Product::class))->toBeTrue()
        ->and(class_exists(FormulaLine::class))->toBeTrue();
});

it('builds complete progressive formula factory records', function () {
    expect(ProductCategory::factory()->make()->name)->not->toBeNull()
        ->and(IngredientCategory::factory()->make()->name)->not->toBeNull()
        ->and(IngredientRef::factory()->make()->normalized_inci)->not->toBeNull()
        ->and(Supplier::factory()->make()->legal_name)->not->toBeNull()
        ->and(Formula::factory()->make()->internal_code)->not->toBeNull()
        ->and(FormulaVersion::factory()->make()->version_number)->toBe(1)
        ->and(Product::factory()->make()->name)->not->toBeNull()
        ->and(FormulaLine::factory()->make()->ingredient_ref_id)->not->toBeNull();
});

it('creates sequential draft versions for a formula', function () {
    $user = User::factory()->create();
    $formula = Formula::factory()->for($user->company)->create();

    $firstVersion = app(FormulaVersionCreationService::class)->createDraft($formula, $user);
    $secondVersion = app(FormulaVersionCreationService::class)->createDraft($formula, $user);

    expect($firstVersion->version_number)->toBe(1)
        ->and($secondVersion->version_number)->toBe(2)
        ->and($firstVersion->created_by)->toBe($user->id);
});

it('scopes formulas products and versions to an adherent company', function () {
    $user = User::factory()->create();
    $ownFormula = Formula::factory()->for($user->company)->create();
    $otherFormula = Formula::factory()->create();
    FormulaVersion::factory()->for($ownFormula)->create();
    FormulaVersion::factory()->for($otherFormula)->create();
    Product::factory()->for($user->company)->create();
    Product::factory()->create();

    expect(Formula::query()->visibleTo($user)->pluck('id')->all())->toBe([$ownFormula->id])
        ->and(FormulaVersion::query()->visibleTo($user)->count())->toBe(1)
        ->and(Product::query()->visibleTo($user)->count())->toBe(1);
});

it('rejects a product formula version owned by another company', function () {
    $product = Product::factory()->make();
    $otherVersion = FormulaVersion::factory()->create();
    $product->current_formula_version_id = $otherVersion->id;

    expect(fn () => $product->save())->toThrow(ValidationException::class);
});
