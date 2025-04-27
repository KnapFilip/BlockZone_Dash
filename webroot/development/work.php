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

// Načti pluginy (To-Do list)
$stmt = $pdo->query("SELECT * FROM work");
$plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Dev Tickety</title>
    <link rel="icon" href="../images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/plugins.css">
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
                        <a href="../development/tickets.php" class="active">Tickety</a>
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
                        <a href="../managment/team_detail.php">Tickety</a>
                    </div>
                </li>
            <?php endif; ?>
            <li><a href="../php/logout.php"><img src="../images/log_out.png" alt="Log-out" width="0.75%" height="0.75%">Odhlásit se</a></li><br>
        </ul>
    </nav>
</header>

<body>
    <h2><strong>To-Do list</strong></h2>
    <table id="WorkTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kdo</th>
                <th>O co se jedná</th>
                <th>Status</th>
                <th>Poslední aktualizace</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($plugins)): ?>
                <tr>
                    <td colspan="6">Žádné úkoly v seznamu.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($plugins as $plugin): ?>
                    <tr>
                        <td><?= htmlspecialchars($plugin['id']) ?></td>
                        <td>
                            <?= htmlspecialchars($plugin['who']) ?>
                            <?php if (empty($plugin['who'])): ?>
                                <form method="POST" action="../php/claim_task.php">
                                    <input type="hidden" name="task_id" value="<?= htmlspecialchars($plugin['id']) ?>">
                                    <button type="submit" name="claim" class="claim-btn">Claim</button>
                                </form>

                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($plugin['what']) ?></td>
                        <td>
                            <!-- Výběr statusu s AJAXem pro okamžitou změnu -->
                            <select name="status" class="status-select" data-task-id="<?= htmlspecialchars($plugin['id']) ?>">
                                <option value="Práce nezačata" <?= $plugin['status'] == 'Práce nezačata' ? 'selected' : '' ?>>Práce nezačata</option>
                                <option value="Probíhá" <?= $plugin['status'] == 'Probíhá' ? 'selected' : '' ?>>Probíhá</option>
                                <option value="Dokončeno" <?= $plugin['status'] == 'Dokončeno' ? 'selected' : '' ?>>Dokončeno</option>
                            </select>
                        </td>
                        <td><?= date('d.m.Y', strtotime($plugin['last_update'])) ?></td>
                        <td><button><a href="../php/work_detail.php?id=<?= htmlspecialchars($plugin['id']) ?>">Detail</a></button></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <h2>Vytvořit nový úkol</h2>
    <form action="../php/create_work.php" method="POST">
        <label for="what">Název úkolu:</label><br>
        <input type="text" id="what" name="what" required><br><br>

        <label for="">Popis úkolu:</label><br>
        <textarea id="" name=""></textarea><br><br>

        <button type="submit" name="submit_task">Vytvořit úkol</button>
    </form>
</body>

<footer>
    <!-- Odkazy na sociální sítě -->
    <a href="https://www.instagram.com/fida_knap/" target="_blank" style="padding: 10px;"><img src="../images/instagram.png" alt="instagram" style="width: 1.5%; height: 1.5%;" class="IG"></a>
    <a href="https://discord.gg/Msv22AUx3m" target="_blank" style="padding: 10px;"><img src="../images/discord.png" alt="discord" style="width: 1.75%; height: 2.25%;" class="DC"></a>
    <p>Created by Filip Knap with lot of ☕ and ❤️</p>
    <p>© 2025 Knap Filip</p>
</footer>

</html>
<script src="../js/plugins.js"></script>
<script>
    document.querySelectorAll('.status-select').forEach(function(select) {
        select.addEventListener('change', function() {
            var taskId = this.getAttribute('data-task-id');
            var newStatus = this.value;

            // Odeslání AJAX požadavku
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../php/update_work.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log('Status úkolu byl úspěšně aktualizován.');
                } else {
                    console.log('Chyba při aktualizaci statusu.');
                }
            };
            xhr.send('task_id=' + taskId + '&status=' + encodeURIComponent(newStatus));
        });
    });
</script>