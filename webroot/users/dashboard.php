<?php
define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ověření přihlášení uživatele a načtení dat z databáze podle discord_id
if (isset($_SESSION['user']['discord_id'])) {
    $discord_id = $_SESSION['user']['discord_id'];

    // Zahrnutí souboru db.php s připojením k databázi (předpokládá se, že vrací $pdo)
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

// ✅ Počet všech uživatelů (přes PDO)
$stmt = $pdo->query("SELECT COUNT(*) AS count FROM dc_users");
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$role_text = $role_names[$role_id] ?? 'Neznámá role';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="../images/logo.png">
    <link rel="stylesheet" href="../css/dashboard_user.css">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <title>BlockZone - Dashboard</title>
</head>
<header>
    <nav class="nav">
        <ul>
            <img src="../images/logo.png" alt=""><br>
            <li><a href="dashboard.php" class="active">Dashboard</a></li><br>
            <li><a href="profile.php">Profile</a></li><br>
            <li><a href="server.php">Server</a></li><br>
            <li><a href="vip.php">VIP</a></li><br>
            <li><a href="shop.php">Shop</a></li><br>
            <li><a href="support.php">Vytvořit Ticketu</a></li><br>
            <li><a href="tickets.php">Moje Tickety</a></li><br>

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
                        <a href="../managment/finance.php">Pluginy</a>
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
    <h1>Vítejte v dashboardu <?php echo htmlspecialchars($username); ?></h1>
    <div class="body">
        <div class="user-info">
            <h2>Uživatelské informace</h2>
            <p><strong>Uživatelské jméno:</strong> <?php echo htmlspecialchars($username); ?>#<?php echo htmlspecialchars($discriminator); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($role_text); ?></p>
            <p><strong>Discord ID:</strong> <?php echo htmlspecialchars($discord_id); ?></p>
            <button><a href="profile.php">Váš profil</a></button>
        </div>
        <div class="stats">
            <h2>Statistiky</h2>
            <p>Statistiky a další informace o serveru.</p>
            <p><strong>Aktivní server:</strong> BlockZone</p>
            <p><strong>IP:</strong> mc.blockzone.cz</p>
            <p><strong>Počet registrovaných hráčů:</strong> <?php echo $count; ?></p>

        </div>
        <div class="vip-info">
            <h2>VIP informace</h2>
            <p>Informace o VIP výhodách a možnostech.</p>
            <p><strong>Aktivní VIP:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Platnost do:</strong> <?php echo date('Y-m-d'); ?></p>
            <button><a href="vip.php">VIP výhody</a></button> <button><a href="tebex.com">E-Shop</a></button>
        </div>
        <div class="punishment">
            <h2>Tresty</h2>
            <p>Seznam trestů a varování.</p>
            <p><strong>Aktivní trest:</strong> <?php echo htmlspecialchars($email); ?></p>
            <button><a href="profile.php">Seznam trestů</a></button>
        </div>
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