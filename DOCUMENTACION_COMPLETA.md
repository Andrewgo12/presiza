# 📖 Documentación Completa - Sistema de Gestión de Evidencias

## 📋 Tabla de Contenidos

1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Componentes Frontend](#componentes-frontend)
4. [API Backend](#api-backend)
5. [Base de Datos](#base-de-datos)
6. [Autenticación y Seguridad](#autenticación-y-seguridad)
7. [Guías de Desarrollo](#guías-de-desarrollo)
8. [Troubleshooting](#troubleshooting)

## 🏗️ Arquitectura del Sistema

### Arquitectura General

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (React + Next.js)              │
├─────────────────────────────────────────────────────────────┤
│  • React 19 + Next.js 15                                   │
│  • React Router DOM para navegación                        │
│  • Tailwind CSS + shadcn/ui                               │
│  • Context API para estado global                          │
│  • Axios para comunicación HTTP                            │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (Node.js + Express)             │
├─────────────────────────────────────────────────────────────┤
│  • Express.js 4.18+ como framework web                     │
│  • JWT para autenticación                                  │
│  • Socket.IO para tiempo real                              │
│  • Multer para carga de archivos                           │
│  • Helmet + CORS para seguridad                            │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    BASES DE DATOS HÍBRIDAS                 │
├─────────────────────────────────────────────────────────────┤
│  MongoDB Atlas (Principal)    │    MySQL/XAMPP (Secundaria) │
│  • Usuarios                   │    • Logs de auditoría      │
│  • Archivos                   │    • Analytics              │
│  • Grupos                     │    • Métricas               │
│  • Mensajes                   │    • Sesiones               │
│  • Evidencias                 │    • Performance            │
│  • Notificaciones             │                             │
└─────────────────────────────────────────────────────────────┘
```

### Patrones de Diseño Implementados

1. **MVC (Model-View-Controller)**
   - Models: Esquemas de MongoDB y MySQL
   - Views: Componentes React
   - Controllers: Rutas de Express

2. **Repository Pattern**
   - Abstracción de acceso a datos
   - Fallback automático entre bases de datos

3. **Middleware Pattern**
   - Autenticación JWT
   - Logging de requests
   - Manejo de errores

4. **Observer Pattern**
   - Sistema de notificaciones
   - WebSockets para tiempo real

## 📁 Estructura del Proyecto

```
reportes/
├── 📁 frontend/
│   ├── 📁 components/          # Componentes reutilizables
│   │   ├── Header.jsx
│   │   ├── Sidebar.jsx
│   │   ├── GlobalSearch.jsx
│   │   ├── NotificationSystem.jsx
│   │   ├── DataExport.jsx
│   │   ├── ReportGenerator.jsx
│   │   └── 📁 ui/             # Componentes UI base (shadcn)
│   ├── 📁 views/              # Páginas principales
│   │   ├── LoginView.jsx
│   │   ├── HomeView.jsx
│   │   ├── UploadView.jsx
│   │   ├── FilesView.jsx
│   │   ├── GroupsView.jsx
│   │   ├── MessagesView.jsx
│   │   ├── EvidencesView.jsx
│   │   ├── AnalyticsView.jsx
│   │   ├── ProfileView.jsx
│   │   ├── SettingsView.jsx
│   │   ├── TasksView.jsx
│   │   ├── NotificationsView.jsx
│   │   ├── AdminGroupsView.jsx
│   │   └── AdminLogsView.jsx
│   ├── 📁 context/            # Estado global
│   │   └── AuthContext.js
│   ├── 📁 services/           # Servicios API
│   │   └── api.js
│   ├── 📁 hooks/              # Custom hooks
│   │   └── use-client.js
│   ├── 📁 lib/                # Utilidades
│   │   └── utils.ts
│   ├── App.jsx                # Componente principal
│   ├── routes.jsx             # Configuración de rutas
│   └── 📁 styles/             # Estilos globales
├── 📁 backend/
│   ├── 📁 routes/             # Endpoints API
│   │   ├── auth.js            # Autenticación
│   │   ├── users.js           # Gestión de usuarios
│   │   ├── files.js           # Gestión de archivos
│   │   ├── groups.js          # Gestión de grupos
│   │   ├── messages.js        # Mensajería
│   │   ├── evidences.js       # Evidencias
│   │   ├── notifications.js   # Notificaciones
│   │   ├── analytics.js       # Analytics
│   │   └── logs.js            # Logs del sistema
│   ├── 📁 models/             # Modelos de datos
│   │   ├── User.js            # Modelo de usuario (MongoDB)
│   │   ├── File.js            # Modelo de archivo (MongoDB)
│   │   ├── Group.js           # Modelo de grupo (MongoDB)
│   │   └── 📁 mysql/          # Modelos MySQL
│   │       ├── Analytics.js
│   │       ├── AuditLog.js
│   │       ├── SystemLog.js
│   │       ├── UserSession.js
│   │       └── PerformanceMetric.js
│   ├── 📁 middleware/         # Middleware personalizado
│   │   ├── auth.js            # Autenticación JWT
│   │   ├── errorHandler.js    # Manejo de errores
│   │   └── logging.js         # Logging de requests
│   ├── 📁 config/             # Configuraciones
│   │   ├── database.js        # Configuración BD híbrida
│   │   └── swagger.js         # Documentación API
│   ├── 📁 utils/              # Utilidades
│   │   └── fallbackData.js    # Datos de fallback
│   ├── 📁 tests/              # Pruebas
│   │   ├── auth.test.js
│   │   └── setup.js
│   ├── 📁 scripts/            # Scripts de utilidad
│   │   └── init-database.js
│   ├── 📁 database/           # Esquemas y configuración BD
│   │   ├── mongodb_schema.js
│   │   ├── mysql_schema.sql
│   │   ├── init-databases.js
│   │   └── README.md
│   └── server.js              # Servidor principal
├── 📁 public/                 # Archivos estáticos
├── 📁 docs/                   # Documentación
├── package.json               # Dependencias frontend
├── README.md                  # Documentación principal
├── CONTRIBUTING.md            # Guía de contribución
├── LICENSE                    # Licencia MIT
├── .env.example               # Variables de entorno ejemplo
└── .gitignore                 # Archivos ignorados por Git
```

## 🎨 Componentes Frontend

### Componentes Principales

#### 1. **Header.jsx**
```jsx
// Barra de navegación superior
const Header = () => {
  const { user, logout } = useAuth();
  
  return (
    <header className="bg-white shadow-sm border-b">
      <div className="flex items-center justify-between px-6 py-4">
        <div className="flex items-center space-x-4">
          <Logo />
          <GlobalSearch />
        </div>
        <UserMenu user={user} onLogout={logout} />
      </div>
    </header>
  );
};
```

**Funcionalidades:**
- Logo y branding
- Búsqueda global (Cmd/Ctrl + K)
- Menú de usuario
- Notificaciones en tiempo real

#### 2. **Sidebar.jsx**
```jsx
// Navegación lateral
const Sidebar = () => {
  const { user } = useAuth();
  const location = useLocation();
  
  const menuItems = [
    { path: '/dashboard', icon: Home, label: 'Dashboard' },
    { path: '/upload', icon: Upload, label: 'Subir Archivos' },
    { path: '/files', icon: FileText, label: 'Archivos' },
    { path: '/groups', icon: Users, label: 'Grupos' },
    { path: '/messages', icon: MessageSquare, label: 'Mensajes' },
    // ... más items
  ];
  
  return (
    <aside className="w-64 bg-gray-50 border-r">
      <nav className="p-4">
        {menuItems.map(item => (
          <SidebarItem 
            key={item.path} 
            {...item} 
            isActive={location.pathname === item.path}
          />
        ))}
      </nav>
    </aside>
  );
};
```

**Funcionalidades:**
- Navegación por secciones
- Indicador de página activa
- Iconos con Lucide React
- Responsive design

#### 3. **GlobalSearch.jsx**
```jsx
// Búsqueda global avanzada
const GlobalSearch = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  
  // Atajo de teclado Cmd/Ctrl + K
  useEffect(() => {
    const handleKeyDown = (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        setIsOpen(true);
      }
    };
    
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, []);
  
  return (
    <CommandDialog open={isOpen} onOpenChange={setIsOpen}>
      <CommandInput 
        placeholder="Buscar archivos, grupos, usuarios..."
        value={query}
        onValueChange={setQuery}
      />
      <CommandList>
        <CommandEmpty>No se encontraron resultados.</CommandEmpty>
        <CommandGroup heading="Archivos">
          {results.files?.map(file => (
            <CommandItem key={file.id}>
              <FileIcon className="mr-2 h-4 w-4" />
              {file.name}
            </CommandItem>
          ))}
        </CommandGroup>
        {/* Más grupos de resultados */}
      </CommandList>
    </CommandDialog>
  );
};
```

**Funcionalidades:**
- Búsqueda en tiempo real
- Resultados categorizados
- Navegación por teclado
- Atajo Cmd/Ctrl + K

### Vistas Principales

#### 1. **HomeView.jsx - Dashboard**
```jsx
const HomeView = () => {
  const [stats, setStats] = useState(null);
  const [recentFiles, setRecentFiles] = useState([]);
  const [analytics, setAnalytics] = useState(null);
  
  return (
    <Layout>
      <div className="p-6">
        <h1 className="text-2xl font-bold mb-6">Dashboard</h1>
        
        {/* Métricas principales */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <StatCard 
            title="Total Archivos" 
            value={stats?.totalFiles} 
            icon={FileText}
            trend="+12%"
          />
          <StatCard 
            title="Grupos Activos" 
            value={stats?.activeGroups} 
            icon={Users}
            trend="+5%"
          />
          <StatCard 
            title="Mensajes Hoy" 
            value={stats?.todayMessages} 
            icon={MessageSquare}
            trend="+23%"
          />
          <StatCard 
            title="Usuarios Online" 
            value={stats?.onlineUsers} 
            icon={Activity}
            trend="+8%"
          />
        </div>
        
        {/* Gráficos y tablas */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Actividad Reciente</CardTitle>
            </CardHeader>
            <CardContent>
              <ActivityChart data={analytics?.activity} />
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader>
              <CardTitle>Archivos Recientes</CardTitle>
            </CardHeader>
            <CardContent>
              <RecentFilesList files={recentFiles} />
            </CardContent>
          </Card>
        </div>
      </div>
    </Layout>
  );
};
```

#### 2. **FilesView.jsx - Gestión de Archivos**
```jsx
const FilesView = () => {
  const [files, setFiles] = useState([]);
  const [filters, setFilters] = useState({});
  const [viewMode, setViewMode] = useState('grid'); // grid | list
  
  return (
    <Layout>
      <div className="p-6">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-2xl font-bold">Gestión de Archivos</h1>
          <div className="flex space-x-2">
            <Button onClick={() => navigate('/upload')}>
              <Upload className="w-4 h-4 mr-2" />
              Subir Archivo
            </Button>
            <DataExport data={files} />
          </div>
        </div>
        
        {/* Filtros */}
        <FileFilters 
          filters={filters} 
          onFiltersChange={setFilters}
        />
        
        {/* Vista de archivos */}
        <div className="mt-6">
          {viewMode === 'grid' ? (
            <FileGrid files={files} />
          ) : (
            <FileList files={files} />
          )}
        </div>
      </div>
    </Layout>
  );
};
```

## 🔧 API Backend

### Estructura de Rutas

#### 1. **Autenticación (/api/v1/auth)**
```javascript
// POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}

// Respuesta
{
  "success": true,
  "data": {
    "user": {
      "id": "user_id",
      "email": "user@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "role": "user"
    },
    "tokens": {
      "accessToken": "jwt_token",
      "refreshToken": "refresh_token"
    }
  }
}
```

#### 2. **Gestión de Archivos (/api/v1/files)**
```javascript
// GET /api/v1/files
// Respuesta
{
  "success": true,
  "data": {
    "files": [
      {
        "id": "file_id",
        "filename": "document.pdf",
        "originalName": "Important Document.pdf",
        "size": 1024000,
        "mimeType": "application/pdf",
        "uploadedBy": "user_id",
        "createdAt": "2024-01-01T00:00:00Z",
        "tags": ["important", "document"],
        "category": "document"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 100,
      "pages": 5
    }
  }
}
```

### Middleware de Seguridad

#### 1. **Autenticación JWT**
```javascript
const authenticateToken = (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];
  
  if (!token) {
    return res.status(401).json({
      success: false,
      message: 'Token de acceso requerido'
    });
  }
  
  jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
    if (err) {
      return res.status(403).json({
        success: false,
        message: 'Token inválido'
      });
    }
    
    req.user = user;
    next();
  });
};
```

#### 2. **Rate Limiting**
```javascript
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 100, // máximo 100 requests por ventana
  message: {
    error: 'Demasiadas solicitudes, intente más tarde',
    code: 'RATE_LIMIT_EXCEEDED'
  }
});
```

## 🗄️ Base de Datos

### MongoDB Collections

#### 1. **users**
```javascript
{
  _id: ObjectId,
  email: String, // único, requerido
  password: String, // hasheado con bcrypt
  firstName: String,
  lastName: String,
  role: String, // 'admin', 'user', 'analyst', 'investigator'
  department: String,
  position: String,
  avatar: String, // URL del avatar
  isActive: Boolean,
  lastLogin: Date,
  notificationSettings: {
    email: Boolean,
    push: Boolean,
    desktop: Boolean
  },
  privacySettings: {
    profileVisible: Boolean,
    showOnlineStatus: Boolean
  },
  createdAt: Date,
  updatedAt: Date
}
```

#### 2. **files**
```javascript
{
  _id: ObjectId,
  filename: String, // nombre único del archivo
  originalName: String, // nombre original
  path: String, // ruta en el sistema de archivos
  url: String, // URL pública (si aplica)
  size: Number, // tamaño en bytes
  mimeType: String,
  category: String, // 'document', 'image', 'video', 'audio', 'other'
  tags: [String], // etiquetas para búsqueda
  description: String,
  uploadedBy: ObjectId, // referencia a users
  isPublic: Boolean,
  accessLevel: String, // 'public', 'internal', 'restricted', 'confidential'
  downloadCount: Number,
  viewCount: Number,
  lastAccessed: Date,
  metadata: {
    width: Number, // para imágenes
    height: Number, // para imágenes
    duration: Number, // para videos/audio
    pages: Number // para documentos
  },
  status: String, // 'active', 'archived', 'deleted'
  version: Number,
  parentFile: ObjectId, // para versiones
  createdAt: Date,
  updatedAt: Date,
  deletedAt: Date
}
```

### MySQL Tables

#### 1. **audit_logs**
```sql
CREATE TABLE audit_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id VARCHAR(50),
  action VARCHAR(100) NOT NULL,
  resource_type VARCHAR(50),
  resource_id VARCHAR(50),
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_action (action),
  INDEX idx_timestamp (timestamp)
);
```

#### 2. **analytics**
```sql
CREATE TABLE analytics (
  id INT PRIMARY KEY AUTO_INCREMENT,
  metric_name VARCHAR(100) NOT NULL,
  metric_value DECIMAL(15,2),
  dimensions JSON,
  date_recorded DATE NOT NULL,
  hour_recorded TINYINT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_metric_date (metric_name, date_recorded),
  INDEX idx_date (date_recorded)
);
```

## 🔐 Autenticación y Seguridad

### Sistema JWT

#### 1. **Generación de Tokens**
```javascript
const generateTokens = (user) => {
  const payload = {
    id: user._id,
    email: user.email,
    role: user.role
  };
  
  const accessToken = jwt.sign(
    payload,
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRE || '24h' }
  );
  
  const refreshToken = jwt.sign(
    payload,
    process.env.JWT_REFRESH_SECRET,
    { expiresIn: process.env.JWT_REFRESH_EXPIRE || '7d' }
  );
  
  return { accessToken, refreshToken };
};
```

#### 2. **Middleware de Autorización**
```javascript
const requireRole = (roles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        success: false,
        message: 'No autenticado'
      });
    }
    
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: 'Permisos insuficientes'
      });
    }
    
    next();
  };
};

// Uso
router.get('/admin/users', 
  authenticateToken, 
  requireRole(['admin']), 
  getUsersController
);
```

### Seguridad Implementada

1. **Helmet.js** - Headers de seguridad
2. **CORS** - Control de acceso entre dominios
3. **Rate Limiting** - Prevención de ataques DDoS
4. **Input Validation** - Validación con Joi
5. **SQL Injection Prevention** - Uso de ORMs
6. **XSS Protection** - Sanitización de inputs
7. **HTTPS Enforcement** - En producción

## 🧪 Testing

### Configuración Jest

```javascript
// jest.config.js
module.exports = {
  testEnvironment: 'node',
  testMatch: [
    '**/tests/**/*.test.js',
    '**/tests/**/*.spec.js'
  ],
  collectCoverageFrom: [
    'routes/**/*.js',
    'middleware/**/*.js',
    'config/**/*.js',
    'utils/**/*.js'
  ],
  coverageDirectory: 'coverage',
  setupFilesAfterEnv: ['<rootDir>/tests/setup.js']
};
```

### Ejemplo de Test

```javascript
describe('Authentication', () => {
  test('should login with valid credentials', async () => {
    const response = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'test@example.com',
        password: 'password123'
      });
    
    expect(response.status).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.data.tokens).toBeDefined();
  });
});
```

## 🚀 Despliegue

### Variables de Entorno Requeridas

```env
# Servidor
NODE_ENV=production
PORT=5001

# Base de Datos
MONGODB_URI=mongodb+srv://...
MYSQL_HOST=localhost
MYSQL_DATABASE=evidence_management

# JWT
JWT_SECRET=your-secret-key
JWT_REFRESH_SECRET=your-refresh-secret

# Archivos
MAX_FILE_SIZE=2147483648
AWS_S3_BUCKET=your-bucket

# Email
SMTP_HOST=smtp.gmail.com
SMTP_USER=your-email@gmail.com
```

### Scripts de Despliegue

```bash
# Construcción
npm run build

# Inicio en producción
npm start

# Con PM2
pm2 start server.js --name "evidence-api"
```

## 🔧 Troubleshooting

### Problemas Comunes

1. **Error de conexión a MongoDB**
   - Verificar URI de conexión
   - Comprobar whitelist de IPs en Atlas
   - Validar credenciales

2. **Error de CORS**
   - Configurar CORS_ORIGINS en .env
   - Verificar headers de requests

3. **Archivos no se suben**
   - Verificar permisos de carpeta uploads/
   - Comprobar MAX_FILE_SIZE
   - Validar tipos de archivo permitidos

### Logs y Monitoreo

```javascript
// Configuración de logs
const winston = require('winston');

const logger = winston.createLogger({
  level: 'info',
  format: winston.format.json(),
  transports: [
    new winston.transports.File({ filename: 'error.log', level: 'error' }),
    new winston.transports.File({ filename: 'combined.log' })
  ]
});
```

---

## 📞 Soporte

Para más información o soporte técnico:
- 📧 Email: support@evidence-platform.com
- 📖 Wiki: [GitHub Wiki](https://github.com/tu-usuario/evidence-management-platform/wiki)
- 🐛 Issues: [GitHub Issues](https://github.com/tu-usuario/evidence-management-platform/issues)
