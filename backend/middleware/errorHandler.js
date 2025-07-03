/**
 * Middleware de Manejo de Errores - Sistema de Gestión de Evidencias
 * Centraliza el manejo de errores y proporciona respuestas consistentes
 */

/**
 * Middleware de manejo de errores global
 */
const errorHandler = (err, req, res, next) => {
  let error = { ...err };
  error.message = err.message;

  // Log del error para debugging
  console.error('Error:', err);

  // Error de validación de Mongoose
  if (err.name === 'ValidationError') {
    const message = Object.values(err.errors).map(val => val.message).join(', ');
    error = {
      message: 'Error de validación',
      details: message,
      code: 'VALIDATION_ERROR'
    };
    return res.status(400).json({ error: error.message, details: error.details, code: error.code });
  }

  // Error de duplicado de Mongoose
  if (err.code === 11000) {
    const field = Object.keys(err.keyValue)[0];
    const value = err.keyValue[field];
    error = {
      message: `El ${field} '${value}' ya existe`,
      code: 'DUPLICATE_FIELD'
    };
    return res.status(409).json({ error: error.message, code: error.code });
  }

  // Error de ObjectId inválido de Mongoose
  if (err.name === 'CastError') {
    error = {
      message: 'ID de recurso inválido',
      code: 'INVALID_ID'
    };
    return res.status(400).json({ error: error.message, code: error.code });
  }

  // Error de JWT
  if (err.name === 'JsonWebTokenError') {
    error = {
      message: 'Token inválido',
      code: 'INVALID_TOKEN'
    };
    return res.status(401).json({ error: error.message, code: error.code });
  }

  // Error de JWT expirado
  if (err.name === 'TokenExpiredError') {
    error = {
      message: 'Token expirado',
      code: 'TOKEN_EXPIRED'
    };
    return res.status(401).json({ error: error.message, code: error.code });
  }

  // Error de archivo muy grande
  if (err.code === 'LIMIT_FILE_SIZE') {
    error = {
      message: 'El archivo es demasiado grande',
      code: 'FILE_TOO_LARGE'
    };
    return res.status(413).json({ error: error.message, code: error.code });
  }

  // Error de tipo de archivo no permitido
  if (err.code === 'INVALID_FILE_TYPE') {
    error = {
      message: 'Tipo de archivo no permitido',
      code: 'INVALID_FILE_TYPE'
    };
    return res.status(400).json({ error: error.message, code: error.code });
  }

  // Error de conexión a la base de datos
  if (err.name === 'MongoNetworkError' || err.name === 'MongooseServerSelectionError') {
    error = {
      message: 'Error de conexión a la base de datos',
      code: 'DATABASE_CONNECTION_ERROR'
    };
    return res.status(503).json({ error: error.message, code: error.code });
  }

  // Error por defecto
  res.status(err.statusCode || 500).json({
    error: error.message || 'Error interno del servidor',
    code: error.code || 'INTERNAL_ERROR'
  });
};

/**
 * Middleware para manejar rutas no encontradas
 */
const notFound = (req, res, next) => {
  const error = new Error(`Ruta no encontrada - ${req.originalUrl}`);
  error.statusCode = 404;
  error.code = 'ROUTE_NOT_FOUND';
  next(error);
};

/**
 * Clase para errores personalizados
 */
class AppError extends Error {
  constructor(message, statusCode, code = null) {
    super(message);
    this.statusCode = statusCode;
    this.code = code;
    this.isOperational = true;

    Error.captureStackTrace(this, this.constructor);
  }
}

/**
 * Wrapper para funciones async para capturar errores automáticamente
 */
const asyncHandler = (fn) => (req, res, next) => {
  Promise.resolve(fn(req, res, next)).catch(next);
};

/**
 * Validador de parámetros de ID
 */
const validateObjectId = (paramName = 'id') => {
  return (req, res, next) => {
    const mongoose = require('mongoose');
    const id = req.params[paramName];
    
    if (!mongoose.Types.ObjectId.isValid(id)) {
      return next(new AppError(`ID inválido: ${id}`, 400, 'INVALID_ID'));
    }
    
    next();
  };
};

/**
 * Middleware de logging de errores
 */
const logErrors = (err, req, res, next) => {
  // En producción, aquí se enviarían los logs a un servicio como Sentry
  const errorLog = {
    timestamp: new Date().toISOString(),
    method: req.method,
    url: req.originalUrl,
    ip: req.ip,
    userAgent: req.get('User-Agent'),
    user: req.user ? req.user._id : 'anonymous',
    error: {
      name: err.name,
      message: err.message,
      stack: err.stack,
      code: err.code
    }
  };

  // Log a archivo o servicio externo
  console.error('Error Log:', JSON.stringify(errorLog, null, 2));
  
  next(err);
};

/**
 * Middleware para validar tipos de contenido
 */
const validateContentType = (allowedTypes = ['application/json']) => {
  return (req, res, next) => {
    if (req.method === 'GET' || req.method === 'DELETE') {
      return next();
    }

    const contentType = req.get('Content-Type');
    
    if (!contentType) {
      return next(new AppError('Content-Type header requerido', 400, 'MISSING_CONTENT_TYPE'));
    }

    const isValidType = allowedTypes.some(type => contentType.includes(type));
    
    if (!isValidType) {
      return next(new AppError(
        `Content-Type no válido. Tipos permitidos: ${allowedTypes.join(', ')}`,
        400,
        'INVALID_CONTENT_TYPE'
      ));
    }

    next();
  };
};

/**
 * Middleware para limitar el tamaño del body
 */
const limitBodySize = (maxSize = '10mb') => {
  return (req, res, next) => {
    const contentLength = req.get('Content-Length');
    
    if (contentLength) {
      const sizeInBytes = parseInt(contentLength);
      const maxSizeInBytes = parseSize(maxSize);
      
      if (sizeInBytes > maxSizeInBytes) {
        return next(new AppError(
          `Cuerpo de la petición demasiado grande. Máximo permitido: ${maxSize}`,
          413,
          'BODY_TOO_LARGE'
        ));
      }
    }
    
    next();
  };
};

/**
 * Utilidad para convertir tamaños legibles a bytes
 */
const parseSize = (size) => {
  const units = {
    'b': 1,
    'kb': 1024,
    'mb': 1024 * 1024,
    'gb': 1024 * 1024 * 1024
  };
  
  const match = size.toLowerCase().match(/^(\d+(?:\.\d+)?)\s*(b|kb|mb|gb)$/);
  
  if (!match) {
    throw new Error('Formato de tamaño inválido');
  }
  
  const value = parseFloat(match[1]);
  const unit = match[2];
  
  return Math.floor(value * units[unit]);
};

/**
 * Middleware para sanitizar respuestas
 */
const sanitizeResponse = (req, res, next) => {
  const originalJson = res.json;
  
  res.json = function(data) {
    // Remover campos sensibles de las respuestas
    if (data && typeof data === 'object') {
      data = sanitizeObject(data);
    }
    
    return originalJson.call(this, data);
  };
  
  next();
};

/**
 * Función para sanitizar objetos removiendo campos sensibles
 */
const sanitizeObject = (obj) => {
  if (Array.isArray(obj)) {
    return obj.map(sanitizeObject);
  }
  
  if (obj && typeof obj === 'object') {
    const sanitized = { ...obj };
    
    // Campos a remover
    const sensitiveFields = [
      'password',
      'passwordResetToken',
      'emailVerificationToken',
      'encryptionKey',
      'accessCode'
    ];
    
    sensitiveFields.forEach(field => {
      delete sanitized[field];
    });
    
    // Recursivamente sanitizar objetos anidados
    Object.keys(sanitized).forEach(key => {
      if (sanitized[key] && typeof sanitized[key] === 'object') {
        sanitized[key] = sanitizeObject(sanitized[key]);
      }
    });
    
    return sanitized;
  }
  
  return obj;
};

module.exports = {
  errorHandler,
  notFound,
  AppError,
  asyncHandler,
  validateObjectId,
  logErrors,
  validateContentType,
  limitBodySize,
  sanitizeResponse
};
