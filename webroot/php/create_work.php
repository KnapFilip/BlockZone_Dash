<?php
// Aktivace chybového hlášení pro lepší ladění
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require 'db.php';


// Aktivace chybového hlášení pro lepší ladění
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Připojení k databázi
require '../php/db.php';

// Zkontrolujeme, zda byl formulář odeslán
if (isset($_POST['submit_task'])) {
    // Načteme hodnoty z formuláře
    $what = htmlspecialchars($_POST['what']); // Název úkolu
    $who = htmlspecialchars($_POST['who']);   // Popis úkolu

    // Příprava SQL dotazu pro vložení nového úkolu do tabulky "work"
    // Status je nastaven na výchozí hodnotu 'Práce nezačata' a last_update na aktuální čas
    $stmt = $pdo->prepare("INSERT INTO work (what, who, status, last_update) VALUES (?, ?, 'Práce nezačata', NOW())");

    // Spuštění SQL dotazu a vložení dat
    if ($stmt->execute([$what, $who])) {
        // Po úspěšném vložení přesměrujeme na seznam úkolů
        header("Location: ../development/work.php");
        exit;
    } else {
        // Pokud dojde k chybě při vkládání, vypíšeme chybu
        echo "Došlo k chybě při vytváření úkolu.";
    }
}
