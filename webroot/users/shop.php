<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SECURE', true);  // Aktivace režimu SECURE pro přístup k db.php

session_start();

// Ověření přihlášení uživatele a načtení dat z databáze podle discord_id (viz předchozí kód)
if (isset($_SESSION['user']['discord_id'])) {
    $discord_id = $_SESSION['user']['discord_id'];

    // Zahrnutí souboru db.php s připojením k databázi
    require '../php/db.php';

    // Získání údajů o uživatelském účtu
    $stmt = $pdo->prepare("SELECT username, discriminator, email, role_id, created_at FROM dc_users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: login.php");
        exit;
    }

    $username = $user['username'];
    $discriminator = $user['discriminator'];
    $email = $user['email'];
    $role_id = $user['role_id'];
    $created_at = $user['created_at'];
} else {
    header("Location: login.php");
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/shop.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <title>BlockZone</title>
</head>

<header>
    <nav class="nav">
        <ul>
            <img src="../images/logo.png" alt=""><br>
            <li><a href="dashboard.php">Dashboard</a></li><br>
            <li><a href="profile.php">Profile</a></li><br>
            <li><a href="server.php">Server</a></li><br>
            <li><a href="vip.php">VIP</a></li><br>
            <li><a href="shop.php" class="active">Shop</a></li><br>
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

<body>

    <h1 class="shop-title">🛍️ E-shop</h1>

    <div class="shop-filter">
        <button onclick="filterItems('all')">Vše</button>
        <button onclick="filterItems('vip')">VIP</button>
        <button onclick="filterItems('unban')">Unban</button>
        <button onclick="filterItems('support')">Support</button>
    </div>

    <div class="shop-container">
        <div class="shop-item vip">
            <div class="item-title"><i class="fas fa-crown"></i> VIP Silver</div>
            <img src="vip_silver.png" alt="VIP Silver" class="item-image">
            <div class="item-description">Získáte přístup k <b>/fly</b>, barevné jméno, prefix a další výhody.</div>
            <div class="item-price">149 Kč</div>
            <a href="#" class="buy-button">Koupit</a>
        </div>

        <div class="shop-item vip">
            <div class="item-title"><i class="fas fa-gem"></i> VIP Gold</div>
            <img src="vip_gold.png" alt="VIP Gold" class="item-image">
            <div class="item-description">Navíc obsahuje <b>/nick</b>, větší kit a vyšší limit /sethome.</div>
            <div class="item-price">249 Kč</div>
            <a href="#" class="buy-button">Koupit</a>
        </div>

        <div class="shop-item vip">
            <div class="item-title"><i class="fas fa-star"></i> VIP Platinum</div>
            <img src="vip_platinum.png" alt="VIP Platinum" class="item-image">
            <div class="item-description">Obsahuje <b>/repair</b>, efekty a tajnou zónu.</div>
            <div class="item-price">399 Kč</div>
            <a href="#" class="buy-button">Koupit</a>
        </div>

        <div class="shop-item unban">
            <div class="item-title"><i class="fas fa-unlock"></i> Unban Balíček</div>
            <img src="unban.png" alt="Unban" class="item-image">
            <div class="item-description">Obnovení přístupu na server po banu.</div>
            <div class="item-price">199 Kč</div>
            <a href="#" class="buy-button">Koupit</a>
        </div>

        <div class="shop-item support">
            <div class="item-title"><i class="fas fa-heart"></i> Support Balíček</div>
            <img src="support.png" alt="Support" class="item-image">
            <div class="item-description">Pomoz serveru a získej odměnu a prefix <b>Supporter</b>.</div>
            <div class="item-price">99 Kč</div>
            <a href="#" class="buy-button">Koupit</a>
        </div>
        <div class="shop-item example">
            <div class="item-title"><i class="fas fa-gift"></i> Example</div>
            <img src="example.png" alt="Example" class="item-image">
            <div class="item-description">Popis balíčku Example.</div>
            <div class="item-price">199 Kč</div>
            <a href="#" class="buy-button">Koupit</a>
        </div>

    </div>

</body>

<footer>
    <!--Odkazy na sociální sítě-->
    <a href="https://www.instagram.com/fida_knap/" target="_blank" style="padding: 10px;">
        <img src="../images/instagram.png" alt="instagram" style="width: 1.5%; height: 1.5%;" class="IG">
    </a>
    <a href="https://discord.gg/Msv22AUx3m" target="_blank" style="padding: 10px;">
        <img src="../images/discord.png" alt="discord" style="width: 1.75%; height: 2.25%;" class="DC">
    </a>
    <p>Created by Filip Knap with lot of ☕ and ❤️</p>
    <p>© 2025 Knap Filip</p>
</footer>

</html>

<script src="../js/shop.js"></script>