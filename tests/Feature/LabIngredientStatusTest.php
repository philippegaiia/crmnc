<?php

use App\Enums\DocumentType;
use App\Enums\IngredientReviewDecision;
use App\Enums\IngredientReviewStatus;
use App\Enums\LabIngredientStatus;
use App\Models\Document;
use App\Models\IngredientRef;
use App\Models\IngredientReview;
use App\Models\LabIngredient;
use App\Models\Supplier;
use App\Models\User;
use App\Services\IngredientReviewSubmissionService;
use App\Services\LabIngredientStatusService;

beforeEach(function () {
    $this->approveIngredient = function (LabIngredient $ingredient, User $user, array $attributes = []): IngredientReview {
        Document::factory()
            ->for($ingredient, 'documentable')
            ->create(['type' => DocumentType::Certificate]);

        $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);

        $review->update([
            'status' => IngredientReviewStatus::Decided,
            'decision' => IngredientReviewDecision::Approved,
            'decided_at' => now(),
            ...$attributes,
        ]);

        return $review->refresh();
    };
});

it('reports not submitted, awaiting review, information requested and refused states', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $statusService = app(LabIngredientStatusService::class);

    expect($statusService->status($ingredient))->toBe(LabIngredientStatus::NotSubmitted);

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);

    $review = app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);

    expect($statusService->status($ingredient->refresh()))->toBe(LabIngredientStatus::AwaitingReview);

    $review->update(['status' => IngredientReviewStatus::InformationRequested]);

    expect($statusService->status($ingredient->refresh()))->toBe(LabIngredientStatus::InformationRequested);

    $refusedIngredient = LabIngredient::factory()->for($user->company)->create();
    Document::factory()
        ->for($refusedIngredient, 'documentable')
        ->create(['type' => DocumentType::Certificate]);
    $refusedReview = app(IngredientReviewSubmissionService::class)->submit($refusedIngredient, $user);
    $refusedReview->update([
        'status' => IngredientReviewStatus::Decided,
        'decision' => IngredientReviewDecision::Refused,
        'decided_at' => now(),
    ]);

    expect($statusService->status($refusedIngredient->refresh()))->toBe(LabIngredientStatus::Refused);
});

it('keeps an unchanged approval valid', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    ($this->approveIngredient)($ingredient, $user);

    $statusService = app(LabIngredientStatusService::class);

    expect($statusService->status($ingredient->refresh()))->toBe(LabIngredientStatus::Approved)
        ->and($statusService->hasValidApproval($ingredient))->toBeTrue();
});

it('invalidates the approval when an essential field changes', function (string $field, mixed $value) {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    ($this->approveIngredient)($ingredient, $user);

    $ingredient->update([$field => value($value)]);

    expect(app(LabIngredientStatusService::class)->status($ingredient->refresh()))
        ->toBe(LabIngredientStatus::ChangedSinceApproval);
})->with([
    'commercial name' => ['commercial_name', 'Nouveau nom commercial'],
    'supplier reference' => ['supplier_reference', 'NOUVELLE-REF'],
    'grade' => ['grade', 'Nouveau grade'],
    'supplier' => ['supplier_id', fn (): int => Supplier::factory()->create()->getKey()],
    'organic content' => ['organic_content_percentage', 42.5],
]);

it('invalidates the approval when a document is added after approval', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    ($this->approveIngredient)($ingredient, $user);

    Document::factory()
        ->for($ingredient, 'documentable')
        ->create(['type' => DocumentType::Other]);

    expect(app(LabIngredientStatusService::class)->status($ingredient->refresh()))
        ->toBe(LabIngredientStatus::ChangedSinceApproval);
});

it('keeps the approval valid when only reference labels are renamed', function () {
    $user = User::factory()->adherent()->create();
    $ingredientRef = IngredientRef::factory()->create();
    $supplier = Supplier::factory()->create();
    $ingredient = LabIngredient::factory()
        ->for($user->company)
        ->for($ingredientRef, 'ingredientRef')
        ->for($supplier, 'supplier')
        ->create();

    ($this->approveIngredient)($ingredient, $user);

    $supplier->update(['legal_name' => 'Fournisseur renommé']);
    $ingredientRef->update(['display_name' => 'Référence renommée']);

    expect(app(LabIngredientStatusService::class)->status($ingredient->refresh()))
        ->toBe(LabIngredientStatus::Approved);
});

it('expires an approval after its validity date', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();
    $review = ($this->approveIngredient)($ingredient, $user);

    $review->update(['valid_until' => today()]);

    expect(app(LabIngredientStatusService::class)->status($ingredient->refresh()))
        ->toBe(LabIngredientStatus::Approved);

    $review->update(['valid_until' => today()->subDay()]);

    expect(app(LabIngredientStatusService::class)->status($ingredient->refresh()))
        ->toBe(LabIngredientStatus::ApprovalExpired);
});

it('allows resubmission after a change and reports awaiting review', function () {
    $user = User::factory()->adherent()->create();
    $ingredient = LabIngredient::factory()->for($user->company)->create();

    ($this->approveIngredient)($ingredient, $user);

    $ingredient->update(['grade' => 'Nouveau grade']);

    $statusService = app(LabIngredientStatusService::class);

    expect($statusService->status($ingredient->refresh()))->toBe(LabIngredientStatus::ChangedSinceApproval);

    app(IngredientReviewSubmissionService::class)->submit($ingredient, $user);

    expect($statusService->status($ingredient->refresh()))->toBe(LabIngredientStatus::AwaitingReview);
});
