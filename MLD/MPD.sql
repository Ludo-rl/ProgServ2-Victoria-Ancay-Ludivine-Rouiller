-- ============================================================
-- Central - Modèle Physique de Données (PostgreSQL)
-- ============================================================
CREATE TYPE role_acces AS ENUM ('lecture_seule');
CREATE TYPE statut_acces AS ENUM ('en_attente', 'actif');
-- ------------------------------------------------------------
CREATE TABLE eleve (
 id_eleve SERIAL PRIMARY KEY,
 identifiant VARCHAR(50) NOT NULL,
 prenom VARCHAR(100) NOT NULL,
 email VARCHAR(255), -- facultatif
 mot_de_passe_hash VARCHAR(255) NOT NULL, -- bcrypt / argon2
 date_creation TIMESTAMP NOT NULL DEFAULT NOW()
);
-- "Cet identifiant est déjà utilisé" : unicité insensible à la casse
CREATE UNIQUE INDEX uq_eleve_identifiant ON eleve (LOWER(identifiant));
-- e-mail unique seulement s'il est renseigné
CREATE UNIQUE INDEX uq_eleve_email ON eleve (LOWER(email))
 WHERE email IS NOT NULL;
-- ------------------------------------------------------------
CREATE TABLE couleur (
 id_couleur SERIAL PRIMARY KEY,
 nom VARCHAR(50) NOT NULL UNIQUE,
 code_hex CHAR(7) NOT NULL UNIQUE
 CHECK (code_hex ~ '^#[0-9A-Fa-f]{6}$')
);
INSERT INTO couleur (nom, code_hex)
VALUES ('Vert sauge', '#9CAF88'); -- à compléter avec la palette

-- ------------------------------------------------------------
CREATE TABLE cours (
 id_cours SERIAL PRIMARY KEY,
 nom VARCHAR(100) NOT NULL,
 date_creation TIMESTAMP NOT NULL DEFAULT NOW(),
 id_eleve INT NOT NULL REFERENCES eleve(id_eleve) ON DELETE CASCADE,
 id_couleur INT NOT NULL REFERENCES couleur(id_couleur),
 UNIQUE (id_eleve, nom)
);
-- ------------------------------------------------------------
CREATE TABLE fichier (
 id_fichier SERIAL PRIMARY KEY,
 nom_original VARCHAR(255) NOT NULL,
 chemin_stockage VARCHAR(500) NOT NULL,
 type_mime VARCHAR(100),
 taille_octets BIGINT CHECK (taille_octets >= 0),
 date_ajout TIMESTAMP NOT NULL DEFAULT NOW(),
 id_cours INT NOT NULL REFERENCES cours(id_cours) ON DELETE CASCADE
);
-- ------------------------------------------------------------
CREATE TABLE resume (
 id_resume SERIAL PRIMARY KEY,
 titre VARCHAR(200) NOT NULL,
 numero_chapitre SMALLINT CHECK (numero_chapitre > 0),
 contenu TEXT,
 date_ajout TIMESTAMP NOT NULL DEFAULT NOW(),
 id_cours INT NOT NULL REFERENCES cours(id_cours) ON DELETE CASCADE,
 id_fichier INT UNIQUE REFERENCES fichier(id_fichier) ON DELETE SET NULL
);
-- ------------------------------------------------------------
CREATE TABLE acces_partage (
 id_acces SERIAL PRIMARY KEY,
 email_invite VARCHAR(255) NOT NULL,
 role role_acces NOT NULL DEFAULT 'lecture_seule',
 statut statut_acces NOT NULL DEFAULT 'en_attente',
 token VARCHAR(100) NOT NULL UNIQUE,
 date_invitation TIMESTAMP NOT NULL DEFAULT NOW(),
 date_acceptation TIMESTAMP,
 id_proprietaire INT NOT NULL REFERENCES eleve(id_eleve) ON DELETE CASCADE,
 id_eleve_invite INT REFERENCES eleve(id_eleve) ON DELETE CASCADE,
 UNIQUE (id_proprietaire, email_invite)
);
-- ------------------------------------------------------------
-- Index
CREATE INDEX idx_cours_eleve ON cours(id_eleve);
CREATE INDEX idx_fichier_cours ON fichier(id_cours);
CREATE INDEX idx_resume_cours ON resume(id_cours);
CREATE INDEX idx_acces_invite ON acces_partage(id_eleve_invite);
-- ------------------------------------------------------------
-- OPTIONNEL : "Mot de passe oublié ?"
-- CREATE TABLE reinitialisation_mdp (
-- id_reinit SERIAL PRIMARY KEY,
-- token VARCHAR(100) NOT NULL UNIQUE,
-- expiration TIMESTAMP NOT NULL,
-- utilise BOOLEAN NOT NULL DEFAULT FALSE,
-- id_eleve INT NOT NULL REFERENCES eleve(id_eleve) ON DELETE CASCADE
-- );
