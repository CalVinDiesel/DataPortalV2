@echo off
title 🛑 HYPER-NITRO ENGINE SHUTDOWN
echo ======================================================
echo    🛑 SHUTTING DOWN HYPER-NITRO ENGINE
echo ======================================================
echo.

:: Kill all PHP processes to clear the ports
echo [!] Stopping all PHP Workers and Portal...
taskkill /F /IM php.exe /T >nul 2>&1

echo [SUCCESS] All lanes have been cleared.
echo.
echo ======================================================
echo    🏁 ENGINE STOPPED SAFELY.
echo ======================================================
timeout /t 3
exit
