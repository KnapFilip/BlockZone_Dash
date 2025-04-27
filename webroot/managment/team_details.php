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

// Načtení všech členů týmu
$stmt = $pdo->query("SELECT * FROM team_members ORDER BY order_priority ASC, name ASC");
$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Předdefinované skupiny
$groups = [
    'Owner' => [],
    'Managment' => [],
    'Developers' => [],
    'Admins' => [],
    'Builders' => [],
    'Testers' => [],
    'Content Creators' => []
];

// Rozřazení do skupin s kontrolou duplicity
foreach ($team_members as $member) {
    if (isset($groups[$member['role']])) {
        // Kontrola, zda už tento člen není ve skupině
        $alreadyInGroup = false;
        foreach ($groups[$member['role']] as $existing_member) {
            if ($existing_member['id'] == $member['id']) {
                $alreadyInGroup = true;
                break;
            }
        }

        // Pokud člen není v seznamu, přidáme ho
        if (!$alreadyInGroup) {
            $groups[$member['role']][] = $member;
        }
    } else {
        // Debugging: Pokud role není definována, vypiš
        echo "Nedefinovaná role pro člena: " . htmlspecialchars($member['role']) . "<br>";
    }
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
    <title>Blockzone - Team</title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/team_details.css">
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
                        <a href="punishments.php">Tresty</a>
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
                        <a href="../managment/team_detail.php" class="active">Tickety</a>
                    </div>
                </li>
            <?php endif; ?>
            <li><a href="../php/logout.php"><img src="../images/log_out.png" alt="Log-out" width="0.75%" height="0.75%">Odhlásit se</a></li><br>
        </ul>
    </nav>
</header>

<body>
    <div class="table-container">
        <h1>Správa Týmu</h1>
        <table id="teamTable">
            <thead>
                <tr>
                    <th>Jméno</th>
                    <th>Role</th>
                    <th>Popis</th>
                    <th>Datum vstupu</th>
                    <th>Foto URL</th>
                    <th>Priorita</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($team_members as $member): ?>
                    <tr data-id="<?= $member['id'] ?>">
                        <td><input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>"></td>
                        <td>
                            <select name="role">
                                <option value="Owner" <?= $member['role'] == 'Owner' ? 'selected' : '' ?>>Owner</option>
                                <option value="Managment" <?= $member['role'] == 'Managment' ? 'selected' : '' ?>>Managment</option>
                                <option value="Developer" <?= $member['role'] == 'Developer' ? 'selected' : '' ?>>Developer</option>
                                <option value="Admin" <?= $member['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="Builder" <?= $member['role'] == 'Builder' ? 'selected' : '' ?>>Builder</option>
                                <option value="Tester" <?= $member['role'] == 'Tester' ? 'selected' : '' ?>>Tester</option>
                                <option value="Content Creators" <?= $member['role'] == 'Content Creators' ? 'selected' : '' ?>>Content Creators</option>
                            </select>
                        </td>
                        <td><textarea name="description"><?= htmlspecialchars($member['description']) ?></textarea></td>
                        <td><input type="date" name="date_of_entry" value="<?= htmlspecialchars($member['date_of_entry']) ?>"></td>
                        <td><input type="url" name="photo_url" value="<?= htmlspecialchars($member['photo_url']) ?>"></td>
                        <td><input type="number" name="order_priority" value="<?= htmlspecialchars($member['order_priority']) ?>" min="1"></td>
                        <td><a href="../php/delete_team_member.php?id=<?= $member['id'] ?>" onclick="return confirm('Opravdu chceš smazat tohoto člena?')">Smazat</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="status" class="status"></div>
    </div>

    <br>
    <div class="form-container">
        <h1>Přidat člena týmu</h1>

        <form method="POST" action="../php/add_team_member.php">
            <input type="text" name="name" placeholder="Jméno člena" required>
            <select name="role" required>
                <option value="Owner">Owner</option>
                <option value="Managment">Managment</option>
                <option value="Developers">Developer</option>
                <option value="Admins">Admin</option>
                <option value="Builders">Builder</option>
                <option value="Testers">Tester</option>
                <option value="Content Creators">Content Creator</option>
            </select>
            <input type="date" name="date_of_entry" required>
            <textarea name="description" placeholder="Popis člena" required></textarea>
            <input type="url" name="photo_url" placeholder="URL na profilový obrázek" required>
            <input type="number" name="order_priority" placeholder="Pořadí zobrazení" min="1" required>
            <button type="submit">Přidat člena</button>
        </form>
    </div>
</body>

<footer>
    <a href="https://www.instagram.com/fida_knap/" target="_blank" style="padding: 10px;"><img src="../images/instagram.png" alt="instagram" style="width: 1.5%; height: 1.5%;" class="IG"></a>
    <a href="https://discord.gg/Msv22AUx3m" target="_blank" style="padding: 10px;"><img src="../images/discord.png" alt="discord" style="width: 1.75%; height: 2.25%;" class="DC"></a>
    <p>Created by Filip Knap with lot of ☕ and ❤️</p>
    <p>© 2025 Knap Filip</p>
</footer>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.form-container form');

        form.addEventListener('submit', function(event) {
            let isValid = true;
            const inputs = form.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.border = '2px solid red';
                    isValid = false;
                } else {
                    input.style.border = '1px solid #ccc';
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Prosím, vyplň všechna pole.');
            }
        });

        // Bonus: efekt při focusu
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.border = '2px solid #00aaff';
            });
            input.addEventListener('blur', () => {
                input.style.border = input.value.trim() ? '1px solid #ccc' : '2px solid red';
            });
        });
    });
</script>
<script>
    document.querySelectorAll('#teamTable input, #teamTable select, #teamTable textarea').forEach(element => {
        element.addEventListener('change', () => {
            const row = element.closest('tr');
            const id = row.dataset.id;
            const formData = new FormData();
            formData.append('id', id);
            row.querySelectorAll('input, select, textarea').forEach(input => {
                formData.append(input.name, input.value);
            });

            fetch('../php/update_team_member_single.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('status');
                    if (data.success) {
                        statusDiv.textContent = 'Změny byly uloženy.';
                        statusDiv.className = 'status success';
                    } else {
                        statusDiv.textContent = 'Chyba při ukládání.';
                        statusDiv.className = 'status error';
                    }
                    setTimeout(() => {
                        statusDiv.textContent = '';
                    }, 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
</script>