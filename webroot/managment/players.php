<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ověření přihlášení uživatele a načtení dat z databáze podle discord_id
if (isset($_SESSION['user']['discord_id'])) {
    $discord_id = $_SESSION['user']['discord_id'];

    require '../php/db.php';

    $stmt = $pdo->prepare("SELECT username, discriminator, email, role_id FROM dc_users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../login.php");
        exit;
    }

    $username = $user['username'];
    $discriminator = $user['discriminator'];
    $email = $user['email'];
    $role_id = $user['role_id'];
} else {
    header("Location: ../login.php");
    exit;
}

$role_names = [
    1 => 'Uživatel',
    2 => 'Admin',
    3 => 'Developer',
    4 => 'Managment',
    5 => 'Web Admin'
];

$role_text = $role_names[$role_id] ?? 'Neznámá role';

// Načtení hráčů
$stmt = $pdo->query("SELECT * FROM players"); // tabulka players
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Hráči</title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/players.css">
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
                        <a href="../managment/players.php" class="active">Hráči</a>
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
    <div class="table-container">
        <h2>Seznam hráčů</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Jméno hráče</th>
                    <th>Discord ID</th>
                    <th>Discord jméno</th>
                    <th>Online</th>
                    <th>UUID</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($players as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['player_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['discord_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['discord_name']); ?></td>
                        <td>
                            <span class="<?php echo $row['online'] ? 'online' : 'offline'; ?>">
                                <?php echo $row['online'] ? 'Ano' : 'Ne'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['uuid']); ?></td>
                        <td>
                            <a class="detail-btn" href="../php/player_profile.php?id=<?php echo urlencode($row['id']); ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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