/**
 * Rutas de Grupos - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const Group = require('../models/Group');
const { requireGroupPermission } = require('../middleware/auth');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

const router = express.Router();

/**
 * GET /api/v1/groups
 * Obtener grupos del usuario
 */
router.get('/', asyncHandler(async (req, res) => {
  const { page = 1, limit = 20, search, type, status } = req.query;

  const filters = {
    'members.user': req.user._id,
    deletedAt: null
  };

  if (search) {
    filters.$or = [
      { name: { $regex: search, $options: 'i' } },
      { description: { $regex: search, $options: 'i' } }
    ];
  }

  if (type) filters.type = type;
  if (status) filters.status = status;

  const skip = (parseInt(page) - 1) * parseInt(limit);

  const groups = await Group.find(filters)
    .populate('createdBy', 'firstName lastName')
    .populate('members.user', 'firstName lastName email avatar')
    .sort({ 'stats.lastActivity': -1 })
    .skip(skip)
    .limit(parseInt(limit));

  const total = await Group.countDocuments(filters);

  res.json({
    groups,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total,
      pages: Math.ceil(total / parseInt(limit))
    }
  });
}));

/**
 * POST /api/v1/groups
 * Crear nuevo grupo
 */
router.post('/', [
  body('name').trim().isLength({ min: 3, max: 100 }).withMessage('Nombre debe tener entre 3 y 100 caracteres'),
  body('description').optional().isLength({ max: 500 }).withMessage('Descripción no puede exceder 500 caracteres'),
  body('type').optional().isIn(['investigation', 'analysis', 'review', 'collaboration', 'project'])
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos inválidos', 400, 'VALIDATION_ERROR');
  }

  const { name, description, type, privacy } = req.body;

  const group = new Group({
    name,
    description,
    type: type || 'collaboration',
    privacy: privacy || 'private',
    createdBy: req.user._id,
    members: [{
      user: req.user._id,
      role: 'owner',
      permissions: {
        canInvite: true,
        canRemove: true,
        canUpload: true,
        canDownload: true,
        canEdit: true,
        canDelete: true
      }
    }]
  });

  await group.save();
  await group.populate('members.user', 'firstName lastName email avatar');

  res.status(201).json({
    message: 'Grupo creado exitosamente',
    group
  });
}));

/**
 * GET /api/v1/groups/:id
 * Obtener grupo específico
 */
router.get('/:id', asyncHandler(async (req, res) => {
  const group = await Group.findById(req.params.id)
    .populate('createdBy', 'firstName lastName')
    .populate('members.user', 'firstName lastName email avatar role')
    .populate('members.invitedBy', 'firstName lastName');

  if (!group || group.deletedAt) {
    throw new AppError('Grupo no encontrado', 404, 'GROUP_NOT_FOUND');
  }

  if (!group.isMember(req.user._id) && req.user.role !== 'admin') {
    throw new AppError('No tienes acceso a este grupo', 403, 'ACCESS_DENIED');
  }

  res.json({ group });
}));

/**
 * PUT /api/v1/groups/:id
 * Actualizar grupo
 */
router.put('/:id', requireGroupPermission('canEdit'), [
  body('name').optional().trim().isLength({ min: 3, max: 100 }),
  body('description').optional().isLength({ max: 500 })
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos inválidos', 400, 'VALIDATION_ERROR');
  }

  const { name, description, privacy, notifications } = req.body;
  const group = req.group;

  if (name) group.name = name;
  if (description !== undefined) group.description = description;
  if (privacy) group.privacy = privacy;
  if (notifications) group.notifications = { ...group.notifications, ...notifications };

  await group.save();

  res.json({
    message: 'Grupo actualizado exitosamente',
    group
  });
}));

/**
 * POST /api/v1/groups/:id/members
 * Agregar miembro al grupo
 */
router.post('/:id/members', requireGroupPermission('canInvite'), [
  body('userId').isMongoId().withMessage('ID de usuario inválido'),
  body('role').optional().isIn(['admin', 'moderator', 'member', 'viewer'])
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos inválidos', 400, 'VALIDATION_ERROR');
  }

  const { userId, role = 'member' } = req.body;
  const group = req.group;

  // Verificar que el usuario existe
  const User = require('../models/User');
  const user = await User.findById(userId);
  if (!user) {
    throw new AppError('Usuario no encontrado', 404, 'USER_NOT_FOUND');
  }

  // Agregar miembro
  await group.addMember(userId, role, req.user._id);
  await group.populate('members.user', 'firstName lastName email avatar');

  res.json({
    message: 'Miembro agregado exitosamente',
    group
  });
}));

module.exports = router;
