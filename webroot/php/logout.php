<?php
// Zde ukončíme session
session_start();
session_unset(); // Smaže všechny proměnné session
session_destroy(); // Zničí session

// Přesměrování na jiný web
header("Location: https://blockzone.knapf.eu"); // Změňte na URL, kam chcete přesměrovat
exit; // Ukončí skript, aby se přesměrování provedlo okamžitě
