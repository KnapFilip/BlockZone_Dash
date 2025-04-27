<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    // Zahrnutí souboru db.php s připojením k databázi
    require 'db.php';

// Kontrola, zda formulář byl odeslán
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Získání hodnot z formuláře
    $punishment_name = trim($_POST['punishment_name']);
    $punishment_type = trim($_POST['punishment_type']);
    $minimum_punishment_length = trim($_POST['minimum_punishment_length']);
    $maximum_punishment_length = trim($_POST['maximum_punishment_length']);
    $punishment_description = trim($_POST['punishment_description']);

    // Validace vstupů
    if (empty($punishment_name) || empty($punishment_type) || empty($punishment_description) || !is_numeric($minimum_punishment_length) || !is_numeric($maximum_punishment_length)) {
        echo "Všechna pole musí být vyplněna správně.";
    } else {
        try {
            // Připravení SQL dotazu pro vložení dat do databáze
            $stmt = $pdo->prepare("INSERT INTO punishments_list (punishment_name, punishment_type, minimum_punishment_length, maximum_punishment_length, punishment_description) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $punishment_name,
                $punishment_type,
                $minimum_punishment_length,
                $maximum_punishment_length,
                $punishment_description,
            ]);

            // Přesměrování na seznam trestů po úspěšném přidání
            header("Location: ../managment/punishments.php");
            exit;
        } catch (PDOException $e) {
            echo "Chyba při připojení k databázi: " . $e->getMessage();
        }
    }
}
