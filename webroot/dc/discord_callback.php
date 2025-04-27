<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
define('SECURE', true);  // Definuj konstantu SECURE, aby byl povolen přístup k db.php
require '../php/db.php'; // Poté zahrň db.php

// OAuth konfigurace
$client_id = '1363084123706626058';
$client_secret = '9Hmy2iCENFkqEFNBK7SQ5BK6Dsav2JE1';
$redirect_uri = 'https://dashblockzone.knapf.eu/dc/discord_callback.php';

// 1. Získání kódu z URL
if (!isset($_GET['code'])) {
    exit('Chybí kód v URL.');
}
$code = $_GET['code'];

// 2. Výběr access tokenu
$data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'scope' => 'identify email'
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://discord.com/api/oauth2/token',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);
$token_response = json_decode($response, true);

// Debugging - zobrazení odpovědi při chybě s access tokenem
if (!isset($token_response['access_token'])) {
    echo "<pre>";
    var_dump($token_response);
    echo "</pre>";
    exit('Nepodařilo se získat access token.');
}

$access_token = $token_response['access_token'];

// 3. Získání informací o uživateli
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://discord.com/api/users/@me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token
    ]
]);
$response = curl_exec($curl);
curl_close($curl);
$user = json_decode($response, true);

// 4. Získání "connections" (volitelné)
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://discord.com/api/users/@me/connections',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token
    ]
]);
$response = curl_exec($curl);
curl_close($curl);
$connections = json_decode($response, true);

// 5. Uložení do databáze
$discord_id = $user['id'];
$username = $user['username'];
$discriminator = $user['discriminator'];
$email = $user['email'] ?? null;
$connections_json = json_encode($connections);

// Zkontroluj, jestli uživatel existuje
$stmt = $pdo->prepare("SELECT id, role_id FROM dc_users WHERE discord_id = ?");
$stmt->execute([$discord_id]);
$user_data = $stmt->fetch();

if ($user_data) {
    // Pokud uživatel existuje, žádné změny v roli
    $role_id = $user_data['role_id'];
} else {
    // Pokud uživatel neexistuje, přiřadíme mu základní roli (role_id = 1)
    $role_id = 1; // Základní role (user)

    // Insert do tabulky
    $stmt = $pdo->prepare("INSERT INTO dc_users (discord_id, username, discriminator, email, connections, role_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$discord_id, $username, $discriminator, $email, $connections_json, $role_id]);
}

// 6. Uložení do session
// Před uložením role do session, ověříme, že máme správně nastavené role_id
$stmt = $pdo->prepare("SELECT role_id FROM dc_users WHERE discord_id = ?");
$stmt->execute([$discord_id]);
$role_id = $stmt->fetchColumn(); // Načteme roli uživatele z databáze

// Uložení do session
$_SESSION['user'] = [
    'discord_id' => $discord_id,
    'username' => $username,
    'discriminator' => $discriminator,
    'email' => $email,
    'role_id' => $role_id // Uložení role_id do session
];


// 7. Přesměrování na dashboard
header('Location: /users/dashboard.php');
exit;
