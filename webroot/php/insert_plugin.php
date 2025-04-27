<?php
define('SECURE', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../php/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["plugin_name"];
    $version = $_POST["plugin_version"];
    $type = $_POST["plugin_type"];
    $link = $_POST["plugin_link"];
    $active = ($_POST["plugin_active"] == "1") ? "ano" : "ne";
    $last_update = $_POST["plugin_last_update"];

    try {
        $stmt = $pdo->prepare("INSERT INTO plugins (plugin_name, plugin_version, plugin_type, plugin_link, plugin_active, plugin_last_update)
                               VALUES (:name, :version, :type, :link, :active, :last_update)");
        $stmt->execute([
            ':name' => $name,
            ':version' => $version,
            ':type' => $type,
            ':link' => $link,
            ':active' => $active,
            ':last_update' => $last_update
        ]);

        header("Location: ../development/plugins.php");
        exit;
    } catch (PDOException $e) {
        echo "❌ Chyba při přidávání pluginu: " . $e->getMessage();
    }
}
