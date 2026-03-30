-- Création de la table avec le champ alt_images
CREATE TABLE IF NOT EXISTS pages (
    id SERIAL PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    meta_desc TEXT,
    alt_images VARCHAR(255)
);
-- Nettoyage pour insertion propre
TRUNCATE TABLE pages;

INSERT INTO pages (slug, title, content, meta_desc, alt_images) VALUES 
(
    'accueil', 
    'Conflit en Iran 2026 : Analyses et Direct Géopolitique', 
    '<p>Bienvenue sur <strong>IranNews</strong>, votre source d''analyse indépendante sur les tensions au Moyen-Orient. Ce dossier central regroupe les évolutions diplomatiques et militaires impactant la région en 2026.</p>
    <h2>Dossiers prioritaires</h2>
    <p>Explorez nos analyses détaillées pour comprendre les enjeux globaux de cette crise.</p>', 
    'Suivez l''évolution du conflit en Iran : analyses géopolitiques, impact sur le prix du pétrole et contexte historique complet par nos experts.',
    'Panorama de Téhéran avec infographie des zones de tension'
),
(
    'contexte-historique', 
    'Origines de la Crise en Iran : De 1979 à nos jours', 
    '<h2>L''héritage de la Révolution Islamique</h2>
    <p>Pour comprendre les tensions actuelles, il est impératif d''analyser la rupture diplomatique de 1979. Cette période marque le début d''une opposition frontale avec les puissances occidentales.</p>
    
    <h3>Chronologie des ruptures majeures</h3>
    <ul>
        <li><strong>1979 :</strong> Proclamation de la République Islamique.</li>
        <li><strong>2015 :</strong> Accord sur le nucléaire (JCPOA), un espoir de stabilisation éphémère.</li>
        <li><strong>2020 :</strong> Incident du détroit d''Ormuz, point de non-retour sécuritaire.</li>
    </ul>
    
    <h2>Le rôle des acteurs régionaux</h2>
    <p>La rivalité entre les puissances du Golfe exacerbe les tensions locales, transformant un différend bilatéral en crise régionale majeure.</p>', 
    'Découvrez les racines historiques du conflit iranien. Analyse détaillée de la révolution de 1979 aux tensions diplomatiques de 2026.',
    'Photo d''archive noir et blanc de la place Azadi lors de la révolution'
),
(
    'impact-geopolitique', 
    'Impact Mondial du Conflit : Énergie et Économie', 
    '<h2>Le choc pétrolier de 2026</h2>
    <p>Le contrôle du <strong>Détroit d''Ormuz</strong> reste le levier principal de la crise. Une fermeture, même partielle, provoque une volatilité immédiate des prix du baril de pétrole Brent.</p>
    
    <h3>Conséquences sur l''Union Européenne</h3>
    <p>L''Europe, en pleine transition énergétique, subit de plein fouet l''augmentation des coûts de transport maritime. Les routes commerciales sont déviées, allongeant les délais de livraison de 15 jours en moyenne.</p>
    
    <h2>Risques de cyber-guerre</h2>
    <p>Au-delà du terrain physique, le conflit se déplace sur le plan numérique, ciblant les infrastructures critiques (réseaux électriques, banques).</p>', 
    'Analyse des conséquences mondiales de la guerre en Iran : crise énergétique, prix du baril de pétrole et menaces cyber-attaques en 2026.',
    'Carte du monde montrant les routes maritimes pétrolières détournées'
),
(
    'cyber-securite-enjeux',
    'Cybersécurité : La Nouvelle Frontière du Conflit',
    '<h2>Guerre hybride et attaques ciblées</h2>
    <p>En 2026, la confrontation ne se limite plus aux frontières géographiques. Les attaques par déni de service (DDoS) et les ransomwares sont devenus des armes diplomatiques.</p>
    
    <h3>Protection des données sensibles</h3>
    <p>Les institutions internationales recommandent une vigilance accrue sur les protocoles de chiffrement pour contrer l''espionnage industriel lié au conflit.</p>',
    'Focus sur la cybersécurité et la guerre hybride en Iran. Comment le conflit numérique redéfinit les relations internationales en 2026.',
    'Visualisation de flux de données numériques sur fond de carte de l''Iran'
);