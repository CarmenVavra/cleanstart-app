@echo off
REM ==========================================================
REM  CleanStart - stoppt den lokalen Server auf Port 8900
REM ==========================================================
setlocal
set PORT=8900
set FOUND=0

REM Fenster von start.bat (php artisan serve) schliessen
taskkill /FI "WINDOWTITLE eq CleanStart Server*" /T /F >nul 2>&1 && set FOUND=1

REM Alle verbleibenden Prozesse beenden, die auf dem Port lauschen
for /f "tokens=5" %%p in ('netstat -ano ^| findstr /R /C:":%PORT% .*LISTENING"') do (
    taskkill /PID %%p /T /F >nul 2>&1 && set FOUND=1
)

if "%FOUND%"=="1" (
    echo CleanStart-Server auf Port %PORT% wurde gestoppt.
) else (
    echo Auf Port %PORT% lief kein Server.
)
exit /b 0
