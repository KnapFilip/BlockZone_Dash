<?php
define('SECURE', true);
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_of_entry = $_POST['date_of_entry'] ?? '';
    $photo_url = $_POST['photo_url'] ?? '';
    $order_priority = $_POST['order_priority'] ?? 1;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE team_members SET name = ?, role = ?, description = ?, date_of_entry = ?, photo_url = ?, order_priority = ? WHERE id = ?");
        $success = $stmt->execute([$name, $role, $description, $date_of_entry, $photo_url, $order_priority, $id]);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
