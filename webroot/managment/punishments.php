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
        <table class="punishments-table">
            <thead>
                <tr>
                    <th>Název skutku</th>
                    <th>Druh trestu</th>
                    <th>Minimální délka (dny)</th>
                    <th>Maximální délka (dny)</th>
                    <th>Popis</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($punishments as $row): ?>
                    <tr>
                        <form action="../php/update_punishment.php" method="POST" id="punishmentForm">
                    <tr>
                        <td><input type="text" name="punishment_name" value="<?php echo htmlspecialchars($row['punishment_name']); ?>" oninput="submitForm()" /></td>
                        <td><input type="text" name="punishment_type" value="<?php echo htmlspecialchars($row['punishment_type']); ?>" oninput="submitForm()" /></td>
                        <td><input type="number" name="minimum_punishment_length" value="<?php echo htmlspecialchars($row['minimum_punishment_length']); ?>" oninput="submitForm()" /></td>
                        <td><input type="number" name="maximum_punishment_length" value="<?php echo htmlspecialchars($row['maximum_punishment_length']); ?>" oninput="submitForm()" /></td>
                        <td><textarea name="punishment_description" oninput="submitForm()"><?php echo htmlspecialchars($row['punishment_description']); ?></textarea></td>
                        <td class="actions">
                            <input type="hidden" name="punishment_id" value="<?php echo $row['id']; ?>" />
                            <a href="../php/delete_punishment.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Opravdu chceš smazat tento trest?');">🗑️ Smazat</a>
                        </td>
                    </tr>
                    </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
    <br>
    <br>
    <br>
    <div class="form">
        <h2>Vytvořit nový trest</h2>
        <form action="../php/create_punishment.php" method="POST">
            <div>
                <label for="punishment_name">Název trestu:</label>
                <input type="text" name="punishment_name" id="punishment_name" required>
            </div>
            <div>
                <label for="punishment_type">Typ trestu:</label>
                <select name="punishment_type" id="punishment_type" required>
                    <option value="ban">Ban</option>
                    <option value="mute">Mute</option>
                    <option value="kick">Kick</option>
                    <option value="warn">Warn</option>
                    <option value="other">Jiné</option>
                </select>
            </div>
            <div>
                <label for="minimum_punishment_length">Minimální délka trestu (v dnech):</label>
                <input type="number" name="minimum_punishment_length" id="minimum_punishment_length" required>
            </div>
            <div>
                <label for="maximum_punishment_length">Maximální délka trestu (v dnech):</label>
                <input type="number" name="maximum_punishment_length" id="maximum_punishment_length" required>
            </div>
            <div>
                <label for="punishment_description">Popis trestu:</label>
                <textarea name="punishment_description" id="punishment_description" rows="4" required></textarea>
            </div>
            <div>
                <button type="submit">Vytvořit trest</button>
            </div>
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
<script>
    function submitForm() {
        var form = document.getElementById("punishmentForm");

        // Vytvoření form data (získání dat z formuláře)
        var formData = new FormData(form);

        // Nastavení AJAX requestu
        var xhr = new XMLHttpRequest();
        xhr.open("POST", form.action, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Volitelné: můžeš přidat kód pro reakci na úspěch
                console.log("Data byla úspěšně odeslána.");
            }
        };

        // Odeslání formuláře pomocí AJAX
        xhr.send(formData);
    }
</script>