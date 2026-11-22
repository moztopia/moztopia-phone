# Server service

`server.Dockerfile` builds with PHP 8.4.27 Alpine, Composer, Laravel 13 and the local SDK snapshot. Composer discovery registers the provider. A separate runtime stage omits Composer, Git and compilers. `start.sh` creates disposable SQLite storage, applies the fixture's single migration and starts `php artisan serve` directly on container port 8000. No dedicated web server or background service is needed. Runtime uses `www-data`; `/app/storage` is tmpfs and disappears on shutdown.

by-dolly
