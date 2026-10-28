@echo off
REM ==========================================================
REM  CleanStart - startet den lokalen Server auf Port 8900
REM ==========================================================
setlocal
cd /d "%~dp0"

set PORT=8900

netstat -ano | findstr /R /C:":%PORT%  *0\.0\.0\.0:0 " /C:":%PORT%  *\[::\]:0 " >nul
if %errorlevel%==0 (
    echo CleanStart laeuft bereits auf http://127.0.0.1:%PORT%
    start "" "http://127.0.0.1:%PORT%"
    exit /b 0
)

if not exist "vendor\autoload.php" (
    echo Installiere PHP-Abhaengigkeiten ...
    call composer install --no-interaction || goto :error
)

if not exist ".env" (
    copy ".env.example" ".env" >nul
    call php artisan key:generate --force || goto :error
)

if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
)

echo Fuehre Datenbank-Migrationen aus ...
call php artisan migrate --force || goto :error
call php artisan db:seed --force || goto :error

if not exist "node_modules" (
    echo Installiere Frontend-Abhaengigkeiten ...
    call npm install || goto :error
)

echo Baue Frontend-Assets ...
call npm run build || goto :error

echo Starte CleanStart auf http://127.0.0.1:%PORT% ...
start "CleanStart Server" /min php artisan serve --host=127.0.0.1 --port=%PORT%

timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:%PORT%"
echo Server laeuft. Zum Beenden stop.bat ausfuehren.
exit /b 0

:error
echo.
echo FEHLER: CleanStart konnte nicht gestartet werden.
pause
exit /b 1
