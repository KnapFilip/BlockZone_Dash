<?php
define('SECURE', true);
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Kontrola přihlášení
if (!isset($_SESSION['user']['discord_id'])) {
    header("Location: ../login.php");
    exit;
}
$discord_id = $_SESSION['user']['discord_id'];

require '../php/db.php';

// 2) Načtení uživatele, včetně ID (primární klíč v dc_users se jmenuje id)
$stmt = $pdo->prepare("
    SELECT id AS user_id, username, discriminator, email, role_id
    FROM dc_users
    WHERE discord_id = ?
");
$stmt->execute([$discord_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) {
    header("Location: ../login.php");
    exit;
}
$user_id     = $u['user_id'];
$username    = $u['username'];
$discriminator = $u['discriminator'];
$email        = $u['email'];
$role_id      = $u['role_id'];

// 3) Role pro navigaci
$role_names = [
    1 => 'Uživatel',
    2 => 'Admin',
    3 => 'Developer',
    4 => 'Managment',
    5 => 'Web Admin'
];
$role_text = $role_names[$role_id] ?? 'Neznámá role';

// 4) Načtení transakcí a výpočet zůstatku
$balance = 0;
$stmt = $pdo->prepare("SELECT * FROM finance WHERE user_id = ? ORDER BY date DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($transactions as $t) {
    $balance += ($t['type'] === 'Příjem' ? $t['amount'] : -$t['amount']);
}

// 5) Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description']);
    $type        = $_POST['type'];
    $amount      = $_POST['amount'];

    $ins = $pdo->prepare("
        INSERT INTO finance (description, amount, type, user_id)
        VALUES (?, ?, ?, ?)
    ");
    $ins->execute([$description, $amount, $type, $user_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Finance</title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/finance.css">
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
                        <a href="../managment/finance.php" class="active">Finance</a>
                        <a href="../managment/players.php">Hráči</a>
                        <a href="../managment/punishments.php">Tresty</a>
                        <a href="../managment/punish.php">Zápis trestu</a>
                        <a href="../managment/records.php">Záznamy trestů</a>
                        <a href="../managment/tickets.php">Tickety</a>
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
    <main>
        <h1>Zůstatek: <?php echo number_format($balance, 2, ',', ' '); ?> Kč</h1>
        <table>
            <thead>
                <tr>
                    <th>Za co</th>
                    <th>Typ</th>
                    <th>Částka</th>
                    <th>Datum</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($transactions): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['description']); ?></td>
                            <td><?php echo $t['type']; ?></td>
                            <td><?php echo number_format($t['amount'], 2, ',', ' '); ?> Kč</td>
                            <td><?php echo $t['date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Žádné transakce nejsou k dispozici.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <td><input type="text" name="description" required placeholder="Za co"></td>
                        <td>
                            <select name="type" required>
                                <option value="Příjem">Příjem</option>
                                <option value="Výdaj">Výdaj</option>
                            </select>
                        </td>
                        <br>
                        <td><input type="number" step="0.01" name="amount" required placeholder="Kolik"></td>
                        <td><button type="submit">Přidat</button></td>
                    </form>
                </tr>
            </tfoot>
        </table>
    </main>

</body>
<footer>
    <!-- Odkazy na sociální sítě -->
    <a href="https://www.instagram.com/fida_knap/" target="_blank" style="padding: 10px;"><img src="../images/instagram.png" alt="instagram" style="width: 1.5%; height: 1.5%;" class="IG"></a>
    <a href="https://discord.gg/Msv22AUx3m" target="_blank" style="padding: 10px;"><img src="../images/discord.png" alt="discord" style="width: 1.75%; height: 2.25%;" class="DC"></a>
    <p>Created by Filip Knap with lot of ☕ and ❤️</p>
    <p>© 2025 Knap Filip</p>
</footer>

</html>