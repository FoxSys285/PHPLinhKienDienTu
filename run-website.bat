@echo off
setlocal

for %%I in ("%~dp0..\..") do set "LARAGON_ROOT=%%~fI"

set "PHP_DIR="
for /d %%D in ("%LARAGON_ROOT%\bin\php\php-*") do (
    if exist "%%D\php.exe" set "PHP_DIR=%%D"
)

set "NODE_DIR="
for /d %%D in ("%LARAGON_ROOT%\bin\nodejs\node-*") do (
    if exist "%%D\npm.cmd" set "NODE_DIR=%%D"
)

if not defined PHP_DIR (
    echo Could not find PHP in "%LARAGON_ROOT%\bin\php".
    pause
    exit /b 1
)

if not defined NODE_DIR (
    echo Could not find Node.js in "%LARAGON_ROOT%\bin\nodejs".
    pause
    exit /b 1
)

if not exist "%LARAGON_ROOT%\bin\composer\composer.bat" (
    echo Could not find Composer in "%LARAGON_ROOT%\bin\composer".
    pause
    exit /b 1
)

if not exist "%~dp0vendor\autoload.php" (
    echo Laravel dependencies are missing. Run composer install first.
    pause
    exit /b 1
)

set "PATH=%PHP_DIR%;%NODE_DIR%;%LARAGON_ROOT%\bin\composer;%PATH%"
start "Laravel development server" /D "%~dp0" "%ComSpec%" /k "composer run dev"

set /a WAIT_COUNT=0
:wait_for_server
curl.exe --silent --output NUL --max-time 2 http://127.0.0.1:8000
if not errorlevel 1 (
    start "" http://127.0.0.1:8000
    echo Website opened at http://127.0.0.1:8000
    exit /b 0
)

set /a WAIT_COUNT+=1
if %WAIT_COUNT% geq 30 goto server_timeout
powershell.exe -NoLogo -NoProfile -NonInteractive -Command "Start-Sleep -Seconds 1"
goto wait_for_server

:server_timeout
echo Could not reach http://127.0.0.1:8000 within 30 seconds.
echo Check the Laravel development server window for errors.
pause
exit /b 1
