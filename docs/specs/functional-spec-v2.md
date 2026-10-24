# CRM Nouvelle Cosmétique — Cahier des charges fonctionnel et modèle de données

**Version 2 — juillet 2026**  
**Statut : à valider avec Michel, Inès et le comité scientifique**

Ce document transforme le brief initial et les échanges de cadrage en exigences métier vérifiables. Il décrit ce que le CRM doit gérer, indépendamment de l'outil retenu. Le choix technique et le calendrier sont traités dans deux documents séparés.

Documents associés :

- `First brief from Michel Pobeda pour nouvelle cométique bio..md` : source historique ;
- `Cahier des charges CRM Nouvelle Cosmétique - Modèle de données v1.md` : première proposition ;
- `Décision d'architecture CRM Nouvelle Cosmétique.md` : choix de la solution ;
- `Plan de livraison CRM Nouvelle Cosmétique.md` : périmètre et calendrier.

---

## 1. Objectifs

Le CRM doit permettre à Nouvelle Cosmétique de passer d'environ 50 à 100 adhérents sans perdre la traçabilité commerciale, administrative ou scientifique.

Il doit couvrir :

1. le premier contact et la qualification d'un laboratoire ;
2. les contacts rattachés à ce laboratoire ;
3. l'adhésion annuelle et le suivi manuel des règlements ;
4. les produits commerciaux et les formules qui les composent ;
5. les versions successives d'une formule ;
6. les ingrédients génériques, les ingrédients propres à chaque laboratoire et leurs justificatifs ;
7. la soumission par l'adhérent, la validation humaine des ingrédients, l'évaluation scientifique et les dérogations ;
8. l'émission et le renouvellement des attestations ;
9. les documents, échanges, échéances et relances ;
10. la saisie décentralisée par les adhérents dans leur espace sécurisé.

### Hors périmètre de la première mise en production

- facturation et comptabilité complètes ;
- rapprochement bancaire automatique ;
- paiement en ligne ;
- annuaire public synchronisé avec le site ;
- traduction complète de l'interface ;
- fonctions avancées du portail adhérent sans lien avec le parcours produit-formule-ingrédient ;
- gestion de lots de fabrication ou de traçabilité industrielle.

Ces éléments pourront être ajoutés sans remettre en cause le modèle central.

---

## 2. Principes directeurs

1. **Une société reste une seule société.** Elle peut être prospect, adhérente, ancienne adhérente, puis revenir. Elle n'est jamais déplacée entre des tables « Prospects » et « Adhérents ».
2. **Le produit est commercial ; la formule est technique.** Plusieurs produits peuvent utiliser la même version acceptée d'une formule.
3. **Une reformulation crée une nouvelle version.** Une version soumise ou acceptée n'est jamais modifiée silencieusement.
4. **La formule commence par l'ingrédient générique.** L'adhérent peut construire sa formule immédiatement avec le référentiel générique, puis sélectionner ou créer l'ingrédient précis de son laboratoire.
5. **Un ingrédient du laboratoire est contrôlé une fois, puis réutilisé.** L'adhérent le crée et dépose ses justificatifs ; un évaluateur vérifie humainement les documents et le fournisseur, puis clique sur Approuver, Demander des informations ou Refuser. Un ingrédient approuvé n'est pas réexaminé pour chaque formule.
6. **Une matière première commerciale peut contenir plusieurs composants INCI.** Cette complexité reste disponible dans le modèle sans alourdir la saisie courante.
7. **Une adhésion et une attestation sont annuelles.** Leur historique est conservé ; l'état courant ne remplace jamais les années précédentes.
8. **Les statuts calculables ne sont pas ressaisis.** Par exemple, « adhérent actif », « paiement soldé » et « éligibilité technique du produit » sont dérivés de leurs données sources.
9. **Toute décision importante est attribuée et datée.** Le système conserve qui a validé quoi, quand, selon quelle version du cahier des charges et avec quelles pièces justificatives.
10. **Les dérogations sont des décisions séparées.** Elles ne remplacent jamais le statut d'approbation d'un ingrédient et possèdent une portée, des conditions et une échéance propres.
11. **Le vocabulaire est contrôlé.** Pays, langues, catégories, INCI, types de documents et motifs de décision utilisent des listes administrées.
12. **Les données d'une société sont isolées.** Un adhérent ne peut voir que ses propres données ; le comité ne voit que ce qui est nécessaire à l'évaluation.

---

## 3. Vue d'ensemble des données

Le modèle comprend **22 tables métier principales**, auxquelles s'ajoutent les comptes utilisateurs, rôles et tables techniques du framework.

### Relation adhérent

1. **Sociétés** — laboratoires prospects, adhérents et anciens adhérents.
2. **Contacts** — personnes rattachées aux sociétés.
3. **Historique des statuts commerciaux** — changements du pipeline et leurs motifs.
4. **Adhésions** — une ligne par société et période d'adhésion.
5. **Paiements** — règlements réellement reçus ou remboursés, rattachés à une adhésion.

### Produits et formules

6. **Catégories de produits** — référentiel géré par l'association.
7. **Produits** — identités commerciales attestables.
8. **Formules** — recettes techniques appartenant à une société.
9. **Versions de formule** — états successifs, figés après soumission.
10. **Lignes de composition** — ingrédient générique, ingrédient du laboratoire et pourcentage d'une version.

### Matières premières et INCI

11. **Référentiel des ingrédients génériques / INCI** — vocabulaire global utilisé dès la création d'une formule.
12. **Fournisseurs** — référentiel global.
13. **Ingrédients du laboratoire** — matières premières commerciales créées et possédées par une société.
14. **Composants INCI des ingrédients du laboratoire** — décomposition facultative d'une matière première en un ou plusieurs INCI.

### Évaluation et attestation

15. **Revues d'ingrédient** — soumissions et décisions humaines sur les documents d'un ingrédient du laboratoire.
16. **Évaluations de formule** — instruction scientifique d'une version de formule sans refaire les revues d'ingrédients déjà approuvés.
17. **Contrôles d'évaluation** — réponses aux critères de la grille scientifique.
18. **Dérogations** — décisions séparées, limitées dans leur portée et dans le temps.
19. **Attestations** — documents annuels émis, remplacés ou révoqués.
20. **Produits attestés** — photographie immuable des produits et versions couverts par une attestation.

### Transversal

21. **Documents** — pièces reçues ou émises, versionnées et rattachées à une entité métier.
22. **Activités et tâches** — échanges, envois, vérifications, relances et actions à réaliser.

Le journal d'audit est un service technique distinct : il enregistre les modifications sensibles, tandis que les activités et tâches décrivent le travail métier visible par les utilisateurs.

### Relations principales

```text
SOCIÉTÉ
├── CONTACTS
├── HISTORIQUE COMMERCIAL
├── ADHÉSIONS ──< PAIEMENTS
├── PRODUITS ────────> VERSION DE FORMULE COURANTE
├── FORMULES ──< VERSIONS ──< LIGNES DE COMPOSITION
│                         │            ├──> INGRÉDIENT GÉNÉRIQUE (obligatoire)
│                         │            └──> INGRÉDIENT DU LABO (optionnel jusqu'à validation)
│                         └──< ÉVALUATIONS ──< CONTRÔLES
├── INGRÉDIENTS DU LABO ──< REVUES HUMAINES
│              ├──< COMPOSANTS ──> RÉFÉRENTIEL INCI
│              └──> FOURNISSEUR
└── ADHÉSIONS ──< ATTESTATIONS ──< PRODUITS ATTESTÉS

DÉROGATIONS ──> ingrédient du labo, version, ligne ou produit selon leur portée
DOCUMENTS ──> société, adhésion, matière première, version, évaluation,
              dérogation ou attestation
ACTIVITÉS ET TÂCHES ──> société et, si utile, contact, produit ou dossier
```

---

## 4. Dictionnaire des données et règles métier

### 4.1 Sociétés

| Champ | Type | Règle |
|---|---|---|
| Nom légal | Texte | Obligatoire |
| Nom usuel | Texte | Optionnel |
| Statut commercial | Liste | Demande d'information, Prospect qualifié, Dossier envoyé, Relance, Converti, Sans suite |
| Origine | Liste | LinkedIn, salon, bouche-à-oreille, site web, presse, recommandation, autre |
| Langue privilégiée | Référence | Français, espagnol, anglais, italien |
| Adresse, code postal, ville, pays | Champs séparés | Pays issu d'un référentiel |
| Téléphone et email généraux | Champs typés | Optionnels si un contact principal existe |
| Site web, LinkedIn, Instagram | URL | Validation du format |
| Spécialités | Liens multiples | Vers Catégories de produits |
| Notes internes | Texte long | Accès restreint |

Le statut d'adhérent actif est calculé depuis l'adhésion en cours. « Ancien adhérent » est calculé depuis l'historique des adhésions ; ce n'est pas un statut commercial saisi manuellement.

### 4.2 Contacts

| Champ | Type | Règle |
|---|---|---|
| Société | Lien | Obligatoire |
| Prénom, nom | Texte | Obligatoires |
| Fonction | Liste | Dirigeant, qualité, formulation, commercial, administratif, autre |
| Email, téléphone | Champs typés | |
| Contact principal | Oui/Non | Au maximum un contact principal actif par société |
| Langue préférée | Référence | Hérite de la société si vide |
| Actif | Oui/Non | Les anciens contacts sont désactivés, pas supprimés |
| Préférences de communication | Liste | À définir selon les usages et obligations applicables |

### 4.3 Historique des statuts commerciaux

Chaque changement conserve la société, l'ancien statut, le nouveau statut, la date, l'auteur et un motif. Le motif est obligatoire pour « Sans suite » et pour une réouverture du dossier.

### 4.4 Adhésions

| Champ | Type | Règle |
|---|---|---|
| Société | Lien | Obligatoire |
| Période / année | Année | Une seule adhésion par société et période |
| Date de début et de fin | Dates | Pas de chevauchement non justifié |
| Montant dû | Montant + devise | |
| Statut | Liste | En attente, active, expirée, résiliée |
| Date de résiliation et motif | Date + texte | Obligatoires si résiliée |

Une adhésion devient active selon la règle décidée par l'association : validation administrative seule ou validation plus paiement. Cette règle doit être fixée avant développement.

### 4.5 Paiements

Un paiement représente un mouvement réel, pas une attente de paiement.

| Champ | Type | Règle |
|---|---|---|
| Adhésion | Lien | Obligatoire |
| Date | Date | Date d'encaissement ou de remboursement |
| Montant et devise | Montant | Montant signé ou type de mouvement séparé |
| Type | Liste | Encaissement, remboursement |
| Mode | Liste | Virement, chèque, carte, autre |
| Référence | Texte | Référence bancaire ou interne |

Le statut « non payé / partiel / payé / remboursé » est calculé à partir du montant dû et des mouvements enregistrés. La facturation et le rapprochement bancaire restent hors périmètre initial.

### 4.6 Catégories de produits

Référentiel administré : saponification à froid, émulsion, huile pure, baume, shampoing solide, déodorant, hydrolat, dentifrice, maquillage, etc. Une catégorie peut être désactivée sans effacer l'historique.

### 4.7 Produits

| Champ | Type | Règle |
|---|---|---|
| Société | Lien | Obligatoire |
| Nom commercial | Texte | Obligatoire |
| Marque | Texte | Obligatoire |
| Client final / marque blanche | Texte ou lien futur | Optionnel |
| Catégorie | Lien | Obligatoire |
| Version de formule courante | Lien | Doit appartenir à une formule de la même société |
| Statut commercial | Liste | Brouillon, commercialisé, suspendu, retiré |
| Notes | Texte long | |

L'éligibilité technique du produit est calculée depuis la version de formule courante, sa décision, les dérogations actives et les règles d'attestation. Elle n'est pas modifiable directement.

Plusieurs produits d'une même société peuvent utiliser la même version. Le changement de version est daté et audité.

### 4.8 Formules

| Champ | Type | Règle |
|---|---|---|
| Société | Lien | Propriétaire de la formule |
| Nom interne / code | Texte | Unique dans la société si possible |
| Description | Texte long | |
| Active | Oui/Non | Une formule retirée reste consultable |

### 4.9 Versions de formule

| Champ | Type | Règle |
|---|---|---|
| Formule | Lien | Obligatoire |
| Numéro de version | Entier | Unique dans la formule, attribué automatiquement |
| Statut | Liste | Brouillon, soumis, incomplet, en étude, en attente d'information, accepté, refusé, remplacé |
| Date de création | Date/heure | Automatique |
| Date de soumission | Date/heure | Fige les données techniques |
| Note de version | Texte | Résumé des changements |

Une version soumise ne peut plus être modifiée. Toute correction de composition crée une nouvelle version, sauf correction administrative explicitement autorisée et auditée.

### 4.10 Lignes de composition

| Champ | Type | Règle |
|---|---|---|
| Version de formule | Lien | Obligatoire |
| Ingrédient générique | Lien | Obligatoire dès la création de la ligne |
| Ingrédient du laboratoire | Lien optionnel | Filtré par société et ingrédient générique ; peut être en attente de validation pendant le brouillon |
| Pourcentage dans la formule | Décimal | Précision à valider avec le comité |
| Ordre / phase | Texte ou nombre | Optionnel selon les besoins scientifiques |
| QSP | Oui/Non | Autorisé en brouillon seulement si le comité le permet |
| Commentaire technique | Texte | |

L'adhérent construit donc sa formule sans attendre : il choisit l'ingrédient générique et le pourcentage, puis sélectionne un ingrédient de son laboratoire déjà approuvé. Si aucun ingrédient correspondant n'existe, il le crée depuis la ligne, renseigne son fournisseur et dépose les documents demandés. Cet ingrédient reste lié à la formule avec le statut « soumis » pendant la revue humaine.

La ligne est prête lorsque l'ingrédient du laboratoire sélectionné correspond au générique et possède soit une approbation en cours de validité, soit une dérogation active couvrant précisément cet usage. L'approbation existante est réutilisée automatiquement ; aucune nouvelle vérification humaine n'est demandée pour chaque formule.

Avant soumission de la formule, la somme des pourcentages doit être égale à 100 % selon une tolérance définie. Si QSP est autorisé, sa valeur finale doit être résolue avant la décision scientifique afin de conserver une composition vérifiable.

### 4.11 Référentiel des ingrédients génériques / INCI

| Champ | Type | Règle |
|---|---|---|
| Nom générique affiché | Texte | Ex. Huile de coco |
| Nom INCI principal | Texte | Ex. Cocos Nucifera Oil ; unique avec gestion des synonymes |
| Noms communs et synonymes | Texte ou table liée | Facilitent la recherche de l'adhérent |
| Famille | Référence | |
| Origine | Référence | Végétale, minérale, animale, synthèse, mixte, autre |
| Position du label | Liste | Autorisé, interdit, sous conditions, à statuer |
| Conditions / limites | Texte structuré | Référence à la règle applicable |
| Éligibilité bio | Liste | Oui, non, conditionnelle, inconnue |
| Statut | Liste | À valider, validé, archivé |

Ce référentiel sert de première liste dans le constructeur de formule. L'adhérent n'administre pas ce vocabulaire ; il recherche un nom courant ou INCI et peut suggérer un élément manquant. Toute décision doit préciser la version du cahier des charges utilisée. Les seuils et calculs automatiques ne seront activés qu'après validation de leurs règles par le comité.

### 4.12 Fournisseurs

Nom légal, pays, site web, coordonnées utiles et statut actif. L'organisme certificateur appartient au document ou au certificat concerné ; il n'est pas nécessairement une propriété permanente du fournisseur.

### 4.13 Ingrédients du laboratoire

Cette table représente les matières premières commerciales que le laboratoire achète réellement. Dans l'interface adhérent, le terme court « Mes ingrédients » est utilisé.

| Champ | Type | Règle |
|---|---|---|
| Société | Lien | Obligatoire |
| Ingrédient générique | Lien | Obligatoire ; permet le filtrage depuis la formule |
| Fournisseur | Lien | Obligatoire |
| Nom commercial | Texte | Obligatoire |
| Référence fournisseur | Texte | Obligatoire si disponible |
| Grade / qualité | Texte | Permet de distinguer bio, conventionnel ou autres grades |
| Statut courant | Calculé | Brouillon, soumis, en revue, informations demandées, approuvé, refusé, expiré |

La clé d'unicité recommandée est `société + fournisseur + référence fournisseur`. En l'absence de référence, le nom commercial et le grade participent au contrôle des doublons.

L'adhérent crée l'ingrédient, sélectionne ou suggère le fournisseur, dépose les pièces obligatoires puis soumet. Le statut courant est dérivé de la dernière revue humaine et de la validité des documents. Une modification du fournisseur, de la référence, du grade, de l'ingrédient générique ou d'un document essentiel déclenche une nouvelle revue. Les décisions précédentes restent historisées.

### 4.14 Composants INCI des ingrédients du laboratoire

| Champ | Type | Règle |
|---|---|---|
| Ingrédient du laboratoire | Lien | Obligatoire |
| INCI | Lien | Obligatoire |
| Concentration exacte, minimale et maximale | Décimaux | Selon les informations fournisseur disponibles |
| Ordre déclaré | Entier | Utilisé si la concentration exacte est inconnue |
| Sous 1 % | Oui/Non/Inconnu | Information fournie ou calculée |
| Source | Document | Fiche technique ou déclaration fournisseur |

Cette décomposition est facultative lorsque l'ingrédient correspond directement à un seul INCI ; elle devient utile pour les mélanges commerciaux. La liste INCI d'un produit peut être **préparée automatiquement** lorsque la décomposition et les concentrations sont suffisantes. Si les données sont incomplètes ou exprimées en plages, le système produit une proposition à valider par le comité, pas une « liste officielle » garantie automatiquement.

### 4.15 Revues d'ingrédient

Une revue correspond à une soumission de l'adhérent et à une décision humaine. Elle est effectuée une fois pour une version donnée des informations et documents de l'ingrédient.

| Champ | Type | Règle |
|---|---|---|
| Ingrédient du laboratoire | Lien | Obligatoire |
| Soumis par / date | Utilisateur + date | Automatique |
| Documents soumis | Liens | Photographie des pièces examinées |
| Évaluateur | Utilisateur | Membre autorisé de l'association ou du comité |
| Statut | Liste | Soumise, en revue, informations demandées, décidée |
| Décision | Liste | Approuvé, refusé |
| Date de décision | Date/heure | Obligatoire à la décision |
| Commentaire | Texte | Obligatoire pour une demande d'information ou un refus |

Le parcours évaluateur est volontairement court : ouvrir l'ingrédient, lire les documents, vérifier le fournisseur, puis cliquer sur **Approuver**, **Demander des informations**, **Refuser** ou **Créer une dérogation**. « Créer une dérogation » ouvre une décision séparée ; cela ne transforme pas l'ingrédient en ingrédient approuvé.

Après approbation, l'ingrédient est immédiatement disponible comme choix validé dans toutes les formules de la société associées au même ingrédient générique. Un email informe l'adhérent de la décision.

### 4.16 Évaluations de formule

Une évaluation est rattachée à une version de formule et à une version de la grille scientifique. Elle exploite les approbations existantes des ingrédients ; l'évaluateur ne relit pas leurs dossiers à chaque produit. Le système signale uniquement les ingrédients non approuvés, expirés, modifiés ou couverts par une dérogation.

| Champ | Type | Règle |
|---|---|---|
| Version de formule | Lien | Obligatoire |
| Version du cahier des charges / grille | Référence | Obligatoire |
| Date de réception | Date/heure | Dépôt initial |
| Date de dossier complet | Date/heure | Point de départ recommandé du délai |
| Date limite | Date/heure calculée | Selon la règle SLA validée |
| Évaluateur responsable | Utilisateur | |
| Statut | Liste | À contrôler, incomplet, en étude, en attente, décidé |
| Décision | Liste | Acceptée, refusée |
| Date et auteur de décision | Date + utilisateur | Obligatoires à la clôture |
| Motif / synthèse | Texte long | Obligatoire pour refus et dérogation |

Une formule utilisant une dérogation active peut être acceptée, mais la dérogation reste un enregistrement séparé et apparaît comme telle dans l'évaluation et l'attestation. La règle des 15 jours doit préciser : jours calendaires ou ouvrés, événement de départ, gestion des jours fériés et suspension lorsque des informations sont demandées au laboratoire.

### 4.17 Contrôles d'évaluation

Chaque ligne représente un critère de la grille applicable : résultat conforme, non conforme, non applicable ou information manquante ; commentaire ; preuve ; évaluateur ; date.

La grille scientifique exacte reste à fournir et à valider par le comité. Elle doit notamment définir les données et pièces indispensables par catégorie de produit. Sans cette validation, le CRM peut stocker un dossier mais ne peut pas garantir la conformité de son instruction.

### 4.18 Dérogations

| Champ | Type | Règle |
|---|---|---|
| Ingrédient concerné | Lien | Ingrédient du laboratoire à l'origine de l'exception |
| Portée | Liste | Tous les usages de cet ingrédient par ce laboratoire, version de formule, ligne de composition ou produit précis |
| Entité concernée | Lien conditionnel | Cohérent avec la portée |
| Motif et conditions | Texte long | Obligatoires |
| Accordée par / date | Utilisateur + date | Obligatoires |
| Date d'effet et d'échéance | Dates | Obligatoires sauf décision explicitement permanente |
| Statut | Calculé | À venir, active, expirée, levée |
| Date et motif de levée | Date + texte | Si levée avant échéance |

Une dérogation reste indépendante de l'approbation : l'ingrédient demeure refusé, non approuvé ou restreint, tandis que la dérogation autorise uniquement le périmètre indiqué. Une dérogation couvrant tous les usages de l'ingrédient pour un laboratoire évite de répéter la décision ; une dérogation limitée à une version, une ligne ou un produit ne s'étend pas aux autres usages.

Une ligne de formule est recevable si l'ingrédient est approuvé ou si une dérogation active couvre cet usage. Une dérogation expirée peut rendre le produit inéligible à une nouvelle attestation sans invalider rétroactivement une attestation déjà émise.

### 4.19 Attestations

| Champ | Type | Règle |
|---|---|---|
| Société et adhésion | Liens | L'adhésion couvre la période attestée |
| Numéro d'attestation | Texte | Unique |
| Année / période | Année ou dates | Obligatoire |
| Date d'émission | Date/heure | |
| Émise par | Utilisateur | |
| Statut | Liste | Brouillon, émise, remplacée, révoquée |
| Version du modèle | Texte | Assure la reproductibilité |
| Document généré | Lien Document | Fichier immuable |
| Remplace / remplacée par | Auto-lien | Historique des corrections |
| Motif de révocation | Texte | Obligatoire si révoquée |

Une attestation émise n'est jamais modifiée en place. Une correction crée une nouvelle attestation et marque la précédente comme remplacée.

### 4.20 Produits attestés

Cette table conserve la photographie exacte de chaque produit au moment de l'émission : nom commercial, marque, catégorie, version de formule, décision scientifique et dérogations actives. Elle ne dépend pas des futures modifications du produit.

### 4.21 Documents

| Champ | Type | Règle |
|---|---|---|
| Type | Référence | Dossier technique, fiche technique, certificat, attestation, kit marketing, échange, autre |
| Entité rattachée | Lien polymorphe contrôlé | Une entité principale obligatoire |
| Fichier | Stockage sécurisé | Nom d'origine et type MIME conservés |
| Version | Entier ou texte | |
| Émis/reçu le | Date | |
| Émetteur | Texte ou lien | |
| Valide du / au | Dates | Si applicable |
| Statut de contrôle | Liste | Reçu, à vérifier, validé, refusé, expiré |
| Vérifié par / date | Utilisateur + date | |
| Remplace le document | Auto-lien | Historique des versions |
| Empreinte du fichier | Hash | Détection des modifications et doublons |
| Niveau d'accès | Liste | Interne, comité, adhérent concerné |

Le fichier du dossier technique d'une version et les certificats des matières premières sont stockés ici, pas dupliqués dans leurs tables métier.

### 4.22 Activités et tâches

| Champ | Type | Règle |
|---|---|---|
| Société | Lien | Obligatoire |
| Contact, produit ou dossier concerné | Liens optionnels | Selon le contexte |
| Type | Référence | Appel, email, dossier type envoyé, relance, kit marketing envoyé, lien retour vérifié, site vérifié, page dérogations créée, participation au livret, réunion, note, autre |
| Nature | Liste | Événement réalisé ou tâche à faire |
| Date prévue | Date/heure | Pour une tâche |
| Date de réalisation | Date/heure | Pour un événement réalisé |
| Responsable | Utilisateur | Obligatoire pour une tâche |
| Statut | Liste | À faire, en cours, terminée, annulée |
| Campagne / contexte | Texte ou référence | Ex. Développement Espagne 2026 |
| Commentaire | Texte long | |

Les cases du brief initial deviennent des faits datés. Une vue peut toujours afficher « kit envoyé » ou « participation livret », mais cette information est calculée depuis la dernière activité correspondante. Les actions annuelles créent une nouvelle occurrence au lieu d'écraser l'année précédente.

---

## 5. Cycles de vie

### 5.1 Relation commerciale

`Demande d'information → Prospect qualifié → Dossier envoyé → Relance → Converti`

Un prospect peut passer à « Sans suite » avec date et motif. La conversion crée ou active une adhésion ; elle ne déplace pas la société.

### 5.2 Construction progressive d'une formule

`Produit créé → Formule créée → Ingrédients génériques saisis → Ingrédients du laboratoire sélectionnés ou créés → Formule prête`

La formule peut rester en brouillon avec des ingrédients du laboratoire encore absents ou en cours de revue. Elle devient prête lorsque les pourcentages sont cohérents et que chaque ligne possède un ingrédient approuvé ou une dérogation active applicable.

### 5.3 Validation humaine d'un ingrédient

`Brouillon → Soumis avec documents → En revue → Approuvé / Informations demandées / Refusé`

L'évaluateur lit les documents et vérifie le fournisseur une seule fois, puis prend sa décision. Après approbation, l'ingrédient est réutilisable sans nouvelle revue dans toutes les formules du laboratoire. Une modification essentielle ou l'expiration d'une pièce déclenche une nouvelle revue. Une dérogation est créée séparément si une exception doit être autorisée.

### 5.4 Soumission et évaluation de la formule

`Formule prête → Soumise → Contrôle de complétude → En étude → Décision`

Le contrôle réutilise les décisions existantes sur les ingrédients. Une demande d'information peut suspendre le délai selon la règle SLA. Une reformulation crée une nouvelle version et une nouvelle évaluation.

### 5.5 Produit et attestation

`Brouillon → Commercialisé → Éligible techniquement → Attesté pour une période → Renouvelé ou retiré`

« Éligible » et « attesté » sont des résultats calculés ; ils ne remplacent pas le statut commercial.

---

## 6. Vues opérationnelles minimales

1. Tous les contacts.
2. Pipeline des prospects par statut et prochaine action.
3. Annuaire interne des adhérents actifs.
4. Développement Espagne 2026 : pays Espagne ou langue espagnole.
5. Adhésions à renouveler.
6. Paiements non soldés ou partiels.
7. Formules brouillon avec ingrédients génériques restant à associer.
8. Ingrédients soumis en attente de revue humaine.
9. Ingrédients pour lesquels des informations ont été demandées.
10. Dossiers de formule incomplets.
11. Évaluations de formule en cours, triées par date limite.
12. Évaluations dont le délai est suspendu.
13. Certificats et documents arrivant à expiration.
14. Dérogations arrivant à échéance.
15. Produits utilisant une version remplacée ou non éligible.
16. Attestations à préparer, émises, remplacées ou révoquées.
17. Relances et tâches échues.

Chaque vue doit définir ses filtres, colonnes, tri, droits d'accès et action principale avant développement.

---

## 7. Automatisations prévues

- accusé de réception d'un dossier ;
- accusé de réception d'un nouvel ingrédient ;
- alerte à l'évaluateur lorsqu'un ingrédient est soumis ;
- notification d'approbation, demande d'information ou refus d'un ingrédient ;
- mise à jour automatique de l'état de préparation des formules utilisant cet ingrédient ;
- notification de dossier incomplet ;
- alerte interne avant dépassement du délai d'étude ;
- notification d'une décision scientifique ;
- alerte d'expiration de document ou de dérogation ;
- préparation de la relance d'adhésion N+1 ;
- notification lorsqu'une attestation est disponible.

Les messages utilisent la langue préférée du destinataire lorsqu'un modèle validé existe. Chaque envoi conserve son modèle, son destinataire, sa date, son résultat et un lien vers l'entité concernée. Les emails automatiques ne sont activés qu'après validation des destinataires, modèles et conditions de déclenchement.

---

## 8. Utilisateurs, sécurité et exploitation

### Rôles initiaux

- **Administrateur** : paramétrage, utilisateurs et accès complet ;
- **Gestionnaire association** : sociétés, adhésions, paiements, documents et attestations ;
- **Évaluateur scientifique** : revue humaine des ingrédients, évaluations de formule, contrôles et dérogations ;
- **Adhérent** : création de ses produits, formules et ingrédients, dépôt de ses documents, réponse aux demandes et suivi de ses décisions ; accès limité à sa société.

Les droits précis doivent distinguer lecture, création, modification, décision, émission et révocation.

### Exigences minimales

- authentification individuelle, sans compte partagé ;
- journal d'audit des changements sensibles ;
- isolation des données entre sociétés ;
- sauvegardes automatiques et test de restauration ;
- stockage privé des documents et liens temporaires de téléchargement ;
- politique de conservation et de suppression à valider ;
- export des données d'une société ;
- procédure de départ d'un utilisateur ou membre du comité ;
- validation juridique et organisationnelle des traitements de données personnelles.

---

## 9. Décisions à obtenir avant construction

| Décision | Responsable pressenti | Bloque |
|---|---|---|
| Grille scientifique et pièces obligatoires par catégorie | Comité scientifique | Évaluations et pré-contrôles |
| Calculs et seuils du label | Comité scientifique | Contrôles automatiques |
| Règle exacte des 15 jours | Michel + comité | Alertes et échéances |
| Pièces obligatoires et contrôles humains pour approuver un ingrédient | Comité scientifique | Revue des ingrédients |
| Durée de validité d'une approbation et événements déclenchant une nouvelle revue | Comité scientifique | Réutilisation des ingrédients |
| Portée et durée autorisées des dérogations séparées | Comité scientifique | Modèle des dérogations |
| Conditions d'activation d'une adhésion | Michel / Inès | Statut adhérent actif |
| Règles d'émission, remplacement et révocation d'une attestation | Michel + comité | Attestations |
| Utilisateurs et matrice de droits | Michel | Sécurité |
| Contenu minimal du tableau de bord adhérent | Michel / Inès | Expérience MVP |
| Données multilingues nécessaires | Michel | Internationalisation |
| Annuaire public automatisé ou non | Michel | Évolution site web |

La facturation reste hors plateforme pour la première version. Le paiement en ligne pourra être ajouté ultérieurement.

---

## 10. Critères d'acceptation du MVP

Le MVP est accepté lorsque :

1. une société et plusieurs contacts peuvent être créés sans doublons évidents ;
2. son parcours prospect puis adhérent est traçable sans déplacer ni dupliquer sa fiche ;
3. une adhésion annuelle et ses encaissements peuvent être suivis ;
4. un adhérent peut créer un produit et une formule en saisissant d'abord les ingrédients génériques et les pourcentages ;
5. il peut sélectionner un de ses ingrédients approuvés ou en créer un depuis la formule et déposer ses documents ;
6. l'ingrédient soumis apparaît dans la file de revue et un évaluateur peut approuver, demander des informations, refuser ou créer une dérogation séparée ;
7. après approbation, l'ingrédient devient immédiatement réutilisable dans toutes les formules compatibles du même laboratoire sans nouvelle revue ;
8. une formule peut avoir plusieurs versions et une version soumise est figée ;
9. un ingrédient du laboratoire peut comporter plusieurs composants INCI et plusieurs versions de documents ;
10. un produit ne peut utiliser qu'une version appartenant à la même société ;
11. une formule n'est prête que si chaque ligne possède un ingrédient approuvé ou une dérogation active couvrant cet usage ;
12. une évaluation conserve la grille, les contrôles, l'évaluateur, les preuves et la décision sans répéter les revues d'ingrédients approuvés ;
13. les échéances de 15 jours et leurs éventuelles suspensions sont visibles ;
14. une dérogation distincte possède une portée, une durée et un décideur ;
15. une attestation annuelle contient une photographie immuable des produits couverts ;
16. les accès respectent les rôles et l'isolation entre laboratoires ;
17. une sauvegarde peut être restaurée dans un environnement de test ;
18. les parcours critiques sont testés de bout en bout avec des données fictives validées par les utilisateurs.

---

## 11. Points nécessitant une validation experte

Le CRM structure et trace les décisions de l'association ; il ne définit pas à lui seul les exigences scientifiques ou réglementaires applicables aux produits cosmétiques. Le comité doit fournir ou valider :

- les pièces obligatoires par type de produit ;
- les critères du label et leurs versions ;
- les méthodes de calcul ;
- les règles INCI, notamment pour les mélanges et concentrations inconnues ;
- les motifs de refus et catégories de dérogations ;
- les responsabilités de validation et de signature.

Ces règles devront être traduites en scénarios de test avant l'automatisation.
