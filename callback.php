<?php
session_start();
require_once 'config.php';

try {
    if (!isset($_GET['code'])) {
        throw new Exception('No se recibió el código de autorización');
    }

    // Verificar el estado para prevenir CSRF
    if (empty($_GET['state']) || $_GET['state'] !== $_SESSION[SPOTIFY_SESSION_STATE_KEY]) {
        throw new Exception('Error de validación del estado');
    }

    // Intercambiar el código por un token de acceso
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => SPOTIFY_REDIRECT_URI,
        'client_id' => SPOTIFY_CLIENT_ID,
        'client_secret' => SPOTIFY_CLIENT_SECRET
    ]));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception('Error en la petición cURL: ' . $error);
    }

    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        throw new Exception('No se recibió el token de acceso');
    }

    // Guardar tokens y tiempo de expiración
    $_SESSION[SPOTIFY_SESSION_TOKEN_KEY] = $data['access_token'];
    $_SESSION['token_expires'] = time() + $data['expires_in'];
    if (isset($data['refresh_token'])) {
        $_SESSION[SPOTIFY_SESSION_REFRESH_TOKEN_KEY] = $data['refresh_token'];
    }

    // Obtener información del usuario
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SPOTIFY_API_URL . '/me');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $data['access_token']
    ]);

    $userResponse = curl_exec($ch);
    $userError = curl_error($ch);
    curl_close($ch);

    if ($userError) {
        throw new Exception('Error al obtener información del usuario: ' . $userError);
    }

    $userData = json_decode($userResponse, true);
    if (isset($userData['id'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['display_name'] ?? $userData['id'];
    }

    // Redirigir al usuario a la página principal
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    // Guardar el error en la sesión
    $_SESSION['auth_error'] = $e->getMessage();
    
    // Redirigir a la página principal con el error
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}
?> 