/**
 * Rutas de Logs y Auditoría - Sistema de Gestión de Evidencias
 * Maneja consulta de logs, auditoría y métricas del sistema
 */

const express = require('express');
const { requireAdmin } = require('../middleware/auth');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

const router = express.Router();

/**
 * GET /api/v1/logs/audit
 * Obtener logs de auditoría (solo admins)
 */
router.get('/audit', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { AuditLog } = require('../models/mysql');
  const { page = 1, limit = 50, action, resource, userId, success } = req.query;

  // Construir filtros
  const where = {};
  if (action) where.action = action;
  if (resource) where.resource = resource;
  if (userId) where.userId = userId;
  if (success !== undefined) where.success = success === 'true';

  // Paginación
  const offset = (parseInt(page) - 1) * parseInt(limit);

  const logs = await AuditLog.findAndCountAll({
    where,
    order: [['timestamp', 'DESC']],
    limit: parseInt(limit),
    offset: offset
  });

  res.json({
    logs: logs.rows,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total: logs.count,
      pages: Math.ceil(logs.count / parseInt(limit))
    }
  });
}));

/**
 * GET /api/v1/logs/system
 * Obtener logs del sistema (solo admins)
 */
router.get('/system', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { SystemLog } = require('../models/mysql');
  const { page = 1, limit = 50, level, component } = req.query;

  // Construir filtros
  const where = {};
  if (level) where.level = level;
  if (component) where.component = component;

  // Paginación
  const offset = (parseInt(page) - 1) * parseInt(limit);

  const logs = await SystemLog.findAndCountAll({
    where,
    order: [['timestamp', 'DESC']],
    limit: parseInt(limit),
    offset: offset
  });

  res.json({
    logs: logs.rows,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total: logs.count,
      pages: Math.ceil(logs.count / parseInt(limit))
    }
  });
}));

/**
 * GET /api/v1/logs/performance
 * Obtener métricas de rendimiento (solo admins)
 */
router.get('/performance', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { PerformanceMetric } = require('../models/mysql');
  const { hours = 24, endpoint } = req.query;

  // Obtener métricas de rendimiento
  const avgResponseTime = await PerformanceMetric.getAverageResponseTime(endpoint, parseInt(hours));
  const slowEndpoints = await PerformanceMetric.getSlowEndpoints(1000, parseInt(hours));
  const systemHealth = await PerformanceMetric.getSystemHealth(parseInt(hours));

  res.json({
    averageResponseTime: avgResponseTime,
    slowEndpoints: slowEndpoints,
    systemHealth: systemHealth,
    period: `${hours} hours`
  });
}));

/**
 * GET /api/v1/logs/analytics
 * Obtener datos de analytics (solo admins)
 */
router.get('/analytics', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { Analytics } = require('../models/mysql');
  const { startDate, endDate, metricType } = req.query;

  // Fechas por defecto (últimos 7 días)
  const end = endDate ? new Date(endDate) : new Date();
  const start = startDate ? new Date(startDate) : new Date(end.getTime() - 7 * 24 * 60 * 60 * 1000);

  const startDateStr = start.toISOString().split('T')[0];
  const endDateStr = end.toISOString().split('T')[0];

  // Obtener estadísticas diarias
  const dailyStats = await Analytics.getDailyStats(startDateStr, endDateStr);

  // Obtener estadísticas por hora para hoy
  const today = new Date().toISOString().split('T')[0];
  const hourlyStats = await Analytics.getHourlyStats(today);

  res.json({
    dailyStats: dailyStats,
    hourlyStats: hourlyStats,
    period: {
      start: startDateStr,
      end: endDateStr
    }
  });
}));

/**
 * GET /api/v1/logs/sessions
 * Obtener información de sesiones (solo admins)
 */
router.get('/sessions', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { UserSession } = require('../models/mysql');
  const { page = 1, limit = 50, status = 'active', userId } = req.query;

  // Construir filtros
  const where = {};
  if (status) where.status = status;
  if (userId) where.userId = userId;

  // Paginación
  const offset = (parseInt(page) - 1) * parseInt(limit);

  const sessions = await UserSession.findAndCountAll({
    where,
    order: [['lastActivity', 'DESC']],
    limit: parseInt(limit),
    offset: offset
  });

  // Obtener estadísticas de sesiones
  const sessionStats = await UserSession.getSessionStats(30);

  res.json({
    sessions: sessions.rows,
    stats: sessionStats,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total: sessions.count,
      pages: Math.ceil(sessions.count / parseInt(limit))
    }
  });
}));

/**
 * GET /api/v1/logs/summary
 * Obtener resumen general de logs y métricas (solo admins)
 */
router.get('/summary', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    return res.json({
      message: 'Base de datos MySQL no disponible',
      mysql_connected: false,
      summary: null
    });
  }

  const { getGeneralStats } = require('../models/mysql');
  const stats = await getGeneralStats();

  res.json({
    mysql_connected: true,
    summary: stats,
    timestamp: new Date().toISOString()
  });
}));

/**
 * POST /api/v1/logs/cleanup
 * Limpiar logs antiguos manualmente (solo admins)
 */
router.post('/cleanup', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { daysToKeep = 90 } = req.body;
  const { cleanOldData } = require('../models/mysql');

  const result = await cleanOldData(parseInt(daysToKeep));

  if (result) {
    res.json({
      message: 'Limpieza completada exitosamente',
      deleted: result,
      daysKept: parseInt(daysToKeep)
    });
  } else {
    throw new AppError('Error durante la limpieza', 500, 'CLEANUP_ERROR');
  }
}));

/**
 * GET /api/v1/logs/export
 * Exportar logs en formato CSV (solo admins)
 */
router.get('/export', requireAdmin, asyncHandler(async (req, res) => {
  if (!global.mysqlConnected) {
    throw new AppError('Base de datos MySQL no disponible', 503, 'MYSQL_UNAVAILABLE');
  }

  const { type = 'audit', startDate, endDate } = req.query;

  // Fechas por defecto (últimos 30 días)
  const end = endDate ? new Date(endDate) : new Date();
  const start = startDate ? new Date(startDate) : new Date(end.getTime() - 30 * 24 * 60 * 60 * 1000);

  let data = [];
  let filename = '';

  if (type === 'audit') {
    const { AuditLog } = require('../models/mysql');
    const { Op } = require('sequelize');
    
    data = await AuditLog.findAll({
      where: {
        timestamp: {
          [Op.between]: [start, end]
        }
      },
      order: [['timestamp', 'DESC']],
      raw: true
    });
    filename = `audit_logs_${start.toISOString().split('T')[0]}_${end.toISOString().split('T')[0]}.csv`;
  } else if (type === 'system') {
    const { SystemLog } = require('../models/mysql');
    const { Op } = require('sequelize');
    
    data = await SystemLog.findAll({
      where: {
        timestamp: {
          [Op.between]: [start, end]
        }
      },
      order: [['timestamp', 'DESC']],
      raw: true
    });
    filename = `system_logs_${start.toISOString().split('T')[0]}_${end.toISOString().split('T')[0]}.csv`;
  }

  // Convertir a CSV
  if (data.length === 0) {
    throw new AppError('No hay datos para exportar en el rango especificado', 404, 'NO_DATA');
  }

  const headers = Object.keys(data[0]);
  const csvContent = [
    headers.join(','),
    ...data.map(row => headers.map(header => `"${row[header] || ''}"`).join(','))
  ].join('\n');

  res.setHeader('Content-Type', 'text/csv');
  res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
  res.send(csvContent);
}));

module.exports = router;
