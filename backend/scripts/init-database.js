/**
 * Script de Inicialización de Base de Datos
 * Sistema de Gestión de Evidencias
 */

require('dotenv').config();
const { initializeDatabases, getDatabaseStatus } = require('../config/database');

/**
 * Crear usuario administrador por defecto
 */
const createDefaultAdmin = async () => {
  try {
    const User = require('../models/User');
    
    // Verificar si ya existe un admin
    const existingAdmin = await User.findOne({ role: 'admin' });
    if (existingAdmin) {
      console.log('✅ Usuario administrador ya existe:', existingAdmin.email);
      return existingAdmin;
    }
    
    // Crear usuario admin por defecto
    const adminUser = new User({
      email: 'admin@company.com',
      password: 'admin123',
      firstName: 'Admin',
      lastName: 'User',
      role: 'admin',
      department: 'IT',
      position: 'System Administrator',
      isActive: true
    });
    
    await adminUser.save();
    console.log('✅ Usuario administrador creado:', adminUser.email);
    return adminUser;
    
  } catch (error) {
    console.error('❌ Error creando usuario administrador:', error);
    return null;
  }
};

/**
 * Crear usuarios de ejemplo
 */
const createSampleUsers = async () => {
  try {
    const User = require('../models/User');
    
    const sampleUsers = [
      {
        email: 'user@company.com',
        password: 'user123',
        firstName: 'Regular',
        lastName: 'User',
        role: 'user',
        department: 'Operations',
        position: 'Analyst'
      },
      {
        email: 'dr.smith@company.com',
        password: 'smith123',
        firstName: 'Dr. John',
        lastName: 'Smith',
        role: 'investigator',
        department: 'Forensics',
        position: 'Lead Investigator'
      }
    ];
    
    for (const userData of sampleUsers) {
      const existingUser = await User.findOne({ email: userData.email });
      if (!existingUser) {
        const user = new User(userData);
        await user.save();
        console.log('✅ Usuario de ejemplo creado:', user.email);
      } else {
        console.log('⚠️  Usuario ya existe:', userData.email);
      }
    }
    
  } catch (error) {
    console.error('❌ Error creando usuarios de ejemplo:', error);
  }
};

/**
 * Insertar datos de ejemplo en MySQL
 */
const insertSampleMySQLData = async () => {
  try {
    if (!global.mysqlConnected) {
      console.log('⚠️  MySQL no conectado, saltando datos de ejemplo');
      return;
    }
    
    const { AuditLog, Analytics, SystemLog } = require('../models/mysql');
    
    // Datos de ejemplo para audit logs
    const sampleAuditLogs = [
      {
        userId: 'admin_user_id',
        userEmail: 'admin@company.com',
        action: 'login',
        resource: 'user',
        resourceId: 'admin_user_id',
        details: { login_method: 'email' },
        ipAddress: '127.0.0.1',
        success: true
      },
      {
        userId: 'user_user_id',
        userEmail: 'user@company.com',
        action: 'file_upload',
        resource: 'file',
        resourceId: 'file_123',
        details: { filename: 'document.pdf', size: 1024000 },
        ipAddress: '127.0.0.1',
        success: true
      }
    ];
    
    for (const logData of sampleAuditLogs) {
      await AuditLog.logAction(logData);
    }
    
    // Datos de ejemplo para analytics
    const today = new Date().toISOString().split('T')[0];
    const currentHour = new Date().getHours();
    
    const sampleAnalytics = [
      {
        type: 'user_login',
        userId: 'admin_user_id',
        ipAddress: '127.0.0.1'
      },
      {
        type: 'page_view',
        count: 5,
        userId: 'user_user_id',
        ipAddress: '127.0.0.1'
      },
      {
        type: 'file_upload',
        value: 1024000,
        userId: 'user_user_id',
        ipAddress: '127.0.0.1'
      }
    ];
    
    for (const analyticsData of sampleAnalytics) {
      await Analytics.recordMetric(analyticsData);
    }
    
    // Log de sistema de ejemplo
    await SystemLog.logInfo('Sistema inicializado correctamente', {
      component: 'init-script',
      functionName: 'insertSampleMySQLData'
    });
    
    console.log('✅ Datos de ejemplo insertados en MySQL');
    
  } catch (error) {
    console.error('❌ Error insertando datos de ejemplo en MySQL:', error);
  }
};

/**
 * Verificar estado del sistema
 */
const verifySystemStatus = async () => {
  try {
    console.log('\n📊 VERIFICANDO ESTADO DEL SISTEMA...\n');
    
    // Estado de bases de datos
    const dbStatus = getDatabaseStatus();
    console.log('🗄️  Estado de Bases de Datos:');
    console.log(`   MongoDB: ${dbStatus.mongodb.connected ? '✅ Conectado' : '❌ Desconectado'}`);
    console.log(`   MySQL:   ${dbStatus.mysql.connected ? '✅ Conectado' : '❌ Desconectado'}`);
    
    // Contar usuarios en MongoDB
    if (dbStatus.mongodb.connected) {
      const User = require('../models/User');
      const userCount = await User.countDocuments();
      const adminCount = await User.countDocuments({ role: 'admin' });
      console.log(`\n👥 Usuarios en MongoDB: ${userCount} (${adminCount} admins)`);
    }
    
    // Estadísticas de MySQL
    if (dbStatus.mysql.connected) {
      const { getGeneralStats } = require('../models/mysql');
      const stats = await getGeneralStats();
      
      console.log('\n📈 Estadísticas MySQL:');
      console.log(`   Audit Logs: ${stats.auditLogs.total}`);
      console.log(`   Analytics: ${stats.analytics.total}`);
      console.log(`   System Logs: ${stats.systemLogs.total}`);
      console.log(`   Sesiones: ${stats.sessions.total} (${stats.sessions.active} activas)`);
    }
    
    console.log('\n✅ Verificación completada\n');
    
  } catch (error) {
    console.error('❌ Error verificando estado del sistema:', error);
  }
};

/**
 * Función principal de inicialización
 */
const initializeSystem = async () => {
  console.log('🚀 INICIANDO CONFIGURACIÓN DEL SISTEMA...\n');
  
  try {
    // 1. Inicializar bases de datos
    console.log('1️⃣  Inicializando bases de datos...');
    const dbStatus = await initializeDatabases();
    global.mongoConnected = dbStatus.mongodb;
    global.mysqlConnected = dbStatus.mysql;
    
    // 2. Crear usuarios por defecto (solo si MongoDB está conectado)
    if (dbStatus.mongodb) {
      console.log('\n2️⃣  Creando usuarios por defecto...');
      await createDefaultAdmin();
      await createSampleUsers();
    } else {
      console.log('\n⚠️  MongoDB no conectado, saltando creación de usuarios');
    }
    
    // 3. Insertar datos de ejemplo en MySQL
    if (dbStatus.mysql) {
      console.log('\n3️⃣  Insertando datos de ejemplo en MySQL...');
      await insertSampleMySQLData();
    } else {
      console.log('\n⚠️  MySQL no conectado, saltando datos de ejemplo');
    }
    
    // 4. Verificar estado final
    await verifySystemStatus();
    
    console.log('🎉 INICIALIZACIÓN COMPLETADA EXITOSAMENTE!\n');
    
    // Mostrar información de acceso
    console.log('📋 INFORMACIÓN DE ACCESO:');
    console.log('   Frontend: http://localhost:3000');
    console.log('   Backend API: http://localhost:5001/api/v1');
    console.log('   Health Check: http://localhost:5001/health');
    console.log('   Database Status: http://localhost:5001/api/v1/database/status');
    console.log('\n🔑 CREDENCIALES DE PRUEBA:');
    console.log('   Admin: admin@company.com / admin123');
    console.log('   User: user@company.com / user123');
    console.log('   Investigator: dr.smith@company.com / smith123\n');
    
  } catch (error) {
    console.error('❌ Error durante la inicialización:', error);
    process.exit(1);
  }
};

// Ejecutar si se llama directamente
if (require.main === module) {
  initializeSystem()
    .then(() => {
      console.log('✅ Script de inicialización completado');
      process.exit(0);
    })
    .catch((error) => {
      console.error('❌ Error en script de inicialización:', error);
      process.exit(1);
    });
}

module.exports = {
  initializeSystem,
  createDefaultAdmin,
  createSampleUsers,
  insertSampleMySQLData,
  verifySystemStatus
};
