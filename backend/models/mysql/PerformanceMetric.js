/**
 * Modelo MySQL - Performance Metrics
 * Para métricas de rendimiento del sistema
 */

const { DataTypes } = require('sequelize');
const { sequelize } = require('../../config/database');

const PerformanceMetric = sequelize.define('PerformanceMetric', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  
  // Información de la métrica
  metricName: {
    type: DataTypes.STRING(100),
    allowNull: false,
    field: 'metric_name'
  },
  
  metricValue: {
    type: DataTypes.DECIMAL(10, 4),
    allowNull: false,
    field: 'metric_value'
  },
  
  unit: {
    type: DataTypes.STRING(20),
    allowNull: true,
    comment: 'ms, seconds, bytes, etc.'
  },
  
  // Contexto
  endpoint: {
    type: DataTypes.STRING,
    allowNull: true
  },
  
  method: {
    type: DataTypes.STRING(10),
    allowNull: true
  },
  
  statusCode: {
    type: DataTypes.INTEGER,
    allowNull: true,
    field: 'status_code'
  },
  
  // Timing
  responseTime: {
    type: DataTypes.DECIMAL(10, 4),
    allowNull: true,
    field: 'response_time',
    comment: 'Tiempo de respuesta en ms'
  },
  
  cpuUsage: {
    type: DataTypes.DECIMAL(5, 2),
    allowNull: true,
    field: 'cpu_usage',
    comment: 'Uso de CPU en %'
  },
  
  memoryUsage: {
    type: DataTypes.BIGINT,
    allowNull: true,
    field: 'memory_usage',
    comment: 'Uso de memoria en bytes'
  },
  
  // Request info
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
  
  // Timestamp
  timestamp: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  }
}, {
  tableName: 'performance_metrics',
  timestamps: false,
  
  indexes: [
    { fields: ['metric_name'] },
    { fields: ['timestamp'] },
    { fields: ['endpoint'] },
    { fields: ['response_time'] },
    { fields: ['metric_name', 'timestamp'] }
  ]
});

// Métodos estáticos
PerformanceMetric.recordApiCall = async function(data) {
  try {
    return await this.create({
      metricName: 'api_response_time',
      metricValue: data.responseTime,
      unit: 'ms',
      endpoint: data.endpoint,
      method: data.method,
      statusCode: data.statusCode,
      responseTime: data.responseTime,
      userId: data.userId,
      ipAddress: data.ipAddress
    });
  } catch (error) {
    console.error('Error recording API call metric:', error);
    return null;
  }
};

PerformanceMetric.recordSystemMetric = async function(metricName, value, unit = null) {
  try {
    const systemInfo = process.memoryUsage();
    const cpuUsage = process.cpuUsage();
    
    return await this.create({
      metricName,
      metricValue: value,
      unit,
      cpuUsage: (cpuUsage.user + cpuUsage.system) / 1000000, // Convert to %
      memoryUsage: systemInfo.heapUsed
    });
  } catch (error) {
    console.error('Error recording system metric:', error);
    return null;
  }
};

PerformanceMetric.getAverageResponseTime = async function(endpoint = null, hours = 24) {
  const { Op } = require('sequelize');
  const startTime = new Date();
  startTime.setHours(startTime.getHours() - hours);
  
  const where = {
    metricName: 'api_response_time',
    timestamp: {
      [Op.gte]: startTime
    }
  };
  
  if (endpoint) {
    where.endpoint = endpoint;
  }
  
  return await this.findOne({
    attributes: [
      [sequelize.fn('AVG', sequelize.col('response_time')), 'avgResponseTime'],
      [sequelize.fn('MIN', sequelize.col('response_time')), 'minResponseTime'],
      [sequelize.fn('MAX', sequelize.col('response_time')), 'maxResponseTime'],
      [sequelize.fn('COUNT', sequelize.col('id')), 'totalRequests']
    ],
    where
  });
};

PerformanceMetric.getSlowEndpoints = async function(threshold = 1000, hours = 24) {
  const { Op } = require('sequelize');
  const startTime = new Date();
  startTime.setHours(startTime.getHours() - hours);
  
  return await this.findAll({
    attributes: [
      'endpoint',
      'method',
      [sequelize.fn('AVG', sequelize.col('response_time')), 'avgResponseTime'],
      [sequelize.fn('COUNT', sequelize.col('id')), 'requestCount']
    ],
    where: {
      metricName: 'api_response_time',
      responseTime: {
        [Op.gte]: threshold
      },
      timestamp: {
        [Op.gte]: startTime
      }
    },
    group: ['endpoint', 'method'],
    order: [[sequelize.fn('AVG', sequelize.col('response_time')), 'DESC']],
    limit: 10
  });
};

PerformanceMetric.getSystemHealth = async function(hours = 1) {
  const { Op } = require('sequelize');
  const startTime = new Date();
  startTime.setHours(startTime.getHours() - hours);
  
  return await this.findAll({
    attributes: [
      'metricName',
      [sequelize.fn('AVG', sequelize.col('metric_value')), 'avgValue'],
      [sequelize.fn('MAX', sequelize.col('metric_value')), 'maxValue'],
      [sequelize.fn('AVG', sequelize.col('cpu_usage')), 'avgCpuUsage'],
      [sequelize.fn('AVG', sequelize.col('memory_usage')), 'avgMemoryUsage']
    ],
    where: {
      timestamp: {
        [Op.gte]: startTime
      }
    },
    group: ['metricName']
  });
};

module.exports = PerformanceMetric;
