<?php
// Zapnutí všech chyb pro ladění
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECURE', true);
session_start();

require_once 'db.php';

// Kontrola, jestli dorazila správná data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['plugin_id']) || !is_numeric($input['plugin_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Neplatné nebo chybějící ID pluginu.'
    ]);
    exit;
}

$plugin_id = (int)$input['plugin_id'];

try {
    // SMAZAT nejdřív všechny zprávy v plugin_chat
    $stmt = $pdo->prepare("DELETE FROM plugin_chat WHERE plugin_id = :id");
    $stmt->bindValue(':id', $plugin_id, PDO::PARAM_INT);
    $stmt->execute();

    // Až potom smažeme samotný plugin
    $stmt = $pdo->prepare("DELETE FROM plugins WHERE id = :id");
    $stmt->bindValue(':id', $plugin_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Plugin byl úspěšně smazán.'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Plugin s tímto ID nebyl nalezen.'
        ]);
    }
} catch (PDOException $e) {
    error_log('Chyba při mazání pluginu: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Chyba při mazání pluginu na serveru.'
    ]);
}
