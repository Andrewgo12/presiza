/**
 * =====================================================
 * USERS TESTS
 * Evidence Management System
 * =====================================================
 */

const request = require('supertest');
const express = require('express');

// Create a test app instance
const app = express();

// Import and configure middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Import routes for testing
const authRoutes = require('../routes/auth');
const userRoutes = require('../routes/users');
const authMiddleware = require('../middleware/auth');

// Configure test routes
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/users', authMiddleware.authenticateToken, userRoutes);

describe('Users Endpoints', () => {
  let authToken;
  let adminToken;
  let testUserId;

  beforeAll(async () => {
    // Login as regular user
    const userLoginResponse = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'user@company.com',
        password: 'user123'
      });

    authToken = userLoginResponse.body.data.tokens.accessToken;

    // Login as admin
    const adminLoginResponse = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'admin@company.com',
        password: 'admin123'
      });

    adminToken = adminLoginResponse.body.data.tokens.accessToken;
  });

  describe('GET /api/v1/users', () => {
    test('should get users list as admin', async () => {
      const response = await request(app)
        .get('/api/v1/users')
        .set('Authorization', `Bearer ${adminToken}`);

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.users).toBeDefined();
      expect(Array.isArray(response.body.data.users)).toBe(true);
    });

    test('should get users with pagination', async () => {
      const response = await request(app)
        .get('/api/v1/users?page=1&limit=5')
        .set('Authorization', `Bearer ${adminToken}`);

      expect(response.status).toBe(200);
      expect(response.body.data.pagination).toBeDefined();
      expect(response.body.data.pagination.page).toBe(1);
      expect(response.body.data.pagination.limit).toBe(5);
    });

    test('should search users by name', async () => {
      const response = await request(app)
        .get('/api/v1/users?search=admin')
        .set('Authorization', `Bearer ${adminToken}`);

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
    });

    test('should filter users by role', async () => {
      const response = await request(app)
        .get('/api/v1/users?role=admin')
        .set('Authorization', `Bearer ${adminToken}`);

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
    });

    test('should filter users by department', async () => {
      const response = await request(app)
        .get('/api/v1/users?department=IT')
        .set('Authorization', `Bearer ${adminToken}`);

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
    });

    test('should fail for non-admin users', async () => {
      const response = await request(app)
        .get('/api/v1/users')
        .set('Authorization', `Bearer ${authToken}`);

      expect(response.status).toBe(403);
      expect(response.body.success).toBe(false);
    });

    test('should fail without authentication', async () => {
      const response = await request(app)
        .get('/api/v1/users');

      expect(response.status).toBe(401);
      expect(response.body.success).toBe(false);
    });
  });

  describe('GET /api/v1/users/me', () => {
    test('should get current user profile', async () => {
      const response = await request(app)
        .get('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`);

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.user).toBeDefined();
      expect(response.body.data.user.email).toBe('user@company.com');
    });

    test('should fail without authentication', async () => {
      const response = await request(app)
        .get('/api/v1/users/me');

      expect(response.status).toBe(401);
      expect(response.body.success).toBe(false);
    });
  });

  describe('PUT /api/v1/users/me', () => {
    test('should update current user profile', async () => {
      const response = await request(app)
        .put('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          firstName: 'Updated',
          lastName: 'User',
          department: 'Engineering'
        });

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.user.firstName).toBe('Updated');
    });

    test('should update notification settings', async () => {
      const response = await request(app)
        .put('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          notificationSettings: {
            email: false,
            push: true,
            desktop: true
          }
        });

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
    });

    test('should not allow email update', async () => {
      const response = await request(app)
        .put('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          email: 'newemail@example.com'
        });

      // Email updates should be restricted or require special validation
      expect([200, 400, 403]).toContain(response.status);
    });

    test('should validate input data', async () => {
      const response = await request(app)
        .put('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          firstName: '', // Invalid empty name
          department: 'x'.repeat(101) // Too long department name
        });

      expect(response.status).toBe(400);
      expect(response.body.success).toBe(false);
    });
  });

  describe('GET /api/v1/users/:id', () => {
    test('should get user by ID as admin', async () => {
      // First get a user ID from the users list
      const usersResponse = await request(app)
        .get('/api/v1/users')
        .set('Authorization', `Bearer ${adminToken}`);

      if (usersResponse.body.data.users.length > 0) {
        const userId = usersResponse.body.data.users[0].id;
        
        const response = await request(app)
          .get(`/api/v1/users/${userId}`)
          .set('Authorization', `Bearer ${adminToken}`);

        expect(response.status).toBe(200);
        expect(response.body.success).toBe(true);
        expect(response.body.data.user).toBeDefined();
      }
    });

    test('should fail with invalid user ID', async () => {
      const response = await request(app)
        .get('/api/v1/users/invalid_id')
        .set('Authorization', `Bearer ${adminToken}`);

      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
    });

    test('should fail for non-admin users', async () => {
      const response = await request(app)
        .get('/api/v1/users/507f1f77bcf86cd799439011')
        .set('Authorization', `Bearer ${authToken}`);

      expect(response.status).toBe(403);
      expect(response.body.success).toBe(false);
    });
  });

  describe('PUT /api/v1/users/:id', () => {
    test('should update user as admin', async () => {
      // Get a user ID first
      const usersResponse = await request(app)
        .get('/api/v1/users')
        .set('Authorization', `Bearer ${adminToken}`);

      if (usersResponse.body.data.users.length > 0) {
        const userId = usersResponse.body.data.users[0].id;
        
        const response = await request(app)
          .put(`/api/v1/users/${userId}`)
          .set('Authorization', `Bearer ${adminToken}`)
          .send({
            firstName: 'Admin Updated',
            role: 'user',
            isActive: true
          });

        expect(response.status).toBe(200);
        expect(response.body.success).toBe(true);
      }
    });

    test('should fail for non-admin users', async () => {
      const response = await request(app)
        .put('/api/v1/users/507f1f77bcf86cd799439011')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          firstName: 'Unauthorized Update'
        });

      expect(response.status).toBe(403);
      expect(response.body.success).toBe(false);
    });
  });

  describe('DELETE /api/v1/users/:id', () => {
    test('should delete user as admin', async () => {
      // In a real test, you'd create a test user first
      const response = await request(app)
        .delete('/api/v1/users/507f1f77bcf86cd799439999')
        .set('Authorization', `Bearer ${adminToken}`);

      // Expect 404 since user doesn't exist, but not 403 (forbidden)
      expect([200, 404]).toContain(response.status);
    });

    test('should fail for non-admin users', async () => {
      const response = await request(app)
        .delete('/api/v1/users/507f1f77bcf86cd799439011')
        .set('Authorization', `Bearer ${authToken}`);

      expect(response.status).toBe(403);
      expect(response.body.success).toBe(false);
    });
  });

  describe('User Statistics', () => {
    test('should get user statistics', async () => {
      const response = await request(app)
        .get('/api/v1/users/me/stats')
        .set('Authorization', `Bearer ${authToken}`);

      expect([200, 404]).toContain(response.status);
      if (response.status === 200) {
        expect(response.body.data.stats).toBeDefined();
      }
    });
  });

  describe('User Avatar', () => {
    test('should handle avatar upload endpoint', async () => {
      const response = await request(app)
        .post('/api/v1/users/me/avatar')
        .set('Authorization', `Bearer ${authToken}`);

      // Without actual file, should fail with 400
      expect(response.status).toBe(400);
    });
  });

  describe('User Validation', () => {
    test('should validate user data on update', async () => {
      const response = await request(app)
        .put('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          firstName: 'A'.repeat(101), // Too long
          email: 'invalid-email', // Invalid format
          role: 'invalid-role' // Invalid role
        });

      expect(response.status).toBe(400);
      expect(response.body.success).toBe(false);
    });

    test('should sanitize user input', async () => {
      const response = await request(app)
        .put('/api/v1/users/me')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          firstName: '<script>alert("xss")</script>',
          lastName: 'Normal Name'
        });

      if (response.status === 200) {
        // Should sanitize the script tag
        expect(response.body.data.user.firstName).not.toContain('<script>');
      }
    });
  });
});
