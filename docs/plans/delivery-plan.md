# CRM Nouvelle Cosmétique — Plan de livraison

**Version 1 — juillet 2026**  
**Objectif proposé : MVP intégré adhérent + validation interne opérationnel le 23 août 2026**

Le brief initial visait un CRM opérationnel au 23 août. Cette date reste un objectif de pilotage, pas une garantie, tant que les décisions scientifiques et l'infrastructure ne sont pas validées.

---

## 1. Définition du MVP du 23 août

Le MVP comprend :

- sociétés, contacts et pipeline ;
- activités, tâches, relances et suivi des actions marketing ;
- adhésions annuelles et suivi manuel des paiements ;
- espace adhérent sécurisé ;
- création par l'adhérent de ses produits et formules à partir d'ingrédients génériques ;
- sélection d'un ingrédient approuvé ou création d'un nouvel ingrédient depuis la formule ;
- dépôt des documents par l'adhérent ;
- revue humaine unique de l'ingrédient avec approbation, demande d'information ou refus ;
- réutilisation immédiate des ingrédients approuvés dans les formules du même laboratoire ;
- évaluations et décisions sur une grille validée ;
- dérogations séparées avec portée, conditions et échéance ;
- attestations annuelles avec photographie des produits ;
- vues prioritaires, alertes internes et journal d'audit ;
- accès adhérent, administrateur, gestionnaire et évaluateur.

Le MVP n'inclut pas le paiement en ligne, l'annuaire public, les traductions complètes de l'interface ni le rapprochement bancaire. Les fonctions du portail sans lien direct avec la création et le suivi des produits, formules, ingrédients, documents et attestations peuvent être différées.

Le développement suit des **tranches verticales intégrées**. Chaque tranche relie l'espace adhérent, le panneau interne, les règles métier, les notifications et les tests. Il ne s'agit pas de construire toutes les tables d'abord puis de connecter les interfaces plus tard.

---

## 2. Rétroplanning

### Semaine 1 — 20 au 26 juillet : validation métier

Livrables :

- validation du modèle de données v2 ;
- grille scientifique et liste des pièces obligatoires ;
- règle des 15 jours ;
- règles d'adhésion, de dérogation et d'attestation ;
- pièces requises et règles de revue humaine d'un ingrédient ;
- liste des utilisateurs et droits ;
- sélection de 3 à 5 dossiers fictifs ou anonymisés représentatifs.

Critère de sortie : aucune règle bloquante du tableau « Décisions à obtenir » ne reste sans responsable ni date.

### Semaine 2 — 27 juillet au 2 août : premier parcours vertical

Le prototype Laravel + Filament étant déjà lancé, cette semaine sert à valider ses fondations avant d'étendre le périmètre. Le chantier graphique prévu dans le brief continue en parallèle. Pour le CRM :

- confirmer l'hébergement, la base, le stockage et les sauvegardes ;
- relever et documenter les versions réellement installées de Laravel, Filament, Livewire et PHP ;
- préparer les modèles d'attestation et de messages ;
- créer l'authentification, l'isolation par société et les rôles de base ;
- permettre à un adhérent de créer un produit et une formule avec un ingrédient générique ;
- permettre la création d'un ingrédient du laboratoire avec fournisseur et documents ;
- afficher cet ingrédient dans une file interne et permettre l'approbation humaine ;
- répercuter immédiatement l'approbation dans la formule ;
- valider les principaux écrans avec Michel et Inès.

Critère de sortie : un adhérent et un évaluateur exécutent ensemble le parcours minimal de bout en bout sur une même donnée isolée.

### Semaine 3 — 3 au 9 août : enrichissement du parcours intégré

- sociétés, contacts et historique commercial ;
- adhésions et paiements ;
- bibliothèque des ingrédients approuvés du laboratoire ;
- demande d'information, correction et nouvelle soumission ;
- refus avec commentaire ;
- ingrédient commercial comportant plusieurs composants INCI lorsque nécessaire ;
- calcul de préparation de la formule ;
- import d'un petit jeu de données réel contrôlé ;
- tests automatiques d'isolation, de statuts et de réutilisation.

Critère de sortie : l'adhérent peut construire une formule progressivement et réutiliser sans nouvelle revue un ingrédient déjà approuvé.

### Semaine 4 — 10 au 16 août : décision, dérogation et versionnement

- versions de formule et gel après soumission ;
- évaluations, contrôles, délais et suspensions ;
- création d'une dérogation séparée depuis la revue d'un ingrédient ;
- portée laboratoire, version, ligne ou produit ;
- expiration et retrait d'une dérogation ;
- notification de chaque décision à l'adhérent ;
- tests des règles avec les dossiers représentatifs.

Critère de sortie : un dossier standard et un dossier avec dérogation peuvent être instruits de bout en bout avec des décisions humaines auditables.

### Semaine 5 — 17 au 23 août : attestations et recette

- génération et remplacement d'attestations ;
- vues opérationnelles prioritaires ;
- alertes et modèles d'emails validés ;
- tests de bout en bout ;
- test de sauvegarde et restauration ;
- correction des anomalies bloquantes ;
- formation courte et procédure d'exploitation ;
- décision de mise en production.

Critère de sortie : les critères d'acceptation du cahier des charges sont validés et signés par les responsables désignés.

### Semaine tampon — 24 août au 13 septembre

- correction des défauts non bloquants ;
- nettoyage et import progressif des données existantes ;
- stabilisation des modèles de documents ;
- amélioration du portail adhérent à partir des retours de recette ;
- travaux internationaux et site web prévus dans le brief.

---

## 3. Priorités si le calendrier se resserre

Les fonctionnalités sont arbitrées dans cet ordre :

1. sécurité, sauvegardes et intégrité des données ;
2. isolation des sociétés et parcours adhérent ;
3. formule par ingrédient générique ;
4. création, documents et revue humaine unique des ingrédients ;
5. réutilisation des ingrédients approuvés et calcul de préparation ;
6. versions de formule, décisions et dérogations séparées ;
7. attestations immuables ;
8. vues opérationnelles et emails automatiques ;
9. confort d'interface et fonctionnalités secondaires.

L'espace adhérent minimal fait partie du cœur. Seuls l'annuaire public, les traductions complètes et les fonctions non nécessaires au parcours produit-formule-ingrédient peuvent être différés.

---

## 4. Gouvernance

| Rôle | Responsabilité |
|---|---|
| Sponsor métier — Michel | Arbitrage du périmètre, règles d'adhésion et validation finale |
| Référente opérationnelle — Inès à confirmer | Parcours quotidiens, vues, données et recette |
| Comité scientifique | Grille, pièces, décisions, dérogations et scénarios de test |
| Responsable technique | Architecture, développement, sécurité, sauvegardes et déploiement |

Chaque décision modifiant le modèle ou le périmètre est consignée avec sa date, son auteur, son motif et son impact sur le calendrier.

---

## 5. Stratégie de recette

La recette utilise au minimum les scénarios suivants :

1. prospect converti en adhérent avec deux contacts ;
2. kit marketing envoyé, lien retour vérifié et relance annuelle conservés comme événements datés ;
3. adhésion partiellement puis totalement payée ;
4. deux produits de marque blanche utilisant la même formule ;
5. formule créée avec ingrédients génériques avant sélection des ingrédients du laboratoire ;
6. création depuis la formule d'un ingrédient avec fournisseur et documents ;
7. approbation humaine rendant l'ingrédient immédiatement réutilisable dans une seconde formule ;
8. demande d'information puis nouvelle soumission sans perte de l'historique ;
9. refus d'un ingrédient sans autorisation implicite de ses usages ;
10. dérogation séparée limitée à un produit et dérogation couvrant tous les usages de l'ingrédient par le laboratoire ;
11. ingrédient commercial comportant plusieurs composants INCI ;
12. certificat expiré déclenchant une nouvelle revue selon la règle retenue ;
13. version refusée puis reformulée ;
14. demande d'information suspendant le délai d'étude ;
15. attestation remplacée sans modification du document historique ;
16. tentative d'accès d'un adhérent aux données d'un autre laboratoire ;
17. restauration d'une sauvegarde de la base et des documents.

Pour chaque scénario : données d'entrée, actions, résultat attendu, responsable de validation et preuve de recette.

---

## 6. Risques principaux

| Risque | Effet | Réponse |
|---|---|---|
| Grille scientifique non disponible | Évaluation impossible à automatiser | La rendre bloquante en S1 |
| Confusion matière première / INCI | Liste INCI et contrôles faux | Valider le prototype en S2 |
| Périmètre trop large | Date du 23 août compromise | Garder uniquement le parcours adhérent indispensable et différer l'international et les fonctions secondaires |
| Revue d'ingrédient répétée pour chaque formule | Temps du comité gaspillé | Réutiliser l'approbation jusqu'à expiration ou modification essentielle |
| Dérogation confondue avec approbation | Usage trop large d'un ingrédient non conforme | Conserver une entité séparée avec portée et échéance explicites |
| Documents ou emails non finalisés | Attestations et communications retardées | Utiliser des modèles provisoires explicitement versionnés |
| Absence de données de test réalistes | Défauts découverts trop tard | Préparer les cas représentatifs en S1 |
| Sauvegarde non testée | Risque de perte de données | Test de restauration obligatoire avant production |
| Droits mal définis | Exposition de données confidentielles | Matrice de droits et tests d'isolation |

---

## 7. Décision de mise en production

La mise en production est autorisée uniquement si :

- aucune anomalie critique ou majeure ne reste ouverte sur les parcours essentiels ;
- les responsables métier ont validé les scénarios de recette ;
- les comptes et droits ont été contrôlés ;
- les sauvegardes et la restauration ont été testées ;
- une procédure de support et de retour arrière existe ;
- les utilisateurs ont reçu une prise en main minimale.

Si ces conditions ne sont pas réunies le 23 août, le système reste en recette et la date de production est déplacée. La qualité de la traçabilité scientifique et la confidentialité priment sur la date cible.
