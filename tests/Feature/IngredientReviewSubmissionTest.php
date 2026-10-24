<?php

use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Enums\IngredientReviewDecision;
use App\Enums\IngredientReviewStatus;
use App\Filament\Resources\LabIngredients\Pages\EditLabIngredient;
use App\Models\Document;
use App\Models\IngredientReview;
use App\Models\LabIngredient;
use App\Models\User;
use App\Services\IngredientReviewSubmissionService;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

use function Pest\Livewire\livewire;

it('provides immutable ingredient review submission storage', function () {
    expect(Schema::hasTable('ingredient_reviews'))->toBeTrue()
        ->and(Schema::hasColumns('ingredient_reviews', [
            'lab_ingredient_id',
            'submitted_by',
            'submitted_at',
            'reviewer_id',
            'status',
            'decision',
            'decided_at',
            'comment',
            'submission_snapshot',
            'submitted_document_ids',
            'submitted_fingerprint',
            'valid_until',
        ]))->toBeTrue();
});

it('submits an ingredient with its exact snapshot and evidence documents', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $document = Document::factory()
        ->for($ingredient, 'documentable')
        ->create([
            'type' => DocumentType::Certificate,
            'control_status' => DocumentControlStatus::Received,
        ]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);

    expect($review)->toBeInstanceOf(IngredientReview::class)
        ->and($review->status)->toBe(IngredientReviewStatus::Submitted)
        ->and($review->submitted_by)->toBe($user->getKey())
        ->and($review->submitted_document_ids)->toBe([$document->getKey()])
        ->and($review->submission_snapshot)->toMatchArray([
            'ingredient_ref_id' => $ingredient->ingredient_ref_id,
            'supplier_id' => $ingredient->supplier_id,
            'commercial_name' => $ingredient->commercial_name,
            'supplier_reference' => $ingredient->supplier_reference,
            'grade' => $ingredient->grade,
            'organic_certification_status' => $ingredient->organic_certification_status->value,
            'organic_content_percentage' => $ingredient->organic_content_percentage,
            'components' => [],
        ])
        ->and($review->submitted_fingerprint)->toHaveLength(64)
        ->and($document->refresh()->control_status)->toBe(DocumentControlStatus::ToVerify);
});

it('requires a technical sheet or certificate before submission', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Other]);

    expect(fn () => app(IngredientReviewSubmissionService::class)->submit($ingredient, $user))
        ->toThrow(ValidationException::class);
});

it('prevents duplicate pending reviews for the same ingredient', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::TechnicalSheet]);

    $service = app(IngredientReviewSubmissionService::class);
    $service->submit($ingredient, $user);

    expect(fn () => $service->submit($ingredient, $user))
        ->toThrow(ValidationException::class)
        ->and($ingredient->reviews()->count())->toBe(1);
});

it('prevents an adherent from submitting another company ingredient', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    expect(fn () => app(IngredientReviewSubmissionService::class)->submit($ingredient, $user))
        ->toThrow(ValidationException::class);
});

it('freezes submitted ingredient data and evidence while review is pending', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $document = Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);

    expect(fn () => $ingredient->update(['commercial_name' => 'Changed after submission']))
        ->toThrow(ValidationException::class)
        ->and(fn () => $document->update(['issuer' => 'Changed issuer']))
        ->toThrow(ValidationException::class)
        ->and(fn () => $document->delete())
        ->toThrow(ValidationException::class)
        ->and(fn () => Document::factory()
            ->for($ingredient, 'documentable')
            ->create(['type' => DocumentType::Other]))
        ->toThrow(ValidationException::class);
});

it('keeps reviewed documents immutable after the review is decided', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $document = Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);
    $review->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
    ]);

    expect(fn () => $document->update(['issuer' => 'x']))
        ->toThrow(ValidationException::class)
        ->and(fn () => $document->delete())
        ->toThrow(ValidationException::class);

    $document->refresh()->update(['control_status' => DocumentControlStatus::Validated]);

    expect($document->refresh()->control_status)->toBe(DocumentControlStatus::Validated);
});

it('allows adding a new document after the review is decided', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);
    $review->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
    ]);

    $newDocument = Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Other]);

    expect($newDocument->exists)->toBeTrue()
        ->and($newDocument->isReviewed())->toBeFalse();
});

it('still allows editing documents that were never reviewed', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);
    $review->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
    ]);

    $document = Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Other]);

    $document->update(['issuer' => 'Nouvel émetteur']);
    $document->delete();

    expect(Document::query()->whereKey($document->getKey())->exists())->toBeFalse();
});

it('does not delete a reviewed document from the edit form', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $document = Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);
    $review->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
    ]);

    $this->actingAs($user);
    Filament::setCurrentPanel('portal');

    livewire(EditLabIngredient::class, ['record' => $ingredient->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertActionHidden(
            TestAction::make('delete')
                ->schemaComponent('documents', 'form')
                ->arguments(['item' => "record-{$document->getKey()}"]),
        );

    expect(Document::query()->whereKey($document->getKey())->exists())->toBeTrue();
});

it('submits an ingredient from its Filament edit action', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $this->actingAs($user);
    Filament::setCurrentPanel('portal');

    livewire(EditLabIngredient::class, ['record' => $ingredient->getRouteKey()])
        ->assertActionExists('submitForReview')
        ->callAction('submitForReview')
        ->assertNotified();

    expect($ingredient->reviews()->sole()->status)->toBe(IngredientReviewStatus::Submitted);
});

/**
 * Create an ingredient for the adherent's company with certificate documents and an approved review.
 *
 * @return array{0: LabIngredient, 1: list<Document>}
 */
function createApprovedIngredientWithDocuments(User $user, int $documentCount = 1): array
{
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $documents = Document::factory()
        ->count($documentCount)
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate])
        ->all();

    app(IngredientReviewSubmissionService::class)->submit($ingredient, $user)->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Approved,
        'decided_at' => now(),
    ]);

    return [$ingredient, $documents];
}

it('keeps a reviewed document immutable when it is moved to another ingredient', function () {
    $user = User::factory()->adherent()->create();
    [$ingredient, [$document]] = createApprovedIngredientWithDocuments($user);
    $otherIngredient = LabIngredient::factory()->for($user->company)->create();

    expect(fn () => $document->forceFill(['documentable_id' => $otherIngredient->getKey()])->save())
        ->toThrow(ValidationException::class)
        ->and(fn () => $document->refresh()->forceFill([
            'documentable_id' => $otherIngredient->getKey(),
            'issuer' => 'Émetteur falsifié',
        ])->save())
        ->toThrow(ValidationException::class);

    $document->refresh()->documentable_id = $otherIngredient->getKey();

    expect(fn () => $document->delete())->toThrow(ValidationException::class)
        ->and($document->refresh()->documentable_id)->toBe($ingredient->getKey())
        ->and($document->issuer)->not->toBe('Émetteur falsifié');
});

it('prevents moving a document into an ingredient under pending review', function () {
    $user = User::factory()->adherent()->create();
    $pendingIngredient = LabIngredient::factory()->for($user->company)->create();
    Document::factory()
        ->for($pendingIngredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);
    app(IngredientReviewSubmissionService::class)->submit($pendingIngredient, $user);

    $looseDocument = Document::factory()
        ->for(LabIngredient::factory()->for($user->company), 'documentable')
        ->create(['type' => DocumentType::Other]);

    expect(fn () => $looseDocument->forceFill(['documentable_id' => $pendingIngredient->getKey()])->save())
        ->toThrow(ValidationException::class);
});

it('loads reviewed documents once per request on the edit form', function () {
    $user = User::factory()->adherent()->create();
    [$singleDocumentIngredient] = createApprovedIngredientWithDocuments($user, 1);
    [$manyDocumentsIngredient] = createApprovedIngredientWithDocuments($user, 5);

    $this->actingAs($user);
    Filament::setCurrentPanel('portal');

    $countReviewQueries = function (LabIngredient $ingredient): int {
        DB::flushQueryLog();
        DB::enableQueryLog();

        livewire(EditLabIngredient::class, ['record' => $ingredient->getRouteKey()])->assertOk();

        $count = collect(DB::getQueryLog())
            ->filter(fn (array $query): bool => str_contains($query['query'], 'ingredient_reviews'))
            ->count();
        DB::disableQueryLog();

        return $count;
    };

    expect($countReviewQueries($manyDocumentsIngredient))
        ->toBe($countReviewQueries($singleDocumentIngredient))
        ->toBeLessThanOrEqual(5);
});
