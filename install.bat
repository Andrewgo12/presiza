@echo off
echo ========================================
echo  INSTALACION SISTEMA DE EVIDENCIAS
echo ========================================
echo.

echo [1/10] Verificando dependencias...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Composer no esta instalado
    echo Descarga e instala Composer desde: https://getcomposer.org/
    pause
    exit /b 1
)

where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Node.js no esta instalado
    echo Descarga e instala Node.js desde: https://nodejs.org/
    pause
    exit /b 1
)

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: PHP no esta instalado
    echo Instala PHP 8.2 o superior
    pause
    exit /b 1
)

echo [2/10] Instalando dependencias PHP...
call composer install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    echo ERROR: Fallo la instalacion de dependencias PHP
    pause
    exit /b 1
)

echo [3/10] Instalando dependencias Node.js...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: Fallo la instalacion de dependencias Node.js
    pause
    exit /b 1
)

echo [4/10] Configurando entorno...
if not exist .env (
    copy .env.example .env
    echo Archivo .env creado desde .env.example
)

echo [5/10] Generando clave de aplicacion...
call php artisan key:generate --force

echo [6/10] Configurando base de datos...
echo.
echo IMPORTANTE: Asegurate de que MySQL este ejecutandose
echo y que hayas creado la base de datos 'reportes_db'
echo.
echo Para crear la base de datos ejecuta:
echo mysql -u root -p
echo CREATE DATABASE reportes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
echo.
set /p continue="Presiona ENTER cuando la base de datos este lista..."

echo [7/10] Ejecutando migraciones...
call php artisan migrate:fresh --seed --force
if %errorlevel% neq 0 (
    echo ERROR: Fallo la migracion de la base de datos
    echo Verifica la configuracion de la base de datos en .env
    pause
    exit /b 1
)

echo [8/10] Creando enlace de storage...
call php artisan storage:link

echo [9/10] Compilando assets...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: Fallo la compilacion de assets
    pause
    exit /b 1
)

echo [10/10] Configurando permisos...
if not exist "storage\logs" mkdir "storage\logs"
if not exist "storage\app\public\files" mkdir "storage\app\public\files"
if not exist "storage\app\public\thumbnails" mkdir "storage\app\public\thumbnails"
if not exist "storage\app\public\avatars" mkdir "storage\app\public\avatars"

echo.
echo ========================================
echo  INSTALACION COMPLETADA EXITOSAMENTE
echo ========================================
echo.
echo El sistema esta listo para usar!
echo.
echo CREDENCIALES DE ACCESO:
echo - Admin: admin@company.com / admin123
echo - Analista: analyst@company.com / analyst123
echo - Investigador: investigator@company.com / investigator123
echo - Usuario: user@company.com / user123
echo.
echo Para iniciar el servidor ejecuta:
echo php artisan serve
echo.
echo Luego visita: http://localhost:8000
echo.
pause
