# CRM Nouvelle Cosmétique — Décision d'architecture

**Version 1 — juillet 2026**  
**Statut : prototype Laravel + Filament démarré ; architecture de production à valider**

---

## 1. Direction retenue pour le prototype

Le prototype est démarré sous Laravel + Filament. L'application est conçue dès le départ comme un système intégré avec deux interfaces sur les mêmes données et règles métier : l'espace adhérent pour la saisie et le panneau interne pour la validation humaine et la gestion.

Laravel, Filament et leurs dépendances seront documentés avec les versions réellement installées dans le prototype. Les numéros de version ne sont pas figés dans le cahier des charges métier afin de ne pas mélanger exigences fonctionnelles et contraintes techniques.

PostgreSQL est recommandé pour la production si l'hébergement existant le permet. SQLite en mode WAL reste adapté au prototype local, mais l'espace adhérent, les tâches automatiques et les écritures concurrentes rendent PostgreSQL plus prudent pour la mise en production.

---

## 2. Pourquoi une application dédiée

Le besoin dépasse un CRM de contacts : il comprend des données versionnées, des décisions scientifiques, des pièces confidentielles, des contraintes entre sociétés, formules et produits, ainsi que des attestations immuables.

Une base relationnelle permet notamment :

- d'empêcher certains doublons et liens incohérents ;
- de figer les versions soumises ;
- de conserver un audit fiable ;
- d'appliquer les droits selon le rôle et la société ;
- de générer des documents à partir de photographies de données ;
- de tester les règles métier automatiquement.

---

## 3. Comparaison raisonnée

| Critère | Airtable | Application dédiée |
|---|---|---|
| Démarrage et édition de listes | Très rapide | Développement initial nécessaire |
| Vues filtrées | Natives | Natives via l'interface d'administration |
| Relations simples | Adapté | Adapté |
| Contraintes fortes et transactions | Possibles seulement en partie, souvent par automatisation | Garanties dans la base et l'application |
| Versionnement scientifique | À construire avec précaution | Modélisable et testable explicitement |
| Portail par laboratoire | Nécessite une configuration ou un outil complémentaire | Peut partager le même modèle de données |
| Audit et autorisations fines | Dépend du plan et de la configuration | Contrôlés par l'application |
| Maintenance | Abonnement et dépendance au fournisseur | Hébergement, sauvegardes, mises à jour et support à assumer |
| Stockage | Limité par le plan | Limité par l'infrastructure choisie |
| Emails et automatisations | Quotas et possibilités selon le plan | Nécessitent un fournisseur d'email, du suivi et de la supervision |
| Export et réversibilité | Exports et API disponibles, avec limites selon les objets | Base et fichiers sous contrôle de l'association |

Airtable n'est donc pas incapable de porter un prototype. Il devient moins approprié lorsque la traçabilité scientifique, l'isolation des adhérents et les règles bloquantes constituent le cœur du produit.

---

## 4. Architecture fonctionnelle

### Espace adhérent — MVP

Chaque société peut :

- créer ses produits et leurs formules avec les ingrédients génériques ;
- sélectionner un ingrédient déjà approuvé dans sa propre bibliothèque ;
- créer un nouvel ingrédient depuis la formule et déposer ses documents ;
- répondre à une demande d'information ;
- suivre l'état de ses ingrédients, formules et produits ;
- télécharger ses attestations.

L'espace présente un parcours simple. Les composants INCI détaillés, versions documentaires et règles de validité restent derrière l'interface lorsque l'usage ne les exige pas.

### Interface administration — MVP

Utilisée par Michel, Inès et le comité selon leurs droits :

- relation prospects et adhérents ;
- référentiel des ingrédients génériques et fournisseurs ;
- file des ingrédients soumis avec lecture des documents et vérification du fournisseur ;
- actions Approuver, Demander des informations, Refuser et Créer une dérogation ;
- contrôle des formules et décisions sans réexaminer les ingrédients déjà approuvés ;
- documents et échéances ;
- attestations et vues opérationnelles.

L'isolation par société doit être testée systématiquement. Le panneau interne et l'espace adhérent ne sont pas deux applications indépendantes : ils utilisent les mêmes services d'approbation, de dérogation, de calcul de préparation et d'audit.

### Services transversaux

- stockage privé des documents ;
- génération PDF ;
- file de tâches pour emails et traitements longs ;
- service de calcul de préparation d'une formule : chaque ligne doit être couverte par un ingrédient approuvé ou une dérogation active ;
- journal d'audit ;
- sauvegarde de la base et des fichiers ;
- supervision des erreurs et des tâches en échec.

---

## 5. Décisions d'infrastructure à prendre

1. Hébergement disponible et contraintes techniques.
2. PostgreSQL disponible ou SQLite temporaire.
3. Stockage local sauvegardé ou stockage objet compatible S3.
4. Fournisseur d'envoi d'emails et domaine d'expédition.
5. Méthode de sauvegarde, durée de conservation et test de restauration.
6. Environnements de développement, recette et production.
7. Responsabilité de la maintenance, des mises à jour et du support.

---

## 6. Conditions de passage du prototype à la production

L'architecture de production est validée lorsque :

- le périmètre du MVP est accepté ;
- les coûts initiaux et récurrents sont estimés ;
- l'hébergement et le stockage sont confirmés ;
- la responsabilité de maintenance est attribuée ;
- le prototype Laravel + Filament prouve le parcours intégré : formule générique, création d'un ingrédient avec documents, approbation humaine unique, réutilisation immédiate, dérogation séparée, isolation par société et attestation immuable.
