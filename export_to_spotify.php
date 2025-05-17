<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Obtener el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['playlistName']) || !isset($data['tracks'])) {
    echo json_encode(['success' => false, 'error' => 'Datos de playlist incompletos']);
    exit;
}

$accessToken = $_SESSION[SPOTIFY_SESSION_TOKEN_KEY];

// 1. Obtener el ID del usuario actual
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.spotify.com/v1/me');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener información del usuario']);
    exit;
}

$userInfo = json_decode($response, true);
$userId = $userInfo['id'];

// 2. Crear la playlist
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/users/{$userId}/playlists");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => $data['playlistName'],
    'description' => 'Playlist creada con Spotify Playlist Generator',
    'public' => false
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 201) {
    echo json_encode(['success' => false, 'error' => 'Error al crear la playlist']);
    exit;
}

$playlistInfo = json_decode($response, true);
$playlistId = $playlistInfo['id'];
$playlistUrl = $playlistInfo['external_urls']['spotify'];

// 3. Agregar las canciones a la playlist
$trackUris = array_map(function($track) {
    return $track['uri'];
}, $data['tracks']);

// Dividir las canciones en grupos de 100 (límite de la API)
$trackChunks = array_chunk($trackUris, 100);

foreach ($trackChunks as $chunk) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/playlists/{$playlistId}/tracks");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'uris' => $chunk
    ]));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 201) {
        echo json_encode(['success' => false, 'error' => 'Error al agregar canciones a la playlist']);
        exit;
    }
}

// 4. Seguir la playlist (agregarla a la biblioteca)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/playlists/{$playlistId}/followers");
curl_setopt($ch, CURLOPT_PUT, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Devolver la respuesta exitosa
echo json_encode([
    'success' => true,
    'playlistId' => $playlistId,
    'playlistUrl' => $playlistUrl,
    'message' => 'Playlist creada y agregada a tu biblioteca'
]);
?> 