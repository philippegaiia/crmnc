<?php

use App\Enums\DocumentAccessLevel;
use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Enums\OrganicCertificationStatus;
use App\Filament\Resources\LabIngredients\Pages\CreateLabIngredient;
use App\Filament\Resources\LabIngredients\Pages\ListLabIngredients;
use App\Filament\Resources\LabIngredients\Pages\ViewLabIngredient;
use App\Models\Company;
use App\Models\Document;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\Supplier;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use function Pest\Livewire\livewire;

it('provides organic and private document storage for laboratory ingredients', function () {
    expect(Schema::hasColumns('lab_ingredients', [
        'organic_certification_status',
        'organic_content_percentage',
    ]))->toBeTrue()
        ->and(Schema::hasTable('documents'))->toBeTrue()
        ->and(Schema::hasColumns('documents', [
            'documentable_type',
            'documentable_id',
            'type',
            'file_path',
            'original_name',
            'mime_type',
            'size',
            'sha256',
            'version',
            'received_date',
            'issuer',
            'valid_from',
            'valid_until',
            'control_status',
            'access_level',
        ]))->toBeTrue();
});

it('stores organic certification data on laboratory ingredients', function () {
    $ingredient = LabIngredient::factory()->create([
        'organic_certification_status' => OrganicCertificationStatus::Partial,
        'organic_content_percentage' => 72.45,
    ]);

    expect($ingredient->organic_certification_status)->toBe(OrganicCertificationStatus::Partial)
        ->and($ingredient->organic_content_percentage)->toBe('72.45');
});

it('rejects an organic content percentage outside zero to one hundred', function (float $percentage) {
    expect(fn () => LabIngredient::factory()->create([
        'organic_content_percentage' => $percentage,
    ]))->toThrow(ValidationException::class);
})->with([-0.01, 100.01]);

it('registers private document metadata and checksum', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/technical-sheet.pdf', 'private technical content');

    $ingredient = LabIngredient::factory()->create();
    $document = $ingredient->documents()->create([
        'type' => DocumentType::TechnicalSheet,
        'file_path' => 'documents/technical-sheet.pdf',
        'original_name' => 'Fiche technique.pdf',
        'version' => 1,
        'received_date' => today(),
        'control_status' => DocumentControlStatus::Received,
        'access_level' => DocumentAccessLevel::Adherent,
    ]);

    expect($document)->toBeInstanceOf(Document::class)
        ->and($document->mime_type)->toBe('application/pdf')
        ->and($document->size)->toBe(strlen('private technical content'))
        ->and($document->sha256)->toBe(hash('sha256', 'private technical content'))
        ->and($document->documentable->is($ingredient))->toBeTrue();
});

it('ignores caller-supplied document metadata', function () {
    Storage::fake('local');

    $content = "%PDF-1.4\ncontrole serveur\n%%EOF\n";
    Storage::disk('local')->put('documents/server-metadata.pdf', $content);

    $ingredient = LabIngredient::factory()->create();
    $document = $ingredient->documents()->create([
        'type' => DocumentType::TechnicalSheet,
        'file_path' => 'documents/server-metadata.pdf',
        'original_name' => 'Fiche technique.pdf',
        'mime_type' => 'image/png',
        'size' => 1,
        'sha256' => str_repeat('0', 64),
        'version' => 1,
        'received_date' => today(),
        'control_status' => DocumentControlStatus::Received,
        'access_level' => DocumentAccessLevel::Adherent,
    ]);

    expect($document->refresh()->mime_type)->toBe('application/pdf')
        ->and($document->size)->toBe(strlen($content))
        ->and($document->sha256)->toBe(hash('sha256', $content));
});

it('recomputes metadata even when forced', function () {
    Storage::fake('local');

    $content = "%PDF-1.4\nforce brute\n%%EOF\n";
    Storage::disk('local')->put('documents/forced-metadata.pdf', $content);

    $ingredient = LabIngredient::factory()->create();
    $document = Document::forceCreate([
        'documentable_type' => LabIngredient::class,
        'documentable_id' => $ingredient->getKey(),
        'type' => DocumentType::TechnicalSheet,
        'file_path' => 'documents/forced-metadata.pdf',
        'original_name' => 'Fiche forcée.pdf',
        'mime_type' => 'image/png',
        'size' => 1,
        'sha256' => str_repeat('0', 64),
        'version' => 1,
        'received_date' => today(),
        'control_status' => DocumentControlStatus::Received,
        'access_level' => DocumentAccessLevel::Adherent,
    ]);

    expect($document->refresh()->mime_type)->toBe('application/pdf')
        ->and($document->size)->toBe(strlen($content))
        ->and($document->sha256)->toBe(hash('sha256', $content));
});

it('builds factory documents with a real stored file', function () {
    $document = Document::factory()->create();

    expect(Storage::disk('local')->exists($document->file_path))->toBeTrue()
        ->and($document->mime_type)->toBe('application/pdf')
        ->and($document->sha256)->toBe(hash('sha256', Storage::disk('local')->get($document->file_path)));
});

it('shows organic and document fields in the laboratory ingredient form', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(CreateLabIngredient::class)
        ->assertFormFieldExists('organic_certification_status')
        ->assertFormFieldExists('organic_content_percentage')
        ->assertFormFieldExists('documents', function (Repeater $repeater): bool {
            $fields = $repeater->getChildSchema()->getFlatFields(withHidden: true);
            $fileUpload = $fields['file_path'] ?? null;

            return isset($fields['type'])
                && $fileUpload instanceof FileUpload
                && $fileUpload->getMaxSize() === 1024
                && ! $fileUpload->isPreviewable()
                && $fileUpload->getPanelLayout() === 'compact';
        });
});

it('shows organic data and documents when listing and viewing a laboratory ingredient', function () {
    $admin = User::factory()->admin()->create();
    $ingredient = LabIngredient::factory()->create([
        'organic_certification_status' => OrganicCertificationStatus::Certified,
        'organic_content_percentage' => 100,
    ]);

    $this->actingAs($admin);

    livewire(ListLabIngredients::class)
        ->assertTableColumnExists('organic_certification_status')
        ->assertTableColumnExists('organic_content_percentage')
        ->assertTableColumnExists('documents_count');

    livewire(ViewLabIngredient::class, ['record' => $ingredient->getRouteKey()])
        ->assertSchemaComponentExists('organic_certification_status')
        ->assertSchemaComponentExists('organic_content_percentage')
        ->assertSchemaComponentExists('documents');
});

it('uploads a supporting document into private storage when creating an ingredient', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $ingredientRef = IngredientRef::factory()->create();
    $supplier = Supplier::factory()->create();
    $admin = User::factory()->admin()->create();
    $file = UploadedFile::fake()->createWithContent(
        'organic-certificate.pdf',
        "%PDF-1.4\nprivate certificate",
    );

    $this->actingAs($admin);

    livewire(CreateLabIngredient::class)
        ->fillForm([
            'company_id' => $company->getKey(),
            'ingredient_ref_id' => $ingredientRef->getKey(),
            'supplier_id' => $supplier->getKey(),
            'commercial_name' => 'Matière certifiée test',
            'organic_certification_status' => OrganicCertificationStatus::Certified->value,
            'organic_content_percentage' => 100,
            'documents' => [[
                'type' => DocumentType::Certificate->value,
                'file_path' => [$file],
                'received_date' => today()->toDateString(),
                'issuer' => 'Organisme de test',
            ]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::query()->sole();

    expect($document->documentable)->toBeInstanceOf(LabIngredient::class)
        ->and($document->original_name)->toBe('organic-certificate.pdf')
        ->and($document->mime_type)->toBe('application/pdf')
        ->and(Storage::disk('local')->exists($document->file_path))->toBeTrue();
});
