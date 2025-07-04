/**
 * Modelo MySQL - Analytics
 * Para métricas y estadísticas usando MySQL/XAMPP
 */

const { DataTypes } = require('sequelize');
const { sequelize } = require('../../config/database');

const Analytics = sequelize.define('Analytics', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  
  // Información temporal
  date: {
    type: DataTypes.DATEONLY,
    allowNull: false,
    comment: 'Fecha de la métrica (YYYY-MM-DD)'
  },
  
  hour: {
    type: DataTypes.INTEGER,
    allowNull: true,
    validate: {
      min: 0,
      max: 23
    },
    comment: 'Hora del día (0-23) para métricas por hora'
  },
  
  // Tipo de métrica
  metricType: {
    type: DataTypes.ENUM(
      'user_login',
      'file_upload',
      'file_download',
      'group_created',
      'message_sent',
      'evidence_created',
      'search_performed',
      'page_view',
      'api_call',
      'error_occurred'
    ),
    allowNull: false
  },
  
  // Valores de la métrica
  count: {
    type: DataTypes.INTEGER,
    defaultValue: 1,
    comment: 'Número de ocurrencias'
  },
  
  value: {
    type: DataTypes.DECIMAL(10, 2),
    allowNull: true,
    comment: 'Valor numérico asociado (tamaño de archivo, tiempo de respuesta, etc.)'
  },
  
  // Información adicional
  userId: {
    type: DataTypes.STRING,
    allowNull: true,
    comment: 'ID del usuario de MongoDB'
  },
  
  resourceId: {
    type: DataTypes.STRING,
    allowNull: true,
    comment: 'ID del recurso relacionado'
  },
  
  metadata: {
    type: DataTypes.JSON,
    allowNull: true,
    comment: 'Metadatos adicionales'
  },
  
  // Información de contexto
  ipAddress: {
    type: DataTypes.STRING,
    allowNull: true
  },
  
  userAgent: {
    type: DataTypes.TEXT,
    allowNull: true
  },
  
  // Timestamps
  timestamp: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  }
}, {
  tableName: 'analytics',
  timestamps: true,
  createdAt: 'timestamp',
  updatedAt: false,
  
  indexes: [
    {
      fields: ['date']
    },
    {
      fields: ['metricType']
    },
    {
      fields: ['userId']
    },
    {
      fields: ['date', 'metricType']
    },
    {
      fields: ['date', 'hour']
    }
  ]
});

// Métodos estáticos para analytics
Analytics.recordMetric = async function(metricData) {
  try {
    const today = new Date().toISOString().split('T')[0];
    const currentHour = new Date().getHours();
    
    return await this.create({
      date: metricData.date || today,
      hour: metricData.hour || currentHour,
      metricType: metricData.type,
      count: metricData.count || 1,
      value: metricData.value,
      userId: metricData.userId,
      resourceId: metricData.resourceId,
      metadata: metricData.metadata,
      ipAddress: metricData.ipAddress,
      userAgent: metricData.userAgent
    });
  } catch (error) {
    console.error('Error recording metric:', error);
    return null;
  }
};

Analytics.getDailyStats = async function(startDate, endDate) {
  const { Op } = require('sequelize');
  
  return await this.findAll({
    attributes: [
      'date',
      'metricType',
      [sequelize.fn('SUM', sequelize.col('count')), 'totalCount'],
      [sequelize.fn('AVG', sequelize.col('value')), 'avgValue'],
      [sequelize.fn('COUNT', sequelize.col('id')), 'records']
    ],
    where: {
      date: {
        [Op.between]: [startDate, endDate]
      }
    },
    group: ['date', 'metricType'],
    order: [['date', 'DESC']]
  });
};

Analytics.getHourlyStats = async function(date) {
  return await this.findAll({
    attributes: [
      'hour',
      'metricType',
      [sequelize.fn('SUM', sequelize.col('count')), 'totalCount']
    ],
    where: { date },
    group: ['hour', 'metricType'],
    order: [['hour', 'ASC']]
  });
};

Analytics.getUserStats = async function(userId, days = 30) {
  const { Op } = require('sequelize');
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - days);
  
  return await this.findAll({
    attributes: [
      'metricType',
      [sequelize.fn('SUM', sequelize.col('count')), 'totalCount'],
      [sequelize.fn('COUNT', sequelize.col('id')), 'records']
    ],
    where: {
      userId,
      date: {
        [Op.gte]: startDate.toISOString().split('T')[0]
      }
    },
    group: ['metricType']
  });
};

Analytics.getTopUsers = async function(metricType, days = 30, limit = 10) {
  const { Op } = require('sequelize');
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - days);
  
  return await this.findAll({
    attributes: [
      'userId',
      [sequelize.fn('SUM', sequelize.col('count')), 'totalCount']
    ],
    where: {
      metricType,
      userId: {
        [Op.ne]: null
      },
      date: {
        [Op.gte]: startDate.toISOString().split('T')[0]
      }
    },
    group: ['userId'],
    order: [[sequelize.fn('SUM', sequelize.col('count')), 'DESC']],
    limit
  });
};

module.exports = Analytics;
