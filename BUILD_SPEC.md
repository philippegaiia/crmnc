# BUILD SPEC — CRM Nouvelle Cosmétique

**Version 2 — agent handoff**  
**Target repository:** `/Users/philippe/Herd/crmnc`

> Agents must read the repository `AGENTS.md` first. `AGENTS.md` governs how work is performed; this file defines what must be built. The functional specification in `docs/specs/functional-spec-v2.md` is the business reference. When documents conflict, stop and report the conflict instead of silently choosing an interpretation.

## Mission

Build a working Laravel + Filament prototype that proves this integrated workflow:

`société → adhésion → produit → formule avec ingrédient générique → ingrédient du laboratoire + documents → revue humaine unique → réutilisation immédiate → évaluation de formule → dérogation séparée → attestation immuable`

The prototype is complete only when an adherent and an evaluator can execute this workflow from their respective accounts in the same application.

---

## 1. Stack, repository and approved dependencies

### Existing stack

- Laravel 13
- Filament 5
- Livewire 4
- PHP 8.5 runtime
- Pest 4
- SQLite with foreign keys enabled; WAL for the local prototype
- Laravel Herd; resolve the project URL through Herd rather than hard-coding the scheme
- Two Filament panels share the same models, policies and services: `/portal` for adherents and `/admin` for the association team (see `docs/decisions/adherent-portal-panel.md`)
- French interface; English class, table, column and enum-value names

### Environment

- `APP_LOCALE=fr`
- `APP_FALLBACK_LOCALE=fr`
- `APP_FAKER_LOCALE=fr_FR`
- `QUEUE_CONNECTION=database`
- `MAIL_MAILER=log`
- `FEATURE_EMAILS=false`, exposed through `config/features.php`
- `FILESYSTEM_DISK=local`; the existing local disk root is `storage/app/private`

Relative document paths therefore resolve under `storage/app/private/documents/...`.

### Dependency additions authorized by this specification

Install compatible releases through Composer after checking version-specific documentation as required by `AGENTS.md`:

- `intervention/image:^4.2` for image normalization;
- `dompdf/dompdf:^3.1` for the prototype attestation PDF;
- `pestphp/pest-plugin-livewire:^4.1` as a development dependency for Filament/Livewire tests.

Do not substitute another package without recording the reason and obtaining approval.

Preflight must confirm the PHP runtime has `dom`, `mbstring` and an image driver such as `gd`. Stop before implementation if these requirements are missing.

---

## 2. Operating rules

1. **Filament-native UI.** Use resources, relation managers, forms, tables, infolists, actions, widgets, tabs and badges. Add a custom Livewire or Blade component only when Filament cannot express the required interaction clearly.
2. **One integrated application.** Adherents create and follow their data; internal users validate and administer it. Both roles use the same models, policies, services and audit rules.
3. **Derived state is not stored twice.** Active-member status, payment status, ingredient status, formula readiness, derogation status and product technical eligibility are calculated.
4. **Decisions are immutable records.** Approval, refusal, derogation, formula decision and attestation issuance are dated and attributed rows.
5. **Approve once, reuse.** A laboratory ingredient is reviewed once for a defined snapshot. An unchanged, unexpired approval is reused in all compatible formulas for that company.
6. **Derogation is separate.** It never changes an ingredient review decision and never turns an unapproved ingredient into an approved one.
7. **Submitted formula versions are frozen.** Technical changes require a new version. Workflow transitions occur through dedicated actions/services, not ordinary CRUD edits.
8. **Authorization has two layers.** Policies block direct access; list and relationship queries are also company-scoped so records are not exposed in tables or selects.
9. **Tests precede implementation.** Each vertical slice begins with a failing Pest test and ends with the focused test, full test suite, formatting and static analysis passing.
10. **No undocumented ambiguity.** Do not use “the stricter option” as a substitute for a product decision.
11. **Keep formatting and the knowledge graph current.** After every agent code-generation or code-edit batch, run Pint and an incremental Graphify update before reporting completion or starting the next batch. The first implementation session creates the baseline graph before code changes.

PHP backed enums use TitleCase case names, snake_case English backed values, and implement Filament `HasLabel` and `HasColor` where displayed.

---

## 3. Roles and access matrix

| Capability | Admin | Gestionnaire | Évaluateur | Adhérent |
|---|---:|---:|---:|---:|
| Manage users and reference data | Yes | No | No | No |
| Manage companies, contacts, memberships and payments | Yes | Yes | Read | Own company only |
| Create products, formulas and laboratory ingredients | Yes | Yes | Read | Own company only |
| Upload and replace own documents | Yes | Yes | Read | Own company only |
| Review laboratory ingredients | Yes | No | Yes | No |
| Evaluate formulas and grant/lift derogations | Yes | No | Yes | No |
| Issue, replace or revoke attestations | Yes | Yes | Read | Download own only |

`User::canAccessPanel()` allows active users whose role is not `adherent` into the `/admin` panel, and active `adherent` users with a non-null `company_id` into the `/portal` panel. Navigation hides unauthorized resources and actions. Policies and scoped queries determine what each user can see.

`users.company_id` is required for the `adherent` role and must be null for internal roles. Enforce this in validation and tests.

---

## 4. Database model

Create **24 new tables** and alter the existing `users` table. The count includes `countries` and the company/category pivot.

Notation: `?` means nullable. All tables use `id` and timestamps unless explicitly stated. Money is decimal, never float. Foreign-key deletion behavior follows §4.8.

### 4.1 Relation adhérent

**countries** — `code: char(2) primary`, `name_fr: string`, `name_en: string`; seed FR, ES, IT, BE, CH, DE, GB, PT, MA and TN.

**companies** — `legal_name: string`, `usual_name: string?`, `commercial_status: enum [default demande_info, index]`, `origin: enum?`, `preferred_language: enum [default fr]`, separate address fields, `country_code: char(2) FK`, phone/email/website/LinkedIn/Instagram fields, `notes_internal: text?`, soft deletes.

**contacts** — company FK, first/last name, role enum, email/phone, `is_primary`, optional preferred language, `is_active`, notes. Enforce at most one active primary contact per company in application validation and with a SQLite partial unique index.

**commercial_status_histories** — company FK, from/to status, reason, `changed_by` user FK, `created_at`; no `updated_at`. A reason is required for `sans_suite` and reopening.

**memberships** — company FK, year, start/end dates, amount due, currency default EUR, status enum, optional `activated_at` timestamp, optional `activated_by` user FK and optional termination date/reason; unique company/year. In the prototype, an admin or gestionnaire activates a membership manually through a dedicated action after checking the dossier. Payment status is displayed separately and never activates a membership automatically.

**payments** — membership FK, date, `amount: decimal(10,2) [strictly positive]`, type enum (`encaissement`, `remboursement`), method enum and optional reference. The type supplies the sign; negative amounts are rejected.

### 4.2 Products and formulas

**product_categories** — unique name, optional description, active flag.

**company_product_category** — company FK + category FK; unique pair.

**formulas** — company FK, internal code, optional description, active flag; unique company/internal code.

**formula_versions** — formula FK, version number, workflow status, optional submission timestamp and version note, creator user FK; unique formula/version. Allocate the next number inside a transaction and rely on the unique constraint as the final guard.

**products** — company FK, category FK, name, brand, optional white-label client, nullable current formula version FK, commercial status and notes. The linked version must belong to the same company.

**formula_lines** — formula-version FK, required generic ingredient reference FK, nullable laboratory ingredient FK, percentage decimal, QSP flag, optional position and technical comment. A selected laboratory ingredient must belong to the formula company and match the generic reference.

### 4.3 Ingredients

**ingredient_refs** — display name, unique normalized INCI name, searchable synonyms, family, origin enum, label-position enum, conditions, organic-eligibility enum and status enum.

**suppliers** — legal name, country FK, website, contact information and active flag. Add a duplicate warning for normalized legal name + country.

**lab_ingredients** — company FK, generic reference FK, supplier FK, commercial name, optional supplier reference, grade and notes. Unique company/supplier/reference when reference exists; when absent, reject duplicates on normalized company/supplier/commercial-name/grade.

**lab_ingredient_components** — laboratory ingredient FK, component generic-reference FK, optional exact/min/max concentration, optional declared order, under-one-percent enum and optional source-document FK. This table is optional for a single-INCI ingredient and used for commercial mixtures.

### 4.4 Human ingredient review

**ingredient_reviews** — laboratory ingredient FK, submitter and submission timestamp, reviewer FK, workflow status, optional decision, decision timestamp, comment, `submission_snapshot: json`, `submitted_document_ids: json`, `submitted_fingerprint: char(64)`, and `valid_until: date?`.

The snapshot contains the submitted generic reference, supplier, commercial name, supplier reference, grade and component definitions. The document-ID list identifies exactly which immutable document rows the human reviewed. The fingerprint is SHA-256 over the canonical snapshot plus document IDs.

Submission rules:

- At least one document of type `fiche_technique` or `certificat` is required.
- Submission creates a new review record and freezes its snapshot.
- While a review is submitted or under review, the snapshot and submitted documents cannot change.
- After an information request, the adherent edits the ingredient/documents and submits a new review; the earlier review remains historical.
- Approval records the reviewer, decision date and optional `valid_until` derived or chosen from the reviewed evidence.
- Refusal and information requests require a comment.
- The ingredient’s current approval is valid only while the essential-field fingerprint still matches and the latest approval has not expired.

### 4.5 Evaluation, derogations and attestations

**formula_evaluations** — formula-version FK, grid version, received/complete/due timestamps, nullable `paused_at`, `total_paused_seconds: unsignedBigInteger [default 0]`, evaluator FK, workflow status, optional decision, decision timestamp/actor and summary. Prototype SLA: 15 calendar days from `complete_at`, excluding time suspended in `en_attente`.

When an evaluation enters `en_attente`, store `paused_at`. On resume, add the elapsed suspension to `total_paused_seconds`, clear `paused_at` and recompute `due_at` as `complete_at + 15 calendar days + total_paused_seconds`. The interface displays the accumulated suspension and the current remaining time. Repeated pause/resume transitions must preserve the remaining SLA accurately.

**evaluation_checks** — evaluation FK, criterion, result enum, optional comment and evidence-document FK, evaluator FK and checked timestamp.

**derogations** — laboratory ingredient FK, scope enum (`tous_usages`, `version`, `ligne`, `produit`), nullable scope FKs, reason, optional conditions, grant actor/timestamp, effect date, `is_permanent: bool`, nullable expiry, optional lift date/reason. Exactly the FK required by the chosen scope must be populated. Permanent requires no expiry; non-permanent requires one.

Creating a derogation never mutates an ingredient review. If the ingredient is not approved, its review remains refused or undecided while formula readiness is supplied by the active, covering derogation.

**attestations** — company FK, membership FK, unique sequential number `NC-YYYY-####`, year, issue timestamp/actor, status, template version, generated-document FK, optional replaced-attestation FK and revocation reason.

**attestation_products** — attestation FK, product FK and immutable JSON snapshot containing product identity, category, formula version and decision, plus active derogations at issuance.

### 4.6 Transversal

**documents** — type enum; controlled morph to company, membership, laboratory ingredient, formula version, formula evaluation, derogation or attestation; private file path, original name, MIME, size, SHA-256, version, received date, issuer, validity dates, control status, verifier/date, replaced-document FK and access level.

Documents referenced by a review snapshot are immutable. Replacing a file creates a new document row with `replaces_id`; it never overwrites the reviewed row.

**activities** — company FK, optional contact/product FKs, type/nature enums, planned/done timestamps, owner FK, status, campaign and comment.

### 4.7 Users

Alter **users** with role enum, nullable company FK and active flag. Update fillable/casts, factory states and panel-access logic.

### 4.8 Foreign keys, deletion and migration order

- Restrict deletion of reviews, decisions, derogations, issued attestations and reviewed documents.
- Do not expose hard-delete actions for submitted/decided/issued scientific records.
- Cascade only disposable draft children such as formula lines when a draft version is deleted.
- Soft-delete companies; retain their historical children.
- Set nullable actor fields to null when an internal user is removed; normally deactivate users instead of deleting them.
- Never automatically delete a physical file while another document row references the same path.

Migration order must resolve dependencies explicitly:

1. countries, companies, user alteration, contacts, status histories, memberships, payments;
2. categories and pivot;
3. ingredient references, suppliers and laboratory ingredients;
4. formulas and formula versions;
5. products;
6. private documents;
7. ingredient components and reviews;
8. formula lines;
9. formula evaluations and checks;
10. derogations;
11. attestations and attestation products;
12. activities.

---

## 5. Domain services and derived logic

Keep orchestration out of Filament page classes. Use focused services in `app/Services`:

- `CompanyAccessService` — company query constraints and ownership checks;
- `MembershipActivationService` — manual activation/termination actions and actor/timestamp audit fields; payment never changes membership status;
- `MembershipPaymentStatusService` — non-paid, partial, paid or refunded from positive payment movements;
- `LabIngredientStatusService` — current status from the latest review, snapshot fingerprint and validity;
- `IngredientReviewSubmissionService` — canonical snapshot, document IDs and fingerprint;
- `IngredientReviewDecisionService` — information request, approval and refusal;
- `DerogationCoverageService` — whether an active derogation covers a specific formula-line usage;
- `FormulaReadinessService` — percentage/QSP calculation and line readiness;
- `FormulaVersionSubmissionService` — readiness gate, submission and freeze;
- `FormulaEvaluationDecisionService` — acceptance/refusal gate and audit fields;
- `FormulaEvaluationSlaService` — enter/leave `en_attente`, accumulate suspended time and recompute `due_at`;
- `DocumentStorageService` — validated private storage and immutable replacements;
- `AttestationIssuanceService` — sequential number, snapshots, PDF, document registration and replacement.

Derived outcomes:

- Active member: an active membership covers today.
- Membership activation: only a dedicated admin/gestionnaire action can set `active`; the derived payment status is informative and does not drive this transition.
- Payment status: compare amount due with encaissements minus refunds.
- Ingredient status: latest review plus fingerprint match and approval validity.
- Formula line ready: a matching laboratory ingredient exists and is approved, or an active derogation covers this exact use.
- Formula version ready: it has at least one line, all lines are ready, and percentages/QSP pass.
- Product technically eligible: its current version is accepted and no required covering derogation has expired.
- Derogation status: upcoming, active, expired or lifted from dates.
- INCI preview: proposal only; expand known components, flag incomplete data and display “À valider par le comité.”

---

## 6. Business invariants

1. **Progressive formula entry.** The generic ingredient and percentage are required first. The adherent may link an existing approved ingredient or create a new laboratory ingredient inline.
2. **Pending ingredients remain usable in drafts.** They do not make a line ready.
3. **Formula submission gate.** Submission requires every line to be ready and the percentage rule to pass. Approval of a pending ingredient must update readiness immediately without creating another review.
4. **Percentage rule.** Without QSP, the sum must equal 100.00 ± 0.01. With one QSP line, non-QSP total must be below 100 and the QSP remainder is calculated. More than one QSP line is rejected in the prototype.
5. **Freeze.** Submission sets `submitted_at`; line and technical edits are thereafter denied in UI, policies and services. Workflow status changes use dedicated actions.
6. **Formula acceptance gate.** An evaluation cannot be accepted unless the frozen version remains covered by approvals or active derogations and all required checks are compliant or non-applicable.
7. **Derogation scope.** `tous_usages` covers only this company’s uses of the laboratory ingredient. Version, line and product scopes cover exactly their selected entity and never leak elsewhere.
8. **Same-company integrity.** Enforce on forms, services, policies and tests.
9. **Attestation immutability.** Issuance creates product snapshots and a PDF. Issued attestations cannot be edited or deleted; correction creates a replacement.
10. **Commercial history.** Every commercial-status change writes a history row through a domain action/observer with actor and reason.
11. **Evaluation SLA suspension.** Entering `en_attente` pauses the 15-calendar-day clock; resuming preserves the remaining duration, updates the accumulated suspension and exposes it in the evaluation view.

---

## 7. Document pipeline

Accept JPEG, PNG, WebP and PDF up to 20 MB. Reject office documents and extension/MIME mismatches.

Create `config/documents.php` with allowed MIME types, maximum size, long edge 2340 px and WebP quality 75.

`DocumentStorageService` must:

1. validate MIME using server-side file information and validate size;
2. normalize images to WebP, remove metadata, scale down to a 2340 px maximum long edge and never upscale;
3. preserve PDFs byte-for-byte, including multipage PDFs;
4. calculate SHA-256 after normalization;
5. store on the private local disk at `documents/{year}/{month}/{uuid}.{webp|pdf}`;
6. reuse the existing physical path for identical content while still creating the required document/version row;
7. create replacements instead of overwriting;
8. provide downloads only through an authorized named route/controller response.

Never expose `storage/app/private` through a public symlink.

Deduplication is byte-level after normalization. It detects identical normalized uploads, not semantically equivalent or perceptually similar scans of the same document; do not add OCR or perceptual hashing in the prototype.

---

## 8. Filament experience

### Role-aware navigation

Adherent screens live in `/portal` and internal screens live in `/admin` (see `docs/decisions/adherent-portal-panel.md`).

- **Relation:** companies, contacts, memberships, payments, activities.
- **Certification:** products, formulas, versions, formula evaluations, ingredient reviews, derogations, attestations.
- **Ingredients:** laboratory ingredients, generic references, suppliers.
- **References:** categories, countries and documents.

Adherents see only their company’s relevant product, formula, ingredient, document and attestation screens. Internal-only reference and decision resources are hidden.

### Formula builder

The formula-version edit page contains:

- status, version number, readiness and INCI preview;
- a table-style repeater: generic ingredient → laboratory ingredient → percentage → QSP;
- generic search over display name, INCI name and synonyms;
- laboratory-ingredient choices filtered by authenticated company and generic reference;
- an inline create action for commercial name, supplier, reference, grade and document uploads;
- a visible approval badge/status for each laboratory ingredient;
- live percentage/QSP totals and precise blocking messages;
- a Submit action enabled only when readiness passes;
- a disabled technical form after submission.

Creating an ingredient inline creates the ingredient, documents and a submitted review snapshot in one transaction.

### Adherent workflow

The adherent can create products/formulas/ingredients, upload replacement documents, answer information requests, resubmit an ingredient, monitor readiness and download issued attestations. They cannot approve, evaluate, grant derogations or issue attestations.

### Ingredient-review queue

Evaluators see submitted, under-review, information-requested and decided tabs. The review screen displays the frozen submission snapshot, supplier and exact reviewed documents together.

Actions:

- **Start review** — assigns reviewer and status;
- **Request information** — requires comment;
- **Approve** — records decision and optional validity date;
- **Refuse** — requires comment;
- **Create derogation** — opens a separate derogation form and does not change the ingredient decision automatically.

### Formula evaluation

Display the checklist, due date, accumulated suspended time, remaining SLA, blocking lines, approval/derogation coverage and decision history. Pause/Resume actions call the SLA service. Accept/Refuse actions call the evaluation service and enforce §6.

### Attestation issuance

From the company, an authorized internal user chooses the year and eligible products. The issuance service creates snapshots, renders a versioned French Blade template through Dompdf, stores the PDF through the document pipeline and returns an authorized download action.

### Required views/widgets

- Companies: all, pipeline, active members, Spain 2026.
- Ingredient reviews: submitted, under review, information requested, decided.
- Evaluations: in progress by due date, suspended, decided.
- Memberships: renewals due within 60 days.
- Documents and derogations: expirations within 60 days.
- Draft formulas with generic ingredients not yet linked to laboratory ingredients.
- Incomplete dossiers, including evaluations marked incomplete and submissions blocked by missing required evidence.
- Attestations to prepare: active memberships with technically eligible products and no current attestation for the selected year.
- Overdue follow-ups and tasks from activities.
- Dashboard: company statuses, pending ingredient reviews, active evaluations, renewals and recent activities.

---

## 9. Vertical implementation slices

Agents work from an implementation plan with file ownership. Do not let multiple agents edit the same migrations, models or Filament resources concurrently.

### Slice 1 — Access and company boundary

Users/roles/company association, policies, scoped queries, companies, contacts and a test proving an adherent cannot list or open another company’s records.

### Slice 2 — Product and progressive formula skeleton

Categories, generic ingredients, suppliers, products, formulas, draft versions and generic formula lines. An adherent can create the skeleton inside their company.

### Slice 3 — Ingredient submission and one-time human review

Laboratory ingredients, documents, immutable review snapshots, reviewer actions and immediate readiness reuse. This is the central end-to-end prototype slice.

### Slice 4 — Formula submission and freeze

Percentage/QSP calculations, readiness, submission service, frozen version protection and version creation.

### Slice 5 — Evaluation and separate derogations

Evaluation checklist, decision gate, precisely scoped derogations, expiry/lift behavior and non-leakage tests.

### Slice 6 — Memberships, payments and attestations

Membership/payment derivation, eligible-product selection, immutable snapshots, sequential numbering, PDF storage, replacement and revocation.

### Slice 7 — Operational CRM and demo polish

Activities, required views, widgets, French notification templates through the log mailer, French seed narrative and final end-to-end tests. Store each recipient's preferred language, but the prototype deliberately sends French templates only; translated templates are deferred.

Each slice must be independently testable and committed before the next slice starts.

---

## 10. Seeded demonstration

Seed realistic French data through factories and production services:

- around 15 generic ingredients with a mix of validated and pending references;
- four suppliers including a Spanish supplier;
- categories SAF, Émulsion, Huile pure, Baume, Shampoing solide and Déodorant;
- Savonnerie des Alpilles with 2025/2026 memberships and a partially paid 2026 balance;
- one accepted soap formula reused by two products, including one white-label product;
- one hand-cream formula with a pending ingredient and an evaluation due in six days;
- two coconut-oil commercial ingredients from different suppliers/grades;
- one approved shea-butter ingredient with two INCI components;
- one separate line-scoped derogation expiring in 45 days;
- one immutable issued 2025 attestation generated through the real service;
- a Spanish laboratory appearing in Spain 2026 and a LinkedIn prospect;
- admin, gestionnaire, evaluator and adherent users with factory states;
- tiny PDF and JPG fixtures processed through the real document pipeline.

Seed these exact local demo accounts:

- `admin@crmnc.test`;
- `ines@crmnc.test` (gestionnaire);
- `comite@crmnc.test` (évaluateur);
- `adherent@crmnc.test`.

All demo accounts use the password from `SEED_USER_PASSWORD`. Add that variable to `.env.example` and document the accounts in the repository README without committing a real password. The demo seeder must refuse to create these accounts outside `local` or `testing`. Never reuse the credentials in another environment.

---

## 11. Required Pest coverage

At minimum, create focused feature tests for:

1. role access and company isolation in list, relationship-select and direct-record access;
2. one active primary contact per company;
3. positive payment amounts and correct derived balance;
4. manual membership activation authorization/audit and proof that payment does not auto-activate;
5. generic-first formula entry and company/ref-filtered laboratory ingredients;
6. review snapshot immutability and re-review after essential changes;
7. approval reuse causing readiness to change without another review;
8. percentage/QSP validation and submission blocking;
9. technical edits blocked after submission;
10. same-company product/version and line/ingredient integrity;
11. evaluation SLA pause/resume, repeated suspension and due-date recomputation;
12. evaluation acceptance blocked by a non-ready line;
13. each derogation scope, expiry/lift behavior and non-leakage;
14. JPEG normalization, PDF preservation, MIME rejection, hash reuse and authorized downloads;
15. attestation snapshot immutability, replacement and deletion denial;
16. commercial-status history;
17. complete adherent → evaluator → attestation workflow.

Use Pest because it is the repository default. Use factories rather than ad hoc model creation where reusable states make sense.

---

## 12. Enum backed values and French labels

Case names are TitleCase. Backed values and labels below are exact and must not be renamed independently by different agents.

| Domain | Backed value | French label |
|---|---|---|
| User role | `admin` | Administrateur |
|  | `gestionnaire` | Gestionnaire association |
|  | `evaluateur` | Évaluateur scientifique |
|  | `adherent` | Adhérent |
| Company status | `demande_info` | Demande d'information |
|  | `prospect` | Prospect qualifié |
|  | `dossier_envoye` | Dossier envoyé |
|  | `relance` | Relance |
|  | `converti` | Converti |
|  | `sans_suite` | Sans suite |
| Membership | `en_attente` | En attente |
|  | `active` | Active |
|  | `expiree` | Expirée |
|  | `resiliee` | Résiliée |
| Payment type | `encaissement` | Encaissement |
|  | `remboursement` | Remboursement |
| Formula version | `brouillon` | Brouillon |
|  | `soumis` | Soumise |
|  | `incomplet` | Incomplète |
|  | `en_etude` | En étude |
|  | `attente_info` | En attente d'information |
|  | `accepte` | Acceptée |
|  | `refuse` | Refusée |
|  | `remplace` | Remplacée |
| Review workflow | `soumise` | Soumise |
|  | `en_revue` | En revue |
|  | `info_demandee` | Informations demandées |
|  | `decidee` | Décidée |
| Review decision | `approuve` | Approuvé |
|  | `refuse` | Refusé |
| Evaluation workflow | `a_controler` | À contrôler |
|  | `incomplet` | Incomplet |
|  | `en_etude` | En étude |
|  | `en_attente` | En attente |
|  | `decide` | Décidé |
| Evaluation decision | `acceptee` | Acceptée |
|  | `refusee` | Refusée |
| Derogation scope | `tous_usages` | Tous les usages du laboratoire |
|  | `version` | Version de formule |
|  | `ligne` | Ligne de formule |
|  | `produit` | Produit |
| Derogation derived status | `a_venir` | À venir |
|  | `active` | Active |
|  | `expiree` | Expirée |
|  | `levee` | Levée |
| Attestation | `brouillon` | Brouillon |
|  | `emise` | Émise |
|  | `remplacee` | Remplacée |
|  | `revoquee` | Révoquée |
| Document control | `recu` | Reçu |
|  | `a_verifier` | À vérifier |
|  | `valide` | Validé |
|  | `refuse` | Refusé |
|  | `expire` | Expiré |
| Ingredient derived status | `brouillon` | Brouillon |
|  | `soumis` | Soumis |
|  | `en_revue` | En revue |
|  | `info_demandee` | Informations demandées |
|  | `approuve` | Approuvé |
|  | `refuse` | Refusé |
|  | `expire` | Expiré |
| Product commercial status | `brouillon` | Brouillon |
|  | `commercialise` | Commercialisé |
|  | `suspendu` | Suspendu |
|  | `retire` | Retiré |
| Activity status | `a_faire` | À faire |
|  | `en_cours` | En cours |
|  | `terminee` | Terminée |
|  | `annulee` | Annulée |

Additional controlled backed values:

- languages: `fr`, `es`, `en`, `it`;
- company origins: `linkedin`, `salon`, `bouche_a_oreille`, `site_web`, `presse`, `recommandation`, `autre`;
- contact roles: `dirigeant`, `qualite`, `formulation`, `commercial`, `administratif`, `autre`;
- payment methods: `virement`, `cheque`, `carte`, `autre`;
- ingredient origins: `vegetale`, `minerale`, `animale`, `synthese`, `mixte`, `autre`;
- label positions: `autorise`, `interdit`, `sous_conditions`, `a_statuer`;
- organic eligibility: `oui`, `non`, `conditionnel`, `inconnu`;
- under-one-percent answers: `oui`, `non`, `inconnu`;
- generic ingredient status: `a_valider`, `valide`, `archive`;
- evaluation-check results: `conforme`, `non_conforme`, `non_applicable`, `info_manquante`;
- document types: `dossier_technique`, `fiche_technique`, `certificat`, `attestation`, `kit_marketing`, `echange`, `autre`;
- document access: `interne`, `comite`, `adherent`;
- activity nature: `evenement`, `tache`;
- activity types: `appel`, `email`, `dossier_type_envoye`, `relance`, `kit_marketing_envoye`, `lien_retour_verifie`, `site_verifie`, `page_derogations_creee`, `participation_livret`, `reunion`, `note`, `autre`.

---

## 13. Explicit prototype exclusions

- real email delivery;
- notification translations beyond the French prototype templates; recipient language remains stored for future use;
- payment gateway and bank reconciliation;
- public directory;
- translated interface;
- OCR or PDF rasterization;
- multi-currency calculations beyond stored currency;
- production-complete GDPR tooling;
- public/self-registration of adherents.

The authenticated adherent workflow is included and must not be deferred.

---

## 14. Verification and definition of done

Before the first code change, build the baseline knowledge graph from the approved specifications and existing scaffold:

```text
/graphify .
```

After every agent code-generation or code-edit batch, including intermediate batches inside a slice, run:

```bash
vendor/bin/pint --dirty --format agent
```

Then refresh the persistent knowledge graph using the Graphify skill:

```text
/graphify . --update
```

Treat this as a batch gate: inspect and resolve Pint failures, confirm Graphify completed, and review its report for unexpected dependencies before generating the next code batch. Use the incremental update after the baseline instead of rebuilding the full graph each time.

Before each commit touching PHP, run the focused tests and:

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact --filter=<FocusedTest>
```

Before declaring a slice complete:

```bash
composer test
npm run build
```

Before the final demo:

```bash
php artisan migrate:fresh --seed --no-interaction
composer test
npm run build
php artisan route:list --except-vendor
```

Expected results: all commands exit successfully, no failed tests, seeded accounts can authenticate, and private documents are accessible only through authorized application routes.

### Demo walkthrough

1. Sign in as the adherent and create a product/formula using generic ingredients.
2. Create a laboratory ingredient inline and upload its documents.
3. Confirm it appears in the evaluator queue with a frozen snapshot.
4. Sign in as evaluator, inspect supplier/documents and approve once.
5. Return as adherent; confirm readiness updates and the ingredient is reusable in another formula without review.
6. Demonstrate a separately scoped derogation and prove it does not cover another line.
7. Submit the ready formula and prove technical editing is blocked.
8. Complete the evaluation and accept the formula.
9. Issue an annual attestation, rename the live product and prove the issued snapshot/PDF is unchanged.
10. Show dashboard, expiry and renewal views.

The prototype is ready only when this walkthrough and every verification command pass from a clean database.
