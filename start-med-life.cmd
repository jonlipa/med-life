@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0"
set "APP_DIR=%ROOT_DIR%medlife-php"
set "HOST=127.0.0.1"
set "PORT=8000"
set "USE_HTTPS=1"
set "HTTPS_PORT=8443"

if not exist "%APP_DIR%\public\index.php" (
    echo Aplikacioni PHP nuk u gjet ne "%APP_DIR%".
    exit /b 1
)

cd /d "%APP_DIR%"

if not exist ".env" if exist ".env.example" (
    copy /Y ".env.example" ".env" >nul
    echo U krijua .env nga .env.example
)

if exist "%APP_DIR%\start-mysql.cmd" (
    echo == Med Life MySQL ==
    call "%APP_DIR%\start-mysql.cmd"
    if errorlevel 1 (
        echo MySQL lokal nuk u nis automatikisht. Health check do te raportoje statusin.
    )
)

echo == Med Life health preflight ==
call "%APP_DIR%\php-runtime.cmd" scripts\health_check.php
set "HEALTH_EXIT=%ERRORLEVEL%"

if "%HEALTH_EXIT%"=="1" (
    echo Health check deshtoi. Rregullo problemet e raportuara me siper.
    echo Sigurohu qe MySQL eshte aktiv dhe kredencialet ne .env jane te sakta.
    exit /b 1
)

if "%HEALTH_EXIT%"=="2" (
    echo Portali do te niset ne setup mode derisa MySQL te jete gati.
)

set "MAX_PORT=8010"
:choose_port
call :port_open %PORT%
if errorlevel 1 goto port_selected
set /a PORT+=1
if %PORT% GTR %MAX_PORT% (
    echo Nuk u gjet port i lire nga 8000 deri ne %MAX_PORT%.
    exit /b 1
)
goto choose_port

:port_selected
if "%USE_HTTPS%"=="0" goto start_http

where node >nul 2>nul
if errorlevel 1 (
    echo Node.js nuk u gjet. Nuk mund te niset HTTPS proxy lokal.
    echo Per HTTP vendos USE_HTTPS=0 ne start-med-life.cmd.
    exit /b 1
)

if exist "%APP_DIR%\trust-local-cert.cmd" (
    call "%APP_DIR%\trust-local-cert.cmd"
    if errorlevel 1 (
        echo Certifikata lokale nuk u shtua si trusted; browser-i mund te shfaqe Not secure.
    )
)

set "MAX_HTTPS_PORT=8453"
:choose_https_port
call :port_open %HTTPS_PORT%
if errorlevel 1 goto https_port_selected
set /a HTTPS_PORT+=1
if %HTTPS_PORT% GTR %MAX_HTTPS_PORT% (
    echo Nuk u gjet port HTTPS i lire nga 8443 deri ne %MAX_HTTPS_PORT%.
    exit /b 1
)
goto choose_https_port

:https_port_selected
set "PHP_HOST=%HOST%"
set "PHP_PORT=%PORT%"
set "MEDLIFE_HTTPS_HOST=%HOST%"
set "MEDLIFE_HTTPS_PORT=%HTTPS_PORT%"

echo Po niset Med Life me SSL:
echo   Backend: http://%HOST%:%PORT%
echo   HTTPS:   https://%HOST%:%HTTPS_PORT%
call "%APP_DIR%\start-https.cmd"
exit /b %ERRORLEVEL%

:start_http
echo Po niset Med Life PHP ne http://%HOST%:%PORT%
call "%APP_DIR%\php-runtime.cmd" -S %HOST%:%PORT% -t public public/index.php
exit /b %ERRORLEVEL%

:port_open
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $client = [Net.Sockets.TcpClient]::new(); $task = $client.ConnectAsync('127.0.0.1', %~1); if ($task.Wait(300) -and $client.Connected) { $client.Close(); exit 0 }; $client.Close(); exit 1 } catch { exit 1 }"
exit /b %ERRORLEVEL%
