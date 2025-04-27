<?php
define('SECURE', true);
session_start();
require '../php/db.php';

$discord_id = $_SESSION['user']['discord_id'];
$role_id = $_SESSION['user']['role_id'];
$type_filter = $_GET['type'] ?? null;

$query = "SELECT * FROM tickets WHERE 1=1";
$params = [];

if ($type_filter) {
    $query .= " AND type = ?";
    $params[] = $type_filter;
}

// Filtrování podle role
switch ($type_filter) {
    case 'dev':
        if (!in_array($role_id, [3, 4, 5])) die("Nepovolený přístup.");
        break;
    case 'admin':
        if (!in_array($role_id, [2, 4, 5])) die("Nepovolený přístup.");
        break;
    case 'managment':
    case 'unban':
    case 'tebex':
        if (!in_array($role_id, [4, 5])) die("Nepovolený přístup.");
        break;
    default:
        if ($type_filter == null && $role_id != 5) {
            // Vlastní tickety
            $query .= " AND discord_id = ?";
            $params[] = $discord_id;
        }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

echo json_encode($tickets);
