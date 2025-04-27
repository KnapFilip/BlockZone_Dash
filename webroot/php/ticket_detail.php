<?php
define('SECURE', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../php/db.php';

if (!isset($_GET['id'])) {
    die("Chybí ID ticketu.");
}

$ticket_id = intval($_GET['id']);

if (!isset($_SESSION['user']['discord_id']) || !isset($_SESSION['user']['role_id'])) {
    die("Nejste přihlášen.");
}

$discord_id = $_SESSION['user']['discord_id'];
$role_id = $_SESSION['user']['role_id'];

// Získání aktuálního uživatele z DB
$stmtUser = $pdo->prepare("SELECT id, username FROM dc_users WHERE discord_id = ?");
$stmtUser->execute([$discord_id]);
$currentUser = $stmtUser->fetch();

if (!$currentUser) {
    die("Uživatel nebyl nalezen.");
}

$user_id = $currentUser['id'];

// Načtení detailu ticketu
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket nenalezen.");
}

// Práva ke zobrazení (kontrola přes discord_id)
if (
    $ticket['discord_id'] != $discord_id &&
    $role_id != 5 &&
    !(in_array($ticket['type'], ['dev']) && in_array($role_id, [3, 4])) &&
    !(in_array($ticket['type'], ['admin']) && in_array($role_id, [2, 4])) &&
    !(in_array($ticket['type'], ['tebex', 'unban']) && $role_id == 4)
) {
    die("Nemáte oprávnění tento ticket zobrazit.");
}

// Načtení odpovědí (přidání role_id pro každou odpověď)
$resp_stmt = $pdo->prepare("
    SELECT r.*, u.username, u.role_id 
    FROM ticket_responses r 
    JOIN dc_users u ON r.discord_id = u.discord_id 
    WHERE r.ticket_id = ? 
    ORDER BY r.created_at ASC
");
$resp_stmt->execute([$ticket_id]);
$responses = $resp_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/dashboard_user.css">
    <link rel="stylesheet" href="../css/ticket_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <li><a href="../users/tickets.php" class="active">Moje Tickety</a></li><br>

            <!-- Admin dropdown, zobrazuje se pouze pro role 2, 4, a 5 -->
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
                    </div>
                </li>
            <?php endif; ?>

            <!-- Managment dropdown, zobrazuje se pouze pro role 4 a 5 -->
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
                        <a href="../managment/team_details.php">Team</a>
                    </div>
                </li>
            <?php endif; ?>
            <li><a href="../php/logout.php"><img src="../images/log_out.png" alt="Log-out" width="0.75%" height="0.75%">Odhlásit se</a></li><br>
        </ul>
    </nav>
</header>

<body class="p-6 bg-gray-100">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Ticket: <?= htmlspecialchars($ticket['subject']) ?></h1>
        <p><strong>Typ:</strong> <?= htmlspecialchars(ucfirst($ticket['type'])) ?></p>
        <p><strong>Zpráva:</strong> <?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
        <hr class="my-4">
        <h2 class="text-xl font-semibold mb-2">Odpovědi:</h2>

        <?php if (count($responses) === 0): ?>
            <p>Žádné odpovědi.</p>
        <?php endif; ?>

        <?php foreach ($responses as $response): ?>
            <div class="mb-3 p-3 border rounded bg-gray-50">
                <p>
                    <strong>
                        <?= htmlspecialchars($response['username']) ?>

                        <!-- Zobrazení role uživatele -->
                        <?php
                        $role_icon = '';
                        $role_name = '';
                        $role_class = ''; // Přidáme třídu pro barvu
                        switch ($response['role_id']) {
                            case 1:
                                $role_name = 'Uživatel';
                                $role_icon = '</i>';
                                $role_class = 'bg-blue-500 text-white'; // Silnější barva pro uživatele
                                break;
                            case 2:
                                $role_name = 'Admin';
                                $role_icon = '<img src="../images/moderator.png" alt="" style="width: 1.5%; height: 1.5%;" align="center">'; // Ikona pro web admina
                                $role_class = 'bg-green-600 text-white'; // Silnější barva pro moderátora
                                break;
                            case 3:
                                $role_name = 'Developer';
                                $role_icon = '<img src="../images/developer.png" alt="" style="width: 1.5%; height: 1.5%;" align="center">'; // Ikona pro web admina
                                $role_class = 'bg-red-600 text-white'; // Silnější barva pro admina
                                break;
                            case 4:
                                $role_name = 'Management';
                                $role_icon = '<img src="../images/owner.png" alt="" style="width: 1.5%; height: 1.5%;" align="center">'; // Ikona pro web admina
                                $role_class = 'bg-purple-600 text-white'; // Silnější barva pro vývojáře
                                break;
                            case 5:
                                $role_icon = '<img src="../images/staff.png" alt="" style="width: 1.5%; height: 1.5%;" align="center">'; // Ikona pro web admina
                                $role_name = 'Web Admin' ;
                                $role_class = 'bg-yellow-600 text-red'; // Silnější barva pro manažera
                                break;
                            default:
                                $role_name = 'Neznámá';
                                $role_icon = '<i class="fas fa-question-circle text-gray-700"></i>';
                                $role_class = 'bg-gray-500 text-white'; // Silnější barva pro neznámou roli
                        }
                        ?>

                        <!-- Zobrazení ikony, názvu a barvy role -->
                        <span class="inline-block <?= $role_class ?> px-2 py-1 rounded text-xs">
                            <?= $role_icon ?> <span class="text-sm">
                        </span>
                    </strong>
                </p>
                <p><?= nl2br(htmlspecialchars($response['message'])) ?></p>
                <small><?= $response['created_at'] ?></small>
            </div>
        <?php endforeach; ?>

        <form action="../php/add_response.php" method="POST">
            <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
            <textarea name="message" required rows="10" placeholder="Napiš odpověď..."></textarea><br>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Odeslat</button>
        </form>
        <form action="/php/close_ticket.php" method="POST">
            <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
            <button type="submit">Uzavřít ticket</button>
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