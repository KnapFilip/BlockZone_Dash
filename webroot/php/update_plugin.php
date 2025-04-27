<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
require_once 'db.php'; // připojení k DB (secure režim)

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['field'], $data['value'])) {
    echo json_encode(['success' => false, 'error' => 'Neplatná data.']);
    exit;
}

$id = (int)$data['id'];
$field = $data['field'];
$value = $data['value'];

// Bezpečný whitelist polí, která lze upravit
$allowedFields = ['plugin_version', 'plugin_status', 'plugin_active', 'plugin_last_update'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'error' => 'Nepovolené pole.']);
    exit;
}

// Připrav SQL
try {
    $stmt = $pdo->prepare("UPDATE plugins SET `$field` = :value WHERE id = :id");
    $stmt->execute(['value' => $value, 'id' => $id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
