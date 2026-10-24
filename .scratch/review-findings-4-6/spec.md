# Review findings 4–6 — handoff spec

Status: ready-for-agent
Branch: `slices-1-3-access-formulas-ingredients` (commit on top of `7657ed6`)

## Why this exists

A code review of slices 1–3 found three remaining problems:

| Finding | Problem | Tickets |
|---|---|---|
| 4 | An ingredient approval stays valid after the ingredient or its reviewed documents change. BUILD_SPEC §4.4 requires: "The ingredient's current approval is valid only while the essential-field fingerprint still matches and the latest approval has not expired." | 01, 02 |
| 6 | `Document::creating` trusts caller-supplied `mime_type`, `size` and `sha256`, so a document's fingerprint can be forged. | 03 |
| 5 | The code has two Filament panels (`/admin` and `/portal`), but BUILD_SPEC and START_HERE describe a single `/admin` panel. **Decision by the product owner: keep both panels and correct the docs.** | 04 |

## Read first

1. `AGENTS.md` / `CLAUDE.md` — mandatory Laravel, Filament 5, Pest 4 rules.
2. `BUILD_SPEC.md` §2 (principles 5, 7), §4.4, §4.6, §5, §8.
3. `CONTEXT.md` — domain vocabulary. Domain language is French; code identifiers are English.
4. Each ticket in `issues/`, in number order.

## Execution order

Do the tickets strictly in order: **01 → 02 → 03 → 04**. Each ticket lists what blocks it.

- 01 and 03 both edit `app/Models/Document.php` and `database/factories/DocumentFactory.php`.
- 02 relies on 01. A fingerprint check only means something if reviewed documents can't be edited in place.

## Working rules (apply to every ticket)

- **Test first.** Write the listed Pest tests, run them, and confirm they fail for the stated reason before changing app code.
- Run a single file with `php artisan test --compact tests/Feature/<File>.php`.
- Validation errors are `Illuminate\Validation\ValidationException` with **French** messages, matching the existing models.
- No new Composer or npm dependencies. No new base folders.
- Use `php artisan make:class`, `make:enum` or `make:test --pest` with `--no-interaction` to create files.
- New enums: TitleCase case names, **snake_case English backed values** (BUILD_SPEC §2), implement Filament `HasLabel` and `HasColor` with French labels.
- PHPDoc over inline comments. Explicit return types. Curly braces always.
- Do not delete or weaken existing tests. If an existing test's assumption is invalidated by the ticket, update it and say why in the commit message.
- If a ticket's instructions conflict with BUILD_SPEC or the code, stop and record the conflict under `## Comments` in that ticket. Do not invent an interpretation.

## Gate after each ticket

All of these must pass before committing the ticket:

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
vendor/bin/filacheck
```

Baseline before you start: 69 tests passing, PHPStan 0 errors. Filacheck has 2 known warnings for `Placeholder` in `LabIngredientForm.php`; don't add new ones.

Then:

- Run `graphify update .` if the `graphify` CLI is available (BUILD_SPEC §14 batch gate). If it isn't available, say so in the ticket's `## Comments`.
- Commit **one commit per ticket**, Conventional Commit style like `7657ed6` (`fix: …`, `docs: …`). Don't push.
- Set the ticket's `Status:` to `resolved` and append a short `## Comments` note: what changed, which tests were added, anything left open.

## Out of scope

Leave these for later slices:

- `IngredientReviewDecisionService` (approve/refuse actions)
- the full `DocumentStorageService` pipeline (WebP normalization, 20 MB limit, dedup, authorized download route)
- deriving approval `valid_until` from document validity dates
- derogations
- formula freeze
