/**
 * API Service - Sistema de Gestión de Evidencias
 * Centraliza todas las llamadas a la API del backend
 */

// Configuración de la API
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:5002/api/v1'

/**
 * Función helper para realizar llamadas a la API
 */
const apiCall = async (endpoint, options = {}) => {
  const url = `${API_BASE_URL}${endpoint}`
  const config = {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers
    },
    ...options
  }

  // Agregar token de autenticación si está disponible
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  }

  try {
    const response = await fetch(url, config)
    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.error || `HTTP Error: ${response.status}`)
    }

    return data
  } catch (error) {
    console.error('API Call Error:', error)
    throw error
  }
}

/**
 * Servicios de Autenticación
 */
export const authAPI = {
  // Iniciar sesión
  login: async (email, password) => {
    return await apiCall('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    })
  },

  // Registrar usuario
  register: async (userData) => {
    return await apiCall('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    })
  },

  // Renovar token
  refreshToken: async (refreshToken) => {
    return await apiCall('/auth/refresh', {
      method: 'POST',
      body: JSON.stringify({ refreshToken })
    })
  },

  // Cerrar sesión
  logout: async () => {
    return await apiCall('/auth/logout', {
      method: 'POST'
    })
  },

  // Obtener perfil del usuario
  getProfile: async () => {
    return await apiCall('/auth/me')
  },

  // Actualizar perfil
  updateProfile: async (userData) => {
    return await apiCall('/auth/profile', {
      method: 'PUT',
      body: JSON.stringify(userData)
    })
  },

  // Cambiar contraseña
  changePassword: async (currentPassword, newPassword) => {
    return await apiCall('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify({ currentPassword, newPassword })
    })
  }
}

/**
 * Servicios de Usuarios
 */
export const usersAPI = {
  // Obtener lista de usuarios
  getUsers: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/users?${queryString}`)
  },

  // Obtener usuario específico
  getUser: async (userId) => {
    return await apiCall(`/users/${userId}`)
  },

  // Crear usuario
  createUser: async (userData) => {
    return await apiCall('/users', {
      method: 'POST',
      body: JSON.stringify(userData)
    })
  },

  // Actualizar usuario
  updateUser: async (userId, userData) => {
    return await apiCall(`/users/${userId}`, {
      method: 'PUT',
      body: JSON.stringify(userData)
    })
  },

  // Desactivar usuario
  deleteUser: async (userId) => {
    return await apiCall(`/users/${userId}`, {
      method: 'DELETE'
    })
  },

  // Buscar usuarios
  searchUsers: async (query, limit = 10) => {
    return await apiCall(`/users/search?q=${encodeURIComponent(query)}&limit=${limit}`)
  }
}

/**
 * Servicios de Archivos
 */
export const filesAPI = {
  // Obtener lista de archivos
  getFiles: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/files?${queryString}`)
  },

  // Obtener archivo específico
  getFile: async (fileId) => {
    return await apiCall(`/files/${fileId}`)
  },

  // Subir archivos
  uploadFiles: async (formData) => {
    return await apiCall('/files/upload', {
      method: 'POST',
      headers: {}, // No establecer Content-Type para FormData
      body: formData
    })
  },

  // Actualizar archivo
  updateFile: async (fileId, fileData) => {
    return await apiCall(`/files/${fileId}`, {
      method: 'PUT',
      body: JSON.stringify(fileData)
    })
  },

  // Eliminar archivo
  deleteFile: async (fileId) => {
    return await apiCall(`/files/${fileId}`, {
      method: 'DELETE'
    })
  },

  // Obtener estadísticas de archivos
  getFileStats: async () => {
    return await apiCall('/files/stats/summary')
  },

  // Obtener URL de descarga
  getDownloadUrl: (filename) => {
    return `${API_BASE_URL}/files/download/${filename}`
  }
}

/**
 * Servicios de Grupos
 */
export const groupsAPI = {
  // Obtener grupos del usuario
  getGroups: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/groups?${queryString}`)
  },

  // Obtener grupo específico
  getGroup: async (groupId) => {
    return await apiCall(`/groups/${groupId}`)
  },

  // Crear grupo
  createGroup: async (groupData) => {
    return await apiCall('/groups', {
      method: 'POST',
      body: JSON.stringify(groupData)
    })
  },

  // Actualizar grupo
  updateGroup: async (groupId, groupData) => {
    return await apiCall(`/groups/${groupId}`, {
      method: 'PUT',
      body: JSON.stringify(groupData)
    })
  },

  // Agregar miembro al grupo
  addMember: async (groupId, userId, role = 'member') => {
    return await apiCall(`/groups/${groupId}/members`, {
      method: 'POST',
      body: JSON.stringify({ userId, role })
    })
  }
}

/**
 * Servicios de Mensajes
 */
export const messagesAPI = {
  // Obtener mensajes
  getMessages: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/messages?${queryString}`)
  },

  // Enviar mensaje
  sendMessage: async (messageData) => {
    return await apiCall('/messages', {
      method: 'POST',
      body: JSON.stringify(messageData)
    })
  }
}

/**
 * Servicios de Evidencias
 */
export const evidencesAPI = {
  // Obtener evidencias
  getEvidences: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/evidences?${queryString}`)
  },

  // Crear evidencia
  createEvidence: async (evidenceData) => {
    return await apiCall('/evidences', {
      method: 'POST',
      body: JSON.stringify(evidenceData)
    })
  }
}

/**
 * Servicios de Analytics
 */
export const analyticsAPI = {
  // Obtener datos del dashboard
  getDashboard: async () => {
    return await apiCall('/analytics/dashboard')
  },

  // Obtener reportes
  getReports: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/analytics/reports?${queryString}`)
  }
}

/**
 * Servicios de Notificaciones
 */
export const notificationsAPI = {
  // Obtener notificaciones
  getNotifications: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return await apiCall(`/notifications?${queryString}`)
  },

  // Marcar notificación como leída
  markAsRead: async (notificationId) => {
    return await apiCall(`/notifications/${notificationId}/read`, {
      method: 'PUT'
    })
  },

  // Marcar todas como leídas
  markAllAsRead: async () => {
    return await apiCall('/notifications/read-all', {
      method: 'PUT'
    })
  }
}

// Exportar configuración de la API
export const API_CONFIG = {
  BASE_URL: API_BASE_URL,
  TIMEOUT: 30000, // 30 segundos
  RETRY_ATTEMPTS: 3
}

export default {
  auth: authAPI,
  users: usersAPI,
  files: filesAPI,
  groups: groupsAPI,
  messages: messagesAPI,
  evidences: evidencesAPI,
  analytics: analyticsAPI,
  notifications: notificationsAPI
}
