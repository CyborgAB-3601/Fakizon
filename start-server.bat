@echo off
title Fakizon E-Commerce Server
echo ========================================================
echo       FAKIZON - Amazon-Style Security Testing Lab
echo ========================================================
echo.

set PHP_EXE=php
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    set "PHP_EXE=C:\Users\Lenovo\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)

echo Finding local IP address...
for /f "tokens=4" %%a in ('route print ^| findstr 0.0.0.0.*0.0.0.0 ^| findstr /v "Default"') do set "LOCAL_IP=%%a"

echo.
echo Server Access URLs:
echo   - This Computer:  http://localhost:8000
echo   - Other Devices:   http://%LOCAL_IP%:8000
echo.
echo Demo Accounts:
echo   - Admin:    admin / admin123
echo   - Customer: alex  / alex123
echo.
echo Press Ctrl+C in this window to stop the server.
echo ========================================================
echo.

start http://localhost:8000
"%PHP_EXE%" -S 0.0.0.0:8000
pause
