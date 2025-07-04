/**
 * =====================================================
 * TEST SETUP
 * Evidence Management System
 * =====================================================
 */

// Set test environment
process.env.NODE_ENV = 'test';
process.env.JWT_SECRET = 'test-jwt-secret-key-for-testing-only';
process.env.JWT_REFRESH_SECRET = 'test-jwt-refresh-secret-key-for-testing-only';

// Increase timeout for async operations
jest.setTimeout(30000);

// Global test setup
beforeAll(async () => {
  console.log('🧪 Setting up test environment...');
  
  // Initialize databases if needed
  try {
    const { initializeDatabases } = require('../config/database');
    await initializeDatabases();
    console.log('✅ Test databases initialized');
  } catch (error) {
    console.log('⚠️ Database initialization skipped:', error.message);
  }
});

// Global test cleanup
afterAll(async () => {
  console.log('🧹 Cleaning up test environment...');
  
  // Close database connections
  try {
    const mongoose = require('mongoose');
    if (mongoose.connection.readyState !== 0) {
      await mongoose.connection.close();
    }
    
    const { sequelize } = require('../config/database');
    if (sequelize) {
      await sequelize.close();
    }
    
    console.log('✅ Database connections closed');
  } catch (error) {
    console.log('⚠️ Error closing connections:', error.message);
  }
});

// Global error handler for unhandled promises
process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

// Suppress console.log during tests (optional)
if (process.env.SUPPRESS_TEST_LOGS === 'true') {
  global.console = {
    ...console,
    log: jest.fn(),
    debug: jest.fn(),
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn(),
  };
}
