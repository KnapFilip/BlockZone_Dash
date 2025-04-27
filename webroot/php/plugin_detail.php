<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true); // Aktivace bezpečnostního režimu pro db.php
session_start();
require 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Neplatné ID pluginu.");
}

// Ověření přihlášení uživatele a načtení dat z databáze podle discord_id (viz předchozí kód)
if (isset($_SESSION['user']['discord_id'])) {
    $discord_id = $_SESSION['user']['discord_id'];


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

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM plugins WHERE id = ?");
$stmt->execute([$id]);
$plugin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plugin) {
    die("Plugin nenalezen.");
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>BlockZone - Tresty</title>
    <link rel="icon" href="../images/logo.png">
    <link rel="icon" href="/images/logo.png" type="image/png">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/advanced.css">
    <link rel="stylesheet" href="../css/plugin_detail.css">
</head>

<body>
    <h2>Detail pluginu: <?= htmlspecialchars($plugin['plugin_name']) ?></h2>
    <table>
        <tr>
            <th>ID</th>
            <td><?= $plugin['id'] ?></td>
        </tr>
        <tr>
            <th>Název</th>
            <td><?= htmlspecialchars($plugin['plugin_name']) ?></td>
        </tr>
        <tr>
            <th>Verze</th>
            <td><?= htmlspecialchars($plugin['plugin_version']) ?></td>
        </tr>
        <tr>
            <th>Typ</th>
            <td><?= htmlspecialchars($plugin['plugin_type']) ?></td>
        </tr>
        <tr>
            <th>Odkaz</th>
            <td><a href="<?= htmlspecialchars($plugin['plugin_link']) ?>" target="_blank"><?= htmlspecialchars($plugin['plugin_link']) ?></a></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= htmlspecialchars($plugin['plugin_status']) ?></td>
        </tr>
        <tr>
            <th>Aktivní</th>
            <td><?= htmlspecialchars($plugin['plugin_active']) ?></td>
        </tr>
        <tr>
            <th>Poslední aktualizace</th>
            <td><?= date("d.m.Y", strtotime($plugin['plugin_last_update'])) ?></td>
        </tr>
    </table>
    <br>
    <div id="chat-container">
        <h3>Chat</h3>
        <div id="chat-messages"></div>

        <input type="text" id="chat-input" placeholder="Napiš zprávu..."><br>
        <button id="chat-send">Odeslat</button>
    </div>


    <button style="margin-left: 46.5%;"><a href="../development/plugins.php">Zpět na seznam</a></button>

    <footer>
        <a href="https://www.instagram.com/fida_knap/" target="_blank" style="padding: 10px;"><img src="../images/instagram.png" alt="instagram" style="width: 1.5%; height: 1.5%;" class="IG"></a>
        <a href="https://discord.gg/Msv22AUx3m" target="_blank" style="padding: 10px;"><img src="../images/discord.png" alt="discord" style="width: 1.75%; height: 2.25%;" class="DC"></a>
        <p>Created by Filip Knap with lot of ☕ and ❤️</p>
        <p>© 2025 Knap Filip</p>
    </footer>
</body>

</html>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pluginId = <?= (int)$_GET['id'] ?>; // ID pluginu z URL
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');

        function fetchMessages() {
            fetch(`../php/get_plugin_message.php?plugin_id=${pluginId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    chatMessages.innerHTML = '';
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            const div = document.createElement('div');
                            div.innerHTML = `<strong>${msg.author}:</strong> ${msg.message} <small style="color:gray;">[${msg.created_at}]</small>`;
                            chatMessages.appendChild(div);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight; // Auto scroll dolů
                    }
                })
                .catch(error => {
                    console.error('fetchMessages error:', error);
                    alert('Chyba při načítání zpráv.');
                });
        }

        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            fetch('../php/send_plugin_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        plugin_id: pluginId,
                        message: message
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        chatInput.value = ''; // Vymažeme input
                        fetchMessages(); // Načteme nové zprávy
                    } else {
                        alert('Chyba při odesílání zprávy: ' + (data.error || 'Neznámá chyba.'));
                    }
                })
                .catch(error => {
                    console.error('sendMessage error:', error);
                    alert('Chyba při odesílání zprávy.');
                });
        }

        chatSend.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        setInterval(fetchMessages, 60000); // Každých 5 sekund načti nové zprávy
        fetchMessages();
    });
</script>
