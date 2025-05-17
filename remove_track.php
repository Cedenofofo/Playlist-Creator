<?php
session_start();
require_once 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
    exit;
}

// Obtener y validar los datos de entrada
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['playlistId']) || !isset($input['uri'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Eliminar la canción de la playlist
    $deleteResult = makeSpotifyRequest(
        SPOTIFY_API_URL . "/playlists/{$input['playlistId']}/tracks",
        'DELETE',
        ['tracks' => [['uri' => $input['uri']]]]
    );

    if ($deleteResult['httpCode'] !== 200) {
        throw new Exception('Error al eliminar la canción de la playlist');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 