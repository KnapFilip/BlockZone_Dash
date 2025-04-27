<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require '../php/db.php';

// Kontrola přihlášení
if (!isset($_SESSION['user']['discord_id'])) {
    header("Location: ../login.php");
    exit;
}

$discord_id = $_SESSION['user']['discord_id'];

// Získání informací o přihlášeném uživateli
$stmt = $pdo->prepare("SELECT username, role_id, discord_id FROM dc_users WHERE discord_id = ?");
$stmt->execute([$discord_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !in_array($user['role_id'], [3, 4, 5])) {
    echo "Nemáš oprávnění pro přístup k této stránce.";
    exit;
}

$username = $user['username'];
$role_id = $user['role_id'];

// Ověření, zda je 'claim' tlačítko stisknuto a 'task_id' je k dispozici
if (isset($_POST['claim']) && isset($_POST['task_id'])) {
    $task_id = (int) $_POST['task_id']; // Zajišťujeme, že task_id je celé číslo

    // Změníme hodnoty v databázi pro tento úkol
    $stmt = $pdo->prepare("UPDATE work SET who = ?, status = 'Probíhá' WHERE id = ?");
    $stmt->execute([$username, $task_id]); // Přiřazujeme uživatelské jméno k úkolu

    // Přesměrování zpět na seznam úkolů
    header("Location: ../development/work.php");
    exit;
} else {
    echo 'Chyba: Neplatná žádost.';
}
