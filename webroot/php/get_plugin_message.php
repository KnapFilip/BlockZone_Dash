<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECURE', true);
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Získáme plugin_id z GET parametru
$plugin_id = isset($_GET['plugin_id']) ? (int)$_GET['plugin_id'] : 0;

if ($plugin_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Chybné plugin ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT author, message, created_at FROM plugin_chat WHERE plugin_id = :plugin_id ORDER BY created_at ASC');
    $stmt->execute(['plugin_id' => $plugin_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Chyba při načítání zpráv.'
    ]);
}
