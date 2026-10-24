# 01 — Make documents referenced by a review immutable

Status: resolved
Finding: 4 (part 1 of 2)
Blocked by: —

## Problem

BUILD_SPEC §4.6: "Documents referenced by a review snapshot are immutable. Replacing a file creates a new document row with `replaces_id`; it never overwrites the reviewed row."

Today `app/Models/Document.php` blocks changes **only while a review is pending** (`belongsToPendingIngredientReview()`). Once a review is decided, a reviewed document can be edited or deleted in place. Its ID doesn't change, so the review fingerprint (snapshot + document IDs) can't detect the edit.

## Required behaviour

A document is **reviewed** when its ID appears in `submitted_document_ids` of any `IngredientReview` for the same laboratory ingredient, whatever the review's status.

| Operation | Pending review on the ingredient | Document is reviewed (no pending review) | Otherwise |
|---|---|---|---|
| Create a new document on the ingredient | blocked (existing rule, keep it) | allowed: new ID, so ticket 02 will treat it as a change | allowed |
| Update a reviewed document | blocked | blocked, except `control_status`, `verified_by`, `verified_at` | allowed |
| Delete a reviewed document | blocked | blocked | allowed |

- Use these messages:
  - « Un document examiné ne peut pas être modifié ; ajoutez un nouveau document. »
  - « Un document examiné ne peut pas être supprimé. »
  - Keep the existing pending-review messages as they are.
- Detect "reviewed" in PHP so it doesn't depend on SQLite JSON grammar. Add a public method `Document::isReviewed(): bool`:

  ```php
  IngredientReview::query()
      ->where('lab_ingredient_id', $this->documentable_id)
      ->pluck('submitted_document_ids')
      ->flatten()
      ->contains($this->getKey())
  ```

  Only documents whose `documentable_type` is `LabIngredient::class` can be reviewed. Return `false` for others, and for unsaved models.
- The service's mass `->update(['control_status' => …])` in `IngredientReviewSubmissionService` uses the query builder and fires no model events. It must keep working.

## Filament UI

The repeater in `app/Filament/Resources/LabIngredients/Schemas/LabIngredientForm.php` must not offer edits the model will reject:

- For an item whose record is a reviewed document: disable its fields and hide its delete action. Adding new items stays possible.
- Look up the Filament 5 API with the Boost `search-docs` tool, e.g. queries `["repeater item delete action visibility", "repeater disable item fields record"]`. Don't guess method names.
- Show a short French helper text on the documents section: « Les documents déjà examinés sont figés ; ajoutez un nouveau document pour les remplacer. »

## Tests (write first)

Add them to `tests/Feature/IngredientReviewSubmissionTest.php`. Mark the review decided via `$review->update(['status' => IngredientReviewStatus::Decided, 'decision' => IngredientReviewDecision::Approved, 'decided_at' => now()])`; no decision service exists yet.

1. `it('keeps reviewed documents immutable after the review is decided')`: submit, decide as approved. Then:
   - `$document->update(['issuer' => 'x'])` throws `ValidationException`
   - `$document->delete()` throws
   - `$document->update(['control_status' => DocumentControlStatus::Validated])` succeeds (check the enum for the exact case name)
2. `it('allows adding a new document after the review is decided')`: after a decided review, `Document::factory()->for($ingredient, 'documentable')->create()` succeeds.
3. `it('still allows editing documents that were never reviewed')`: a document created after the decided review can be updated and deleted.
4. `it('does not delete a reviewed document from the edit form')`: as the adherent on the `portal` panel (see `PortalWriteBoundaryTest` for setup), load `EditLabIngredient` for an ingredient with a decided review. Assert the document still exists after `call('save')`. If the per-item delete is hidden, also assert it isn't available, using whatever the Filament testing helpers support.

The existing test `freezes submitted ingredient data and evidence while review is pending` must still pass unchanged.

## Done when

- The 4 tests above plus the full suite pass, and the gate in `spec.md` is green.
- Committed as `fix: freeze documents referenced by an ingredient review`.

## Comments

- `Document::isReviewed()` detects reviewed rows in PHP from `ingredient_reviews.submitted_document_ids`, regardless of review status; it returns `false` for unsaved models and non-`LabIngredient` morphs.
- Reviewed documents now reject updates except `control_status`, `verified_by` and `verified_at`, and reject deletion, with the ticket's French messages. Pending-review rules and messages are unchanged.
- The documents repeater disables item fields whose record is reviewed and hides the per-item delete action; a French helper text explains the freeze. Mass `control_status` updates in `IngredientReviewSubmissionService` keep working because they use the query builder.
- Tests added to `tests/Feature/IngredientReviewSubmissionTest.php`: `keeps reviewed documents immutable after the review is decided`, `allows adding a new document after the review is decided`, `still allows editing documents that were never reviewed`, `does not delete a reviewed document from the edit form`.
- The Filament edit-form test stores a real file on a faked `local` disk because the factory does not write files until ticket 03.
- Gate: 73 tests passing, PHPStan 0 errors, Filacheck 2 pre-existing `Placeholder` warnings. `graphify update .` run successfully.
- Nothing left open; no deviation from the plan.
