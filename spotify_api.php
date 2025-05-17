<?php
// Función para hacer peticiones a la API de Spotify
function makeSpotifyRequest($url, $method = 'GET', $body = null) {
    if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
        throw new Exception('No hay token de acceso');
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = [
        'Authorization: Bearer ' . $_SESSION[SPOTIFY_SESSION_TOKEN_KEY],
        'Content-Type: application/json'
    ];

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } else if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        throw new Exception('Error en la petición cURL: ' . curl_error($ch));
    }

    curl_close($ch);

    return [
        'httpCode' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Función para refrescar el token si es necesario
function refreshSpotifyToken() {
    // Implementar si es necesario
    return false;
}
?> 