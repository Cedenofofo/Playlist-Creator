<?php
// Activar todos los errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Asegurarnos de que no haya output antes de los headers
ob_start();

// Log de los datos recibidos
error_log("Received input: " . file_get_contents('php://input'));

session_start();
require_once 'config.php';
require_once 'spotify_api.php';

// Limpiar cualquier output anterior
ob_clean();

// Asegurar que siempre devolvemos JSON
header('Content-Type: application/json');

// Función para hacer log y devolver error
function returnError($message, $code = 500) {
    error_log("Error: " . $message);
    http_response_code($code);
    ob_clean(); // Limpiar cualquier output anterior
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

// Verificar si hay errores de PHP antes de continuar
if (error_get_last()) {
    error_log("PHP Error found: " . print_r(error_get_last(), true));
    returnError('Internal PHP Error');
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
    returnError('No hay sesión activa', 401);
}

// Obtener y validar los datos de entrada
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['playlistName']) || !isset($input['artists']) || !isset($input['trackCount'])) {
    returnError('Faltan datos requeridos', 400);
}

if (empty($input['artists'])) {
    returnError('Debe proporcionar al menos un artista', 400);
}

$playlistName = $input['playlistName'];
$artists = $input['artists'];
$tracksPerArtist = $input['trackCount'];

try {
    $allTracks = [];
    $trackDetails = [];
    
    // Buscar canciones para cada artista
    foreach ($artists as $artistQuery) {
        if (empty(trim($artistQuery))) continue;

        // Buscar el artista con coincidencia exacta
        $searchResult = makeSpotifyRequest(
            SPOTIFY_API_URL . '/search?' . http_build_query([
                'q' => $artistQuery,
                'type' => 'artist',
                'limit' => 5
            ])
        );

        if ($searchResult['httpCode'] !== 200) {
            continue;
        }

        if (empty($searchResult['response']['artists']['items'])) {
            continue;
        }

        // Buscar coincidencia exacta
        $artistId = null;
        $artistName = null;
        foreach ($searchResult['response']['artists']['items'] as $artist) {
            if (strtolower(trim($artist['name'])) === strtolower(trim($artistQuery))) {
                $artistId = $artist['id'];
                $artistName = $artist['name'];
                break;
            }
        }

        // Si no hay coincidencia exacta, usar el primer resultado
        if (!$artistId) {
            $artistId = $searchResult['response']['artists']['items'][0]['id'];
            $artistName = $searchResult['response']['artists']['items'][0]['name'];
        }

        // Obtener las mejores canciones del artista
        $tracksResult = makeSpotifyRequest(
            SPOTIFY_API_URL . "/artists/{$artistId}/top-tracks?" . http_build_query([
                'market' => 'ES'
            ])
        );

        if ($tracksResult['httpCode'] !== 200) {
            continue;
        }

        $tracks = array_slice($tracksResult['response']['tracks'], 0, $tracksPerArtist);
        foreach ($tracks as $track) {
            $trackDetails[] = [
                'uri' => $track['uri'],
                'name' => $track['name'],
                'artist' => $artistName,
                'duration' => $track['duration_ms'],
                'preview_url' => $track['preview_url'],
                'external_url' => $track['external_urls']['spotify'],
                'album' => [
                    'name' => $track['album']['name'],
                    'image' => isset($track['album']['images'][0]) ? $track['album']['images'][0]['url'] : null
                ]
            ];
        }
    }

    if (empty($trackDetails)) {
        returnError('No se encontraron canciones para los artistas proporcionados');
    }

    // Guardar la playlist en la sesión para uso posterior
    if (!isset($_SESSION['playlists'])) {
        $_SESSION['playlists'] = [];
    }
    
    $playlistId = uniqid('playlist_');
    $_SESSION['playlists'][$playlistId] = [
        'name' => $playlistName,
        'tracks' => $trackDetails,
        'created_at' => time()
    ];

    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Playlist creada localmente',
        'playlistId' => $playlistId,
        'playlistName' => $playlistName,
        'tracks' => $trackDetails
    ]);

} catch (Exception $e) {
    returnError($e->getMessage());
}
?> 