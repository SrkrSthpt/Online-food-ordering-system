@echo off
setlocal
title Bitezy Launcher
cd /d "%~dp0"

echo ============================================
echo   Bitezy - Food Delivery System
echo ============================================
echo.

rem --- Locate PHP (PATH first, then common installs) ----------------
set "PHP_CANDIDATE=C:\xampp\php\php.exe"
if not exist "%PHP_CANDIDATE%" set "PHP_CANDIDATE="
where php >nul 2>nul
if %errorlevel% equ 0 (
  set "PHP=php"
) else (
  if defined PHP_CANDIDATE (
    set "PHP=%PHP_CANDIDATE%"
  ) else (
    echo PHP not found. Install PHP 8.2+ or add it to PATH.
    pause
    exit /b 1
  )
)

echo [1/4] Ensuring MySQL is reachable on port 3306...
call :mysql_up
if "%MYSQL_UP%"=="1" (
  echo       OK - MySQL is running
) else (
  echo       Could not reach MySQL on port 3306.
  echo       Start it manually (services.msc or XAMPP Control) and re-run.
  pause
  exit /b 1
)

echo [2/4] Ensuring dependencies are installed...
if not exist vendor\autoload.php (
  call :composer_install
  if errorlevel 1 (
    echo       Could not install dependencies. Install Composer and re-run.
    pause
    exit /b 1
  )
) else (
  echo       OK
)

echo [3/4] Applying pending migrations...
call "%PHP%" bin\bitezy db:migrate
echo.

echo [4/4] Starting web server...
echo   Serving at http://localhost:8080
echo   Press Ctrl+C to stop the server.
echo.
start "" "http://localhost:8080/"
"%PHP%" -S localhost:8080 -t public

echo.
echo   Server stopped.
pause
exit /b 0

rem --- Start MySQL/MariaDB if not already listening on 3306 ---------
:mysql_up
set "MYSQL_UP=0"
call :probe_3306
if not errorlevel 1 (
  set "MYSQL_UP=1"
  exit /b 0
)
echo       Not running - attempting to start...
net start MySQL80 >nul 2>&1
if %errorlevel% equ 0 (
  set "MYSQL_UP=1"
  exit /b 0
)
net start MySQL >nul 2>&1
if %errorlevel% equ 0 (
  set "MYSQL_UP=1"
  exit /b 0
)
net start MariaDB >nul 2>&1
if %errorlevel% equ 0 (
  set "MYSQL_UP=1"
  exit /b 0
)
if exist "C:\xampp\mysql\bin\mysqld.exe" (
  echo       Starting XAMPP MySQL in the background...
  start "Bitezy MySQL" /b "C:\xampp\mysql\bin\mysqld.exe" --port=3306
  powershell -NoProfile -Command "Start-Sleep -Seconds 6" >nul
  call :probe_3306
  if not errorlevel 1 (
    set "MYSQL_UP=1"
    exit /b 0
  )
)
set "MYSQL_UP=0"
exit /b 0

rem --- Exit 0 if port 3306 is listening, else exit 1 ----------------
:probe_3306
powershell -NoProfile -Command "$r = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -WarningAction SilentlyContinue; if ($r.TcpTestSucceeded) { exit 0 } else { exit 1 }" >nul 2>&1
exit /b %errorlevel%

rem --- Install Composer dependencies (composer on PATH or composer.phar)
:composer_install
where composer >nul 2>nul
if %errorlevel% equ 0 (
  call composer install
  exit /b %errorlevel%
)
if exist "composer.phar" (
  call "%PHP%" composer.phar install
  exit /b %errorlevel%
)
exit /b 1
