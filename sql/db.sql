create database information_db;
create table pages (
    id serial primary key,
    slug varchar(255) not null unique,
    title varchar(255) not null,
    content text not null,
    meta_desc text
);

CREATE TABLE IF NOT EXISTS pages (
    id          SERIAL          PRIMARY KEY,
    slug        VARCHAR(255)    NOT NULL UNIQUE,
    title       VARCHAR(255)    NOT NULL,
    content     TEXT            NOT NULL,
    meta_desc   TEXT,
    image       VARCHAR(255),                               -- nom du fichier image
    alt_images  VARCHAR(255),                               -- texte alt de l'image
    created_at  TIMESTAMP       NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP       NOT NULL DEFAULT NOW()
);