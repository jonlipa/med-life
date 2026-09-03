@echo off
setlocal EnableExtensions EnableDelayedExpansion

set "ROOT_DIR=%~dp0"
set "PHP_EXE="

call :resolve_php
if errorlevel 1 exit /b 1

for %%I in ("%PHP_EXE%") do set "PHP_DIR=%%~dpI"
set "PHP_EXT_DIR=%PHP_DIR%ext"

if not exist "%PHP_EXT_DIR%\php_pdo_mysql.dll" (
    echo U gjet PHP, por mungon php_pdo_mysql.dll ne "%PHP_EXT_DIR%".
    exit /b 1
)

if "%~1"=="" (
    echo Perdorimi: php-runtime.cmd [argumente per php]
    echo Shembull: php-runtime.cmd -S 127.0.0.1:8000 -t public public/index.php
    exit /b 1
)

echo Duke perdorur PHP: "%PHP_EXE%"
"%PHP_EXE%" -d "extension_dir=%PHP_EXT_DIR%" -d extension=pdo_mysql %*
exit /b %ERRORLEVEL%

:resolve_php
for %%I in (php.exe) do if not "%%~$PATH:I"=="" set "PHP_EXE=%%~$PATH:I"
if defined PHP_EXE goto :resolved

if exist "%USERPROFILE%\scoop\shims\php.exe" set "PHP_EXE=%USERPROFILE%\scoop\shims\php.exe"
if defined PHP_EXE goto :resolved

if exist "%ProgramFiles%\PHP\php.exe" set "PHP_EXE=%ProgramFiles%\PHP\php.exe"
if defined PHP_EXE goto :resolved

if exist "%ProgramFiles(x86)%\PHP\php.exe" set "PHP_EXE=%ProgramFiles(x86)%\PHP\php.exe"
if defined PHP_EXE goto :resolved

for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.*") do (
    if exist "%%~fD\php.exe" set "PHP_EXE=%%~fD\php.exe"
)
if defined PHP_EXE goto :resolved

echo PHP nuk u gjet.
echo Instalo PHP ose hape nje terminal te ri qe te merret PATH i rifreskuar.
exit /b 1

:resolved
exit /b 0
