# 🗄️ Guía de Configuración de Bases de Datos Híbridas

## 📋 Resumen

El Sistema de Gestión de Evidencias ahora soporta **configuración híbrida** con dos bases de datos:

1. **MongoDB Atlas** (Principal) - Para datos principales del sistema
2. **MySQL/XAMPP** (Secundaria) - Para auditoría, analytics y logs

## 🚀 **CONFIGURACIÓN COMPLETADA**

### ✅ **Archivos Implementados**

1. **`backend/config/database.js`** - Configuración híbrida de bases de datos
2. **`backend/models/mysql/AuditLog.js`** - Modelo MySQL para auditoría
3. **`backend/models/mysql/Analytics.js`** - Modelo MySQL para métricas
4. **`backend/.env.example`** - Variables de entorno actualizadas
5. **`backend/server.js`** - Servidor actualizado para ambas BD

### ✅ **Dependencias Instaladas**
- `mysql2` - Driver MySQL para Node.js
- `sequelize` - ORM para MySQL

## 🔧 **CONFIGURACIÓN PASO A PASO**

### **1. MongoDB Atlas (Base de Datos Principal)**

#### Crear Cuenta y Cluster
1. Ve a [MongoDB Atlas](https://www.mongodb.com/atlas)
2. Crea una cuenta gratuita
3. Crea un nuevo cluster (M0 Sandbox - Gratis)
4. Configura usuario y contraseña
5. Agrega tu IP a la whitelist

#### Obtener String de Conexión
1. En Atlas, ve a "Connect" → "Connect your application"
2. Copia el string de conexión
3. Reemplaza `<password>` con tu contraseña real

#### Configurar en .env
```env
# En backend/.env
MONGODB_URI=mongodb+srv://tu_usuario:tu_password@cluster0.xxxxx.mongodb.net/evidence_management?retryWrites=true&w=majority
```

### **2. MySQL/XAMPP (Base de Datos Secundaria)**

#### Instalar XAMPP
1. Descarga [XAMPP](https://www.apachefriends.org/)
2. Instala XAMPP
3. Inicia Apache y MySQL desde el panel de control

#### Crear Base de Datos
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Crea nueva base de datos: `evidence_management_mysql`
3. Configura usuario (opcional)

#### Configurar en .env
```env
# En backend/.env
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_DATABASE=evidence_management_mysql
MYSQL_USERNAME=root
MYSQL_PASSWORD=
```

## 📊 **DISTRIBUCIÓN DE DATOS**

### **MongoDB Atlas (Principal)**
- ✅ **Usuarios** - Información de usuarios y autenticación
- ✅ **Archivos** - Metadatos de archivos subidos
- ✅ **Grupos** - Grupos de colaboración
- ✅ **Mensajes** - Sistema de mensajería
- ✅ **Evidencias** - Datos de evidencias

### **MySQL/XAMPP (Secundaria)**
- ✅ **Audit Logs** - Registro de todas las acciones del sistema
- ✅ **Analytics** - Métricas y estadísticas detalladas
- ✅ **Performance Logs** - Logs de rendimiento
- ✅ **Reports Cache** - Cache de reportes generados

## 🔍 **VERIFICACIÓN DE CONEXIÓN**

### **Endpoints de Verificación**
```bash
# Estado general del servidor
curl http://localhost:5001/health

# Estado específico de bases de datos
curl http://localhost:5001/api/v1/database/status
```

### **Respuesta Esperada**
```json
{
  "status": "OK",
  "databases": {
    "mongodb": {
      "connected": true,
      "type": "MongoDB Atlas"
    },
    "mysql": {
      "connected": true,
      "type": "MySQL/XAMPP"
    }
  }
}
```

## 🛠️ **COMANDOS DE DESARROLLO**

### **Iniciar Servidor**
```bash
cd backend
node server.js
```

### **Verificar Conexiones**
```bash
# Test MongoDB
node -e "require('./config/database').connectMongoDB()"

# Test MySQL
node -e "require('./config/database').connectMySQL()"
```

### **Sincronizar Tablas MySQL**
```bash
# Las tablas se crean automáticamente en desarrollo
# Para forzar sincronización:
NODE_ENV=development node server.js
```

## 📈 **VENTAJAS DE LA CONFIGURACIÓN HÍBRIDA**

### **MongoDB Atlas**
- ✅ **Escalabilidad** - Escala automáticamente
- ✅ **Disponibilidad** - 99.995% uptime
- ✅ **Seguridad** - Encriptación automática
- ✅ **Backups** - Backups automáticos
- ✅ **Global** - Disponible mundialmente

### **MySQL/XAMPP**
- ✅ **Rendimiento** - Excelente para analytics
- ✅ **Consultas Complejas** - SQL avanzado
- ✅ **Reportes** - Ideal para reporting
- ✅ **Auditoría** - Logs detallados
- ✅ **Local** - Control total de datos

## 🔒 **SEGURIDAD**

### **MongoDB Atlas**
- Autenticación por usuario/contraseña
- Whitelist de IPs
- Encriptación en tránsito y reposo
- Auditoría de accesos

### **MySQL/XAMPP**
- Configuración de usuarios específicos
- Restricción de acceso por IP
- Encriptación de conexiones SSL
- Logs de auditoría detallados

## 🚨 **TROUBLESHOOTING**

### **MongoDB Atlas No Conecta**
1. Verifica el string de conexión
2. Revisa la whitelist de IPs
3. Confirma usuario y contraseña
4. Verifica conectividad a internet

### **MySQL/XAMPP No Conecta**
1. Verifica que XAMPP esté ejecutándose
2. Confirma que MySQL esté iniciado
3. Revisa puerto 3306 disponible
4. Verifica credenciales de base de datos

### **Logs de Debug**
```bash
# Ver logs detallados
DEBUG=* node server.js

# Solo logs de base de datos
DEBUG=database:* node server.js
```

## 🎯 **PRÓXIMOS PASOS**

1. **Configurar MongoDB Atlas** con tus credenciales reales
2. **Instalar y configurar XAMPP**
3. **Probar conexiones** con los endpoints de verificación
4. **Ejecutar el servidor** y verificar ambas conexiones
5. **Probar funcionalidades** del frontend con backend real

## 📞 **Soporte**

Si encuentras problemas:
1. Revisa los logs del servidor
2. Verifica las configuraciones de .env
3. Confirma que ambos servicios estén ejecutándose
4. Consulta la documentación oficial de MongoDB Atlas y XAMPP

---

**¡La configuración híbrida está lista! Ahora tienes lo mejor de ambos mundos: la escalabilidad de MongoDB Atlas y el poder de análisis de MySQL.** 🚀
