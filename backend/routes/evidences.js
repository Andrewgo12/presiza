/**
 * Rutas de Evidencias - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

const router = express.Router();

// Mock data para evidencias
const mockEvidences = [
  {
    id: '1',
    title: 'Evidencia Digital - Caso 2024-001',
    description: 'Análisis forense de dispositivo móvil',
    status: 'under_review',
    priority: 'high',
    assignedTo: { id: '1', name: 'Dr. Smith' },
    createdBy: { id: '2', name: 'Ana García' },
    createdAt: new Date(Date.now() - 24 * 60 * 60 * 1000),
    files: ['archivo1.pdf', 'imagen1.jpg']
  }
];

/**
 * GET /api/v1/evidences
 * Obtener evidencias
 */
router.get('/', asyncHandler(async (req, res) => {
  const { page = 1, limit = 20, status, priority } = req.query;

  let filteredEvidences = mockEvidences;

  if (status) {
    filteredEvidences = filteredEvidences.filter(e => e.status === status);
  }

  if (priority) {
    filteredEvidences = filteredEvidences.filter(e => e.priority === priority);
  }

  res.json({
    evidences: filteredEvidences,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total: filteredEvidences.length,
      pages: 1
    }
  });
}));

/**
 * POST /api/v1/evidences
 * Crear nueva evidencia
 */
router.post('/', [
  body('title').trim().isLength({ min: 3, max: 200 }).withMessage('Título debe tener entre 3 y 200 caracteres'),
  body('description').optional().isLength({ max: 1000 }).withMessage('Descripción no puede exceder 1000 caracteres')
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos inválidos', 400, 'VALIDATION_ERROR');
  }

  const { title, description, priority = 'medium' } = req.body;

  const newEvidence = {
    id: Date.now().toString(),
    title,
    description,
    status: 'pending',
    priority,
    createdBy: {
      id: req.user._id,
      name: req.user.fullName
    },
    createdAt: new Date(),
    files: []
  };

  res.status(201).json({
    message: 'Evidencia creada exitosamente',
    evidence: newEvidence
  });
}));

module.exports = router;
