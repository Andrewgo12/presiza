/**
 * Rutas de Usuarios - Sistema de Gestión de Evidencias
 * Maneja operaciones CRUD de usuarios y gestión de perfiles
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const User = require('../models/User');
const { requireAdmin, requireAdminOrSelf } = require('../middleware/auth');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

const router = express.Router();

/**
 * GET /api/v1/users
 * Obtener lista de usuarios (solo admins)
 */
router.get('/', requireAdmin, asyncHandler(async (req, res) => {
  const {
    page = 1,
    limit = 20,
    search,
    role,
    department,
    isActive,
    sortBy = 'createdAt',
    sortOrder = 'desc'
  } = req.query;

  // Construir filtros
  const filters = {};

  if (search) {
    filters.$or = [
      { firstName: { $regex: search, $options: 'i' } },
      { lastName: { $regex: search, $options: 'i' } },
      { email: { $regex: search, $options: 'i' } },
      { department: { $regex: search, $options: 'i' } }
    ];
  }

  if (role) filters.role = role;
  if (department) filters.department = department;
  if (isActive !== undefined) filters.isActive = isActive === 'true';

  // Configurar paginación
  const skip = (parseInt(page) - 1) * parseInt(limit);
  const sort = { [sortBy]: sortOrder === 'desc' ? -1 : 1 };

  // Ejecutar consulta
  const users = await User.find(filters)
    .select('-password')
    .sort(sort)
    .skip(skip)
    .limit(parseInt(limit));

  const total = await User.countDocuments(filters);

  res.json({
    users,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total,
      pages: Math.ceil(total / parseInt(limit))
    }
  });
}));

/**
 * GET /api/v1/users/:id
 * Obtener usuario específico
 */
router.get('/:id', requireAdminOrSelf, asyncHandler(async (req, res) => {
  const user = await User.findById(req.params.id).select('-password');

  if (!user) {
    throw new AppError('Usuario no encontrado', 404, 'USER_NOT_FOUND');
  }

  res.json({ user: user.toPublicJSON() });
}));

/**
 * POST /api/v1/users
 * Crear nuevo usuario (solo admins)
 */
router.post('/', requireAdmin, [
  body('email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Email válido es requerido'),
  body('password')
    .isLength({ min: 6 })
    .withMessage('Contraseña debe tener al menos 6 caracteres'),
  body('firstName')
    .trim()
    .isLength({ min: 2, max: 50 })
    .withMessage('Nombre debe tener entre 2 y 50 caracteres'),
  body('lastName')
    .trim()
    .isLength({ min: 2, max: 50 })
    .withMessage('Apellido debe tener entre 2 y 50 caracteres'),
  body('role')
    .isIn(['admin', 'user', 'analyst', 'investigator'])
    .withMessage('Rol inválido')
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos de entrada inválidos', 400, 'VALIDATION_ERROR');
  }

  const { email, password, firstName, lastName, role, department, position, phone } = req.body;

  // Verificar si el email ya existe
  const existingUser = await User.findOne({ email });
  if (existingUser) {
    throw new AppError('El email ya está registrado', 409, 'EMAIL_EXISTS');
  }

  // Crear nuevo usuario
  const user = new User({
    email,
    password,
    firstName,
    lastName,
    role,
    department,
    position,
    phone,
    createdBy: req.user._id
  });

  await user.save();

  res.status(201).json({
    message: 'Usuario creado exitosamente',
    user: user.toPublicJSON()
  });
}));

/**
 * PUT /api/v1/users/:id
 * Actualizar usuario
 */
router.put('/:id', requireAdminOrSelf, [
  body('firstName')
    .optional()
    .trim()
    .isLength({ min: 2, max: 50 })
    .withMessage('Nombre debe tener entre 2 y 50 caracteres'),
  body('lastName')
    .optional()
    .trim()
    .isLength({ min: 2, max: 50 })
    .withMessage('Apellido debe tener entre 2 y 50 caracteres'),
  body('phone')
    .optional()
    .matches(/^[\+]?[1-9][\d]{0,15}$/)
    .withMessage('Número de teléfono inválido'),
  body('role')
    .optional()
    .isIn(['admin', 'user', 'analyst', 'investigator'])
    .withMessage('Rol inválido')
], asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos de entrada inválidos', 400, 'VALIDATION_ERROR');
  }

  const user = await User.findById(req.params.id);
  if (!user) {
    throw new AppError('Usuario no encontrado', 404, 'USER_NOT_FOUND');
  }

  const { firstName, lastName, phone, department, position, role, preferences } = req.body;

  // Solo admins pueden cambiar roles
  if (role && req.user.role !== 'admin') {
    throw new AppError('Solo los administradores pueden cambiar roles', 403, 'INSUFFICIENT_PERMISSIONS');
  }

  // Actualizar campos permitidos
  if (firstName) user.firstName = firstName;
  if (lastName) user.lastName = lastName;
  if (phone) user.phone = phone;
  if (department) user.department = department;
  if (position) user.position = position;
  if (role && req.user.role === 'admin') user.role = role;
  if (preferences) user.preferences = { ...user.preferences, ...preferences };

  user.updatedBy = req.user._id;
  await user.save();

  res.json({
    message: 'Usuario actualizado exitosamente',
    user: user.toPublicJSON()
  });
}));

/**
 * DELETE /api/v1/users/:id
 * Desactivar usuario (solo admins)
 */
router.delete('/:id', requireAdmin, asyncHandler(async (req, res) => {
  const user = await User.findById(req.params.id);
  if (!user) {
    throw new AppError('Usuario no encontrado', 404, 'USER_NOT_FOUND');
  }

  // No permitir que un admin se desactive a sí mismo
  if (user._id.toString() === req.user._id.toString()) {
    throw new AppError('No puedes desactivar tu propia cuenta', 400, 'CANNOT_DEACTIVATE_SELF');
  }

  user.isActive = false;
  user.updatedBy = req.user._id;
  await user.save();

  res.json({
    message: 'Usuario desactivado exitosamente'
  });
}));

/**
 * POST /api/v1/users/:id/activate
 * Reactivar usuario (solo admins)
 */
router.post('/:id/activate', requireAdmin, asyncHandler(async (req, res) => {
  const user = await User.findById(req.params.id);
  if (!user) {
    throw new AppError('Usuario no encontrado', 404, 'USER_NOT_FOUND');
  }

  user.isActive = true;
  user.updatedBy = req.user._id;
  await user.save();

  res.json({
    message: 'Usuario reactivado exitosamente',
    user: user.toPublicJSON()
  });
}));

/**
 * GET /api/v1/users/stats/summary
 * Obtener estadísticas de usuarios (solo admins)
 */
router.get('/stats/summary', requireAdmin, asyncHandler(async (req, res) => {
  // Total de usuarios
  const totalUsers = await User.countDocuments();
  const activeUsers = await User.countDocuments({ isActive: true });
  const inactiveUsers = totalUsers - activeUsers;

  // Usuarios por rol
  const usersByRole = await User.aggregate([
    { $group: { _id: '$role', count: { $sum: 1 } } }
  ]);

  // Usuarios por departamento
  const usersByDepartment = await User.aggregate([
    { $match: { department: { $ne: null, $ne: '' } } },
    { $group: { _id: '$department', count: { $sum: 1 } } }
  ]);

  // Usuarios registrados en los últimos 30 días
  const recentUsers = await User.countDocuments({
    createdAt: { $gte: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000) }
  });

  // Últimos logins
  const recentLogins = await User.countDocuments({
    lastLogin: { $gte: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000) }
  });

  res.json({
    totalUsers,
    activeUsers,
    inactiveUsers,
    usersByRole,
    usersByDepartment,
    recentUsers,
    recentLogins
  });
}));

/**
 * GET /api/v1/users/search
 * Buscar usuarios para menciones, invitaciones, etc.
 */
router.get('/search', asyncHandler(async (req, res) => {
  const { q, limit = 10 } = req.query;

  if (!q || q.length < 2) {
    return res.json({ users: [] });
  }

  const users = await User.find({
    isActive: true,
    $or: [
      { firstName: { $regex: q, $options: 'i' } },
      { lastName: { $regex: q, $options: 'i' } },
      { email: { $regex: q, $options: 'i' } }
    ]
  })
  .select('firstName lastName email avatar role department')
  .limit(parseInt(limit));

  res.json({ users });
}));

module.exports = router;
