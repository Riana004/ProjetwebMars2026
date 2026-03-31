-- ═══════════════════════════════════════════════════════════════════════════
-- IranNews 2026 — Base de données PostgreSQL
-- Compatible avec index.php (champs : image, alt_images, created_at, updated_at)
-- ═══════════════════════════════════════════════════════════════════════════

-- Création de la base (à exécuter en tant que superuser si besoin)
-- CREATE DATABASE information_db;
-- \c information_db

-- ─── Création de la table ────────────────────────────────────────────────────
-- Remarques :
--   • "image"      : nom du fichier image (ex: "contexte.jpg") → utilisé par le PHP
--                    pour construire /img/<image> et l'OG image
--   • "alt_images" : texte alternatif SEO de l'image (cours p.9 — balise alt)
--   • "created_at" : date de publication → <time> sémantique + JSON-LD datePublished
--   • "updated_at" : date de mise à jour  → JSON-LD dateModified

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

-- Index sur le slug pour accélerer les requêtes SELECT WHERE slug = :slug
CREATE INDEX IF NOT EXISTS idx_pages_slug ON pages (slug);

-- Trigger PostgreSQL : met à jour "updated_at" automatiquement à chaque UPDATE
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_pages_updated_at ON pages;
CREATE TRIGGER trg_pages_updated_at
    BEFORE UPDATE ON pages
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- ─── Nettoyage pour insertion propre ─────────────────────────────────────────
TRUNCATE TABLE pages RESTART IDENTITY;

-- ─── Données initiales ───────────────────────────────────────────────────────
INSERT INTO pages (slug, title, content, meta_desc, image, alt_images, created_at) VALUES

(
    'accueil',
    'Conflit en Iran 2026 : Analyses et Direct Géopolitique',
    '<p>Bienvenue sur <strong>IranNews</strong>, votre source d''analyse indépendante
     sur les tensions au Moyen-Orient. Ce dossier central regroupe les évolutions
     diplomatiques et militaires impactant la région en 2026.</p>
     <h2>Dossiers prioritaires</h2>
     <p>Explorez nos analyses détaillées pour comprendre les enjeux globaux de
     cette crise.</p>',
    'Suivez l''évolution du conflit en Iran : analyses géopolitiques, impact sur
     le prix du pétrole et contexte historique complet par nos experts.',
    'accueil-hero.jpg',
    'Panorama de Téhéran avec infographie des zones de tension géopolitique en 2026',
    '2026-01-15 08:00:00'
),

(
    'contexte-historique',
    'Origines de la Crise en Iran : De 1979 à nos jours',
    '<h2>L''héritage de la Révolution Islamique</h2>
     <p>Pour comprendre les tensions actuelles, il est impératif d''analyser la
     rupture diplomatique de 1979. Cette période marque le début d''une opposition
     frontale avec les puissances occidentales.</p>
     <h3>Chronologie des ruptures majeures</h3>
     <ul>
         <li><strong>1979 :</strong> Proclamation de la République Islamique.</li>
         <li><strong>2015 :</strong> Accord sur le nucléaire (JCPOA), un espoir
             de stabilisation éphémère.</li>
         <li><strong>2020 :</strong> Incident du détroit d''Ormuz, point de
             non-retour sécuritaire.</li>
     </ul>
     <h2>Le rôle des acteurs régionaux</h2>
     <p>La rivalité entre les puissances du Golfe exacerbe les tensions locales,
     transformant un différend bilatéral en crise régionale majeure.</p>',
    'Découvrez les racines historiques du conflit iranien. Analyse détaillée de
     la révolution de 1979 aux tensions diplomatiques de 2026.',
    'contexte-historique.jpg',
    'Photo d''archive en noir et blanc de la place Azadi lors de la révolution iranienne de 1979',
    '2026-01-20 09:00:00'
),

(
    'impact-geopolitique',
    'Impact Mondial du Conflit : Énergie et Économie',
    '<h2>Le choc pétrolier de 2026</h2>
     <p>Le contrôle du <strong>Détroit d''Ormuz</strong> reste le levier principal
     de la crise. Une fermeture, même partielle, provoque une volatilité immédiate
     des prix du baril de pétrole Brent.</p>
     <h3>Conséquences sur l''Union Européenne</h3>
     <p>L''Europe, en pleine transition énergétique, subit de plein fouet
     l''augmentation des coûts de transport maritime. Les routes commerciales
     sont déviées, allongeant les délais de livraison de 15 jours en moyenne.</p>
     <h2>Risques de cyber-guerre</h2>
     <p>Au-delà du terrain physique, le conflit se déplace sur le plan numérique,
     ciblant les infrastructures critiques (réseaux électriques, banques).</p>',
    'Analyse des conséquences mondiales de la guerre en Iran : crise énergétique,
     prix du baril de pétrole et menaces cyber-attaques en 2026.',
    'impact-geopolitique.jpg',
    'Carte du monde montrant les routes maritimes pétrolières détournées suite au conflit iranien',
    '2026-02-01 10:00:00'
),

(
    'cyber-securite-enjeux',
    'Cybersécurité : La Nouvelle Frontière du Conflit',
    '<h2>Guerre hybride et attaques ciblées</h2>
     <p>En 2026, la confrontation ne se limite plus aux frontières géographiques.
     Les attaques par déni de service (DDoS) et les ransomwares sont devenus
     des armes diplomatiques.</p>
     <h3>Protection des données sensibles</h3>
     <p>Les institutions internationales recommandent une vigilance accrue sur
     les protocoles de chiffrement pour contrer l''espionnage industriel lié
     au conflit.</p>',
    'Focus sur la cybersécurité et la guerre hybride en Iran. Comment le conflit
     numérique redéfinit les relations internationales en 2026.',
    'cyber-securite.jpg',
    'Visualisation de flux de données numériques sur fond de carte de l''Iran symbolisant la cyberguerre',
    '2026-02-10 11:00:00'
);