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
if errorlevel 1 goto :no_php_path
set "PHP=php"
goto :php_ready

:no_php_path
if not defined PHP_CANDIDATE goto :php_missing
set "PHP=%PHP_CANDIDATE%"
goto :php_ready

:php_missing
echo PHP not found. Install PHP 8.2+ or add it to PATH.
pause
exit /b 1

:php_ready

echo [1/4] Ensuring MySQL is reachable on port 3306...
call :mysql_up
if "%MYSQL_UP%"=="1" goto :mysql_ok
echo       Could not reach MySQL on port 3306.
echo       Start it manually - services.msc or XAMPP Control - then re-run.
pause
exit /b 1

:mysql_ok
echo       OK - MySQL is running

echo [2/4] Ensuring dependencies are installed...
if exist vendor\autoload.php goto :deps_ok
call :composer_install
if errorlevel 1 goto :deps_fail
goto :deps_ok

:deps_fail
echo       Could not install dependencies. Install Composer and re-run.
pause
exit /b 1

:deps_ok
echo       OK

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
if errorlevel 1 goto :mysql_up_try
set "MYSQL_UP=1"
goto :eof

:mysql_up_try
echo       Not running - attempting to start...
net start MySQL80 >nul 2>&1
if not errorlevel 1 goto :mysql_up_ok
net start MySQL >nul 2>&1
if not errorlevel 1 goto :mysql_up_ok
net start MariaDB >nul 2>&1
if not errorlevel 1 goto :mysql_up_ok
if not exist "C:\xampp\mysql\bin\mysqld.exe" goto :eof
echo       Starting XAMPP MySQL in the background...
start "Bitezy MySQL" /b "C:\xampp\mysql\bin\mysqld.exe" --port=3306
powershell -NoProfile -Command "Start-Sleep -Seconds 6" >nul
call :probe_3306
if errorlevel 1 goto :eof
set "MYSQL_UP=1"
goto :eof

:mysql_up_ok
set "MYSQL_UP=1"
goto :eof

rem --- Exit 0 if port 3306 is listening, else exit 1 ----------------
:probe_3306
powershell -NoProfile -Command "$r = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -WarningAction SilentlyContinue; if ($r.TcpTestSucceeded) { exit 0 } else { exit 1 }" >nul 2>&1
exit /b %errorlevel%

rem --- Install Composer dependencies (composer on PATH or composer.phar)
:composer_install
where composer >nul 2>nul
if errorlevel 1 goto :ci_phar
call composer install
exit /b %errorlevel%

:ci_phar
if not exist "composer.phar" exit /b 1
call "%PHP%" composer.phar install
exit /b %errorlevel%
