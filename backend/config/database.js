/**
 * Configuración de Bases de Datos Híbridas
 * MongoDB Atlas + MySQL/XAMPP
 */

const mongoose = require('mongoose');
const { Sequelize } = require('sequelize');

// Configuración MongoDB Atlas
const mongoConfig = {
  uri: process.env.MONGODB_URI || 'mongodb+srv://username:password@cluster0.xxxxx.mongodb.net/evidence_management?retryWrites=true&w=majority',
  options: {
    useNewUrlParser: true,
    useUnifiedTopology: true,
    maxPoolSize: 10,
    serverSelectionTimeoutMS: 5000,
    socketTimeoutMS: 45000,
    bufferCommands: false,
    bufferMaxEntries: 0
  }
};

// Configuración MySQL/XAMPP
const mysqlConfig = {
  host: process.env.MYSQL_HOST || 'localhost',
  port: process.env.MYSQL_PORT || 3306,
  database: process.env.MYSQL_DATABASE || 'evidence_management_mysql',
  username: process.env.MYSQL_USERNAME || 'root',
  password: process.env.MYSQL_PASSWORD || '',
  dialect: 'mysql',
  pool: {
    max: parseInt(process.env.MYSQL_CONNECTION_LIMIT) || 10,
    min: 0,
    acquire: 30000,
    idle: 10000
  },
  logging: process.env.NODE_ENV === 'development' ? console.log : false
};

// Instancia de Sequelize para MySQL
const sequelize = new Sequelize(
  mysqlConfig.database,
  mysqlConfig.username,
  mysqlConfig.password,
  {
    host: mysqlConfig.host,
    port: mysqlConfig.port,
    dialect: mysqlConfig.dialect,
    pool: mysqlConfig.pool,
    logging: mysqlConfig.logging
  }
);

/**
 * Conectar a MongoDB con fallback automático
 */
const connectMongoDB = async () => {
  const mongoUris = [
    process.env.MONGODB_URI,
    process.env.MONGODB_URI_LOCAL,
    'mongodb://localhost:27017/evidence_management'
  ].filter(Boolean);

  for (let i = 0; i < mongoUris.length; i++) {
    const uri = mongoUris[i];

    try {
      // Skip if password placeholder is still present
      if (uri.includes('<db_password>')) {
        console.log('⚠️ MongoDB URI contiene placeholder de contraseña, omitiendo...');
        continue;
      }

      const isAtlas = uri.includes('mongodb+srv');
      const isLocal = uri.includes('localhost');

      console.log(`🍃 Intentando conectar a MongoDB ${isAtlas ? 'Atlas' : isLocal ? 'Local' : 'Remoto'}...`);

      await mongoose.connect(uri, {
        ...mongoConfig.options,
        serverSelectionTimeoutMS: isAtlas ? 10000 : 5000, // More time for Atlas
      });

      console.log(`✅ MongoDB conectado exitosamente (${isAtlas ? 'Atlas' : isLocal ? 'Local' : 'Remoto'})`);
      console.log('📊 Database:', mongoose.connection.db.databaseName);
      console.log('🌐 Host:', mongoose.connection.host);

      // Configurar eventos de conexión
      mongoose.connection.on('error', (error) => {
        console.error('❌ Error en MongoDB:', error);
      });

      mongoose.connection.on('disconnected', () => {
        console.log('⚠️  MongoDB desconectado');
      });

      mongoose.connection.on('reconnected', () => {
        console.log('🔄 MongoDB reconectado');
      });

      return true;

    } catch (error) {
      const isAtlas = uri.includes('mongodb+srv');
      const isLocal = uri.includes('localhost');

      console.error(`❌ Error conectando a MongoDB ${isAtlas ? 'Atlas' : isLocal ? 'Local' : 'Remoto'}:`, error.message);

      if (isAtlas && error.message.includes('authentication failed')) {
        console.log('💡 Verifica la contraseña de MongoDB Atlas en el archivo .env');
      } else if (isLocal && error.message.includes('ECONNREFUSED')) {
        console.log('💡 MongoDB local no está ejecutándose');
      }

      // Continue to next URI if this one failed
      if (i < mongoUris.length - 1) {
        console.log('🔄 Intentando siguiente opción de MongoDB...');
      }
    }
  }

  console.log('⚠️ No se pudo conectar a ninguna instancia de MongoDB');
  console.log('🔄 El sistema funcionará en modo fallback con usuarios de desarrollo');
  return false;
};

/**
 * Conectar a MySQL/XAMPP
 */
const connectMySQL = async () => {
  try {
    await sequelize.authenticate();
    console.log('✅ MySQL/XAMPP conectado exitosamente');

    // Importar y sincronizar modelos MySQL
    const { syncModels } = require('../models/mysql');

    if (process.env.NODE_ENV === 'development') {
      await syncModels(false); // false = no forzar recreación
      console.log('🔄 Modelos MySQL sincronizados');
    }

    return true;
  } catch (error) {
    console.error('❌ Error conectando a MySQL/XAMPP:', error.message);
    console.log('💡 Asegúrate de que XAMPP esté ejecutándose y MySQL iniciado');
    return false;
  }
};

/**
 * Inicializar ambas bases de datos
 */
const initializeDatabases = async () => {
  console.log('🚀 Inicializando bases de datos...');

  const mongoConnected = await connectMongoDB();
  const mysqlConnected = await connectMySQL();

  if (mongoConnected && mysqlConnected) {
    console.log('✅ Ambas bases de datos conectadas exitosamente');
  } else if (mongoConnected) {
    console.log('⚠️  Solo MongoDB Atlas disponible');
  } else if (mysqlConnected) {
    console.log('⚠️  Solo MySQL/XAMPP disponible');
  } else {
    console.log('❌ No se pudo conectar a ninguna base de datos');
    console.log('💡 El servidor continuará con datos mock para desarrollo');
  }

  return {
    mongodb: mongoConnected,
    mysql: mysqlConnected
  };
};

/**
 * Cerrar conexiones de base de datos
 */
const closeDatabases = async () => {
  try {
    await mongoose.connection.close();
    await sequelize.close();
    console.log('✅ Conexiones de base de datos cerradas');
  } catch (error) {
    console.error('❌ Error cerrando conexiones:', error);
  }
};

/**
 * Verificar estado de las conexiones
 */
const getDatabaseStatus = () => {
  return {
    mongodb: {
      connected: mongoose.connection.readyState === 1,
      state: mongoose.connection.readyState,
      host: mongoose.connection.host,
      name: mongoose.connection.name
    },
    mysql: {
      connected: sequelize.connectionManager.pool !== null,
      host: mysqlConfig.host,
      database: mysqlConfig.database
    }
  };
};

module.exports = {
  // Configuraciones
  mongoConfig,
  mysqlConfig,

  // Instancias
  mongoose,
  sequelize,

  // Funciones de conexión
  connectMongoDB,
  connectMySQL,
  initializeDatabases,
  closeDatabases,
  getDatabaseStatus
};
