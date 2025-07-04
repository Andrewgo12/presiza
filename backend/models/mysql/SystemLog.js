/**
 * Modelo MySQL - System Logs
 * Para logs del sistema y errores
 */

const { DataTypes } = require('sequelize');
const { sequelize } = require('../../config/database');

const SystemLog = sequelize.define('SystemLog', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  
  // Información del log
  level: {
    type: DataTypes.ENUM('error', 'warn', 'info', 'debug'),
    allowNull: false,
    defaultValue: 'info'
  },
  
  message: {
    type: DataTypes.TEXT,
    allowNull: false
  },
  
  // Contexto del error
  component: {
    type: DataTypes.STRING(100),
    allowNull: true,
    comment: 'Componente que generó el log'
  },
  
  functionName: {
    type: DataTypes.STRING(100),
    allowNull: true,
    field: 'function_name',
    comment: 'Función donde ocurrió'
  },
  
  // Información técnica
  stackTrace: {
    type: DataTypes.TEXT,
    allowNull: true,
    field: 'stack_trace'
  },
  
  errorCode: {
    type: DataTypes.STRING(50),
    allowNull: true,
    field: 'error_code'
  },
  
  // Request info
  requestId: {
    type: DataTypes.STRING,
    allowNull: true,
    field: 'request_id'
  },
  
  userId: {
    type: DataTypes.STRING,
    allowNull: true,
    field: 'user_id'
  },
  
  ipAddress: {
    type: DataTypes.STRING(45),
    allowNull: true,
    field: 'ip_address'
  },
  
  userAgent: {
    type: DataTypes.TEXT,
    allowNull: true,
    field: 'user_agent'
  },
  
  // Metadata adicional
  metadata: {
    type: DataTypes.JSON,
    allowNull: true
  },
  
  // Timestamp
  timestamp: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  }
}, {
  tableName: 'system_logs',
  timestamps: false,
  
  indexes: [
    { fields: ['level'] },
    { fields: ['timestamp'] },
    { fields: ['component'] },
    { fields: ['user_id'] },
    { fields: ['level', 'timestamp'] }
  ]
});

// Métodos estáticos
SystemLog.logError = async function(error, context = {}) {
  try {
    return await this.create({
      level: 'error',
      message: error.message || error.toString(),
      component: context.component,
      functionName: context.functionName,
      stackTrace: error.stack,
      errorCode: error.code,
      requestId: context.requestId,
      userId: context.userId,
      ipAddress: context.ipAddress,
      userAgent: context.userAgent,
      metadata: context.metadata
    });
  } catch (logError) {
    console.error('Error logging to database:', logError);
    return null;
  }
};

SystemLog.logInfo = async function(message, context = {}) {
  try {
    return await this.create({
      level: 'info',
      message,
      component: context.component,
      functionName: context.functionName,
      requestId: context.requestId,
      userId: context.userId,
      ipAddress: context.ipAddress,
      userAgent: context.userAgent,
      metadata: context.metadata
    });
  } catch (error) {
    console.error('Error logging to database:', error);
    return null;
  }
};

SystemLog.logWarning = async function(message, context = {}) {
  try {
    return await this.create({
      level: 'warn',
      message,
      component: context.component,
      functionName: context.functionName,
      requestId: context.requestId,
      userId: context.userId,
      ipAddress: context.ipAddress,
      userAgent: context.userAgent,
      metadata: context.metadata
    });
  } catch (error) {
    console.error('Error logging to database:', error);
    return null;
  }
};

SystemLog.getRecentLogs = async function(level = null, limit = 100) {
  const where = level ? { level } : {};
  
  return await this.findAll({
    where,
    order: [['timestamp', 'DESC']],
    limit
  });
};

SystemLog.getErrorStats = async function(days = 7) {
  const { Op } = require('sequelize');
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - days);
  
  return await this.findAll({
    attributes: [
      'level',
      'component',
      [sequelize.fn('COUNT', sequelize.col('id')), 'count']
    ],
    where: {
      timestamp: {
        [Op.gte]: startDate
      }
    },
    group: ['level', 'component'],
    order: [[sequelize.fn('COUNT', sequelize.col('id')), 'DESC']]
  });
};

module.exports = SystemLog;
