<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
require_once 'db.php'; // secure připojení

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Chybí ID.']);
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare('SELECT * FROM plugins WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $plugin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($plugin) {
        echo json_encode(['success' => true, 'plugin' => $plugin]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Plugin nenalezen.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
