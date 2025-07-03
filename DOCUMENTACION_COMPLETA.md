# 📚 Documentación Completa - Sistema de Gestión de Evidencias

## 📋 Índice

1. [Arquitectura del Proyecto](#arquitectura-del-proyecto)
2. [Documentación de Clases y Componentes](#documentación-de-clases-y-componentes)
3. [Documentación de Funciones](#documentación-de-funciones)
4. [Integración con Base de Datos](#integración-con-base-de-datos)
5. [Construcción del Backend](#construcción-del-backend)
6. [Preparación para Repositorio](#preparación-para-repositorio)

---

## 🏗️ Arquitectura del Proyecto

### Estructura de Directorios

\`\`\`
evidence-management-platform/
├── app/
│   └── page.tsx                    # Página principal de Next.js
├── components/                     # Componentes reutilizables
│   ├── DataExport.jsx             # Exportación de datos
│   ├── GlobalSearch.jsx           # Búsqueda global
│   ├── Header.jsx                 # Encabezado de la aplicación
│   ├── NotificationSystem.jsx     # Sistema de notificaciones
│   ├── ReportGenerator.jsx        # Generador de reportes
│   └── Sidebar.jsx               # Barra lateral de navegación
├── context/
│   └── AuthContext.js             # Contexto de autenticación
├── views/                         # Vistas principales
│   ├── AdminGroupsView.jsx        # Administración de grupos
│   ├── AnalyticsView.jsx          # Vista de analíticas
│   ├── EvidencesView.jsx          # Gestión de evidencias
│   ├── FilesView.jsx              # Explorador de archivos
│   ├── GroupsView.jsx             # Vista de grupos
│   ├── HomeView.jsx               # Dashboard principal
│   ├── LoginView.jsx              # Vista de inicio de sesión
│   ├── MessagesView.jsx           # Sistema de mensajería
│   ├── NotificationsView.jsx      # Centro de notificaciones
│   ├── ProfileView.jsx            # Perfil de usuario
│   ├── SettingsView.jsx           # Configuración del sistema
│   ├── TasksView.jsx              # Gestión de tareas
│   └── UploadView.jsx             # Carga de archivos
├── routes.jsx                     # Configuración de rutas
├── App.jsx                        # Componente principal
└── App.css                        # Estilos globales
\`\`\`

---

## 📖 Documentación de Clases y Componentes

### 🔐 AuthContext (context/AuthContext.js)

**Propósito**: Gestiona el estado de autenticación global de la aplicación.

#### Clase: AuthProvider
- **Ruta**: `context/AuthContext.js`
- **Descripción**: Proveedor de contexto que maneja la autenticación de usuarios
- **Estado**:
  - `user`: Objeto del usuario autenticado
  - `loading`: Estado de carga
  - `isAuthenticated`: Estado de autenticación

#### Funciones Principales:

##### `login(email, password)`
- **Parámetros**:
  - `email` (string): Correo electrónico del usuario
  - `password` (string): Contraseña del usuario
- **Retorna**: Promise con objeto de resultado
- **Descripción**: Autentica al usuario y establece la sesión
- **Flujo**:
  1. Valida credenciales contra usuarios mock
  2. Genera token de sesión
  3. Guarda datos en localStorage
  4. Actualiza estado global

##### `logout()`
- **Parámetros**: Ninguno
- **Retorna**: void
- **Descripción**: Cierra la sesión del usuario
- **Flujo**:
  1. Limpia localStorage
  2. Resetea estado de usuario
  3. Redirige a login

##### `updateUser(userData)`
- **Parámetros**:
  - `userData` (object): Datos actualizados del usuario
- **Retorna**: void
- **Descripción**: Actualiza información del usuario autenticado

---

### 🏠 HomeView (views/HomeView.jsx)

**Propósito**: Dashboard principal con métricas y acciones rápidas.

#### Clase: HomeView
- **Ruta**: `views/HomeView.jsx`
- **Descripción**: Vista principal del dashboard
- **Rutas**: `/dashboard`, `/`

#### Componentes Internos:

##### `QuickActionCard({ icon, title, description, onClick, color })`
- **Parámetros**:
  - `icon` (Component): Icono del componente
  - `title` (string): Título de la acción
  - `description` (string): Descripción de la acción
  - `onClick` (function): Función de callback
  - `color` (string): Color del tema
- **Descripción**: Tarjeta de acción rápida para navegación

##### `StatCard({ title, value, icon, color })`
- **Parámetros**:
  - `title` (string): Título de la estadística
  - `value` (string|number): Valor a mostrar
  - `icon` (Component): Icono representativo
  - `color` (string): Color del tema
- **Descripción**: Tarjeta de estadística con métricas

#### Funciones Específicas:

##### `AdminDashboard()`
- **Descripción**: Renderiza dashboard para administradores
- **Características**:
  - Estadísticas del sistema
  - Actividad reciente
  - Acciones administrativas

##### `UserDashboard()`
- **Descripción**: Renderiza dashboard para usuarios regulares
- **Características**:
  - Estadísticas personales
  - Acciones de usuario
  - Actividad personal

---

### 📤 UploadView (views/UploadView.jsx)

**Propósito**: Interfaz para carga de archivos con soporte para 100+ tipos.

#### Clase: UploadView
- **Ruta**: `views/UploadView.jsx`
- **Descripción**: Vista de carga de archivos
- **Rutas**: `/upload`

#### Funciones Principales:

##### `validateFile(file)`
- **Parámetros**:
  - `file` (File): Archivo a validar
- **Retorna**: Array de errores
- **Descripción**: Valida tipo y tamaño de archivo
- **Validaciones**:
  - Tamaño máximo: 2GB
  - Tipos soportados: 100+ formatos

##### `handleDrag(e)`
- **Parámetros**:
  - `e` (Event): Evento de drag
- **Retorna**: void
- **Descripción**: Maneja eventos de arrastrar y soltar

##### `handleDrop(e)`
- **Parámetros**:
  - `e` (Event): Evento de drop
- **Retorna**: void
- **Descripción**: Procesa archivos soltados

##### `handleFiles(fileList)`
- **Parámetros**:
  - `fileList` (FileList): Lista de archivos
- **Retorna**: void
- **Descripción**: Procesa y valida archivos seleccionados

##### `handleSubmit(e)`
- **Parámetros**:
  - `e` (Event): Evento de formulario
- **Retorna**: Promise
- **Descripción**: Envía archivos al servidor
- **Flujo**:
  1. Valida archivos
  2. Simula carga
  3. Guarda metadatos
  4. Muestra confirmación

---

### 👥 GroupsView (views/GroupsView.jsx)

**Propósito**: Gestión de grupos colaborativos.

#### Clase: GroupsView
- **Ruta**: `views/GroupsView.jsx`
- **Descripción**: Vista de gestión de grupos
- **Rutas**: `/groups`

#### Funciones Principales:

##### `handleJoinGroup(group)`
- **Parámetros**:
  - `group` (object): Objeto del grupo
- **Retorna**: void
- **Descripción**: Procesa solicitud de unión a grupo
- **Flujo**:
  1. Verifica tipo de grupo
  2. Maneja autenticación si es necesario
  3. Actualiza membresía

##### `handleLeaveGroup(groupId)`
- **Parámetros**:
  - `groupId` (number): ID del grupo
- **Retorna**: void
- **Descripción**: Procesa salida de grupo

##### `CreateGroupModal()`
- **Descripción**: Modal para crear nuevos grupos
- **Campos**:
  - Nombre del grupo
  - Descripción
  - Tipo (público/privado/protegido)
  - Categoría
  - Contraseña (si es protegido)

---

### 📁 FilesView (views/FilesView.jsx)

**Propósito**: Explorador de archivos con filtros avanzados.

#### Clase: FilesView
- **Ruta**: `views/FilesView.jsx`
- **Descripción**: Vista de exploración de archivos
- **Rutas**: `/files`

#### Funciones Principales:

##### `formatFileSize(bytes)`
- **Parámetros**:
  - `bytes` (number): Tamaño en bytes
- **Retorna**: string
- **Descripción**: Convierte bytes a formato legible

##### `getFileIcon(type, category)`
- **Parámetros**:
  - `type` (string): Tipo MIME
  - `category` (string): Categoría del archivo
- **Retorna**: Component
- **Descripción**: Retorna icono apropiado para el tipo de archivo

##### `handleDownload(file)`
- **Parámetros**:
  - `file` (object): Objeto del archivo
- **Retorna**: void
- **Descripción**: Inicia descarga del archivo

##### `handleLike(fileId)`
- **Parámetros**:
  - `fileId` (number): ID del archivo
- **Retorna**: void
- **Descripción**: Procesa "me gusta" en archivo

#### Componentes Internos:

##### `FileCard({ file })`
- **Descripción**: Tarjeta de archivo en vista de cuadrícula
- **Características**:
  - Vista previa
  - Metadatos
  - Acciones (ver, descargar, like)

##### `FileRow({ file })`
- **Descripción**: Fila de archivo en vista de lista
- **Características**:
  - Información compacta
  - Acciones rápidas

---

### 🛡️ EvidencesView (views/EvidencesView.jsx)

**Propósito**: Gestión y evaluación de evidencias.

#### Clase: EvidencesView
- **Ruta**: `views/EvidencesView.jsx`
- **Descripción**: Vista de gestión de evidencias
- **Rutas**: `/evidences`

#### Funciones Principales:

##### `handleViewEvidence(evidence)`
- **Parámetros**:
  - `evidence` (object): Objeto de evidencia
- **Retorna**: void
- **Descripción**: Abre modal de detalle de evidencia

##### `handleUpdateStatus(evidenceId, newStatus, rating, feedback)`
- **Parámetros**:
  - `evidenceId` (number): ID de la evidencia
  - `newStatus` (string): Nuevo estado
  - `rating` (number): Calificación (1-5)
  - `feedback` (string): Retroalimentación
- **Retorna**: void
- **Descripción**: Actualiza estado de evidencia

#### Componentes Internos:

##### `EvidenceCard({ evidence })`
- **Descripción**: Tarjeta de evidencia
- **Características**:
  - Estado visual
  - Calificación con estrellas
  - Metadatos completos

##### `EvidenceDetailModal()`
- **Descripción**: Modal de detalle y evaluación
- **Características**:
  - Información completa
  - Sistema de comentarios
  - Panel de evaluación (admin)

---

### 💬 MessagesView (views/MessagesView.jsx)

**Propósito**: Sistema de mensajería en tiempo real.

#### Clase: MessagesView
- **Ruta**: `views/MessagesView.jsx`
- **Descripción**: Vista de mensajería
- **Rutas**: `/messages`

#### Funciones Principales:

##### `handleSendMessage(e)`
- **Parámetros**:
  - `e` (Event): Evento de formulario
- **Retorna**: void
- **Descripción**: Envía nuevo mensaje
- **Flujo**:
  1. Valida contenido
  2. Crea objeto mensaje
  3. Actualiza conversación
  4. Limpia formulario

##### `formatTime(timestamp)`
- **Parámetros**:
  - `timestamp` (string): Marca de tiempo ISO
- **Retorna**: string
- **Descripción**: Formatea tiempo para mostrar

#### Componentes Internos:

##### `ConversationItem({ conversation })`
- **Descripción**: Item de conversación en lista
- **Características**:
  - Avatar y estado en línea
  - Último mensaje
  - Contador de no leídos

##### `MessageBubble({ message, isOwn })`
- **Descripción**: Burbuja de mensaje
- **Características**:
  - Estilo diferenciado por autor
  - Estado de entrega
  - Marca de tiempo

##### `NewChatModal()`
- **Descripción**: Modal para crear nueva conversación
- **Características**:
  - Selección de usuarios
  - Chat individual o grupal

---

### 🔔 NotificationsView (views/NotificationsView.jsx)

**Propósito**: Centro de notificaciones del sistema.

#### Clase: NotificationsView
- **Ruta**: `views/NotificationsView.jsx`
- **Descripción**: Vista de notificaciones
- **Rutas**: `/notifications`

#### Funciones Principales:

##### `markAsRead(notificationId)`
- **Parámetros**:
  - `notificationId` (number): ID de la notificación
- **Retorna**: void
- **Descripción**: Marca notificación como leída

##### `markAllAsRead()`
- **Parámetros**: Ninguno
- **Retorna**: void
- **Descripción**: Marca todas las notificaciones como leídas

##### `deleteNotification(notificationId)`
- **Parámetros**:
  - `notificationId` (number): ID de la notificación
- **Retorna**: void
- **Descripción**: Elimina notificación

##### `getActivityIcon(type)`
- **Parámetros**:
  - `type` (string): Tipo de actividad
- **Retorna**: Component
- **Descripción**: Retorna icono según tipo de notificación

#### Componentes Internos:

##### `NotificationCard({ notification })`
- **Descripción**: Tarjeta de notificación
- **Características**:
  - Icono por tipo
  - Estado visual (leída/no leída)
  - Acciones (marcar, archivar, eliminar)

---

### ✅ TasksView (views/TasksView.jsx)

**Propósito**: Gestión de tareas y evaluaciones (Solo Admin).

#### Clase: TasksView
- **Ruta**: `views/TasksView.jsx`
- **Descripción**: Vista de gestión de tareas
- **Rutas**: `/admin/tasks`
- **Acceso**: Solo administradores

#### Funciones Principales:

##### `handleUpdateStatus(evidenceId, newStatus, rating, feedback)`
- **Parámetros**:
  - `evidenceId` (number): ID de la evidencia
  - `newStatus` (string): Nuevo estado
  - `rating` (number): Calificación
  - `feedback` (string): Retroalimentación
- **Retorna**: void
- **Descripción**: Actualiza estado de tarea

#### Componentes Internos:

##### `TaskCard({ task })`
- **Descripción**: Tarjeta de tarea
- **Características**:
  - Estado y prioridad visual
  - Progreso de envíos
  - Fechas límite

##### `CreateTaskModal()`
- **Descripción**: Modal para crear tareas
- **Campos**:
  - Título y descripción
  - Grupo asignado
  - Usuarios específicos
  - Fecha límite
  - Prioridad y categoría

##### `EvaluateTaskModal()`
- **Descripción**: Modal de evaluación
- **Características**:
  - Lista de envíos
  - Sistema de calificación
  - Retroalimentación

---

### 📊 AnalyticsView (views/AnalyticsView.jsx)

**Propósito**: Dashboard de analíticas y métricas (Solo Admin).

#### Clase: AnalyticsView
- **Ruta**: `views/AnalyticsView.jsx`
- **Descripción**: Vista de analíticas
- **Rutas**: `/admin/analytics`
- **Acceso**: Solo administradores

#### Funciones Principales:

##### `exportData(format)`
- **Parámetros**:
  - `format` (string): Formato de exportación
- **Retorna**: void
- **Descripción**: Exporta datos analíticos

#### Componentes Internos:

##### `StatCard({ title, value, icon, color, trend, subtitle })`
- **Descripción**: Tarjeta de estadística avanzada
- **Características**:
  - Tendencias
  - Subtítulos informativos
  - Colores temáticos

##### `SimpleBarChart({ data, title, color })`
- **Descripción**: Gráfico de barras simple
- **Características**:
  - Datos responsivos
  - Colores personalizables

##### `LineChart({ data, title, color })`
- **Descripción**: Gráfico de líneas
- **Características**:
  - Tendencias temporales
  - Interactividad hover

---

### 👤 ProfileView (views/ProfileView.jsx)

**Propósito**: Gestión de perfil de usuario.

#### Clase: ProfileView
- **Ruta**: `views/ProfileView.jsx`
- **Descripción**: Vista de perfil de usuario
- **Rutas**: `/profile`

#### Funciones Principales:

##### `handleSaveProfile()`
- **Parámetros**: Ninguno
- **Retorna**: void
- **Descripción**: Guarda cambios del perfil

##### `handleCancelEdit()`
- **Parámetros**: Ninguno
- **Retorna**: void
- **Descripción**: Cancela edición del perfil

#### Componentes Internos:

##### `PasswordChangeModal()`
- **Descripción**: Modal para cambio de contraseña
- **Características**:
  - Validación de contraseña actual
  - Confirmación de nueva contraseña
  - Visibilidad de contraseñas

##### `DeleteAccountModal()`
- **Descripción**: Modal para eliminar cuenta
- **Características**:
  - Confirmación por texto
  - Advertencias de seguridad

---

### ⚙️ SettingsView (views/SettingsView.jsx)

**Propósito**: Configuración del sistema (Solo Admin).

#### Clase: SettingsView
- **Ruta**: `views/SettingsView.jsx`
- **Descripción**: Vista de configuración del sistema
- **Rutas**: `/settings`
- **Acceso**: Solo administradores

#### Funciones Principales:

##### `handleSaveSettings(section)`
- **Parámetros**:
  - `section` (string): Sección a guardar
- **Retorna**: Promise
- **Descripción**: Guarda configuración de sección específica

##### `handleTestEmail()`
- **Parámetros**: Ninguno
- **Retorna**: Promise
- **Descripción**: Envía email de prueba

##### `handleResetSection(section)`
- **Parámetros**:
  - `section` (string): Sección a resetear
- **Retorna**: void
- **Descripción**: Resetea sección a valores por defecto

---

### 🔍 GlobalSearch (components/GlobalSearch.jsx)

**Propósito**: Búsqueda global con atajos de teclado.

#### Clase: GlobalSearch
- **Ruta**: `components/GlobalSearch.jsx`
- **Descripción**: Componente de búsqueda global
- **Atajo**: `Cmd/Ctrl + K`

#### Funciones Principales:

##### `performSearch(query, type)`
- **Parámetros**:
  - `query` (string): Término de búsqueda
  - `type` (string): Tipo de búsqueda
- **Retorna**: Array de resultados
- **Descripción**: Ejecuta búsqueda en diferentes tipos de datos

##### `handleResultClick(result)`
- **Parámetros**:
  - `result` (object): Resultado seleccionado
- **Retorna**: void
- **Descripción**: Navega al resultado seleccionado

---

### 📋 ReportGenerator (components/ReportGenerator.jsx)

**Propósito**: Generación de reportes personalizables.

#### Clase: ReportGenerator
- **Ruta**: `components/ReportGenerator.jsx`
- **Descripción**: Generador de reportes
- **Atajo**: `Cmd/Ctrl + Shift + R`

#### Funciones Principales:

##### `handleGenerateReport()`
- **Parámetros**: Ninguno
- **Retorna**: Promise
- **Descripción**: Genera y descarga reporte
- **Flujo**:
  1. Recopila configuración
  2. Procesa datos
  3. Genera archivo
  4. Inicia descarga

---

### 📤 DataExport (components/DataExport.jsx)

**Propósito**: Exportación de datos del sistema.

#### Clase: DataExport
- **Ruta**: `components/DataExport.jsx`
- **Descripción**: Exportador de datos
- **Atajo**: `Cmd/Ctrl + Shift + E`

#### Funciones Principales:

##### `handleExport()`
- **Parámetros**: Ninguno
- **Retorna**: Promise
- **Descripción**: Exporta datos seleccionados
- **Formatos**: JSON, CSV, XML, Excel

---

## 🗄️ Integración con Base de Datos

### Paso 1: Configuración del Backend

#### Crear estructura del servidor

\`\`\`bash
mkdir backend
cd backend
npm init -y
npm install express mongoose cors dotenv bcryptjs jsonwebtoken multer
\`\`\`

#### Estructura de archivos del backend:

\`\`\`
backend/
├── config/
│   └── database.js
├── controllers/
│   ├── authController.js
│   ├── fileController.js
│   ├── groupController.js
│   ├── evidenceController.js
│   ├── messageController.js
│   └── analyticsController.js
├── models/
│   ├── User.js
│   ├── File.js
│   ├── Group.js
│   ├── Evidence.js
│   ├── Message.js
│   └── Notification.js
├── routes/
│   ├── auth.js
│   ├── files.js
│   ├── groups.js
│   ├── evidences.js
│   ├── messages.js
│   └── analytics.js
├── middleware/
│   ├── auth.js
│   └── upload.js
├── uploads/
└── server.js
\`\`\`

### Paso 2: Modelos de Base de Datos

#### User.js (models/User.js)
\`\`\`javascript
const mongoose = require('mongoose');

const userSchema = new mongoose.Schema({
  name: { type: String, required: true },
  email: { type: String, required: true, unique: true },
  password: { type: String, required: true },
  role: { type: String, enum: ['user', 'admin'], default: 'user' },
  avatar: String,
  bio: String,
  phone: String,
  location: String,
  socialLinks: {
    github: String,
    linkedin: String,
    website: String
  },
  privacy: {
    profilePublic: { type: Boolean, default: true },
    showInSearch: { type: Boolean, default: true },
    allowMessages: { type: Boolean, default: true }
  },
  stats: {
    filesUploaded: { type: Number, default: 0 },
    evidencesSubmitted: { type: Number, default: 0 },
    groupsJoined: { type: Number, default: 0 },
    averageRating: { type: Number, default: 0 }
  }
}, { timestamps: true });

module.exports = mongoose.model('User', userSchema);
\`\`\`

#### File.js (models/File.js)
\`\`\`javascript
const mongoose = require('mongoose');

const fileSchema = new mongoose.Schema({
  name: { type: String, required: true },
  originalName: { type: String, required: true },
  path: { type: String, required: true },
  size: { type: Number, required: true },
  mimeType: { type: String, required: true },
  category: { type: String, required: true },
  author: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  group: { type: mongoose.Schema.Types.ObjectId, ref: 'Group' },
  status: { type: String, enum: ['pending', 'approved', 'rejected'], default: 'pending' },
  tags: [String],
  description: String,
  downloads: { type: Number, default: 0 },
  likes: { type: Number, default: 0 },
  comments: [{
    author: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    content: String,
    timestamp: { type: Date, default: Date.now }
  }]
}, { timestamps: true });

module.exports = mongoose.model('File', fileSchema);
\`\`\`

#### Group.js (models/Group.js)
\`\`\`javascript
const mongoose = require('mongoose');

const groupSchema = new mongoose.Schema({
  name: { type: String, required: true },
  description: { type: String, required: true },
  type: { type: String, enum: ['public', 'private', 'protected'], default: 'public' },
  category: { type: String, required: true },
  createdBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  members: [{
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    role: { type: String, enum: ['admin', 'moderator', 'member'], default: 'member' },
    joinDate: { type: Date, default: Date.now }
  }],
  settings: {
    maxMembers: { type: Number, default: 50 },
    allowFileUpload: { type: Boolean, default: true },
    requireApproval: { type: Boolean, default: false },
    password: String
  },
  stats: {
    totalUploads: { type: Number, default: 0 },
    approvedFiles: { type: Number, default: 0 },
    pendingFiles: { type: Number, default: 0 },
    rejectedFiles: { type: Number, default: 0 }
  }
}, { timestamps: true });

module.exports = mongoose.model('Group', groupSchema);
\`\`\`

### Paso 3: Controladores

#### authController.js (controllers/authController.js)
\`\`\`javascript
const User = require('../models/User');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

exports.login = async (req, res) => {
  try {
    const { email, password } = req.body;
    
    const user = await User.findOne({ email });
    if (!user) {
      return res.status(401).json({ success: false, error: 'Invalid credentials' });
    }
    
    const isMatch = await bcrypt.compare(password, user.password);
    if (!isMatch) {
      return res.status(401).json({ success: false, error: 'Invalid credentials' });
    }
    
    const token = jwt.sign({ userId: user._id }, process.env.JWT_SECRET, { expiresIn: '24h' });
    
    res.json({
      success: true,
      token,
      user: {
        id: user._id,
        name: user.name,
        email: user.email,
        role: user.role,
        avatar: user.avatar
      }
    });
  } catch (error) {
    res.status(500).json({ success: false, error: error.message });
  }
};

exports.register = async (req, res) => {
  try {
    const { name, email, password } = req.body;
    
    const existingUser = await User.findOne({ email });
    if (existingUser) {
      return res.status(400).json({ success: false, error: 'User already exists' });
    }
    
    const hashedPassword = await bcrypt.hash(password, 12);
    
    const user = new User({
      name,
      email,
      password: hashedPassword
    });
    
    await user.save();
    
    const token = jwt.sign({ userId: user._id }, process.env.JWT_SECRET, { expiresIn: '24h' });
    
    res.status(201).json({
      success: true,
      token,
      user: {
        id: user._id,
        name: user.name,
        email: user.email,
        role: user.role
      }
    });
  } catch (error) {
    res.status(500).json({ success: false, error: error.message });
  }
};
\`\`\`

### Paso 4: Rutas de API

#### auth.js (routes/auth.js)
\`\`\`javascript
const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');

router.post('/login', authController.login);
router.post('/register', authController.register);

module.exports = router;
\`\`\`

### Paso 5: Servidor Principal

#### server.js
\`\`\`javascript
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use('/uploads', express.static('uploads'));

// Database connection
mongoose.connect(process.env.MONGODB_URI, {
  useNewUrlParser: true,
  useUnifiedTopology: true
});

// Routes
app.use('/api/auth', require('./routes/auth'));
app.use('/api/files', require('./routes/files'));
app.use('/api/groups', require('./routes/groups'));
app.use('/api/evidences', require('./routes/evidences'));
app.use('/api/messages', require('./routes/messages'));
app.use('/api/analytics', require('./routes/analytics'));

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
\`\`\`

### Paso 6: Integración Frontend-Backend

#### Crear servicio API (frontend/services/api.js)
\`\`\`javascript
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:5000/api';

class ApiService {
  constructor() {
    this.token = localStorage.getItem('token');
  }

  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...(this.token && { Authorization: `Bearer ${this.token}` })
      },
      ...options
    };

    const response = await fetch(url, config);
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'API request failed');
    }

    return data;
  }

  // Auth methods
  async login(email, password) {
    return this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
  }

  // File methods
  async uploadFile(formData) {
    return this.request('/files/upload', {
      method: 'POST',
      headers: {
        ...(this.token && { Authorization: `Bearer ${this.token}` })
      },
      body: formData
    });
  }

  async getFiles(filters = {}) {
    const queryString = new URLSearchParams(filters).toString();
    return this.request(`/files?${queryString}`);
  }

  // Group methods
  async getGroups() {
    return this.request('/groups');
  }

  async createGroup(groupData) {
    return this.request('/groups', {
      method: 'POST',
      body: JSON.stringify(groupData)
    });
  }
}

export default new ApiService();
\`\`\`

### Paso 7: Actualizar AuthContext para usar API real

#### Modificar AuthContext.js
\`\`\`javascript
import ApiService from '../services/api';

export const AuthProvider = ({ children }) => {
  // ... estado existente

  const login = async (email, password) => {
    try {
      setLoading(true);
      
      const response = await ApiService.login(email, password);
      
      if (response.success) {
        localStorage.setItem('user', JSON.stringify(response.user));
        localStorage.setItem('token', response.token);
        
        setUser(response.user);
        setIsAuthenticated(true);
        
        return { success: true, user: response.user };
      }
    } catch (error) {
      return { success: false, error: error.message };
    } finally {
      setLoading(false);
    }
  };

  // ... resto del código
};
\`\`\`

---

## 📊 Construcción de Gráficos y Visualizaciones

### Paso 1: Instalar librerías de gráficos

\`\`\`bash
npm install recharts d3 chart.js react-chartjs-2
\`\`\`

### Paso 2: Componente de gráficos reutilizable

#### ChartComponents.jsx (components/ChartComponents.jsx)
\`\`\`javascript
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

export const LineChartComponent = ({ data, xKey, yKey, color = '#8884d8', title }) => (
  <div className="bg-white p-6 rounded-lg shadow">
    <h3 className="text-lg font-semibold mb-4">{title}</h3>
    <ResponsiveContainer width="100%" height={300}>
      <LineChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey={xKey} />
        <YAxis />
        <Tooltip />
        <Legend />
        <Line type="monotone" dataKey={yKey} stroke={color} strokeWidth={2} />
      </LineChart>
    </ResponsiveContainer>
  </div>
);

export const BarChartComponent = ({ data, xKey, yKey, color = '#8884d8', title }) => (
  <div className="bg-white p-6 rounded-lg shadow">
    <h3 className="text-lg font-semibold mb-4">{title}</h3>
    <ResponsiveContainer width="100%" height={300}>
      <BarChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey={xKey} />
        <YAxis />
        <Tooltip />
        <Legend />
        <Bar dataKey={yKey} fill={color} />
      </BarChart>
    </ResponsiveContainer>
  </div>
);

export const PieChartComponent = ({ data, dataKey, nameKey, colors, title }) => (
  <div className="bg-white p-6 rounded-lg shadow">
    <h3 className="text-lg font-semibold mb-4">{title}</h3>
    <ResponsiveContainer width="100%" height={300}>
      <PieChart>
        <Pie
          data={data}
          cx="50%"
          cy="50%"
          labelLine={false}
          label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
          outerRadius={80}
          fill="#8884d8"
          dataKey={dataKey}
        >
          {data.map((entry, index) => (
            <Cell key={`cell-${index}`} fill={colors[index % colors.length]} />
          ))}
        </Pie>
        <Tooltip />
      </PieChart>
    </ResponsiveContainer>
  </div>
);
\`\`\`

### Paso 3: Integrar gráficos en AnalyticsView

\`\`\`javascript
import { LineChartComponent, BarChartComponent, PieChartComponent } from '../components/ChartComponents';

// En AnalyticsView.jsx
const renderCharts = () => (
  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <LineChartComponent
      data={analytics.fileStats?.uploadTrend}
      xKey="date"
      yKey="uploads"
      color="#3B82F6"
      title="Tendencia de Subidas"
    />
    <BarChartComponent
      data={analytics.fileStats?.byType}
      xKey="type"
      yKey="count"
      color="#10B981"
      title="Archivos por Tipo"
    />
    <PieChartComponent
      data={analytics.groupStats?.mostActive}
      dataKey="activity"
      nameKey="name"
      colors={['#3B82F6', '#10B981', '#F59E0B', '#EF4444']}
      title="Grupos Más Activos"
    />
  </div>
);
\`\`\`

---

## 🚀 Preparación para Repositorio

### Paso 1: Estructura final del proyecto

\`\`\`
evidence-management-platform/
├── frontend/                   # Aplicación React
│   ├── public/
│   ├── src/
│   ├── package.json
│   └── README.md
├── backend/                    # Servidor Node.js
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── routes/
│   ├── middleware/
│   ├── uploads/
│   ├── package.json
│   └── README.md
├── docs/                       # Documentación
│   ├── DOCUMENTACION_COMPLETA.md
│   ├── API_REFERENCE.md
│   └── DEPLOYMENT_GUIDE.md
├── .gitignore
├── README.md
├── docker-compose.yml
└── package.json
\`\`\`

### Paso 2: README.md principal

\`\`\`markdown
# 🛡️ Sistema de Gestión de Evidencias

## 📋 Descripción

Sistema completo de gestión de evidencias con funcionalidades avanzadas de colaboración, evaluación y análisis. Desarrollado con React.js y Node.js.

## ✨ Características Principales

- 🔐 **Autenticación segura** con roles de usuario
- 📤 **Carga de archivos** con soporte para 100+ tipos
- 👥 **Gestión de grupos** colaborativos
- 🛡️ **Evaluación de evidencias** con sistema de calificación
- 💬 **Mensajería en tiempo real**
- 📊 **Dashboard analítico** con métricas avanzadas
- 🔍 **Búsqueda global** con filtros inteligentes
- 📋 **Generación de reportes** personalizables
- 📱 **Diseño responsivo** para todos los dispositivos

## 🚀 Inicio Rápido

### Prerrequisitos
- Node.js 16+
- MongoDB 4.4+
- npm o yarn

### Instalación

1. **Clonar el repositorio**
\`\`\`bash
git clone https://github.com/tu-usuario/evidence-management-platform.git
cd evidence-management-platform
\`\`\`

2. **Instalar dependencias del backend**
\`\`\`bash
cd backend
npm install
\`\`\`

3. **Instalar dependencias del frontend**
\`\`\`bash
cd ../frontend
npm install
\`\`\`

4. **Configurar variables de entorno**
\`\`\`bash
# Backend (.env)
MONGODB_URI=mongodb://localhost:27017/evidence_management
JWT_SECRET=tu_jwt_secret_aqui
PORT=5000

# Frontend (.env)
REACT_APP_API_URL=http://localhost:5000/api
\`\`\`

5. **Iniciar la aplicación**
\`\`\`bash
# Terminal 1 - Backend
cd backend
npm run dev

# Terminal 2 - Frontend
cd frontend
npm start
\`\`\`

## 📚 Documentación

- [Documentación Completa](docs/DOCUMENTACION_COMPLETA.md)
- [Referencia de API](docs/API_REFERENCE.md)
- [Guía de Despliegue](docs/DEPLOYMENT_GUIDE.md)

## 🏗️ Arquitectura

\`\`\`
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   React.js      │    │   Node.js       │    │   MongoDB       │
│   Frontend      │◄──►│   Backend       │◄──►│   Database      │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
\`\`\`

## 🛠️ Tecnologías Utilizadas

### Frontend
- React.js 18
- React Router DOM
- Tailwind CSS
- Lucide React (iconos)
- Recharts (gráficos)

### Backend
- Node.js
- Express.js
- MongoDB con Mongoose
- JWT para autenticación
- Multer para carga de archivos
- bcryptjs para encriptación

## 📱 Capturas de Pantalla

### Dashboard Principal
![Dashboard](screenshots/dashboard.png)

### Gestión de Archivos
![Files](screenshots/files.png)

### Sistema de Mensajería
![Messages](screenshots/messages.png)

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 👥 Autores

- **Tu Nombre** - *Desarrollo inicial* - [TuGitHub](https://github.com/tu-usuario)

## 🙏 Agradecimientos

- Equipo de desarrollo
- Comunidad de código abierto
- Contribuidores del proyecto
\`\`\`

### Paso 3: .gitignore

\`\`\`gitignore
# Dependencies
node_modules/
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Production builds
/frontend/build
/backend/dist

# Environment variables
.env
.env.local
.env.development.local
.env.test.local
.env.production.local

# Database
*.db
*.sqlite

# Uploads
/backend/uploads/*
!/backend/uploads/.gitkeep

# Logs
logs
*.log

# Runtime data
pids
*.pid
*.seed
*.pid.lock

# Coverage directory used by tools like istanbul
coverage/

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Temporary files
*.tmp
*.temp
\`\`\`

### Paso 4: package.json raíz

\`\`\`json
{
  "name": "evidence-management-platform",
  "version": "1.0.0",
  "description": "Sistema completo de gestión de evidencias",
  "main": "index.js",
  "scripts": {
    "dev": "concurrently \"npm run server\" \"npm run client\"",
    "server": "cd backend && npm run dev",
    "client": "cd frontend && npm start",
    "build": "cd frontend && npm run build",
    "install-deps": "npm install && cd backend && npm install && cd ../frontend && npm install"
  },
  "keywords": [
    "evidence-management",
    "react",
    "nodejs",
    "mongodb",
    "collaboration"
  ],
  "author": "Tu Nombre",
  "license": "MIT",
  "devDependencies": {
    "concurrently": "^7.6.0"
  }
}
\`\`\`

### Paso 5: Docker Compose (Opcional)

\`\`\`yaml
version: '3.8'

services:
  mongodb:
    image: mongo:4.4
    container_name: evidence_mongodb
    restart: unless-stopped
    ports:
      - "27017:27017"
    volumes:
      - mongodb_data:/data/db
    environment:
      MONGO_INITDB_ROOT_USERNAME: admin
      MONGO_INITDB_ROOT_PASSWORD: password

  backend:
    build: ./backend
    container_name: evidence_backend
    restart: unless-stopped
    ports:
      - "5000:5000"
    depends_on:
      - mongodb
    environment:
      MONGODB_URI: mongodb://admin:password@mongodb:27017/evidence_management?authSource=admin
      JWT_SECRET: your_jwt_secret_here
    volumes:
      - ./backend/uploads:/app/uploads

  frontend:
    build: ./frontend
    container_name: evidence_frontend
    restart: unless-stopped
    ports:
      - "3000:3000"
    depends_on:
      - backend
    environment:
      REACT_APP_API_URL: http://localhost:5000/api

volumes:
  mongodb_data:
\`\`\`

### Paso 6: Scripts de despliegue

#### deploy.sh
\`\`\`bash
#!/bin/bash

echo "🚀 Iniciando despliegue..."

# Instalar dependencias
echo "📦 Instalando dependencias..."
npm run install-deps

# Construir frontend
echo "🏗️ Construyendo frontend..."
cd frontend && npm run build

# Iniciar servicios con Docker
echo "🐳 Iniciando servicios..."
cd .. && docker-compose up -d

echo "✅ Despliegue completado!"
echo "🌐 Frontend: http://localhost:3000"
echo "🔧 Backend: http://localhost:5000"
echo "🗄️ MongoDB: mongodb://localhost:27017"
\`\`\`

### Paso 7: Documentación de API

#### API_REFERENCE.md
\`\`\`markdown
# 📚 Referencia de API

## Autenticación

### POST /api/auth/login
Autentica un usuario y retorna un token JWT.

**Parámetros:**
\`\`\`json
{
  "email": "string",
  "password": "string"
}
\`\`\`

**Respuesta:**
\`\`\`json
{
  "success": true,
  "token": "jwt_token",
  "user": {
    "id": "user_id",
    "name": "string",
    "email": "string",
    "role": "user|admin"
  }
}
\`\`\`

## Archivos

### GET /api/files
Obtiene lista de archivos con filtros opcionales.

**Parámetros de consulta:**
- `page`: Número de página (default: 1)
- `limit`: Archivos por página (default: 20)
- `category`: Filtrar por categoría
- `status`: Filtrar por estado

### POST /api/files/upload
Sube uno o más archivos.

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: multipart/form-data`

**Body:**
- `files`: Archivos a subir
- `title`: Título del archivo
- `description`: Descripción
- `tags`: Tags separados por coma

## Grupos

### GET /api/groups
Obtiene lista de grupos.

### POST /api/groups
Crea un nuevo grupo.

**Body:**
\`\`\`json
{
  "name": "string",
  "description": "string",
  "type": "public|private|protected",
  "category": "string",
  "password": "string (opcional)"
}
\`\`\`
\`\`\`

---

## 🎯 Conclusión

Esta documentación proporciona una guía completa para:

1. **Entender la arquitectura** del sistema
2. **Mantener y extender** el código existente
3. **Integrar con base de datos** real
4. **Construir el backend** completo
5. **Desplegar la aplicación** en producción
6. **Contribuir al proyecto** de manera efectiva

El sistema está diseñado para ser escalable, mantenible y fácil de extender con nuevas funcionalidades.
