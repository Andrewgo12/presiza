/**
 * Rutas de Notificaciones - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

// Import database status and fallback data
const { getDatabaseStatus } = require('../config/database');
const { getFallbackNotifications, getUserById } = require('../utils/fallbackData');

const router = express.Router();

// Mock data para notificaciones
const mockNotifications = [
  {
    id: '1',
    title: 'Nuevo archivo subido',
    message: 'Se ha subido un nuevo archivo al grupo "Investigación 2024"',
    type: 'file_upload',
    read: false,
    createdAt: new Date(Date.now() - 30 * 60 * 1000),
    data: { fileId: '123', groupId: '456' }
  },
  {
    id: '2',
    title: 'Mensaje recibido',
    message: 'Dr. Smith te ha enviado un mensaje',
    type: 'message',
    read: false,
    createdAt: new Date(Date.now() - 2 * 60 * 60 * 1000),
    data: { messageId: '789', senderId: '101' }
  }
];

/**
 * GET /api/v1/notifications
 * Obtener notificaciones del usuario
 */
router.get('/', asyncHandler(async (req, res) => {
  const { page = 1, limit = 20, unreadOnly = false, type, category } = req.query;

  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    let fallbackNotifications = getFallbackNotifications();

    // Filtrar notificaciones del usuario actual
    fallbackNotifications = fallbackNotifications.filter(notification =>
      notification.recipient === req.user._id
    );

    // Aplicar filtros
    if (unreadOnly === 'true') {
      fallbackNotifications = fallbackNotifications.filter(notification => !notification.isRead);
    }

    if (type) {
      fallbackNotifications = fallbackNotifications.filter(notification => notification.type === type);
    }

    if (category) {
      fallbackNotifications = fallbackNotifications.filter(notification => notification.category === category);
    }

    // Ordenar por fecha de creación (más recientes primero)
    fallbackNotifications.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

    // Aplicar paginación
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + parseInt(limit);
    const paginatedNotifications = fallbackNotifications.slice(startIndex, endIndex);

    // Calcular notificaciones no leídas
    const unreadCount = fallbackNotifications.filter(n => !n.isRead).length;

    return res.json({
      notifications: paginatedNotifications,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total: fallbackNotifications.length,
        pages: Math.ceil(fallbackNotifications.length / limit)
      },
      unreadCount,
      filters: { unreadOnly, type, category },
      mode: 'development'
    });
  }

  let notifications = mockNotifications;

  if (unreadOnly === 'true') {
    notifications = notifications.filter(n => !n.read);
  }

  res.json({
    notifications,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total: notifications.length,
      pages: 1
    },
    unreadCount: notifications.filter(n => !n.read).length
  });
}));

/**
 * PUT /api/v1/notifications/:id/read
 * Marcar notificación como leída
 */
router.put('/:id/read', asyncHandler(async (req, res) => {
  const notification = mockNotifications.find(n => n.id === req.params.id);

  if (!notification) {
    throw new AppError('Notificación no encontrada', 404, 'NOTIFICATION_NOT_FOUND');
  }

  notification.read = true;

  res.json({
    message: 'Notificación marcada como leída',
    notification
  });
}));

/**
 * PUT /api/v1/notifications/read-all
 * Marcar todas las notificaciones como leídas
 */
router.put('/read-all', asyncHandler(async (req, res) => {
  mockNotifications.forEach(n => n.read = true);

  res.json({
    message: 'Todas las notificaciones marcadas como leídas'
  });
}));

module.exports = router;
