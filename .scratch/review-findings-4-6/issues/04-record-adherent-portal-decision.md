# 04 — Record the two-panel decision in the docs

Status: resolved
Finding: 5
Blocked by: 03 (only so commits stay in order; no shared files)

## Decision (made by the product owner, 2026-10-24)

Keep two Filament panels. **No application code changes in this ticket.**

- **`/portal` — Espace adhérent:** for members (role `adherent`, attached to a company). Members enter and provide their own data: products, formulas, versions, laboratory ingredients, documents, contacts and their company record.
- **`/admin` — Panneau interne:** for the association's staff (`admin`, `gestionnaire`, `evaluateur`). Staff review and approve what members provide, manage reference data, users and all companies.

Both panels share the same models, policies, services and company-scoped queries.

Facts from the code, to describe accurately:

- `User::canAccessPanel()`:
  - `admin` panel: active users whose role isn't `adherent`
  - `portal` panel: active `adherent` users with a `company_id`
- `PortalPanelProvider` registers resources explicitly: `CompanyResource`, `ContactResource`, `ProductResource`, `FormulaResource`, `FormulaVersionResource`, `LabIngredientResource`.
- `AdminPanelProvider` discovers all resources.

`docs/decisions/architecture-decision.md` §1 already describes "deux interfaces sur les mêmes données et règles métier : l'espace adhérent … et le panneau interne". The conflict exists only in `BUILD_SPEC.md`, `START_HERE.md` and possibly `README.md`.

## Changes

1. **New ADR** `docs/decisions/adherent-portal-panel.md`, **in French**, following the heading style of `architecture-decision.md`:
   - title « Décision : espace adhérent séparé (`/portal`) »
   - date 24 octobre 2026, statut « Acceptée »
   - sections Contexte, Décision, Conséquences

   Points to cover:
   - **Contexte:** BUILD_SPEC described one role-aware panel; the implementation and the architecture decision separate the two audiences.
   - **Décision:** the role split above.
   - **Conséquences:**
     - a resource reaches adherents only if it's registered in `PortalPanelProvider`, an extra layer on top of policies and scoped queries
     - each new adherent-facing resource must be added there and covered by a cross-company test
     - shared Filament resource classes still need to stay role-aware
2. **`BUILD_SPEC.md`:**
   - §1 line "One Filament panel at `/admin`…": describe the two panels and link the ADR.
   - §3 paragraph "All four roles may authenticate into the single Filament panel…": state the `canAccessPanel()` rules above. Keep the sentence that policies and scoped queries decide what each user sees, and the `company_id` rule that follows.
   - §8 "Role-aware navigation": add one sentence saying adherent screens live in `/portal` and internal screens in `/admin`.
3. **`START_HERE.md`:**
   - "One Filament panel already exists at `/admin`." → both panels exist.
   - "Use a single role-aware Filament `/admin` panel unless an approved specification change says otherwise." → the two-panel rule, pointing to the ADR.
4. **`README.md`:** if it states panel URLs, list both; otherwise add one line under "Local demo accounts": staff sign in at `/admin`, the adherent at `/portal`.
5. Don't edit `docs/reference/*`; those are historical sources.

## Tests

None needed; this is docs only. Still run the gate in `spec.md`, which must stay green. `tests/Feature/PanelBoundaryTest.php` already covers the two-panel access rules.

## Done when

- `grep -n -i "single.*panel\|one filament panel" BUILD_SPEC.md START_HERE.md README.md` returns nothing that contradicts the ADR.
- Committed as `docs: record the separate adherent portal decision`.

## Comments

- Added the French ADR `docs/decisions/adherent-portal-panel.md` (24 octobre 2026, statut « Acceptée ») covering the two-panel context, the role split and its consequences.
- `BUILD_SPEC.md`: §1 now describes both panels and links the ADR; §3 states the `canAccessPanel()` rules while keeping the policies/scoped-queries and `company_id` requirements; §8 states that adherent screens live in `/portal` and internal screens in `/admin`.
- `START_HERE.md` describes both existing panels and replaces the single-panel rule with the two-panel rule pointing to the ADR.
- `README.md`: demo accounts now state that staff sign in at `/admin` and the adherent at `/portal`.
- No application code or tests changed; `tests/Feature/PanelBoundaryTest.php` already covers the two-panel access rules.
- `grep -n -i "single.*panel\|one filament panel" BUILD_SPEC.md START_HERE.md README.md` returns nothing.
- Gate: 88 tests passing, PHPStan 0 errors, Filacheck 2 pre-existing `Placeholder` warnings. `graphify update .` run successfully.
- Nothing left open; no deviation from the plan.
