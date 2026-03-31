<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_admin'])) {
	http_response_code(401);
	echo json_encode([
		'status' => 'error',
		'message' => 'Non autorisé'
	]);
	exit;
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode([
		'status' => 'error',
		'message' => 'Méthode non autorisée'
	]);
	exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);

if (!is_array($payload)) {
	$payload = $_POST;
}

$title = trim((string)($payload['title'] ?? ''));
$content = (string)($payload['content'] ?? '');
$slugInput = trim((string)($payload['slug'] ?? ''));
$image = trim((string)($payload['image'] ?? ''));
$altImages = trim((string)($payload['alt_images'] ?? ''));

if ($title === '' || trim($content) === '') {
	http_response_code(422);
	echo json_encode([
		'status' => 'error',
		'message' => 'Le titre et le contenu sont obligatoires'
	]);
	exit;
}

$slugBase = $slugInput !== '' ? $slugInput : $title;
$slug = strtolower($slugBase);
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

$metaDescRaw = trim(strip_tags($content));
$metaDesc = mb_substr($metaDescRaw, 0, 160);

try {
	$stmt = $pdo->prepare(
		"INSERT INTO pages (slug, title, content, meta_desc, image, alt_images)
		 VALUES (:slug, :title, :content, :meta_desc, :image, :alt_images)
		 ON CONFLICT (slug)
		 DO UPDATE SET
			title = EXCLUDED.title,
			content = EXCLUDED.content,
			meta_desc = EXCLUDED.meta_desc,
			image = EXCLUDED.image,
			alt_images = EXCLUDED.alt_images"
	);

	$stmt->execute([
		'slug' => $slug,
		'title' => $title,
		'content' => $content,
		'meta_desc' => $metaDesc,
		'image' => $image !== '' ? $image : null,
		'alt_images' => $altImages !== '' ? $altImages : null,
	]);

	echo json_encode([
		'status' => 'success',
		'message' => 'Page enregistrée',
		'slug' => $slug,
		'url' => '/' . $slug . '.html'
	]);
} catch (Throwable $exception) {
	http_response_code(500);
	echo json_encode([
		'status' => 'error',
		'message' => 'Erreur lors de la sauvegarde'
	]);
}

