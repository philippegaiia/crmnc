# Décision : espace adhérent séparé (`/portal`)

**Date : 24 octobre 2026**  
**Statut : Acceptée**

---

## Contexte

Le `BUILD_SPEC.md` initial décrivait un panneau Filament unique à `/admin`, avec une navigation et des données adaptées au rôle authentifié. L'implémentation et la décision d'architecture ont depuis séparé deux publics : les adhérents qui saisissent leurs données et l'équipe de l'association qui les instruit.

Le code expose aujourd'hui deux panneaux Filament : `/portal` (Espace adhérent) et `/admin` (Panneau interne). Cette séparation ne crée pas deux applications : les deux panneaux partagent les mêmes modèles, policies, services et requêtes limitées à la société.

## Décision

Conserver deux panneaux Filament :

- **`/portal` — Espace adhérent :** destiné aux membres (rôle `adherent`, rattaché à une société). Ils saisissent et fournissent leurs propres données : produits, formules, versions, ingrédients du laboratoire, documents, contacts et leur fiche société.
- **`/admin` — Panneau interne :** destiné au personnel de l'association (`admin`, `gestionnaire`, `evaluateur`). Il instruit et approuve ce que les adhérents fournissent, et gère les référentiels, les utilisateurs et toutes les sociétés.

L'accès est décidé par `User::canAccessPanel()` :

- panneau `admin` : utilisateurs actifs dont le rôle n'est pas `adherent` ;
- panneau `portal` : utilisateurs actifs de rôle `adherent` et rattachés à une société (`company_id` non nul).

`PortalPanelProvider` enregistre explicitement `CompanyResource`, `ContactResource`, `ProductResource`, `FormulaResource`, `FormulaVersionResource` et `LabIngredientResource`. `AdminPanelProvider` découvre toutes les ressources.

## Conséquences

- Une ressource n'atteint les adhérents que si elle est enregistrée dans `PortalPanelProvider` : c'est une couche supplémentaire au-dessus des policies et des requêtes limitées à la société.
- Chaque nouvelle ressource destinée aux adhérents doit être ajoutée à ce fournisseur et couverte par un test inter-sociétés.
- Les classes Filament partagées doivent rester adaptées au rôle, même lorsqu'elles sont exposées dans les deux panneaux.
