<?php
session_start();
require_once 'config.php';
require_once 'spotify_api.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
    exit;
}

// Obtener y validar los datos de entrada
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta el término de búsqueda']);
    exit;
}

try {
    // Buscar directamente canciones que coincidan con la búsqueda
    $trackSearch = makeSpotifyRequest(
        SPOTIFY_API_URL . '/search?' . http_build_query([
            'q' => $input['query'],
            'type' => 'track',
            'limit' => 20,
            'market' => 'ES'
        ])
    );

    if ($trackSearch['httpCode'] !== 200) {
        throw new Exception('Error al buscar canciones');
    }

    $tracks = [];
    foreach ($trackSearch['response']['tracks']['items'] as $track) {
        $artists = array_map(function($artist) {
            return $artist['name'];
        }, $track['artists']);

        $tracks[] = [
            'uri' => $track['uri'],
            'name' => $track['name'],
            'artist' => implode(', ', $artists),
            'duration' => $track['duration_ms'],
            'preview_url' => $track['preview_url'],
            'external_url' => $track['external_urls']['spotify'],
            'album' => [
                'name' => $track['album']['name'],
                'image' => isset($track['album']['images'][0]) ? $track['album']['images'][0]['url'] : null
            ]
        ];
    }

    // Si se proporciona playlistId y trackUri, añadir la canción a la playlist
    if (isset($input['playlistId']) && isset($input['trackUri'])) {
        $addResult = makeSpotifyRequest(
            SPOTIFY_API_URL . "/playlists/{$input['playlistId']}/tracks",
            'POST',
            ['uris' => [$input['trackUri']]]
        );

        if ($addResult['httpCode'] !== 201) {
            throw new Exception('Error al añadir la canción a la playlist');
        }

        // Encontrar la información de la canción añadida
        $addedTrack = array_filter($tracks, function($track) use ($input) {
            return $track['uri'] === $input['trackUri'];
        });

        $addedTrack = reset($addedTrack);
        if (!$addedTrack) {
            throw new Exception('No se pudo encontrar la información de la canción añadida');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Canción añadida correctamente',
            'track' => $addedTrack
        ]);
    } else {
        // Solo devolver las sugerencias
        echo json_encode([
            'success' => true,
            'tracks' => $tracks
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 