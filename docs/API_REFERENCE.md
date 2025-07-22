# 🔌 API Reference - Sistema de Gestión de Evidencias

## 📋 Tabla de Contenidos

1. [Información General](#información-general)
2. [Autenticación](#autenticación)
3. [Usuarios](#usuarios)
4. [Archivos](#archivos)
5. [Grupos](#grupos)
6. [Mensajes](#mensajes)
7. [Evidencias](#evidencias)
8. [Notificaciones](#notificaciones)
9. [Analytics](#analytics)
10. [Códigos de Error](#códigos-de-error)

## 🌐 Información General

### Base URL
```
Desarrollo: http://localhost:5001/api/v1
Producción: https://yourdomain.com/api/v1
```

### Formato de Respuesta
Todas las respuestas siguen este formato estándar:

```json
{
  "success": true,
  "data": {
    // Datos de respuesta
  },
  "message": "Mensaje descriptivo",
  "timestamp": "2024-01-01T00:00:00.000Z"
}
```

### Headers Requeridos
```http
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

### Paginación
Los endpoints que retornan listas incluyen paginación:

```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 100,
      "pages": 5,
      "hasNext": true,
      "hasPrev": false
    }
  }
}
```

## 🔐 Autenticación

### POST /auth/login
Iniciar sesión con email y contraseña.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "507f1f77bcf86cd799439011",
      "email": "user@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "role": "user",
      "department": "IT",
      "avatar": "https://example.com/avatar.jpg"
    },
    "tokens": {
      "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "expiresIn": 86400
    }
  },
  "message": "Login exitoso"
}
```

### POST /auth/refresh
Renovar token de acceso usando refresh token.

**Request:**
```json
{
  "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 86400
  }
}
```

### POST /auth/logout
Cerrar sesión e invalidar tokens.

**Request:**
```json
{
  "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### POST /auth/register
Registrar nuevo usuario (solo admins).

**Request:**
```json
{
  "email": "newuser@example.com",
  "password": "securePassword123",
  "firstName": "Jane",
  "lastName": "Smith",
  "role": "user",
  "department": "Marketing"
}
```

### POST /auth/forgot-password
Solicitar restablecimiento de contraseña.

**Request:**
```json
{
  "email": "user@example.com"
}
```

### POST /auth/reset-password
Restablecer contraseña con token.

**Request:**
```json
{
  "token": "reset_token_here",
  "newPassword": "newSecurePassword123"
}
```

## 👥 Usuarios

### GET /users
Obtener lista de usuarios (requiere autenticación).

**Query Parameters:**
- `page` (number): Página (default: 1)
- `limit` (number): Elementos por página (default: 20)
- `search` (string): Buscar por nombre o email
- `role` (string): Filtrar por rol
- `department` (string): Filtrar por departamento
- `isActive` (boolean): Filtrar por estado activo

**Response:**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": "507f1f77bcf86cd799439011",
        "email": "user@example.com",
        "firstName": "John",
        "lastName": "Doe",
        "role": "user",
        "department": "IT",
        "position": "Developer",
        "avatar": "https://example.com/avatar.jpg",
        "isActive": true,
        "lastLogin": "2024-01-01T10:00:00.000Z",
        "createdAt": "2024-01-01T00:00:00.000Z"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 50,
      "pages": 3
    }
  }
}
```

### GET /users/:id
Obtener usuario específico.

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "507f1f77bcf86cd799439011",
      "email": "user@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "role": "user",
      "department": "IT",
      "position": "Developer",
      "avatar": "https://example.com/avatar.jpg",
      "isActive": true,
      "notificationSettings": {
        "email": true,
        "push": true,
        "desktop": false
      },
      "privacySettings": {
        "profileVisible": true,
        "showOnlineStatus": true
      },
      "stats": {
        "filesUploaded": 25,
        "groupsJoined": 5,
        "messagesSent": 150
      }
    }
  }
}
```

### PUT /users/:id
Actualizar usuario.

**Request:**
```json
{
  "firstName": "John Updated",
  "lastName": "Doe Updated",
  "department": "Engineering",
  "position": "Senior Developer",
  "notificationSettings": {
    "email": true,
    "push": false,
    "desktop": true
  }
}
```

### DELETE /users/:id
Eliminar usuario (solo admins).

### GET /users/me
Obtener perfil del usuario actual.

### PUT /users/me
Actualizar perfil del usuario actual.

### POST /users/me/avatar
Subir avatar del usuario.

**Request:** Multipart form data
- `avatar`: Archivo de imagen

## 📁 Archivos

### GET /files
Obtener lista de archivos.

**Query Parameters:**
- `page`, `limit`: Paginación
- `search`: Buscar por nombre
- `category`: Filtrar por categoría
- `tags`: Filtrar por etiquetas (separadas por coma)
- `uploadedBy`: Filtrar por usuario que subió
- `dateFrom`, `dateTo`: Filtrar por rango de fechas
- `minSize`, `maxSize`: Filtrar por tamaño
- `sortBy`: Ordenar por (name, size, date, downloads)
- `sortOrder`: asc o desc

**Response:**
```json
{
  "success": true,
  "data": {
    "files": [
      {
        "id": "507f1f77bcf86cd799439020",
        "filename": "document_2024.pdf",
        "originalName": "Important Document 2024.pdf",
        "size": 1048576,
        "mimeType": "application/pdf",
        "category": "document",
        "tags": ["important", "2024", "report"],
        "description": "Annual report for 2024",
        "uploadedBy": {
          "id": "507f1f77bcf86cd799439011",
          "firstName": "John",
          "lastName": "Doe"
        },
        "isPublic": false,
        "accessLevel": "internal",
        "downloadCount": 15,
        "viewCount": 45,
        "url": "https://example.com/files/document_2024.pdf",
        "thumbnailUrl": "https://example.com/thumbnails/document_2024.jpg",
        "createdAt": "2024-01-01T00:00:00.000Z",
        "updatedAt": "2024-01-01T12:00:00.000Z"
      }
    ],
    "pagination": {...},
    "stats": {
      "totalFiles": 150,
      "totalSize": 1073741824,
      "categories": {
        "document": 80,
        "image": 45,
        "video": 15,
        "other": 10
      }
    }
  }
}
```

### POST /files/upload
Subir archivo.

**Request:** Multipart form data
- `file`: Archivo a subir
- `category`: Categoría del archivo
- `tags`: Etiquetas (JSON array)
- `description`: Descripción
- `isPublic`: Si es público (boolean)
- `accessLevel`: Nivel de acceso

**Response:**
```json
{
  "success": true,
  "data": {
    "file": {
      "id": "507f1f77bcf86cd799439020",
      "filename": "document_2024.pdf",
      "originalName": "Important Document 2024.pdf",
      "size": 1048576,
      "url": "https://example.com/files/document_2024.pdf"
    }
  },
  "message": "Archivo subido exitosamente"
}
```

### GET /files/:id
Obtener información de archivo específico.

### PUT /files/:id
Actualizar metadatos de archivo.

**Request:**
```json
{
  "tags": ["updated", "important"],
  "description": "Updated description",
  "category": "document",
  "isPublic": true,
  "accessLevel": "public"
}
```

### DELETE /files/:id
Eliminar archivo.

### GET /files/:id/download
Descargar archivo.

**Response:** Archivo binario con headers apropiados.

### GET /files/:id/view
Ver archivo (para imágenes, PDFs, etc.).

### POST /files/:id/share
Compartir archivo y generar enlace.

**Request:**
```json
{
  "expiresIn": 86400,
  "password": "optional_password",
  "allowDownload": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "shareUrl": "https://example.com/share/abc123def456",
    "expiresAt": "2024-01-02T00:00:00.000Z"
  }
}
```

## 👥 Grupos

### GET /groups
Obtener lista de grupos.

**Query Parameters:**
- `page`, `limit`: Paginación
- `search`: Buscar por nombre
- `type`: Filtrar por tipo (public, private, protected)
- `memberOf`: Solo grupos donde soy miembro

**Response:**
```json
{
  "success": true,
  "data": {
    "groups": [
      {
        "id": "507f1f77bcf86cd799439030",
        "name": "Equipo de Desarrollo",
        "description": "Grupo para el equipo de desarrollo",
        "type": "private",
        "avatar": "https://example.com/group-avatar.jpg",
        "memberCount": 15,
        "fileCount": 45,
        "isOwner": true,
        "isMember": true,
        "role": "admin",
        "createdBy": {
          "id": "507f1f77bcf86cd799439011",
          "firstName": "John",
          "lastName": "Doe"
        },
        "createdAt": "2024-01-01T00:00:00.000Z"
      }
    ],
    "pagination": {...}
  }
}
```

### POST /groups
Crear nuevo grupo.

**Request:**
```json
{
  "name": "Nuevo Grupo",
  "description": "Descripción del grupo",
  "type": "private",
  "settings": {
    "allowMemberInvites": true,
    "allowFileSharing": true,
    "requireApproval": false
  }
}
```

### GET /groups/:id
Obtener información detallada del grupo.

**Response:**
```json
{
  "success": true,
  "data": {
    "group": {
      "id": "507f1f77bcf86cd799439030",
      "name": "Equipo de Desarrollo",
      "description": "Grupo para el equipo de desarrollo",
      "type": "private",
      "avatar": "https://example.com/group-avatar.jpg",
      "settings": {
        "allowMemberInvites": true,
        "allowFileSharing": true,
        "requireApproval": false
      },
      "members": [
        {
          "user": {
            "id": "507f1f77bcf86cd799439011",
            "firstName": "John",
            "lastName": "Doe",
            "avatar": "https://example.com/avatar.jpg"
          },
          "role": "admin",
          "joinedAt": "2024-01-01T00:00:00.000Z"
        }
      ],
      "recentFiles": [...],
      "stats": {
        "totalMembers": 15,
        "totalFiles": 45,
        "totalMessages": 230
      }
    }
  }
}
```

### PUT /groups/:id
Actualizar grupo.

### DELETE /groups/:id
Eliminar grupo.

### POST /groups/:id/join
Unirse a grupo público.

### POST /groups/:id/leave
Salir del grupo.

### POST /groups/:id/invite
Invitar usuario al grupo.

**Request:**
```json
{
  "userId": "507f1f77bcf86cd799439012",
  "role": "member",
  "message": "Te invito a unirte al grupo"
}
```

### GET /groups/:id/members
Obtener miembros del grupo.

### PUT /groups/:id/members/:userId
Actualizar rol de miembro.

### DELETE /groups/:id/members/:userId
Remover miembro del grupo.

## 💬 Mensajes

### GET /messages
Obtener lista de conversaciones.

**Response:**
```json
{
  "success": true,
  "data": {
    "conversations": [
      {
        "id": "507f1f77bcf86cd799439040",
        "type": "direct",
        "participants": [
          {
            "id": "507f1f77bcf86cd799439011",
            "firstName": "John",
            "lastName": "Doe",
            "avatar": "https://example.com/avatar.jpg",
            "isOnline": true
          }
        ],
        "lastMessage": {
          "content": "Hola, ¿cómo estás?",
          "sender": "507f1f77bcf86cd799439011",
          "timestamp": "2024-01-01T12:00:00.000Z",
          "isRead": false
        },
        "unreadCount": 3
      }
    ]
  }
}
```

### GET /messages/:conversationId
Obtener mensajes de una conversación.

**Query Parameters:**
- `page`, `limit`: Paginación
- `before`: Mensajes antes de esta fecha

**Response:**
```json
{
  "success": true,
  "data": {
    "messages": [
      {
        "id": "507f1f77bcf86cd799439041",
        "content": "Hola, ¿cómo estás?",
        "type": "text",
        "sender": {
          "id": "507f1f77bcf86cd799439011",
          "firstName": "John",
          "lastName": "Doe",
          "avatar": "https://example.com/avatar.jpg"
        },
        "timestamp": "2024-01-01T12:00:00.000Z",
        "isRead": true,
        "readBy": [
          {
            "user": "507f1f77bcf86cd799439012",
            "readAt": "2024-01-01T12:05:00.000Z"
          }
        ],
        "attachments": []
      }
    ],
    "pagination": {...}
  }
}
```

### POST /messages/:conversationId
Enviar mensaje.

**Request:**
```json
{
  "content": "Hola, ¿cómo estás?",
  "type": "text",
  "replyTo": "507f1f77bcf86cd799439040"
}
```

### POST /messages/:conversationId/attachment
Enviar archivo adjunto.

**Request:** Multipart form data
- `file`: Archivo adjunto
- `message`: Mensaje opcional

### PUT /messages/:messageId/read
Marcar mensaje como leído.

### DELETE /messages/:messageId
Eliminar mensaje.

## 🛡️ Evidencias

### GET /evidences
Obtener lista de evidencias.

**Query Parameters:**
- `page`, `limit`: Paginación
- `status`: Filtrar por estado (pending, approved, rejected)
- `category`: Filtrar por categoría
- `assignedTo`: Filtrar por asignado a
- `priority`: Filtrar por prioridad

**Response:**
```json
{
  "success": true,
  "data": {
    "evidences": [
      {
        "id": "507f1f77bcf86cd799439050",
        "title": "Evidencia de Incidente #001",
        "description": "Descripción detallada de la evidencia",
        "category": "security",
        "priority": "high",
        "status": "pending",
        "files": [
          {
            "id": "507f1f77bcf86cd799439020",
            "filename": "evidence_photo.jpg",
            "url": "https://example.com/files/evidence_photo.jpg"
          }
        ],
        "submittedBy": {
          "id": "507f1f77bcf86cd799439011",
          "firstName": "John",
          "lastName": "Doe"
        },
        "assignedTo": {
          "id": "507f1f77bcf86cd799439012",
          "firstName": "Jane",
          "lastName": "Smith"
        },
        "evaluations": [
          {
            "evaluator": "507f1f77bcf86cd799439013",
            "rating": 4,
            "comment": "Evidencia clara y bien documentada",
            "evaluatedAt": "2024-01-01T15:00:00.000Z"
          }
        ],
        "createdAt": "2024-01-01T10:00:00.000Z",
        "updatedAt": "2024-01-01T15:00:00.000Z"
      }
    ],
    "pagination": {...}
  }
}
```

### POST /evidences
Crear nueva evidencia.

**Request:**
```json
{
  "title": "Nueva Evidencia",
  "description": "Descripción detallada",
  "category": "security",
  "priority": "medium",
  "fileIds": ["507f1f77bcf86cd799439020"],
  "assignedTo": "507f1f77bcf86cd799439012",
  "metadata": {
    "location": "Oficina Principal",
    "timestamp": "2024-01-01T10:00:00.000Z"
  }
}
```

### GET /evidences/:id
Obtener evidencia específica.

### PUT /evidences/:id
Actualizar evidencia.

### DELETE /evidences/:id
Eliminar evidencia.

### POST /evidences/:id/evaluate
Evaluar evidencia.

**Request:**
```json
{
  "rating": 4,
  "comment": "Evidencia clara y bien documentada",
  "status": "approved"
}
```

### GET /evidences/:id/history
Obtener historial de cambios de la evidencia.

## 🔔 Notificaciones

### GET /notifications
Obtener notificaciones del usuario.

**Query Parameters:**
- `page`, `limit`: Paginación
- `isRead`: Filtrar por leídas/no leídas
- `type`: Filtrar por tipo

**Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "507f1f77bcf86cd799439060",
        "type": "file_shared",
        "title": "Archivo compartido contigo",
        "message": "John Doe compartió un archivo: document.pdf",
        "data": {
          "fileId": "507f1f77bcf86cd799439020",
          "sharedBy": "507f1f77bcf86cd799439011"
        },
        "isRead": false,
        "createdAt": "2024-01-01T14:00:00.000Z"
      }
    ],
    "pagination": {...},
    "unreadCount": 5
  }
}
```

### PUT /notifications/:id/read
Marcar notificación como leída.

### PUT /notifications/read-all
Marcar todas las notificaciones como leídas.

### DELETE /notifications/:id
Eliminar notificación.

## 📊 Analytics

### GET /analytics/dashboard
Obtener métricas del dashboard.

**Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "totalFiles": 1250,
      "totalUsers": 45,
      "totalGroups": 12,
      "totalMessages": 3420
    },
    "trends": {
      "filesUploadedToday": 15,
      "newUsersThisWeek": 3,
      "messagesThisWeek": 245
    },
    "charts": {
      "fileUploads": [
        {
          "date": "2024-01-01",
          "count": 25
        }
      ],
      "userActivity": [
        {
          "hour": 9,
          "activeUsers": 12
        }
      ]
    }
  }
}
```

### GET /analytics/files
Obtener analytics de archivos.

### GET /analytics/users
Obtener analytics de usuarios.

### GET /analytics/groups
Obtener analytics de grupos.

## ❌ Códigos de Error

### Códigos HTTP Estándar
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict
- `422` - Unprocessable Entity
- `429` - Too Many Requests
- `500` - Internal Server Error

### Códigos de Error Personalizados

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Los datos proporcionados no son válidos",
    "details": [
      {
        "field": "email",
        "message": "El email no es válido"
      }
    ]
  },
  "timestamp": "2024-01-01T00:00:00.000Z"
}
```

**Códigos Comunes:**
- `VALIDATION_ERROR` - Error de validación
- `AUTHENTICATION_FAILED` - Fallo de autenticación
- `AUTHORIZATION_FAILED` - Sin permisos
- `RESOURCE_NOT_FOUND` - Recurso no encontrado
- `DUPLICATE_RESOURCE` - Recurso duplicado
- `RATE_LIMIT_EXCEEDED` - Límite de requests excedido
- `FILE_TOO_LARGE` - Archivo muy grande
- `INVALID_FILE_TYPE` - Tipo de archivo no válido
- `DATABASE_ERROR` - Error de base de datos

## 🔗 WebSocket Events

### Conexión
```javascript
const socket = io('http://localhost:5001', {
  auth: {
    token: 'your_jwt_token'
  }
});
```

### Eventos Disponibles

#### Mensajes
- `message:new` - Nuevo mensaje recibido
- `message:read` - Mensaje marcado como leído
- `message:typing` - Usuario escribiendo

#### Notificaciones
- `notification:new` - Nueva notificación
- `notification:read` - Notificación leída

#### Archivos
- `file:uploaded` - Archivo subido
- `file:shared` - Archivo compartido

#### Usuarios
- `user:online` - Usuario conectado
- `user:offline` - Usuario desconectado

---

## 📞 Soporte

Para más información sobre la API:

- 📚 **Swagger UI**: `http://localhost:5001/api-docs`
- 📧 **Email**: api-support@evidence-platform.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/Andrewgo12/reportes/issues)
- 📖 **Documentación**: [Docs Completas](../DOCUMENTACION_COMPLETA.md)
