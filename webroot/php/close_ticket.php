<?php
define('SECURE', true);
session_start();

require 'db.php';

// Kontrola, zda je uživatel přihlášen
if (!isset($_SESSION['user']['discord_id'], $_SESSION['user']['role_id'])) {
    header("Location: ../users/login.php");
    exit;
}

$discord_id = $_SESSION['user']['discord_id'];
$role_id = (int) $_SESSION['user']['role_id'];

// Kontrola POST dat
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id'])) {
    echo "Neplatné ID ticketu.";
    exit;
}

$ticket_id = (int) $_POST['ticket_id'];

// Načtení ticketu
$stmt = $pdo->prepare("SELECT discord_id, status FROM tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

// Ověření, že ticket existuje
if (!$ticket) {
    echo "Ticket nebyl nalezen.";
    exit;
}

// Kontrola oprávnění: majitel ticketu nebo role admin/dev/management/webadmin
$allowed_roles = [2, 3, 4, 5];
if ($ticket['discord_id'] !== $discord_id && !in_array($role_id, $allowed_roles, true)) {
    echo "Nemáš oprávnění uzavřít tento ticket.";
    exit;
}

// Zkontroluj, jestli už není zavřený (nepovinné, ale dobré)
if ($ticket['status'] === 'closed') {
    echo "Ticket je již uzavřen.";
    exit;
}

// Uzavření ticketu
$stmt = $pdo->prepare("UPDATE tickets SET status = 'closed' WHERE id = ?");
$stmt->execute([$ticket_id]);

header("Location: ../users/tickets.php");
exit;
