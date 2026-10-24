# CRM Nouvelle Cosmétique — Cahier des charges : modèle de données

**Version 1 — juillet 2026**
Document de travail préparé pour l'audit « Cap des 100 » (Semaine 1 : 20–26 juillet)

---

## 1. Objet de ce document

Ce document définit le **modèle de données** du CRM : les tables, leurs champs, leurs relations et les règles métier. Il répond à la question posée dans le brief initial :

> « Lister toutes les informations dont nous avons impérativement besoin pour gérer un adhérent, du premier contact jusqu'à la validation technique de sa formule. »

Il est **indépendant de l'outil** (Airtable ou application dédiée) : un bon modèle reste un bon modèle quel que soit l'outil. Le choix de l'outil est traité en section 8.

---

## 2. Principes directeurs

Cinq règles simples guident toute la structure :

1. **Le produit est au cœur commercial, la formule au cœur technique.** L'étude du comité porte sur la **formule** (sa composition). Une même formule peut donner plusieurs **produits** — ex. marque blanche : même savon, deux marques, deux noms. On ne ressaisit jamais deux fois la même formule.
2. **Un ingrédient vérifié appartient à un laboratoire.** La vérification (certificat fournisseur) se fait par laboratoire, mais le vocabulaire (noms INCI, fournisseurs) est partagé par tous.
3. **Tout ce qui se répète dans le temps devient une table.** Une adhésion, c'est chaque année. Un paiement, c'est chaque année. Une case à cocher « payé » ne peut pas représenter 2026 payé et 2027 en attente.
4. **Tout événement daté est tracé dans un journal.** « Kit marketing envoyé » n'est pas une case à cocher : c'est un événement avec une date, qu'on pourra refaire l'an prochain.
5. **Le vocabulaire est contrôlé.** Catégories de produits, noms INCI, pays, langues : des listes de référence gérées par l'association, pas du texte libre (sinon on se retrouve avec « SAF », « savon à froid » et « saponification à froid » dans trois fiches différentes).

---

## 3. Vue d'ensemble — 15 tables

### Gestion des adhérents
| Table | Rôle |
|---|---|
| **Sociétés** | Les laboratoires (prospects et adhérents) |
| **Contacts** | Les personnes (dirigeant, responsable qualité…) — plusieurs par société |
| **Adhésions** | Une ligne par société **et par année** |
| **Paiements** | Les règlements, rattachés à une adhésion |

### Certification des produits
| Table | Rôle |
|---|---|
| **Produits** | Les produits commerciaux déclarés (nom, marque, client final éventuel) |
| **Formules** | La bibliothèque de formules du labo — une formule peut servir plusieurs produits |
| **Versions de formule** | Chaque état d'une formule soumis à évaluation (reformulation = nouvelle version) |
| **Lignes de composition** | Les ingrédients d'une version, avec pourcentages |
| **Dérogations** | Accordées par produit / ingrédient, avec échéance |
| **Catégories de produits** | SAF, émulsion, huile pure… (sert aussi pour les spécialités des sociétés) |

### Ingrédients
| Table | Rôle |
|---|---|
| **Référentiel ingrédients (INCI)** | Le vocabulaire officiel, **global**, géré par l'association |
| **Fournisseurs** | Référence partagée, **globale** (nom, pays, organisme certificateur) |
| **Ingrédients du laboratoire** | Ce que chaque labo achète réellement : INCI + fournisseur + certificat — **vérifié par labo** |

### Transversal
| Table | Rôle |
|---|---|
| **Documents** | Toutes les pièces jointes (dossiers techniques, attestations, certificats, kit marketing) |
| **Journal d'activités** | Tous les événements datés (envois, relances, vérifications…) |
| **Historique des statuts** | La mémoire des changements de statut (prospect → adhérent → arrêt, avec causes) |

### Schéma des relations

```
SOCIÉTÉS ──┬──< CONTACTS
           ├──< ADHÉSIONS ──< PAIEMENTS
           │
           ├──< FORMULES ──< VERSIONS DE FORMULE ──< LIGNES DE COMPOSITION ──> INGRÉDIENTS DU LABO ──> RÉF. INCI (global)
           │      ▲                                        │                            └──────> FOURNISSEURS (global)
           │      │                                        └──< DÉROGATIONS (par ligne)
           │      │
           ├──< PRODUITS ──── utilisent une VERSION DE FORMULE
           │      (plusieurs produits peuvent partager la même formule : marque blanche)
           │
           ├──< DOCUMENTS (rattachables aussi aux produits, versions, ingrédients)
           ├──< JOURNAL D'ACTIVITÉS
           └──< HISTORIQUE DES STATUTS

CATÉGORIES DE PRODUITS ──┬── classifie les PRODUITS
                         └── spécialités des SOCIÉTÉS (même liste !)
```

---

## 4. Dictionnaire des champs

### 4.1 SOCIÉTÉS
| Champ | Type | Remarque |
|---|---|---|
| Nom de l'entreprise | Texte | |
| Statut | Liste : **Demande d'info** / Prospect / Dossier type envoyé / Relance / Adhérent actif / Sans suite / Arrêt | Chaque changement est tracé dans l'historique |
| Origine | Liste : LinkedIn / Salon / Bouche-à-oreille / Site web / Presse / Autre | D'où vient le contact — indispensable pour mesurer ce qui amène des adhérents |
| Langue privilégiée | Liste : Français / Espagnol / Anglais / Italien | |
| Adresse, CP, ville | Texte | |
| Pays | Liste de référence | Le « Développement Espagne 2026 » est un **filtre**, pas une table |
| Téléphone, email principal | Texte | |
| Site web, LinkedIn, Instagram | URL | |
| Spécialités techniques | Liens multiples → Catégories de produits | La même liste que les produits |
| Notes | Texte long | |

> « Cause de l'arrêt » n'est pas un champ : c'est enregistré avec date et motif dans l'**Historique des statuts**. On garde ainsi toute la mémoire (prospect en 2025, adhérent en 2026, arrêt en 2028 pour telle cause).

### 4.2 CONTACTS
| Champ | Type | Remarque |
|---|---|---|
| Société | Lien | |
| Prénom, Nom | Texte | |
| Fonction | Liste : Dirigeant / Responsable qualité / Formulateur / Commercial / Autre | |
| Email, téléphone | Texte | |
| Contact principal | Oui/Non | |
| Langue préférée | Liste | Par défaut celle de la société |

> Fini les « Dirigeant 2, email2, autre1, autre2 » : on ajoute autant de contacts que nécessaire, proprement.

### 4.3 ADHÉSIONS
| Champ | Type | Remarque |
|---|---|---|
| Société | Lien | |
| Année | Nombre | Une ligne par année : 2026, 2027… |
| Date de début / fin | Dates | |
| Montant dû | Montant | |
| Statut | Liste : En attente / Active / Expirée / Résiliée | |

### 4.4 PAIEMENTS
| Champ | Type | Remarque |
|---|---|---|
| Adhésion | Lien | |
| Date, montant, mode (virement/chèque/CB), référence | | |
| Statut | Liste : En attente / Payé / Remboursé | |

### 4.5 CATÉGORIES DE PRODUITS
Liste gérée par l'association : saponification à froid, émulsion (crème/lait), huile pure, baume, shampoing solide, déodorant, hydrolat, dentifrice, maquillage…
Sert **deux fois** : pour classer les produits, et pour les spécialités des sociétés.

### 4.6 PRODUITS
| Champ | Type | Remarque |
|---|---|---|
| Société | Lien | |
| Nom commercial | Texte | |
| Marque | Texte | |
| Client final (marque blanche) | Texte, optionnel | Ex. même savon vendu sous la marque d'un distributeur |
| Catégorie | Lien → Catégories | |
| Version de formule utilisée | Lien → Versions de formule | La validité technique du produit = celle de la version liée |
| Statut | Liste : Déclaré / Formule à l'étude / Validé / Retiré | « Validé » est hérité de la décision sur la formule |
| Notes | Texte long | |

> **Règle métier clé :** plusieurs produits peuvent pointer vers la même version de formule. L'étude technique se fait **une seule fois** ; l'attestation liste chaque produit commercial séparément.

### 4.7 FORMULES
| Champ | Type | Remarque |
|---|---|---|
| Société | Lien | La formule appartient au labo (sa « bibliothèque ») |
| Nom interne / code | Texte | Ex. « Base savon SAF n°3 » — le labo la nomme comme il veut |
| Description, notes | Texte | |

### 4.8 VERSIONS DE FORMULE
| Champ | Type | Remarque |
|---|---|---|
| Formule | Lien → Formules | |
| N° de version | Automatique | Reformulation = nouvelle version, l'ancienne est conservée |
| Fichier dossier technique | Document | |
| Date de réception | Date | |
| Date limite d'étude | Date **calculée** | Réception + 15 jours, avec alerte |
| Statut d'évaluation | Liste : En cours d'étude / Acceptée / Refusée / Dérogation | |
| Motif de refus / commentaires du comité | Texte long | |

> **Reformulation :** les produits restent liés à l'ancienne version tant que le labo ne les bascule pas — d'où une vue « produits sur version obsolète ».

### 4.9 LIGNES DE COMPOSITION
| Champ | Type | Remarque |
|---|---|---|
| Version de formule | Lien | |
| Ingrédient (INCI) | Lien → Référentiel global | **1re liste :** on choisit l'ingrédient (ex. huile de coco) |
| Ingrédient du labo | Lien → Ingrédients du labo | **2e liste :** filtrée par l'INCI choisi — on sélectionne sa propre huile de coco vérifiée (fournisseur X) |
| Pourcentage | Décimal | **La somme doit faire 100 %** — contrôle bloquant à la saisie |
| QSP | Oui/Non | « quantum satis » (ex. eau) |

**Saisie décentralisée — le client fait le travail :** si le labo n'a pas encore d'ingrédient vérifié pour cet INCI, il le déclare sur-le-champ (fournisseur, nom commercial) et dépose le certificat ; l'ingrédient passe « en vérification », la plateforme (le comité) le valide, et un email de confirmation part au labo.

**Deux garde-fous :**
- une version de formule ne peut pas être **acceptée** tant qu'une de ses lignes pointe vers un ingrédient non vérifié — le pré-contrôle signale ces lignes au comité (la déclaration, elle, reste libre : pas de friction à la saisie) ;
- une version acceptée est **figée** : changer un ingrédient, un fournisseur ou un % = nouvelle version (traçabilité complète).

**Deux résultats automatiques :**
- la **liste INCI officielle** est générée seule (tri par % décroissant, règle des < 1 %) — personne ne la retape ;
- si le cahier des charges fixe des seuils (ex. % minimum bio), la formule affiche un **pré-contrôle de conformité** avant même l'étude du comité.

### 4.10 RÉFÉRENTIEL INGRÉDIENTS — global
| Champ | Type | Remarque |
|---|---|---|
| Nom INCI | Texte | Ex. *Cocos Nucifera Oil* |
| Nom(s) commun(s) | Texte | Ex. huile de coco |
| Famille | Liste : huile végétale / beurre / huile essentielle / émulsifiant / conservateur / tensioactif / hydrolat / minéral / cire… | |
| Origine | Liste : végétale / minérale / animale / synthèse | |
| Position du label | Liste : Autorisé / Interdit / Sous conditions | |
| Éligible bio | Oui/Non | Sert au calcul du % bio des formules |
| Statut | Liste : Validé / À valider | Un ingrédient créé par un labo arrive « à valider » par le comité |

### 4.11 FOURNISSEURS — global
| Champ | Type |
|---|---|
| Nom, pays, site web | Texte |
| Organisme certificateur | Liste (Ecocert, USDA…) |

### 4.12 INGRÉDIENTS DU LABORATOIRE — vérifiés par labo
| Champ | Type | Remarque |
|---|---|---|
| Société | Lien | **La vérification est par laboratoire** |
| Ingrédient (INCI) | Lien → Référentiel | |
| Fournisseur | Lien → Fournisseurs | |
| Nom commercial / réf. fournisseur | Texte | |
| Certificat | Document | Certificat bio, fiche technique |
| Statut | Liste : Déclaré / En vérification / Vérifié / Refusé | |
| Date de vérification, vérifié par | | |

> **Règle d'unicité :** une société ne peut pas avoir deux fois le même couple INCI + fournisseur. Pas de doublons possibles.
>
> **Bonus sans effort :** si trois labos déclarent la même huile de coco du même fournisseur, le comité le voit et vérifie plus vite — sans créer pour autant une liste approuvée globale (qui n'est pas prévue).

### 4.13 DÉROGATIONS
| Champ | Type | Remarque |
|---|---|---|
| Version de formule, ligne concernée | Liens | S'applique automatiquement à tous les produits utilisant cette version |
| Motif | Texte long | |
| Date d'accord, date d'échéance | Dates | |
| Statut | Accordée / Expirée / Levée | |

### 4.14 DOCUMENTS
Pièce jointe rattachée à une société, un produit, une version de formule ou un ingrédient. Type : dossier technique / attestation / certificat / kit marketing / autre. Avec date d'envoi ou de réception.

### 4.15 JOURNAL D'ACTIVITÉS
| Champ | Type | Exemples |
|---|---|---|
| Société | Lien | |
| Type | Liste | Envoi dossier type, relance, envoi kit marketing, lien retour vérifié, participation livret, page dérogations créée, appel, email, note |
| Date | Date | |
| Commentaire | Texte | |

> Toutes les cases à cocher du brief initial (« kit envoyé », « lien retour vérifié », « participation livret ») deviennent des événements datés. La « case » existe toujours visuellement, mais elle est **calculée** depuis le journal — et on sait quand, et on pourra la refaire l'an prochain.

---

## 5. Les cycles de vie

**Cycle commercial :** Demande d'info (contact entrant, non qualifié) → Prospect (qualifié) → Dossier type envoyé → Relance → Adhérent actif → (Arrêt, avec date + cause tracées). Un prospect qui ne donne pas suite passe en « Sans suite » (on le distingue de l'« Arrêt », réservé aux anciens adhérents).

**Cycle d'une formule :** Créée → Composition complète (Σ = 100 %) → Dossier reçu → En cours d'étude (15 jours, avec alerte de date limite) → Acceptée / Refusée / Dérogation → (reformulation = nouvelle version, l'ancienne est conservée)

**Cycle d'un produit :** Déclaré → Lié à une version de formule → (hérite de l'étude de la formule) → Validé → Attestation annuelle → Relance N+1 → Retiré

**Emails automatiques :** chaque étape validée déclenche un email à l'adhérent (dossier bien reçu, ingrédient vérifié, décision du comité, attestation disponible, relance N+1…) — **rédigé dans sa langue préférée** (français, espagnol, anglais), ce qui prépare l'ouverture internationale.

**Cycle d'un ingrédient (par labo) :** Déclaré → En vérification → Vérifié / Refusé

**Cycle annuel :** Adhésion N → Paiement → Attestation avec liste des produits générée automatiquement → Relance adhésion N+1

---

## 6. Les vues

Toutes les vues demandées dans le brief, plus celles qui manquaient :

1. Tous les contacts ✔
2. Pipeline Prospects (statuts Demande d'info → Relance) ✔
3. Annuaire Adhérents Actifs ✔
4. Développement Espagne 2026 (filtre langue = espagnol **ou** pays = Espagne) ✔
5. Dossiers en cours d'étude, **triés par date limite des 15 jours** ✔
6. Relances à prévoir ✔
7. **Ingrédients en attente de vérification** (nouveau)
8. **Attestations à renouveler** (nouveau)
9. **Paiements en attente** (nouveau)
10. **Dérogations arrivant à échéance** (nouveau)
11. **Produits liés à une version de formule obsolète** (après reformulation) (nouveau)

---

## 7. Le point clé : les ingrédients, vus par l'artisan

L'artisan raisonne en deux temps : **« je mets de l'huile de coco »**, puis **« celle que j'achète à Huilerie X »**.

Donc dans l'écran de composition d'une formule, chaque ligne propose **deux listes enchaînées** :

1. **L'ingrédient (référentiel global)** : `Huile de coco — Cocos Nucifera Oil`
2. **Son ingrédient à lui**, filtré automatiquement : `Huilerie X · Bio ✓ vérifié`

Si aucun ingrédient vérifié n'existe pour cet INCI : un petit formulaire « déclarer cet ingrédient » — choix ou création du fournisseur, nom commercial, dépôt du certificat — et l'ingrédient part « en vérification ». **Le client saisit, la plateforme valide** (le comité), avec email de confirmation. L'artisan ne gère jamais les catégories ni le référentiel : c'est le travail de l'association. (Et si l'INCI lui-même est inconnu du référentiel, il peut le suggérer : il part « à valider » au comité.)

**Conséquence pour la saisie :** chaque labo construit progressivement **sa propre palette d'ingrédients vérifiés**, réutilisable instantanément dans toutes ses formules et reformulations.

---

## 8. Choix de l'outil : Airtable ou application dédiée ?

### Ce qu'Airtable fait bien (honnêtement)
Mise en route immédiate, interface tableur agréable, vues filtrées natives, zéro développement. Pour des listes simples, c'est un bon outil.

### Pourquoi ce projet précis dépasse Airtable

| Besoin du projet | Airtable | Application dédiée (Laravel + Filament) |
|---|---|---|
| Vues filtrées, tri, recherche | ✔ Natif | ✔ Natif (identique à l'usage) |
| Pièces jointes | ✔ (stockage limité par plan) | ✔ (serveur propre, sans limite) |
| Ingrédient = labo + INCI + fournisseur, **sans doublon possible** | ✘ Pas de vraie contrainte d'unicité — les doublons arrivent vite | ✔ Garanti par la base |
| Vérification par labo + vue transversale comité | ✘ Liaisons fragiles | ✔ Natif |
| Contrôle « somme des % = 100 » à la saisie | ✘ | ✔ Bloquant |
| Liste INCI générée automatiquement | ✘ | ✔ |
| Attestation annuelle PDF avec liste des produits | ✘ (extensions tierces payantes) | ✔ Générée en un clic |
| Relances email automatiques (15 jours, N+1, paiements) | Limité par plan | ✔ Inclus |
| Historique complet des statuts | Partiel | ✔ Complet |
| Évolutions futures : portail adhérent, annuaire public sur le site | Payant / limité | ✔ Même base, même outil |
| Coût | Gratuit jusqu'à ~500 lignes, puis ~20 €/mois/**par utilisateur** (Michel + Inès + comité = coût récurrent qui grandit) | Hébergement existant, coût marginal ; effort de développement initial |
| Pérennité | Données chez Airtable, export CSV seulement | Base de données à vous, aucun verrou |
| Qui peut casser la structure | Tout le monde (un champ supprimé par mégarde…) | Droits par rôle |

### Recommandation
**Valider d'abord ce modèle avec Michel (Semaine 1), puis construire sur Laravel 13 + Filament 5 + Livewire 4 (Semaines 3 à 5 du rétroplanning existant).** Filament offre exactement l'interface tableur/filtres qu'Airtable aurait donnée — Michel retrouve ses repères — avec les règles métier qu'Airtable ne peut pas tenir. Le rétroplanning « CRM opérationnel le 23 août » reste tenable si le périmètre reste le CRM seul (charte graphique et traductions du site restent des chantiers parallèles, inchangés).

### Architecture retenue

Une seule application, deux interfaces :

- **Panneau Admin** (association : Michel, Inès, comité scientifique) — gestion complète, vues filtrées, validation des produits et ingrédients, génération des attestations.
- **Espace Adhérent** (phase 2) — chaque laboratoire suit ses produits, déclare ses ingrédients, dépose ses certificats, télécharge ses attestations. Même application, mêmes règles métier ; chaque labo ne voit **que ses données** (isolation native de Filament). Des pages sur mesure en composants Livewire restent possibles si besoin, sans rien jeter de l'existant.
- **Tableau de bord adhérent** — dès la connexion, l'essentiel : statut d'adhésion, avancement de chaque produit, ingrédients en attente de vérification, échéances (relance N+1, dérogations, paiement). Sur la page d'un produit, une **frise d'étapes** montre où en est le dossier (déclaré → formule liée → à l'étude → décision → attestation) et ce qui reste à compléter.

Hébergement simple : base **SQLite (mode WAL)**, largement suffisante à cette échelle (quelques centaines de membres), avec sauvegarde répliquée ; les pièces jointes vivent sur disque ou stockage objet, jamais dans la base. Migration PostgreSQL possible plus tard sans réécriture si la croissance l'exigeait un jour.

> À noter : l'option Airtable aurait de toute façon exigé un outil de portail supplémentaire (type Softr) pour offrir cet espace membre — soit **deux abonnements par utilisateur** pour approcher ce qu'une seule application fait nativement… et toujours sans la logique métier (contrôle des %, INCI automatique, attestations PDF).
>
> L'Espace Adhérent est aussi la vraie réponse au « Cap des 100 » : la vérification des ingrédients étant **par laboratoire**, ce sont les labos eux-mêmes qui déclarent et déposent leurs certificats. À 100 membres, une saisie 100 % manuelle par Michel/Inès serait le goulot d'étranglement ; la saisie décentralisée + validation par le comité est le seul modèle qui tienne la charge.

---

## 9. Questions ouvertes pour Michel

1. **Seuils chiffrés :** le cahier des charges / les douze piliers fixent-ils des minimums (ex. % bio, % d'origine naturelle) ? Si oui → pré-contrôle automatique des formules.
2. **Paiements :** simple suivi de statut, ou faut-il aussi factures et rapprochement bancaire ?
3. **Espace Adhérent :** principe validé — chaque labo saisira à terme ses produits et ingrédients dans son espace sécurisé. Reste à fixer le calendrier : dès septembre, ou après la montée vers 100 membres ?
4. **Annuaire public :** le site doit-il afficher automatiquement les adhérents actifs ?
5. **Attestation annuelle :** génération PDF automatique avec liste des produits acceptés — validé ?
6. **Utilisateurs du CRM :** qui ? (Michel, Inès, membres du comité scientifique en lecture seule ?)
7. **Langues :** interface du CRM en français seul, données multilingues ?
8. **Marque blanche & facturation :** une même formule peut donner plusieurs produits (noms, marques ou clients finaux différents). Facture-t-on à la formule étudiée, au produit attesté, ou est-ce inclus dans l'adhésion ? → **Réponse (juillet 2026) : facturation gérée hors plateforme pour l'instant.** Une passerelle de paiement (Creem, Stripe…) pourra être ajoutée plus tard, sans impact sur le modèle.

---

## 10. Rétroplanning proposé (aligné sur celui du brief)

| Semaine | Contenu CRM |
|---|---|
| S1 (20–26 juil.) | Validation de ce modèle avec Michel — réponses aux 7 questions |
| S2 (27 juil.–2 août) | Chantier graphique Canva (inchangé) — préparation des modèles de documents (attestation, dossier technique) dont le CRM se servira |
| S3 (3–9 août) | Construction : sociétés, contacts, adhésions — premières saisies réelles |
| S4 (10–16 août) | Produits, formules, ingrédients, dérogations — le cœur certification |
| S5 (17–23 août) | Vues, relances, génération des attestations, tests de bout en bout — **CRM opérationnel le 23 août** ✔ |
| S6–S9 | Chantiers internationaux et marge (inchangés) |
