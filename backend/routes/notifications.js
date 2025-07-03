/**
 * Rutas de Notificaciones - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

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
  const { page = 1, limit = 20, unreadOnly = false } = req.query;

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
