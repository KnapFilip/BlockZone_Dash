<?php
define('SECURE', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../php/db.php';

// Kontrola, zda je uživatel přihlášen a má platný discord_id
if (!isset($_SESSION['user']['discord_id'])) {
    die("Nejste přihlášen.");
}

$discord_id = $_SESSION['user']['discord_id'];
$ticket_id = intval($_POST['ticket_id']);
$message = htmlspecialchars($_POST['message']);

// Ověření, zda existuje ticket_id a zpráva
if (empty($ticket_id) || empty($message)) {
    die("Chybí ticket ID nebo zpráva.");
}

// Vložení odpovědi do tabulky ticket_responses
$stmt = $pdo->prepare("INSERT INTO ticket_responses (ticket_id, discord_id, message) VALUES (?, ?, ?)");
$stmt->execute([$ticket_id, $discord_id, $message]);

// Přesměrování zpět na detail ticketu
header("Location: ../php/ticket_detail.php?id=" . $ticket_id);
exit();
