<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ověření přihlášení uživatele
if (isset($_SESSION['user']['discord_id'])) {
    $discord_id = $_SESSION['user']['discord_id'];

    // Zahrnutí souboru db.php s připojením k databázi
    require '../php/db.php';

    // Ověření, zda jsou data v POSTu
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $punishment_id = $_POST['punishment_id'];
        $punishment_name = $_POST['punishment_name'];
        $punishment_type = $_POST['punishment_type'];
        $minimum_punishment_length = $_POST['minimum_punishment_length'];
        $maximum_punishment_length = $_POST['maximum_punishment_length'];
        $punishment_description = $_POST['punishment_description'];

        // Aktualizace záznamu v databázi
        $stmt = $pdo->prepare("UPDATE punishments_list SET punishment_name = ?, punishment_type = ?, minimum_punishment_length = ?, maximum_punishment_length = ?, punishment_description = ? WHERE id = ?");
        $stmt->execute([
            $punishment_name,
            $punishment_type,
            $minimum_punishment_length,
            $maximum_punishment_length,
            $punishment_description,
            $punishment_id
        ]);
    }
}
    // Přesměrování zpět na stránku s tabulkou
    header('Location: ../managment/punishments.php'); // Nebo jiný soubor, kde máš tuto tabulku
    exit();