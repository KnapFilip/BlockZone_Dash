<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require '../php/db.php';

// Ověření, zda jsou údaje ve POSTu
if (isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = (int) $_POST['task_id'];
    $status = $_POST['status'];

    // Aktualizace statusu úkolu v databázi
    $stmt = $pdo->prepare("UPDATE work SET status = ? WHERE id = ?");
    $stmt->execute([$status, $task_id]);

    echo "Status úkolu byl úspěšně změněn.";
} else {
    echo "Chyba: Neplatné údaje.";
}
