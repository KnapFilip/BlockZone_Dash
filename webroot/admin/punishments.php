<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ověření přihlášení uživatele a načtení dat z databáze podle discord_id (viz předchozí kód)
if (isset($_SESSION['user']['discord_id'])) {
    $discord_id = $_SESSION['user']['discord_id'];

    // Zahrnutí souboru db.php s připojením k databázi
    require '../php/db.php';

    // Získání údajů o uživatelském účtu
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

// ✅ Načtení všech záznamů z tabulky punishments
$stmt = $pdo->query("SELECT * FROM punishments_list");
$punishments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>BlockzoneSeznam trestů</title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/punishment.css">
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
                        <a href="dashboard.php">Dashboard</a>
                        <a href="players.php">Hráči</a>
                        <a href="punishments.php" class="active">Tresty</a>
                        <a href="punish.php">Zápis trestu</a>
                        <a href="records.php">Záznami trestů</a>
                        <a href="tickets.php">Tickety</a>
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
                        <a href="../managment/team_detail.php">Tickety</a>
                    </div>
                </li>
            <?php endif; ?>
            <li><a href="../php/logout.php"><img src="../images/log_out.png" alt="Log-out" width="0.75%" height="0.75%">Odhlásit se</a></li><br>
        </ul>
    </nav>
</header>

<body>
    <div class="table-container">
        <h2>Seznam trestů</h2>
        <table>
            <thead>
                <tr>
                    <th>Název skutku</th>
                    <th>Druh trestu</th>
                    <th>Minimální délka</th>
                    <th>Maximální délka</th>
                    <th>Popis</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($punishments as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['punishment_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['punishment_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['minimum_punishment_length']); ?></td>
                        <td><?php echo htmlspecialchars($row['maximum_punishment_length']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['punishment_description'])); ?></td>
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