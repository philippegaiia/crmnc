<?php

use App\Enums\UserRole;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Contacts\Pages\CreateContact;
use App\Filament\Resources\Contacts\Pages\EditContact;
use App\Filament\Resources\Contacts\Pages\ListContacts;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Country;
use App\Models\User;
use App\Services\CompanyAccessService;
use Database\Seeders\CountrySeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

use function Pest\Livewire\livewire;

it('provides the company boundary schema', function () {
    expect(Schema::hasTable('countries'))->toBeTrue()
        ->and(Schema::hasTable('companies'))->toBeTrue()
        ->and(Schema::hasTable('contacts'))->toBeTrue()
        ->and(Schema::hasColumns('users', ['role', 'company_id', 'is_active']))->toBeTrue();
});

it('defines the company boundary models', function () {
    expect(class_exists(Country::class))->toBeTrue()
        ->and(class_exists(Company::class))->toBeTrue()
        ->and(class_exists(Contact::class))->toBeTrue();
});

it('defines the role and Filament company boundary', function () {
    expect(class_exists(UserRole::class))->toBeTrue()
        ->and(class_exists(CompanyResource::class))->toBeTrue()
        ->and(class_exists(ContactResource::class))->toBeTrue()
        ->and(class_exists(UserResource::class))->toBeTrue()
        ->and(class_exists(CompanyAccessService::class))->toBeTrue();
});

it('builds complete company boundary factory records', function () {
    $country = Country::factory()->make();
    $company = Company::factory()->make();
    $contact = Contact::factory()->make();

    expect($country->code)->not->toBeNull()
        ->and($country->name_fr)->not->toBeNull()
        ->and($company->legal_name)->not->toBeNull()
        ->and($company->country_code)->not->toBeNull()
        ->and($contact->first_name)->not->toBeNull()
        ->and($contact->role)->not->toBeNull();
});

it('defines role-aware panel access on users', function () {
    expect(method_exists(User::class, 'canAccessPanel'))->toBeTrue();
});

it('allows active internal roles into the admin panel and rejects adherents and inactive users', function (UserRole $role) {
    $company = $role === UserRole::Adherent ? Company::factory()->create() : null;
    $user = User::factory()->create([
        'role' => $role,
        'company_id' => $company?->getKey(),
    ]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))
        ->toBe($role !== UserRole::Adherent);

    $user->update(['is_active' => false]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
})->with(UserRole::cases());

it('rejects an adherent without a company', function () {
    expect(fn () => User::factory()->create([
        'role' => UserRole::Adherent,
        'company_id' => null,
    ]))->toThrow(ValidationException::class);
});

it('rejects an internal user with a company', function () {
    $company = Company::factory()->create();

    expect(fn () => User::factory()->create([
        'role' => UserRole::Admin,
        'company_id' => $company->getKey(),
    ]))->toThrow(ValidationException::class);
});

it('prevents an adherent from listing or opening another company', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $adherent = User::factory()->adherent($ownCompany)->create();

    $this->actingAs($adherent);

    expect($adherent->can('view', $ownCompany))->toBeTrue()
        ->and($adherent->can('view', $otherCompany))->toBeFalse()
        ->and(CompanyResource::getEloquentQuery()->pluck('id')->all())->toBe([$ownCompany->getKey()]);

    livewire(ListCompanies::class)
        ->assertCanSeeTableRecords([$ownCompany])
        ->assertCanNotSeeTableRecords([$otherCompany]);

    expect(fn () => livewire(EditCompany::class, ['record' => $otherCompany->getRouteKey()]))
        ->toThrow(ModelNotFoundException::class);
});

it('enforces one active primary contact per company', function () {
    $company = Company::factory()->create();

    Contact::factory()->for($company)->create([
        'is_primary' => true,
        'is_active' => true,
    ]);

    expect(fn () => Contact::factory()->for($company)->create([
        'is_primary' => true,
        'is_active' => true,
    ]))->toThrow(QueryException::class);
});

it('prevents an adherent from listing or opening another company contact', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $ownContact = Contact::factory()->for($ownCompany)->create();
    $otherContact = Contact::factory()->for($otherCompany)->create();
    $adherent = User::factory()->adherent($ownCompany)->create();

    $this->actingAs($adherent);

    expect($adherent->can('view', $ownContact))->toBeTrue()
        ->and($adherent->can('view', $otherContact))->toBeFalse()
        ->and(ContactResource::getEloquentQuery()->pluck('id')->all())->toBe([$ownContact->getKey()]);

    livewire(ListContacts::class)
        ->assertCanSeeTableRecords([$ownContact])
        ->assertCanNotSeeTableRecords([$otherContact]);

    expect(fn () => livewire(EditContact::class, ['record' => $otherContact->getRouteKey()]))
        ->toThrow(ModelNotFoundException::class);
});

it('provides the required company form fields', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(CreateCompany::class)
        ->assertFormFieldExists('legal_name')
        ->assertFormFieldExists('commercial_status')
        ->assertFormFieldExists('country_code');
});

it('provides the required contact form fields', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(CreateContact::class)
        ->assertFormFieldExists('company_id')
        ->assertFormFieldExists('first_name')
        ->assertFormFieldExists('last_name')
        ->assertFormFieldExists('role');
});

it('allows only administrators to manage users', function () {
    $admin = User::factory()->admin()->create();
    $adherent = User::factory()->create();

    expect($admin->can('viewAny', User::class))->toBeTrue()
        ->and($adherent->can('viewAny', User::class))->toBeFalse();

    $this->actingAs($adherent);

    livewire(ListUsers::class)->assertForbidden();
});

it('provides role and company fields when creating users', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(CreateUser::class)
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('email')
        ->assertFormFieldExists('role')
        ->assertFormFieldExists('company_id')
        ->assertFormFieldExists('is_active');
});

it('seeds the supported countries', function () {
    $this->seed(CountrySeeder::class);

    expect(Country::query()->count())->toBe(10)
        ->and(Country::query()->findOrFail('FR')->name_fr)->toBe('France');
});
