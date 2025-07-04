/**
 * Modelo MySQL - Audit Log
 * Para auditoría y logs del sistema usando MySQL/XAMPP
 */

const { DataTypes } = require('sequelize');
const { sequelize } = require('../../config/database');

const AuditLog = sequelize.define('AuditLog', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  
  // Usuario que realizó la acción
  userId: {
    type: DataTypes.STRING,
    allowNull: true,
    comment: 'ID del usuario de MongoDB'
  },
  
  userEmail: {
    type: DataTypes.STRING,
    allowNull: true,
    validate: {
      isEmail: true
    }
  },
  
  // Información de la acción
  action: {
    type: DataTypes.STRING,
    allowNull: false,
    comment: 'Tipo de acción realizada'
  },
  
  resource: {
    type: DataTypes.STRING,
    allowNull: false,
    comment: 'Recurso afectado (file, user, group, etc.)'
  },
  
  resourceId: {
    type: DataTypes.STRING,
    allowNull: true,
    comment: 'ID del recurso afectado'
  },
  
  // Detalles de la acción
  details: {
    type: DataTypes.JSON,
    allowNull: true,
    comment: 'Detalles adicionales de la acción'
  },
  
  // Información de la sesión
  ipAddress: {
    type: DataTypes.STRING,
    allowNull: true,
    validate: {
      isIP: true
    }
  },
  
  userAgent: {
    type: DataTypes.TEXT,
    allowNull: true
  },
  
  sessionId: {
    type: DataTypes.STRING,
    allowNull: true
  },
  
  // Resultado de la acción
  success: {
    type: DataTypes.BOOLEAN,
    defaultValue: true
  },
  
  errorMessage: {
    type: DataTypes.TEXT,
    allowNull: true
  },
  
  // Timestamps automáticos
  timestamp: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  }
}, {
  tableName: 'audit_logs',
  timestamps: true,
  createdAt: 'timestamp',
  updatedAt: false,
  
  indexes: [
    {
      fields: ['userId']
    },
    {
      fields: ['action']
    },
    {
      fields: ['resource']
    },
    {
      fields: ['timestamp']
    },
    {
      fields: ['ipAddress']
    }
  ]
});

// Métodos estáticos
AuditLog.logAction = async function(actionData) {
  try {
    return await this.create({
      userId: actionData.userId,
      userEmail: actionData.userEmail,
      action: actionData.action,
      resource: actionData.resource,
      resourceId: actionData.resourceId,
      details: actionData.details,
      ipAddress: actionData.ipAddress,
      userAgent: actionData.userAgent,
      sessionId: actionData.sessionId,
      success: actionData.success !== false,
      errorMessage: actionData.errorMessage
    });
  } catch (error) {
    console.error('Error logging audit action:', error);
    return null;
  }
};

AuditLog.getRecentActivity = async function(limit = 50) {
  return await this.findAll({
    order: [['timestamp', 'DESC']],
    limit: limit
  });
};

AuditLog.getUserActivity = async function(userId, limit = 20) {
  return await this.findAll({
    where: { userId },
    order: [['timestamp', 'DESC']],
    limit: limit
  });
};

AuditLog.getResourceActivity = async function(resource, resourceId, limit = 10) {
  return await this.findAll({
    where: { 
      resource,
      resourceId 
    },
    order: [['timestamp', 'DESC']],
    limit: limit
  });
};

module.exports = AuditLog;
