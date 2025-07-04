/**
 * Rutas de Evidencias - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

// Import database status and fallback data
const { getDatabaseStatus } = require('../config/database');
const { getFallbackEvidences, getUserById } = require('../utils/fallbackData');

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
  const { page = 1, limit = 20, status, priority, category, search } = req.query;

  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    let fallbackEvidences = getFallbackEvidences();

    // Aplicar filtros
    if (status) {
      fallbackEvidences = fallbackEvidences.filter(evidence => evidence.status === status);
    }

    if (priority) {
      fallbackEvidences = fallbackEvidences.filter(evidence => evidence.priority === priority);
    }

    if (category) {
      fallbackEvidences = fallbackEvidences.filter(evidence => evidence.category === category);
    }

    if (search) {
      const searchRegex = new RegExp(search, 'i');
      fallbackEvidences = fallbackEvidences.filter(evidence =>
        searchRegex.test(evidence.title) ||
        searchRegex.test(evidence.description) ||
        evidence.tags.some(tag => searchRegex.test(tag))
      );
    }

    // Filtrar por permisos (usuarios no admin solo ven sus evidencias)
    if (req.user.role !== 'admin') {
      fallbackEvidences = fallbackEvidences.filter(evidence =>
        evidence.submittedBy === req.user._id ||
        evidence.reviewedBy === req.user._id
      );
    }

    // Ordenar por fecha de creación (más recientes primero)
    fallbackEvidences.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

    // Aplicar paginación
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + parseInt(limit);
    const paginatedEvidences = fallbackEvidences.slice(startIndex, endIndex);

    // Enriquecer con información de usuarios
    const enrichedEvidences = paginatedEvidences.map(evidence => ({
      ...evidence,
      submittedBy: getUserById(evidence.submittedBy),
      reviewedBy: evidence.reviewedBy ? getUserById(evidence.reviewedBy) : null,
      comments: evidence.comments ? evidence.comments.map(comment => ({
        ...comment,
        author: getUserById(comment.author)
      })) : []
    }));

    return res.json({
      evidences: enrichedEvidences,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total: fallbackEvidences.length,
        pages: Math.ceil(fallbackEvidences.length / limit)
      },
      filters: { status, priority, category, search },
      mode: 'development'
    });
  }

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
