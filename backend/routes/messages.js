/**
 * Rutas de Mensajes - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

const router = express.Router();

// Mock data para mensajes (será reemplazado con modelo real)
const mockMessages = [
  {
    id: '1',
    sender: { id: '1', name: 'Dr. Smith', avatar: null },
    content: 'Necesito revisar los archivos del caso 2024-001',
    timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
    read: false,
    type: 'text'
  },
  {
    id: '2',
    sender: { id: '2', name: 'Ana García', avatar: null },
    content: 'Los resultados del análisis están listos',
    timestamp: new Date(Date.now() - 4 * 60 * 60 * 1000),
    read: true,
    type: 'text'
  }
];

/**
 * GET /api/v1/messages
 * Obtener mensajes del usuario
 */
router.get('/', asyncHandler(async (req, res) => {
  const { page = 1, limit = 20 } = req.query;

  // Por ahora retornamos mock data
  res.json({
    messages: mockMessages,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total: mockMessages.length,
      pages: 1
    }
  });
}));

/**
 * POST /api/v1/messages
 * Enviar nuevo mensaje
 */
router.post('/', [
  body('content').trim().isLength({ min: 1, max: 1000 }).withMessage('Mensaje debe tener entre 1 y 1000 caracteres'),
  body('recipientId').isMongoId().withMessage('ID de destinatario inválido')
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos inválidos', 400, 'VALIDATION_ERROR');
  }

  const { content, recipientId } = req.body;

  // Mock response
  const newMessage = {
    id: Date.now().toString(),
    sender: {
      id: req.user._id,
      name: req.user.fullName,
      avatar: req.user.avatar
    },
    content,
    timestamp: new Date(),
    read: false,
    type: 'text'
  };

  res.status(201).json({
    message: 'Mensaje enviado exitosamente',
    data: newMessage
  });
}));

module.exports = router;
