@echo off
setlocal
cd /d "%~dp0"
set "MYSQL_BIN=C:\Program Files\MySQL\MySQL Server 8.4\bin"
set "LOCAL_RUNTIME=%LOCALAPPDATA%\PMMA-Support-Local"
set "PHP_EXE=%LOCALAPPDATA%\Programs\PHP\8.2\php.exe"
if not exist "%PHP_EXE%" (
    echo PHP is missing from "%PHP_EXE%".
    exit /b 1
)
if not exist "%LOCAL_RUNTIME%\my.ini" (
    echo Local MySQL configuration is missing.
    exit /b 1
)
netstat -ano | findstr /C:"127.0.0.1:3306 " | findstr /C:"LISTENING" >nul
if errorlevel 1 (
    start "" /b "%MYSQL_BIN%\mysqld.exe" --defaults-file="%LOCAL_RUNTIME%\my.ini"
    timeout /t 5 /nobreak >nul
)
netstat -ano | findstr /C:"127.0.0.1:8001 " | findstr /C:"LISTENING" >nul
if not errorlevel 1 (
    echo PMMA Support is already running at http://127.0.0.1:8001
    exit /b 0
)
echo Starting PMMA Support at http://127.0.0.1:8001
"%PHP_EXE%" artisan serve --host=127.0.0.1 --port=8001
