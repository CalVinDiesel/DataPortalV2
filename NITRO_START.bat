@echo off
title HYPER-NITRO ENGINE LAUNCHER (v90)
echo ======================================================
echo    DATA PORTAL V2: HYPER-NITRO ENGINE STARTING
echo ======================================================
echo.

:: 1. Start the 16-Lane Worker Fleet (Ports 9001-9016)
echo [1/2] Launching 16-Lane Worker Farm...
FOR /L %%i IN (9001,1,9016) DO (
    start /b php -S 127.0.0.1:%%i -t public >nul 2>&1
)
echo [DONE] 16 Workers are running in the background.

:: 2. Start the Main Portal (Port 8000)
echo [2/2] Launching Main Portal...
echo (Keeping this window open to show logs)
echo.

:: Start the portal
php artisan serve --port=8000

echo.
echo ======================================================
echo    ENGINE STOPPED.
echo ======================================================
pause
