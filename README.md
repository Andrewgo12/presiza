# 🚀 Sistema de Gestión de Evidencias - Laravel

## 📋 Descripción

Sistema completo de gestión de evidencias desarrollado en Laravel 11, diseñado para organizaciones que necesitan un control riguroso de documentos, archivos y evidencias digitales con flujos de aprobación, roles de usuario y trazabilidad completa.

## ✨ Características Principales

### 🔐 Sistema de Autenticación y Autorización
- **Autenticación completa** con Laravel Breeze
- **4 roles de usuario**: Admin, Analyst, Investigator, User
- **Políticas granulares** de acceso a recursos
- **Middleware personalizado** para verificación de usuarios activos
- **Gestión de perfiles** con avatars y configuraciones

### 📁 Gestión Avanzada de Archivos
- **Subida con drag & drop** usando Alpine.js
- **Categorización automática** por tipo MIME
- **4 niveles de acceso**: Public, Internal, Restricted, Confidential
- **Generación automática de thumbnails** para imágenes
- **Sistema de etiquetas** dinámico
- **Previsualización** de archivos en el navegador
- **Control de descargas** y visualizaciones
- **Limpieza automática** de archivos expirados

### 🔍 Sistema de Evidencias
- **CRUD completo** con validaciones robustas
- **5 estados**: Pending, Under Review, Approved, Rejected, Archived
- **4 niveles de prioridad**: Low, Medium, High, Critical
- **6 categorías**: Security, Investigation, Compliance, Audit, Incident, Other
- **Sistema de asignación** a responsables
- **Flujo de evaluación** con calificaciones y recomendaciones
- **Historial completo** de cambios con trazabilidad
- **Asociación múltiple** con archivos

### 👥 Gestión de Usuarios y Grupos
- **Administración completa** de usuarios
- **Grupos de trabajo** con 3 tipos: Public, Private, Restricted
- **Sistema de membresías** con roles (Admin, Moderator, Member)
- **Estadísticas detalladas** por usuario
- **Estados activo/inactivo** con control de acceso

### 💬 Sistema de Mensajería
- **Mensajes directos** entre usuarios
- **Mensajes de grupo** con notificaciones
- **4 niveles de prioridad** para mensajes
- **Sistema de adjuntos** en mensajes
- **Marcado de leído/no leído**
- **Funciones de respuesta** y reenvío

### 📊 Dashboard y Analytics
- **Métricas en tiempo real** con datos actualizados
- **Gráficos interactivos** usando Chart.js
- **Estadísticas por usuario** y departamento
- **Actividad reciente** con filtros
- **Indicadores de rendimiento** (KPIs)

### 🎨 Interfaz de Usuario Moderna
- **Diseño responsivo** con Tailwind CSS
- **Componentes reutilizables** en Blade
- **Interactividad** con Alpine.js
- **Animaciones suaves** y transiciones
- **Iconos SVG** optimizados
- **Tema consistente** con variables CSS

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 11** - Framework PHP
- **MySQL** - Base de datos relacional
- **Eloquent ORM** - Mapeo objeto-relacional
- **Laravel Breeze** - Autenticación
- **Policies** - Autorización granular

### Frontend
- **Tailwind CSS 3** - Framework CSS utilitario
- **Alpine.js 3** - Framework JavaScript reactivo
- **Chart.js 4** - Gráficos interactivos
- **Blade Templates** - Motor de plantillas

### Herramientas de Desarrollo
- **Vite** - Bundler de assets
- **PHPUnit** - Testing framework
- **Laravel Factories** - Generación de datos de prueba
- **Artisan Commands** - Comandos personalizados

## 📦 Instalación

### Prerrequisitos
- PHP 8.2 o superior
- Composer
- Node.js 16+ y npm
- MySQL 8.0+
- Extensiones PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/laravel-reportes.git
cd laravel-reportes
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias Node.js**
```bash
npm install
```

4. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reportes_db
DB_USERNAME=root
DB_PASSWORD=tu_password
```

6. **Crear base de datos**
```sql
CREATE DATABASE reportes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. **Ejecutar migraciones y seeders**
```bash
php artisan migrate:fresh --seed
```

8. **Crear enlace de storage**
```bash
php artisan storage:link
```

9. **Compilar assets**
```bash
npm run build
```

10. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

## 👤 Usuarios de Prueba

El sistema incluye usuarios de prueba con diferentes roles:

| Email | Contraseña | Rol | Descripción |
|-------|------------|-----|-------------|
| admin@company.com | admin123 | Admin | Acceso completo al sistema |
| analyst@company.com | analyst123 | Analyst | Gestión de evidencias y análisis |
| investigator@company.com | investigator123 | Investigator | Investigación y evaluación |
| user@company.com | user123 | User | Usuario básico |

## 🧪 Testing

### Ejecutar tests
```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter AuthTest
php artisan test --filter FileTest
php artisan test --filter EvidenceTest
```

### Coverage de tests
```bash
php artisan test --coverage
```

## 📁 Estructura del Proyecto

```
laravel-reportes/
├── app/
│   ├── Console/Commands/          # Comandos Artisan personalizados
│   ├── Http/Controllers/          # Controladores
│   ├── Http/Middleware/           # Middleware personalizado
│   ├── Models/                    # Modelos Eloquent
│   ├── Policies/                  # Políticas de autorización
│   └── Services/                  # Servicios de negocio
├── database/
│   ├── factories/                 # Factories para testing
│   ├── migrations/                # Migraciones de BD
│   └── seeders/                   # Seeders de datos
├── resources/
│   ├── css/                       # Estilos CSS
│   ├── js/                        # JavaScript
│   └── views/                     # Vistas Blade
├── routes/
│   ├── web.php                    # Rutas web
│   └── api.php                    # Rutas API
└── tests/
    └── Feature/                   # Tests funcionales
```

## 🔧 Comandos Artisan Personalizados

### Limpieza de archivos expirados
```bash
php artisan files:cleanup-expired
php artisan files:cleanup-expired --dry-run
```

### Generar reporte del sistema
```bash
php artisan system:report
php artisan system:report --format=json
php artisan system:report --format=csv
```

## 📊 Características Técnicas

### Base de Datos
- **10 tablas principales** con relaciones optimizadas
- **Índices estratégicos** para consultas rápidas
- **Soft deletes** para recuperación de datos
- **Timestamps automáticos** en todas las tablas
- **Constraints de integridad** referencial

### Seguridad
- **Validación robusta** en todos los formularios
- **Sanitización** de datos de entrada
- **Protección CSRF** en formularios
- **Políticas granulares** de acceso
- **Logs de auditoría** para acciones críticas

### Performance
- **Eager loading** para evitar N+1 queries
- **Índices de base de datos** optimizados
- **Cache de configuración** y rutas
- **Assets minificados** en producción
- **Lazy loading** de imágenes

## 🚀 Despliegue en Producción

### Configuración de producción
```bash
# Optimizar autoloader
composer install --optimize-autoloader --no-dev

# Cache de configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compilar assets para producción
npm run build
```

### Variables de entorno importantes
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de datos de producción
DB_CONNECTION=mysql
DB_HOST=tu-host-db
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password_seguro

# Configuración de correo
MAIL_MAILER=smtp
MAIL_HOST=tu-smtp-host
MAIL_USERNAME=tu-email
MAIL_PASSWORD=tu-password
```

## 📈 Métricas y Monitoreo

El sistema incluye métricas detalladas:
- **Usuarios activos** y registros por período
- **Archivos subidos** y espacio utilizado
- **Evidencias procesadas** por estado y prioridad
- **Actividad de grupos** y mensajes
- **Performance** de consultas y operaciones

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o consultas:
- **Email**: soporte@evidencias.com
- **Documentación**: [Wiki del proyecto](https://github.com/tu-usuario/laravel-reportes/wiki)
- **Issues**: [GitHub Issues](https://github.com/tu-usuario/laravel-reportes/issues)

## 🎯 Roadmap

### Próximas características
- [ ] API REST completa
- [ ] Notificaciones push en tiempo real
- [ ] Integración con servicios de nube (AWS S3)
- [ ] Módulo de reportes avanzados
- [ ] Autenticación de dos factores (2FA)
- [ ] Modo oscuro (Dark mode)
- [ ] Aplicación móvil (PWA)

---

**Desarrollado con ❤️ usando Laravel 11**
