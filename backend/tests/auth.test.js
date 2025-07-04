/**
 * =====================================================
 * AUTHENTICATION TESTS
 * Evidence Management System
 * =====================================================
 */

const request = require('supertest');
const express = require('express');
const { getDatabaseStatus } = require('../config/database');

// Create a test app instance
const app = express();

// Import and configure middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Import routes for testing
const authRoutes = require('../routes/auth');
const userRoutes = require('../routes/users');
const fileRoutes = require('../routes/files');
const groupRoutes = require('../routes/groups');
const messageRoutes = require('../routes/messages');
const evidenceRoutes = require('../routes/evidences');
const notificationRoutes = require('../routes/notifications');
const authMiddleware = require('../middleware/auth');

// Configure test routes
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/users', authMiddleware.authenticateToken, userRoutes);
app.use('/api/v1/files', authMiddleware.authenticateToken, fileRoutes);
app.use('/api/v1/groups', authMiddleware.authenticateToken, groupRoutes);
app.use('/api/v1/messages', authMiddleware.authenticateToken, messageRoutes);
app.use('/api/v1/evidences', authMiddleware.authenticateToken, evidenceRoutes);
app.use('/api/v1/notifications', authMiddleware.authenticateToken, notificationRoutes);

// Health check endpoint
app.get('/health', (req, res) => {
  const dbStatus = getDatabaseStatus();
  res.json({
    status: 'OK',
    timestamp: new Date().toISOString(),
    databases: dbStatus
  });
});

describe('Authentication Endpoints', () => {
  let authToken;
  let refreshToken;

  beforeAll(async () => {
    // Ensure server is ready
    await new Promise(resolve => setTimeout(resolve, 1000));
  });

  describe('POST /api/v1/auth/login', () => {
    test('should login with valid admin credentials', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'admin@test.com',
          password: 'admin123'
        })
        .expect(200);

      expect(response.body).toHaveProperty('message');
      expect(response.body).toHaveProperty('user');
      expect(response.body).toHaveProperty('tokens');
      expect(response.body.user.email).toBe('admin@test.com');
      expect(response.body.user.role).toBe('admin');
      expect(response.body.tokens).toHaveProperty('accessToken');
      expect(response.body.tokens).toHaveProperty('refreshToken');

      // Store tokens for subsequent tests
      authToken = response.body.tokens.accessToken;
      refreshToken = response.body.tokens.refreshToken;
    });

    test('should login with valid user credentials', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'user@test.com',
          password: 'user123'
        })
        .expect(200);

      expect(response.body.user.email).toBe('user@test.com');
      expect(response.body.user.role).toBe('user');
    });

    test('should login with valid analyst credentials', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'analyst@test.com',
          password: 'analyst123'
        })
        .expect(200);

      expect(response.body.user.email).toBe('analyst@test.com');
      expect(response.body.user.role).toBe('analyst');
    });

    test('should login with valid investigator credentials', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'investigator@test.com',
          password: 'investigator123'
        })
        .expect(200);

      expect(response.body.user.email).toBe('investigator@test.com');
      expect(response.body.user.role).toBe('investigator');
    });

    test('should reject invalid credentials', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'admin@test.com',
          password: 'wrongpassword'
        })
        .expect(401);

      expect(response.body).toHaveProperty('error');
    });

    test('should reject non-existent user', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'nonexistent@test.com',
          password: 'password123'
        })
        .expect(401);

      expect(response.body).toHaveProperty('error');
    });

    test('should reject missing email', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          password: 'admin123'
        })
        .expect(400);

      expect(response.body).toHaveProperty('error');
    });

    test('should reject missing password', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'admin@test.com'
        })
        .expect(400);

      expect(response.body).toHaveProperty('error');
    });
  });

  describe('GET /api/v1/users/:id (Protected Route)', () => {
    test('should access protected route with valid token', async () => {
      const response = await request(app)
        .get('/api/v1/users/507f1f77bcf86cd799439011')
        .set('Authorization', `Bearer ${authToken}`)
        .expect(200);

      expect(response.body).toHaveProperty('user');
      expect(response.body.user.email).toBe('admin@test.com');
    });

    test('should reject access without token', async () => {
      const response = await request(app)
        .get('/api/v1/users/507f1f77bcf86cd799439011')
        .expect(401);

      expect(response.body).toHaveProperty('error');
    });

    test('should reject access with invalid token', async () => {
      const response = await request(app)
        .get('/api/v1/users/507f1f77bcf86cd799439011')
        .set('Authorization', 'Bearer invalid-token')
        .expect(401);

      expect(response.body).toHaveProperty('error');
    });
  });

  describe('POST /api/v1/auth/refresh', () => {
    test('should refresh token with valid refresh token', async () => {
      const response = await request(app)
        .post('/api/v1/auth/refresh')
        .send({
          refreshToken: refreshToken
        })
        .expect(200);

      expect(response.body).toHaveProperty('accessToken');
      expect(response.body).toHaveProperty('refreshToken');
    });

    test('should reject invalid refresh token', async () => {
      const response = await request(app)
        .post('/api/v1/auth/refresh')
        .send({
          refreshToken: 'invalid-refresh-token'
        })
        .expect(401);

      expect(response.body).toHaveProperty('error');
    });
  });

  describe('POST /api/v1/auth/logout', () => {
    test('should logout successfully', async () => {
      const response = await request(app)
        .post('/api/v1/auth/logout')
        .set('Authorization', `Bearer ${authToken}`)
        .expect(200);

      expect(response.body).toHaveProperty('message');
    });
  });

  describe('Database Status Integration', () => {
    test('should work in both MongoDB and fallback modes', async () => {
      const dbStatus = getDatabaseStatus();

      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'admin@test.com',
          password: 'admin123'
        })
        .expect(200);

      expect(response.body).toHaveProperty('user');

      if (dbStatus.mongodb.connected) {
        expect(response.body.mode).toBeUndefined(); // Real MongoDB mode
      } else {
        expect(response.body.mode).toBe('development'); // Fallback mode
      }
    });
  });
});

describe('Health Check', () => {
  test('should return system health status', async () => {
    const response = await request(app)
      .get('/health')
      .expect(200);

    expect(response.body).toHaveProperty('status');
    expect(response.body).toHaveProperty('timestamp');
    expect(response.body).toHaveProperty('databases');
    expect(response.body.databases).toHaveProperty('mongodb');
    expect(response.body.databases).toHaveProperty('mysql');
  });
});

describe('API Endpoints Fallback', () => {
  let testAuthToken;

  beforeAll(async () => {
    // Get auth token for testing
    const loginResponse = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'admin@test.com',
        password: 'admin123'
      });

    testAuthToken = loginResponse.body.tokens.accessToken;
  });

  test('Files endpoint should work in fallback mode', async () => {
    const response = await request(app)
      .get('/api/v1/files')
      .set('Authorization', `Bearer ${testAuthToken}`)
      .expect(200);

    expect(response.body).toHaveProperty('files');
    expect(response.body).toHaveProperty('pagination');
    expect(Array.isArray(response.body.files)).toBe(true);
  });

  test('Groups endpoint should work in fallback mode', async () => {
    const response = await request(app)
      .get('/api/v1/groups')
      .set('Authorization', `Bearer ${testAuthToken}`)
      .expect(200);

    expect(response.body).toHaveProperty('groups');
    expect(response.body).toHaveProperty('pagination');
    expect(Array.isArray(response.body.groups)).toBe(true);
  });

  test('Messages endpoint should work in fallback mode', async () => {
    const response = await request(app)
      .get('/api/v1/messages')
      .set('Authorization', `Bearer ${testAuthToken}`)
      .expect(200);

    expect(response.body).toHaveProperty('messages');
    expect(response.body).toHaveProperty('pagination');
    expect(Array.isArray(response.body.messages)).toBe(true);
  });

  test('Evidences endpoint should work in fallback mode', async () => {
    const response = await request(app)
      .get('/api/v1/evidences')
      .set('Authorization', `Bearer ${testAuthToken}`)
      .expect(200);

    expect(response.body).toHaveProperty('evidences');
    expect(response.body).toHaveProperty('pagination');
    expect(Array.isArray(response.body.evidences)).toBe(true);
  });

  test('Notifications endpoint should work in fallback mode', async () => {
    const response = await request(app)
      .get('/api/v1/notifications')
      .set('Authorization', `Bearer ${testAuthToken}`)
      .expect(200);

    expect(response.body).toHaveProperty('notifications');
    expect(response.body).toHaveProperty('pagination');
    expect(Array.isArray(response.body.notifications)).toBe(true);
  });
});
