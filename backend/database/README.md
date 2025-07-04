# 📊 **DATABASE SCHEMAS - EVIDENCE MANAGEMENT SYSTEM**

## 🏗️ **ARQUITECTURA HÍBRIDA**

Este sistema utiliza una arquitectura híbrida con dos bases de datos:

- **MongoDB Atlas** (Principal): Usuarios, archivos, grupos, mensajes, evidencias, notificaciones
- **MySQL/XAMPP** (Secundaria): Auditoría, analytics, logs, métricas de rendimiento, sesiones

## 📁 **ARCHIVOS INCLUIDOS**

### **1. mysql_schema.sql**
- **Propósito**: Schema completo para MySQL/XAMPP
- **Contiene**: 10 tablas principales con datos de ejemplo
- **Uso**: Auditoría, analytics, logs del sistema

### **2. mongodb_schema.js**
- **Propósito**: Schemas de Mongoose para MongoDB
- **Contiene**: 6 colecciones principales con validaciones
- **Uso**: Datos principales del sistema

### **3. init-databases.js**
- **Propósito**: Script de inicialización automática
- **Contiene**: Funciones para poblar ambas bases de datos
- **Uso**: Configuración inicial del sistema

## 🚀 **INSTALACIÓN Y CONFIGURACIÓN**

### **Paso 1: Configurar MySQL/XAMPP**

1. **Instalar XAMPP** (si no está instalado)
2. **Iniciar MySQL** desde el panel de XAMPP
3. **Ejecutar el schema SQL**:
   ```bash
   # Opción 1: Desde línea de comandos
   mysql -u root -p < backend/database/mysql_schema.sql
   
   # Opción 2: Desde phpMyAdmin
   # - Abrir http://localhost/phpmyadmin
   # - Importar el archivo mysql_schema.sql
   ```

### **Paso 2: Configurar MongoDB**

**Opción A: MongoDB Atlas (Recomendado)**
1. Crear cuenta en [MongoDB Atlas](https://www.mongodb.com/atlas)
2. Crear un cluster gratuito
3. Obtener la cadena de conexión
4. Actualizar `.env`:
   ```env
   MONGODB_URI=mongodb+srv://username:password@cluster0.xxxxx.mongodb.net/evidence_management?retryWrites=true&w=majority
   ```

**Opción B: MongoDB Local**
1. Instalar MongoDB Community Edition
2. Iniciar el servicio MongoDB
3. Usar la configuración por defecto:
   ```env
   MONGODB_URI=mongodb://localhost:27017/evidence_management
   ```

### **Paso 3: Ejecutar Inicialización**

```bash
# Desde el directorio backend
cd backend
node database/init-databases.js
```

## 📋 **ESTRUCTURA DE DATOS**

### **🍃 MONGODB COLLECTIONS**

#### **1. users**
```javascript
{
  email: "admin@test.com",
  password: "hashed_password",
  firstName: "Admin",
  lastName: "User",
  role: "admin", // admin, user, analyst, investigator
  department: "IT",
  position: "System Administrator",
  isActive: true,
  notificationSettings: { email: true, push: true },
  privacySettings: { profileVisible: true }
}
```

#### **2. files**
```javascript
{
  filename: "document.pdf",
  originalName: "Research Document.pdf",
  mimeType: "application/pdf",
  size: 1024000,
  path: "/uploads/2024/01/document.pdf",
  uploadedBy: ObjectId("..."),
  category: "document", // document, image, video, audio
  accessLevel: "internal", // public, internal, restricted
  status: "active"
}
```

#### **3. groups**
```javascript
{
  name: "Research Team Alpha",
  description: "Equipo principal de investigación",
  type: "public", // public, private, protected
  category: "research", // project, department, team
  members: [
    {
      user: ObjectId("..."),
      role: "owner", // owner, admin, moderator, member
      joinedAt: Date,
      permissions: { canInvite: true }
    }
  ]
}
```

#### **4. messages**
```javascript
{
  content: "Mensaje de texto",
  sender: ObjectId("..."),
  recipient: ObjectId("..."),
  recipientType: "User", // User, Group
  messageType: "text", // text, file, image, system
  status: "sent", // sent, delivered, read
  attachments: [...]
}
```

#### **5. evidences**
```javascript
{
  title: "Q4 Research Analysis",
  description: "Análisis completo de datos Q4",
  evidenceType: "document", // document, image, video, data
  category: "research", // investigation, research, audit
  submittedBy: ObjectId("..."),
  status: "pending", // pending, under_review, approved, rejected
  priority: "high", // low, medium, high, critical
  files: [ObjectId("...")],
  comments: [...]
}
```

#### **6. notifications**
```javascript
{
  title: "File Upload Approved",
  message: "Tu documento ha sido aprobado",
  recipient: ObjectId("..."),
  type: "success", // info, success, warning, error
  category: "upload", // upload, comment, task, system
  isRead: false,
  actionUrl: "/evidences"
}
```

### **🐬 MYSQL TABLES**

#### **1. audit_logs**
```sql
id, user_id, user_email, action, resource, resource_id,
ip_address, user_agent, details (JSON), success, timestamp
```

#### **2. analytics**
```sql
id, date, metric_type, metric_name, value, additional_data (JSON)
```

#### **3. system_logs**
```sql
id, level, message, component, stack_trace, metadata (JSON), timestamp
```

#### **4. performance_metrics**
```sql
id, endpoint, method, response_time, status_code, user_id, timestamp
```

#### **5. user_sessions**
```sql
id, session_id, user_id, user_email, ip_address, login_at, 
logout_at, status, expires_at
```

## 🔑 **CREDENCIALES DE DESARROLLO**

### **Usuarios Predefinidos**

| Email | Password | Role | Descripción |
|-------|----------|------|-------------|
| admin@test.com | admin123 | admin | Administrador del sistema |
| user@test.com | user123 | user | Usuario regular |
| analyst@test.com | analyst123 | analyst | Analista de datos |
| investigator@test.com | investigator123 | investigator | Investigador |

### **Grupos de Ejemplo**

- **Research Team Alpha**: Grupo público de investigación
- **Development Squad**: Grupo privado de desarrollo
- **Design Collective**: Grupo protegido de diseño

## 🔧 **COMANDOS ÚTILES**

### **Verificar Estado de Bases de Datos**
```bash
# Health check del backend
curl http://localhost:5002/health

# Verificar conexiones
node -e "
const { getDatabaseStatus } = require('./config/database');
console.log(getDatabaseStatus());
"
```

### **Limpiar y Reinicializar**
```bash
# Limpiar MongoDB (cuidado: elimina todos los datos)
node -e "
const mongoose = require('mongoose');
mongoose.connect(process.env.MONGODB_URI);
mongoose.connection.dropDatabase();
"

# Limpiar MySQL (cuidado: elimina todos los datos)
mysql -u root -p -e "DROP DATABASE IF EXISTS evidence_management_mysql;"
```

### **Backup de Datos**
```bash
# Backup MongoDB
mongodump --uri="mongodb://localhost:27017/evidence_management" --out=./backup/

# Backup MySQL
mysqldump -u root -p evidence_management_mysql > backup/mysql_backup.sql
```

## 📊 **SOPORTE PARA VISTAS DEL FRONTEND**

### **Vistas Soportadas por MongoDB**
- ✅ LoginView (users)
- ✅ ProfileView (users)
- ✅ FilesView (files)
- ✅ UploadView (files)
- ✅ GroupsView (groups)
- ✅ AdminGroupsView (groups)
- ✅ MessagesView (messages)
- ✅ EvidencesView (evidences)
- ✅ NotificationsView (notifications)

### **Vistas Soportadas por MySQL**
- ✅ AnalyticsView (analytics, audit_logs)
- ✅ AdminLogsView (system_logs, audit_logs)
- ✅ HomeView (analytics, performance_metrics)
- ✅ TasksView (audit_logs para tracking)

### **Vistas Híbridas (Ambas Bases de Datos)**
- ✅ HomeView: Datos de usuarios (MongoDB) + Analytics (MySQL)
- ✅ AnalyticsView: Métricas (MySQL) + Conteos (MongoDB)
- ✅ AdminLogsView: Logs (MySQL) + Información de usuarios (MongoDB)

## 🛠️ **MANTENIMIENTO**

### **Limpieza Automática de Logs**
```sql
-- Ejecutar mensualmente
CALL CleanOldLogs(90); -- Mantener logs de 90 días
```

### **Optimización de Índices**
```javascript
// MongoDB - Recrear índices si es necesario
db.users.reIndex();
db.files.reIndex();
db.evidences.reIndex();
```

### **Monitoreo de Rendimiento**
```sql
-- Ver métricas de rendimiento
SELECT endpoint, AVG(response_time) as avg_time, COUNT(*) as requests
FROM performance_metrics 
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY endpoint
ORDER BY avg_time DESC;
```

## 🚨 **SOLUCIÓN DE PROBLEMAS**

### **MongoDB No Conecta**
1. Verificar credenciales en `.env`
2. Verificar whitelist de IPs en Atlas
3. Verificar que el servicio MongoDB esté corriendo (local)

### **MySQL No Conecta**
1. Verificar que XAMPP esté ejecutándose
2. Verificar que MySQL esté iniciado
3. Verificar credenciales en `.env`

### **Datos No Aparecen**
1. Ejecutar `node database/init-databases.js`
2. Verificar logs del backend
3. Verificar permisos de base de datos

## 📞 **SOPORTE**

Para problemas con las bases de datos:
1. Verificar logs del backend
2. Ejecutar health check: `curl http://localhost:5002/health`
3. Revisar configuración en `.env`
4. Ejecutar script de inicialización
