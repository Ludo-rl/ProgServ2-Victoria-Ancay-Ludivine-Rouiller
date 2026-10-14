-- Active: 1791965242550@@127.0.0.1@3306@my_database
-- ============================================================
-- Central - Modèle Physique de Données (MariaDB)
-- ============================================================

-- Si besoin, remplace nom_de_ta_base par le nom de ta base (voir compose.yaml)
-- USE nom_de_ta_base;

CREATE TABLE eleve (
  id_eleve INT AUTO_INCREMENT PRIMARY KEY,
  identifiant VARCHAR(50) NOT NULL UNIQUE,   -- insensible à la casse par défaut
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NULL UNIQUE,            -- facultatif, unique si renseigné
  mot_de_passe_hash VARCHAR(255) NOT NULL,   -- bcrypt / argon2
  date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
CREATE TABLE couleur (
  id_couleur INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL UNIQUE,
  code_hex CHAR(7) NOT NULL UNIQUE,
  CHECK (code_hex REGEXP '^#[0-9A-Fa-f]{6}$')
);

INSERT INTO couleur (nom, code_hex)
VALUES ('Vert sauge', '#9CAF88'); -- à compléter avec la palette

-- ------------------------------------------------------------
CREATE TABLE cours (
  id_cours INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_eleve INT NOT NULL,
  id_couleur INT NOT NULL,
  UNIQUE (id_eleve, nom),
  FOREIGN KEY (id_eleve) REFERENCES eleve(id_eleve) ON DELETE CASCADE,
  FOREIGN KEY (id_couleur) REFERENCES couleur(id_couleur)
);

-- ------------------------------------------------------------
CREATE TABLE fichier (
  id_fichier INT AUTO_INCREMENT PRIMARY KEY,
  nom_original VARCHAR(255) NOT NULL,
  chemin_stockage VARCHAR(500) NOT NULL,
  type_mime VARCHAR(100),
  taille_octets BIGINT UNSIGNED,
  date_ajout TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_cours INT NOT NULL,
  FOREIGN KEY (id_cours) REFERENCES cours(id_cours) ON DELETE CASCADE
);

-- ------------------------------------------------------------
CREATE TABLE resume (
  id_resume INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(200) NOT NULL,
  numero_chapitre SMALLINT UNSIGNED,
  contenu TEXT,
  date_ajout TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_cours INT NOT NULL,
  id_fichier INT NULL UNIQUE,
  CHECK (numero_chapitre IS NULL OR numero_chapitre > 0),
  FOREIGN KEY (id_cours) REFERENCES cours(id_cours) ON DELETE CASCADE,
  FOREIGN KEY (id_fichier) REFERENCES fichier(id_fichier) ON DELETE SET NULL
);

-- ------------------------------------------------------------
CREATE TABLE acces_partage (
  id_acces INT AUTO_INCREMENT PRIMARY KEY,
  email_invite VARCHAR(255) NOT NULL,
  role_acces ENUM('lecture_seule') NOT NULL DEFAULT 'lecture_seule',
  statut ENUM('en_attente', 'actif') NOT NULL DEFAULT 'en_attente',
  token VARCHAR(100) NOT NULL UNIQUE,
  date_invitation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_acceptation TIMESTAMP NULL DEFAULT NULL,
  id_proprietaire INT NOT NULL,
  id_eleve_invite INT NULL,
  UNIQUE (id_proprietaire, email_invite),
  FOREIGN KEY (id_proprietaire) REFERENCES eleve(id_eleve) ON DELETE CASCADE,
  FOREIGN KEY (id_eleve_invite) REFERENCES eleve(id_eleve) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- OPTIONNEL : "Mot de passe oublié ?"
-- CREATE TABLE reinitialisation_mdp (
--   id_reinit INT AUTO_INCREMENT PRIMARY KEY,
--   token VARCHAR(100) NOT NULL UNIQUE,
--   expiration TIMESTAMP NOT NULL,
--   utilise BOOLEAN NOT NULL DEFAULT FALSE,
--   id_eleve INT NOT NULL,
--   FOREIGN KEY (id_eleve) REFERENCES eleve(id_eleve) ON DELETE CASCADE
-- );
