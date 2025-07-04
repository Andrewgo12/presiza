/**
 * Middleware de Logging Avanzado
 * Sistema de Gestión de Evidencias
 */

const { v4: uuidv4 } = require('uuid');

/**
 * Middleware para logging de requests
 */
const requestLogger = (req, res, next) => {
  // Generar ID único para el request
  req.requestId = uuidv4();
  req.startTime = Date.now();
  
  // Agregar información del request
  req.requestInfo = {
    id: req.requestId,
    method: req.method,
    url: req.originalUrl,
    ip: req.ip || req.connection.remoteAddress,
    userAgent: req.get('User-Agent'),
    timestamp: new Date().toISOString()
  };
  
  // Log del request entrante
  console.log(`📥 [${req.requestId}] ${req.method} ${req.originalUrl} - ${req.requestInfo.ip}`);
  
  // Interceptar la respuesta
  const originalSend = res.send;
  res.send = function(data) {
    const responseTime = Date.now() - req.startTime;
    
    // Log de la respuesta
    console.log(`📤 [${req.requestId}] ${res.statusCode} - ${responseTime}ms`);
    
    // Registrar métricas de performance si MySQL está disponible
    if (global.mysqlConnected) {
      recordPerformanceMetric(req, res, responseTime);
    }
    
    return originalSend.call(this, data);
  };
  
  next();
};

/**
 * Registrar métrica de performance
 */
const recordPerformanceMetric = async (req, res, responseTime) => {
  try {
    const { PerformanceMetric } = require('../models/mysql');
    
    await PerformanceMetric.recordApiCall({
      endpoint: req.route ? req.route.path : req.originalUrl,
      method: req.method,
      statusCode: res.statusCode,
      responseTime: responseTime,
      userId: req.user ? req.user._id : null,
      ipAddress: req.requestInfo.ip
    });
  } catch (error) {
    console.error('Error recording performance metric:', error);
  }
};

/**
 * Middleware para logging de errores
 */
const errorLogger = (error, req, res, next) => {
  const errorInfo = {
    requestId: req.requestId,
    message: error.message,
    stack: error.stack,
    url: req.originalUrl,
    method: req.method,
    ip: req.requestInfo?.ip,
    userAgent: req.requestInfo?.userAgent,
    userId: req.user ? req.user._id : null,
    timestamp: new Date().toISOString()
  };
  
  // Log en consola
  console.error(`❌ [${req.requestId}] Error:`, {
    message: error.message,
    url: req.originalUrl,
    method: req.method,
    stack: process.env.NODE_ENV === 'development' ? error.stack : undefined
  });
  
  // Registrar en base de datos si está disponible
  if (global.mysqlConnected) {
    logErrorToDatabase(error, req);
  }
  
  next(error);
};

/**
 * Registrar error en base de datos
 */
const logErrorToDatabase = async (error, req) => {
  try {
    const { SystemLog } = require('../models/mysql');
    
    await SystemLog.logError(error, {
      component: 'api',
      functionName: req.route ? req.route.path : req.originalUrl,
      requestId: req.requestId,
      userId: req.user ? req.user._id : null,
      ipAddress: req.requestInfo?.ip,
      userAgent: req.requestInfo?.userAgent,
      metadata: {
        method: req.method,
        url: req.originalUrl,
        params: req.params,
        query: req.query,
        body: req.method !== 'GET' ? req.body : undefined
      }
    });
  } catch (logError) {
    console.error('Error logging to database:', logError);
  }
};

/**
 * Middleware para logging de auditoría
 */
const auditLogger = (action, resource) => {
  return async (req, res, next) => {
    // Ejecutar el siguiente middleware/controlador
    const originalSend = res.send;
    res.send = function(data) {
      // Solo registrar si la operación fue exitosa
      if (res.statusCode >= 200 && res.statusCode < 300) {
        logAuditAction(req, action, resource, true);
      } else {
        logAuditAction(req, action, resource, false, data);
      }
      
      return originalSend.call(this, data);
    };
    
    next();
  };
};

/**
 * Registrar acción de auditoría
 */
const logAuditAction = async (req, action, resource, success, errorData = null) => {
  try {
    if (!global.mysqlConnected) return;
    
    const { AuditLog } = require('../models/mysql');
    
    await AuditLog.logAction({
      userId: req.user ? req.user._id : null,
      userEmail: req.user ? req.user.email : null,
      action: action,
      resource: resource,
      resourceId: req.params.id || req.body.id || null,
      details: {
        method: req.method,
        url: req.originalUrl,
        params: req.params,
        query: req.query,
        body: req.method !== 'GET' ? req.body : undefined,
        errorData: success ? null : errorData
      },
      ipAddress: req.requestInfo?.ip,
      userAgent: req.requestInfo?.userAgent,
      sessionId: req.sessionId || null,
      success: success,
      errorMessage: success ? null : (errorData?.error || 'Unknown error')
    });
  } catch (error) {
    console.error('Error logging audit action:', error);
  }
};

/**
 * Middleware para logging de analytics
 */
const analyticsLogger = (metricType) => {
  return async (req, res, next) => {
    // Registrar métrica después de la respuesta
    const originalSend = res.send;
    res.send = function(data) {
      if (res.statusCode >= 200 && res.statusCode < 300) {
        recordAnalyticsMetric(req, metricType);
      }
      
      return originalSend.call(this, data);
    };
    
    next();
  };
};

/**
 * Registrar métrica de analytics
 */
const recordAnalyticsMetric = async (req, metricType) => {
  try {
    if (!global.mysqlConnected) return;
    
    const { Analytics } = require('../models/mysql');
    
    await Analytics.recordMetric({
      type: metricType,
      userId: req.user ? req.user._id : null,
      resourceId: req.params.id || null,
      ipAddress: req.requestInfo?.ip,
      userAgent: req.requestInfo?.userAgent,
      metadata: {
        method: req.method,
        url: req.originalUrl,
        params: req.params,
        query: req.query
      }
    });
  } catch (error) {
    console.error('Error recording analytics metric:', error);
  }
};

/**
 * Middleware para limpiar logs antiguos (ejecutar periódicamente)
 */
const cleanupLogs = async () => {
  try {
    if (!global.mysqlConnected) return;
    
    const { cleanOldData } = require('../models/mysql');
    const result = await cleanOldData(90); // Mantener 90 días
    
    if (result) {
      console.log('🧹 Limpieza automática de logs completada');
    }
  } catch (error) {
    console.error('Error en limpieza automática:', error);
  }
};

/**
 * Inicializar limpieza automática (cada 24 horas)
 */
const initAutoCleanup = () => {
  // Ejecutar limpieza cada 24 horas
  setInterval(cleanupLogs, 24 * 60 * 60 * 1000);
  
  // Ejecutar una vez al inicio (después de 5 minutos)
  setTimeout(cleanupLogs, 5 * 60 * 1000);
};

module.exports = {
  requestLogger,
  errorLogger,
  auditLogger,
  analyticsLogger,
  cleanupLogs,
  initAutoCleanup
};
