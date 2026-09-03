@echo off
setlocal

set "ROOT_DIR=%~dp0"
cd /d "%ROOT_DIR%"
call "%ROOT_DIR%php-runtime.cmd" scripts/migrate.php
exit /b %ERRORLEVEL%
