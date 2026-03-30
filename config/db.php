<?php
/**
 * Configuration de la connexion PostgreSQL
 * Adapté pour l'environnement Docker
 */

$host = getenv('DB_HOST') ?: 'db'; 
$db   = getenv('DB_NAME') ?: 'guerre_iran_db';
$user = getenv('DB_USER') ?: 'admin';
$pass = getenv('DB_PASS') ?: 'password';
$port = getenv('DB_PORT') ?: '5432'; // Port par défaut de Postgres

// Le DSN pour PostgreSQL utilise "pgsql:"
$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Note : On ne désactive pas l'émulation des prepares pour Postgres
    // car il le gère nativement de manière très sécurisée.
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion PostgreSQL : " . $e->getMessage());
}