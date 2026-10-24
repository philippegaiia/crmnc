<?php

use App\Enums\ContactRole;
use App\Enums\UserRole;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Contacts\Pages\CreateContact;
use App\Filament\Resources\Formulas\FormulaResource;
use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use App\Filament\Resources\LabIngredients\LabIngredientResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

use function Pest\Livewire\livewire;

it('separates internal and adherent panel access', function (UserRole $role, bool $adminAccess, bool $portalAccess) {
    $company = $role === UserRole::Adherent ? Company::factory()->create() : null;
    $user = User::factory()->create([
        'role' => $role,
        'company_id' => $company?->getKey(),
    ]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBe($adminAccess)
        ->and($user->canAccessPanel(Filament::getPanel('portal')))->toBe($portalAccess);

    $user->update(['is_active' => false]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse()
        ->and($user->canAccessPanel(Filament::getPanel('portal')))->toBeFalse();
})->with([
    'administrator' => [UserRole::Admin, true, false],
    'association manager' => [UserRole::Gestionnaire, true, false],
    'scientific evaluator' => [UserRole::Evaluateur, true, false],
    'adherent' => [UserRole::Adherent, false, true],
]);

it('registers only company-scoped resources in the adherent portal', function () {
    $portal = Filament::getPanel('portal');

    expect($portal->getResources())->toBe([
        CompanyResource::class,
        ContactResource::class,
        ProductResource::class,
        FormulaResource::class,
        FormulaVersionResource::class,
        LabIngredientResource::class,
    ])->and(Route::has('filament.portal.resources.companies.index'))->toBeTrue()
        ->and(Route::has('filament.portal.resources.contacts.index'))->toBeTrue()
        ->and(Route::has('filament.portal.resources.products.index'))->toBeTrue()
        ->and(Route::has('filament.portal.resources.formulas.index'))->toBeTrue()
        ->and(Route::has('filament.portal.resources.formula-versions.index'))->toBeTrue()
        ->and(Route::has('filament.portal.resources.lab-ingredients.index'))->toBeTrue()
        ->and(Route::has('filament.portal.resources.users.index'))->toBeFalse()
        ->and(Route::has('filament.portal.resources.ingredient-refs.index'))->toBeFalse()
        ->and(Route::has('filament.portal.resources.ingredient-categories.index'))->toBeFalse()
        ->and(Route::has('filament.portal.resources.product-categories.index'))->toBeFalse()
        ->and(Route::has('filament.portal.resources.suppliers.index'))->toBeFalse();
});

it('enforces panel boundaries on panel dashboards', function () {
    $admin = User::factory()->admin()->create();
    $adherent = User::factory()->adherent()->create();

    $this->actingAs($admin)->get('/admin')->assertSuccessful();
    $this->get('/portal')->assertForbidden();

    $this->actingAs($adherent)->get('/portal')->assertSuccessful();
    $this->get('/admin')->assertForbidden();
});

it('keeps portal company records isolated', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $adherent = User::factory()->adherent($ownCompany)->create();

    $this->actingAs($adherent)
        ->get(route('filament.portal.resources.companies.edit', $ownCompany))
        ->assertSuccessful();

    $this->get(route('filament.portal.resources.companies.edit', $otherCompany))
        ->assertNotFound();
});

it('rejects attempts to create portal records for another company', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $adherent = User::factory()->adherent($ownCompany)->create();

    $this->actingAs($adherent);
    Filament::setCurrentPanel('portal');

    livewire(CreateContact::class)
        ->fillForm([
            'company_id' => $otherCompany->getKey(),
            'first_name' => 'Portal',
            'last_name' => 'Contact',
            'role' => ContactRole::Qualite->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['company_id']);

    expect(Contact::query()->where('first_name', 'Portal')->exists())->toBeFalse();
});
