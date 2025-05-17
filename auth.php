<?php
session_start();
require_once 'config.php';

// Generar un estado aleatorio para seguridad
$state = bin2hex(random_bytes(16));
$_SESSION['spotify_auth_state'] = $state;

// Construir los parámetros de autorización
$params = array(
    'client_id' => SPOTIFY_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => SPOTIFY_REDIRECT_URI,
    'state' => $state,
    'scope' => SPOTIFY_SCOPES,
    'show_dialog' => 'true' // Forzar diálogo de autorización
);

// Construir y redirigir a la URL de autorización de Spotify
$url = SPOTIFY_AUTH_URL . '?' . http_build_query($params);

// Guardar la URL de retorno si existe
if (isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['return_url'] = $_SERVER['HTTP_REFERER'];
}

// Redirigir a la página de autorización de Spotify
header('Location: ' . $url);
exit;
?> 