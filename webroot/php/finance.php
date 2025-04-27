<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'db.php'; // připojení k databázi

// Nejprve získáme transakce z databáze
$stmt = $pdo->prepare("SELECT * FROM finance WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// Nastavíme počáteční zůstatek
$balance = 0;

// Pokud jsou transakce, počítáme zůstatek
if ($transactions) {
    foreach ($transactions as $transaction) {
        if ($transaction['type'] == 'Příjem') {
            $balance += $transaction['amount'];
        } else {
            $balance -= $transaction['amount'];
        }
    }
}

// Pokud je zůstatek null, nastavíme jej na 0
if ($balance === null) {
    $balance = 0;
}

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = $_POST['description'];
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id']; // předpokládám, že máš uživatele v session

    // Vložení do databáze
    $stmt = $pdo->prepare("INSERT INTO finance (description, amount, type, user_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$description, $amount, $type, $user_id]);

    // Přesměrování zpět na stránku, aby se data zformulovala
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
