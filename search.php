<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['artists']) && isset($_POST['playlistName']) && isset($_POST['tracksPerArtist'])) {
        $artists = json_decode($_POST['artists'], true);
        $playlistName = $_POST['playlistName'];
        $tracksPerArtist = intval($_POST['tracksPerArtist']);
        
        // Obtener token de acceso para las búsquedas en la API
        $token = getSpotifyToken(SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET);
        
        if ($token) {
            $allTracks = [];
            
            // Procesar cada artista individualmente
            foreach ($artists as $artist) {
                // Buscar información del artista
                $artist_data = searchArtist($token, $artist);
                if ($artist_data) {
                    // Obtener las canciones top del artista
                    $tracks = getTopTracks($token, $artist_data['id']);
                    if ($tracks) {
                        // Tomar el número específico de canciones para este artista
                        $artistTracks = array_slice($tracks, 0, $tracksPerArtist);
                        $allTracks = array_merge($allTracks, $artistTracks);
                    }
                }
            }

            if (!empty($allTracks)) {
                // Almacenar la información en la sesión para uso posterior
                $_SESSION['pending_playlist'] = [
                    'name' => $playlistName,
                    'tracks' => $allTracks
                ];

                // Devolver éxito y los datos necesarios
                echo json_encode([
                    'success' => true,
                    'tracks' => $allTracks,
                    'auth_url' => 'auth.php'
                ]);
                exit;
            }
        }
        
        // Mensaje de error en inglés para la interfaz
        $_SESSION['message'] = "Error creating playlist.";
        echo json_encode(['success' => false, 'message' => 'Error creating playlist']);
        exit;
    }
}

// Función para obtener el token de acceso de Spotify
function getSpotifyToken($client_id, $client_secret) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result) {
        $data = json_decode($result, true);
        return $data['access_token'] ?? null;
    }
    return null;
}

// Función para buscar un artista por nombre
function searchArtist($token, $artist) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.spotify.com/v1/search?q=' . urlencode($artist) . '&type=artist&limit=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result) {
        $data = json_decode($result, true);
        return $data['artists']['items'][0] ?? null;
    }
    return null;
}

// Función para obtener las canciones más populares de un artista
function getTopTracks($token, $artist_id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/artists/{$artist_id}/top-tracks?market=US");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result) {
        $data = json_decode($result, true);
        return $data['tracks'] ?? null;
    }
    return null;
}
?> 