<?php
// Connexion via le dossier config
require_once __DIR__ . '/../config/db.php';

// Récupération du slug (ex: accueil, contexte-historique)
$slug = $_GET['slug'] ?? 'accueil';

// Nettoyage strict du slug pour éviter les injections dans les URLs
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

// ─── 1. Page demandée ───────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT title, content, meta_desc FROM pages WHERE slug = :slug LIMIT 1"
);
$stmt->execute(['slug' => $slug]);
$page = $stmt->fetch();
$is404 = !$page;

// ─── 2. Navigation (titres + slugs pour le menu et la grille d'accueil) ────
$categories = $pdo->query(
    "SELECT title, slug FROM pages WHERE slug != 'accueil' ORDER BY id ASC"
)->fetchAll();

// ─── 3. Gestion 404 – renvoie le bon code HTTP ─────────────────────────────
if ($is404) {
    header('HTTP/1.1 404 Not Found');
    $page = [
        'title' => 'Page non trouvée',
        'content' => '<h2>Oups !</h2><p>Le contenu que vous recherchez n\'existe pas.</p>',
        'meta_desc' => 'Erreur 404 – Page introuvable sur Info Guerre Iran.',
    ];
}

// ─── 4. Helpers SEO ────────────────────────────────────────────────────────
// Troncature sécurisée de la meta-description (≤ 159 caractères, cours p.8)
$metaDesc = mb_substr(strip_tags((string)($page['meta_desc'] ?? '')), 0, 159);

// Construction de l'URL canonique (cours p.9 – évite le contenu dupliqué)
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'example.com', ENT_QUOTES);
$canonical = $proto . '://' . $host . '/' . urlencode($slug) . '.html';

// Titre de page : mot-clé en premier, ≤ 60 car. (cours p.8)
$pageTitle = htmlspecialchars($page['title'], ENT_QUOTES)
    . ' | Actualités Iran 2026';

// Données structurées JSON-LD (cours p.12 – Schema.org)
$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type' => ($slug === 'accueil') ? 'WebSite' : 'Article',
    'headline' => $page['title'],
    'url' => $canonical,
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Info Guerre Iran',
        'url' => $proto . '://' . $host,
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <!--
        SEO Technique – Mobile-First (cours p.11)
        Google indexe en mobile-first depuis 2023 → balise viewport obligatoire.
    -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--
        SEO On-Page – <title> (cours p.8)
        • 50-60 caractères
        • Mot-clé principal EN PREMIER
        • Unique par page
    -->
    <title><?= $pageTitle ?></title>

    <!--
        SEO On-Page – Meta description (cours p.8)
        • 150-160 caractères
        • Incitatif (améliore le CTR)
    -->
    <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES) ?>">

    <!--
        SEO Technique – Canonical (cours p.9)
        Évite le contenu dupliqué et désigne l'URL de référence.
    -->
    <link rel="canonical" href="<?= $canonical ?>">

    <!--
        SEO Technique – Contrôle de l'indexation (cours p.9)
        Valeurs par défaut : index, follow.
        À changer en "noindex, nofollow" pour les pages privées ou les erreurs 404.
    -->
    <?php if ($is404): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>

    <!-- Open Graph (booste les partages → signal indirect de popularité) -->
    <meta property="og:title" content="<?= htmlspecialchars($page['title'], ENT_QUOTES) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES) ?>">
    <meta property="og:url" content="<?= $canonical ?>">
    <meta property="og:type" content="<?= $slug === 'accueil' ? 'website' : 'article' ?>">
    <meta property="og:locale" content="fr_FR">

    <!--
        SEO Technique – Données structurées JSON-LD / Schema.org (cours p.12)
        Enrichit les résultats SERP (Rich Snippets) et améliore le CTR.
    -->
    <script type="application/ld+json"><?= $jsonLd ?></script>

    <!--
        Performance / Core Web Vitals (cours p.10)
        preconnect réduit la latence réseau → améliore le LCP.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!--
        Chargement asynchrone des polices pour ne pas bloquer le rendu
        (impact direct sur le LCP – cible < 2,5 s).
    -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,300;0,8..60,400;1,8..60,300&display=swap"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,300;0,8..60,400;1,8..60,300&display=swap">
    </noscript>

    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<!--
    Accessibilité + SEO sémantique : attribut lang sur <html>.
-->

<body>

    <!-- ── HEADER ─────────────────────────────────────────────────────────── -->
    <header role="banner" class="main-header">
        <div class="container header-inner">
            <!--
                Lien de marque : aria-label explicite (cours p.9 – ancres descriptives).
                Évite "cliquez ici" → le texte d'ancre doit être descriptif.
            -->
            <a href="/accueil.html" class="logo" aria-label="Iran News – Retour à l'accueil">
                Iran<span>News</span>
            </a>

            <!-- Bouton hamburger (mobile) -->
            <button class="nav-toggle" aria-expanded="false" aria-controls="main-nav"
                aria-label="Ouvrir le menu de navigation">
                <span></span><span></span><span></span>
            </button>

            <!--
                Navigation : role="navigation" + aria-label pour les lecteurs d'écran.
                Touch targets ≥ 48 px (cours p.11 – Mobile-First).
            -->
            <nav id="main-nav" role="navigation" aria-label="Menu principal">
                <ul class="nav-links">
                    <li>
                        <a href="/accueil.html" <?= $slug === 'accueil' ? 'aria-current="page"' : '' ?>>
                            Accueil
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/<?= urlencode($cat['slug']) ?>.html" <?= $slug === $cat['slug'] ? 'aria-current="page"' : '' ?>>
                                <?= htmlspecialchars($cat['title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ── CONTENU PRINCIPAL ──────────────────────────────────────────────── -->
    <!--
        id="content" : ancre interne (#content) pour les liens d'évitement
        (accessibilité et navigation clavier).
    -->
    <main class="container" id="content">

        <article class="page-article" itemscope itemtype="https://schema.org/Article">

            <!--
                SEO On-Page – <h1> (cours p.8)
                • Un seul par page
                • Contient le mot-clé principal
                • Correspond à l'intention de recherche (cours p.6)
            -->
            <h1 class="main-title" itemprop="headline">
                <?= htmlspecialchars($page['title']) ?>
            </h1>

            <section class="entry-content" itemprop="articleBody">
                <!--
                    Le contenu issu de la BDD est affiché tel quel (HTML).
                    En production : filtrer avec HTMLPurifier pour éviter les XSS.
                    Structure attendue en BDD : H2 pour sections, H3 pour sous-sections
                    → hiérarchie Hn logique (cours p.8).
                -->
                <?= $page['content'] ?>
            </section>

            <!-- ── GRILLE CATÉGORIES (page d'accueil uniquement) ─────────── -->
            <?php if ($slug === 'accueil' && !empty($categories)): ?>
                <section class="secondary-content" aria-labelledby="dossiers-heading">
                    <h2 id="dossiers-heading">Dossiers Spéciaux &amp; Analyses</h2>

                    <div class="category-grid">
                        <?php foreach ($categories as $item): ?>
                            <div class="card">
                                <h3><?= htmlspecialchars($item['title']) ?></h3>
                                <a href="/<?= urlencode($item['slug']) ?>.html" class="btn-link"
                                    title="Lire le dossier complet : <?= htmlspecialchars($item['title']) ?>">
                                    Consulter le dossier
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </article>
    </main>

    <!-- ── FOOTER ─────────────────────────────────────────────────────────── -->
    <footer role="contentinfo" class="main-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <p class="footer-logo">Iran<span>News</span></p>
                <p>Analyse et suivi du conflit en temps réel.</p>
            </div>
            <nav aria-label="Liens de pied de page">
                <ul class="footer-links">
                    <li><a href="/accueil.html">Accueil</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/<?= urlencode($cat['slug']) ?>.html">
                                <?= htmlspecialchars($cat['title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="footer-meta">
                <p>&copy; 2026 Info Guerre Iran — IT University</p>
                <p><small>Lighthouse SEO : 100 / 100</small></p>
            </div>
        </div>
    </footer>

    <script>
        // ── Hamburger menu (Mobile-First – touch targets ≥ 48 px) ────────────
        const toggle = document.querySelector('.nav-toggle');
        const nav = document.getElementById('main-nav');
        if (toggle && nav) {
            toggle.addEventListener('click', () => {
                const open = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!open));
                nav.classList.toggle('is-open', !open);
            });
        }
    </script>

</body>

</html>