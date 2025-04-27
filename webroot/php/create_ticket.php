<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('SECURE', true);
session_start();
require '../php/db.php';

if (!isset($_SESSION['user']['discord_id'])) {
    header("Location: support.php");
    exit;
}

$discord_id = $_SESSION['user']['discord_id'];
$type = $_POST['type'];
$subject = $_POST['subject'];
$message = $_POST['message'];
$created_at = date('Y-m-d H:i:s');

// Uložit ticket do DB
$stmt = $pdo->prepare("INSERT INTO tickets (discord_id, type, subject, message, created_at) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$discord_id, $type, $subject, $message, $created_at]);

// URL pro Discord Webhook
$webhook_url = 'https://discord.com/api/webhooks/1364304722047012944/y-AReLm8BRD_oDH9MJkfodUzWH1z4Nax-jw1eHrdgdNsDOksFRYcvYFIuxqbF_Lsh8gf';

// Zjistit zmíněné role podle typu ticketu
switch ($type) {
    case 'admin':
        $discord_message = "<@&1363607650855682148> , <@&1363611837521985780> Právě dorazil nový ticket pro Adminy.";
        break;
    case 'dev':
        $discord_message = "<@&1363607650855682148> , <@&1363611837521985780>  Právě dorazil nový ticket pro Dev Team.";
        break;
    case 'managment':
        $discord_message = "<@&112233445566778899> Právě dorazil nový ticket pro Management.";
        break;
    default:
        $discord_message = "Právě dorazil nový ticket.";
}

// Připravit payload pro Webhook
$data = [
    'content' => $discord_message,
    'embeds' => [
        [
            'title' => $subject,
            'description' => $message,
            'color' => 3447003,
            'footer' => ['text' => 'Ticket ID: ' . $pdo->lastInsertId()]
        ]
    ]
];

// Použití cURL pro odeslání na Discord Webhook
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

header("Location: ../../users/tickets.php");
exit;
