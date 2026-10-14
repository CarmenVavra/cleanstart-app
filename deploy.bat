@echo off
rem ============================================================================
rem  CleanStart auf https://cleanstart.caryssa.at aktualisieren (netcup/Plesk)
rem
rem  Ablauf: Tests -> Push zu GitHub -> Wartungsmodus -> git pull, Composer,
rem          Migrationen -> Design-Dateien hochladen -> Caches -> online.
rem  Voraussetzung: SSH-Schluessel %USERPROFILE%\.ssh\cleanstart_netcup
rem ============================================================================
setlocal
cd /d "%~dp0"

set "SSH=ssh -i "%USERPROFILE%\.ssh\cleanstart_netcup" -o IdentitiesOnly=yes -o BatchMode=yes hosting171221@202.61.232.106"
set "REMOTE=~/cleanstart.caryssa.at/app"

echo.
echo [1/6] Pruefe auf nicht committete Aenderungen ...
git diff --quiet
if errorlevel 1 goto :dirty
git diff --cached --quiet
if errorlevel 1 goto :dirty

echo [2/6] Tests ...
call php artisan test
if errorlevel 1 goto :failed

echo [3/6] Push zu GitHub ...
git push origin main
if errorlevel 1 goto :failed

echo [4/6] Design-Dateien bauen ...
call npm run build
if errorlevel 1 goto :failed

echo [5/6] Server aktualisieren (kurzer Wartungsmodus) ...
%SSH% "set -e; cd %REMOTE%; php artisan down --retry=15; git pull --ff-only; php ../bin/composer.phar install --no-dev --optimize-autoloader --no-interaction --no-progress; php artisan migrate --force --no-interaction"
if errorlevel 1 goto :remote_failed

tar czf - -C public build | %SSH% "set -e; cd %REMOTE%/public; rm -rf build.neu; mkdir build.neu; tar xzf - -C build.neu; rm -rf build; mv build.neu/build build; rm -rf build.neu"
if errorlevel 1 goto :remote_failed

echo [6/6] Caches leeren und wieder online gehen ...
rem Kein config:cache/optimize per SSH: Die SSH-Sitzung laeuft in einer isolierten Umgebung (chroot)
rem mit anderen Pfaden als der Webserver - zwischengespeicherte Pfade wuerden die Seite lahmlegen.
%SSH% "set -e; cd %REMOTE%; php artisan config:clear -q; php artisan route:clear -q; php artisan view:clear -q; php artisan event:clear -q; php artisan up"
if errorlevel 1 goto :remote_failed

echo.
echo Fertig: https://cleanstart.caryssa.at ist aktualisiert.
exit /b 0

:dirty
echo.
echo Es gibt nicht committete Aenderungen. Bitte zuerst committen.
exit /b 1

:failed
echo.
echo Abgebrochen - auf dem Server wurde nichts veraendert.
exit /b 1

:remote_failed
echo.
echo Fehler auf dem Server. Die Seite ist evtl. noch im Wartungsmodus.
echo Wieder online schalten:  %SSH% "cd %REMOTE% && php artisan up"
exit /b 1
