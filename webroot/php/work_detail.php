<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require '../php/db.php';

// Nastavení kódování
$pdo->exec("SET NAMES 'utf8mb4'");

// Kontrola přihlášení
if (!isset($_SESSION['user']['discord_id'])) {
    header("Location: ../login.php");
    exit;
}

$discord_id = $_SESSION['user']['discord_id'];

// Získání informací o přihlášeném uživateli
$stmt = $pdo->prepare("SELECT username, role_id, discord_id FROM dc_users WHERE discord_id = ?");
$stmt->execute([$discord_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !in_array($user['role_id'], [3, 4, 5])) {
    echo "Nemáš oprávnění pro přístup k této stránce.";
    exit;
}

$username = $user['username'];
$role_id = $user['role_id'];

// Získání detailu pro daný work
if (isset($_GET['id'])) {
    $work_id = intval($_GET['id']);

    $stmt = $pdo->prepare("SELECT * FROM work WHERE id = ?");
    $stmt->execute([$work_id]);
    $work = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$work) {
        echo "Úkol nebyl nalezen.";
        exit;
    }
} else {
    echo "Chybí ID úkolu.";
    exit;
}

// Načti chat pro tento úkol
$stmt = $pdo->prepare("SELECT wc.*, du.username FROM work_chat wc JOIN dc_users du ON wc.discord_id = du.discord_id WHERE wc.work_id = ? ORDER BY wc.created_at ASC");
$stmt->execute([$work_id]);
$chat_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nejprve smažeme závislé záznamy z tabulky work_chat
$stmt = $pdo->prepare("DELETE FROM work_chat WHERE work_id = ?");
$stmt->execute([$work_id]);

// Nyní smažeme úkol z tabulky work
$stmt = $pdo->prepare("DELETE FROM work WHERE id = ?");
$stmt->execute([$work_id]);

header("Location: ../development/work.php"); // Přesměrování po smazání
exit;


// Přidání zprávy do chatu
if (isset($_POST['message']) && !empty($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO work_chat (work_id, discord_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$work_id, $discord_id, $message]);
    header("Location: work_detail.php?id=$work_id"); // Obnovení stránky s novým chatem
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Detail Úkolu - <?= htmlspecialchars($work['what']) ?></title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/work_detail.css">
</head>

<header>
    <nav class="nav">
        <ul>
            <img src="../images/logo.png" alt=""><br>
            <li><a href="../users/dashboard.php">Dashboard</a></li><br>
            <li><a href="../users/profile.php">Profile</a></li><br>
            <li><a href="../users/server.php">Server</a></li><br>
            <li><a href="../users/vip.php">VIP</a></li><br>
            <li><a href="../users/shop.php">Shop</a></li><br>
            <li><a href="../users/support.php">Vytvořit Ticketu</a></li><br>
            <li><a href="../users/tickets.php">Moje Tickety</a></li><br>

            <?php if (in_array($role_id, [2, 4, 5])): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Admin</a>
                    <div class="dropdown-content">
                        <a href="../admin/dashboard.php">Dashboard</a>
                        <a href="../admin/players.php">Hráči</a>
                        <a href="../admin/punishments.php">Tresty</a>
                        <a href="../admin/punish.php">Zápis trestu</a>
                        <a href="../admin/records.php">Záznami trestů</a>
                        <a href="../admin/tickets.php">Tickety</a>
                    </div>
                </li>
            <?php endif; ?>

            <?php if (in_array($role_id, [3, 4, 5])): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Developer</a>
                    <div class="dropdown-content">
                        <a href="../development/dashboard.php">Dashboard</a>
                        <a href="../development/plugins.php">Pluginy</a>
                        <a href="../development/work.php">To-Do</a>
                        <a href="../development/tickets.php">Tickety</a>
                        <a href="../development/stats.php">Statistiky pluginů</a>
                    </div>
                </li>
            <?php endif; ?>

            <?php if (in_array($role_id, [4, 5])): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Managment</a>
                    <div class="dropdown-content">
                        <a href="../managment/dashboard.php">Dashboard</a>
                        <a href="../managment/finance.php">Pluginy</a>
                        <a href="../managment/players.php">Hráči</a>
                        <a href="../managment/punishments.php">Tresty</a>
                        <a href="../managment/punish.php">Zápis trestu</a>
                        <a href="../managment/records.php">Záznami trestů</a>
                        <a href="../managment/tickets.php">Tickety</a>
                    </div>
                </li>
            <?php endif; ?>
            <li><a href="../php/logout.php"><img src="../images/log_out.png" alt="Log-out" width="0.75%" height="0.75%">Odhlásit se</a></li><br>
        </ul>
    </nav>
</header>

<body>
    <div class="container">
        <h2><strong>Detail Úkolu: <?= htmlspecialchars($work['what']) ?></strong></h2>
        <p><strong>Kdo:</strong> <?= htmlspecialchars($work['who']) ?></p>
        <p><strong>O co se jedná:</strong> <?= htmlspecialchars($work['what']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($work['status']) ?></p>
        <p><strong>Poslední aktualizace:</strong> <?= date('d.m.Y', strtotime($work['last_update'])) ?></p>
    </div>
    <!-- Chat pro úkol -->
    <h3><strong>Chat</strong></h3>
    <div class="chat-container">
        <?php if (empty($chat_messages)): ?>
            <p>Žádné zprávy v chatu.</p>
        <?php else: ?>
            <?php foreach ($chat_messages as $message): ?>
                <div class="chat-message">
                    <p><strong><?= htmlspecialchars($message['username']) ?>:</strong> <?= htmlspecialchars($message['message']) ?></p>
                    <p><small><?= date('d.m.Y H:i', strtotime($message['created_at'])) ?></small></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="work_detail.php?id=<?= $work_id ?>" method="POST">
            <textarea name="message" placeholder="Napište zprávu..." required></textarea><br>
            <button type="submit" style="margin-left: 0%">Odeslat zprávu</button>
        </form>
    </div>
    <div class="buttons" style="text-align: center;">

        <!-- Tlačítko na návrat -->
        <form action="../development/work.php">
            <button type="submit">Zpět na seznam úkolů</button>
        </form>
    </div>
</body>

<footer>
    <!-- Odkazy na sociální sítě -->
    <a href="https://www.instagram.com/fida_knap/" target="_blank" style="padding: 10px;"><img src="../images/instagram.png" alt="instagram" style="width: 1.5%; height: 1.5%;" class="IG"></a>
    <a href="https://discord.gg/Msv22AUx3m" target="_blank" style="padding: 10px;"><img src="../images/discord.png" alt="discord" style="width: 1.75%; height: 2.25%;" class="DC"></a>
    <p>Created by Filip Knap with lot of ☕ and ❤️</p>
    <p>© 2025 Knap Filip</p>
</footer>

</html>