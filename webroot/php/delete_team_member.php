<?php
define('SECURE', true);
require 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: ../managment/team_details.php');
exit;
