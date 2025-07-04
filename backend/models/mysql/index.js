/**
 * Índice de Modelos MySQL
 * Sistema de Gestión de Evidencias
 */

const { sequelize } = require('../../config/database');

// Importar todos los modelos MySQL
const AuditLog = require('./AuditLog');
const Analytics = require('./Analytics');
const SystemLog = require('./SystemLog');
const PerformanceMetric = require('./PerformanceMetric');
const UserSession = require('./UserSession');

// Definir relaciones entre modelos (si las hay)
// Por ejemplo, si quisiéramos relacionar sesiones con logs de auditoría:
// UserSession.hasMany(AuditLog, { foreignKey: 'sessionId', sourceKey: 'sessionId' });
// AuditLog.belongsTo(UserSession, { foreignKey: 'sessionId', targetKey: 'sessionId' });

/**
 * Sincronizar todos los modelos MySQL
 */
const syncModels = async (force = false) => {
  try {
    await sequelize.sync({ force, alter: !force });
    console.log('✅ Modelos MySQL sincronizados exitosamente');
    return true;
  } catch (error) {
    console.error('❌ Error sincronizando modelos MySQL:', error);
    return false;
  }
};

/**
 * Verificar conexión y modelos
 */
const checkModels = async () => {
  try {
    await sequelize.authenticate();
    
    const models = {
      AuditLog: await AuditLog.count(),
      Analytics: await Analytics.count(),
      SystemLog: await SystemLog.count(),
      PerformanceMetric: await PerformanceMetric.count(),
      UserSession: await UserSession.count()
    };
    
    console.log('📊 Estado de modelos MySQL:', models);
    return models;
  } catch (error) {
    console.error('❌ Error verificando modelos MySQL:', error);
    return null;
  }
};

/**
 * Limpiar datos antiguos (mantenimiento)
 */
const cleanOldData = async (daysToKeep = 90) => {
  try {
    const { Op } = require('sequelize');
    const cutoffDate = new Date();
    cutoffDate.setDate(cutoffDate.getDate() - daysToKeep);
    
    // Limpiar logs antiguos
    const deletedAuditLogs = await AuditLog.destroy({
      where: {
        timestamp: {
          [Op.lt]: cutoffDate
        }
      }
    });
    
    const deletedSystemLogs = await SystemLog.destroy({
      where: {
        timestamp: {
          [Op.lt]: cutoffDate
        }
      }
    });
    
    const deletedMetrics = await PerformanceMetric.destroy({
      where: {
        timestamp: {
          [Op.lt]: cutoffDate
        }
      }
    });
    
    // Limpiar sesiones expiradas
    const deletedSessions = await UserSession.destroy({
      where: {
        status: 'expired',
        logoutAt: {
          [Op.lt]: cutoffDate
        }
      }
    });
    
    console.log(`🧹 Limpieza completada:
      - Audit Logs eliminados: ${deletedAuditLogs}
      - System Logs eliminados: ${deletedSystemLogs}
      - Performance Metrics eliminados: ${deletedMetrics}
      - Sesiones eliminadas: ${deletedSessions}`);
    
    return {
      auditLogs: deletedAuditLogs,
      systemLogs: deletedSystemLogs,
      performanceMetrics: deletedMetrics,
      userSessions: deletedSessions
    };
  } catch (error) {
    console.error('❌ Error en limpieza de datos:', error);
    return null;
  }
};

/**
 * Obtener estadísticas generales
 */
const getGeneralStats = async () => {
  try {
    const stats = {
      // Estadísticas de auditoría
      auditLogs: {
        total: await AuditLog.count(),
        today: await AuditLog.count({
          where: {
            timestamp: {
              [sequelize.Op.gte]: new Date().setHours(0, 0, 0, 0)
            }
          }
        }),
        errors: await AuditLog.count({
          where: { success: false }
        })
      },
      
      // Estadísticas de analytics
      analytics: {
        total: await Analytics.count(),
        today: await Analytics.count({
          where: {
            date: new Date().toISOString().split('T')[0]
          }
        })
      },
      
      // Estadísticas de sistema
      systemLogs: {
        total: await SystemLog.count(),
        errors: await SystemLog.count({
          where: { level: 'error' }
        }),
        warnings: await SystemLog.count({
          where: { level: 'warn' }
        })
      },
      
      // Estadísticas de rendimiento
      performance: {
        total: await PerformanceMetric.count(),
        today: await PerformanceMetric.count({
          where: {
            timestamp: {
              [sequelize.Op.gte]: new Date().setHours(0, 0, 0, 0)
            }
          }
        })
      },
      
      // Estadísticas de sesiones
      sessions: {
        total: await UserSession.count(),
        active: await UserSession.count({
          where: { status: 'active' }
        }),
        today: await UserSession.count({
          where: {
            loginAt: {
              [sequelize.Op.gte]: new Date().setHours(0, 0, 0, 0)
            }
          }
        })
      }
    };
    
    return stats;
  } catch (error) {
    console.error('❌ Error obteniendo estadísticas:', error);
    return null;
  }
};

module.exports = {
  // Modelos
  AuditLog,
  Analytics,
  SystemLog,
  PerformanceMetric,
  UserSession,
  
  // Instancia de Sequelize
  sequelize,
  
  // Funciones de utilidad
  syncModels,
  checkModels,
  cleanOldData,
  getGeneralStats
};
