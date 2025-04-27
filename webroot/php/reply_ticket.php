<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
require 'db.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id   = $_SESSION['user_id'];
$role_id   = $_SESSION['role_id'];
$ticket_id = intval($_POST['ticket_id']);
$message   = trim($_POST['message']);

// Načteme typ ticketu pro kontrolu přístupu
$stmt = $pdo->prepare("SELECT type FROM tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die('Ticket nenalezen.');
}

// Ověření dle role (stejné jako v ticket_detail.php)
switch ($role_id) {
    case 5:
    case 4:
        $perms = ['admin', 'dev', 'managment', 'unban', 'tebex'];
        break;
    case 3:
        $perms = ['dev'];
        break;
    case 2:
        $perms = ['admin'];
        break;
    default:
        $perms = [];
}

if (!in_array($ticket['type'], $perms)) {
    die('Nemáte oprávnění odpovědět na tento ticket.');
}

// Uložíme odpověď a přepneme status
$stmt2 = $pdo->prepare(
    "INSERT INTO ticket_messages (ticket_id, sender_id, message)
     VALUES (?, ?, ?)"
);
$stmt2->execute([$ticket_id, $user_id, $message]);

$pdo->prepare(
    "UPDATE tickets
     SET updated_at = NOW(), status = 'in_progress'
     WHERE id = ?"
)->execute([$ticket_id]);

header('Location: ticket_detail.php?id=' . $ticket_id);
exit;
