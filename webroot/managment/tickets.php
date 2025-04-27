<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require '../php/db.php';

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

// Načtení všech ticketů s otevřeným stavem
$stmt = $pdo->prepare("
    SELECT t.*, u.username 
    FROM tickets t 
    LEFT JOIN dc_users u ON t.discord_id = u.discord_id 
    WHERE t.status = 'open'
    ORDER BY t.created_at DESC
");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Zpracování požadavku pro změnu stavu claim (tlačítko Claim)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_ticket'])) {
    $ticket_id = $_POST['ticket_id'];

    // Získání jména uživatele podle discord_id
    $stmt = $pdo->prepare("SELECT username FROM dc_users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $claimed_by = $user ? $user['username'] : 'Neznámý';

    // Aktualizace ticketu
    $stmt = $pdo->prepare("UPDATE tickets SET claimed = 'ano', who = ? WHERE id = ?");
    $stmt->execute([$claimed_by, $ticket_id]);

    // Přesměrování zpět na stránku, aby se aktualizovaly změny
    header("Location: tickets.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Management Tickety</title>
    <link rel="icon" href="../images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/tickets.css">
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

            <!-- Admin dropdown, zobrazuje se pouze pro role 2, 4, a 5 -->
            <?php if (in_array($role_id, [2, 4, 5])): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Admin</a>
                    <div class="dropdown-content">
                        <a href="../admin/dashboard.php">Dashboard</a>
                        <a href="../admin/players.php">Hráči</a>
                        <a href="../admin/punishments.php">Tresty</a>
                        <a href="../admin/punish.php">Zápis trestu</a>
                        <a href="../admin/records.php">Záznamy trestů</a>
                        <a href="../admin/tickets.php">Tickety</a>
                    </div>
                </li>
            <?php endif; ?>

            <!-- Developer dropdown, zobrazuje se pouze pro role 3, 4, a 5 -->
            <?php if (in_array($role_id, [3, 4, 5])): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Developer</a>
                    <div class="dropdown-content">
                        <a href="../development/dashboard.php">Dashboard</a>
                        <a href="../development/plugins.php">Pluginy</a>
                        <a href="../development/work.php">To-Do</a>
                        <a href="../development/tickets.php">Tickety</a>
                        <a href="../development/stats.php">Statistiky pluginů</a>
                        <a href="#">PLAN</a>
                    </div>
                </li>
            <?php endif; ?>

            <!-- Managment dropdown, zobrazuje se pouze pro role 4 a 5 -->
            <?php if (in_array($role_id, [4, 5])): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Managment</a>
                    <div class="dropdown-content">
                        <a href="../managment/dashboard.php">Dashboard</a>
                        <a href="../managment/finance.php">Finance</a>
                        <a href="../managment/players.php">Hráči</a>
                        <a href="../managment/punishments.php">Tresty</a>
                        <a href="../managment/punish.php">Zápis trestu</a>
                        <a href="../managment/records.php">Záznamy trestů</a>
                        <a href="../managment/tickets.php" class="active">Tickety</a>
                        <a href="../managment/team_details.php">Team</a>
                        <a href="../managment/blacklist.php">Blacklist</a>
                        <a href="#">PLAN</a>
                    </div>
                </li>
            <?php endif; ?><br><br>
            <li><a href="../php/logout.php"><img src="../images/log_out.png" alt="Log-out" width="0.75%" height="0.75%">Odhlásit se</a></li><br>
        </ul>
    </nav>
</header>

<body>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Management Tickety</h1>

        <?php if (count($tickets) > 0): ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="mb-4 p-4 border rounded bg-gray-50 shadow-sm hover:shadow transition">
                    <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($ticket['subject']) ?></h2>
                    <p class="text-gray-600"><strong>Typ:</strong> <?= ucfirst(htmlspecialchars($ticket['type'])) ?></p>
                    <p class="text-gray-600"><strong>Autor:</strong> <?= htmlspecialchars($ticket['username'] ?? 'Neznámý') ?></p>
                    <p class="text-gray-600"><strong>Vytvořeno:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>

                    <!-- Nápis "Řeší někdo tento ticket?" -->
                    <p class="text-gray-600">
                        <strong>Řeší někdo tento ticket?</strong>
                        <span class="text-<?= $ticket['claimed'] === 'ano' ? 'red' : 'green' ?>-600"><?= $ticket['claimed'] === 'ano' ? 'Ano' : 'Ne' ?></span>
                    </p>

                    <!-- Tlačítko Claim -->
                    <?php if ($ticket['claimed'] === 'ne'): ?>
                        <form method="POST" action="tickets.php" class="mt-3">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <button type="submit" name="claim_ticket" class="inline-block mt-3 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Claim
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Nápis "Kdo řeší ticket?" -->
                    <p class="text-gray-600">
                        <strong>Kdo řeší ticket:</strong> <?= htmlspecialchars($ticket['who'] ?? 'Nikdo') ?>
                    </p>

                    <a href="../../php/ticket_detail.php?id=<?= $ticket['id'] ?>" class="inline-block mt-3 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Zobrazit
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500">Zatím tu není žádný ticket.</p>
        <?php endif; ?>
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