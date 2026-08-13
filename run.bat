@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"

rem ---- Relaunch hidden so no terminal window appears ----
if "%~1" neq "hidden" (
    wscript.exe "%~dp0_hide.vbs" "%~f0" "hidden"
    exit /b 0
)

set "PORT=8080"
set "URL=http://localhost:%PORT%"
set "ERROR_LOG=%~dp0run-error.log"

rem ---- Already running? Just open the browser ----
netstat -ano | findstr /R /C:":%PORT% .*LISTENING" >nul 2>&1
if not errorlevel 1 (
    start "" "%URL%"
    exit /b 0
)

rem ---- Ensure MySQL is up on 3306 ----
call :ensure_mysql
if "%MYSQL_UP%"=="0" (
    echo [ERROR] MySQL is not reachable on port 3306. Start the MySQL80 service or XAMPP MySQL, then re-run. > "%ERROR_LOG%"
    exit /b 1
)

rem ---- Ensure Apache is up ----
call :ensure_apache "%PORT%"
if "%APACHE_UP%"=="0" (
    echo [ERROR] Could not start Apache. Check C:\xampp\apache\logs\error.log. > "%ERROR_LOG%"
    exit /b 1
)

rem ---- Open the app in the default browser ----
start "" "%URL%"
exit /b 0

rem ==================== helpers ====================

:ensure_mysql
set "MYSQL_UP=0"
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
if not errorlevel 1 (
    set "MYSQL_UP=1"
    goto :eof
)
net start MySQL80 >nul 2>&1
if not errorlevel 1 goto :mysql_wait
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    powershell -NoProfile -Command "Start-Process -FilePath 'C:\xampp\mysql\bin\mysqld.exe' -WindowStyle Hidden -ArgumentList '--defaults-file=C:\xampp\mysql\bin\my.ini'"
    goto :mysql_wait
)
goto :eof

:mysql_wait
set /a tries=0
:mysql_wait_loop
set /a tries+=1
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
if not errorlevel 1 (
    set "MYSQL_UP=1"
    goto :eof
)
if !tries! lss 20 (
    powershell -NoProfile -Command "Start-Sleep -Seconds 1" >nul
    goto mysql_wait_loop
)
goto :eof

:ensure_apache
set "APACHE_UP=0"
set "CHK_PORT=%~1"
netstat -ano | findstr /R /C:":%CHK_PORT% .*LISTENING" >nul 2>&1
if not errorlevel 1 (
    set "APACHE_UP=1"
    goto :eof
)
powershell -NoProfile -Command "Start-Process -FilePath 'C:\xampp\apache\bin\httpd.exe' -WindowStyle Hidden"
set /a tries=0
:apache_wait_loop
set /a tries+=1
netstat -ano | findstr /R /C:":%CHK_PORT% .*LISTENING" >nul 2>&1
if not errorlevel 1 (
    set "APACHE_UP=1"
    goto :eof
)
if !tries! lss 20 (
    powershell -NoProfile -Command "Start-Sleep -Seconds 1" >nul
    goto apache_wait_loop
)
goto :eof
