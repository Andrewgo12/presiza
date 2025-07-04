-- =====================================================
-- SCRIPT SQL PARA MYSQL/XAMPP
-- Sistema de Gestión de Evidencias
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS evidence_management_mysql 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE evidence_management_mysql;

-- =====================================================
-- TABLA: audit_logs
-- Para auditoría y seguimiento de acciones
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Usuario que realizó la acción
    userId VARCHAR(255) NULL COMMENT 'ID del usuario de MongoDB',
    userEmail VARCHAR(255) NULL,
    
    -- Información de la acción
    action VARCHAR(100) NOT NULL COMMENT 'Tipo de acción realizada',
    resource VARCHAR(100) NOT NULL COMMENT 'Recurso afectado (file, user, group, etc.)',
    resourceId VARCHAR(255) NULL COMMENT 'ID del recurso afectado',
    
    -- Detalles de la acción
    details JSON NULL COMMENT 'Detalles adicionales de la acción',
    
    -- Información de la sesión
    ipAddress VARCHAR(45) NULL,
    userAgent TEXT NULL,
    sessionId VARCHAR(255) NULL,
    
    -- Resultado de la acción
    success BOOLEAN DEFAULT TRUE,
    errorMessage TEXT NULL,
    
    -- Timestamps
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_userId (userId),
    INDEX idx_action (action),
    INDEX idx_resource (resource),
    INDEX idx_timestamp (timestamp),
    INDEX idx_ipAddress (ipAddress),
    INDEX idx_composite (timestamp, action, resource)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: analytics
-- Para métricas y estadísticas del sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información temporal
    date DATE NOT NULL COMMENT 'Fecha de la métrica (YYYY-MM-DD)',
    hour INT NULL CHECK (hour >= 0 AND hour <= 23) COMMENT 'Hora del día (0-23)',
    
    -- Tipo de métrica
    metricType ENUM(
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
    ) NOT NULL,
    
    -- Valores de la métrica
    count INT DEFAULT 1 COMMENT 'Número de ocurrencias',
    value DECIMAL(10,2) NULL COMMENT 'Valor numérico asociado',
    
    -- Información adicional
    userId VARCHAR(255) NULL COMMENT 'ID del usuario de MongoDB',
    resourceId VARCHAR(255) NULL COMMENT 'ID del recurso relacionado',
    metadata JSON NULL COMMENT 'Metadatos adicionales',
    
    -- Información de contexto
    ipAddress VARCHAR(45) NULL,
    userAgent TEXT NULL,
    
    -- Timestamps
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_date (date),
    INDEX idx_metricType (metricType),
    INDEX idx_userId (userId),
    INDEX idx_date_metric (date, metricType),
    INDEX idx_date_hour (date, hour),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: system_logs
-- Para logs del sistema y errores
-- =====================================================
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información del log
    level ENUM('error', 'warn', 'info', 'debug') NOT NULL DEFAULT 'info',
    message TEXT NOT NULL,
    
    -- Contexto del error
    component VARCHAR(100) NULL COMMENT 'Componente que generó el log',
    function_name VARCHAR(100) NULL COMMENT 'Función donde ocurrió',
    
    -- Información técnica
    stack_trace TEXT NULL,
    error_code VARCHAR(50) NULL,
    
    -- Request info (si aplica)
    request_id VARCHAR(255) NULL,
    user_id VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    -- Metadata adicional
    metadata JSON NULL,
    
    -- Timestamps
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_level (level),
    INDEX idx_timestamp (timestamp),
    INDEX idx_component (component),
    INDEX idx_user_id (user_id),
    INDEX idx_level_timestamp (level, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: performance_metrics
-- Para métricas de rendimiento del sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información de la métrica
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,4) NOT NULL,
    unit VARCHAR(20) NULL COMMENT 'ms, seconds, bytes, etc.',
    
    -- Contexto
    endpoint VARCHAR(255) NULL,
    method VARCHAR(10) NULL,
    status_code INT NULL,
    
    -- Timing
    response_time DECIMAL(10,4) NULL COMMENT 'Tiempo de respuesta en ms',
    cpu_usage DECIMAL(5,2) NULL COMMENT 'Uso de CPU en %',
    memory_usage BIGINT NULL COMMENT 'Uso de memoria en bytes',
    
    -- Request info
    user_id VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    
    -- Timestamps
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_metric_name (metric_name),
    INDEX idx_timestamp (timestamp),
    INDEX idx_endpoint (endpoint),
    INDEX idx_response_time (response_time),
    INDEX idx_composite (metric_name, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: report_cache
-- Para cache de reportes generados
-- =====================================================
CREATE TABLE IF NOT EXISTS report_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información del reporte
    report_type VARCHAR(100) NOT NULL,
    report_name VARCHAR(255) NOT NULL,
    
    -- Parámetros del reporte
    parameters JSON NULL COMMENT 'Parámetros usados para generar el reporte',
    filters JSON NULL COMMENT 'Filtros aplicados',
    
    -- Datos del reporte
    data LONGTEXT NOT NULL COMMENT 'Datos del reporte en JSON',
    format ENUM('json', 'csv', 'pdf', 'excel') DEFAULT 'json',
    
    -- Metadata
    generated_by VARCHAR(255) NULL COMMENT 'Usuario que generó el reporte',
    file_size INT NULL COMMENT 'Tamaño en bytes',
    row_count INT NULL COMMENT 'Número de filas/registros',
    
    -- Cache info
    cache_key VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NULL,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_report_type (report_type),
    INDEX idx_cache_key (cache_key),
    INDEX idx_expires_at (expires_at),
    INDEX idx_generated_by (generated_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: user_sessions
-- Para tracking de sesiones de usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información de la sesión
    session_id VARCHAR(255) UNIQUE NOT NULL,
    user_id VARCHAR(255) NOT NULL COMMENT 'ID del usuario de MongoDB',
    
    -- Información del dispositivo/navegador
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    device_type ENUM('desktop', 'mobile', 'tablet', 'unknown') DEFAULT 'unknown',
    browser VARCHAR(100) NULL,
    os VARCHAR(100) NULL,
    
    -- Estado de la sesión
    status ENUM('active', 'expired', 'terminated') DEFAULT 'active',
    
    -- Timestamps
    login_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    logout_at DATETIME NULL,
    expires_at DATETIME NULL,
    
    -- Índices
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_last_activity (last_activity),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTAR DATOS DE EJEMPLO (OPCIONAL)
-- =====================================================

-- Ejemplo de audit log
INSERT INTO audit_logs (userId, userEmail, action, resource, resourceId, details, ipAddress, success) VALUES
('admin_user_id', 'admin@company.com', 'login', 'user', 'admin_user_id', '{"login_method": "email"}', '127.0.0.1', TRUE),
('user_user_id', 'user@company.com', 'file_upload', 'file', 'file_123', '{"filename": "document.pdf", "size": 1024000}', '127.0.0.1', TRUE);

-- Ejemplo de analytics
INSERT INTO analytics (date, hour, metricType, count, userId, ipAddress) VALUES
(CURDATE(), HOUR(NOW()), 'user_login', 1, 'admin_user_id', '127.0.0.1'),
(CURDATE(), HOUR(NOW()), 'page_view', 5, 'user_user_id', '127.0.0.1');

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- =====================================================

DELIMITER //

-- Procedimiento para limpiar logs antiguos
CREATE PROCEDURE CleanOldLogs(IN days_to_keep INT)
BEGIN
    DELETE FROM audit_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM system_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM performance_metrics WHERE timestamp < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
END //

-- Procedimiento para obtener estadísticas diarias
CREATE PROCEDURE GetDailyStats(IN target_date DATE)
BEGIN
    SELECT 
        metricType,
        SUM(count) as total_count,
        AVG(value) as avg_value,
        COUNT(*) as records
    FROM analytics 
    WHERE date = target_date 
    GROUP BY metricType
    ORDER BY total_count DESC;
END //

DELIMITER ;

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista para actividad reciente
CREATE VIEW recent_activity AS
SELECT 
    al.timestamp,
    al.action,
    al.resource,
    al.userEmail,
    al.ipAddress,
    al.success
FROM audit_logs al
WHERE al.timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY al.timestamp DESC;

-- Vista para métricas del día actual
CREATE VIEW today_metrics AS
SELECT 
    metricType,
    SUM(count) as total_count,
    AVG(value) as avg_value
FROM analytics 
WHERE date = CURDATE()
GROUP BY metricType;

-- =====================================================
-- CONFIGURACIÓN FINAL
-- =====================================================

-- Configurar zona horaria
SET time_zone = '+00:00';

-- Mostrar resumen de tablas creadas
SELECT 
    TABLE_NAME as 'Tabla Creada',
    TABLE_ROWS as 'Filas',
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as 'Tamaño (MB)'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'evidence_management_mysql'
ORDER BY TABLE_NAME;

-- =====================================================
-- SCRIPT COMPLETADO
-- =====================================================
-- Para ejecutar este script:
-- 1. Abre phpMyAdmin o MySQL Workbench
-- 2. Copia y pega este script completo
-- 3. Ejecuta el script
-- 4. Verifica que todas las tablas se crearon correctamente
-- =====================================================
