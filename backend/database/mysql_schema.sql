-- =====================================================
-- EVIDENCE MANAGEMENT SYSTEM - MySQL Schema
-- Base de Datos: MySQL/XAMPP (Secundaria)
-- Propósito: Auditoría, Analytics, Logs, Sesiones
-- =====================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS evidence_management_mysql 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE evidence_management_mysql;

-- =====================================================
-- TABLA: audit_logs
-- Propósito: Registro de auditoría de todas las acciones del sistema
-- Usado por: AdminLogsView, AnalyticsView, HomeView
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(24) NOT NULL COMMENT 'ID del usuario de MongoDB',
    user_email VARCHAR(255) NOT NULL,
    action VARCHAR(100) NOT NULL COMMENT 'Acción realizada (LOGIN, CREATE, UPDATE, DELETE, etc.)',
    resource VARCHAR(100) NOT NULL COMMENT 'Recurso afectado (USER, FILE, GROUP, etc.)',
    resource_id VARCHAR(24) COMMENT 'ID del recurso afectado',
    ip_address VARCHAR(45) COMMENT 'Dirección IP del usuario',
    user_agent TEXT COMMENT 'User Agent del navegador',
    details JSON COMMENT 'Detalles adicionales de la acción',
    success BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Si la acción fue exitosa',
    error_message TEXT COMMENT 'Mensaje de error si la acción falló',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_id VARCHAR(255) COMMENT 'ID de la sesión',
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_resource (resource),
    INDEX idx_timestamp (timestamp),
    INDEX idx_success (success),
    INDEX idx_session_id (session_id)
) ENGINE=InnoDB COMMENT='Registro de auditoría del sistema';

-- =====================================================
-- TABLA: analytics
-- Propósito: Métricas y estadísticas del sistema
-- Usado por: AnalyticsView, HomeView, AdminLogsView
-- =====================================================
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL COMMENT 'Fecha de las métricas',
    metric_type VARCHAR(50) NOT NULL COMMENT 'Tipo de métrica (users, files, groups, etc.)',
    metric_name VARCHAR(100) NOT NULL COMMENT 'Nombre específico de la métrica',
    value DECIMAL(15,2) NOT NULL COMMENT 'Valor de la métrica',
    additional_data JSON COMMENT 'Datos adicionales de la métrica',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_daily_metric (date, metric_type, metric_name),
    INDEX idx_date (date),
    INDEX idx_metric_type (metric_type),
    INDEX idx_metric_name (metric_name)
) ENGINE=InnoDB COMMENT='Métricas y analytics del sistema';

-- =====================================================
-- TABLA: system_logs
-- Propósito: Logs del sistema y errores
-- Usado por: AdminLogsView, AnalyticsView
-- =====================================================
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level ENUM('error', 'warn', 'info', 'debug') NOT NULL COMMENT 'Nivel del log',
    message TEXT NOT NULL COMMENT 'Mensaje del log',
    component VARCHAR(100) COMMENT 'Componente que generó el log',
    stack_trace TEXT COMMENT 'Stack trace del error (si aplica)',
    metadata JSON COMMENT 'Metadatos adicionales',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT FALSE COMMENT 'Si el error fue resuelto',
    resolved_by VARCHAR(24) COMMENT 'ID del usuario que resolvió el error',
    resolved_at TIMESTAMP NULL COMMENT 'Fecha de resolución',
    
    INDEX idx_level (level),
    INDEX idx_component (component),
    INDEX idx_timestamp (timestamp),
    INDEX idx_resolved (resolved)
) ENGINE=InnoDB COMMENT='Logs del sistema';

-- =====================================================
-- TABLA: performance_metrics
-- Propósito: Métricas de rendimiento del sistema
-- Usado por: AdminLogsView, AnalyticsView
-- =====================================================
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL COMMENT 'Endpoint de la API',
    method ENUM('GET', 'POST', 'PUT', 'DELETE', 'PATCH') NOT NULL,
    response_time INT NOT NULL COMMENT 'Tiempo de respuesta en ms',
    status_code INT NOT NULL COMMENT 'Código de estado HTTP',
    user_id VARCHAR(24) COMMENT 'ID del usuario que hizo la petición',
    ip_address VARCHAR(45) COMMENT 'IP del cliente',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    memory_usage DECIMAL(10,2) COMMENT 'Uso de memoria en MB',
    cpu_usage DECIMAL(5,2) COMMENT 'Uso de CPU en porcentaje',
    
    INDEX idx_endpoint (endpoint),
    INDEX idx_method (method),
    INDEX idx_timestamp (timestamp),
    INDEX idx_status_code (status_code),
    INDEX idx_response_time (response_time)
) ENGINE=InnoDB COMMENT='Métricas de rendimiento';

-- =====================================================
-- TABLA: user_sessions
-- Propósito: Gestión de sesiones de usuario
-- Usado por: AdminLogsView, AnalyticsView, autenticación
-- =====================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL UNIQUE COMMENT 'ID único de la sesión',
    user_id VARCHAR(24) NOT NULL COMMENT 'ID del usuario de MongoDB',
    user_email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'expired', 'terminated') DEFAULT 'active',
    refresh_token_hash VARCHAR(255) COMMENT 'Hash del refresh token',
    expires_at TIMESTAMP NOT NULL COMMENT 'Fecha de expiración de la sesión',
    
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB COMMENT='Sesiones de usuario';

-- =====================================================
-- TABLA: file_analytics
-- Propósito: Analytics específicos de archivos
-- Usado por: FilesView, AnalyticsView, HomeView
-- =====================================================
CREATE TABLE IF NOT EXISTS file_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id VARCHAR(24) NOT NULL COMMENT 'ID del archivo de MongoDB',
    action VARCHAR(50) NOT NULL COMMENT 'Acción (upload, download, view, delete)',
    user_id VARCHAR(24) NOT NULL COMMENT 'ID del usuario',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_size BIGINT COMMENT 'Tamaño del archivo en bytes',
    file_type VARCHAR(100) COMMENT 'Tipo de archivo',
    download_duration INT COMMENT 'Duración de descarga en segundos',
    
    INDEX idx_file_id (file_id),
    INDEX idx_action (action),
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_file_type (file_type)
) ENGINE=InnoDB COMMENT='Analytics de archivos';

-- =====================================================
-- TABLA: notification_analytics
-- Propósito: Analytics de notificaciones
-- Usado por: NotificationsView, AnalyticsView
-- =====================================================
CREATE TABLE IF NOT EXISTS notification_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id VARCHAR(24) NOT NULL COMMENT 'ID de la notificación de MongoDB',
    user_id VARCHAR(24) NOT NULL COMMENT 'ID del usuario receptor',
    type VARCHAR(50) NOT NULL COMMENT 'Tipo de notificación',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL COMMENT 'Fecha de lectura',
    clicked_at TIMESTAMP NULL COMMENT 'Fecha de click',
    delivery_status ENUM('sent', 'delivered', 'failed') DEFAULT 'sent',
    
    INDEX idx_notification_id (notification_id),
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_sent_at (sent_at),
    INDEX idx_delivery_status (delivery_status)
) ENGINE=InnoDB COMMENT='Analytics de notificaciones';

-- =====================================================
-- TABLA: group_analytics
-- Propósito: Analytics de grupos
-- Usado por: GroupsView, AnalyticsView, AdminGroupsView
-- =====================================================
CREATE TABLE IF NOT EXISTS group_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(24) NOT NULL COMMENT 'ID del grupo de MongoDB',
    action VARCHAR(50) NOT NULL COMMENT 'Acción (join, leave, create, message)',
    user_id VARCHAR(24) NOT NULL COMMENT 'ID del usuario',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    additional_data JSON COMMENT 'Datos adicionales',
    
    INDEX idx_group_id (group_id),
    INDEX idx_action (action),
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB COMMENT='Analytics de grupos';

-- =====================================================
-- TABLA: evidence_analytics
-- Propósito: Analytics de evidencias
-- Usado por: EvidencesView, AnalyticsView, TasksView
-- =====================================================
CREATE TABLE IF NOT EXISTS evidence_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evidence_id VARCHAR(24) NOT NULL COMMENT 'ID de la evidencia de MongoDB',
    action VARCHAR(50) NOT NULL COMMENT 'Acción (submit, approve, reject, comment)',
    user_id VARCHAR(24) NOT NULL COMMENT 'ID del usuario',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_change VARCHAR(100) COMMENT 'Cambio de estado',
    review_time INT COMMENT 'Tiempo de revisión en minutos',
    
    INDEX idx_evidence_id (evidence_id),
    INDEX idx_action (action),
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB COMMENT='Analytics de evidencias';

-- =====================================================
-- INSERTAR DATOS DE EJEMPLO
-- =====================================================

-- Datos de ejemplo para audit_logs
INSERT INTO audit_logs (user_id, user_email, action, resource, resource_id, ip_address, details, success) VALUES
('507f1f77bcf86cd799439011', 'admin@test.com', 'LOGIN', 'AUTH', NULL, '127.0.0.1', '{"method": "email", "browser": "Chrome"}', TRUE),
('507f1f77bcf86cd799439012', 'user@test.com', 'LOGIN', 'AUTH', NULL, '127.0.0.1', '{"method": "email", "browser": "Firefox"}', TRUE),
('507f1f77bcf86cd799439011', 'admin@test.com', 'CREATE', 'USER', '507f1f77bcf86cd799439013', '127.0.0.1', '{"role": "user", "department": "IT"}', TRUE),
('507f1f77bcf86cd799439012', 'user@test.com', 'UPLOAD', 'FILE', '507f1f77bcf86cd799439014', '127.0.0.1', '{"filename": "document.pdf", "size": 1024000}', TRUE);

-- Datos de ejemplo para analytics
INSERT INTO analytics (date, metric_type, metric_name, value, additional_data) VALUES
(CURDATE(), 'users', 'total_users', 150, '{"active": 145, "inactive": 5}'),
(CURDATE(), 'users', 'new_users_today', 3, '{"source": "registration"}'),
(CURDATE(), 'files', 'total_files', 1250, '{"total_size_gb": 45.6}'),
(CURDATE(), 'files', 'files_uploaded_today', 12, '{"total_size_mb": 156.7}'),
(CURDATE(), 'groups', 'total_groups', 25, '{"public": 15, "private": 10}'),
(CURDATE(), 'evidences', 'total_evidences', 89, '{"pending": 12, "approved": 65, "rejected": 12}');

-- Datos de ejemplo para system_logs
INSERT INTO system_logs (level, message, component, metadata) VALUES
('info', 'Sistema iniciado correctamente', 'server', '{"port": 5002, "environment": "development"}'),
('warn', 'MongoDB connection timeout, using fallback mode', 'database', '{"timeout": 5000, "fallback": true}'),
('error', 'Failed to send email notification', 'notifications', '{"recipient": "user@test.com", "error": "SMTP timeout"}'),
('info', 'Daily backup completed successfully', 'backup', '{"files_backed_up": 1250, "duration_minutes": 15}');

-- Datos de ejemplo para performance_metrics
INSERT INTO performance_metrics (endpoint, method, response_time, status_code, user_id, ip_address) VALUES
('/api/v1/auth/login', 'POST', 245, 200, '507f1f77bcf86cd799439011', '127.0.0.1'),
('/api/v1/files', 'GET', 156, 200, '507f1f77bcf86cd799439012', '127.0.0.1'),
('/api/v1/users/507f1f77bcf86cd799439011', 'GET', 89, 200, '507f1f77bcf86cd799439011', '127.0.0.1'),
('/api/v1/analytics/dashboard', 'GET', 312, 200, '507f1f77bcf86cd799439011', '127.0.0.1');

-- Datos de ejemplo para user_sessions
INSERT INTO user_sessions (session_id, user_id, user_email, ip_address, user_agent, expires_at, status) VALUES
('sess_admin_001', '507f1f77bcf86cd799439011', 'admin@test.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_ADD(NOW(), INTERVAL 24 HOUR), 'active'),
('sess_user_001', '507f1f77bcf86cd799439012', 'user@test.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_ADD(NOW(), INTERVAL 24 HOUR), 'active');

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- =====================================================

DELIMITER //

-- Procedimiento para limpiar logs antiguos
CREATE PROCEDURE CleanOldLogs(IN days_to_keep INT)
BEGIN
    DECLARE cutoff_date TIMESTAMP DEFAULT DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    DELETE FROM audit_logs WHERE timestamp < cutoff_date;
    DELETE FROM system_logs WHERE timestamp < cutoff_date;
    DELETE FROM performance_metrics WHERE timestamp < cutoff_date;
    DELETE FROM user_sessions WHERE status = 'expired' AND logout_at < cutoff_date;
    
    SELECT 'Logs antiguos eliminados correctamente' AS message;
END //

-- Procedimiento para obtener estadísticas diarias
CREATE PROCEDURE GetDailyStats(IN target_date DATE)
BEGIN
    SELECT 
        'audit_logs' as table_name,
        COUNT(*) as count,
        SUM(CASE WHEN success = TRUE THEN 1 ELSE 0 END) as successful_actions,
        SUM(CASE WHEN success = FALSE THEN 1 ELSE 0 END) as failed_actions
    FROM audit_logs 
    WHERE DATE(timestamp) = target_date
    
    UNION ALL
    
    SELECT 
        'performance_metrics' as table_name,
        COUNT(*) as count,
        AVG(response_time) as avg_response_time,
        COUNT(DISTINCT user_id) as unique_users
    FROM performance_metrics 
    WHERE DATE(timestamp) = target_date;
END //

DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices compuestos para consultas comunes
CREATE INDEX idx_audit_user_action_date ON audit_logs (user_id, action, timestamp);
CREATE INDEX idx_analytics_type_date ON analytics (metric_type, date);
CREATE INDEX idx_performance_endpoint_date ON performance_metrics (endpoint, timestamp);
CREATE INDEX idx_sessions_user_status ON user_sessions (user_id, status);

-- =====================================================
-- COMENTARIOS FINALES
-- =====================================================
-- Este schema MySQL está diseñado para trabajar con:
-- 1. Sequelize ORM (backend/models/mysql/)
-- 2. 14 vistas del frontend
-- 3. Sistema híbrido con MongoDB
-- 4. Auditoría completa del sistema
-- 5. Analytics en tiempo real
-- 6. Gestión de sesiones
-- 7. Monitoreo de rendimiento
-- =====================================================
