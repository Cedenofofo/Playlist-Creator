<?php
session_start();
require_once 'config.php';

echo "<h1>Spotify Debug Information</h1>";

// Mostrar información de configuración
echo "<h2>Configuration</h2>";
echo "<pre>";
echo "Client ID: " . SPOTIFY_CLIENT_ID . "\n";
echo "Redirect URI: " . SPOTIFY_REDIRECT_URI . "\n";
echo "Current URL: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\n";
echo "</pre>";

// Mostrar información de sesión
echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Mostrar información de cookies
echo "<h2>Cookie Information</h2>";
echo "<pre>";
echo "Session Cookie Name: " . session_name() . "\n";
echo "Session Cookie ID: " . session_id() . "\n";
echo "All Cookies:\n";
print_r($_COOKIE);
echo "</pre>";

// Mostrar parámetros GET
echo "<h2>GET Parameters</h2>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

// Limpiar estado anterior y generar nuevo estado
unset($_SESSION[SPOTIFY_SESSION_STATE_KEY]);
$state = generateSpotifyState();

// Mostrar información del estado
echo "<h2>State Information</h2>";
echo "<pre>";
echo "Generated State: " . $state . "\n";
echo "Stored State: " . (isset($_SESSION[SPOTIFY_SESSION_STATE_KEY]) ? $_SESSION[SPOTIFY_SESSION_STATE_KEY] : 'Not set') . "\n";
echo "</pre>";

// Generar URL de autenticación
$auth_params = array(
    'client_id' => SPOTIFY_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => SPOTIFY_REDIRECT_URI,
    'state' => $state,
    'scope' => SPOTIFY_SCOPES,
    'show_dialog' => 'true'
);

$auth_url = SPOTIFY_AUTH_URL . '?' . http_build_query($auth_params);

echo "<h2>Test Authentication URL</h2>";
echo "<p>Click the link below to test authentication:</p>";
echo "<a href='" . htmlspecialchars($auth_url) . "'>Test Spotify Auth</a>";

// Mostrar información del servidor
echo "<h2>Server Information</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Save Handler: " . ini_get('session.save_handler') . "\n";
echo "Session Cookie Parameters:\n";
print_r(session_get_cookie_params());
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";
?> 