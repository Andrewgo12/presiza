/**
 * =====================================================
 * SCRIPT DE INICIALIZACIÓN COMPLETA DE BASES DE DATOS
 * Evidence Management System
 * =====================================================
 * 
 * Este script inicializa tanto MongoDB como MySQL con:
 * - Esquemas y tablas necesarias
 * - Datos de ejemplo para desarrollo
 * - Usuarios por defecto
 * - Configuración inicial del sistema
 */

require('dotenv').config();
const { initializeDatabases, getDatabaseStatus } = require('../config/database');

/**
 * Inicializar MongoDB con datos de ejemplo
 */
const initializeMongoDB = async () => {
  try {
    console.log('🍃 Inicializando MongoDB...');

    // Importar funciones de MongoDB
    const {
      initializeDatabase: initMongoDB,
      createDefaultAdmin,
      createSampleUsers,
      createSampleGroups,
      createSampleEvidences,
      createSampleNotifications
    } = require('./mongodb_schema');

    // Ejecutar inicialización
    await initMongoDB();

    console.log('✅ MongoDB inicializado correctamente');
    return true;

  } catch (error) {
    console.error('❌ Error inicializando MongoDB:', error.message);
    console.log('💡 Verifica tu conexión a MongoDB Atlas o MongoDB local');
    return false;
  }
};

/**
 * Inicializar MySQL con datos de ejemplo
 */
const initializeMySQL = async () => {
  try {
    console.log('🐬 Inicializando MySQL...');

    const { sequelize } = require('../config/database');
    const { syncModels } = require('../models/mysql');

    // Sincronizar modelos (crear tablas)
    await syncModels(false); // false = no forzar recreación

    // Insertar datos de ejemplo si las tablas están vacías
    const { AuditLog, Analytics, SystemLog, PerformanceMetric, UserSession } = require('../models/mysql');

    // Verificar si ya hay datos
    const auditCount = await AuditLog.count();

    if (auditCount === 0) {
      console.log('📊 Insertando datos de ejemplo en MySQL (TRIPLICADOS)...');

      // Datos de ejemplo para audit_logs - TRIPLICADOS
      await AuditLog.bulkCreate([
        // Logins y autenticación
        {
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          action: 'LOGIN',
          resource: 'AUTH',
          ipAddress: '127.0.0.1',
          details: { method: 'email', browser: 'Chrome', success: true },
          success: true,
          timestamp: new Date('2024-07-04 09:00:00')
        },
        {
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          action: 'LOGIN',
          resource: 'AUTH',
          ipAddress: '192.168.1.100',
          details: { method: 'email', browser: 'Firefox', success: true },
          success: true,
          timestamp: new Date('2024-07-04 08:30:00')
        },
        {
          userId: '507f1f77bcf86cd799439013',
          userEmail: 'analyst@test.com',
          action: 'LOGIN',
          resource: 'AUTH',
          ipAddress: '192.168.1.101',
          details: { method: 'email', browser: 'Safari', success: true },
          success: true,
          timestamp: new Date('2024-07-04 08:45:00')
        },
        {
          userId: '507f1f77bcf86cd799439014',
          userEmail: 'investigator@test.com',
          action: 'LOGIN',
          resource: 'AUTH',
          ipAddress: '192.168.1.102',
          details: { method: 'email', browser: 'Edge', success: true },
          success: true,
          timestamp: new Date('2024-07-04 09:15:00')
        },

        // Acciones de archivos
        {
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          action: 'UPLOAD',
          resource: 'FILE',
          resourceId: '507f1f77bcf86cd799439020',
          ipAddress: '192.168.1.100',
          details: { filename: 'research_analysis_q4.pdf', size: 2048576, category: 'document' },
          success: true,
          timestamp: new Date('2024-06-01 14:30:00')
        },
        {
          userId: '507f1f77bcf86cd799439013',
          userEmail: 'analyst@test.com',
          action: 'DOWNLOAD',
          resource: 'FILE',
          resourceId: '507f1f77bcf86cd799439020',
          ipAddress: '192.168.1.101',
          details: { filename: 'research_analysis_q4.pdf', downloadDuration: 3.2 },
          success: true,
          timestamp: new Date('2024-06-02 10:15:00')
        },
        {
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          action: 'DELETE',
          resource: 'FILE',
          resourceId: '507f1f77bcf86cd799439021',
          ipAddress: '127.0.0.1',
          details: { filename: 'old_backup.zip', reason: 'expired' },
          success: true,
          timestamp: new Date('2024-06-15 16:00:00')
        },

        // Acciones de evidencias
        {
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          action: 'CREATE',
          resource: 'EVIDENCE',
          resourceId: '507f1f77bcf86cd799439030',
          ipAddress: '192.168.1.100',
          details: { title: 'Q4 Research Analysis', type: 'document', priority: 'high' },
          success: true,
          timestamp: new Date('2024-06-01 15:00:00')
        },
        {
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          action: 'APPROVE',
          resource: 'EVIDENCE',
          resourceId: '507f1f77bcf86cd799439030',
          ipAddress: '127.0.0.1',
          details: { previousStatus: 'under_review', newStatus: 'approved', feedback: 'Excellent work' },
          success: true,
          timestamp: new Date('2024-06-15 11:30:00')
        },
        {
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          action: 'REJECT',
          resource: 'EVIDENCE',
          resourceId: '507f1f77bcf86cd799439031',
          ipAddress: '127.0.0.1',
          details: { previousStatus: 'pending', newStatus: 'rejected', reason: 'Needs optimization' },
          success: true,
          timestamp: new Date('2024-06-25 14:20:00')
        },

        // Acciones de grupos
        {
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          action: 'CREATE',
          resource: 'GROUP',
          resourceId: '507f1f77bcf86cd799439040',
          ipAddress: '127.0.0.1',
          details: { groupName: 'Research Team Alpha', type: 'public', category: 'research' },
          success: true,
          timestamp: new Date('2024-01-15 10:00:00')
        },
        {
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          action: 'JOIN',
          resource: 'GROUP',
          resourceId: '507f1f77bcf86cd799439040',
          ipAddress: '192.168.1.100',
          details: { groupName: 'Research Team Alpha', role: 'member' },
          success: true,
          timestamp: new Date('2024-02-01 09:30:00')
        },

        // Intentos fallidos
        {
          userId: null,
          userEmail: 'hacker@malicious.com',
          action: 'LOGIN',
          resource: 'AUTH',
          ipAddress: '203.0.113.1',
          details: { method: 'brute_force', attempts: 5 },
          success: false,
          errorMessage: 'Invalid credentials - account locked',
          timestamp: new Date('2024-07-03 02:15:00')
        },
        {
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          action: 'UPLOAD',
          resource: 'FILE',
          resourceId: null,
          ipAddress: '192.168.1.100',
          details: { filename: 'large_file.mp4', size: 524288000, error: 'File too large' },
          success: false,
          errorMessage: 'File size exceeds maximum allowed (500MB)',
          timestamp: new Date('2024-07-02 16:45:00')
        }
      ]);

      // Datos de ejemplo para analytics - TRIPLICADOS
      await Analytics.bulkCreate([
        // Métricas de usuarios
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'users',
          metricName: 'total_users',
          value: 150,
          additionalData: { active: 145, inactive: 5, new_today: 3 }
        },
        {
          date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          metricType: 'users',
          metricName: 'total_users',
          value: 147,
          additionalData: { active: 142, inactive: 5, new_today: 2 }
        },
        {
          date: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          metricType: 'users',
          metricName: 'total_users',
          value: 145,
          additionalData: { active: 140, inactive: 5, new_today: 1 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'users',
          metricName: 'daily_active_users',
          value: 89,
          additionalData: { peak_hour: '10:00', avg_session_duration: 45.6 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'users',
          metricName: 'login_attempts',
          value: 234,
          additionalData: { successful: 229, failed: 5, blocked: 2 }
        },

        // Métricas de archivos
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'files',
          metricName: 'total_files',
          value: 1250,
          additionalData: { total_size_gb: 45.6, documents: 890, images: 245, videos: 78, other: 37 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'files',
          metricName: 'files_uploaded_today',
          value: 12,
          additionalData: { total_size_mb: 156.7, avg_size_mb: 13.1 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'files',
          metricName: 'files_downloaded_today',
          value: 67,
          additionalData: { unique_users: 23, total_bandwidth_gb: 2.3 }
        },
        {
          date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          metricType: 'files',
          metricName: 'files_uploaded_today',
          value: 8,
          additionalData: { total_size_mb: 98.4, avg_size_mb: 12.3 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'files',
          metricName: 'storage_usage',
          value: 45.6,
          additionalData: { limit_gb: 100, usage_percent: 45.6, growth_rate_gb_per_month: 3.2 }
        },

        // Métricas de evidencias
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'evidences',
          metricName: 'total_evidences',
          value: 89,
          additionalData: { pending: 12, under_review: 8, approved: 65, rejected: 4 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'evidences',
          metricName: 'evidences_submitted_today',
          value: 5,
          additionalData: { high_priority: 2, medium_priority: 2, low_priority: 1 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'evidences',
          metricName: 'review_performance',
          value: 2.3,
          additionalData: { avg_review_time_days: 2.3, pending_reviews: 12, overdue_reviews: 2 }
        },
        {
          date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          metricType: 'evidences',
          metricName: 'evidences_submitted_today',
          value: 3,
          additionalData: { high_priority: 1, medium_priority: 1, low_priority: 1 }
        },

        // Métricas de grupos
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'groups',
          metricName: 'total_groups',
          value: 25,
          additionalData: { public: 15, private: 7, protected: 3, active_today: 18 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'groups',
          metricName: 'group_activity',
          value: 156,
          additionalData: { messages_today: 156, files_shared: 23, new_members: 4 }
        },

        // Métricas de sistema
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'system',
          metricName: 'api_requests',
          value: 12450,
          additionalData: { successful: 12234, errors: 216, avg_response_time_ms: 245 }
        },
        {
          date: new Date().toISOString().split('T')[0],
          metricType: 'system',
          metricName: 'database_performance',
          value: 156.7,
          additionalData: { avg_query_time_ms: 156.7, slow_queries: 12, connections: 45 }
        },

        // Métricas históricas
        {
          date: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          metricType: 'users',
          metricName: 'weekly_active_users',
          value: 134,
          additionalData: { growth_rate: 5.2, retention_rate: 89.3 }
        },
        {
          date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          metricType: 'evidences',
          metricName: 'monthly_submissions',
          value: 78,
          additionalData: { approval_rate: 82.1, avg_review_time: 2.8 }
        }
      ]);

      // Datos de ejemplo para system_logs - TRIPLICADOS
      await SystemLog.bulkCreate([
        // Logs de información
        {
          level: 'info',
          message: 'Sistema iniciado correctamente',
          component: 'server',
          metadata: { port: 5002, environment: 'development', uptime: 0 },
          timestamp: new Date('2024-07-04 08:00:00')
        },
        {
          level: 'info',
          message: 'Database connections established successfully',
          component: 'database',
          metadata: { mongodb: false, mysql: true, fallback_mode: true },
          timestamp: new Date('2024-07-04 08:00:15')
        },
        {
          level: 'info',
          message: 'Authentication service initialized',
          component: 'auth',
          metadata: { jwt_enabled: true, fallback_mode: true, dev_users: 4 },
          timestamp: new Date('2024-07-04 08:00:30')
        },
        {
          level: 'info',
          message: 'File upload service ready',
          component: 'files',
          metadata: { max_size_mb: 500, allowed_types: 'pdf,jpg,png,docx,xlsx' },
          timestamp: new Date('2024-07-04 08:01:00')
        },
        {
          level: 'info',
          message: 'Daily backup completed successfully',
          component: 'backup',
          metadata: { files_backed_up: 1250, duration_minutes: 15, size_gb: 45.6 },
          timestamp: new Date('2024-07-04 03:00:00')
        },

        // Logs de advertencia
        {
          level: 'warn',
          message: 'MongoDB connection timeout, using fallback mode',
          component: 'database',
          metadata: { timeout: 5000, fallback: true, retry_attempts: 3 },
          timestamp: new Date('2024-07-04 08:00:10')
        },
        {
          level: 'warn',
          message: 'High memory usage detected',
          component: 'system',
          metadata: { memory_usage_percent: 85.2, threshold: 80, pid: 1234 },
          timestamp: new Date('2024-07-04 14:30:00')
        },
        {
          level: 'warn',
          message: 'Slow query detected in analytics endpoint',
          component: 'database',
          metadata: { query_time_ms: 2500, threshold_ms: 1000, endpoint: '/api/v1/analytics/dashboard' },
          timestamp: new Date('2024-07-04 11:45:00')
        },
        {
          level: 'warn',
          message: 'Rate limit threshold reached for IP',
          component: 'security',
          metadata: { ip_address: '192.168.1.100', requests_per_minute: 95, limit: 100 },
          timestamp: new Date('2024-07-04 16:20:00')
        },

        // Logs de error
        {
          level: 'error',
          message: 'Failed to send email notification',
          component: 'notifications',
          metadata: { recipient: 'user@test.com', error: 'SMTP timeout', retry_count: 3 },
          stackTrace: 'Error: SMTP timeout\n    at SMTPConnection.timeout\n    at ...',
          timestamp: new Date('2024-07-03 15:30:00')
        },
        {
          level: 'error',
          message: 'File upload failed - disk space insufficient',
          component: 'files',
          metadata: { filename: 'large_video.mp4', size_mb: 500, available_space_mb: 200 },
          stackTrace: 'Error: ENOSPC: no space left on device\n    at WriteStream.write\n    at ...',
          timestamp: new Date('2024-07-02 16:45:00')
        },
        {
          level: 'error',
          message: 'Authentication token validation failed',
          component: 'auth',
          metadata: { token_expired: true, user_id: '507f1f77bcf86cd799439012', endpoint: '/api/v1/files' },
          timestamp: new Date('2024-07-04 12:15:00')
        },

        // Logs de debug
        {
          level: 'debug',
          message: 'Cache hit for user profile data',
          component: 'cache',
          metadata: { user_id: '507f1f77bcf86cd799439011', cache_key: 'user_profile_admin', ttl_seconds: 300 },
          timestamp: new Date('2024-07-04 09:30:00')
        },
        {
          level: 'debug',
          message: 'WebSocket connection established',
          component: 'websocket',
          metadata: { socket_id: 'sock_123456', user_id: '507f1f77bcf86cd799439012', room: 'research_team_alpha' },
          timestamp: new Date('2024-07-04 10:15:00')
        },
        {
          level: 'debug',
          message: 'Evidence status updated via workflow',
          component: 'workflow',
          metadata: { evidence_id: '507f1f77bcf86cd799439030', old_status: 'pending', new_status: 'under_review', reviewer: 'admin@test.com' },
          timestamp: new Date('2024-07-04 13:45:00')
        }
      ]);

      // Datos de ejemplo para performance_metrics - TRIPLICADOS
      await PerformanceMetric.bulkCreate([
        // Métricas de autenticación
        {
          endpoint: '/api/v1/auth/login',
          method: 'POST',
          responseTime: 245,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439011',
          ipAddress: '127.0.0.1',
          memoryUsage: 45.2,
          cpuUsage: 12.5,
          timestamp: new Date('2024-07-04 09:00:00')
        },
        {
          endpoint: '/api/v1/auth/login',
          method: 'POST',
          responseTime: 189,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439012',
          ipAddress: '192.168.1.100',
          memoryUsage: 43.8,
          cpuUsage: 10.2,
          timestamp: new Date('2024-07-04 08:30:00')
        },
        {
          endpoint: '/api/v1/auth/login',
          method: 'POST',
          responseTime: 312,
          statusCode: 401,
          userId: null,
          ipAddress: '203.0.113.1',
          memoryUsage: 44.1,
          cpuUsage: 15.8,
          timestamp: new Date('2024-07-03 02:15:00')
        },

        // Métricas de archivos
        {
          endpoint: '/api/v1/files',
          method: 'GET',
          responseTime: 156,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439012',
          ipAddress: '192.168.1.100',
          memoryUsage: 42.3,
          cpuUsage: 8.7,
          timestamp: new Date('2024-07-04 10:15:00')
        },
        {
          endpoint: '/api/v1/files/upload',
          method: 'POST',
          responseTime: 2340,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439013',
          ipAddress: '192.168.1.101',
          memoryUsage: 67.8,
          cpuUsage: 25.4,
          timestamp: new Date('2024-07-01 14:30:00')
        },
        {
          endpoint: '/api/v1/files/507f1f77bcf86cd799439020',
          method: 'GET',
          responseTime: 89,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439011',
          ipAddress: '127.0.0.1',
          memoryUsage: 41.2,
          cpuUsage: 6.3,
          timestamp: new Date('2024-07-04 11:20:00')
        },

        // Métricas de usuarios
        {
          endpoint: '/api/v1/users/507f1f77bcf86cd799439011',
          method: 'GET',
          responseTime: 89,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439011',
          ipAddress: '127.0.0.1',
          memoryUsage: 40.5,
          cpuUsage: 5.2,
          timestamp: new Date('2024-07-04 09:30:00')
        },
        {
          endpoint: '/api/v1/users',
          method: 'GET',
          responseTime: 234,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439011',
          ipAddress: '127.0.0.1',
          memoryUsage: 46.7,
          cpuUsage: 11.8,
          timestamp: new Date('2024-07-04 15:45:00')
        },

        // Métricas de evidencias
        {
          endpoint: '/api/v1/evidences',
          method: 'GET',
          responseTime: 178,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439012',
          ipAddress: '192.168.1.100',
          memoryUsage: 43.2,
          cpuUsage: 9.1,
          timestamp: new Date('2024-07-04 14:20:00')
        },
        {
          endpoint: '/api/v1/evidences',
          method: 'POST',
          responseTime: 456,
          statusCode: 201,
          userId: '507f1f77bcf86cd799439013',
          ipAddress: '192.168.1.101',
          memoryUsage: 52.1,
          cpuUsage: 18.3,
          timestamp: new Date('2024-07-03 16:00:00')
        },

        // Métricas de analytics
        {
          endpoint: '/api/v1/analytics/dashboard',
          method: 'GET',
          responseTime: 312,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439011',
          ipAddress: '127.0.0.1',
          memoryUsage: 48.9,
          cpuUsage: 14.6,
          timestamp: new Date('2024-07-04 11:45:00')
        },
        {
          endpoint: '/api/v1/analytics/reports',
          method: 'GET',
          responseTime: 567,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439013',
          ipAddress: '192.168.1.101',
          memoryUsage: 55.3,
          cpuUsage: 22.1,
          timestamp: new Date('2024-07-04 13:30:00')
        },

        // Métricas de grupos y mensajes
        {
          endpoint: '/api/v1/groups',
          method: 'GET',
          responseTime: 134,
          statusCode: 200,
          userId: '507f1f77bcf86cd799439012',
          ipAddress: '192.168.1.100',
          memoryUsage: 41.8,
          cpuUsage: 7.4,
          timestamp: new Date('2024-07-04 12:15:00')
        },
        {
          endpoint: '/api/v1/messages',
          method: 'POST',
          responseTime: 98,
          statusCode: 201,
          userId: '507f1f77bcf86cd799439014',
          ipAddress: '192.168.1.102',
          memoryUsage: 39.7,
          cpuUsage: 6.8,
          timestamp: new Date('2024-07-04 16:30:00')
        },

        // Métricas de health check
        {
          endpoint: '/health',
          method: 'GET',
          responseTime: 23,
          statusCode: 200,
          userId: null,
          ipAddress: '127.0.0.1',
          memoryUsage: 38.2,
          cpuUsage: 3.1,
          timestamp: new Date('2024-07-04 17:00:00')
        }
      ]);

      // Datos de ejemplo para user_sessions - TRIPLICADOS
      await UserSession.bulkCreate([
        // Sesiones activas
        {
          sessionId: 'sess_admin_001',
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          ipAddress: '127.0.0.1',
          userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
          loginAt: new Date('2024-07-04 09:00:00'),
          lastActivity: new Date('2024-07-04 17:00:00'),
          expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000), // 24 horas
          status: 'active',
          refreshTokenHash: 'hash_admin_refresh_001'
        },
        {
          sessionId: 'sess_user_001',
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          ipAddress: '192.168.1.100',
          userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
          loginAt: new Date('2024-07-04 08:30:00'),
          lastActivity: new Date('2024-07-04 16:45:00'),
          expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000), // 24 horas
          status: 'active',
          refreshTokenHash: 'hash_user_refresh_001'
        },
        {
          sessionId: 'sess_analyst_001',
          userId: '507f1f77bcf86cd799439013',
          userEmail: 'analyst@test.com',
          ipAddress: '192.168.1.101',
          userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
          loginAt: new Date('2024-07-04 08:45:00'),
          lastActivity: new Date('2024-07-04 16:30:00'),
          expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000), // 24 horas
          status: 'active',
          refreshTokenHash: 'hash_analyst_refresh_001'
        },
        {
          sessionId: 'sess_investigator_001',
          userId: '507f1f77bcf86cd799439014',
          userEmail: 'investigator@test.com',
          ipAddress: '192.168.1.102',
          userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59',
          loginAt: new Date('2024-07-04 09:15:00'),
          lastActivity: new Date('2024-07-04 17:15:00'),
          expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000), // 24 horas
          status: 'active',
          refreshTokenHash: 'hash_investigator_refresh_001'
        },

        // Sesiones expiradas
        {
          sessionId: 'sess_user_expired_001',
          userId: '507f1f77bcf86cd799439012',
          userEmail: 'user@test.com',
          ipAddress: '192.168.1.100',
          userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
          loginAt: new Date('2024-07-02 14:00:00'),
          logoutAt: new Date('2024-07-02 18:30:00'),
          lastActivity: new Date('2024-07-02 18:30:00'),
          expiresAt: new Date('2024-07-03 14:00:00'),
          status: 'expired',
          refreshTokenHash: 'hash_user_expired_001'
        },
        {
          sessionId: 'sess_analyst_expired_001',
          userId: '507f1f77bcf86cd799439013',
          userEmail: 'analyst@test.com',
          ipAddress: '192.168.1.101',
          userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15',
          loginAt: new Date('2024-07-01 09:00:00'),
          logoutAt: new Date('2024-07-01 17:00:00'),
          lastActivity: new Date('2024-07-01 17:00:00'),
          expiresAt: new Date('2024-07-02 09:00:00'),
          status: 'expired',
          refreshTokenHash: 'hash_analyst_expired_001'
        },

        // Sesión terminada manualmente
        {
          sessionId: 'sess_admin_terminated_001',
          userId: '507f1f77bcf86cd799439011',
          userEmail: 'admin@test.com',
          ipAddress: '127.0.0.1',
          userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
          loginAt: new Date('2024-07-03 10:00:00'),
          logoutAt: new Date('2024-07-03 15:30:00'),
          lastActivity: new Date('2024-07-03 15:30:00'),
          expiresAt: new Date('2024-07-04 10:00:00'),
          status: 'terminated',
          refreshTokenHash: 'hash_admin_terminated_001'
        }
      ]);

      // Verificar si existen las tablas de analytics específicas y crear datos
      try {
        const { FileAnalytic, NotificationAnalytic, GroupAnalytic, EvidenceAnalytic } = require('../models/mysql');

        // Datos para file_analytics
        if (FileAnalytic) {
          await FileAnalytic.bulkCreate([
            {
              fileId: '507f1f77bcf86cd799439020',
              action: 'upload',
              userId: '507f1f77bcf86cd799439012',
              fileSize: 2048576,
              fileType: 'application/pdf',
              timestamp: new Date('2024-06-01 14:30:00')
            },
            {
              fileId: '507f1f77bcf86cd799439020',
              action: 'download',
              userId: '507f1f77bcf86cd799439013',
              fileSize: 2048576,
              fileType: 'application/pdf',
              downloadDuration: 3,
              timestamp: new Date('2024-06-02 10:15:00')
            },
            {
              fileId: '507f1f77bcf86cd799439021',
              action: 'view',
              userId: '507f1f77bcf86cd799439011',
              fileSize: 3145728,
              fileType: 'image/png',
              timestamp: new Date('2024-07-01 11:20:00')
            },
            {
              fileId: '507f1f77bcf86cd799439022',
              action: 'upload',
              userId: '507f1f77bcf86cd799439014',
              fileSize: 1536000,
              fileType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              timestamp: new Date('2024-07-03 15:45:00')
            },
            {
              fileId: '507f1f77bcf86cd799439020',
              action: 'delete',
              userId: '507f1f77bcf86cd799439011',
              fileSize: 2048576,
              fileType: 'application/pdf',
              timestamp: new Date('2024-06-15 16:00:00')
            }
          ]);
        }

        // Datos para notification_analytics
        if (NotificationAnalytic) {
          await NotificationAnalytic.bulkCreate([
            {
              notificationId: '507f1f77bcf86cd799439050',
              userId: '507f1f77bcf86cd799439012',
              type: 'evidence_approved',
              sentAt: new Date('2024-06-15 11:30:00'),
              readAt: new Date('2024-06-15 12:00:00'),
              clickedAt: new Date('2024-06-15 12:05:00'),
              deliveryStatus: 'delivered'
            },
            {
              notificationId: '507f1f77bcf86cd799439051',
              userId: '507f1f77bcf86cd799439013',
              type: 'comment_added',
              sentAt: new Date('2024-07-02 14:30:00'),
              readAt: new Date('2024-07-02 15:00:00'),
              deliveryStatus: 'delivered'
            },
            {
              notificationId: '507f1f77bcf86cd799439052',
              userId: '507f1f77bcf86cd799439011',
              type: 'system_maintenance',
              sentAt: new Date('2024-06-30 18:00:00'),
              readAt: new Date('2024-07-01 08:00:00'),
              deliveryStatus: 'delivered'
            },
            {
              notificationId: '507f1f77bcf86cd799439053',
              userId: '507f1f77bcf86cd799439014',
              type: 'group_invitation',
              sentAt: new Date('2024-03-10 10:00:00'),
              readAt: new Date('2024-03-10 10:30:00'),
              clickedAt: new Date('2024-03-10 10:35:00'),
              deliveryStatus: 'delivered'
            }
          ]);
        }

        // Datos para group_analytics
        if (GroupAnalytic) {
          await GroupAnalytic.bulkCreate([
            {
              groupId: '507f1f77bcf86cd799439040',
              action: 'create',
              userId: '507f1f77bcf86cd799439011',
              additionalData: { groupName: 'Research Team Alpha', type: 'public' },
              timestamp: new Date('2024-01-15 10:00:00')
            },
            {
              groupId: '507f1f77bcf86cd799439040',
              action: 'join',
              userId: '507f1f77bcf86cd799439012',
              additionalData: { role: 'member' },
              timestamp: new Date('2024-02-01 09:30:00')
            },
            {
              groupId: '507f1f77bcf86cd799439040',
              action: 'message',
              userId: '507f1f77bcf86cd799439013',
              additionalData: { messageType: 'text', hasAttachment: false },
              timestamp: new Date('2024-07-04 14:20:00')
            },
            {
              groupId: '507f1f77bcf86cd799439041',
              action: 'create',
              userId: '507f1f77bcf86cd799439011',
              additionalData: { groupName: 'Development Squad', type: 'private' },
              timestamp: new Date('2024-01-20 11:00:00')
            },
            {
              groupId: '507f1f77bcf86cd799439040',
              action: 'leave',
              userId: '507f1f77bcf86cd799439014',
              additionalData: { reason: 'project_completed' },
              timestamp: new Date('2024-06-30 17:00:00')
            }
          ]);
        }

        // Datos para evidence_analytics
        if (EvidenceAnalytic) {
          await EvidenceAnalytic.bulkCreate([
            {
              evidenceId: '507f1f77bcf86cd799439030',
              action: 'submit',
              userId: '507f1f77bcf86cd799439012',
              statusChange: 'draft_to_pending',
              timestamp: new Date('2024-06-01 15:00:00')
            },
            {
              evidenceId: '507f1f77bcf86cd799439030',
              action: 'approve',
              userId: '507f1f77bcf86cd799439011',
              statusChange: 'pending_to_approved',
              reviewTime: 2160, // 36 horas en minutos
              timestamp: new Date('2024-06-15 11:30:00')
            },
            {
              evidenceId: '507f1f77bcf86cd799439031',
              action: 'reject',
              userId: '507f1f77bcf86cd799439011',
              statusChange: 'pending_to_rejected',
              reviewTime: 1440, // 24 horas en minutos
              timestamp: new Date('2024-06-25 14:20:00')
            },
            {
              evidenceId: '507f1f77bcf86cd799439032',
              action: 'comment',
              userId: '507f1f77bcf86cd799439013',
              timestamp: new Date('2024-07-02 16:30:00')
            },
            {
              evidenceId: '507f1f77bcf86cd799439033',
              action: 'submit',
              userId: '507f1f77bcf86cd799439014',
              statusChange: 'draft_to_pending',
              timestamp: new Date('2024-07-04 09:00:00')
            }
          ]);
        }

      } catch (error) {
        console.log('⚠️ Algunas tablas de analytics específicas no están disponibles:', error.message);
      }

      console.log('✅ Datos de ejemplo insertados en MySQL');
    } else {
      console.log('ℹ️  MySQL ya contiene datos, omitiendo inserción de ejemplos');
    }

    console.log('✅ MySQL inicializado correctamente');
    return true;

  } catch (error) {
    console.error('❌ Error inicializando MySQL:', error.message);
    console.log('💡 Asegúrate de que XAMPP esté ejecutándose y MySQL iniciado');
    return false;
  }
};

/**
 * Verificar estado de las bases de datos
 */
const verifyDatabases = async () => {
  try {
    console.log('🔍 Verificando estado de las bases de datos...');

    const status = getDatabaseStatus();

    console.log('\n📊 Estado de las bases de datos:');
    console.log('MongoDB:', status.mongodb.connected ? '✅ Conectado' : '❌ Desconectado');
    console.log('MySQL:', status.mysql.connected ? '✅ Conectado' : '❌ Desconectado');

    if (status.mongodb.connected && status.mysql.connected) {
      console.log('\n🎉 ¡Ambas bases de datos están funcionando correctamente!');
    } else if (status.mongodb.connected) {
      console.log('\n⚠️  Solo MongoDB está disponible (modo parcial)');
    } else if (status.mysql.connected) {
      console.log('\n⚠️  Solo MySQL está disponible (modo parcial)');
    } else {
      console.log('\n❌ Ninguna base de datos está disponible');
    }

    return status;

  } catch (error) {
    console.error('❌ Error verificando bases de datos:', error);
    return null;
  }
};

/**
 * Mostrar información de credenciales de desarrollo
 */
const showDevelopmentCredentials = () => {
  console.log('\n🔑 CREDENCIALES DE DESARROLLO:');
  console.log('=====================================');
  console.log('Admin User:');
  console.log('  Email: admin@test.com');
  console.log('  Password: admin123');
  console.log('  Role: admin');
  console.log('');
  console.log('Regular User:');
  console.log('  Email: user@test.com');
  console.log('  Password: user123');
  console.log('  Role: user');
  console.log('');
  console.log('Analyst User:');
  console.log('  Email: analyst@test.com');
  console.log('  Password: analyst123');
  console.log('  Role: analyst');
  console.log('');
  console.log('Investigator User:');
  console.log('  Email: investigator@test.com');
  console.log('  Password: investigator123');
  console.log('  Role: investigator');
  console.log('=====================================');
};

/**
 * Función principal de inicialización
 */
const main = async () => {
  try {
    console.log('🚀 INICIALIZANDO EVIDENCE MANAGEMENT SYSTEM');
    console.log('===========================================\n');

    // Conectar a las bases de datos
    console.log('1. Conectando a las bases de datos...');
    const connections = await initializeDatabases();

    // Inicializar MongoDB si está disponible
    if (connections.mongodb) {
      console.log('\n2. Inicializando MongoDB...');
      await initializeMongoDB();
    } else {
      console.log('\n2. ⚠️  MongoDB no disponible, omitiendo inicialización');
    }

    // Inicializar MySQL si está disponible
    if (connections.mysql) {
      console.log('\n3. Inicializando MySQL...');
      await initializeMySQL();
    } else {
      console.log('\n3. ⚠️  MySQL no disponible, omitiendo inicialización');
    }

    // Verificar estado final
    console.log('\n4. Verificando estado final...');
    await verifyDatabases();

    // Mostrar credenciales de desarrollo
    showDevelopmentCredentials();

    console.log('\n✅ INICIALIZACIÓN COMPLETADA');
    console.log('El sistema está listo para usar!');
    console.log('Frontend: http://localhost:3000');
    console.log('Backend: http://localhost:5002');
    console.log('Health Check: http://localhost:5002/health');

  } catch (error) {
    console.error('\n❌ Error durante la inicialización:', error);
    process.exit(1);
  }
};

// Ejecutar si se llama directamente
if (require.main === module) {
  main().then(() => {
    console.log('\n🎯 Inicialización completada. Presiona Ctrl+C para salir.');
  }).catch((error) => {
    console.error('❌ Error fatal:', error);
    process.exit(1);
  });
}

module.exports = {
  initializeMongoDB,
  initializeMySQL,
  verifyDatabases,
  showDevelopmentCredentials,
  main
};
