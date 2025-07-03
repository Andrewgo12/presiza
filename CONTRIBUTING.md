# 🤝 Guía de Contribución

¡Gracias por tu interés en contribuir al Sistema de Gestión de Evidencias! Esta guía te ayudará a empezar.

## 📋 Tabla de Contenidos

1. [Código de Conducta](#código-de-conducta)
2. [¿Cómo puedo contribuir?](#cómo-puedo-contribuir)
3. [Configuración del entorno de desarrollo](#configuración-del-entorno-de-desarrollo)
4. [Proceso de desarrollo](#proceso-de-desarrollo)
5. [Estándares de código](#estándares-de-código)
6. [Proceso de Pull Request](#proceso-de-pull-request)
7. [Reporte de bugs](#reporte-de-bugs)
8. [Solicitud de características](#solicitud-de-características)

## 📜 Código de Conducta

Este proyecto se adhiere al [Código de Conducta del Contribuyente](CODE_OF_CONDUCT.md). Al participar, se espera que mantengas este código.

## 🚀 ¿Cómo puedo contribuir?

### Tipos de contribuciones que buscamos

- 🐛 **Corrección de bugs**
- ✨ **Nuevas características**
- 📚 **Mejoras en documentación**
- 🎨 **Mejoras en UI/UX**
- ⚡ **Optimizaciones de rendimiento**
- 🧪 **Pruebas adicionales**
- 🌐 **Traducciones**

### Primeras contribuciones

¿Es tu primera vez contribuyendo? Busca issues etiquetados con:
- `good first issue` - Problemas fáciles para empezar
- `help wanted` - Issues donde necesitamos ayuda
- `documentation` - Mejoras en documentación

## 🛠️ Configuración del entorno de desarrollo

### Prerrequisitos

- Node.js 16 o superior
- npm 8 o superior
- Git

### Configuración inicial

1. **Fork el repositorio**
   \`\`\`bash
   # Haz click en "Fork" en GitHub, luego clona tu fork
   git clone https://github.com/TU_USUARIO/evidence-management-platform.git
   cd evidence-management-platform
   \`\`\`

2. **Configura el repositorio upstream**
   \`\`\`bash
   git remote add upstream https://github.com/USUARIO_ORIGINAL/evidence-management-platform.git
   \`\`\`

3. **Instala dependencias**
   \`\`\`bash
   npm install
   \`\`\`

4. **Inicia el servidor de desarrollo**
   \`\`\`bash
   npm start
   \`\`\`

5. **Verifica que todo funcione**
   - Abre http://localhost:3000
   - Ejecuta las pruebas: `npm test`
   - Ejecuta el linter: `npm run lint`

## 🔄 Proceso de desarrollo

### Flujo de trabajo con Git

1. **Mantén tu fork actualizado**
   \`\`\`bash
   git fetch upstream
   git checkout main
   git merge upstream/main
   \`\`\`

2. **Crea una rama para tu feature**
   \`\`\`bash
   git checkout -b feature/nombre-descriptivo
   # o para bugs:
   git checkout -b fix/descripcion-del-bug
   \`\`\`

3. **Haz tus cambios**
   - Escribe código limpio y bien documentado
   - Añade pruebas si es necesario
   - Actualiza documentación si es relevante

4. **Commit tus cambios**
   \`\`\`bash
   git add .
   git commit -m "feat: añade nueva funcionalidad de búsqueda avanzada"
   \`\`\`

### Convenciones de commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` Nueva característica
- `fix:` Corrección de bug
- `docs:` Cambios en documentación
- `style:` Cambios de formato (no afectan funcionalidad)
- `refactor:` Refactorización de código
- `test:` Añadir o modificar pruebas
- `chore:` Tareas de mantenimiento

**Ejemplos:**
\`\`\`bash
feat: añade sistema de notificaciones push
fix: corrige error en carga de archivos grandes
docs: actualiza guía de instalación
style: mejora espaciado en componente Header
refactor: optimiza consultas de base de datos
test: añade pruebas para AuthContext
chore: actualiza dependencias
\`\`\`

## 📏 Estándares de código

### JavaScript/React

- Usa **ES6+** y características modernas de JavaScript
- Prefiere **functional components** con hooks
- Usa **destructuring** cuando sea apropiado
- Mantén funciones **pequeñas y enfocadas**
- Usa **nombres descriptivos** para variables y funciones

### Estructura de componentes

\`\`\`jsx
// ✅ Buena estructura
import React, { useState, useEffect } from 'react';
import { ComponentePadre } from './ComponentePadre';

const MiComponente = ({ prop1, prop2, ...props }) => {
  // 1. Hooks de estado
  const [estado, setEstado] = useState(null);
  
  // 2. Hooks de efecto
  useEffect(() => {
    // lógica del efecto
  }, []);
  
  // 3. Funciones del componente
  const manejarClick = () => {
    // lógica del handler
  };
  
  // 4. Renderizado condicional temprano
  if (!estado) {
    return <div>Cargando...</div>;
  }
  
  // 5. JSX principal
  return (
    <div className="mi-componente">
      {/* contenido */}
    </div>
  );
};

export default MiComponente;
\`\`\`

### CSS/Tailwind

- Usa **Tailwind CSS** para estilos
- Prefiere **clases utilitarias** sobre CSS personalizado
- Mantén **consistencia** en espaciado y colores
- Usa **responsive design** desde el inicio

\`\`\`jsx
// ✅ Buen uso de Tailwind
<div className="flex flex-col md:flex-row gap-4 p-6 bg-white rounded-lg shadow-md">
  <div className="flex-1">
    <h2 className="text-xl font-semibold text-gray-900 mb-2">
      Título
    </h2>
    <p className="text-gray-600 leading-relaxed">
      Descripción
    </p>
  </div>
</div>
\`\`\`

### Accesibilidad

- Usa **elementos semánticos** HTML
- Incluye **atributos ARIA** cuando sea necesario
- Asegura **contraste de colores** adecuado
- Implementa **navegación por teclado**

\`\`\`jsx
// ✅ Componente accesible
<button
  className="btn-primary"
  onClick={manejarClick}
  aria-label="Cerrar modal"
  aria-pressed={isPressed}
>
  <X className="w-4 h-4" aria-hidden="true" />
  <span className="sr-only">Cerrar</span>
</button>
\`\`\`

## 🔍 Pruebas

### Ejecutar pruebas

\`\`\`bash
# Ejecutar todas las pruebas
npm test

# Ejecutar pruebas en modo watch
npm test -- --watch

# Ejecutar pruebas con coverage
npm test -- --coverage
\`\`\`

### Escribir pruebas

\`\`\`jsx
// ✅ Ejemplo de prueba
import { render, screen, fireEvent } from '@testing-library/react';
import MiComponente from './MiComponente';

describe('MiComponente', () => {
  test('renderiza correctamente', () => {
    render(<MiComponente prop1="valor" />);
    
    expect(screen.getByText('Texto esperado')).toBeInTheDocument();
  });
  
  test('maneja clicks correctamente', () => {
    const mockHandler = jest.fn();
    render(<MiComponente onClick={mockHandler} />);
    
    fireEvent.click(screen.getByRole('button'));
    
    expect(mockHandler).toHaveBeenCalledTimes(1);
  });
});
\`\`\`

## 📝 Proceso de Pull Request

### Antes de enviar

1. **Ejecuta todas las verificaciones**
   \`\`\`bash
   npm run lint        # Verifica estilo de código
   npm test           # Ejecuta pruebas
   npm run build      # Verifica que compile
   \`\`\`

2. **Actualiza tu rama**
   \`\`\`bash
   git fetch upstream
   git rebase upstream/main
   \`\`\`

3. **Push a tu fork**
   \`\`\`bash
   git push origin feature/tu-rama
   \`\`\`

### Creando el Pull Request

1. Ve a GitHub y crea un Pull Request
2. Usa el template proporcionado
3. Incluye:
   - **Descripción clara** de los cambios
   - **Screenshots** si hay cambios visuales
   - **Referencias** a issues relacionados
   - **Lista de verificación** completada

### Template de Pull Request

\`\`\`markdown
## 📋 Descripción

Breve descripción de los cambios realizados.

## 🔗 Issue relacionado

Fixes #123

## 🧪 Tipo de cambio

- [ ] Bug fix (cambio que corrige un issue)
- [ ] Nueva característica (cambio que añade funcionalidad)
- [ ] Breaking change (cambio que podría romper funcionalidad existente)
- [ ] Documentación

## ✅ Lista de verificación

- [ ] Mi código sigue las guías de estilo del proyecto
- [ ] He realizado una auto-revisión de mi código
- [ ] He comentado mi código, especialmente en áreas difíciles de entender
- [ ] He realizado cambios correspondientes en la documentación
- [ ] Mis cambios no generan nuevas advertencias
- [ ] He añadido pruebas que demuestran que mi fix es efectivo o que mi característica funciona
- [ ] Las pruebas unitarias nuevas y existentes pasan localmente con mis cambios

## 📸 Screenshots (si aplica)

Añade screenshots para mostrar los cambios visuales.
\`\`\`

### Revisión del código

- Responde a los comentarios de manera constructiva
- Realiza cambios solicitados promptamente
- Mantén la discusión enfocada en el código

## 🐛 Reporte de bugs

### Antes de reportar

1. **Busca** en issues existentes
2. **Verifica** que sea reproducible
3. **Prueba** en la última versión

### Template de bug report

\`\`\`markdown
## 🐛 Descripción del bug

Descripción clara y concisa del bug.

## 🔄 Pasos para reproducir

1. Ve a '...'
2. Haz click en '...'
3. Desplázate hacia '...'
4. Ve el error

## ✅ Comportamiento esperado

Descripción clara de lo que esperabas que pasara.

## 📸 Screenshots

Si aplica, añade screenshots para ayudar a explicar el problema.

## 🖥️ Información del entorno

- OS: [e.g. macOS, Windows, Linux]
- Navegador: [e.g. Chrome, Firefox, Safari]
- Versión: [e.g. 22]

## 📝 Contexto adicional

Añade cualquier otro contexto sobre el problema aquí.
\`\`\`

## ✨ Solicitud de características

### Template de feature request

\`\`\`markdown
## 🚀 Descripción de la característica

Descripción clara y concisa de la característica que te gustaría ver.

## 💡 Motivación

¿Por qué es importante esta característica? ¿Qué problema resuelve?

## 📋 Solución propuesta

Descripción clara de lo que quieres que pase.

## 🔄 Alternativas consideradas

Descripción de cualquier solución alternativa que hayas considerado.

## 📝 Contexto adicional

Añade cualquier otro contexto o screenshots sobre la solicitud aquí.
\`\`\`

## 🏷️ Etiquetas de issues

- `bug` - Algo no está funcionando
- `enhancement` - Nueva característica o solicitud
- `documentation` - Mejoras o adiciones a documentación
- `good first issue` - Bueno para nuevos contribuyentes
- `help wanted` - Ayuda extra es bienvenida
- `question` - Información adicional es solicitada
- `wontfix` - Esto no será trabajado

## 🎉 Reconocimiento

Los contribuyentes serán reconocidos en:
- README.md
- Página de contribuyentes
- Release notes (para contribuciones significativas)

## 📞 ¿Necesitas ayuda?

- 💬 [Discusiones de GitHub](https://github.com/tu-usuario/evidence-management-platform/discussions)
- 📧 Email: contribute@evidence-platform.com
- 🐛 [Issues](https://github.com/tu-usuario/evidence-management-platform/issues)

¡Gracias por contribuir! 🙏
