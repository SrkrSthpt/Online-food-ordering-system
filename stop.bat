@echo off
setlocal
cd /d "%~dp0"

if "%~1" neq "hidden" (
    wscript.exe "%~dp0_hide.vbs" "%~f0" "hidden"
    exit /b 0
)

rem Stop Apache (serves all three local apps)
taskkill /IM httpd.exe /F >nul 2>&1

exit /b 0
