# 🛡️ Sistema de Gestión de Evidencias

[![React](https://img.shields.io/badge/React-18.0-blue.svg)](https://reactjs.org/)
[![Node.js](https://img.shields.io/badge/Node.js-16+-green.svg)](https://nodejs.org/)
[![MongoDB](https://img.shields.io/badge/MongoDB-4.4+-brightgreen.svg)](https://mongodb.com/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## 📋 Descripción

Sistema completo de gestión de evidencias con funcionalidades avanzadas de colaboración, evaluación y análisis. Desarrollado con tecnologías modernas para proporcionar una experiencia de usuario excepcional.

## ✨ Características Principales

### 🔐 Autenticación y Seguridad
- Sistema de login seguro con JWT
- Roles de usuario (Admin/Usuario)
- Protección de rutas sensibles
- Gestión de sesiones

### 📤 Gestión de Archivos
- Soporte para 100+ tipos de archivo
- Carga por arrastrar y soltar
- Límite de 2GB por archivo
- Vista previa de imágenes
- Sistema de etiquetas

### 👥 Colaboración en Grupos
- Grupos públicos, privados y protegidos
- Gestión de miembros y roles
- Sistema de invitaciones
- Configuraciones personalizables

### 🛡️ Evaluación de Evidencias
- Sistema de calificación (1-5 estrellas)
- Comentarios y retroalimentación
- Estados de aprobación
- Historial de evaluaciones

### 💬 Comunicación
- Mensajería en tiempo real
- Chats individuales y grupales
- Estados de entrega y lectura
- Notificaciones push

### 📊 Analytics y Reportes
- Dashboard con métricas en tiempo real
- Gráficos interactivos
- Exportación de datos (PDF, CSV, JSON, XML)
- Reportes personalizables

### 🔍 Búsqueda Avanzada
- Búsqueda global con `Cmd/Ctrl + K`
- Filtros inteligentes
- Resultados categorizados
- Navegación por teclado

### 📱 Diseño Responsivo
- Optimizado para móviles, tablets y desktop
- Interfaz moderna y intuitiva
- Animaciones fluidas
- Modo oscuro (próximamente)

## 🚀 Inicio Rápido

### Prerrequisitos
- Node.js 16 o superior
- npm o yarn
- MongoDB 4.4+ (para producción)

### Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/Andrewgo12/reportes.git
cd reportes
```

2. **Configurar Frontend**
```bash
# Instalar dependencias del frontend
npm install

# Configurar variables de entorno (opcional)
cp .env.example .env.local
```

3. **Configurar Backend**
```bash
# Navegar al directorio backend
cd backend

# Instalar dependencias
npm install

# Configurar variables de entorno
cp .env.example .env

# Editar .env con tus configuraciones
# MONGODB_URI=mongodb://localhost:27017/evidence_management
# JWT_SECRET=tu-clave-secreta-jwt
# PORT=5001
```

4. **Iniciar MongoDB**
```bash
# Si usas MongoDB local
mongod

# O usar MongoDB Atlas (configurar MONGODB_URI en .env)
```

5. **Ejecutar la Aplicación**

**Desarrollo (2 terminales):**
```bash
# Terminal 1: Backend
cd backend
node server.js

# Terminal 2: Frontend (desde la raíz)
npm run dev
```

6. **Abrir en el navegador**
```
Frontend: http://localhost:3000
Backend API: http://localhost:5001/api/v1
Health Check: http://localhost:5001/health
```

### Credenciales de Demo

**Administrador:**
- Email: `admin@company.com`
- Password: `admin123`

**Usuario Regular:**
- Email: `user@company.com`
- Password: `user123`

## 🏗️ Arquitectura del Sistema

\`\`\`
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (React.js)                      │
├─────────────────────────────────────────────────────────────┤
│  Views          │  Components     │  Context & Services     │
│  ├─ LoginView   │  ├─ Header      │  ├─ AuthContext        │
│  ├─ HomeView    │  ├─ Sidebar     │  ├─ NotificationSystem │
│  ├─ UploadView  │  ├─ GlobalSearch│  └─ API Services       │
│  ├─ GroupsView  │  ├─ DataExport  │                        │
│  ├─ FilesView   │  └─ Reports     │                        │
│  └─ ...         │                 │                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (Node.js)                       │
├─────────────────────────────────────────────────────────────┤
│  Controllers    │  Models         │  Routes & Middleware    │
│  ├─ Auth        │  ├─ User        │  ├─ Authentication      │
│  ├─ Files       │  ├─ File        │  ├─ File Upload        │
│  ├─ Groups      │  ├─ Group       │  ├─ CORS & Security    │
│  ├─ Messages    │  ├─ Message     │  └─ Error Handling     │
│  └─ Analytics   │  └─ Evidence    │                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE (MongoDB)                      │
├─────────────────────────────────────────────────────────────┤
│  Collections: users, files, groups, messages, evidences    │
│  Indexes: email, timestamps, file_types, group_members     │
│  Aggregations: analytics, reports, statistics              │
└─────────────────────────────────────────────────────────────┘
\`\`\`

## 📱 Capturas de Pantalla

### Dashboard Principal
![Dashboard](docs/screenshots/dashboard.png)

### Gestión de Archivos
![Files Management](docs/screenshots/files.png)

### Sistema de Grupos
![Groups](docs/screenshots/groups.png)

### Mensajería
![Messages](docs/screenshots/messages.png)

## 🛠️ Tecnologías Utilizadas

### Frontend
- **React.js 18** - Framework principal
- **React Router DOM** - Navegación
- **Tailwind CSS** - Estilos y diseño responsivo
- **Lucide React** - Iconografía moderna
- **Recharts** - Gráficos y visualizaciones

### Backend (Para producción)
- **Node.js** - Runtime de JavaScript
- **Express.js** - Framework web
- **MongoDB** - Base de datos NoSQL
- **Mongoose** - ODM para MongoDB
- **JWT** - Autenticación
- **Multer** - Carga de archivos
- **bcryptjs** - Encriptación de contraseñas

### Herramientas de Desarrollo
- **ESLint** - Linting de código
- **Prettier** - Formateo de código
- **Concurrently** - Ejecución de scripts paralelos

## 📚 Documentación

- [📖 Documentación Completa](DOCUMENTACION_COMPLETA.md) - Guía detallada de clases y funciones
- [🔧 Guía de Instalación](docs/INSTALLATION.md) - Instrucciones paso a paso
- [🚀 Guía de Despliegue](docs/DEPLOYMENT.md) - Despliegue en producción
- [🔌 API Reference](docs/API_REFERENCE.md) - Documentación de la API
- [🎨 Guía de Diseño](docs/DESIGN_GUIDE.md) - Principios de diseño y UI/UX

## 🔧 Scripts Disponibles

\`\`\`bash
# Desarrollo
npm start              # Inicia la aplicación en modo desarrollo
npm run build          # Construye la aplicación para producción
npm test               # Ejecuta las pruebas
npm run lint           # Ejecuta el linter
npm run format         # Formatea el código

# Backend (cuando esté configurado)
npm run server         # Inicia el servidor backend
npm run dev            # Inicia frontend y backend simultáneamente
npm run seed           # Pobla la base de datos con datos de prueba
\`\`\`

## 🌟 Características Avanzadas

### Atajos de Teclado
- `Cmd/Ctrl + K` - Búsqueda global
- `Cmd/Ctrl + Shift + R` - Generar reporte
- `Cmd/Ctrl + Shift + E` - Exportar datos
- `Esc` - Cerrar modales

### Funcionalidades de Accesibilidad
- Navegación por teclado completa
- Soporte para lectores de pantalla
- Contraste de colores optimizado
- Textos alternativos en imágenes

### Optimizaciones de Rendimiento
- Lazy loading de componentes
- Optimización de imágenes
- Caché inteligente
- Compresión de assets

## 🤝 Contribución

¡Las contribuciones son bienvenidas! Por favor, sigue estos pasos:

1. **Fork** el proyecto
2. **Crea** una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. **Abre** un Pull Request

### Guías de Contribución
- Sigue las convenciones de código existentes
- Añade tests para nuevas funcionalidades
- Actualiza la documentación cuando sea necesario
- Usa commits descriptivos

## 🐛 Reporte de Bugs

Si encuentras un bug, por favor:

1. Verifica que no haya sido reportado anteriormente
2. Crea un issue con información detallada
3. Incluye pasos para reproducir el problema
4. Añade capturas de pantalla si es relevante

## 📈 Roadmap

### Versión 1.1 (Próximamente)
- [ ] Modo oscuro
- [ ] Notificaciones push reales
- [ ] Integración con servicios de nube
- [ ] API REST completa

### Versión 1.2
- [ ] Aplicación móvil (React Native)
- [ ] Integración con Microsoft Office
- [ ] Sistema de workflows
- [ ] Análisis de sentimientos en comentarios

### Versión 2.0
- [ ] Inteligencia artificial para categorización
- [ ] Colaboración en tiempo real
- [ ] Sistema de plugins
- [ ] Multi-tenancy

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 👥 Equipo

- **Desarrollador Principal** - [Tu Nombre](https://github.com/tu-usuario)
- **Diseñador UI/UX** - [Nombre del Diseñador](https://github.com/diseñador)
- **Arquitecto de Software** - [Nombre del Arquitecto](https://github.com/arquitecto)

## 🙏 Agradecimientos

- Comunidad de React.js por las herramientas increíbles
- Equipo de Tailwind CSS por el framework de estilos
- Contribuidores de código abierto
- Beta testers y usuarios que proporcionaron feedback

## 📞 Soporte

¿Necesitas ayuda? Contáctanos:

- 📧 Email: support@evidence-platform.com
- 💬 Discord: [Servidor de la Comunidad](https://discord.gg/evidence-platform)
- 📖 Wiki: [Documentación Completa](https://github.com/tu-usuario/evidence-management-platform/wiki)
- 🐛 Issues: [GitHub Issues](https://github.com/tu-usuario/evidence-management-platform/issues)

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, ¡no olvides darle una estrella! ⭐**

[🚀 Demo en Vivo](https://evidence-platform-demo.vercel.app) | [📚 Documentación](DOCUMENTACION_COMPLETA.md) | [🔧 API Docs](docs/API_REFERENCE.md)

</div>
\`\`\`
