<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_admin'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Non autorise'
    ]);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$slug = strtolower($slug);
$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
$slug = preg_replace('/[\s_-]+/', '-', $slug) ?? '';
$slug = trim($slug, '-');

if ($slug === '') {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Slug invalide'
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT slug, title, content, image, alt_images FROM pages WHERE slug = :slug LIMIT 1"
);
$stmt->execute(['slug' => $slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Page introuvable'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'slug' => $page['slug'],
    'title' => $page['title'],
    'content' => $page['content'],
    'image' => $page['image'],
    'alt_images' => $page['alt_images'],
]);
