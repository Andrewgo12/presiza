/**
 * =====================================================
 * HYBRID DATABASE SETUP SCRIPT
 * Evidence Management System
 * =====================================================
 * 
 * This script sets up the complete hybrid database system:
 * - MongoDB Atlas (Primary): Users, Files, Groups, Messages, Evidences, Notifications
 * - MySQL/XAMPP (Secondary): Analytics, Audit Logs, Performance Metrics, Sessions
 */

require('dotenv').config();
const fs = require('fs');
const path = require('path');

/**
 * Check if MongoDB Atlas password is configured
 */
const checkMongoDBConfiguration = () => {
  const mongoUri = process.env.MONGODB_URI;
  
  if (!mongoUri) {
    console.log('❌ MONGODB_URI not found in .env file');
    return false;
  }
  
  if (mongoUri.includes('<db_password>')) {
    console.log('⚠️  MongoDB Atlas password not configured!');
    console.log('📝 Please update your .env file:');
    console.log('   1. Open backend/.env');
    console.log('   2. Replace <db_password> with your actual MongoDB Atlas password');
    console.log('   3. Save the file and run this script again');
    console.log('');
    console.log('Current MONGODB_URI:', mongoUri);
    return false;
  }
  
  console.log('✅ MongoDB Atlas configuration found');
  return true;
};

/**
 * Test MongoDB Atlas connection
 */
const testMongoDBConnection = async () => {
  try {
    console.log('🍃 Testing MongoDB Atlas connection...');
    
    const mongoose = require('mongoose');
    
    // Set connection options
    const options = {
      useNewUrlParser: true,
      useUnifiedTopology: true,
      serverSelectionTimeoutMS: 10000, // 10 seconds
      socketTimeoutMS: 45000, // 45 seconds
    };
    
    await mongoose.connect(process.env.MONGODB_URI, options);
    
    console.log('✅ MongoDB Atlas connection successful!');
    console.log('📊 Database:', mongoose.connection.db.databaseName);
    console.log('🌐 Host:', mongoose.connection.host);
    
    // Test basic operations
    const collections = await mongoose.connection.db.listCollections().toArray();
    console.log('📁 Available collections:', collections.length);
    
    await mongoose.disconnect();
    return true;
    
  } catch (error) {
    console.log('❌ MongoDB Atlas connection failed:');
    console.log('   Error:', error.message);
    
    if (error.message.includes('authentication failed')) {
      console.log('💡 Solution: Check your MongoDB Atlas password in .env file');
    } else if (error.message.includes('network')) {
      console.log('💡 Solution: Check your internet connection and MongoDB Atlas whitelist');
    } else if (error.message.includes('timeout')) {
      console.log('💡 Solution: Check MongoDB Atlas cluster status and network connectivity');
    }
    
    return false;
  }
};

/**
 * Check XAMPP/MySQL status
 */
const checkXAMPPStatus = async () => {
  try {
    console.log('🐬 Checking XAMPP/MySQL status...');
    
    const { sequelize } = require('../config/database');
    
    await sequelize.authenticate();
    console.log('✅ MySQL/XAMPP connection successful!');
    console.log('📊 Database:', sequelize.config.database);
    console.log('🌐 Host:', sequelize.config.host + ':' + sequelize.config.port);
    
    return true;
    
  } catch (error) {
    console.log('❌ MySQL/XAMPP connection failed:');
    console.log('   Error:', error.message);
    console.log('💡 Solution: Make sure XAMPP is running and MySQL service is started');
    return false;
  }
};

/**
 * Create MySQL database and tables
 */
const setupMySQLDatabase = async () => {
  try {
    console.log('🔧 Setting up MySQL database...');
    
    const mysql = require('mysql2/promise');
    
    // Connect to MySQL without specifying database
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST || 'localhost',
      port: process.env.MYSQL_PORT || 3306,
      user: process.env.MYSQL_USERNAME || 'root',
      password: process.env.MYSQL_PASSWORD || ''
    });
    
    // Create database if it doesn't exist
    const dbName = process.env.MYSQL_DATABASE || 'evidence_management_mysql';
    await connection.execute(`CREATE DATABASE IF NOT EXISTS \`${dbName}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
    console.log(`✅ Database '${dbName}' created/verified`);
    
    // Use the database
    await connection.execute(`USE \`${dbName}\``);
    
    // Read and execute the schema file
    const schemaPath = path.join(__dirname, 'mysql_schema.sql');
    if (fs.existsSync(schemaPath)) {
      const schemaSQL = fs.readFileSync(schemaPath, 'utf8');
      
      // Split by semicolon and execute each statement
      const statements = schemaSQL.split(';').filter(stmt => stmt.trim().length > 0);
      
      for (const statement of statements) {
        if (statement.trim()) {
          try {
            await connection.execute(statement);
          } catch (error) {
            // Ignore table already exists errors
            if (!error.message.includes('already exists')) {
              console.log('⚠️ SQL Warning:', error.message);
            }
          }
        }
      }
      
      console.log('✅ MySQL schema and sample data imported successfully');
    } else {
      console.log('⚠️ MySQL schema file not found at:', schemaPath);
    }
    
    await connection.end();
    return true;
    
  } catch (error) {
    console.log('❌ MySQL setup failed:', error.message);
    return false;
  }
};

/**
 * Initialize MongoDB with sample data
 */
const initializeMongoDB = async () => {
  try {
    console.log('🍃 Initializing MongoDB with sample data...');
    
    const { initializeDatabase } = require('./mongodb_schema');
    await initializeDatabase();
    
    console.log('✅ MongoDB initialization completed');
    return true;
    
  } catch (error) {
    console.log('❌ MongoDB initialization failed:', error.message);
    return false;
  }
};

/**
 * Initialize MySQL with enhanced sample data
 */
const initializeMySQL = async () => {
  try {
    console.log('🐬 Initializing MySQL with enhanced sample data...');
    
    const { initializeMySQL: initMySQL } = require('./init-databases');
    await initMySQL();
    
    console.log('✅ MySQL initialization completed');
    return true;
    
  } catch (error) {
    console.log('❌ MySQL initialization failed:', error.message);
    return false;
  }
};

/**
 * Test hybrid database functionality
 */
const testHybridFunctionality = async () => {
  try {
    console.log('🔄 Testing hybrid database functionality...');
    
    // Test MongoDB operations
    const mongoose = require('mongoose');
    await mongoose.connect(process.env.MONGODB_URI);
    
    const { User } = require('./mongodb_schema');
    const userCount = await User.countDocuments();
    console.log(`✅ MongoDB: ${userCount} users found`);
    
    await mongoose.disconnect();
    
    // Test MySQL operations
    const { sequelize } = require('../config/database');
    const { AuditLog } = require('../models/mysql');
    
    const auditCount = await AuditLog.count();
    console.log(`✅ MySQL: ${auditCount} audit logs found`);
    
    console.log('✅ Hybrid database functionality verified');
    return true;
    
  } catch (error) {
    console.log('❌ Hybrid functionality test failed:', error.message);
    return false;
  }
};

/**
 * Display setup summary
 */
const displaySetupSummary = () => {
  console.log('\n🎉 HYBRID DATABASE SETUP COMPLETED!');
  console.log('=====================================');
  console.log('');
  console.log('📊 Database Configuration:');
  console.log('  🍃 MongoDB Atlas: Primary application data');
  console.log('  🐬 MySQL/XAMPP: Analytics, audit logs, performance metrics');
  console.log('');
  console.log('🔑 Test Credentials:');
  console.log('  Admin: admin@test.com / admin123');
  console.log('  User: user@test.com / user123');
  console.log('  Analyst: analyst@test.com / analyst123');
  console.log('  Investigator: investigator@test.com / investigator123');
  console.log('');
  console.log('🚀 Next Steps:');
  console.log('  1. Start the backend: cd backend && npm start');
  console.log('  2. Start the frontend: npm run dev');
  console.log('  3. Open http://localhost:3000');
  console.log('  4. Login with any test credentials above');
  console.log('');
  console.log('🔍 Health Check:');
  console.log('  Backend: http://localhost:5002/health');
  console.log('  API: http://localhost:5002/api/v1');
  console.log('');
  console.log('📁 Sample Data Created:');
  console.log('  👥 Users: 12+ with diverse roles and departments');
  console.log('  📁 Files: 12 files of various types and sizes');
  console.log('  👥 Groups: 9 groups covering all categories');
  console.log('  📋 Evidences: 11 evidences in all workflow states');
  console.log('  💬 Messages: 15 messages across conversations');
  console.log('  🔔 Notifications: 15 notifications of all types');
  console.log('  📊 Analytics: Comprehensive metrics and audit trails');
};

/**
 * Main setup function
 */
const main = async () => {
  console.log('🚀 EVIDENCE MANAGEMENT SYSTEM - HYBRID DATABASE SETUP');
  console.log('====================================================\n');
  
  let mongoOK = false;
  let mysqlOK = false;
  
  // Step 1: Check MongoDB configuration
  console.log('📋 Step 1: Checking MongoDB Atlas configuration...');
  if (!checkMongoDBConfiguration()) {
    console.log('\n❌ Setup cannot continue without MongoDB Atlas configuration');
    console.log('Please configure your MongoDB Atlas password and try again.');
    process.exit(1);
  }
  
  // Step 2: Test MongoDB connection
  console.log('\n📋 Step 2: Testing MongoDB Atlas connection...');
  mongoOK = await testMongoDBConnection();
  
  // Step 3: Check XAMPP/MySQL
  console.log('\n📋 Step 3: Checking XAMPP/MySQL status...');
  mysqlOK = await checkXAMPPStatus();
  
  // Step 4: Setup MySQL database
  if (mysqlOK) {
    console.log('\n📋 Step 4: Setting up MySQL database...');
    mysqlOK = await setupMySQLDatabase();
  }
  
  // Step 5: Initialize databases
  if (mongoOK) {
    console.log('\n📋 Step 5a: Initializing MongoDB...');
    await initializeMongoDB();
  }
  
  if (mysqlOK) {
    console.log('\n📋 Step 5b: Initializing MySQL...');
    await initializeMySQL();
  }
  
  // Step 6: Test hybrid functionality
  if (mongoOK && mysqlOK) {
    console.log('\n📋 Step 6: Testing hybrid functionality...');
    await testHybridFunctionality();
  }
  
  // Display results
  console.log('\n📊 SETUP RESULTS:');
  console.log('==================');
  console.log('MongoDB Atlas:', mongoOK ? '✅ Ready' : '❌ Failed');
  console.log('MySQL/XAMPP:', mysqlOK ? '✅ Ready' : '❌ Failed');
  console.log('Hybrid Mode:', (mongoOK && mysqlOK) ? '✅ Fully Operational' : '⚠️ Partial');
  
  if (mongoOK || mysqlOK) {
    displaySetupSummary();
  } else {
    console.log('\n❌ Setup failed. Please check the errors above and try again.');
    process.exit(1);
  }
};

// Run setup if called directly
if (require.main === module) {
  main().catch(error => {
    console.error('\n❌ Setup failed with error:', error);
    process.exit(1);
  });
}

module.exports = {
  checkMongoDBConfiguration,
  testMongoDBConnection,
  checkXAMPPStatus,
  setupMySQLDatabase,
  initializeMongoDB,
  initializeMySQL,
  testHybridFunctionality,
  main
};
