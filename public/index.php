<?php
// ─── Connexion BDD ──────────────────────────────────────────────────────────
require_once __DIR__ . '/../config/db.php';

// ─── Slug + nettoyage ───────────────────────────────────────────────────────
$slug = $_GET['slug'] ?? 'accueil';
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

// ─── 1. Page demandée (on récupère aussi les dates pour le JSON-LD) ─────────
$stmt = $pdo->prepare(
    "SELECT title, content, meta_desc, image, created_at, updated_at
     FROM pages WHERE slug = :slug LIMIT 1"
);
$stmt->execute(['slug' => $slug]);
$page  = $stmt->fetch();
$is404 = !$page;

// ─── 2. Navigation ──────────────────────────────────────────────────────────
$categories = $pdo->query(
    "SELECT title, slug FROM pages WHERE slug != 'accueil' ORDER BY id ASC"
)->fetchAll();

// ─── 3. Gestion 404 ─────────────────────────────────────────────────────────
if ($is404) {
    header('HTTP/1.1 404 Not Found');
    $page = [
        'title'      => 'Page non trouvée',
        'content'    => '',
        'meta_desc'  => 'Erreur 404 – Page introuvable sur IranNews.',
        'image'      => null,
        'created_at' => null,
        'updated_at' => null,
    ];
}

// ─── 4. Helpers SEO ─────────────────────────────────────────────────────────
$metaDesc  = mb_substr(strip_tags((string)($page['meta_desc'] ?? '')), 0, 159);
$proto     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES);
$baseUrl   = $proto . '://' . $host;
$canonical = $baseUrl . '/' . urlencode($slug) . '.html';
$pageTitle = htmlspecialchars($page['title'], ENT_QUOTES) . ' | IranNews 2026';

// Image OG : image propre à la page ou image par défaut
$ogImage = !empty($page['image'])
    ? $baseUrl . '/img/' . rawurlencode($page['image'])
    : $baseUrl . '/img/og-default.jpg';

// Dates ISO 8601 pour Schema.org
$datePublished = !empty($page['created_at']) ? date('c', strtotime($page['created_at'])) : null;
$dateModified  = !empty($page['updated_at']) ? date('c', strtotime($page['updated_at'])) : $datePublished;

// ─── 5. JSON-LD Schema.org ───────────────────────────────────────────────────
if ($slug === 'accueil') {
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'WebSite',
        'name'        => 'IranNews',
        'url'         => $baseUrl,
        'description' => $metaDesc,
        'inLanguage'  => 'fr-FR',
        'publisher'   => [
            '@type' => 'Organization',
            'name'  => 'IranNews',
            'url'   => $baseUrl,
            'logo'  => ['@type' => 'ImageObject', 'url' => $baseUrl . '/img/logo.png'],
        ],
    ];
} else {
    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'Article',
        'headline'   => $page['title'],
        'url'        => $canonical,
        'inLanguage' => 'fr-FR',
        'image'      => $ogImage,
        'publisher'  => [
            '@type' => 'Organization',
            'name'  => 'IranNews',
            'url'   => $baseUrl,
            'logo'  => ['@type' => 'ImageObject', 'url' => $baseUrl . '/img/logo.png'],
        ],
        'breadcrumb' => [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1,
                 'name'  => 'Accueil', 'item' => $baseUrl . '/accueil.html'],
                ['@type' => 'ListItem', 'position' => 2,
                 'name'  => $page['title'], 'item' => $canonical],
            ],
        ],
    ];
    if ($datePublished) $schema['datePublished'] = $datePublished;
    if ($dateModified)  $schema['dateModified']  = $dateModified;
}
$jsonLd = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// ─── 6. Découpe catégories pour le mega-menu (5 par colonne) ────────────────
$catChunks = array_chunk($categories, 5);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <!--
        Mobile-First (cours p.11) — balise viewport obligatoire pour Google.
    -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--
        Favicons + theme-color (améliore le score Lighthouse PWA/Best Practices).
    -->
    <link rel="icon" type="image/svg+xml" href="/img/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png">
    <meta name="theme-color" content="#c0392b">

    <!--
        <title> (cours p.8) : mot-clé en premier · unique · 50-60 car.
    -->
    <title><?= $pageTitle ?></title>

    <!--
        Meta description (cours p.8) : 150-160 car. · incitative · unique.
    -->
    <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES) ?>">

    <!--
        Canonical (cours p.9) — évite le contenu dupliqué.
    -->
    <link rel="canonical" href="<?= $canonical ?>">

    <!--
        Robots (cours p.9) — noindex sur 404 pour économiser le crawl budget.
    -->
    <?php if ($is404): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>

    <!-- ── Open Graph (partages sociaux → signal de popularité indirect) ── -->
    <meta property="og:title"       content="<?= htmlspecialchars($page['title'], ENT_QUOTES) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES) ?>">
    <meta property="og:url"         content="<?= $canonical ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($ogImage, ENT_QUOTES) ?>">
    <meta property="og:type"        content="<?= $slug === 'accueil' ? 'website' : 'article' ?>">
    <meta property="og:locale"      content="fr_FR">
    <meta property="og:site_name"   content="IranNews">

    <!-- ── Twitter Card ── -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= htmlspecialchars($page['title'], ENT_QUOTES) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($ogImage, ENT_QUOTES) ?>">

    <!--
        Données structurées JSON-LD / Schema.org (cours p.12)
        — Rich Snippets dans la SERP → meilleur CTR.
    -->
    <script type="application/ld+json"><?= $jsonLd ?></script>

    <!-- Référence au sitemap (également déclaré dans robots.txt) -->
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">

    <!--
        Polices — preconnect réduit la latence → améliore LCP (cours p.10).
        Chargement async via media="print" trick pour ne pas bloquer le rendu.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,300;0,8..60,400;1,8..60,300&display=swap"
          media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,300;0,8..60,400;1,8..60,300&display=swap">
    </noscript>

    <link rel="stylesheet" href="/css/style.css">
</head>

<body>

    <!-- ── SKIP LINK (accessibilité + SEO, cours p.9) ────────────────────── -->
    <a href="#content" class="skip-link">Aller au contenu principal</a>

    <!-- ════════════════════════════════════════════════════════════════════
         HEADER
    ════════════════════════════════════════════════════════════════════════ -->
    <header role="banner" class="main-header">
        <div class="header-inner container">

            <a href="/accueil.html" class="logo" aria-label="IranNews – Retour à l'accueil">
                Iran<span>News</span>
            </a>

            <!--
                NAV DESKTOP — mega-dropdown "Dossiers"
                Toutes les catégories dans le dropdown : navbar toujours propre.
            -->
            <nav id="main-nav" role="navigation" aria-label="Menu principal">
                <ul class="nav-list">

                    <li class="nav-item">
                        <a href="/accueil.html"
                           class="nav-link <?= $slug === 'accueil' ? 'nav-link--active' : '' ?>"
                           <?= $slug === 'accueil' ? 'aria-current="page"' : '' ?>>
                            Accueil
                        </a>
                    </li>

                    <?php if (!empty($categories)): ?>
                    <li class="nav-item nav-item--dropdown">
                        <button class="nav-link nav-dropdown-toggle"
                                aria-expanded="false"
                                aria-haspopup="true"
                                aria-controls="dropdown-dossiers">
                            Dossiers
                            <svg class="nav-chevron" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12">
                                <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8"
                                      fill="none" stroke-linecap="round"/>
                            </svg>
                        </button>

                        <div id="dropdown-dossiers"
                             class="nav-dropdown"
                             role="region"
                             aria-label="Liste des dossiers">
                            <div class="nav-dropdown__inner">
                                <?php foreach ($catChunks as $chunk): ?>
                                <ul class="nav-dropdown__col">
                                    <?php foreach ($chunk as $cat): ?>
                                    <li>
                                        <a href="/<?= urlencode($cat['slug']) ?>.html"
                                           class="nav-dropdown__link <?= $slug === $cat['slug'] ? 'nav-dropdown__link--active' : '' ?>"
                                           <?= $slug === $cat['slug'] ? 'aria-current="page"' : '' ?>>
                                            <?= htmlspecialchars($cat['title']) ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a href="/a-propos.html" class="nav-link">À propos</a>
                    </li>

                </ul>
            </nav>

            <button class="nav-toggle"
                    aria-expanded="false"
                    aria-controls="mobile-drawer"
                    aria-label="Ouvrir le menu de navigation">
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
            </button>
        </div>
    </header>

    <!-- ════════════════════════════════════════════════════════════════════
         DRAWER MOBILE
    ════════════════════════════════════════════════════════════════════════ -->
    <div id="mobile-drawer"
         class="mobile-drawer"
         aria-hidden="true"
         role="dialog"
         aria-modal="true"
         aria-label="Menu de navigation">
        <div class="mobile-drawer__overlay"></div>
        <nav class="mobile-drawer__panel" aria-label="Navigation mobile">

            <button class="mobile-drawer__close" aria-label="Fermer le menu">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M4 4l12 12M16 4L4 16" stroke="currentColor"
                          stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="mobile-drawer__logo" aria-hidden="true">Iran<span>News</span></div>

            <ul class="mobile-nav-list">
                <li>
                    <a href="/accueil.html"
                       class="mobile-nav-link <?= $slug === 'accueil' ? 'mobile-nav-link--active' : '' ?>">
                        Accueil
                    </a>
                </li>

                <?php if (!empty($categories)): ?>
                <li class="mobile-nav-section">
                    <button class="mobile-nav-accordion" aria-expanded="false">
                        Dossiers
                        <svg class="nav-chevron" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12">
                            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8"
                                  fill="none" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <ul class="mobile-nav-subnav">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/<?= urlencode($cat['slug']) ?>.html"
                               class="mobile-nav-sublink <?= $slug === $cat['slug'] ? 'mobile-nav-sublink--active' : '' ?>">
                                <?= htmlspecialchars($cat['title']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <li><a href="/a-propos.html" class="mobile-nav-link">À propos</a></li>
            </ul>
        </nav>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════
         CONTENU PRINCIPAL
    ════════════════════════════════════════════════════════════════════════ -->
    <main class="main-content container" id="content">

        <?php if ($is404): ?>
        <!-- ── PAGE 404 DÉDIÉE ──────────────────────────────────────────── -->
        <div class="error-404">
            <p class="error-404__code" aria-hidden="true">404</p>
            <h1 class="error-404__title">Page introuvable</h1>
            <p class="error-404__text">
                Le contenu que vous recherchez n'existe pas ou a été déplacé.
            </p>
            <a href="/accueil.html" class="btn-primary">← Retourner à l'accueil</a>
        </div>

        <?php else: ?>
        <!-- ── ARTICLE ───────────────────────────────────────────────────── -->
        <article class="page-article"
                 itemscope
                 itemtype="https://schema.org/Article">

            <!--
                FIL D'ARIANE / BreadcrumbList (cours p.12)
                UX : repérage de l'utilisateur dans le site.
                SEO : hiérarchie signalée à Google via microdata + JSON-LD.
            -->
            <?php if ($slug !== 'accueil'): ?>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <ol class="breadcrumb__list"
                    itemscope
                    itemtype="https://schema.org/BreadcrumbList">
                    <li class="breadcrumb__item"
                        itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                        <a href="/accueil.html" itemprop="item">
                            <span itemprop="name">Accueil</span>
                        </a>
                        <meta itemprop="position" content="1">
                    </li>
                    <li class="breadcrumb__item breadcrumb__item--current"
                        itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                        <span itemprop="name"><?= htmlspecialchars($page['title']) ?></span>
                        <meta itemprop="item"     content="<?= $canonical ?>">
                        <meta itemprop="position" content="2">
                    </li>
                </ol>
            </nav>
            <?php endif; ?>

            <header class="article-header">
                <!--
                    H1 (cours p.8) : un seul par page · mot-clé principal.
                -->
                <h1 class="article-title" itemprop="headline">
                    <?= htmlspecialchars($page['title']) ?>
                </h1>

                <!--
                    Image hero de l'article.
                    alt (cours p.9) : description précise · évite le bourrage.
                    width + height déclarés → évite le CLS (cours p.10).
                    loading="eager" car image above-the-fold → améliore LCP.
                -->
                <?php if (!empty($page['image'])): ?>
                <figure class="article-hero">
                    <img
                        src="/img/<?= htmlspecialchars($page['image'], ENT_QUOTES) ?>"
                        alt="Illustration pour l'article : <?= htmlspecialchars($page['title'], ENT_QUOTES) ?>"
                        width="820"
                        height="460"
                        loading="eager"
                        decoding="async"
                        itemprop="image">
                </figure>
                <?php endif; ?>

                <!--
                    <time> sémantique — aide Google à évaluer la fraîcheur du contenu.
                -->
                <?php if ($datePublished): ?>
                <p class="article-meta">
                    Publié le
                    <time datetime="<?= $datePublished ?>" itemprop="datePublished">
                        <?= date('d F Y', strtotime($page['created_at'])) ?>
                    </time>
                    <?php if ($dateModified && $dateModified !== $datePublished): ?>
                    · Mis à jour le
                    <time datetime="<?= $dateModified ?>" itemprop="dateModified">
                        <?= date('d F Y', strtotime($page['updated_at'])) ?>
                    </time>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </header>

            <!--
                Corps de l'article (HTML issu de la BDD).
                Structure attendue : H2 pour sections, H3 pour sous-sections.
                Hiérarchie Hn logique (cours p.8).
                Note production : filtrer avec HTMLPurifier pour prévenir les XSS.
            -->
            <section class="article-body" itemprop="articleBody">
                <?= $page['content'] ?>
            </section>

            <!--
                PAGINATION ENTRE ARTICLES (rel="prev" / rel="next")
                Signal de structure interne pour les moteurs de recherche.
            -->
            <?php
            $prevStmt = $pdo->prepare(
                "SELECT title, slug FROM pages
                 WHERE slug != 'accueil'
                   AND id < (SELECT id FROM pages WHERE slug = :slug)
                 ORDER BY id DESC LIMIT 1"
            );
            $prevStmt->execute(['slug' => $slug]);
            $prevPage = $prevStmt->fetch();

            $nextStmt = $pdo->prepare(
                "SELECT title, slug FROM pages
                 WHERE slug != 'accueil'
                   AND id > (SELECT id FROM pages WHERE slug = :slug)
                 ORDER BY id ASC LIMIT 1"
            );
            $nextStmt->execute(['slug' => $slug]);
            $nextPage = $nextStmt->fetch();
            ?>
            <?php if ($prevPage || $nextPage): ?>
            <nav class="article-pagination" aria-label="Articles précédent et suivant">
                <div class="pagination-inner">
                    <?php if ($prevPage): ?>
                    <a href="/<?= urlencode($prevPage['slug']) ?>.html"
                       class="pagination-link pagination-link--prev"
                       rel="prev">
                        <span class="pagination-label">← Précédent</span>
                        <span class="pagination-title"><?= htmlspecialchars($prevPage['title']) ?></span>
                    </a>
                    <?php else: ?><div></div><?php endif; ?>

                    <?php if ($nextPage): ?>
                    <a href="/<?= urlencode($nextPage['slug']) ?>.html"
                       class="pagination-link pagination-link--next"
                       rel="next">
                        <span class="pagination-label">Suivant →</span>
                        <span class="pagination-title"><?= htmlspecialchars($nextPage['title']) ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>

            <!-- ── GRILLE DOSSIERS (page d'accueil uniquement) ─────────── -->
            <?php if ($slug === 'accueil' && !empty($categories)): ?>
            <section class="dossiers-section" aria-labelledby="dossiers-heading">
                <h2 class="dossiers-title" id="dossiers-heading">
                    Dossiers Spéciaux &amp; Analyses
                </h2>
                <div class="dossiers-grid">
                    <?php foreach ($categories as $item): ?>
                    <div class="dossier-card">
                        <h3 class="dossier-card__title">
                            <?= htmlspecialchars($item['title']) ?>
                        </h3>
                        <a href="/<?= urlencode($item['slug']) ?>.html"
                           class="dossier-card__link"
                           title="Lire le dossier complet : <?= htmlspecialchars($item['title']) ?>">
                            Consulter le dossier
                            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 14 14">
                                <path d="M3 7h8M7 3l4 4-4 4" stroke="currentColor" stroke-width="1.6"
                                      fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

        </article>
        <?php endif; ?>

    </main>

    <!-- ════════════════════════════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════════════════════════════ -->
    <footer role="contentinfo" class="main-footer">
        <div class="container footer-inner">

            <div class="footer-brand">
                <p class="footer-logo">Iran<span>News</span></p>
                <p class="footer-tagline">Analyse et suivi du conflit en temps réel.</p>
            </div>

            <nav aria-label="Liens de pied de page" class="footer-nav">
                <p class="footer-nav__heading">Navigation</p>
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
                <p>&copy; <?= date('Y') ?> IranNews — IT University</p>
                <p><small>Lighthouse SEO : 100 / 100</small></p>
                <p class="footer-legal">
                    <a href="/mentions-legales.html">Mentions légales</a>
                </p>
            </div>

        </div>
    </footer>

    <!-- ════════════════════════════════════════════════════════════════════
         SCRIPTS
    ════════════════════════════════════════════════════════════════════════ -->
    <script>
    (() => {
        // ── Mega-dropdown desktop ────────────────────────────────────────────
        const dropdownToggle = document.querySelector('.nav-dropdown-toggle');
        const dropdown       = document.getElementById('dropdown-dossiers');

        if (dropdownToggle && dropdown) {
            dropdownToggle.addEventListener('click', () => {
                const open = dropdownToggle.getAttribute('aria-expanded') === 'true';
                dropdownToggle.setAttribute('aria-expanded', String(!open));
                dropdown.classList.toggle('is-open', !open);
            });
            document.addEventListener('click', (e) => {
                if (!dropdownToggle.closest('.nav-item--dropdown').contains(e.target)) {
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    dropdown.classList.remove('is-open');
                }
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    dropdown.classList.remove('is-open');
                    dropdownToggle.focus();
                }
            });
        }

        // ── Drawer mobile ────────────────────────────────────────────────────
        const navToggle   = document.querySelector('.nav-toggle');
        const drawer      = document.getElementById('mobile-drawer');
        const drawerClose = drawer?.querySelector('.mobile-drawer__close');
        const overlay     = drawer?.querySelector('.mobile-drawer__overlay');

        function openDrawer() {
            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            navToggle.setAttribute('aria-expanded', 'true');
            document.body.classList.add('drawer-open');
            drawerClose?.focus();
        }
        function closeDrawer() {
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            navToggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('drawer-open');
            navToggle?.focus();
        }

        navToggle?.addEventListener('click', openDrawer);
        drawerClose?.addEventListener('click', closeDrawer);
        overlay?.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && drawer?.classList.contains('is-open')) closeDrawer();
        });

        // ── Accordéon dans le drawer ─────────────────────────────────────────
        document.querySelectorAll('.mobile-nav-accordion').forEach(btn => {
            btn.addEventListener('click', () => {
                const open = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', String(!open));
                btn.nextElementSibling?.classList.toggle('is-open', !open);
            });
        });
    })();
    </script>

</body>
</html>