<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require 'db.php';

// Začneme session pro ověření uživatele a práv
session_start();

// Zkontrolujeme, jestli je uživatel přihlášen a má dostatečná práva (např. role 3, 4, 5)
if (!isset($_SESSION['user']['discord_id']) || !in_array($_SESSION['user']['role_id'], [3, 4, 5])) {
    echo "Nemáš oprávnění pro tuto akci.";
    exit;
}

// Ověříme, jestli byl poslán ID pro smazání
if (isset($_GET['id'])) {
    // Ochrana proti nevalidním ID, validujeme, že ID je celé číslo
    $work_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    // Pokud není ID platné, zobrazíme chybu
    if ($work_id === false) {
        echo "Neplatné ID úkolu.";
        exit;
    }

    // Pokud je metoda požadavku POST (potvrzení smazání), pokračujeme
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Příprava SQL dotazu na smazání úkolu s bezpečným parametrem
        $stmt = $pdo->prepare("DELETE FROM work WHERE id = ?");
        $stmt->execute([$work_id]);

        // Po smazání přesměrujeme na seznam úkolů
        header("Location: ../development/work.php");
        exit;
    }
} else {
    echo "Chybí ID úkolu.";
    exit;
}
