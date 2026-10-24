# 03 — Always compute document MIME, size and SHA-256 from the stored file

Status: resolved
Finding: 6
Blocked by: 01 (same files)

## Problem

`Document::creating` in `app/Models/Document.php` skips its own computation if `mime_type` (string), `size` (int) and `sha256` (string) are already set:

```php
if (is_string($storedMimeType) && is_int($storedSize) && is_string($storedChecksum)) {
    return;
}
```

All three are mass-assignable through `#[Fillable]`. So any code path that passes them in can register a document whose recorded fingerprint doesn't match its file. BUILD_SPEC §7 says the SHA-256 is computed by the server.

`DocumentFactory` currently relies on this shortcut: it sets a fake `sha256` for a file that doesn't exist.

## Required behaviour

1. Remove `mime_type`, `size` and `sha256` from `#[Fillable]` on `Document`.
2. In `creating`, delete the early return. Always:
   - check the file exists on the `local` disk
   - read the MIME type and size from storage
   - hash the file
   - **overwrite** whatever values were set (even by `forceFill` or `forceCreate`)

   Keep the existing French error messages.
3. `DocumentFactory`:
   - remove `mime_type`, `size` and `sha256` from `definition()`
   - add `configure()` with `afterMaking()` that writes a small valid PDF stub to `$document->file_path` on the `local` disk, e.g. `"%PDF-1.4\n%crmnc factory\n%%EOF\n"`
   - confirm with a test that the detected MIME is `application/pdf`
4. Make every Feature test use a fake `local` disk, so factories never write to real `storage/app/private`. In `tests/Pest.php`, add a `beforeEach` that calls `Storage::fake('local')` on the existing `pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature')` chain. Check the Pest 4 syntax with `search-docs` (`["pest beforeEach global hooks"]`). Tests that already call `Storage::fake('local')` keep working.
5. Check `database/seeders` for any document creation. At handoff time there is none; if you find one, make it write a real file first.

## Tests (write first)

Add to `tests/Feature/LabIngredientDocumentationTest.php`:

1. `it('ignores caller-supplied document metadata')`: store a file with known content.
   - Call `$ingredient->documents()->create([...])` with `'mime_type' => 'image/png'`, `'size' => 1`, `'sha256' => str_repeat('0', 64)`.
   - Assert the saved row has `application/pdf`, the real byte size, and `hash('sha256', $content)`.
2. `it('recomputes metadata even when forced')`: same as test 1 but with `Document::forceCreate([...])`, or `(new Document)->forceFill([...])->save()`. Supply `documentable_type`/`documentable_id`. Assert the same real values.
3. `it('builds factory documents with a real stored file')`: `Document::factory()->create()`. Assert the file exists on the fake disk, `mime_type` is `application/pdf`, and `sha256` equals the file's hash.

All existing tests in `IngredientReviewSubmissionTest.php` use `Document::factory()`. They must pass unchanged once the factory and the global storage fake are in place.

## Done when

- The tests above plus the full suite pass, and the gate in `spec.md` is green.
- Committed as `fix: compute document metadata from the stored file`.

## Comments

- `Document` no longer accepts `mime_type`, `size` or `sha256` through mass assignment, and `creating` always reads the MIME type, size and SHA-256 from the file on the `local` disk, overwriting values supplied through `forceFill`/`forceCreate`. Existing French error messages are unchanged.
- `DocumentFactory` no longer sets metadata. Its `configure()`/`afterMaking()` hook writes a small PDF stub to the generated `file_path` on the `local` disk, so the model computes `application/pdf` and the real checksum.
- `tests/Pest.php` now fakes the `local` disk for every Feature test before each test, so factories never write to `storage/app/private`.
- No document creation exists in `database/seeders`; nothing to change there.
- Tests added to `tests/Feature/LabIngredientDocumentationTest.php`: `ignores caller-supplied document metadata`, `recomputes metadata even when forced`, `builds factory documents with a real stored file`.
- Gate: 88 tests passing, PHPStan 0 errors, Filacheck 2 pre-existing `Placeholder` warnings. `graphify update .` run successfully.
- Nothing left open; no deviation from the plan.
