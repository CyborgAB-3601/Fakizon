@echo off
title Stop Fakizon PHP Server
echo ========================================================
echo          Stopping Fakizon PHP Web Server...
echo ========================================================
echo.

taskkill /F /IM php.exe >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] PHP server has been successfully stopped.
) else (
    echo [INFO] No running PHP server was found.
)

echo ========================================================
timeout /t 3 >nul
