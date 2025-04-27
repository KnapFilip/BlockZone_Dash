<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$client_id = '1363084123706626058';
$redirect_uri = 'https://dashblockzone.knapf.eu/dc/discord_callback.php'; // správná URL
$scope = 'identify email'; // požadované scope

$params = [
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'permissions' => 8
];

$discord_oauth_url = "https://discord.com/oauth2/authorize?client_id=1363084123706626058&response_type=code&redirect_uri=https%3A%2F%2Fdashblockzone.knapf.eu%2Fdc%2Fdiscord_callback.php&scope=identify+email" . http_build_query($params);
define('SECURE', true);  // Definuj konstantu SECURE, aby byl povolen přístup k db.php
require 'php/db.php'; // Poté zahrň db.php


?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Discord Login</title>
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/advanced.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <h1>Přihlášení</h1>
    <a class="discord-button" href="<?= $discord_oauth_url ?>">
        <img src="images/discord_black.png" alt="discord_logo"> Přihlásit se přes Discord
    </a>
</body>

</html>