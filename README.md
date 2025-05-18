# Spotify Playlist Manager

Una aplicación web que te permite gestionar tus playlists de Spotify de manera fácil y eficiente.

## Características

- Crear nuevas playlists en Spotify
- Buscar y añadir canciones a tus playlists
- Eliminar canciones de tus playlists
- Exportar playlists a Spotify
- Interfaz web amigable y fácil de usar

## Requisitos

- PHP 7.4 o superior
- Servidor web (Apache/Nginx)
- Cuenta de desarrollador de Spotify
- Extensión PHP cURL habilitada
- XAMPP o entorno similar (para desarrollo local)

## Instalación

1. Clona este repositorio:
```bash
git clone [URL_DEL_REPOSITORIO]
cd spotify-playlist
```

2. Configura las credenciales de Spotify:
   - Ve a [Spotify Developer Dashboard](https://developer.spotify.com/dashboard)
   - Crea una nueva aplicación
   - Obtén el Client ID y Client Secret
   - Configura la URL de callback como `http://tu-dominio/callback.php`

3. Copia `config.php.example` a `config.php` y configura tus credenciales:
```php
define('SPOTIFY_CLIENT_ID', 'tu_client_id');
define('SPOTIFY_CLIENT_SECRET', 'tu_client_secret');
define('SPOTIFY_REDIRECT_URI', 'http://tu-dominio/callback.php');
```

4. Asegúrate que el directorio `sessions/` tenga permisos de escritura:
```bash
chmod 777 sessions/
```

## Uso

1. Accede a la aplicación a través de tu navegador web
2. Inicia sesión con tu cuenta de Spotify
3. ¡Comienza a gestionar tus playlists!

## Contribuir

Las contribuciones son bienvenidas. Por favor, sigue estos pasos:

1. Haz fork del repositorio
2. Crea una nueva rama (`git checkout -b feature/mejora`)
3. Haz commit de tus cambios (`git commit -am 'Añade nueva característica'`)
4. Push a la rama (`git push origin feature/mejora`)
5. Crea un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Si tienes alguna pregunta o problema, por favor abre un issue en el repositorio.
