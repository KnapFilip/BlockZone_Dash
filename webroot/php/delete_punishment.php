<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
require_once('../php/db.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];  // Převedení na celé číslo pro větší bezpečnost
    echo "ID: " . $id . "<br>"; // Ladicí výstup pro zobrazení ID

    try {
        // Zkontrolujeme, zda záznam s tímto ID skutečně existuje
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM punishments_list WHERE id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        echo "Počet záznamů: " . $count . "<br>"; // Ladicí výstup pro zobrazení počtu záznamů

        if ($count > 0) {
            // Bezpečný SQL příkaz pro delete
            $sql = "DELETE FROM punishments_list WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            // Přesměrování zpět na stránku s tabulkou
            header('Location: ../managment/punishments.php');
            exit();
        } else {
            // Pokud záznam neexistuje
            echo "Záznam s tímto ID neexistuje.";
        }
    } catch (PDOException $e) {
        // Ošetření chyby při připojení k databázi nebo provádění SQL příkazu
        echo "Chyba při mazání trestu: " . $e->getMessage();
    }
} else {
    echo "Neplatné ID.";
}
