@php
    use Filament\Support\Icons\Heroicon;

    $steps = [
        ['key' => 'draft', 'label' => 'Brouillon'],
        ['key' => 'ready', 'label' => 'Prêt'],
        ['key' => 'submitted', 'label' => 'Soumis'],
        ['key' => 'completeness', 'label' => 'Vérification'],
        ['key' => 'review', 'label' => 'Étude'],
        ['key' => 'decision', 'label' => 'Décision'],
    ];

    $currentStepIndex = $readiness['stage'] === 'ready' ? 1 : 0;
@endphp

<div class="product-cockpit">
    <style>
        .product-cockpit {
            display: grid;
            gap: 1rem;
        }

        .product-cockpit__identity {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .product-cockpit__identity p,
        .product-cockpit__check p,
        .product-cockpit__metric p {
            margin: 0;
        }

        .product-cockpit__title {
            font-size: 1.125rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .product-cockpit__subtitle,
        .product-cockpit__check-detail,
        .product-cockpit__metric-label {
            color: rgb(107 114 128);
            font-size: .875rem;
        }

        .dark .product-cockpit__subtitle,
        .dark .product-cockpit__check-detail,
        .dark .product-cockpit__metric-label {
            color: rgb(156 163 175);
        }

        .product-cockpit__steps {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: .5rem;
            margin-top: 1.25rem;
        }

        .product-cockpit__step {
            position: relative;
            display: grid;
            justify-items: center;
            gap: .45rem;
            color: rgb(156 163 175);
            font-size: .75rem;
            text-align: center;
        }

        .product-cockpit__step:not(:last-child)::after {
            position: absolute;
            top: .55rem;
            left: calc(50% + .75rem);
            width: calc(100% - 1.5rem);
            height: 2px;
            background: rgb(229 231 235);
            content: '';
        }

        .dark .product-cockpit__step:not(:last-child)::after {
            background: rgb(55 65 81);
        }

        .product-cockpit__step-marker {
            z-index: 1;
            width: 1.15rem;
            height: 1.15rem;
            border: 2px solid rgb(209 213 219);
            border-radius: 9999px;
            background: white;
        }

        .dark .product-cockpit__step-marker {
            border-color: rgb(75 85 99);
            background: rgb(17 24 39);
        }

        .product-cockpit__step--current {
            color: rgb(5 150 105);
            font-weight: 600;
        }

        .product-cockpit__step--current .product-cockpit__step-marker {
            border-color: rgb(16 185 129);
            background: rgb(16 185 129);
            box-shadow: inset 0 0 0 3px white;
        }

        .dark .product-cockpit__step--current .product-cockpit__step-marker {
            box-shadow: inset 0 0 0 3px rgb(17 24 39);
        }

        .product-cockpit__main {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(15rem, .5fr);
            gap: 1rem;
            align-items: start;
        }

        .product-cockpit__checks {
            display: grid;
            gap: .75rem;
        }

        .product-cockpit__check {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: .75rem;
            align-items: start;
        }

        .product-cockpit__check-label {
            font-size: .875rem;
            font-weight: 600;
        }

        .product-cockpit__summary {
            display: grid;
            gap: .75rem;
        }

        .product-cockpit__blocking-count {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }

        .product-cockpit__metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .product-cockpit__metric {
            padding: .875rem;
            border: 1px solid rgb(229 231 235);
            border-radius: .75rem;
        }

        .dark .product-cockpit__metric {
            border-color: rgb(55 65 81);
        }

        .product-cockpit__metric-value {
            margin-top: .25rem !important;
            font-size: .875rem;
            font-weight: 600;
        }

        @media (max-width: 767px) {
            .product-cockpit__identity,
            .product-cockpit__main {
                display: grid;
                grid-template-columns: 1fr;
            }

            .product-cockpit__steps {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                row-gap: 1rem;
            }

            .product-cockpit__step::after {
                display: none;
            }

            .product-cockpit__metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <x-filament::section
        heading="Cockpit produit"
        description="Suivez la préparation du dossier avant sa soumission."
        :icon="Heroicon::OutlinedRectangleStack"
    >
        <x-slot name="afterHeader">
            <x-filament::badge :color="$readiness['is_ready'] ? 'success' : 'gray'">
                {{ $readiness['stage_label'] }}
            </x-filament::badge>
        </x-slot>

        <div class="product-cockpit__identity">
            <div>
                <p class="product-cockpit__title">{{ $record->name }}</p>
                <p class="product-cockpit__subtitle">
                    {{ $record->brand }}
                    @if ($record->category)
                        · {{ $record->category->name }}
                    @endif
                </p>
            </div>
        </div>

        <div class="product-cockpit__steps" aria-label="Avancement du dossier produit">
            @foreach ($steps as $index => $step)
                <div
                    @class([
                        'product-cockpit__step',
                        'product-cockpit__step--current' => $index === $currentStepIndex,
                    ])
                    @if ($index === $currentStepIndex) aria-current="step" @endif
                >
                    <span class="product-cockpit__step-marker" aria-hidden="true"></span>
                    <span>{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <div class="product-cockpit__main">
        <x-filament::section
            heading="Avant de soumettre"
            description="Chaque point bloquant indique exactement ce qui reste à compléter."
        >
            <div class="product-cockpit__checks">
                @foreach ($readiness['checks'] as $check)
                    <div class="product-cockpit__check">
                        <x-filament::badge
                            :color="$check['passed'] ? 'success' : 'danger'"
                            :icon="$check['passed'] ? Heroicon::CheckCircle : Heroicon::XCircle"
                            size="sm"
                        >
                            {{ $check['passed'] ? 'Validé' : 'À compléter' }}
                        </x-filament::badge>

                        <div>
                            <p class="product-cockpit__check-label">{{ $check['label'] }}</p>
                            <p class="product-cockpit__check-detail">{{ $check['detail'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section
            heading="Action requise"
            :description="$readiness['is_ready']
                ? 'Le dossier remplit les conditions connues.'
                : 'La soumission restera indisponible tant que ces points ne sont pas résolus.'"
            :icon="$readiness['is_ready'] ? Heroicon::CheckCircle : Heroicon::ExclamationTriangle"
            :icon-color="$readiness['is_ready'] ? 'success' : 'warning'"
        >
            <div class="product-cockpit__summary">
                <p class="product-cockpit__blocking-count">{{ $readiness['blocking_count'] }}</p>
                <p class="product-cockpit__check-detail">
                    {{ trans_choice('{0} Aucun blocage|{1} blocage|[2,*] :count blocages', $readiness['blocking_count'], ['count' => $readiness['blocking_count']]) }}
                </p>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section heading="Vue d'ensemble" secondary>
        <div class="product-cockpit__metrics">
            <div class="product-cockpit__metric">
                <p class="product-cockpit__metric-label">Formule actuelle</p>
                <p class="product-cockpit__metric-value">
                    @if ($record->currentFormulaVersion)
                        {{ $record->currentFormulaVersion->formula->internal_code }} · v{{ $record->currentFormulaVersion->version_number }}
                    @else
                        Non sélectionnée
                    @endif
                </p>
            </div>

            <div class="product-cockpit__metric">
                <p class="product-cockpit__metric-label">Lignes de formule</p>
                <p class="product-cockpit__metric-value">
                    {{ $record->currentFormulaVersion?->lines->count() ?? 0 }}
                </p>
            </div>

            <div class="product-cockpit__metric">
                <p class="product-cockpit__metric-label">Client marque blanche</p>
                <p class="product-cockpit__metric-value">{{ $record->white_label_client ?: 'Non renseigné' }}</p>
            </div>
        </div>
    </x-filament::section>
</div>
