<?php
/**
 * sitemap.xml — Généré dynamiquement depuis la BDD (cours p.11)
 * Soumettre à Google Search Console après déploiement.
 * Déclaré dans robots.txt : Sitemap: https://domaine.com/sitemap.xml
 *
 * Fréquences :
 *   - Accueil   : daily   (mise à jour fréquente)
 *   - Articles  : weekly  (contenu plus stable)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/xml; charset=utf-8');

$proto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES);
$baseUrl = $proto . '://' . $host;

// Récupère toutes les pages avec date de modification
$pages = $pdo->query(
    "SELECT slug, updated_at FROM pages ORDER BY id ASC"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $p):
    $url      = $baseUrl . '/' . urlencode($p['slug']) . '.html';
    $lastmod  = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : date('Y-m-d');
    $freq     = ($p['slug'] === 'accueil') ? 'daily' : 'weekly';
    $priority = ($p['slug'] === 'accueil') ? '1.0'   : '0.8';
?>
    <url>
        <loc><?= htmlspecialchars($url) ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq><?= $freq ?></changefreq>
        <priority><?= $priority ?></priority>
    </url>
<?php endforeach; ?>
</urlset>