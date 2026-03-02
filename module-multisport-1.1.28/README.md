# Module MultiSport — gestion privée club / équipes / membres / rencontres

MultiSport est un module **privé** pour ZwiiCMS destiné à centraliser la gestion d’un club (asso / équipe / section) : **membres**, **staff**, **événements + bénévoles**, **calendrier des rencontres**, **convocations** et **résultats**.  
L’objectif : une interface claire, rapide, pensée pour un usage quotidien par un secrétariat ou un responsable d’équipe.

---

## Points clés

- **Vue d’ensemble** (icône maison) : informations essentielles du club + indicateurs rapides
- **Paramètres** : identité du club (nom, sigle…), sport (champ libre), TVA, IBAN…
- **Club** : coordonnées, infrastructures (terrains), organigramme, tarifs licences, réductions, sponsors
- **Équipes** : création rapide (équipe + catégorie) sur **une ligne**
- **Membres** : fiche joueur + parents 1/2, équipe + catégorie, cotisation, documents
- **Staff** : fiche staff (fonction + contacts + équipe + catégorie)
- **Événements** : **texte libre** + **bénévoles intégrés dans la même fiche** (tâches / planning)
- **Calendrier — rencontres** : création de rencontre, convocations, résultats, calendrier mensuel

---

## Pré-requis

- **ZwiiCMS** (version 14.x recommandée)
- **PHP 8.3** (le module est conçu pour cet environnement)
- Accès admin/éditeur à l’administration Zwii

---

## Installation

1. Dans l’admin Zwii → **Extensions / Modules → Installer / Importer**
2. Importer le ZIP du module MultiSport
3. Créer une page et lui associer le module **MultiSport**

> Conseil : lors d’une mise à jour, supprime le dossier `/module/multisport/` avant réimport si ton environnement conserve d’anciens fichiers.

---

## Navigation

Dans le menu du module :
- **🏠** (sans texte) → **Vue d’ensemble**
- **Paramètres**
- **Club**
- **Équipes**
- **Membres**
- **Staff**
- **Événements**
- **Calendrier — rencontres**

---

## Vue d’ensemble (🏠)

### Cadre 1 — Club
Affiche, en un coup d’œil :
- Logo du club
- TVA
- Adresse
- Téléphone / Email
- IBAN
- Accès direct aux **Paramètres**

### Cadre 2 — Membres
- Total membres
- Bouton **Nouvelle inscription** (accès direct à la création d’un membre)
- Accès rapide aux rencontres à venir (si renseignées)

### Cadre 3 — Événements
- Total événements
- Bouton vers la page **Événements**
- Liste des derniers événements

---

## Paramètres

Dans **Paramètres**, tu définis l’identité “administrative” du club :

- **Nom complet du club**
- **Sigle**
- **Fédération**
- **Sport** *(champ texte libre : football, hand, hockey, judo, futsal…)*  
- **Numéro de TVA**
- **IBAN**
- (Logo / bannière si activés dans la configuration)

> Ces données alimentent la Vue d’ensemble et les aperçus (ex. résultats, documents, etc.).

---

## Club

La page **Club** sert à structurer le club.

### Coordonnées & siège social
- Adresse
- Email / téléphone
- Latitude / longitude (si utilisées)

### Infrastructures
Ajout d’un terrain avec :
- Nom du terrain
- Adresse
- Coordonnées GPS
- Cases à cocher : **Buvette**, **Vestiaires**

### Organigramme
Ajout des responsables :
- Rôle (Président, Trésorier, Secrétaire…)
- Nom + prénom
- Photo
- Email / téléphone

### Tarifs licences & réductions
- Tarifs : Catégorie (U11, Senior…) + prix
- Réductions automatiques : ex. “2e enfant…”

### Partenaires / Sponsors
- Logo
- Nom
- Contact : téléphone + email

---

## Équipes

Création ultra rapide sur **une ligne** :
- **Équipe** (ex. Cadets)
- **Catégorie** (ex. U8)
- Bouton **Créer**
- Bouton **Rajouter 1 équipe**

Chaque équipe sert ensuite de référence pour :
- l’affectation des **membres**
- l’affectation du **staff**
- les filtres (catégories) côté rencontres / convocations

---

## Membres (joueurs)

### Fiche membre
- Photo
- Nom / prénom
- Date de naissance
- **Équipe + Catégorie** (2 champs distincts)
- **Cotisation** (oui/non + note)
- Parents (gestion privée) :
  - Parent 1 : nom / téléphone / email
  - Parent 2 : nom / téléphone / email
- Notes internes
- Documents / justificatifs (si activé)

> La gestion est pensée “privée” : parents 1/2 visibles côté admin, pas de publication publique par défaut.

---

## Staff

Fiche staff :
- Photo
- Nom / prénom
- Fonction
- Email / téléphone
- **Équipe + Catégorie** (2 champs distincts)

---

## Événements (texte libre) + bénévoles intégrés

### Événement
- **Événement (texte)** : annonce, tournoi, fête, réunion, festivité…

> Ici, on ne gère pas les matchs : ils sont dans **Calendrier — rencontres**.

### Bénévoles — tâches & planning (dans la même fiche)
Sur la fiche événement, tu ajoutes des lignes :
- Tâche
- Horaire
- Bénévoles nécessaires
- Affectations (texte libre)

Le planning s’affiche sous forme de tableau, avec suppression des lignes.

---

## Calendrier — rencontres

### Créer une rencontre (grand cadre)
Champs :
- Adversaire
- Domicile / Extérieur
- Catégorie
- Début + fin (optionnel)
- Adresse / lieu
- Rencontre (texte)
- Bouton **Enregistrer**

### Convocations (grand cadre)
- Sélection de la rencontre
- Joueurs convoqués filtrés par **catégorie**
- Statut à l’envoi : **Présent / Absent / En attente**
- Suivi des réponses (selon configuration)

### Résultats rencontres (grand cadre)
- **Aperçu** : `Nom du club - Adversaire`
- Sur **une ligne** :
  - Équipe (texte) + Score → Adversaire (texte) + Score adverse
- Notes

### Calendrier mensuel
- Vue mensuelle (vendor “animated-calendar”)
- Affichage des rencontres (points/labels sur les dates)

---

## Bonnes pratiques d’utilisation

- **Commence par Paramètres** : logo, identité, TVA, IBAN, sport
- Crée ensuite tes **Équipes + catégories**
- Ajoute les **Membres** (en renseignant équipe + catégorie) et le **Staff**
- Utilise :
  - **Événements** pour la vie du club + bénévoles
  - **Rencontres** pour les matchs + convocations + résultats

---

## Notes

- Module conçu pour un usage **administratif privé**.
- Stockage en données module (structure interne Zwii).  
- Le module privilégie une mise en page nette et stable : on évite les “surcouches” inutiles.

---

## Version

- MultiSport v1.1.28 (et suivantes)