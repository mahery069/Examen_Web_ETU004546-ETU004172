# Taches.md — Suivi des travaux par étudiant

### ETU4546 — Côté Opérateur (Back-office)

- **Création du schéma `base.sql`** (tables `prefixes_operateur`, `types_operation`, `baremes_frais`)
  - Définition des colonnes et types (ex: `prefixe VARCHAR(3)`, `montant_min`, `montant_max`, `frais`, `type_operation_id` en clé étrangère)
  - Contraintes (préfixe unique, tranches de montant cohérentes sans chevauchement)
  - Script `CREATE TABLE` testé sous SQLite

- **Interface de configuration des préfixes valables de l'opérateur** (033, 037, etc.)
  - Formulaire d'ajout d'un préfixe
  - Liste des préfixes existants avec possibilité de suppression/modification
  - Validation (format à 3 chiffres, pas de doublon)

- **Interface CRUD de gestion des barèmes de frais par tranche de montant**, pour chaque type d'opération
  - Formulaire de création d'une tranche (montant min, montant max, frais, type d'opération associé)
  - Liste des tranches existantes, modifiables et supprimables, groupées par type d'opération (dépôt / retrait / transfert)
  - Vérification qu'il n'y a pas de trou ou de chevauchement entre les tranches

- **Vue "Situation des gains"** (total des frais perçus sur retraits et transferts)
  - Calcul de la somme des frais prélevés, filtrable par type d'opération
  - Affichage sous forme de tableau récapitulatif (et éventuellement un total global)

- **Vue "Situation des comptes clients"** (liste des clients et de leurs soldes)
  - Tableau listant chaque client (numéro de téléphone) avec son solde actuel
  - Recherche simple par numéro

- **Données de test (seed) insérées dans `base.sql`**
  - Quelques préfixes d'exemple (033, 037)
  - Un barème de frais d'exemple pour chaque type d'opération
  - Quelques clients fictifs avec un solde de départ

### ETU4172 — Côté Client

- **Système de login automatique par numéro de téléphone** (création de compte à la volée si le préfixe est valide)
  - Formulaire de saisie du numéro de téléphone
  - Vérification que le préfixe du numéro existe dans `prefixes_operateur`
  - Si le client n'existe pas encore en base : création automatique du compte avec un solde initial à 0
  - Si le client existe déjà : connexion directe sur son compte existant
  - Gestion de la session client (rester connecté pendant la navigation)

- **Vue "Solde" du client connecté**
  - Affichage du solde actuel récupéré depuis la table `comptes`
  - Affichage du numéro de téléphone du client connecté

- **Formulaire et logique de dépôt** (crédit automatique du solde)
  - Formulaire de saisie du montant à déposer
  - Mise à jour du solde du client (+ montant)
  - Enregistrement de l'opération dans l'historique (table `operations`)

- **Formulaire et logique de retrait** (débit + application des frais selon le barème)
  - Formulaire de saisie du montant à retirer
  - Vérification que le solde est suffisant (montant + frais)
  - Recherche de la tranche de frais correspondante dans `baremes_frais` selon le montant et le type d'opération "retrait"
  - Débit du solde (montant + frais) et enregistrement de l'opération

- **Formulaire et logique de transfert** entre deux comptes clients (débit + crédit + frais)
  - Formulaire de saisie du numéro du destinataire et du montant
  - Vérification que le destinataire existe (ou gestion du cas où il n'existe pas)
  - Vérification du solde suffisant chez l'expéditeur (montant + frais de transfert)
  - Débit chez l'expéditeur, crédit chez le destinataire, enregistrement de l'opération pour les deux comptes

- **Vue "Historique des opérations" du client connecté**
  - Liste chronologique des opérations du client (dépôt, retrait, transfert envoyé/reçu)
  - Affichage du type d'opération, du montant, des frais éventuels et de la date


## Version 2 (tag `v2`) 

### ETU4546 — Côté Opérateur (Back-office)

- **Configuration des préfixes valables pour les autres opérateurs** (ex: 032, 031, etc.)
  - Ajout d'une colonne (ou d'un champ) permettant de distinguer un préfixe "opérateur interne" (le nôtre) d'un préfixe "autre opérateur" dans `prefixes_operateur`
  - Formulaire d'ajout de préfixe externe, avec le même contrôle de format que les préfixes internes
  - Liste séparée (ou filtrable) : préfixes internes vs préfixes des autres opérateurs

- **Configuration du % de commission pour les transferts vers les autres opérateurs**
  - Nouvelle table/notion de "commission inter-opérateur" (pourcentage appliqué en plus du barème de frais classique, uniquement quand le destinataire est sur un préfixe externe)
  - Interface pour définir/modifier ce pourcentage (éventuellement par opérateur externe si plusieurs sont configurés)
  - Logique de calcul : frais habituels du barème + (montant × % commission) quand le transfert sort vers un autre opérateur

- **Page "Situation des gains" : séparation opérateur / autres opérateurs**
  - Distinguer, dans le calcul des gains, les opérations effectuées entre clients internes de celles allant vers un numéro d'un autre opérateur
  - Affichage de deux blocs (ou deux colonnes) : gains internes vs gains liés aux transferts sortants vers d'autres opérateurs
  - Le gain "autres opérateurs" correspond à la commission perçue, distincte des frais internes classiques

- **Vue "Situation des montants à envoyer à chaque opérateur"**
  - Table de réconciliation : pour chaque transfert sortant vers un autre opérateur, le montant net dû à cet opérateur (montant transféré, hors commission gardée par notre opérateur)
  - Regroupement par opérateur externe (somme des montants dus à chacun)
  - Objectif : simuler le règlement inter-opérateurs (ce que notre opérateur doit reverser aux autres opérateurs pour les fonds reçus par leurs clients)

### ETU4172 — Côté Client

- **Option "inclure les frais de retrait lors de l'envoi"**
  - Case à cocher dans le formulaire de transfert : "le destinataire recevra le montant net après ses propres frais de retrait" (c'est-à-dire que l'expéditeur prend en charge par avance les frais que le destinataire paierait normalement en retirant)
  - Si l'option est cochée : calcul du montant total à débiter chez l'expéditeur = montant envoyé + frais de transfert + frais de retrait estimés (selon le barème retrait pour ce montant)
  - Si l'option n'est pas cochée : comportement actuel de la V1 (le destinataire paiera ses frais de retrait lui-même plus tard)
  - Affichage clair du récapitulatif avant validation (montant reçu net vs frais totaux payés par l'expéditeur)

- **Envoi multiple vers plusieurs numéros** (division du montant entre plusieurs destinataires)
  - Formulaire permettant d'ajouter plusieurs numéros de destinataires (liste dynamique)
  - Choix de la répartition : montant total divisé équitablement entre tous les numéros (ou saisie d'un montant par destinataire, selon ce que vous choisissez d'implémenter)
  - Vérification du solde suffisant chez l'expéditeur pour couvrir la somme de tous les envois + frais cumulés
  - Une opération enregistrée par destinataire dans `operations` (ou une opération "groupée" reliée à plusieurs lignes, selon le modèle choisi), pour que l'historique reste cohérent
  - Gestion des erreurs partielles (ex: un des numéros n'existe pas) — à clarifier en équipe : tout annuler ou envoyer aux numéros valides seulement

---
