<?php
// player_profile.php

define('SECURE', true);
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user']['discord_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../php/db.php';

if (!isset($_GET['id'])) {
    echo "Hráč nenalezen.";
    exit;
}

$player_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM players WHERE id = ?");
$stmt->execute([$player_id]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$player) {
    echo "Hráč nenalezen.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM punishments WHERE player_id = ?");
$stmt->execute([$player_id]);
$punishments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Profil hráče - <?php echo htmlspecialchars($player['player_name']); ?></title>
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/player_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="profile-container">
        <h2><i class="fas fa-user"></i> Profil hráče: <?php echo htmlspecialchars($player['player_name']); ?></h2>
        <div class="player-info">
            <p><strong><i class="fab fa-discord"></i> Discord:</strong> <?php echo htmlspecialchars($player['discord_name']); ?> (<?php echo htmlspecialchars($player['discord_id']); ?>)</p>
            <p><strong><i class="fas fa-id-badge"></i> UUID:</strong> <?php echo htmlspecialchars($player['uuid']); ?></p>
            <p><strong><i class="fas fa-circle"></i> Online:</strong>
                <span class="<?php echo $player['online'] ? 'online' : 'offline'; ?>">
                    <?php echo $player['online'] ? 'Ano' : 'Ne'; ?>
                </span>
            </p>
            <p><strong><i class="fas fa-calendar-alt"></i> Datum registrace:</strong>
                <?php
                if (!empty($player['created_at'])) {
                    echo date('d.m.Y', strtotime($player['created_at']));
                } else {
                    echo 'Neuvedeno';
                }
                ?>
            </p>
            <p><strong><i class="fas fa-gavel"></i> Počet trestů:</strong> <?php echo count($punishments); ?></p>
        </div>

        <div class="buttons">
            <a href="punishment_add.php?player_id=<?php echo urlencode($player_id); ?>" class="button add"><i class="fas fa-plus"></i> Zapsat trest</a>
            <a href="#" class="button back" onclick="window.history.back(); return false;">
                <i class="fas fa-arrow-left"></i> Zpět na seznam hráčů
            </a>

        </div>

        <h3><i class="fas fa-list"></i> Seznam trestů</h3>

        <?php if (empty($punishments)): ?>
            <p class="no-punishments"><i class="fas fa-check-circle"></i> Hráč ještě nebyl trestán.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Druh</th>
                        <th>délka trestu</th>
                        <th>Popis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($punishments as $punish): ?>
                        <tr class="<?php echo strtolower($punish['punishment_type']); ?>">
                            <td><?php echo htmlspecialchars($punish['punishment_name']); ?></td>
                            <td><?php echo htmlspecialchars($punish['punishment_type']); ?></td>
                            <td><?php echo htmlspecialchars($punish['punishment_length']); ?></td>
                            <td><?php echo htmlspecialchars($punish['punishment_description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
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