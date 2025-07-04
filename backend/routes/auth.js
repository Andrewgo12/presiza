/**
 * Rutas de Autenticación - Sistema de Gestión de Evidencias
 * Maneja login, registro, refresh tokens y gestión de sesiones
 */

const express = require('express');
const { body, validationResult } = require('express-validator');
const User = require('../models/User');
const { generateTokens, verifyRefreshToken, authenticateToken } = require('../middleware/auth');
const { auditLogger, analyticsLogger } = require('../middleware/logging');

const router = express.Router();

/**
 * POST /api/v1/auth/login
 * Iniciar sesión de usuario
 */
router.post('/login', [
  body('email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Email válido es requerido'),
  body('password')
    .isLength({ min: 6 })
    .withMessage('Contraseña debe tener al menos 6 caracteres')
], async (req, res) => {
  try {
    // Verificar errores de validación
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        error: 'Datos de entrada inválidos',
        details: errors.array(),
        code: 'VALIDATION_ERROR'
      });
    }

    const { email, password } = req.body;

    // FALLBACK: Si MongoDB no está disponible, usar credenciales hardcodeadas para desarrollo
    const { getDatabaseStatus } = require('../config/database');
    const dbStatus = getDatabaseStatus();

    if (!dbStatus.mongodb.connected) {
      // Credenciales de desarrollo hardcodeadas
      const devUsers = [
        {
          _id: '507f1f77bcf86cd799439011',
          email: 'admin@test.com',
          password: 'admin123',
          firstName: 'Admin',
          lastName: 'User',
          role: 'admin',
          department: 'IT',
          position: 'System Administrator',
          isActive: true
        },
        {
          _id: '507f1f77bcf86cd799439012',
          email: 'user@test.com',
          password: 'user123',
          firstName: 'Test',
          lastName: 'User',
          role: 'user',
          department: 'General',
          position: 'User',
          isActive: true
        },
        {
          _id: '507f1f77bcf86cd799439013',
          email: 'analyst@test.com',
          password: 'analyst123',
          firstName: 'Data',
          lastName: 'Analyst',
          role: 'analyst',
          department: 'Analytics',
          position: 'Senior Analyst',
          isActive: true
        },
        {
          _id: '507f1f77bcf86cd799439014',
          email: 'investigator@test.com',
          password: 'investigator123',
          firstName: 'John',
          lastName: 'Investigator',
          role: 'investigator',
          department: 'Investigation',
          position: 'Lead Investigator',
          isActive: true
        }
      ];

      const user = devUsers.find(u => u.email === email && u.password === password);

      if (!user) {
        return res.status(401).json({
          error: 'Credenciales inválidas',
          code: 'INVALID_CREDENTIALS',
          hint: 'Para desarrollo: admin@test.com/admin123 o user@test.com/user123'
        });
      }

      // Generar tokens para usuario de desarrollo
      const { accessToken, refreshToken } = generateTokens(user._id);

      // Log de auditoría
      await auditLogger(req, {
        action: 'LOGIN',
        resource: 'AUTH',
        resourceId: user._id,
        details: { email: user.email, method: 'FALLBACK_DEV' },
        success: true
      });

      return res.json({
        message: 'Inicio de sesión exitoso (modo desarrollo)',
        user: {
          id: user._id,
          email: user.email,
          firstName: user.firstName,
          lastName: user.lastName,
          role: user.role,
          department: user.department,
          position: user.position,
          isActive: user.isActive
        },
        tokens: {
          accessToken,
          refreshToken
        },
        mode: 'development'
      });
    }

    // Buscar usuario en MongoDB (cuando esté disponible)
    const user = await User.findByCredentials(email, password);

    // Generar tokens
    const { accessToken, refreshToken } = generateTokens(user._id);

    // Respuesta exitosa
    res.json({
      message: 'Inicio de sesión exitoso',
      user: user.toPublicJSON(),
      tokens: {
        accessToken,
        refreshToken,
        expiresIn: process.env.JWT_EXPIRE || '24h'
      }
    });

  } catch (error) {
    // Distinguir entre errores esperados y errores reales
    if (error.message === 'Credenciales inválidas' ||
      error.message === 'Cuenta bloqueada temporalmente') {
      // Error esperado: credenciales incorrectas
      return res.status(401).json({
        error: error.message,
        code: 'INVALID_CREDENTIALS'
      });
    }

    if (error.code === 'ECONNREFUSED' || error.name === 'MongoNetworkError') {
      // Error esperado: MongoDB no disponible (modo fallback activo)
      console.log('ℹ️ MongoDB no disponible durante login, usando modo fallback');
      return res.status(503).json({
        error: 'Servicio temporalmente no disponible',
        code: 'SERVICE_UNAVAILABLE'
      });
    }

    // Error inesperado: registrar para debugging
    console.error('❌ Error inesperado en login:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

/**
 * POST /api/v1/auth/register
 * Registrar nuevo usuario (solo admins pueden crear usuarios)
 */
router.post('/register', [
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
    .optional()
    .isIn(['admin', 'user', 'analyst', 'investigator'])
    .withMessage('Rol inválido')
], async (req, res) => {
  try {
    // Verificar errores de validación
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        error: 'Datos de entrada inválidos',
        details: errors.array(),
        code: 'VALIDATION_ERROR'
      });
    }

    const { email, password, firstName, lastName, role, department, position } = req.body;

    // Verificar si el email ya existe
    const existingUser = await User.findOne({ email });
    if (existingUser) {
      return res.status(409).json({
        error: 'El email ya está registrado',
        code: 'EMAIL_EXISTS'
      });
    }

    // Crear nuevo usuario
    const user = new User({
      email,
      password,
      firstName,
      lastName,
      role: role || 'user',
      department,
      position
    });

    await user.save();

    // Generar tokens
    const { accessToken, refreshToken } = generateTokens(user._id);

    res.status(201).json({
      message: 'Usuario registrado exitosamente',
      user: user.toPublicJSON(),
      tokens: {
        accessToken,
        refreshToken,
        expiresIn: process.env.JWT_EXPIRE || '24h'
      }
    });

  } catch (error) {
    console.error('Error en registro:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

/**
 * POST /api/v1/auth/refresh
 * Renovar token de acceso usando refresh token
 */
router.post('/refresh', [
  body('refreshToken')
    .notEmpty()
    .withMessage('Refresh token es requerido')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        error: 'Refresh token requerido',
        code: 'VALIDATION_ERROR'
      });
    }

    const { refreshToken } = req.body;

    // Verificar refresh token
    const decoded = verifyRefreshToken(refreshToken);

    // FALLBACK: Si MongoDB no está disponible, usar usuarios de desarrollo
    const { getDatabaseStatus } = require('../config/database');
    const dbStatus = getDatabaseStatus();

    if (!dbStatus.mongodb.connected) {
      // En modo desarrollo, simplemente generar nuevos tokens
      const { accessToken: newAccessToken, refreshToken: newRefreshToken } = generateTokens(decoded.userId);

      return res.json({
        accessToken: newAccessToken,
        refreshToken: newRefreshToken,
        expiresIn: process.env.JWT_EXPIRE || '24h',
        mode: 'development'
      });
    }

    // Buscar usuario en MongoDB
    const user = await User.findById(decoded.userId);
    if (!user || !user.isActive) {
      return res.status(401).json({
        error: 'Usuario no válido',
        code: 'INVALID_USER'
      });
    }

    // Generar nuevos tokens
    const tokens = generateTokens(user._id);

    res.json({
      message: 'Token renovado exitosamente',
      tokens: {
        ...tokens,
        expiresIn: process.env.JWT_EXPIRE || '24h'
      }
    });

  } catch (error) {
    // Distinguir entre errores esperados y errores reales
    if (error.name === 'JsonWebTokenError' || error.name === 'TokenExpiredError') {
      // Error esperado: token inválido o expirado
      return res.status(401).json({
        error: 'Refresh token inválido o expirado',
        code: 'INVALID_REFRESH_TOKEN'
      });
    }

    if (error.code === 'ECONNREFUSED' || error.name === 'MongoNetworkError') {
      // Error esperado: MongoDB no disponible (modo fallback activo)
      console.log('ℹ️ MongoDB no disponible durante refresh token, usando modo fallback');
      return res.status(503).json({
        error: 'Servicio temporalmente no disponible',
        code: 'SERVICE_UNAVAILABLE'
      });
    }

    // Error inesperado: registrar para debugging
    console.error('❌ Error inesperado renovando token:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

/**
 * POST /api/v1/auth/logout
 * Cerrar sesión (invalidar tokens)
 */
router.post('/logout', authenticateToken, async (req, res) => {
  try {
    // En una implementación completa, aquí se invalidarían los tokens
    // Por ahora, simplemente confirmamos el logout

    res.json({
      message: 'Sesión cerrada exitosamente'
    });

  } catch (error) {
    console.error('Error en logout:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

/**
 * GET /api/v1/auth/me
 * Obtener información del usuario autenticado
 */
router.get('/me', authenticateToken, async (req, res) => {
  try {
    res.json({
      user: req.user.toPublicJSON()
    });
  } catch (error) {
    console.error('Error obteniendo perfil:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

/**
 * PUT /api/v1/auth/profile
 * Actualizar perfil del usuario autenticado
 */
router.put('/profile', authenticateToken, [
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
    .withMessage('Número de teléfono inválido')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        error: 'Datos de entrada inválidos',
        details: errors.array(),
        code: 'VALIDATION_ERROR'
      });
    }

    const allowedUpdates = ['firstName', 'lastName', 'phone', 'department', 'position', 'preferences'];
    const updates = {};

    // Filtrar solo campos permitidos
    Object.keys(req.body).forEach(key => {
      if (allowedUpdates.includes(key)) {
        updates[key] = req.body[key];
      }
    });

    // Actualizar usuario
    Object.assign(req.user, updates);
    await req.user.save();

    res.json({
      message: 'Perfil actualizado exitosamente',
      user: req.user.toPublicJSON()
    });

  } catch (error) {
    console.error('Error actualizando perfil:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

/**
 * POST /api/v1/auth/change-password
 * Cambiar contraseña del usuario autenticado
 */
router.post('/change-password', authenticateToken, [
  body('currentPassword')
    .notEmpty()
    .withMessage('Contraseña actual es requerida'),
  body('newPassword')
    .isLength({ min: 6 })
    .withMessage('Nueva contraseña debe tener al menos 6 caracteres')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        error: 'Datos de entrada inválidos',
        details: errors.array(),
        code: 'VALIDATION_ERROR'
      });
    }

    const { currentPassword, newPassword } = req.body;

    // Obtener usuario con contraseña
    const user = await User.findById(req.user._id).select('+password');

    // Verificar contraseña actual
    const isCurrentPasswordValid = await user.comparePassword(currentPassword);
    if (!isCurrentPasswordValid) {
      return res.status(400).json({
        error: 'Contraseña actual incorrecta',
        code: 'INVALID_CURRENT_PASSWORD'
      });
    }

    // Actualizar contraseña
    user.password = newPassword;
    await user.save();

    res.json({
      message: 'Contraseña actualizada exitosamente'
    });

  } catch (error) {
    console.error('Error cambiando contraseña:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
});

module.exports = router;
