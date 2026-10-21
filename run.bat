@echo off
title RansomGuard - Launcher & CLI Services
echo ====================================================================
echo   RansomGuard - Security Platform & FIM Background Engine
echo ====================================================================
echo.
echo Select an option:
echo   [1] Start FIM Background Monitoring Service (PHP CLI)
echo   [2] Start Web Dashboard Server (localhost:8000)
echo   [3] Start BOTH Web Server and FIM Background Monitor
echo.
set /p choice="Enter choice [1, 2, or 3] (Default: 1): "

if "%choice%"=="2" goto start_web
if "%choice%"=="3" goto start_both
goto start_fim

:start_fim
echo.
echo Starting FIM CLI Background Service on selected folders...
set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=php
"%PHP_BIN%" "%~dp0service\fim_monitor.php"
pause
exit

:start_web
echo.
echo Starting Web Server on http://localhost:8000 ...
start "" "http://localhost:8000/login.php"
php -S localhost:8000 -t "%~dp0"
pause
exit

:start_both
echo.
echo Starting FIM CLI Background Monitor in separate console window...
start "RansomGuard FIM Background Engine" cmd /k ""%~dp0fim_monitor.bat""
echo Starting Web Server on http://localhost:8000 ...
start "" "http://localhost:8000/login.php"
php -S localhost:8000 -t "%~dp0"
pause
exit
