@echo off
setlocal
title Bitezy Launcher
cd /d "%~dp0"

echo ============================================
echo   Bitezy - Food Delivery System
echo ============================================
echo.

set "HTDOCS=C:\xampp\htdocs\bitezy"

echo [1/4] Syncing project files to Apache htdocs...
robocopy "%~dp0" "%HTDOCS%" /E /NFL /NDL /NJH /NJS /NC /NS /NP >nul
if %errorlevel% leq 7 (
  echo       OK
) else (
  echo       Sync failed - run as Administrator
)

echo [2/4] Ensuring MySQL is running...
sc query MySQL80 | find /i "RUNNING" >nul
if %errorlevel% equ 0 (
  echo       MySQL already running
) else (
  net start MySQL80 >nul 2>&1
  if %errorlevel% equ 0 ( echo       MySQL started ) else ( echo       Could not start MySQL - run as Administrator )
)

echo [3/4] Ensuring Apache is running...
netstat -an | find "0.0.0.0:80" | find "LISTENING" >nul
if %errorlevel% equ 0 (
  echo       Apache already running
) else (
  start "" "C:\xampp\apache\bin\httpd.exe"
  echo       Apache started
)

echo [4/4] Waiting for server to respond...
timeout /t 3 /nobreak >nul
start "" "http://localhost/bitezy/"

echo.
echo   Bitezy is running at http://localhost/bitezy/
echo   (Apache and MySQL keep running after this window closes)
echo.
pause
