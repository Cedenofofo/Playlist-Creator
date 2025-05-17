<?php
session_start();
require_once 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION[SPOTIFY_SESSION_TOKEN_KEY])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Playlist - Spotify Generator</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1DB954, #191414);
            min-height: 100vh;
            color: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 120px);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .playlist-form {
            background: rgba(0, 0, 0, 0.5);
            padding: 2rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            margin-bottom: 1rem;
        }
        input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        .artist-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .remove-artist {
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .button {
            background-color: #1DB954;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s, background-color 0.2s;
            width: 100%;
            margin-top: 1rem;
        }
        .button:hover {
            background-color: #1ed760;
            transform: scale(1.02);
        }
        .button.secondary {
            background-color: rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        .result-container {
            margin-top: 2rem;
            padding: 1rem;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
        }
        .track-list {
            margin-top: 1rem;
        }
        .track-item {
            display: flex;
            align-items: center;
            padding: 0.8rem;
            margin-bottom: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        .track-item:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        .track-info {
            flex-grow: 1;
        }
        .track-name {
            font-weight: bold;
            margin-bottom: 0.2rem;
        }
        .track-artist {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .track-controls {
            display: flex;
            gap: 0.5rem;
        }
        .track-button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        .track-button:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .track-button.remove {
            color: #ff4444;
        }
        .track-button.play {
            color: #1DB954;
        }
        .add-specific-track {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }
        .add-specific-track input {
            width: calc(100% - 120px);
            margin-right: 10px;
        }
        .add-specific-track button {
            width: 100px;
        }
        .duration {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        .logout {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .logout:hover {
            background: rgba(0, 0, 0, 0.5);
        }
        .error-message, .success-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .error-message {
            background-color: #e74c3c;
            border-left: 4px solid #c0392b;
        }
        .success-message {
            background-color: #2ecc71;
            border-left: 4px solid #27ae60;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .loading {
            text-align: center;
            padding: 2rem;
        }
        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-left-color: #1DB954;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        #export-spotify {
            background-color: #1DB954;
            transition: all 0.3s ease;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 1rem;
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
        #export-success {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            background-color: #1DB954;
            color: white;
            text-align: center;
        }
        .hidden {
            display: none !important;
        }
        .search-container {
            position: relative;
            margin: 20px 0;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        #track-search {
            flex: 1;
            padding: 12px 40px 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        #track-search:focus {
            outline: none;
            border-color: #1DB954;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 10px rgba(29, 185, 84, 0.3);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.3s ease;
        }

        #track-search:focus + .search-icon {
            color: #1DB954;
        }

        #search-results {
            margin-top: 15px;
            max-height: 400px;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .track-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .track-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .track-item.selected {
            background: rgba(29, 185, 84, 0.2);
            border: 1px solid rgba(29, 185, 84, 0.3);
        }

        .track-controls {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .track-item:hover .track-controls {
            opacity: 1;
        }

        .track-button {
            background: rgba(29, 185, 84, 0.2);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 8px;
        }

        .track-button:hover {
            background: #1DB954;
            transform: scale(1.1);
        }

        .track-button:disabled {
            background: rgba(255, 255, 255, 0.1);
            cursor: not-allowed;
            transform: none;
        }

        .loading-dots {
            display: inline-block;
        }

        .loading-dots:after {
            content: '.';
            animation: dots 1.5s steps(5, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60% { content: '...'; }
            80%, 100% { content: ''; }
        }

        /* Estilo para el scrollbar del contenedor de resultados */
        #search-results::-webkit-scrollbar {
            width: 8px;
        }

        #search-results::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        #search-results::-webkit-scrollbar-thumb {
            background: rgba(29, 185, 84, 0.5);
            border-radius: 4px;
        }

        #search-results::-webkit-scrollbar-thumb:hover {
            background: #1DB954;
        }

        .footer {
            width: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            text-align: center;
            color: white;
            font-size: 0.9rem;
            z-index: 999;
            margin-top: 40px;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .creator-credit {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .social-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .social-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .social-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .social-link i {
            font-size: 1.1rem;
        }

        .social-link.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        }

        .social-link.twitter:hover {
            background: #1DA1F2;
        }

        .social-link.x:hover {
            background: #000000;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <a href="logout.php" class="logout">Cerrar Sesión</a>
    <div class="container">
        <div class="header">
            <h1>Crear Playlist</h1>
        </div>
        <div class="playlist-form">
            <div class="form-group">
                <label for="playlist-name">Nombre de la Playlist</label>
                <input type="text" id="playlist-name" placeholder="Ingresa un nombre para tu playlist">
            </div>
            <div class="form-group">
                <label>Artistas</label>
                <div id="artists-container">
                    <div class="artist-input-group">
                        <input type="text" class="artist-input" placeholder="Nombre del artista">
                        <button class="remove-artist" onclick="removeArtist(this)">×</button>
                    </div>
                </div>
                <button class="button secondary" onclick="addArtistInput()">+ Agregar otro artista</button>
            </div>
            <div class="form-group">
                <label for="track-count">Canciones por artista</label>
                <select id="track-count">
                    <option value="3">3 canciones</option>
                    <option value="5" selected>5 canciones</option>
                    <option value="10">10 canciones</option>
                    <option value="15">15 canciones</option>
                </select>
            </div>
            <button class="button" onclick="createPlaylist()">Crear Playlist</button>
        </div>

        <div id="loading" class="loading" style="display: none;">
            <p>Creando playlist...</p>
        </div>

        <div id="error" class="error-message" style="display: none;"></div>

        <div id="result" class="result-container" style="display: none;">
            <h2>Playlist creada con éxito</h2>
            <div class="track-list" id="track-list"></div>
            
            <!-- Add specific track search section -->
            <div class="add-specific-track">
                <h3>Agregar canción específica</h3>
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="track-search" 
                               placeholder="Escribe para buscar canciones..."
                               autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <div id="search-results" class="track-list"></div>
                </div>
            </div>

            <div class="action-buttons">
                <button id="export-spotify" class="button">
                    <i class="fab fa-spotify"></i> Exportar a Spotify
                </button>
                <button onclick="resetForm()" class="button secondary">Crear otra playlist</button>
            </div>
            <div id="export-success" class="hidden">
                <p>¡Playlist exportada con éxito!</p>
                <a href="#" id="open-spotify" class="button" target="_blank">
                    <i class="fab fa-spotify"></i> Abrir en Spotify
                </a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="creator-credit">
                Created by: Rodolfo Cedeño S
            </div>
            <div class="social-links">
                <a href="https://x.com/cedenofofo" target="_blank" class="social-link x">
                    <i class="fab fa-x-twitter"></i>
                    X
                </a>
                <a href="https://instagram.com/cedenofofo" target="_blank" class="social-link instagram">
                    <i class="fab fa-instagram"></i>
                    Instagram
                </a>
            </div>
        </div>
    </footer>

    <script>
        // Funciones globales para manejo de audio y tracks
        let audioPreview = null;
        function previewTrack(url) {
            if (audioPreview) {
                audioPreview.pause();
            }
            audioPreview = new Audio(url);
            audioPreview.play();
        }

        async function removeTrack(uri) {
            const playlistId = document.getElementById('result').dataset.playlistId;
            try {
                const response = await fetch('remove_track.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        playlistId: playlistId,
                        uri: uri
                    })
                });
                
                if (response.ok) {
                    document.querySelector(`[data-uri="${uri}"]`).remove();
                } else {
                    alert('Error al eliminar la canción');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar la canción');
            }
        }

        let searchTimeout = null;
        let isSearching = false;

        // Funciones de utilidad
        function showError(message) {
            const errorDiv = document.getElementById('error');
            errorDiv.style.display = 'block';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            document.querySelector('.container').appendChild(successDiv);
            
            // Auto-ocultar después de 3 segundos
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        // Función para manejar errores de fetch
        async function handleFetchResponse(response) {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('La respuesta del servidor no es JSON válido');
            }
            
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Error en la solicitud');
            }
            
            return data;
        }

        // Función para manejar la búsqueda en tiempo real
        document.getElementById('track-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.trim();
            const searchResults = document.getElementById('search-results');
            
            // Limpiar el timeout anterior
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Si el campo está vacío, limpiar resultados
            if (!searchTerm) {
                searchResults.innerHTML = '';
                return;
            }

            // Mostrar indicador de carga
            searchResults.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Buscando<span class="loading-dots"></span></p></div>';

            // Establecer un nuevo timeout para la búsqueda
            searchTimeout = setTimeout(() => {
                searchTrack(searchTerm);
            }, 500);
        });

        // Función mejorada de búsqueda
        async function searchTrack(searchTerm) {
            if (isSearching) return;
            isSearching = true;

            const searchResults = document.getElementById('search-results');
            
            try {
                const response = await fetch('search_track.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        query: searchTerm
                    })
                });

                const data = await handleFetchResponse(response);
                
                if (data.success && data.tracks && data.tracks.length > 0) {
                    searchResults.innerHTML = data.tracks.map(track => {
                        // Asegurarse de que el artista sea una cadena
                        const artistName = track.artist || (Array.isArray(track.artists) ? track.artists.join(', ') : 'Artista desconocido');
                        
                        return `
                            <div class="track-item" data-uri="${track.uri}">
                                <div class="track-info">
                                    <div class="track-name">${track.name}</div>
                                    <div class="track-artist">${artistName}</div>
                                    <div class="duration">${formatDuration(track.duration_ms || track.duration)}</div>
                                </div>
                                <div class="track-controls">
                                    ${track.preview_url ? `
                                        <button class="track-button play" onclick="previewTrack('${track.preview_url}')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    ` : ''}
                                    <button class="track-button add" onclick="addTrackToPlaylist('${track.uri}', this)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    searchResults.innerHTML = '<div class="no-results">No se encontraron canciones</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                searchResults.innerHTML = '<div class="error-message">Error al buscar canciones</div>';
            } finally {
                isSearching = false;
            }
        }

        // Función mejorada para agregar canciones
        async function addTrackToPlaylist(trackUri, button) {
            const playlist = JSON.parse(sessionStorage.getItem('currentPlaylist'));
            if (!playlist) {
                showError('No se encontró la información de la playlist');
                return;
            }

            // Deshabilitar el botón y mostrar loading
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch('add_track.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        trackUri: trackUri
                    })
                });

                const data = await handleFetchResponse(response);
                
                if (data.success) {
                    // Actualizar el botón con un check
                    button.innerHTML = '<i class="fas fa-check"></i>';
                    button.style.background = '#1DB954';
                    
                    // Agregar la canción a la lista
                    const trackList = document.getElementById('track-list');
                    trackList.insertAdjacentHTML('beforeend', createTrackItem(data.track));
                    
                    // Actualizar sessionStorage
                    playlist.tracks.push(data.track);
                    sessionStorage.setItem('currentPlaylist', JSON.stringify(playlist));
                    
                    // Agregar clase selected al elemento de la canción
                    const trackItem = button.closest('.track-item');
                    trackItem.classList.add('selected');

                    // Mostrar mensaje de éxito
                    showSuccess('Canción agregada a la playlist');
                } else {
                    throw new Error(data.error || 'Error al añadir la canción');
                }
            } catch (error) {
                console.error('Error:', error);
                // Restaurar el botón
                button.disabled = false;
                button.innerHTML = originalContent;
                showError(error.message || 'Error al añadir la canción');
            }
        }

        function createTrackItem(track) {
            return `
                <div class="track-item" data-uri="${track.uri}">
                    <div class="track-info">
                        <div class="track-name">${track.name}</div>
                        <div class="track-artist">${track.artist}</div>
                        <div class="duration">${formatDuration(track.duration)}</div>
                    </div>
                    <div class="track-controls">
                        ${track.preview_url ? `
                            <button class="track-button play" onclick="previewTrack('${track.preview_url}')">
                                ▶
                            </button>
                        ` : ''}
                        <a href="${track.external_url}" target="_blank" class="track-button">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <path fill="currentColor" d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0z"/>
                            </svg>
                        </a>
                        <button class="track-button remove" onclick="removeTrack('${track.uri}')">×</button>
                    </div>
                </div>
            `;
        }

        function formatDuration(ms) {
            const minutes = Math.floor(ms / 60000);
            const seconds = ((ms % 60000) / 1000).toFixed(0);
            return `${minutes}:${seconds.padStart(2, '0')}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const artistsContainer = document.getElementById('artists-container');
            const resultDiv = document.getElementById('result');
            const loadingDiv = document.getElementById('loading');
            const errorDiv = document.getElementById('error');
            const trackList = document.getElementById('track-list');

            // Función para mostrar error
            function showError(message) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = message;
                loadingDiv.style.display = 'none';
            }

            // Función para mostrar carga
            function showLoading() {
                loadingDiv.style.display = 'block';
                errorDiv.style.display = 'none';
                resultDiv.style.display = 'none';
            }

            // Crear playlist
            window.createPlaylist = function() {
                const playlistName = document.getElementById('playlist-name').value;
                const trackCount = document.getElementById('track-count').value;
                const artists = Array.from(document.querySelectorAll('.artist-input'))
                    .map(input => input.value.trim())
                    .filter(value => value !== '');

                if (!playlistName) {
                    alert('Por favor, ingresa un nombre para la playlist');
                    return;
                }

                if (artists.length === 0) {
                    alert('Por favor, ingresa al menos un artista');
                    return;
                }

                // Mostrar mensaje de carga
                showLoading();

                // Enviar datos al servidor
                fetch('create_playlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        playlistName: playlistName,
                        trackCount: parseInt(trackCount),
                        artists: artists
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    loadingDiv.style.display = 'none';
                    if (data.success) {
                        // Store playlist data in sessionStorage
                        sessionStorage.setItem('currentPlaylist', JSON.stringify({
                            name: data.playlistName,
                            tracks: data.tracks
                        }));

                        // Show results
                        resultDiv.style.display = 'block';
                        errorDiv.style.display = 'none';

                        // Display tracks
                        trackList.innerHTML = data.tracks.map(track => createTrackItem(track)).join('');
                    } else {
                        showError(data.error || 'Error al crear la playlist');
                    }
                })
                .catch(error => {
                    showError(`Error al crear la playlist: ${error.message}`);
                    console.error('Error:', error);
                });
            };

            // Make addArtistInput and removeArtist functions global
            window.addArtistInput = function() {
                const group = document.createElement('div');
                group.className = 'artist-input-group';
                group.innerHTML = `
                    <input type="text" class="artist-input" placeholder="Nombre del artista">
                    <button class="remove-artist" onclick="removeArtist(this)">×</button>
                `;
                artistsContainer.appendChild(group);
            };

            window.removeArtist = function(button) {
                const group = button.parentElement;
                if (document.querySelectorAll('.artist-input-group').length > 1) {
                    group.remove();
                } else {
                    alert('Debe haber al menos un artista');
                }
            };

            window.resetForm = function() {
                document.getElementById('playlist-name').value = '';
                const artistInputs = document.querySelectorAll('.artist-input');
                artistInputs.forEach((input, index) => {
                    if (index === 0) {
                        input.value = '';
                    } else {
                        input.closest('.artist-input-group').remove();
                    }
                });
                document.getElementById('track-count').value = '5';
                resultDiv.style.display = 'none';
                errorDiv.style.display = 'none';
                sessionStorage.removeItem('currentPlaylist');
            };
        });

        async function exportToSpotify() {
            const playlist = JSON.parse(sessionStorage.getItem('currentPlaylist'));
            
            if (!playlist) {
                showError('No se encontró la información de la playlist');
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
                    document.getElementById('export-success').classList.remove('hidden');
                    document.getElementById('open-spotify').href = data.playlistUrl;
                    exportButton.style.display = 'none';
                } else {
                    throw new Error(data.error || 'Error al exportar la playlist');
                }
            } catch (error) {
                showError('Error al exportar la playlist: ' + error.message);
                const exportButton = document.getElementById('export-spotify');
                exportButton.disabled = false;
                exportButton.innerHTML = '<i class="fab fa-spotify"></i> Exportar a Spotify';
            }
        }

        // Event listener para el botón de exportar
        document.getElementById('export-spotify').addEventListener('click', exportToSpotify);
    </script>
</body>
</html> 