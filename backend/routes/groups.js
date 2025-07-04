/**
 * Rutas de Grupos - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const Group = require('../models/Group');
const { requireGroupPermission } = require('../middleware/auth');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

// Import database status for fallback functionality
const { getDatabaseStatus } = require('../config/database');

// Fallback group data for development mode
const getFallbackGroups = () => [
  {
    _id: '507f1f77bcf86cd799439040',
    name: 'Research Team Alpha',
    description: 'Equipo principal de investigación y análisis de datos científicos',
    type: 'public',
    category: 'research',
    members: [
      { user: '507f1f77bcf86cd799439011', role: 'owner', joinedAt: new Date('2024-01-01') },
      { user: '507f1f77bcf86cd799439012', role: 'admin', joinedAt: new Date('2024-01-15') },
      { user: '507f1f77bcf86cd799439013', role: 'member', joinedAt: new Date('2024-02-01') }
    ],
    settings: {
      maxMembers: 50,
      allowInvites: true,
      requireApproval: false
    },
    messageCount: 45,
    fileCount: 12,
    lastActivity: new Date('2024-07-03'),
    createdAt: new Date('2024-01-01'),
    updatedAt: new Date('2024-07-03')
  },
  {
    _id: '507f1f77bcf86cd799439041',
    name: 'Development Squad',
    description: 'Equipo de desarrollo de software y sistemas',
    type: 'private',
    category: 'team',
    members: [
      { user: '507f1f77bcf86cd799439011', role: 'owner', joinedAt: new Date('2023-12-01') },
      { user: '507f1f77bcf86cd799439013', role: 'admin', joinedAt: new Date('2024-01-10') }
    ],
    settings: {
      maxMembers: 20,
      allowInvites: false,
      requireApproval: true
    },
    messageCount: 78,
    fileCount: 25,
    lastActivity: new Date('2024-07-04'),
    createdAt: new Date('2023-12-01'),
    updatedAt: new Date('2024-07-04')
  },
  {
    _id: '507f1f77bcf86cd799439042',
    name: 'Design Collective',
    description: 'Grupo colaborativo de diseño y experiencia de usuario',
    type: 'protected',
    category: 'project',
    members: [
      { user: '507f1f77bcf86cd799439011', role: 'admin', joinedAt: new Date('2024-01-05') },
      { user: '507f1f77bcf86cd799439012', role: 'member', joinedAt: new Date('2024-02-20') },
      { user: '507f1f77bcf86cd799439014', role: 'member', joinedAt: new Date('2024-02-20') }
    ],
    settings: {
      maxMembers: 30,
      allowInvites: true,
      requireApproval: true
    },
    messageCount: 32,
    fileCount: 18,
    lastActivity: new Date('2024-07-02'),
    createdAt: new Date('2024-01-05'),
    updatedAt: new Date('2024-07-02')
  },
  {
    _id: '507f1f77bcf86cd799439043',
    name: 'Data Analytics Hub',
    description: 'Centro de análisis de datos y business intelligence',
    type: 'public',
    category: 'research',
    members: [
      { user: '507f1f77bcf86cd799439013', role: 'owner', joinedAt: new Date('2024-02-01') },
      { user: '507f1f77bcf86cd799439011', role: 'admin', joinedAt: new Date('2024-02-10') },
      { user: '507f1f77bcf86cd799439012', role: 'member', joinedAt: new Date('2024-02-20') }
    ],
    settings: {
      maxMembers: 35,
      allowInvites: true,
      requireApproval: false
    },
    messageCount: 156,
    fileCount: 34,
    lastActivity: new Date('2024-07-04'),
    createdAt: new Date('2024-02-01'),
    updatedAt: new Date('2024-07-04')
  },
  {
    _id: '507f1f77bcf86cd799439044',
    name: 'General Discussion',
    description: 'Espacio abierto para discusiones generales y anuncios',
    type: 'public',
    category: 'general',
    members: [
      { user: '507f1f77bcf86cd799439011', role: 'owner', joinedAt: new Date('2023-12-01') },
      { user: '507f1f77bcf86cd799439012', role: 'member', joinedAt: new Date('2024-01-01') },
      { user: '507f1f77bcf86cd799439013', role: 'member', joinedAt: new Date('2024-01-01') },
      { user: '507f1f77bcf86cd799439014', role: 'member', joinedAt: new Date('2024-01-01') }
    ],
    settings: {
      maxMembers: 100,
      allowInvites: true,
      requireApproval: false
    },
    messageCount: 234,
    fileCount: 45,
    lastActivity: new Date('2024-07-04'),
    createdAt: new Date('2023-12-01'),
    updatedAt: new Date('2024-07-04')
  }
];

const router = express.Router();

/**
 * GET /api/v1/groups
 * Obtener grupos del usuario
 */
router.get('/', asyncHandler(async (req, res) => {
  const { page = 1, limit = 20, search, type, status } = req.query;

  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    let fallbackGroups = getFallbackGroups();

    // Filtrar grupos donde el usuario es miembro
    fallbackGroups = fallbackGroups.filter(group =>
      group.members.some(member => member.user === req.user._id)
    );

    // Aplicar filtros de búsqueda
    if (search) {
      const searchRegex = new RegExp(search, 'i');
      fallbackGroups = fallbackGroups.filter(group =>
        searchRegex.test(group.name) || searchRegex.test(group.description)
      );
    }

    if (type) {
      fallbackGroups = fallbackGroups.filter(group => group.type === type);
    }

    // Ordenar por última actividad
    fallbackGroups.sort((a, b) => new Date(b.lastActivity) - new Date(a.lastActivity));

    // Aplicar paginación
    const skip = (page - 1) * limit;
    const paginatedGroups = fallbackGroups.slice(skip, skip + parseInt(limit));

    // Enriquecer con información de usuarios
    const enrichedGroups = paginatedGroups.map(group => ({
      ...group,
      members: group.members.map(member => ({
        ...member,
        user: {
          _id: member.user,
          firstName: member.user === '507f1f77bcf86cd799439011' ? 'Admin' : 'Test',
          lastName: 'User',
          email: member.user === '507f1f77bcf86cd799439011' ? 'admin@test.com' : 'user@test.com',
          avatar: null
        }
      }))
    }));

    return res.json({
      groups: enrichedGroups,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total: fallbackGroups.length,
        pages: Math.ceil(fallbackGroups.length / limit)
      },
      mode: 'development'
    });
  }

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
