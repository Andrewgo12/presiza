# 🔧 Guía de Instalación - Sistema de Gestión de Evidencias

## 📋 Tabla de Contenidos

1. [Prerrequisitos](#prerrequisitos)
2. [Instalación Rápida](#instalación-rápida)
3. [Instalación Detallada](#instalación-detallada)
4. [Configuración de Base de Datos](#configuración-de-base-de-datos)
5. [Variables de Entorno](#variables-de-entorno)
6. [Verificación de Instalación](#verificación-de-instalación)
7. [Troubleshooting](#troubleshooting)

## 🔧 Prerrequisitos

### Software Requerido

| Software | Versión Mínima | Versión Recomendada | Propósito |
|----------|----------------|---------------------|-----------|
| **Node.js** | 16.0.0 | 18.0.0+ | Runtime de JavaScript |
| **npm** | 8.0.0 | 9.0.0+ | Gestor de paquetes |
| **Git** | 2.20.0 | 2.40.0+ | Control de versiones |
| **MongoDB** | 4.4.0 | 6.0.0+ | Base de datos principal |
| **MySQL** | 8.0.0 | 8.0.0+ | Base de datos secundaria |

### Sistemas Operativos Soportados

- ✅ **Windows 10/11** (con WSL2 recomendado)
- ✅ **macOS 10.15+** (Catalina o superior)
- ✅ **Linux** (Ubuntu 20.04+, CentOS 8+, Debian 11+)

### Hardware Recomendado

- **RAM**: 8GB mínimo, 16GB recomendado
- **Almacenamiento**: 10GB libres mínimo
- **CPU**: 2 cores mínimo, 4 cores recomendado

## 🚀 Instalación Rápida

### Opción 1: Instalación Automática (Recomendada)

```bash
# 1. Clonar el repositorio
git clone https://github.com/Andrewgo12/reportes.git
cd reportes

# 2. Ejecutar script de instalación automática
chmod +x install.sh
./install.sh
```

### Opción 2: Instalación Manual Rápida

```bash
# 1. Clonar repositorio
git clone https://github.com/Andrewgo12/reportes.git
cd reportes

# 2. Instalar dependencias del frontend
npm install

# 3. Instalar dependencias del backend
cd backend
npm install

# 4. Configurar variables de entorno
cp .env.example .env
cd ..
cp .env.example .env.local

# 5. Inicializar base de datos
cd backend
node database/init-databases.js

# 6. Iniciar aplicación
npm run dev
```

## 📖 Instalación Detallada

### Paso 1: Preparación del Entorno

#### 1.1 Instalar Node.js

**Windows:**
```powershell
# Usando Chocolatey
choco install nodejs

# O descargar desde https://nodejs.org
```

**macOS:**
```bash
# Usando Homebrew
brew install node

# O usando MacPorts
sudo port install nodejs18
```

**Linux (Ubuntu/Debian):**
```bash
# Método 1: Repositorio oficial
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Método 2: Snap
sudo snap install node --classic
```

#### 1.2 Verificar Instalación

```bash
node --version  # Debe mostrar v16.0.0 o superior
npm --version   # Debe mostrar v8.0.0 o superior
```

### Paso 2: Configuración de Base de Datos

#### 2.1 MongoDB Atlas (Recomendado)

1. **Crear cuenta en MongoDB Atlas**
   - Ir a [https://www.mongodb.com/atlas](https://www.mongodb.com/atlas)
   - Registrarse con email o Google/GitHub
   - Verificar email

2. **Crear cluster gratuito**
   ```
   - Seleccionar "Build a Database"
   - Elegir "M0 Sandbox" (gratuito)
   - Seleccionar región más cercana
   - Nombrar cluster: "evidence-management"
   ```

3. **Configurar acceso**
   ```
   - Database Access → Add New Database User
   - Username: admin
   - Password: [generar contraseña segura]
   - Database User Privileges: Atlas admin
   
   - Network Access → Add IP Address
   - Seleccionar "Allow access from anywhere" (0.0.0.0/0)
   ```

4. **Obtener cadena de conexión**
   ```
   - Clusters → Connect → Connect your application
   - Driver: Node.js, Version: 4.1 or later
   - Copiar connection string
   ```

#### 2.2 MongoDB Local (Alternativa)

**Windows:**
```powershell
# Descargar MongoDB Community Server
# https://www.mongodb.com/try/download/community

# Instalar como servicio
mongod --install --serviceName "MongoDB" --serviceDisplayName "MongoDB" --dbpath "C:\data\db"

# Iniciar servicio
net start MongoDB
```

**macOS:**
```bash
# Usando Homebrew
brew tap mongodb/brew
brew install mongodb-community
brew services start mongodb/brew/mongodb-community
```

**Linux:**
```bash
# Ubuntu/Debian
wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | sudo apt-key add -
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/6.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-6.0.list
sudo apt-get update
sudo apt-get install -y mongodb-org
sudo systemctl start mongod
sudo systemctl enable mongod
```

#### 2.3 MySQL/XAMPP

**Opción A: XAMPP (Recomendado para desarrollo)**
```bash
# Windows: Descargar desde https://www.apachefriends.org/
# macOS: brew install --cask xampp
# Linux: Descargar .run file y ejecutar

# Iniciar XAMPP Control Panel
# Activar Apache y MySQL
```

**Opción B: MySQL Standalone**
```bash
# Windows: Descargar MySQL Installer
# macOS: brew install mysql
# Linux: sudo apt-get install mysql-server
```

### Paso 3: Clonación y Configuración del Proyecto

#### 3.1 Clonar Repositorio

```bash
# HTTPS
git clone https://github.com/Andrewgo12/reportes.git

# SSH (si tienes configurado)
git clone git@github.com:Andrewgo12/reportes.git

# Entrar al directorio
cd reportes
```

#### 3.2 Estructura de Directorios

```
reportes/
├── frontend/              # Aplicación React
├── backend/              # API Node.js
├── docs/                 # Documentación
├── public/               # Archivos estáticos
├── package.json          # Dependencias frontend
└── README.md            # Documentación principal
```

#### 3.3 Instalación de Dependencias

```bash
# Dependencias del frontend (desde raíz)
npm install

# Dependencias del backend
cd backend
npm install

# Volver a la raíz
cd ..
```

### Paso 4: Configuración de Variables de Entorno

#### 4.1 Frontend (.env.local)

```bash
# Copiar archivo de ejemplo
cp .env.example .env.local

# Editar variables
nano .env.local  # o usar tu editor preferido
```

```env
# Frontend Configuration
NEXT_PUBLIC_API_URL=http://localhost:5001/api/v1
NEXT_PUBLIC_APP_NAME=Sistema de Gestión de Evidencias
NEXT_PUBLIC_APP_VERSION=1.0.0
NEXT_PUBLIC_ENVIRONMENT=development
```

#### 4.2 Backend (.env)

```bash
# Entrar al directorio backend
cd backend

# Copiar archivo de ejemplo
cp .env.example .env

# Editar variables
nano .env
```

```env
# Configuración del Servidor
NODE_ENV=development
PORT=5001
API_VERSION=v1

# MongoDB Atlas
MONGODB_URI=mongodb+srv://admin:PASSWORD@cluster0.xxxxx.mongodb.net/evidence_management?retryWrites=true&w=majority

# MySQL/XAMPP
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_DATABASE=evidence_management_mysql
MYSQL_USERNAME=root
MYSQL_PASSWORD=

# JWT
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_REFRESH_SECRET=your-super-secret-refresh-key-change-this-in-production
JWT_EXPIRE=24h
JWT_REFRESH_EXPIRE=7d

# Archivos
MAX_FILE_SIZE=2147483648
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,mp4,avi,mov,mp3,wav

# Seguridad
BCRYPT_ROUNDS=12
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100

# CORS
FRONTEND_URL=http://localhost:3000
CORS_ORIGINS=http://localhost:3000,http://localhost:3001
```

### Paso 5: Inicialización de Base de Datos

#### 5.1 Ejecutar Script de Inicialización

```bash
# Desde el directorio backend
cd backend

# Inicializar ambas bases de datos
node database/init-databases.js
```

#### 5.2 Verificar Conexiones

```bash
# Verificar estado de las bases de datos
node -e "
const { getDatabaseStatus } = require('./config/database');
const { initializeDatabases } = require('./config/database');

initializeDatabases().then(() => {
  console.log('Estado de las bases de datos:');
  console.log(getDatabaseStatus());
  process.exit(0);
});
"
```

### Paso 6: Iniciar la Aplicación

#### 6.1 Modo Desarrollo

```bash
# Opción 1: Iniciar frontend y backend por separado

# Terminal 1: Backend
cd backend
npm run dev

# Terminal 2: Frontend (nueva terminal, desde raíz)
npm run dev
```

```bash
# Opción 2: Iniciar ambos simultáneamente (si está configurado)
npm run dev:full
```

#### 6.2 Verificar que Todo Funciona

1. **Frontend**: [http://localhost:3000](http://localhost:3000)
2. **Backend API**: [http://localhost:5001/api/v1](http://localhost:5001/api/v1)
3. **Health Check**: [http://localhost:5001/health](http://localhost:5001/health)
4. **API Docs**: [http://localhost:5001/api-docs](http://localhost:5001/api-docs)

## ✅ Verificación de Instalación

### Checklist de Verificación

```bash
# 1. Verificar Node.js y npm
node --version && npm --version

# 2. Verificar dependencias instaladas
npm list --depth=0
cd backend && npm list --depth=0

# 3. Verificar conexión a bases de datos
cd backend
curl http://localhost:5001/health

# 4. Verificar frontend
curl http://localhost:3000

# 5. Verificar API
curl http://localhost:5001/api/v1/auth/health
```

### Credenciales de Prueba

Una vez instalado, puedes usar estas credenciales para probar:

**Administrador:**
- Email: `admin@company.com`
- Password: `admin123`

**Usuario Regular:**
- Email: `user@company.com`
- Password: `user123`

## 🔧 Troubleshooting

### Problemas Comunes

#### 1. Error: "Cannot find module"

```bash
# Solución: Reinstalar dependencias
rm -rf node_modules package-lock.json
npm install

# Para backend
cd backend
rm -rf node_modules package-lock.json
npm install
```

#### 2. Error de conexión a MongoDB

```bash
# Verificar URI de conexión
echo $MONGODB_URI

# Probar conexión manual
node -e "
const mongoose = require('mongoose');
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/evidence_management')
  .then(() => console.log('✅ MongoDB conectado'))
  .catch(err => console.error('❌ Error MongoDB:', err.message));
"
```

#### 3. Error de conexión a MySQL

```bash
# Verificar que MySQL esté ejecutándose
# Windows (XAMPP): Abrir XAMPP Control Panel
# macOS: brew services list | grep mysql
# Linux: sudo systemctl status mysql

# Probar conexión
mysql -h localhost -u root -p -e "SHOW DATABASES;"
```

#### 4. Puerto ya en uso

```bash
# Encontrar proceso usando el puerto
# Windows: netstat -ano | findstr :3000
# macOS/Linux: lsof -i :3000

# Matar proceso
# Windows: taskkill /PID <PID> /F
# macOS/Linux: kill -9 <PID>

# O cambiar puerto en .env
PORT=3001
```

#### 5. Permisos de archivos (Linux/macOS)

```bash
# Dar permisos de ejecución
chmod +x install.sh

# Cambiar propietario de archivos
sudo chown -R $USER:$USER .

# Permisos para carpeta de uploads
mkdir -p backend/uploads
chmod 755 backend/uploads
```

### Logs de Depuración

```bash
# Ver logs del backend
cd backend
npm run dev -- --verbose

# Ver logs de MongoDB (si es local)
tail -f /var/log/mongodb/mongod.log

# Ver logs de MySQL
tail -f /var/log/mysql/error.log
```

### Reinstalación Completa

```bash
# Limpiar todo y empezar de nuevo
rm -rf node_modules backend/node_modules
rm package-lock.json backend/package-lock.json
npm cache clean --force

# Reinstalar
npm install
cd backend && npm install

# Reinicializar base de datos
node database/init-databases.js
```

## 📞 Soporte

Si tienes problemas durante la instalación:

1. **Revisa los logs** de error detalladamente
2. **Consulta la documentación** completa
3. **Busca en Issues** del repositorio
4. **Crea un nuevo Issue** con:
   - Sistema operativo y versión
   - Versiones de Node.js y npm
   - Logs de error completos
   - Pasos que llevaron al error

**Contacto:**
- 📧 Email: support@evidence-platform.com
- 🐛 Issues: [GitHub Issues](https://github.com/Andrewgo12/reportes/issues)
- 📖 Wiki: [Documentación Completa](../DOCUMENTACION_COMPLETA.md)

---

¡Felicidades! 🎉 Si llegaste hasta aquí, tu instalación debería estar completa y funcionando.
