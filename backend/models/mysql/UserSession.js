/**
 * Modelo MySQL - User Sessions
 * Para tracking de sesiones de usuario
 */

const { DataTypes } = require('sequelize');
const { sequelize } = require('../../config/database');

const UserSession = sequelize.define('UserSession', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  
  // Información de la sesión
  sessionId: {
    type: DataTypes.STRING,
    allowNull: false,
    unique: true,
    field: 'session_id'
  },
  
  userId: {
    type: DataTypes.STRING,
    allowNull: false,
    field: 'user_id',
    comment: 'ID del usuario de MongoDB'
  },
  
  // Información del dispositivo/navegador
  ipAddress: {
    type: DataTypes.STRING(45),
    allowNull: false,
    field: 'ip_address'
  },
  
  userAgent: {
    type: DataTypes.TEXT,
    allowNull: true,
    field: 'user_agent'
  },
  
  deviceType: {
    type: DataTypes.ENUM('desktop', 'mobile', 'tablet', 'unknown'),
    defaultValue: 'unknown',
    field: 'device_type'
  },
  
  browser: {
    type: DataTypes.STRING(100),
    allowNull: true
  },
  
  os: {
    type: DataTypes.STRING(100),
    allowNull: true
  },
  
  // Estado de la sesión
  status: {
    type: DataTypes.ENUM('active', 'expired', 'terminated'),
    defaultValue: 'active'
  },
  
  // Timestamps
  loginAt: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW,
    field: 'login_at'
  },
  
  lastActivity: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW,
    field: 'last_activity'
  },
  
  logoutAt: {
    type: DataTypes.DATE,
    allowNull: true,
    field: 'logout_at'
  },
  
  expiresAt: {
    type: DataTypes.DATE,
    allowNull: true,
    field: 'expires_at'
  }
}, {
  tableName: 'user_sessions',
  timestamps: false,
  
  indexes: [
    { fields: ['session_id'] },
    { fields: ['user_id'] },
    { fields: ['status'] },
    { fields: ['last_activity'] },
    { fields: ['ip_address'] }
  ]
});

// Métodos de instancia
UserSession.prototype.updateActivity = async function() {
  this.lastActivity = new Date();
  return await this.save();
};

UserSession.prototype.terminate = async function() {
  this.status = 'terminated';
  this.logoutAt = new Date();
  return await this.save();
};

UserSession.prototype.isExpired = function() {
  if (!this.expiresAt) return false;
  return new Date() > this.expiresAt;
};

// Métodos estáticos
UserSession.createSession = async function(sessionData) {
  try {
    // Detectar tipo de dispositivo basado en user agent
    const deviceType = UserSession.detectDeviceType(sessionData.userAgent);
    const browserInfo = UserSession.parseBrowserInfo(sessionData.userAgent);
    
    // Calcular fecha de expiración (24 horas por defecto)
    const expiresAt = new Date();
    expiresAt.setHours(expiresAt.getHours() + 24);
    
    return await this.create({
      sessionId: sessionData.sessionId,
      userId: sessionData.userId,
      ipAddress: sessionData.ipAddress,
      userAgent: sessionData.userAgent,
      deviceType: deviceType,
      browser: browserInfo.browser,
      os: browserInfo.os,
      expiresAt: expiresAt
    });
  } catch (error) {
    console.error('Error creating session:', error);
    return null;
  }
};

UserSession.getActiveSession = async function(sessionId) {
  return await this.findOne({
    where: {
      sessionId,
      status: 'active'
    }
  });
};

UserSession.getUserSessions = async function(userId, activeOnly = true) {
  const where = { userId };
  if (activeOnly) {
    where.status = 'active';
  }
  
  return await this.findAll({
    where,
    order: [['lastActivity', 'DESC']]
  });
};

UserSession.terminateUserSessions = async function(userId, exceptSessionId = null) {
  const where = {
    userId,
    status: 'active'
  };
  
  if (exceptSessionId) {
    where.sessionId = { [sequelize.Op.ne]: exceptSessionId };
  }
  
  return await this.update(
    {
      status: 'terminated',
      logoutAt: new Date()
    },
    { where }
  );
};

UserSession.cleanExpiredSessions = async function() {
  const { Op } = require('sequelize');
  
  return await this.update(
    { status: 'expired' },
    {
      where: {
        status: 'active',
        expiresAt: {
          [Op.lt]: new Date()
        }
      }
    }
  );
};

UserSession.getSessionStats = async function(days = 30) {
  const { Op } = require('sequelize');
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - days);
  
  return await this.findAll({
    attributes: [
      [sequelize.fn('DATE', sequelize.col('login_at')), 'date'],
      'deviceType',
      [sequelize.fn('COUNT', sequelize.col('id')), 'sessionCount'],
      [sequelize.fn('COUNT', sequelize.fn('DISTINCT', sequelize.col('user_id'))), 'uniqueUsers']
    ],
    where: {
      loginAt: {
        [Op.gte]: startDate
      }
    },
    group: [sequelize.fn('DATE', sequelize.col('login_at')), 'deviceType'],
    order: [[sequelize.fn('DATE', sequelize.col('login_at')), 'DESC']]
  });
};

// Métodos de utilidad
UserSession.detectDeviceType = function(userAgent) {
  if (!userAgent) return 'unknown';
  
  const ua = userAgent.toLowerCase();
  
  if (ua.includes('mobile') || ua.includes('android') || ua.includes('iphone')) {
    return 'mobile';
  } else if (ua.includes('tablet') || ua.includes('ipad')) {
    return 'tablet';
  } else {
    return 'desktop';
  }
};

UserSession.parseBrowserInfo = function(userAgent) {
  if (!userAgent) return { browser: null, os: null };
  
  const ua = userAgent.toLowerCase();
  let browser = 'unknown';
  let os = 'unknown';
  
  // Detectar navegador
  if (ua.includes('chrome')) browser = 'Chrome';
  else if (ua.includes('firefox')) browser = 'Firefox';
  else if (ua.includes('safari')) browser = 'Safari';
  else if (ua.includes('edge')) browser = 'Edge';
  else if (ua.includes('opera')) browser = 'Opera';
  
  // Detectar OS
  if (ua.includes('windows')) os = 'Windows';
  else if (ua.includes('mac')) os = 'macOS';
  else if (ua.includes('linux')) os = 'Linux';
  else if (ua.includes('android')) os = 'Android';
  else if (ua.includes('ios')) os = 'iOS';
  
  return { browser, os };
};

module.exports = UserSession;
