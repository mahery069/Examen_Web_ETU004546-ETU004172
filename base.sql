
-- ---------------------------------------------------------------------
-- Suppression des tables existantes, dans l'ordre inverse des dépendances
-- (tables enfants avant leurs tables parentes). Nécessaire pour permettre
-- de réinitialiser une base déjà peuplée sans violer les contraintes de
-- clé étrangère (PRAGMA foreign_keys = ON dans InitDatabase.php).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS operations;
DROP TABLE IF EXISTS comptes;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS baremes_frais;
DROP TABLE IF EXISTS types_operation;
DROP TABLE IF EXISTS prefixes_operateur;
DROP TABLE IF EXISTS operateurs;

-- ---------------------------------------------------------------------
-- Table : operateurs
-- Comptes de connexion pour le back-office (côté opérateur)
-- ---------------------------------------------------------------------
CREATE TABLE operateurs (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    login          VARCHAR(50) NOT NULL UNIQUE,
    mot_de_passe   VARCHAR(255) NOT NULL,
    date_creation  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE prefixes_operateur (
    id                     INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe                VARCHAR(3) NOT NULL UNIQUE,
    libelle                VARCHAR(50),
    is_internal            BOOLEAN DEFAULT TRUE NOT NULL,
    -- % de commission inter-opérateur appliqué (en plus des frais habituels
    -- du barème) quand un transfert sort vers ce préfixe externe.
    -- Sans objet (0) pour les préfixes internes.
    commission_pourcentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    date_creation          DATETIME DEFAULT CURRENT_TIMESTAMP,
    CHECK (commission_pourcentage >= 0 AND commission_pourcentage <= 100)
);

-- ---------------------------------------------------------------------
-- Table : types_operation
-- Types d'opérations possibles : depot, retrait, transfert
-- ---------------------------------------------------------------------
CREATE TABLE types_operation (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    code    VARCHAR(20) NOT NULL UNIQUE,   -- 'depot', 'retrait', 'transfert'
    libelle VARCHAR(50) NOT NULL
);

-- ---------------------------------------------------------------------
-- Table : baremes_frais
-- Frais appliqués par tranche de montant, pour chaque type d'opération
-- ---------------------------------------------------------------------
CREATE TABLE baremes_frais (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id  INTEGER NOT NULL,
    montant_min        DECIMAL(12,2) NOT NULL,
    montant_max        DECIMAL(12,2) NOT NULL,
    frais              DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (type_operation_id) REFERENCES types_operation(id),
    CHECK (montant_max >= montant_min)
);

-- ---------------------------------------------------------------------
-- Table : clients
-- Un client est identifié par son numéro de téléphone
-- (login automatique, pas d'inscription préalable)
-- ---------------------------------------------------------------------
CREATE TABLE clients (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_telephone    VARCHAR(15) NOT NULL UNIQUE,
    prefixe_id          INTEGER NOT NULL,
    date_creation       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prefixe_id) REFERENCES prefixes_operateur(id)
);

-- ---------------------------------------------------------------------
-- Table : comptes
-- Solde associé à chaque client (1 client = 1 compte pour la V1)
--
-- V2 (ETU4172) : ajout de `credit_frais_retrait`, un crédit provénant
-- de transferts reçus avec l'option "frais de retrait inclus". Ce
-- crédit vient réduire (ou annuler) les frais du/des prochains retraits
-- de ce compte, à hauteur du montant prépayé par l'expéditeur.
-- ---------------------------------------------------------------------
CREATE TABLE comptes (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id             INTEGER NOT NULL UNIQUE,
    solde                 DECIMAL(12,2) NOT NULL DEFAULT 0,
    credit_frais_retrait  DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

-- ---------------------------------------------------------------------
-- Table : operations
-- Historique des opérations (dépôt, retrait, transfert)
-- Pour un transfert : compte_id = expéditeur, compte_destinataire_id = destinataire
-- ---------------------------------------------------------------------
CREATE TABLE operations (
    id                     INTEGER PRIMARY KEY AUTOINCREMENT,
    compte_id              INTEGER NOT NULL,
    compte_destinataire_id INTEGER,
    type_operation_id      INTEGER NOT NULL,
    montant                DECIMAL(12,2) NOT NULL,
    frais                  DECIMAL(12,2) NOT NULL DEFAULT 0,
    -- Commission inter-opérateur prélevée en plus de `frais`, uniquement
    -- quand un transfert sort vers un numéro d'un autre opérateur
    -- (montant × prefixes_operateur.commission_pourcentage / 100).
    commission              DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_operation         DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compte_id) REFERENCES comptes(id),
    FOREIGN KEY (compte_destinataire_id) REFERENCES comptes(id),
    FOREIGN KEY (type_operation_id) REFERENCES types_operation(id)
);

CREATE TABLE promotions_frais(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL,
    pourcentage_remise DECIMAL(12,2) NOT NULL DEFAULT 0,
    type_operation_id  INTEGER NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT 1,
    date_debut DATETIME,
    date_fin DATETIME,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_operation_id) REFERENCES types_operation(id),
    CHECK (pourcentage_remise >0 AND pourcentage_remise <100)

)

-- =====================================================================
-- DONNÉES DE TEST (SEED)
-- =====================================================================

-- Préfixes valables (Telma - interne)
INSERT INTO prefixes_operateur (prefixe, libelle, is_internal, commission_pourcentage) VALUES
    ('034', 'Telma', 1, 0),
    ('038', 'Telma', 1, 0);

-- Préfixes des autres opérateurs (externes), avec leur % de commission
-- inter-opérateur appliqué sur les transferts sortants vers ces numéros.
INSERT INTO prefixes_operateur (prefixe, libelle, is_internal, commission_pourcentage) VALUES
    ('032', 'Orange', 0, 2.5),
    ('033', 'Airtel', 0, 1.5);

-- Types d'opérations
INSERT INTO types_operation (code, libelle) VALUES
    ('depot', 'Dépôt'),
    ('retrait', 'Retrait'),
    ('transfert', 'Transfert');

-- Barème de frais — exemple pour les RETRAITS (type_operation_id = 2)
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES
    (2, 100,      1000,     50),
    (2, 1001,     5000,     50),
    (2, 5001,     10000,    100),
    (2, 10001,    25000,    200),
    (2, 25001,    50000,    400),
    (2, 50001,    100000,   800),
    (2, 100001,   250000,   1500),
    (2, 250001,   500000,   1500),
    (2, 500001,   1000000,  2500),
    (2, 1000001,  2000000,  3000);

-- Barème de frais — exemple pour les TRANSFERTS (type_operation_id = 3)
-- (même grille que les retraits à titre d'exemple, modifiable via le back-office)
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES
    (3, 100,      1000,     50),
    (3, 1001,     5000,     50),
    (3, 5001,     10000,    100),
    (3, 10001,    25000,    200),
    (3, 25001,    50000,    400),
    (3, 50001,    100000,   800),
    (3, 100001,   250000,   1500),
    (3, 250001,   500000,   1500),
    (3, 500001,   1000000,  2500),
    (3, 1000001,  2000000,  3000);

-- NB : le dépôt (type_operation_id = 1) n'a pas de frais dans cet exemple.
-- Si besoin, ajouter des lignes dans baremes_frais avec type_operation_id = 1.

-- Clients fictifs de test
-- Rappel des prefixe_id : 1=034 (interne), 2=038 (interne), 3=032 (externe, Orange), 4=033 (externe, Airtel)
INSERT INTO clients (numero_telephone, prefixe_id) VALUES
    ('0341234567', 1),
    ('0349876543', 1),
    ('0381122334', 2),
    ('0321112233', 3);

-- Comptes associés avec un solde de départ
INSERT INTO comptes (client_id, solde) VALUES
    (1, 50000),
    (2, 120000),
    (3, 0),
    (4, 0);

-- Compte opérateur par défaut pour se connecter au back-office
-- login: admin / mot de passe: admin123 (hashé avec password_hash(), à changer en prod)
INSERT INTO operateurs (login, mot_de_passe) VALUES
    ('admin', '$2y$10$Q0I8W0su/rshhHyEOW6cIO7C/7XneAe/ZwPTE2qn3g2cwatu6r7US');