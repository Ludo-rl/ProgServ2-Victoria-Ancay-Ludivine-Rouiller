-- Central - Modèle Physique de Données (PostgreSQL)

CREATE TYPE role_acces AS ENUM ('lecture_seule');
CREATE TYPE statut_acces AS ENUM ('en_attente', 'actif');

CREATE TABLE eleve (
    id_eleve SERIAL PRIMARY KEY,
    identifiant VARCHAR(50) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE UNIQUE INDEX uq_eleve_identifiant ON eleve (LOWER(identifiant));
CREATE UNIQUE INDEX uq_eleve_email ON eleve (LOWER(email));

CREATE TABLE couleur (
    id_couleur SERIAL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    code_hex CHAR(7) NOT NULL UNIQUE
);

INSERT INTO couleur (nom, code_hex)
VALUES ('Vert sauge', '#9CAF88');

CREATE TABLE cours (
    id_cours SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    date_creation TIMESTAMP NOT NULL DEFAULT NOW(),
    id_eleve INT NOT NULL REFERENCES eleve(id_eleve) ON DELETE CASCADE,
    id_couleur INT NOT NULL REFERENCES couleur(id_couleur),
    UNIQUE (id_eleve, nom)
);

CREATE TABLE fichier (
    id_fichier SERIAL PRIMARY KEY,
    nom_original VARCHAR(255) NOT NULL,
    chemin_stockage VARCHAR(500) NOT NULL,
    type_mime VARCHAR(100),
    taille_octets BIGINT CHECK (taille_octets >= 0),
    date_ajout TIMESTAMP NOT NULL DEFAULT NOW(),
    id_cours INT NOT NULL REFERENCES cours(id_cours) ON DELETE CASCADE
);

CREATE TABLE resume (
    id_resume SERIAL PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    numero_chapitre SMALLINT CHECK (numero_chapitre > 0),
    contenu TEXT,
    date_ajout TIMESTAMP NOT NULL DEFAULT NOW(),
    id_cours INT NOT NULL REFERENCES cours(id_cours) ON DELETE CASCADE,
    id_fichier INT UNIQUE REFERENCES fichier(id_fichier) ON DELETE SET NULL
);

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
