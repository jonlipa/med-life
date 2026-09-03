@echo off
setlocal EnableExtensions

set "CERT_SCRIPT=%~dp0scripts\generate_local_cert.ps1"

if not exist "%CERT_SCRIPT%" (
    echo Script-i i certifikates lokale nuk u gjet: "%CERT_SCRIPT%"
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%CERT_SCRIPT%"
exit /b %ERRORLEVEL%
