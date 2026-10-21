@echo off
title RansomGuard - FIM Background Monitoring Service
echo ====================================================================
echo   RansomGuard - File Integrity Monitoring (FIM) Background Engine
echo ====================================================================
echo.
echo Searching for PHP executable...

set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" (
    set PHP_BIN=php
)

echo Using PHP: %PHP_BIN%
echo.
echo Starting continuous file monitoring service...
echo Monitoring ONLY folders explicitly configured by the Administrator in MySQL.
echo (Press Ctrl+C to terminate the service)
echo.

"%PHP_BIN%" "%~dp0service\fim_monitor.php"

echo.
echo [SERVICE STOPPED]
pause
