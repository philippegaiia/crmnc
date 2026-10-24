# CRM Nouvelle Cosmétique

Système de gestion de la relation entre l'association Nouvelle Cosmétique et les laboratoires adhérents : suivi commercial, adhésions, produits et formules, validation humaine des ingrédients, évaluation scientifique et attestations annuelles.

La langue du domaine est le français ; les identifiants de code sont en anglais (ligne `_Code_:` de chaque terme).

## Acteurs et accès

**Société**:
Laboratoire cosmétique suivi par l'association : prospect, adhérent ou ancien adhérent selon son statut commercial et son historique d'adhésions.
_Avoid_: Client, compte, entreprise
_Code_: `Company`

**Adhérent**:
Société titulaire d'une adhésion active ; désigne aussi l'utilisateur du portail rattaché à cette société.
_Avoid_: Membre
_Code_: `UserRole::Adherent`, `User->company`

**Gestionnaire association**:
Membre de l'équipe de l'association en charge des sociétés, des adhésions et des relances.
_Code_: `UserRole::Gestionnaire`

**Évaluateur scientifique**:
Membre du comité scientifique habilité à instruire les revues d'ingrédients et les évaluations de formule.
_Avoid_: Expert, comité (l'instance, pas la personne)
_Code_: `UserRole::Evaluateur`

**Contact principal**:
Contact de référence d'une société ; au plus un contact principal actif par société.
_Avoid_: Référent, contact de facturation
_Code_: `Contact`

## Relation commerciale

**Statut commercial**:
Position de la société dans le pipeline de l'association : demande d'information, prospect qualifié, dossier envoyé, relance, converti ou sans suite.
_Avoid_: Étape de vente, statut d'adhésion
_Code_: `CompanyCommercialStatus`

**Activité**:
Échange, envoi ou vérification réalisé et daté sur une société (appel, email, kit marketing envoyé, lien retour vérifié…).
_Avoid_: Événement (ambigü), action
_Code_: à venir (modèle non encore implémenté)

**Tâche**:
Action à réaliser sur une société, avec date prévue et responsable.
_Avoid_: To-do, relance (la relance est un type d'activité)
_Code_: à venir (modèle non encore implémenté)

## Adhésions et paiements

**Adhésion**:
Engagement annuel d'une société auprès de l'association, une ligne par société et période, avec montant dû et statut propre.
_Avoid_: Cotisation, abonnement
_Code_: à venir (modèle non encore implémenté)

**Paiement**:
Mouvement réellement encaissé ou remboursé, rattaché à une adhésion ; le statut payé, partiel ou non payé est calculé.
_Avoid_: Facture, règlement attendu
_Code_: à venir (modèle non encore implémenté)

## Produits et formules

**Produit**:
Identité commerciale attestable d'une société : nom commercial, marque, catégorie et version de formule courante.
_Avoid_: Article, référence
_Code_: `Product`

**Marque blanche**:
Produit fabriqué pour le compte d'un client final distinct du laboratoire.
_Avoid_: Sous-traitance, private label
_Code_: `Product` (client final)

**Formule**:
Composition technique d'un produit, propriété de la société, évoluant par versions successives.
_Avoid_: Recette
_Code_: `Formula`

**Version de formule**:
État successif d'une formule, figé à la soumission ; toute correction ultérieure crée une nouvelle version.
_Avoid_: Brouillon (statut, pas une version), version publiée
_Code_: `FormulaVersion`

**Ligne de composition**:
Composant d'une version de formule : ingrédient générique obligatoire, ingrédient du laboratoire optionnel, pourcentage.
_Code_: `FormulaLine`

**QSP**:
Quantité suffisante pour 100 % : le complément de pourcentage de la formule, résolu avant la décision scientifique.
_Avoid_: QSP 100 (redondant)
_Code_: `FormulaLine->is_qsp`

**Catégorie de produits**:
Référentiel géré par l'association (saponification à froid, émulsion, huile pure, baume, shampoing solide, déodorant…).
_Code_: `ProductCategory`

## Ingrédients

**Ingrédient générique**:
Entrée du référentiel global des ingrédients et de leurs noms INCI, utilisée dès la création d'une formule.
_Avoid_: Matière première, INCI (le nom, pas l'entrée du référentiel)
_Code_: `IngredientRef`

**Nom INCI**:
Nom international normalisé de l'ingrédient, unique dans le référentiel (ex. Cocos Nucifera Oil).
_Avoid_: Nom commercial
_Code_: `IngredientRef->normalized_inci`

**Famille d'ingrédients**:
Regroupement du référentiel générique (végétale, minérale, animale, synthèse, mixte…) utilisé pour la recherche et le filtrage.
_Code_: `IngredientCategory`

**Ingrédient du laboratoire**:
Matière première commerciale réellement achetée par une société, avec fournisseur, référence et grade ; affichée « Mes ingrédients » dans le portail.
_Avoid_: Matière première, ingrédient (tout court)
_Code_: `LabIngredient`

**Fournisseur**:
Référentiel global des fournisseurs de matières premières.
_Code_: `Supplier`

**Composant INCI**:
Décomposition facultative d'un ingrédient du laboratoire en plusieurs noms INCI avec concentrations, utile pour les mélanges commerciaux.
_Code_: à venir (modèle non encore implémenté)

## Revue et validation des ingrédients

**Revue d'ingrédient**:
Soumission de l'adhérent et décision humaine (approuver, demander des informations, refuser, créer une dérogation) sur les documents d'un ingrédient du laboratoire.
_Avoid_: Validation (automatique), contrôle
_Code_: `IngredientReview`

**Approbation**:
Décision « approuvé » d'une revue, réutilisable pour le laboratoire jusqu'à expiration ou modification essentielle de l'ingrédient.
_Avoid_: Validation définitive, agrément
_Code_: `IngredientReviewDecision::Approved`, `LabIngredientStatusService`

**Statut de validation**:
État calculé d'un ingrédient du laboratoire à partir de sa dernière revue, de l'empreinte des champs essentiels et de la date de validité.
_Code_: `LabIngredientStatus`

**Demande d'information**:
Décision de revue suspendant la validation jusqu'à correction et nouvelle soumission, sans perte de l'historique.
_Code_: `IngredientReviewStatus::InformationRequested`

**Dérogation**:
Décision séparée, limitée à un périmètre (tous les usages du laboratoire, une version, une ligne ou un produit) et à une échéance, qui autorise un usage précis d'un ingrédient non approuvé.
_Avoid_: Exception, approbation partielle
_Code_: à venir (modèle non encore implémenté)

## Évaluation scientifique

**Évaluation de formule**:
Instruction scientifique d'une version de formule contre une version de la grille ; elle exploite les approbations existantes sans relire les dossiers d'ingrédients.
_Avoid_: Contrôle qualité, audit
_Code_: à venir (modèle non encore implémenté)

**Contrôle d'évaluation**:
Réponse à un critère de la grille scientifique : conforme, non conforme, non applicable ou information manquante.
_Avoid_: Point de contrôle (ambigü)
_Code_: à venir (modèle non encore implémenté)

**Grille scientifique**:
Version du cahier des charges définissant les critères et pièces exigés par catégorie de produit.
_Avoid_: Checklist, référentiel de validation
_Code_: à venir (modèle non encore implémenté)

**Délai d'étude**:
Règle des 15 jours encadrant l'évaluation, suspendue pendant une demande d'information et reprise avec le délai restant.
_Avoid_: SLA (terme technique)
_Code_: à venir (règle non encore implémentée)

## Attestations et documents

**Attestation**:
Document annuel émis pour une société et une période, immuable après émission ; une correction crée une attestation de remplacement.
_Avoid_: Certificat, justificatif
_Code_: à venir (modèle non encore implémenté)

**Produit attesté**:
Photographie immuable d'un produit et de sa version au moment de l'émission, indépendante des modifications ultérieures.
_Code_: à venir (modèle non encore implémenté)

**Éligibilité technique**:
État calculé d'un produit à partir des approbations en cours de validité, des dérogations actives et de la décision de sa version ; non modifiable directement.
_Avoid_: Conformité (jugement, pas calcul)
_Code_: `ProductReadinessService`

**Document**:
Pièce reçue ou émise, versionnée et rattachée à une entité métier, avec statut de contrôle, empreinte et niveau d'accès.
_Avoid_: Fichier joint
_Code_: `Document`

**Statut de contrôle**:
Reçu, à vérifier, validé, refusé ou expiré.
_Code_: `DocumentControlStatus`

**Niveau d'accès**:
Interne, comité ou adhérent concerné ; détermine qui peut consulter un document.
_Avoid_: Visibilité (ambigü)
_Code_: `DocumentAccessLevel`
