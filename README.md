# CRM Nouvelle Cosmétique

Laravel 13 + Filament 5 prototype for the Nouvelle Cosmétique adherent, ingredient-review, formula-evaluation and attestation workflow.

## Start here

Agents and contributors must read the documents in this order:

1. [`AGENTS.md`](AGENTS.md) — repository working rules;
2. [`BUILD_SPEC.md`](BUILD_SPEC.md) — authoritative technical build contract and definition of done;
3. [`docs/specs/functional-spec-v2.md`](docs/specs/functional-spec-v2.md) — current business requirements;
4. [`docs/decisions/architecture-decision.md`](docs/decisions/architecture-decision.md) — architectural rationale;
5. [`docs/plans/delivery-plan.md`](docs/plans/delivery-plan.md) — delivery sequence and coordination context.

If these documents conflict, stop and report the conflict. Do not silently choose an interpretation. Files under `docs/reference/` are historical source material, not the active implementation contract.

## Local setup

Use Laravel Herd and follow the repository `AGENTS.md` for version-specific Laravel, Filament, Livewire and testing practices.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

Set `SEED_USER_PASSWORD` in the local `.env` before seeding. The demo seeder is allowed only in the `local` and `testing` environments.

## Local demo accounts

All four accounts use the local value of `SEED_USER_PASSWORD`:

- `admin@crmnc.test` — administrateur;
- `ines@crmnc.test` — gestionnaire association;
- `comite@crmnc.test` — évaluateur scientifique;
- `adherent@crmnc.test` — adhérent.

Staff sign in at `/admin`; the adherent account signs in at `/portal`.

Never commit a real password or reuse these credentials outside local/testing environments.

## Documentation map

```text
BUILD_SPEC.md
docs/
├── specs/
│   └── functional-spec-v2.md
├── decisions/
│   └── architecture-decision.md
├── plans/
│   └── delivery-plan.md
└── reference/
    ├── original-brief.md
    └── functional-spec-v1.md
```

The root `AGENTS.md` remains the generated Laravel/Filament execution guide and must not be replaced by project requirements.
