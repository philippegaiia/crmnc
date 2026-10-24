# Agent handoff — CRM Nouvelle Cosmétique

This repository is ready to begin implementation from a clean Laravel 13 + Filament 5 baseline.

## Read before changing code

1. `AGENTS.md` — mandatory repository and framework working rules;
2. `BUILD_SPEC.md` — authoritative technical build contract and definition of done;
3. `docs/specs/functional-spec-v2.md` — current business specification;
4. `docs/decisions/architecture-decision.md` — approved architecture;
5. `docs/plans/delivery-plan.md` — delivery context and sequencing.

If these documents conflict, stop and report the conflict. Do not silently invent an interpretation. Documents under `docs/reference/` are preserved historical sources, not the active implementation contract.

## Current repository state

- Laravel, Filament, Livewire and Pest are installed; the application is otherwise the initial scaffold.
- One Filament panel already exists at `/admin`.
- The project specifications and documentation have been copied into the repository.
- Graphify is installed locally. Create the baseline project graph before the first code change, then update it incrementally after every code-generation batch.
- `AGENTS.md` is the existing Laravel/Filament execution guide and must not be replaced.
- Check `git status` before editing. The documentation handoff may still be uncommitted.

## Approved product decisions

- Build one integrated, role-aware application for admin, gestionnaire, évaluateur and adhérent.
- The authenticated adherent workflow is part of the prototype and must not be deferred.
- An adherent first enters a formula using generic ingredients, then links or creates laboratory-owned ingredients.
- Ingredient approval is human: an evaluator reads the submitted documents, checks the supplier and records Approve, Request information or Refuse.
- An unchanged, unexpired approval is reusable for that laboratory without another laborious review.
- Derogations are separate, explicitly scoped records and never mutate ingredient approval status.
- Membership activation is a manual admin/gestionnaire action; payment never auto-activates it.
- Evaluation SLA pauses while the evaluation is `en_attente` and resumes with the remaining duration preserved.
- Prototype notifications use French templates; the recipient's preferred language remains stored for later translation.
- Use a single role-aware Filament `/admin` panel unless an approved specification change says otherwise.
- Implement the system as connected vertical slices so the adherent and evaluator workflows work together throughout development.

## How to start

1. Inspect the repository and verify the installed versions against `BUILD_SPEC.md`.
2. Review the current working tree and preserve all existing documentation changes.
3. Create a concrete implementation plan for the seven vertical slices in `BUILD_SPEC.md`, with file ownership if multiple agents will work concurrently.
4. Run `/graphify .` before changing code to create the baseline knowledge graph from the specifications and scaffold.
5. Start Slice 1: roles, company boundary, policies and scoped queries.
6. Follow test-driven development: add the failing Pest test first and implement the smallest complete vertical behavior.
7. After every agent code-generation or code-edit batch, run `vendor/bin/pint --dirty --format agent`, then `/graphify . --update`, before reporting completion or generating the next batch.

Do not begin by generating all 24 tables as a disconnected schema phase. Each slice must deliver a testable part of the final end-to-end workflow.

Pint and Graphify are mandatory batch gates, not only end-of-slice checks. The initial Graphify run is full; all later runs are incremental. Inspect Graphify's report for unexpected coupling or contradictions before continuing.

## Local demo configuration

Set `SEED_USER_PASSWORD` in the local `.env`. Demo users are:

- `admin@crmnc.test`;
- `ines@crmnc.test`;
- `comite@crmnc.test`;
- `adherent@crmnc.test`.

Never commit a real password or create these demo accounts outside `local` or `testing`.
