<?php
// Zabezpečení proti přímému přístupu
if (!defined('SECURE')) {
    die('Přímý přístup zakázán');
}

// Konfigurace databáze
$host = 'cz1.helkor.eu:3306';
$db   = 's2009_blockzone_web';
$user = 'u2009_JLHLQca6m0';
$pass = 'porzhqo8Jw!0JQaRN=FQg5nA';

try {
    // Vytvoření připojení k databázi
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Nastavení PDO režimu pro chybové hlášení
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Pokud dojde k chybě při připojení, vypíše se chyba
    echo "Chyba připojení k databázi: " . $e->getMessage();
    exit;
}
