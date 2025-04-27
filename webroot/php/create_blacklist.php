<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Připojení k databázi
define('SECURE', true);
require ('../php/db.php');

// PŘIDÁNÍ NOVÉHO ZÁZNAMU
if (isset($_POST['add'])) {
    $new_name = trim($_POST['new_name']);
    $new_discord_id = trim($_POST['new_discord_id']);
    $new_reason = trim($_POST['new_reason']);

    // Validace Discord ID
    if (!preg_match('/^\d{17,21}$/', $new_discord_id)) {
        die('Chyba: Discord ID musí být číslo o délce 17 až 21 znaků.');
    }

    // Pokud vše v pohodě, ulož
    $stmt = $pdo->prepare("INSERT INTO blacklist (name, discord_id, reason) VALUES (?, ?, ?)");
    $stmt->execute([$new_name, $new_discord_id, $new_reason]);

    header("Location: blacklist.php");
    exit;
}

// NAČTENÍ VŠECH ZÁZNAMŮ DO TABULKY
try {
    $query = "SELECT * FROM blacklist";
    $result = $pdo->query($query);
} catch (PDOException $e) {
    die("Chyba při načítání blacklistu: " . $e->getMessage());
}
