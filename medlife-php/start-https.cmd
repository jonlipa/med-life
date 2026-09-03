@echo off
setlocal

cd /d "%~dp0"

if "%PHP_HOST%"=="" set "PHP_HOST=127.0.0.1"
if "%PHP_PORT%"=="" set "PHP_PORT=8000"
if "%MEDLIFE_HTTPS_HOST%"=="" set "MEDLIFE_HTTPS_HOST=127.0.0.1"
if "%MEDLIFE_HTTPS_PORT%"=="" set "MEDLIFE_HTTPS_PORT=8443"

where node >nul 2>nul
if errorlevel 1 (
    echo Node.js is required for the local HTTPS proxy.
    exit /b 1
)

set "ORIG_PHP_PORT=%PHP_PORT%"
set /a MAX_PHP_PORT=PHP_PORT+10
:choose_php_port
call :port_open "%PHP_HOST%" %PHP_PORT%
if errorlevel 1 goto php_port_selected
echo PHP backend port %PHP_PORT% is already in use; trying next port ...
set /a PHP_PORT+=1
if %PHP_PORT% GTR %MAX_PHP_PORT% (
    echo No free PHP backend port found from %ORIG_PHP_PORT% to %MAX_PHP_PORT%.
    exit /b 1
)
goto choose_php_port

:php_port_selected
set "ORIG_HTTPS_PORT=%MEDLIFE_HTTPS_PORT%"
set /a MAX_HTTPS_PORT=MEDLIFE_HTTPS_PORT+10
:choose_https_port
call :port_open "%MEDLIFE_HTTPS_HOST%" %MEDLIFE_HTTPS_PORT%
if errorlevel 1 goto https_port_selected
echo HTTPS port %MEDLIFE_HTTPS_PORT% is already in use; trying next port ...
set /a MEDLIFE_HTTPS_PORT+=1
if %MEDLIFE_HTTPS_PORT% GTR %MAX_HTTPS_PORT% (
    echo No free HTTPS port found from %ORIG_HTTPS_PORT% to %MAX_HTTPS_PORT%.
    exit /b 1
)
goto choose_https_port

:https_port_selected
set "APP_URL=https://%MEDLIFE_HTTPS_HOST%:%MEDLIFE_HTTPS_PORT%"
set "APP_FORCE_HTTPS=true"
set "APP_HSTS_ENABLED=true"
if "%APP_HSTS_MAX_AGE%"=="" set "APP_HSTS_MAX_AGE=31536000"
if "%APP_HSTS_INCLUDE_SUBDOMAINS%"=="" set "APP_HSTS_INCLUDE_SUBDOMAINS=false"
if "%APP_HSTS_PRELOAD%"=="" set "APP_HSTS_PRELOAD=false"
set "SESSION_COOKIE_SECURE=true"

if exist "%~dp0trust-local-cert.cmd" (
    call "%~dp0trust-local-cert.cmd"
    if errorlevel 1 (
        echo Local SSL certificate is not trusted; the browser may show Not secure.
    )
)

echo Starting PHP backend on http://%PHP_HOST%:%PHP_PORT% ...
start "Med Life PHP Backend" /min cmd /c ".\php-runtime.cmd -S %PHP_HOST%:%PHP_PORT% -t public public/index.php"
timeout /t 2 /nobreak >nul

set MEDLIFE_BACKEND_URL=http://%PHP_HOST%:%PHP_PORT%

echo Starting HTTPS proxy on https://%MEDLIFE_HTTPS_HOST%:%MEDLIFE_HTTPS_PORT% ...
node scripts\https_proxy.js
exit /b %ERRORLEVEL%

:port_open
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $client = [Net.Sockets.TcpClient]::new(); $task = $client.ConnectAsync('%~1', %~2); if ($task.Wait(300) -and $client.Connected) { $client.Close(); exit 0 }; $client.Close(); exit 1 } catch { exit 1 }"
exit /b %ERRORLEVEL%
