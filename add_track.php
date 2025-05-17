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

if (!isset($data['trackUri'])) {
    echo json_encode(['success' => false, 'error' => 'URI de la canción no proporcionada']);
    exit;
}

$trackUri = $data['trackUri'];
$accessToken = $_SESSION[SPOTIFY_SESSION_TOKEN_KEY];

// Obtener información detallada de la canción
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.spotify.com/v1/tracks/' . substr($trackUri, 14)); // Remover 'spotify:track:'
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener información de la canción']);
    exit;
}

$trackInfo = json_decode($response, true);

// Formatear la información de la canción
$track = [
    'uri' => $trackInfo['uri'],
    'name' => $trackInfo['name'],
    'artist' => implode(', ', array_map(function($artist) { return $artist['name']; }, $trackInfo['artists'])),
    'duration_ms' => $trackInfo['duration_ms'],
    'preview_url' => $trackInfo['preview_url'],
    'external_url' => $trackInfo['external_urls']['spotify']
];

echo json_encode([
    'success' => true,
    'track' => $track
]); 