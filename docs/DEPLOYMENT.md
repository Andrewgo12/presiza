# 🚀 Guía de Despliegue - Sistema de Gestión de Evidencias

## 📋 Tabla de Contenidos

1. [Preparación para Producción](#preparación-para-producción)
2. [Despliegue en VPS/Servidor Dedicado](#despliegue-en-vpsservidor-dedicado)
3. [Despliegue en la Nube](#despliegue-en-la-nube)
4. [Despliegue con Docker](#despliegue-con-docker)
5. [Configuración de Dominio y SSL](#configuración-de-dominio-y-ssl)
6. [Monitoreo y Mantenimiento](#monitoreo-y-mantenimiento)
7. [Backup y Recuperación](#backup-y-recuperación)

## 🔧 Preparación para Producción

### Checklist Pre-Despliegue

- [ ] **Código probado** en entorno de desarrollo
- [ ] **Tests pasando** (npm test)
- [ ] **Variables de entorno** configuradas para producción
- [ ] **Base de datos** configurada y migrada
- [ ] **Archivos estáticos** optimizados
- [ ] **Certificados SSL** obtenidos
- [ ] **Dominio** configurado
- [ ] **Monitoreo** configurado

### Optimización para Producción

#### 1. Build del Frontend

```bash
# Desde la raíz del proyecto
npm run build

# Verificar que se generó la carpeta dist/
ls -la dist/
```

#### 2. Optimización del Backend

```bash
# Instalar solo dependencias de producción
cd backend
npm ci --only=production

# Verificar que no hay vulnerabilidades
npm audit
npm audit fix
```

#### 3. Variables de Entorno de Producción

```env
# backend/.env (PRODUCCIÓN)
NODE_ENV=production
PORT=5001
API_VERSION=v1

# Base de Datos (usar URLs de producción)
MONGODB_URI=mongodb+srv://prod_user:secure_password@cluster-prod.xxxxx.mongodb.net/evidence_management_prod?retryWrites=true&w=majority
MYSQL_HOST=your-mysql-server.com
MYSQL_DATABASE=evidence_management_prod
MYSQL_USERNAME=prod_user
MYSQL_PASSWORD=secure_mysql_password

# JWT (usar claves seguras)
JWT_SECRET=super-secure-jwt-secret-for-production-256-bits-long
JWT_REFRESH_SECRET=super-secure-refresh-secret-for-production-256-bits-long

# Archivos (configurar para producción)
MAX_FILE_SIZE=2147483648
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_S3_BUCKET=evidence-management-prod-files

# Email (configurar SMTP real)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your-app-password

# Seguridad
BCRYPT_ROUNDS=12
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100

# URLs de producción
FRONTEND_URL=https://yourdomain.com
CORS_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
```

## 🖥️ Despliegue en VPS/Servidor Dedicado

### Opción 1: Ubuntu Server 20.04/22.04

#### 1.1 Preparación del Servidor

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias básicas
sudo apt install -y curl wget git nginx certbot python3-certbot-nginx

# Instalar Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar PM2 (Process Manager)
sudo npm install -g pm2

# Instalar MongoDB
wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | sudo apt-key add -
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/6.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-6.0.list
sudo apt-get update
sudo apt-get install -y mongodb-org

# Instalar MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

#### 1.2 Configuración de Usuario

```bash
# Crear usuario para la aplicación
sudo adduser evidence-app
sudo usermod -aG sudo evidence-app

# Cambiar a usuario de aplicación
sudo su - evidence-app

# Configurar SSH keys (opcional pero recomendado)
mkdir ~/.ssh
chmod 700 ~/.ssh
# Copiar tu clave pública a ~/.ssh/authorized_keys
```

#### 1.3 Despliegue de la Aplicación

```bash
# Clonar repositorio
git clone https://github.com/Andrewgo12/reportes.git
cd reportes

# Instalar dependencias
npm install
cd backend && npm install --only=production

# Configurar variables de entorno
cp backend/.env.example backend/.env
# Editar backend/.env con valores de producción

# Build del frontend
cd ..
npm run build

# Inicializar base de datos
cd backend
node database/init-databases.js
```

#### 1.4 Configuración de PM2

```bash
# Crear archivo de configuración PM2
cat > ecosystem.config.js << EOF
module.exports = {
  apps: [{
    name: 'evidence-api',
    script: './backend/server.js',
    instances: 'max',
    exec_mode: 'cluster',
    env: {
      NODE_ENV: 'development'
    },
    env_production: {
      NODE_ENV: 'production',
      PORT: 5001
    },
    error_file: './logs/err.log',
    out_file: './logs/out.log',
    log_file: './logs/combined.log',
    time: true
  }]
};
EOF

# Crear directorio de logs
mkdir logs

# Iniciar aplicación con PM2
pm2 start ecosystem.config.js --env production

# Configurar PM2 para iniciar al boot
pm2 startup
pm2 save
```

#### 1.5 Configuración de Nginx

```bash
# Crear configuración de Nginx
sudo tee /etc/nginx/sites-available/evidence-management << EOF
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    # Frontend (archivos estáticos)
    location / {
        root /home/evidence-app/reportes/dist;
        try_files \$uri \$uri/ /index.html;
        
        # Headers de seguridad
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header Referrer-Policy "no-referrer-when-downgrade" always;
        add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    }

    # API Backend
    location /api/ {
        proxy_pass http://localhost:5001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    # WebSocket para Socket.IO
    location /socket.io/ {
        proxy_pass http://localhost:5001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    # Archivos estáticos con cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        root /home/evidence-app/reportes/dist;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Logs
    access_log /var/log/nginx/evidence-management.access.log;
    error_log /var/log/nginx/evidence-management.error.log;
}
EOF

# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/evidence-management /etc/nginx/sites-enabled/

# Probar configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

### Opción 2: CentOS/RHEL 8

```bash
# Actualizar sistema
sudo dnf update -y

# Instalar EPEL
sudo dnf install -y epel-release

# Instalar dependencias
sudo dnf install -y curl wget git nginx certbot python3-certbot-nginx

# Instalar Node.js
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo dnf install -y nodejs

# El resto es similar a Ubuntu, ajustando comandos específicos de CentOS
```

## ☁️ Despliegue en la Nube

### AWS (Amazon Web Services)

#### 1. EC2 + RDS + S3

```bash
# 1. Crear instancia EC2
# - AMI: Ubuntu Server 20.04 LTS
# - Tipo: t3.medium (2 vCPU, 4 GB RAM)
# - Almacenamiento: 20 GB SSD
# - Security Group: HTTP (80), HTTPS (443), SSH (22), Custom (5001)

# 2. Configurar RDS para MySQL
# - Engine: MySQL 8.0
# - Clase: db.t3.micro
# - Almacenamiento: 20 GB
# - Multi-AZ: No (para desarrollo)

# 3. Configurar S3 para archivos
# - Bucket: evidence-management-files-prod
# - Región: us-east-1
# - Versioning: Habilitado
# - Encryption: AES-256
```

#### 2. Elastic Beanstalk (Más Simple)

```bash
# Instalar EB CLI
pip install awsebcli

# Inicializar aplicación
eb init evidence-management

# Crear entorno
eb create production

# Desplegar
eb deploy
```

### Google Cloud Platform

#### 1. App Engine

```yaml
# app.yaml
runtime: nodejs18

env_variables:
  NODE_ENV: production
  MONGODB_URI: mongodb+srv://...
  JWT_SECRET: your-secret

automatic_scaling:
  min_instances: 1
  max_instances: 10
```

```bash
# Desplegar
gcloud app deploy
```

### Microsoft Azure

#### 1. App Service

```bash
# Crear grupo de recursos
az group create --name evidence-management --location "East US"

# Crear plan de App Service
az appservice plan create --name evidence-plan --resource-group evidence-management --sku B1 --is-linux

# Crear Web App
az webapp create --resource-group evidence-management --plan evidence-plan --name evidence-management-app --runtime "NODE|18-lts"

# Configurar variables de entorno
az webapp config appsettings set --resource-group evidence-management --name evidence-management-app --settings NODE_ENV=production

# Desplegar desde Git
az webapp deployment source config --resource-group evidence-management --name evidence-management-app --repo-url https://github.com/Andrewgo12/reportes --branch main
```

### Vercel (Frontend) + Railway/Render (Backend)

#### Frontend en Vercel

```bash
# Instalar Vercel CLI
npm i -g vercel

# Desplegar
vercel --prod
```

#### Backend en Railway

```bash
# Instalar Railway CLI
npm install -g @railway/cli

# Login y desplegar
railway login
railway init
railway up
```

## 🐳 Despliegue con Docker

### Dockerfile para Backend

```dockerfile
# backend/Dockerfile
FROM node:18-alpine

WORKDIR /app

# Copiar package files
COPY package*.json ./
RUN npm ci --only=production

# Copiar código fuente
COPY . .

# Crear usuario no-root
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nodejs -u 1001

# Cambiar permisos
RUN chown -R nodejs:nodejs /app
USER nodejs

EXPOSE 5001

CMD ["node", "server.js"]
```

### Dockerfile para Frontend

```dockerfile
# Dockerfile
FROM node:18-alpine AS builder

WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM nginx:alpine
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

### Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  frontend:
    build: .
    ports:
      - "80:80"
    depends_on:
      - backend

  backend:
    build: ./backend
    ports:
      - "5001:5001"
    environment:
      - NODE_ENV=production
      - MONGODB_URI=${MONGODB_URI}
      - MYSQL_HOST=mysql
      - MYSQL_DATABASE=evidence_management
      - MYSQL_USERNAME=root
      - MYSQL_PASSWORD=${MYSQL_PASSWORD}
    depends_on:
      - mongodb
      - mysql

  mongodb:
    image: mongo:6.0
    ports:
      - "27017:27017"
    volumes:
      - mongodb_data:/data/db
    environment:
      - MONGO_INITDB_ROOT_USERNAME=${MONGO_USERNAME}
      - MONGO_INITDB_ROOT_PASSWORD=${MONGO_PASSWORD}

  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=${MYSQL_PASSWORD}
      - MYSQL_DATABASE=evidence_management

volumes:
  mongodb_data:
  mysql_data:
```

### Comandos Docker

```bash
# Build y ejecutar
docker-compose up -d

# Ver logs
docker-compose logs -f

# Escalar servicios
docker-compose up -d --scale backend=3

# Actualizar
docker-compose pull
docker-compose up -d
```

## 🔒 Configuración de Dominio y SSL

### 1. Configurar DNS

```
# Registros DNS necesarios
A     yourdomain.com        -> IP_DEL_SERVIDOR
A     www.yourdomain.com    -> IP_DEL_SERVIDOR
CNAME api.yourdomain.com    -> yourdomain.com
```

### 2. Obtener Certificado SSL con Let's Encrypt

```bash
# Obtener certificado
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Verificar renovación automática
sudo certbot renew --dry-run

# Configurar cron para renovación
sudo crontab -e
# Añadir: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 3. Configuración Nginx con SSL

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Rest of configuration...
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

## 📊 Monitoreo y Mantenimiento

### 1. Configurar Monitoreo con PM2

```bash
# Instalar PM2 Plus (opcional)
pm2 install pm2-server-monit

# Ver métricas
pm2 monit

# Logs en tiempo real
pm2 logs

# Reiniciar aplicación
pm2 restart evidence-api

# Recargar sin downtime
pm2 reload evidence-api
```

### 2. Configurar Logs

```bash
# Configurar logrotate
sudo tee /etc/logrotate.d/evidence-management << EOF
/home/evidence-app/reportes/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 evidence-app evidence-app
    postrotate
        pm2 reloadLogs
    endscript
}
EOF
```

### 3. Monitoreo de Sistema

```bash
# Instalar htop para monitoreo
sudo apt install htop

# Monitorear recursos
htop

# Monitorear espacio en disco
df -h

# Monitorear memoria
free -h

# Monitorear procesos de Node.js
ps aux | grep node
```

## 💾 Backup y Recuperación

### 1. Backup de MongoDB

```bash
# Script de backup
cat > backup-mongodb.sh << EOF
#!/bin/bash
DATE=\$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/evidence-app/backups/mongodb"
mkdir -p \$BACKUP_DIR

mongodump --uri="\$MONGODB_URI" --out=\$BACKUP_DIR/backup_\$DATE

# Comprimir backup
tar -czf \$BACKUP_DIR/backup_\$DATE.tar.gz -C \$BACKUP_DIR backup_\$DATE
rm -rf \$BACKUP_DIR/backup_\$DATE

# Mantener solo últimos 7 backups
find \$BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
EOF

chmod +x backup-mongodb.sh

# Configurar cron para backup diario
crontab -e
# Añadir: 0 2 * * * /home/evidence-app/backup-mongodb.sh
```

### 2. Backup de MySQL

```bash
# Script de backup MySQL
cat > backup-mysql.sh << EOF
#!/bin/bash
DATE=\$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/evidence-app/backups/mysql"
mkdir -p \$BACKUP_DIR

mysqldump -h \$MYSQL_HOST -u \$MYSQL_USERNAME -p\$MYSQL_PASSWORD evidence_management_prod > \$BACKUP_DIR/backup_\$DATE.sql

# Comprimir
gzip \$BACKUP_DIR/backup_\$DATE.sql

# Mantener solo últimos 7 backups
find \$BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
EOF

chmod +x backup-mysql.sh
```

### 3. Backup de Archivos

```bash
# Backup de uploads y configuración
cat > backup-files.sh << EOF
#!/bin/bash
DATE=\$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/evidence-app/backups/files"
mkdir -p \$BACKUP_DIR

# Backup de uploads
tar -czf \$BACKUP_DIR/uploads_\$DATE.tar.gz -C /home/evidence-app/reportes/backend uploads/

# Backup de configuración
tar -czf \$BACKUP_DIR/config_\$DATE.tar.gz -C /home/evidence-app/reportes backend/.env ecosystem.config.js

# Mantener solo últimos 30 backups
find \$BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
EOF

chmod +x backup-files.sh
```

## 🔧 Troubleshooting de Producción

### Problemas Comunes

1. **Aplicación no responde**
   ```bash
   pm2 restart evidence-api
   pm2 logs evidence-api --lines 100
   ```

2. **Error 502 Bad Gateway**
   ```bash
   sudo nginx -t
   sudo systemctl restart nginx
   pm2 status
   ```

3. **Base de datos desconectada**
   ```bash
   # MongoDB
   sudo systemctl status mongod
   
   # MySQL
   sudo systemctl status mysql
   ```

4. **Espacio en disco lleno**
   ```bash
   df -h
   sudo du -sh /var/log/*
   sudo journalctl --vacuum-time=7d
   ```

### Comandos de Emergencia

```bash
# Reiniciar todos los servicios
sudo systemctl restart nginx
pm2 restart all
sudo systemctl restart mongod
sudo systemctl restart mysql

# Ver logs de sistema
sudo journalctl -f

# Monitorear recursos en tiempo real
htop
iotop
```

---

## 📞 Soporte Post-Despliegue

Una vez desplegado, mantén:

1. **Monitoreo continuo** de logs y métricas
2. **Backups regulares** automatizados
3. **Actualizaciones de seguridad** del sistema
4. **Certificados SSL** renovados automáticamente
5. **Documentación** de cambios y configuraciones

**Contacto para soporte:**
- 📧 Email: support@evidence-platform.com
- 🐛 Issues: [GitHub Issues](https://github.com/Andrewgo12/reportes/issues)
- 📖 Docs: [Documentación Completa](../DOCUMENTACION_COMPLETA.md)
