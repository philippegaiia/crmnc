<?php

use App\Enums\OrganicCertificationStatus;
use App\Models\Company;
use App\Models\Formula;
use App\Models\FormulaLine;
use App\Models\FormulaVersion;
use App\Models\IngredientCategory;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;

it('seeds a repeatable cosmetics certification activity dataset', function () {
    $this->seed();
    $this->seed();

    expect(Company::query()->count())->toBe(3)
        ->and(ProductCategory::query()->count())->toBe(5)
        ->and(IngredientCategory::query()->count())->toBe(8)
        ->and(IngredientRef::query()->count())->toBe(15)
        ->and(Supplier::query()->count())->toBe(4)
        ->and(LabIngredient::query()->count())->toBe(7)
        ->and(Formula::query()->count())->toBe(4)
        ->and(FormulaVersion::query()->count())->toBe(5)
        ->and(FormulaLine::query()->count())->toBe(24)
        ->and(Product::query()->count())->toBe(4)
        ->and(User::query()->whereIn('email', [
            'phil@mail.com',
            'gestionnaire@cosmebio.test',
            'evaluateur@cosmebio.test',
            'adherent@mediterranee.test',
            'adherent@maison-botanique.test',
        ])->count())->toBe(5);

    $product = Product::query()->where('name', 'Crème Visage Hydratante Bio')->firstOrFail();

    expect($product->currentFormulaVersion)->not->toBeNull()
        ->and($product->currentFormulaVersion->formula->company_id)->toBe($product->company_id)
        ->and($product->currentFormulaVersion->lines)->not->toBeEmpty();

    FormulaVersion::query()->with('lines')->each(function (FormulaVersion $version): void {
        expect((float) $version->lines->sum('percentage'))->toBe(100.0);
    });

    expect(FormulaLine::query()->whereNotNull('lab_ingredient_id')->exists())->toBeTrue()
        ->and(FormulaLine::query()->whereNull('lab_ingredient_id')->exists())->toBeTrue()
        ->and(LabIngredient::query()
            ->where('organic_certification_status', OrganicCertificationStatus::Certified)
            ->whereNotNull('organic_content_percentage')
            ->count())->toBeGreaterThanOrEqual(3);
});
