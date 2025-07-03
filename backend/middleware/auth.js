/**
 * Middleware de Autenticación - Sistema de Gestión de Evidencias
 * Maneja la verificación de tokens JWT y autorización de usuarios
 */

const jwt = require('jsonwebtoken');
const User = require('../models/User');

/**
 * Middleware para verificar token JWT
 */
const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN
    
    if (!token) {
      return res.status(401).json({
        error: 'Token de acceso requerido',
        code: 'NO_TOKEN'
      });
    }
    
    // Verificar token
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Buscar usuario
    const user = await User.findById(decoded.userId).select('-password');
    
    if (!user) {
      return res.status(401).json({
        error: 'Token inválido - usuario no encontrado',
        code: 'INVALID_TOKEN'
      });
    }
    
    if (!user.isActive) {
      return res.status(401).json({
        error: 'Cuenta desactivada',
        code: 'ACCOUNT_DISABLED'
      });
    }
    
    // Agregar usuario a la request
    req.user = user;
    req.token = token;
    
    next();
  } catch (error) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        error: 'Token inválido',
        code: 'INVALID_TOKEN'
      });
    }
    
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        error: 'Token expirado',
        code: 'TOKEN_EXPIRED'
      });
    }
    
    console.error('Error en autenticación:', error);
    res.status(500).json({
      error: 'Error interno del servidor',
      code: 'INTERNAL_ERROR'
    });
  }
};

/**
 * Middleware para verificar roles específicos
 */
const requireRole = (...roles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        error: 'Autenticación requerida',
        code: 'AUTHENTICATION_REQUIRED'
      });
    }
    
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        error: 'Permisos insuficientes',
        code: 'INSUFFICIENT_PERMISSIONS',
        required: roles,
        current: req.user.role
      });
    }
    
    next();
  };
};

/**
 * Middleware para verificar si es admin
 */
const requireAdmin = requireRole('admin');

/**
 * Middleware para verificar si es admin o el mismo usuario
 */
const requireAdminOrSelf = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({
      error: 'Autenticación requerida',
      code: 'AUTHENTICATION_REQUIRED'
    });
  }
  
  const targetUserId = req.params.userId || req.params.id;
  const isAdmin = req.user.role === 'admin';
  const isSelf = req.user._id.toString() === targetUserId;
  
  if (!isAdmin && !isSelf) {
    return res.status(403).json({
      error: 'Solo puedes acceder a tu propia información o ser administrador',
      code: 'INSUFFICIENT_PERMISSIONS'
    });
  }
  
  next();
};

/**
 * Middleware opcional de autenticación (no falla si no hay token)
 */
const optionalAuth = async (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader && authHeader.split(' ')[1];
    
    if (token) {
      const decoded = jwt.verify(token, process.env.JWT_SECRET);
      const user = await User.findById(decoded.userId).select('-password');
      
      if (user && user.isActive) {
        req.user = user;
        req.token = token;
      }
    }
    
    next();
  } catch (error) {
    // En autenticación opcional, continuamos sin usuario
    next();
  }
};

/**
 * Middleware para verificar permisos en grupos
 */
const requireGroupPermission = (permission) => {
  return async (req, res, next) => {
    try {
      const Group = require('../models/Group');
      const groupId = req.params.groupId || req.body.groupId;
      
      if (!groupId) {
        return res.status(400).json({
          error: 'ID de grupo requerido',
          code: 'GROUP_ID_REQUIRED'
        });
      }
      
      const group = await Group.findById(groupId);
      
      if (!group) {
        return res.status(404).json({
          error: 'Grupo no encontrado',
          code: 'GROUP_NOT_FOUND'
        });
      }
      
      // Verificar si el usuario es miembro del grupo
      if (!group.isMember(req.user._id)) {
        return res.status(403).json({
          error: 'No eres miembro de este grupo',
          code: 'NOT_GROUP_MEMBER'
        });
      }
      
      // Verificar permisos específicos
      if (!group.hasPermission(req.user._id, permission)) {
        return res.status(403).json({
          error: `No tienes permisos para: ${permission}`,
          code: 'INSUFFICIENT_GROUP_PERMISSIONS'
        });
      }
      
      req.group = group;
      next();
    } catch (error) {
      console.error('Error verificando permisos de grupo:', error);
      res.status(500).json({
        error: 'Error interno del servidor',
        code: 'INTERNAL_ERROR'
      });
    }
  };
};

/**
 * Middleware para verificar propiedad de recursos
 */
const requireOwnership = (Model, paramName = 'id') => {
  return async (req, res, next) => {
    try {
      const resourceId = req.params[paramName];
      const resource = await Model.findById(resourceId);
      
      if (!resource) {
        return res.status(404).json({
          error: 'Recurso no encontrado',
          code: 'RESOURCE_NOT_FOUND'
        });
      }
      
      // Verificar propiedad (campo uploadedBy, createdBy, etc.)
      const ownerField = resource.uploadedBy || resource.createdBy || resource.userId;
      
      if (!ownerField || ownerField.toString() !== req.user._id.toString()) {
        // Los admins pueden acceder a cualquier recurso
        if (req.user.role !== 'admin') {
          return res.status(403).json({
            error: 'No tienes permisos para acceder a este recurso',
            code: 'NOT_RESOURCE_OWNER'
          });
        }
      }
      
      req.resource = resource;
      next();
    } catch (error) {
      console.error('Error verificando propiedad:', error);
      res.status(500).json({
        error: 'Error interno del servidor',
        code: 'INTERNAL_ERROR'
      });
    }
  };
};

/**
 * Utilidad para generar tokens JWT
 */
const generateTokens = (userId) => {
  const accessToken = jwt.sign(
    { userId },
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRE || '24h' }
  );
  
  const refreshToken = jwt.sign(
    { userId },
    process.env.JWT_REFRESH_SECRET,
    { expiresIn: process.env.JWT_REFRESH_EXPIRE || '7d' }
  );
  
  return { accessToken, refreshToken };
};

/**
 * Utilidad para verificar refresh token
 */
const verifyRefreshToken = (token) => {
  return jwt.verify(token, process.env.JWT_REFRESH_SECRET);
};

module.exports = {
  authenticateToken,
  requireRole,
  requireAdmin,
  requireAdminOrSelf,
  optionalAuth,
  requireGroupPermission,
  requireOwnership,
  generateTokens,
  verifyRefreshToken
};
