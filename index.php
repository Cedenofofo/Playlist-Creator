<?php
session_start();
require_once 'config.php';

// Si ya tenemos un token de acceso, redirigir a la página principal
if (isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
    header('Location: main.php');
    exit;
}

// Generar nuevo estado para la autenticación
$state = generateSpotifyState();

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Playlist de Spotify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/search.css">
    <style>
        .hidden {
            display: none !important;
        }
        #export-success {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            background-color: #1DB954;
            color: white;
            text-align: center;
        }
        #export-spotify {
            background-color: #1DB954;
            transition: background-color 0.3s ease;
        }
        #export-spotify:hover {
            background-color: #1ed760;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(29, 185, 84, 0.3);
        }
        #export-spotify:active {
            transform: scale(0.98);
        }
        #export-spotify:disabled {
            background-color: #1DB954;
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generador de Playlist de Spotify</h1>
        
        <?php if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])): ?>
            <div class="login-section">
                <p>Para comenzar, inicia sesión con tu cuenta de Spotify</p>
                <a href="auth.php" class="spotify-button">
                    <i class="fab fa-spotify"></i> Iniciar sesión con Spotify
                </a>
            </div>
        <?php else: ?>
            <div class="playlist-section">
                <div class="search-container">
                    <input type="text" id="track-search" placeholder="Buscar canciones...">
                    <div id="search-suggestions"></div>
                </div>

                <div id="selected-tracks"></div>

                <input type="hidden" id="playlist-id" value="">
                
                <div class="playlist-form">
                    <input type="text" id="playlist-name" placeholder="Nombre de la playlist" required>
                    <textarea id="playlist-description" placeholder="Descripción de la playlist"></textarea>
                    <button id="create-playlist" class="spotify-button">
                        <i class="fas fa-plus"></i> Crear Playlist
                    </button>
                </div>

                <div id="loading" class="loading-spinner" style="display: none;">
                    <div class="spinner"></div>
                    <p>Creando playlist...</p>
                </div>

                <div id="success-message" class="success-message" style="display: none;">
                    <h3>¡Playlist creada con éxito!</h3>
                    <p>Tu playlist ha sido creada. Puedes:</p>
                    <div class="action-buttons">
                        <button id="export-spotify" class="spotify-button">
                            <i class="fab fa-spotify"></i> Exportar a Spotify
                        </button>
                        <button id="create-new" class="spotify-button secondary">
                            <i class="fas fa-plus"></i> Crear otra playlist
                        </button>
                    </div>
                    <div id="export-success" class="hidden">
                        <p>¡Playlist exportada con éxito!</p>
                        <a href="#" id="open-spotify" class="spotify-button" target="_blank">
                            <i class="fab fa-spotify"></i> Abrir en Spotify
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para exportar la playlist a Spotify
            async function exportToSpotify() {
                const playlistId = document.getElementById('playlist-id').value;
                const playlist = JSON.parse(sessionStorage.getItem('currentPlaylist'));
                
                if (!playlist) {
                    alert('No se encontró la información de la playlist');
                    return;
                }

                try {
                    const exportButton = document.getElementById('export-spotify');
                    exportButton.disabled = true;
                    exportButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exportando...';

                    const response = await fetch('export_to_spotify.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            playlistName: playlist.name,
                            tracks: playlist.tracks
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Mostrar mensaje de éxito y botón para abrir en Spotify
                        document.getElementById('export-success').classList.remove('hidden');
                        document.getElementById('open-spotify').href = data.playlistUrl;
                        exportButton.style.display = 'none';
                    } else {
                        throw new Error(data.error || 'Error al exportar la playlist');
                    }
                } catch (error) {
                    alert('Error al exportar la playlist: ' + error.message);
                    exportButton.disabled = false;
                    exportButton.innerHTML = '<i class="fab fa-spotify"></i> Exportar a Spotify';
                }
            }

            // Event listener para el botón de exportar
            const exportButton = document.getElementById('export-spotify');
            if (exportButton) {
                exportButton.addEventListener('click', exportToSpotify);
            }

            // Event listener para el botón de crear nueva playlist
            const createNewButton = document.getElementById('create-new');
            if (createNewButton) {
                createNewButton.addEventListener('click', function() {
                    location.reload();
                });
            }
        });
    </script>
    <script src="js/search.js"></script>
    <script src="js/main.js"></script>
</body>
</html> 