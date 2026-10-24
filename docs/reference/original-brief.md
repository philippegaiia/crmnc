**

Organisation NC

  

Voila le prompt dont je voulais me servir pour créer le CRM de la Nouvelle Cosmétique sur Airtable

Ceci dit et par expérience l’IA de Airtable à tendance à bâcler le travail. Je pensais donc faire un travail de préparation en amont avec Claude Cowork pour continuer à mouliner toutes les procédures et peaufiner le cahier des charges. Préparer la connexion des tables entre elles.

  

Ce cahier des charges ne me paraît pas assez complet pour les tables, les champs spécifiques et vues filtrées).

  

Texte 

Créer un nouveau CRM complet à partir de zéro pour une association de certification. 

La base doit contenir 2 tables liées entre elles, avec les vues et les champs suivants.

  

TABLE 1 : "Laboratoires et Contacts"

Champs à créer :

- "Nom de l'entreprise" (Texte court, champ principal)
    
- "Statut" (Sélection unique avec les options : Prospect, Envoi dossier type, Relance, Adhérent Actif, Arrêt)
    
- "Cause de l'arrêt" (Texte long)
    
- "Langue privilégiée" (Sélection unique avec les options : Français, Espagnol, Anglais)
    
- "Prénom Dirigeant" (Texte court)
    
- "Nom Dirigeant" (Texte court)
    
- "Email" (Email)
    
- "Téléphone" (Téléphone)
    
- "Adresse complète" (Texte long)
    
- "Pays" (Texte court)
    
- "Spécialité technique" (Sélection multiple)
    
- "Marques utilisées" (Texte court)
    
- "Site Web" (URL)
    
- "LinkedIn" (URL)
    
- "Instagram" (URL)
    
- "Kit marketing avec logo envoyé" (Case à cocher)
    
- "Lien retour vérifié" (Case à cocher)
    
- "Participation livret" (Case à cocher)
    
- "Statut paiement" (Sélection unique avec les options : En attente, Payé)
    

Vues à créer pour la Table 1 :

- Vue "Tous les contacts" (Vue par défaut)
    
- Vue "Pipeline Prospects" (Filtrer où Statut est Prospect, Envoi dossier type, ou Relance)
    
- Vue "Annuaire Adhérents Actifs" (Filtrer où Statut est Adhérent Actif)
    
- Vue "Développement Espagne 2026" (Filtrer où Langue privilégiée est Espagnol)
    

TABLE 2 : "Dossiers Techniques"

Champs à créer :

- "ID Dossier" (Numérotation automatique, champ principal)
    
- "Laboratoire" (Champ lié à la table "Laboratoires et Contacts")
    
- "Fichier Dossier Technique" (Pièce jointe)
    
- "Date de réception" (Date)
    
- "Statut de l'évaluation" (Sélection unique avec les options : En cours d'étude, Accepté, Refusé, Dérogation accordée)
    
- "Détail de la dérogation" (Texte long)
    
- "Attestation annuelle" (Pièce jointe)
    
- "Date de relance N+1" (Date)
    

Vues à créer pour la Table 2 :

- Vue "Tous les dossiers" (Vue par défaut)
    
- Vue "Dossiers en cours d'étude" (Filtrer où Statut de l'évaluation est En cours d'étude)
    
- Vue "Relances à prévoir" (Trier par Date de relance N+1)
    

### Après la génération :

  

Mise en place d’une organisation administrative pour faire face au passage de 50 à 100 membres

  

Objectif CRM opérationnel 23 août. La partie internationale pourra être décalée 

  

Voici un rétroplanning étalé  :

  

### Phase 1 : Besoins & Esthétique (Fin Juillet)

- Semaine 1 (20 au 26 juillet) : L'audit "Cap des 100"
    

- L'objectif : Réflexion avec Claude Cowork. Travail sur le cahier des charges. Lister sur un document simple toutes les informations dont nous avons impérativement besoin pour gérer un adhérent (du premier contact jusqu'à la validation technique de sa formule). Définir ce que fera le CRM.
    

- Semaine 2 (27 juillet au 2 août) : Le grand ménage graphique
    

- L'objectif : Lister les documents actuels vieillissants. Créer sur Canva une charte graphique épurée et scientifique, et on commence à rhabiller les documents clés (comme le dossier technique ou le formulaire d'adhésion).
    

### Phase 2 : Construction du CRM (Août)

- Semaine 3 (3 au 9 août) : Fondations sur Airtable
    

- L'objectif : Ouvrir Airtable et créer les tables brutes (sans se soucier des liens pour l'instant) : une table "Laboratoires/Marques", une table "Contacts", et une table "Suivi des Formules". Saisie pure.
    

- Semaine 4 (10 au 16 août) : Intelligence de la base (Formation)
    

- L'objectif : Semaine technique. Prendre le temps. Connecter les tables entre elles (apprendre à lier la fiche d'un laboratoire directement à ses dossiers de saponification ou ses cosmétiques validés).
    

- Semaine 5 (17 au 23 août) : Intégration et premiers tests
    

- L'objectif : Injecter nos nouveaux documents graphiques (réalisés en S2) dans la base, on fait quelques fausses saisies pour vérifier que tout est fluide, et on fige la structure. L'outil est prêt à être utilisé.
    

### Ouverture Internationale (Août)

- Semaine 6 (24 août au 30 août) : Préparation des textes
    

- L'objectif : Rassembler la philosophie du label, le cahier des charges et la présentation du Conservatoire pour préparer les traductions en anglais, italien et espagnol.
    

- Semaine 7 (31 août au 6 septembre) : Déploiement Multisite
    

- L'objectif : Intégration des trois nouvelles langues sur l'architecture de votre site web pour être prêt à accueillir les contacts étrangers.
    

### Phase 4 : La Sécurité (Début Septembre)

- Semaine 8 (7 au 13 septembre) : La semaine tampon
    

- L'objectif : Une semaine de marge pure pour absorber tout retard éventuel accumulé sur Airtable, la création visuelle ou le site web. 
    

- Semaine 9 (13 au 20 septembre) : Revue finale
    

- L'objectif : Dernier tour de piste pour s'assurer que tout fonctionne avant de reprendre les échanges avec les grands distributeurs à la rentrée.
    

  
  
  

@ Inès : ce sera à vous de voir si vous souhaitez vous former sur Airtable qui est un outil de base données qui me plait. Pas de problème si  je dois le suivre seul.

  

Pour la traduction des sites, on commencera avec l'espagnol où mes premiers contacts sont intéressants

  

Je compte orienter les posts Linkedin de juillet en revenant sur nos douze piliers et sur la préparation de la nouvelle charte et du nouveau cahier des charges ainsi que du livret

  

Vous ne m’avez pas répondu pour savoir si vous voulez que je m’occupe d’instagram

  

- Identité & Contact (Entreprise, Adresse avec Pays francophones, Nom du dirigeant, Prénom, téléphone, email1, Dirigeant 2, email2 site web, Linkedin o/n, Instagram o/n, autre1 o/n, autre2 o/n,)
    
- Profil technique (Spécialité : saponification à froid, émulsions, etc… Marques utilisées)
    
- Suivi commercial (Statut du dossier de prospection). Pour le suivi commercial, je pensais à faire deux tables, une prospects et une adhérents
    
- Suivi de paiement (lien banque ?)
    
- Relation commerciale (envoi du kit marketing avec logo, lien internet retour vers notre site, page complémentaire sur notre site, page pour ses dérogations, participation au livret, vérification du site, )
    
- Développement international (Langue privilégiée). Espagnol pour 2026 (à mettre sans une table par pays)
    
- Dossier de validation technique joint, envoi, réception 15 jours pour étude, acceptation, envoi attestation, date de relance pour l’adhésion année n+1, peut-être une table propre aux dossiers techniques compte tenu des pièces demandées voir DT)
    
- Attestation annuelle avec liste des produits
    
- Dérogation accordée
    
- Arrêt (date, cause,
    
- Dans la table prospect (origine, nom, envoi dossier type, relance, adhésion et passage table adhérents, etc…
    
- Table pour les réseaux ?
    
- Dans la table espagne (kit logo communication, nom, traduction site
    

Pour concevoir la table dédiée à la validation scientifique des formules, quelles sont les pièces ou données techniques indispensables que le comité doit impérativement tracer et valider pour chaque produit soumis par un artisan ?

**