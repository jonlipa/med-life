@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0"
set "HOST=127.0.0.1"
set "PORT=3306"
set "MYSQLD_EXE="
set "MYSQL_INI="
set "MYSQL_LOG_DIR=%ROOT_DIR%storage\logs"
set "MYSQL_OUT_LOG=%MYSQL_LOG_DIR%\mysql.out.log"
set "MYSQL_ERR_LOG=%MYSQL_LOG_DIR%\mysql.err.log"

call :port_open
if not errorlevel 1 (
    echo MySQL eshte aktive ne %HOST%:%PORT%.
    exit /b 0
)

for %%S in (MySQL84 MySQL83 MySQL82 MySQL81 MySQL80 MySQL57 MySQL) do (
    sc query "%%S" >nul 2>nul
    if not errorlevel 1 (
        echo Po niset sherbimi MySQL: %%S
        net start "%%S" >nul 2>nul
        call :wait_for_mysql
        if not errorlevel 1 exit /b 0
    )
)

for %%I in (mysqld.exe) do if not "%%~$PATH:I"=="" set "MYSQLD_EXE=%%~$PATH:I"
if defined MYSQLD_EXE set "MYSQL_INI=C:\ProgramData\MySQL\MySQL Server 8.4\my.ini"

if not defined MYSQLD_EXE if exist "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe" (
    set "MYSQLD_EXE=C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe"
    set "MYSQL_INI=C:\ProgramData\MySQL\MySQL Server 8.4\my.ini"
)

if defined MYSQLD_EXE if not exist "%MYSQL_INI%" (
    set "MYSQLD_EXE="
    set "MYSQL_INI="
)

if defined MYSQLD_EXE (
    if not exist "%MYSQL_LOG_DIR%" mkdir "%MYSQL_LOG_DIR%" >nul 2>nul
    echo Po niset MySQL lokal: "%MYSQLD_EXE%"
    start "Med Life MySQL" /min cmd /c ""%MYSQLD_EXE%" --defaults-file="%MYSQL_INI%" --console >> "%MYSQL_OUT_LOG%" 2>> "%MYSQL_ERR_LOG%""
    call :wait_for_mysql
    if not errorlevel 1 exit /b 0
)

echo MySQL nuk eshte aktiv ne %HOST%:%PORT% dhe nuk u gjet sherbim lokal MySQL.
echo Instalo MySQL Server ose nise manualisht, pastaj perditeso DB_* ne .env.
exit /b 1

:wait_for_mysql
for /L %%I in (1,1,20) do (
    call :port_open
    if not errorlevel 1 (
        echo MySQL u nis me sukses.
        exit /b 0
    )
    timeout /t 1 /nobreak >nul
)
exit /b 1

:port_open
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $client = [Net.Sockets.TcpClient]::new(); $task = $client.ConnectAsync('%HOST%', %PORT%); if ($task.Wait(500) -and $client.Connected) { $client.Close(); exit 0 }; $client.Close(); exit 1 } catch { exit 1 }"
exit /b %ERRORLEVEL%
