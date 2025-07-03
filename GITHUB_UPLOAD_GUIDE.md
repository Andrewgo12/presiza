# 📤 Guía para Subir el Proyecto a GitHub

## 🎯 Preparación Completada

El proyecto **Sistema de Gestión de Evidencias** está listo para ser subido a GitHub. Todos los archivos sensibles están protegidos y la documentación está actualizada.

## 📋 Pasos para Subir a GitHub

### 1. Verificar que estás en el directorio correcto
```bash
cd c:\Users\kevin\Desktop\rep
```

### 2. Inicializar Git (si no está inicializado)
```bash
git init
```

### 3. Agregar el repositorio remoto
```bash
git remote add origin https://github.com/Andrewgo12/reportes.git
```

### 4. Verificar que los archivos sensibles están excluidos
```bash
# Verificar que estos archivos NO aparezcan en git status:
# - backend/.env
# - node_modules/
# - backend/node_modules/
# - .next/
# - backend/uploads/

git status
```

### 5. Agregar todos los archivos
```bash
git add .
```

### 6. Crear el commit inicial
```bash
git commit -m "feat: Sistema de Gestión de Evidencias completo

- ✅ Frontend React 19 + Next.js 15 con shadcn/ui
- ✅ Backend Node.js + Express + MongoDB
- ✅ Autenticación JWT real implementada
- ✅ API RESTful completa con todos los endpoints
- ✅ Gestión de archivos con upload real
- ✅ Sistema de grupos y colaboración
- ✅ Dashboard con analytics
- ✅ Diseño responsivo y moderno
- ✅ Documentación completa en español
- ✅ Configuración de producción lista

Funcionalidades principales:
- Autenticación y autorización con roles
- Gestión completa de usuarios
- Upload y gestión de archivos (100+ tipos)
- Grupos de colaboración
- Mensajería (API ready)
- Analytics y reportes
- Búsqueda global avanzada
- Interfaz responsive y moderna"
```

### 7. Subir al repositorio
```bash
# Si es la primera vez
git push -u origin main

# O si ya existe
git push origin main
```

## ✅ Verificación Post-Upload

Después de subir, verifica en GitHub que:

1. **Archivos incluidos**:
   - ✅ Todo el código fuente (frontend y backend)
   - ✅ README.md actualizado
   - ✅ DOCUMENTACION_COMPLETA.md
   - ✅ package.json (ambos)
   - ✅ .env.example (ambos)

2. **Archivos excluidos**:
   - ❌ .env (archivos de entorno)
   - ❌ node_modules/ (dependencias)
   - ❌ backend/uploads/ (archivos subidos)
   - ❌ .next/ (build de Next.js)

3. **Documentación**:
   - ✅ README.md con instrucciones completas
   - ✅ Credenciales de demo documentadas
   - ✅ Estructura del proyecto explicada

## 🔧 Comandos de Troubleshooting

Si hay problemas durante el upload:

```bash
# Verificar estado de Git
git status

# Ver archivos que serán incluidos
git ls-files

# Verificar .gitignore
cat .gitignore

# Forzar push si hay conflictos (usar con cuidado)
git push --force-with-lease origin main

# Verificar remotes
git remote -v
```

## 📞 Soporte

Si encuentras algún problema durante el upload:
1. Verifica que tienes permisos de escritura en el repositorio
2. Asegúrate de estar autenticado en GitHub
3. Revisa que no hay archivos demasiado grandes (>100MB)

## 🎉 ¡Listo!

Una vez completado el upload, el proyecto estará disponible en:
**https://github.com/Andrewgo12/reportes**

Otros desarrolladores podrán clonar y ejecutar el proyecto siguiendo las instrucciones del README.md.
