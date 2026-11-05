@echo off
REM ==========================================================
REM  CleanStart - stoppt den lokalen Server auf Port 8900
REM ==========================================================
setlocal
rem Vite-Dev-Server (falls "npm run dev" laeuft): fester Port, siehe vite.config.js
set "VITE_PORT=6150"
for /f "tokens=5" %%v in ('netstat -ano ^| findstr /R /C:":%VITE_PORT%  *0\.0\.0\.0:0 " /C:":%VITE_PORT%  *\[::\]:0 "') do taskkill /PID %%v /T /F >nul 2>&1
if exist "%~dp0public\hot" del /q "%~dp0public\hot" >nul 2>&1
set PORT=8900
set FOUND=0

REM Fenster von start.bat (php artisan serve) schliessen
taskkill /FI "WINDOWTITLE eq CleanStart Server*" /T /F >nul 2>&1 && set FOUND=1

REM Alle verbleibenden Prozesse beenden, die auf dem Port lauschen
for /f "tokens=5" %%p in ('netstat -ano ^| findstr /R /C:":%PORT%  *0\.0\.0\.0:0 " /C:":%PORT%  *\[::\]:0 "') do (
    taskkill /PID %%p /T /F >nul 2>&1 && set FOUND=1
)

if "%FOUND%"=="1" (
    echo CleanStart-Server auf Port %PORT% wurde gestoppt.
) else (
    echo Auf Port %PORT% lief kein Server.
)
exit /b 0
