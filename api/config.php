<?php
declare(strict_types=1);

// Hostinger MySQL settings. Fill these once after creating the database.
const DB_HOST = 'localhost';
const DB_NAME = 'YOUR_DATABASE_NAME';
const DB_USER = 'YOUR_DATABASE_USER';
const DB_PASS = 'YOUR_DATABASE_PASSWORD';
const DB_CHARSET = 'utf8mb4';

// Exact Firebase project supplied for this app.
const FIREBASE_API_KEY = 'AIzaSyBBSsIRGaOswYUxMY6G6udxLM8V_-Tdytk';

// Public URL of this installation, without a trailing slash.
const APP_BASE_URL = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function json_response(array $data, int $status=200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}

function input_json(): array {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
