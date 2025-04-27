<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require '../php/db.php';

// Kontrola přihlášení
if (!isset($_SESSION['user']['discord_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nejste přihlášen']);
    exit;
}

$discord_id = $_SESSION['user']['discord_id'];

// Načtení dat z POST požadavku
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Nebyly poskytnuty žádné údaje.']);
    exit;
}

foreach ($data as $plugin) {
    if (!isset($plugin['id'], $plugin['plugin_version'], $plugin['plugin_status'], $plugin['plugin_active'], $plugin['plugin_last_update'])) {
        echo json_encode(['success' => false, 'message' => 'Nebyly poskytnuty všechny požadované údaje.']);
        exit;
    }

    $id = (int) $plugin['id'];
    $plugin_version = htmlspecialchars($plugin['plugin_version']);
    $plugin_status = htmlspecialchars($plugin['plugin_status']);
    $plugin_active = htmlspecialchars($plugin['plugin_active']);
    $plugin_last_update = $plugin['plugin_last_update'];

    // Příprava SQL dotazu pro aktualizaci pluginu
    $stmt = $pdo->prepare("UPDATE plugins SET plugin_version = ?, plugin_status = ?, plugin_active = ?, plugin_last_update = ? WHERE id = ?");
    $stmt->execute([$plugin_version, $plugin_status, $plugin_active, $plugin_last_update, $id]);

    // Kontrola, zda byla aktualizace úspěšná
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Nebyly provedeny žádné změny.']);
        exit;
    }
}

echo json_encode(['success' => true]);
