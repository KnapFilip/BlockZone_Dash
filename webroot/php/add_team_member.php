<?php
define('SECURE', true);
session_start();
header('Content-Type: application/json');
require '../php/db.php';

// Přístupová kontrola
if (!isset($_SESSION['user']['discord_id']) || !in_array($_SESSION['user']['role_id'], [2, 4, 5])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Přístup odepřen."]);
    exit;
}

// Přijímáme pouze POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Špatná metoda požadavku."]);
    exit;
}

// Validace vstupu
$required_fields = ['name', 'role', 'date_of_entry', 'description', 'photo_url', 'order_priority'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Chybí požadované pole: $field"]);
        exit;
    }
}

// Ošetření a příprava dat
$name = trim($_POST['name']);
$role = trim($_POST['role']);
$date_of_entry = $_POST['date_of_entry'];
$description = trim($_POST['description']);
$photo_url = trim($_POST['photo_url']);
$order_priority = (int)$_POST['order_priority'];

// Zápis do databáze
try {
    $stmt = $pdo->prepare("INSERT INTO team_members (name, role, date_of_entry, description, photo_url, order_priority) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $role, $date_of_entry, $description, $photo_url, $order_priority]);
    echo json_encode(["status" => "success", "message" => "Člen týmu byl úspěšně přidán."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Chyba databáze: " . $e->getMessage()]);
}
header('Location: ../managment/team_details.php');
exit;
