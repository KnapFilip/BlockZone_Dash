<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECURE', true);
session_start();
require_once 'db.php';

// Ověření přihlášení uživatele
if (!isset($_SESSION['user']['discord_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Nejste přihlášen.']);
    exit;
}

$discord_id = $_SESSION['user']['discord_id'];

// Načteme údaje o uživateli
$stmt = $pdo->prepare("SELECT username, discriminator, email, role_id, created_at FROM dc_users WHERE discord_id = ?");
$stmt->execute([$discord_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen.']);
    exit;
}

$username = $user['username'];
$discriminator = $user['discriminator'];
$email = $user['email'];
$role_id = $user['role_id'];
$created_at = $user['created_at'];

// Připravíme jméno autora
$author = $username . "#" . $discriminator;

// Přečteme JSON z těla požadavku
$data = json_decode(file_get_contents('php://input'), true);

$plugin_id = isset($data['plugin_id']) ? (int)$data['plugin_id'] : 0;
$message = isset($data['message']) ? trim($data['message']) : '';

if ($plugin_id <= 0 || $message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Chybí plugin ID nebo zpráva.']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO plugin_chat (plugin_id, author, message, created_at)
        VALUES (:plugin_id, :author, :message, NOW())
    ');
    $stmt->execute([
        'plugin_id' => $plugin_id,
        'author' => $author,
        'message' => $message
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Chyba při odesílání zprávy: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Chyba při odesílání zprávy na serveru.']);
}
