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

// Načti pluginy
$stmt = $pdo->query("SELECT * FROM plugins");
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
    <h2><strong>Seznam pluginů</strong></h2>
    <table id="pluginTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Název</th>
                <th>Verze</th>
                <th>Typ</th>
                <th>Odkaz</th>
                <th>Status</th>
                <th>Aktivní</th>
                <th>Poslední aktualizace</th>
                <th>Detail</th>
                <th>Mazání</th> <!-- Sloupec pro tlačítko smazání -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plugins as $plugin): ?>
                <tr data-id="<?= $plugin['id'] ?>">
                    <td><?= $plugin['id'] ?></td>
                    <td><?= htmlspecialchars($plugin['plugin_name']) ?></td>
                    <td><input type="text" class="edit-version" value="<?= htmlspecialchars($plugin['plugin_version']) ?>"></td>
                    <td><?= htmlspecialchars($plugin['plugin_type']) ?></td>
                    <td><a href="<?= htmlspecialchars($plugin['plugin_link']) ?>" target="_blank">Otevřít</a></td>
                    <td>
                        <select class="edit-status">
                            <?php
                            $options = ['Ve vývoji', 'Testování', 'Plně funkční'];
                            foreach ($options as $opt) {
                                $selected = $opt === $plugin['plugin_status'] ? 'selected' : '';
                                echo "<option value=\"$opt\" $selected>$opt</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <select class="edit-active">
                            <option value="Ano" <?= $plugin['plugin_active'] === 'Ano' ? 'selected' : '' ?>>Ano</option>
                            <option value="Ne" <?= $plugin['plugin_active'] === 'Ne' ? 'selected' : '' ?>>Ne</option>
                        </select>
                    </td>
                    <td><input type="date" class="edit-date" value="<?= date('Y-m-d', strtotime($plugin['plugin_last_update'])) ?>"></td>
                    <td><button onclick="location.href='../php/plugin_detail.php?id=<?= $plugin['id'] ?>'">Detail</button></td>
                    <td><button class="delete-btn" data-id="<?= $plugin['id'] ?>">Smazat</button></td> <!-- Tlačítko pro smazání -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <h2><strong>Přidat nový plugin</strong></h2>

    <form action="../php/insert_plugin.php" method="post">
        <label for="plugin_name">Název:</label><br>
        <input type="text" name="plugin_name" id="plugin_name" required><br>

        <label for="plugin_version">Verze:</label><br>
        <input type="text" name="plugin_version" id="plugin_version" required><br>

        <label for="plugin_type">Typ:</label><br>
        <input type="text" name="plugin_type" id="plugin_type" required><br>

        <label for="plugin_link">Odkaz:</label><br>
        <input type="url" name="plugin_link" id="plugin_link" required><br>

        <label for="plugin_active">Aktivní:</label><br>
        <select name="plugin_active" id="plugin_active" required><br>
            <option value="1">Ano</option>
            <option value="0">Ne</option>
        </select><br>

        <label for="plugin_last_update">Poslední aktualizace:</label><br>
        <input type="date" name="plugin_last_update" id="plugin_last_update" required><br><br>

        <button type="submit">Přidat plugin</button>
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        function reloadRow(pluginId, rowElement) {
            fetch(`../php/get_plugin.php?id=${pluginId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const plugin = data.plugin;

                        // Přepíšeme buňky v řádku
                        const cells = rowElement.children;
                        cells[1].textContent = plugin.plugin_name;
                        cells[2].querySelector('input').value = plugin.plugin_version;
                        cells[3].textContent = plugin.plugin_type;
                        cells[4].querySelector('a').href = plugin.plugin_link;
                        cells[5].querySelector('select').value = plugin.plugin_status;
                        cells[6].querySelectorAll('select')[0].value = plugin.plugin_active;
                        cells[7].querySelector('input').value = plugin.plugin_last_update;
                        // Detail button necháme beze změny (id se nemění)
                    } else {
                        console.error('Chyba při načítání řádku:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Chyba při načítání:', error);
                });
        }

        document.querySelectorAll('#pluginTable input, #pluginTable select').forEach(element => {
            element.addEventListener('change', (e) => {
                const row = e.target.closest('tr');
                const pluginId = row.getAttribute('data-id');
                let field, value;

                if (e.target.classList.contains('edit-version')) {
                    field = 'plugin_version';
                    value = e.target.value;
                } else if (e.target.classList.contains('edit-status')) {
                    field = 'plugin_status';
                    value = e.target.value;
                } else if (e.target.classList.contains('edit-active')) {
                    field = 'plugin_active';
                    value = e.target.value;
                } else if (e.target.classList.contains('edit-date')) {
                    field = 'plugin_last_update';
                    value = e.target.value;
                } else {
                    return; // nepodporované pole
                }

                fetch('../php/update_plugin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: pluginId,
                            field: field,
                            value: value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Změna uložena.');
                            reloadRow(pluginId, row);
                        } else {
                            alert('Chyba při ukládání: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Chyba:', error);
                        alert('Nepodařilo se odeslat změnu.');
                    });
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const pluginId = this.getAttribute('data-id');

                if (!confirm('Opravdu chceš smazat tento plugin?')) return;

                try {
                    const response = await fetch('../php/delete_plugin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            plugin_id: pluginId
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Úspěšně smazáno
                        this.closest('tr').remove();
                        alert(data.message);
                    } else {
                        // Chyba při mazání
                        alert('Chyba: ' + (data.message || 'Nepodařilo se smazat plugin.'));
                    }
                } catch (error) {
                    console.error('Chyba při komunikaci se serverem:', error);
                    alert('Chyba při komunikaci se serverem.');
                }
            });
        });
    });
</script>