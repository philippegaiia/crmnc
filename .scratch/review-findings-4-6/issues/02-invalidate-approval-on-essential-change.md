# 02 — Approval is valid only while the fingerprint matches

Status: resolved
Finding: 4 (part 2 of 2)
Blocked by: 01

## Problem

`ProductReadinessService::hasValidApproval()` only checks that the latest review is `Decided` + `Approved` and not past `valid_until`. Suppose an adherent changes the supplier, grade or commercial name of an approved ingredient, or adds evidence documents. The old approval still counts, and the product stays "ready".

BUILD_SPEC §4.4 and §5 say otherwise:

- "The ingredient's current approval is valid only while the essential-field fingerprint still matches and the latest approval has not expired."
- "`LabIngredientStatusService` — current status from the latest review, snapshot fingerprint and validity."

## Design decisions (already made — implement as written)

### 1. One canonical fingerprint, owned by `IngredientReviewSubmissionService`

Split today's inline logic in `submit()` into public methods, so submission and status use the same code:

```php
/** @return array<string, mixed> */
public function snapshotFor(LabIngredient $ingredient): array;

/** @return list<int> */
public function documentIdsFor(LabIngredient $ingredient): array;

/** @param list<int> $documentIds */
public function fingerprintFor(LabIngredient $ingredient, array $documentIds): string;
```

- `snapshotFor()` returns the same array `submit()` stores today, display labels included. Leave the existing exact-snapshot test unchanged.
- `documentIdsFor()` returns all document IDs of the ingredient in ascending order. Use the loaded `documents` relation if present, otherwise query it.
- `fingerprintFor()` hashes **only the essential fields plus document IDs**:
  - `ingredient_ref_id`, `supplier_id`, `commercial_name`, `supplier_reference`, `grade`, `organic_certification_status` (value), `organic_content_percentage`, `components`
  - the document IDs

  Use SHA-256 over `json_encode([$essentialFields, $documentIds], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)`.
- **Leave display labels out of the fingerprint** (`ingredient_ref.display_name`, `ingredient_ref.normalized_inci`, `supplier.legal_name`). Otherwise an admin renaming a supplier or fixing an INCI spelling would silently invalidate every approval. Note this choice in a PHPDoc on `fingerprintFor()`.
- `submit()` must call these three methods. The stored `submitted_fingerprint` uses the new definition. Existing data is prototype-only, so no migration is needed.
- Don't add `lockForUpdate` inside these helpers; `submit()` keeps its own locked reload.

### 2. New enum `App\Enums\LabIngredientStatus`

Values are English snake_case; labels are French.

| Case | Value | Label | Colour | When |
|---|---|---|---|---|
| `NotSubmitted` | `not_submitted` | À soumettre | gray | no review |
| `AwaitingReview` | `awaiting_review` | En attente de revue | warning | latest review `Submitted` or `UnderReview` |
| `InformationRequested` | `information_requested` | Information demandée | warning | latest review `InformationRequested` |
| `Refused` | `refused` | Refusé | danger | latest review `Decided` + `Refused` |
| `Approved` | `approved` | Approuvé | success | latest `Decided` + `Approved`, fingerprint matches, not expired |
| `ChangedSinceApproval` | `changed_since_approval` | Modifié depuis l'approbation | danger | latest approved but current fingerprint ≠ `submitted_fingerprint` |
| `ApprovalExpired` | `approval_expired` | Approbation expirée | danger | latest approved, fingerprint matches, `valid_until` < today |

If the fingerprint differs **and** the approval has expired, `ChangedSinceApproval` wins. "Latest review" means the `latestReview` relation (`latestOfMany`, by id).

### 3. New `App\Services\LabIngredientStatusService`

```php
public function status(LabIngredient $ingredient): LabIngredientStatus;
public function hasValidApproval(LabIngredient $ingredient): bool; // status() === Approved
```

- It is constructor-injected with `IngredientReviewSubmissionService`.
- It works from loaded relations (`latestReview`, `documents`) when present, so callers can eager load and avoid N+1 queries.

## Wire it in

- **`ProductReadinessService`:**
  - delete the private `hasValidApproval()` and inject `LabIngredientStatusService` instead
  - extend the eager load to `currentFormulaVersion.lines.labIngredient.latestReview` **and** `currentFormulaVersion.lines.labIngredient.documents`
  - keep existing check keys, labels and messages
- **`LabIngredientsTable` and `LabIngredientInfolist`:**
  - replace the `latestReview.status` column/entry with a computed badge: `->state(fn (LabIngredient $record): LabIngredientStatus => app(LabIngredientStatusService::class)->status($record))`, label « Statut de validation »
  - in the table, eager load `latestReview` and `documents` via `modifyQueryUsing`
- **`EditLabIngredient` "Soumettre pour revue" action:** show it only when the status is `NotSubmitted`, `InformationRequested`, `Refused`, `ChangedSinceApproval` or `ApprovalExpired`. Re-submitting an unchanged, valid approval is pointless (BUILD_SPEC principle 5).
- Update `CONTEXT.md`: set the `_Code_:` line for **Approbation** to mention `LabIngredientStatusService`, and add a short entry for **Statut de validation** → `LabIngredientStatus`.

## Tests (write first)

Create `tests/Feature/LabIngredientStatusTest.php` with `php artisan make:test --pest LabIngredientStatusTest --no-interaction`. Build an approved ingredient by:

1. creating an ingredient with a certificate document
2. calling `submit()`
3. updating the returned review to `Decided` + `Approved` (+ `decided_at`)

Tests:

1. `it('reports not submitted, awaiting review, information requested and refused states')`
2. `it('keeps an unchanged approval valid')`: the status is `Approved`, and `hasValidApproval()` is true.
3. `it('invalidates the approval when an essential field changes')`: run it with a dataset over `commercial_name`, `supplier_reference`, `grade`, `supplier_id` (a new supplier), `organic_content_percentage`. Each one leads to `ChangedSinceApproval`.
4. `it('invalidates the approval when a document is added after approval')`
5. `it('keeps the approval valid when only reference labels are renamed')`: update the supplier's `legal_name` and the generic ref's `display_name`. The status stays `Approved`.
6. `it('expires an approval after its validity date')`: `valid_until` = yesterday leads to `ApprovalExpired`; today stays `Approved`.
7. `it('allows resubmission after a change and reports awaiting review')`

Add to `tests/Feature/ProductCockpitTest.php`:

8. `it('blocks readiness when an approved ingredient changed after approval')`: a product with all lines assigned to an approved ingredient. Readiness `ingredient_approvals` passes. Change the ingredient's `grade`, then call `assess()` on a **fresh** product model: the check now fails.

## Done when

- The tests above plus the full suite pass, and the gate in `spec.md` is green.
- Committed as `fix: invalidate ingredient approval when its fingerprint changes`.

## Comments

- `IngredientReviewSubmissionService` now exposes `snapshotFor()`, `documentIdsFor()` and `fingerprintFor()`. `submit()` uses the three methods; the fingerprint covers only the essential fields plus the reviewed document IDs, so display-label renames do not invalidate approvals.
- Added `App\Enums\LabIngredientStatus` (seven French-labelled, coloured cases) and `App\Services\LabIngredientStatusService` with `status()` and `hasValidApproval()`. A fingerprint mismatch wins over an expired approval; `valid_until` today still counts as valid.
- Wired in: `ProductReadinessService` injects the status service and eager loads `latestReview` and `documents`; the ingredients table and infolist show « Statut de validation »; the edit page only offers « Soumettre pour revue » for statuses that need a submission; `CONTEXT.md` documents the new vocabulary.
- Tests added to `tests/Feature/LabIngredientStatusTest.php`: the seven ticket tests (the essential-field test runs a five-case dataset). Added `blocks readiness when an approved ingredient changed after approval` to `tests/Feature/ProductCockpitTest.php`.
- Updated the existing cockpit test `counts unresolved formula lines instead of unrelated pending ingredient reviews`: its approved-ingredient fixture used a random factory fingerprint, which the new rule correctly rejects. It now submits and decides a real review so the fingerprint matches. The test's original assertion is unchanged.
- Gate: 85 tests passing, PHPStan 0 errors, Filacheck 2 pre-existing `Placeholder` warnings. `graphify update .` run successfully.
- Nothing left open; no deviation from the plan.
