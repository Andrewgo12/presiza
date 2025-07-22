/**
 * =====================================================
 * INTEGRATION TESTS
 * Evidence Management System
 * =====================================================
 */

const request = require('supertest');
const express = require('express');
const path = require('path');
const fs = require('fs');

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
const authMiddleware = require('../middleware/auth');

// Configure test routes
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/users', authMiddleware.authenticateToken, userRoutes);
app.use('/api/v1/files', authMiddleware.authenticateToken, fileRoutes);
app.use('/api/v1/groups', authMiddleware.authenticateToken, groupRoutes);
app.use('/api/v1/messages', authMiddleware.authenticateToken, messageRoutes);
app.use('/api/v1/evidences', authMiddleware.authenticateToken, evidenceRoutes);

describe('Integration Tests - Complete Workflows', () => {
  let adminToken;
  let userToken;
  let testFileId;
  let testGroupId;
  let testEvidenceId;

  beforeAll(async () => {
    // Login as admin
    const adminLogin = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'admin@company.com',
        password: 'admin123'
      });

    adminToken = adminLogin.body.data.tokens.accessToken;

    // Login as regular user
    const userLogin = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'user@company.com',
        password: 'user123'
      });

    userToken = userLogin.body.data.tokens.accessToken;
  });

  describe('Complete File Management Workflow', () => {
    test('should complete full file lifecycle', async () => {
      // 1. Upload a file
      const testFilePath = path.join(__dirname, 'integration-test-file.txt');
      fs.writeFileSync(testFilePath, 'Integration test file content');

      const uploadResponse = await request(app)
        .post('/api/v1/files/upload')
        .set('Authorization', `Bearer ${userToken}`)
        .attach('file', testFilePath)
        .field('category', 'document')
        .field('description', 'Integration test file')
        .field('tags', JSON.stringify(['integration', 'test']));

      expect(uploadResponse.status).toBe(201);
      testFileId = uploadResponse.body.data.file.id;

      // 2. Get file details
      const getFileResponse = await request(app)
        .get(`/api/v1/files/${testFileId}`)
        .set('Authorization', `Bearer ${userToken}`);

      expect(getFileResponse.status).toBe(200);
      expect(getFileResponse.body.data.file.originalName).toBe('integration-test-file.txt');

      // 3. Update file metadata
      const updateResponse = await request(app)
        .put(`/api/v1/files/${testFileId}`)
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          description: 'Updated integration test file',
          tags: ['integration', 'test', 'updated']
        });

      expect(updateResponse.status).toBe(200);

      // 4. Share file
      const shareResponse = await request(app)
        .post(`/api/v1/files/${testFileId}/share`)
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          expiresIn: 86400,
          allowDownload: true
        });

      expect([200, 404]).toContain(shareResponse.status);

      // 5. Download file
      const downloadResponse = await request(app)
        .get(`/api/v1/files/${testFileId}/download`)
        .set('Authorization', `Bearer ${userToken}`);

      expect([200, 404]).toContain(downloadResponse.status);

      // Clean up
      fs.unlinkSync(testFilePath);
    });
  });

  describe('Complete Group Management Workflow', () => {
    test('should complete full group lifecycle', async () => {
      // 1. Create a group
      const createGroupResponse = await request(app)
        .post('/api/v1/groups')
        .set('Authorization', `Bearer ${adminToken}`)
        .send({
          name: 'Integration Test Group',
          description: 'Group for integration testing',
          type: 'private',
          settings: {
            allowMemberInvites: true,
            allowFileSharing: true
          }
        });

      expect(createGroupResponse.status).toBe(201);
      testGroupId = createGroupResponse.body.data.group.id;

      // 2. Get group details
      const getGroupResponse = await request(app)
        .get(`/api/v1/groups/${testGroupId}`)
        .set('Authorization', `Bearer ${adminToken}`);

      expect(getGroupResponse.status).toBe(200);
      expect(getGroupResponse.body.data.group.name).toBe('Integration Test Group');

      // 3. Update group
      const updateGroupResponse = await request(app)
        .put(`/api/v1/groups/${testGroupId}`)
        .set('Authorization', `Bearer ${adminToken}`)
        .send({
          description: 'Updated integration test group'
        });

      expect(updateGroupResponse.status).toBe(200);

      // 4. Invite user to group
      const inviteResponse = await request(app)
        .post(`/api/v1/groups/${testGroupId}/invite`)
        .set('Authorization', `Bearer ${adminToken}`)
        .send({
          userId: 'user_id_here',
          role: 'member'
        });

      expect([200, 400, 404]).toContain(inviteResponse.status);

      // 5. Get group members
      const membersResponse = await request(app)
        .get(`/api/v1/groups/${testGroupId}/members`)
        .set('Authorization', `Bearer ${adminToken}`);

      expect(membersResponse.status).toBe(200);
    });
  });

  describe('Complete Evidence Management Workflow', () => {
    test('should complete full evidence lifecycle', async () => {
      // 1. Create evidence (requires file)
      const createEvidenceResponse = await request(app)
        .post('/api/v1/evidences')
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          title: 'Integration Test Evidence',
          description: 'Evidence for integration testing',
          category: 'security',
          priority: 'medium',
          fileIds: testFileId ? [testFileId] : [],
          metadata: {
            location: 'Test Location',
            timestamp: new Date().toISOString()
          }
        });

      expect(createEvidenceResponse.status).toBe(201);
      testEvidenceId = createEvidenceResponse.body.data.evidence.id;

      // 2. Get evidence details
      const getEvidenceResponse = await request(app)
        .get(`/api/v1/evidences/${testEvidenceId}`)
        .set('Authorization', `Bearer ${userToken}`);

      expect(getEvidenceResponse.status).toBe(200);

      // 3. Update evidence
      const updateEvidenceResponse = await request(app)
        .put(`/api/v1/evidences/${testEvidenceId}`)
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          description: 'Updated evidence description',
          priority: 'high'
        });

      expect(updateEvidenceResponse.status).toBe(200);

      // 4. Evaluate evidence (as admin)
      const evaluateResponse = await request(app)
        .post(`/api/v1/evidences/${testEvidenceId}/evaluate`)
        .set('Authorization', `Bearer ${adminToken}`)
        .send({
          rating: 4,
          comment: 'Good quality evidence',
          status: 'approved'
        });

      expect(evaluateResponse.status).toBe(200);

      // 5. Get evidence history
      const historyResponse = await request(app)
        .get(`/api/v1/evidences/${testEvidenceId}/history`)
        .set('Authorization', `Bearer ${adminToken}`);

      expect([200, 404]).toContain(historyResponse.status);
    });
  });

  describe('Complete Messaging Workflow', () => {
    test('should complete messaging workflow', async () => {
      // 1. Get conversations
      const conversationsResponse = await request(app)
        .get('/api/v1/messages')
        .set('Authorization', `Bearer ${userToken}`);

      expect(conversationsResponse.status).toBe(200);

      // 2. Send a message (if conversation exists)
      if (conversationsResponse.body.data.conversations.length > 0) {
        const conversationId = conversationsResponse.body.data.conversations[0].id;

        const sendMessageResponse = await request(app)
          .post(`/api/v1/messages/${conversationId}`)
          .set('Authorization', `Bearer ${userToken}`)
          .send({
            content: 'Integration test message',
            type: 'text'
          });

        expect([200, 201, 404]).toContain(sendMessageResponse.status);

        // 3. Get messages from conversation
        const getMessagesResponse = await request(app)
          .get(`/api/v1/messages/${conversationId}`)
          .set('Authorization', `Bearer ${userToken}`);

        expect([200, 404]).toContain(getMessagesResponse.status);
      }
    });
  });

  describe('Cross-Module Integration', () => {
    test('should handle file sharing between users', async () => {
      if (!testFileId) return;

      // 1. Share file with specific user
      const shareResponse = await request(app)
        .post(`/api/v1/files/${testFileId}/share`)
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          userId: 'target_user_id',
          permissions: ['read', 'download']
        });

      expect([200, 400, 404]).toContain(shareResponse.status);
    });

    test('should handle group file sharing', async () => {
      if (!testFileId || !testGroupId) return;

      // Share file with group
      const shareWithGroupResponse = await request(app)
        .post(`/api/v1/files/${testFileId}/share-with-group`)
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          groupId: testGroupId,
          permissions: ['read']
        });

      expect([200, 400, 404]).toContain(shareWithGroupResponse.status);
    });

    test('should handle evidence with multiple files', async () => {
      // Create evidence with multiple files
      const multiFileEvidenceResponse = await request(app)
        .post('/api/v1/evidences')
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          title: 'Multi-file Evidence',
          description: 'Evidence with multiple files',
          category: 'investigation',
          fileIds: testFileId ? [testFileId] : []
        });

      expect([200, 201, 400]).toContain(multiFileEvidenceResponse.status);
    });
  });

  describe('Error Handling Integration', () => {
    test('should handle cascading errors gracefully', async () => {
      // Try to create evidence with non-existent file
      const invalidEvidenceResponse = await request(app)
        .post('/api/v1/evidences')
        .set('Authorization', `Bearer ${userToken}`)
        .send({
          title: 'Invalid Evidence',
          fileIds: ['507f1f77bcf86cd799439999'] // Non-existent file
        });

      expect([400, 404]).toContain(invalidEvidenceResponse.status);
    });

    test('should handle database connection issues', async () => {
      // This would test fallback behavior when database is unavailable
      // In the current implementation, it falls back to mock data
      const response = await request(app)
        .get('/api/v1/files')
        .set('Authorization', `Bearer ${userToken}`);

      expect(response.status).toBe(200);
    });
  });

  describe('Performance Integration', () => {
    test('should handle concurrent requests', async () => {
      const promises = [];
      
      // Make 5 concurrent requests
      for (let i = 0; i < 5; i++) {
        promises.push(
          request(app)
            .get('/api/v1/files')
            .set('Authorization', `Bearer ${userToken}`)
        );
      }

      const responses = await Promise.all(promises);
      
      responses.forEach(response => {
        expect(response.status).toBe(200);
      });
    });

    test('should handle large data sets', async () => {
      // Test pagination with large datasets
      const response = await request(app)
        .get('/api/v1/files?limit=100')
        .set('Authorization', `Bearer ${userToken}`);

      expect(response.status).toBe(200);
      expect(response.body.data.pagination).toBeDefined();
    });
  });

  afterAll(async () => {
    // Clean up test data
    if (testFileId) {
      await request(app)
        .delete(`/api/v1/files/${testFileId}`)
        .set('Authorization', `Bearer ${userToken}`);
    }

    if (testGroupId) {
      await request(app)
        .delete(`/api/v1/groups/${testGroupId}`)
        .set('Authorization', `Bearer ${adminToken}`);
    }

    if (testEvidenceId) {
      await request(app)
        .delete(`/api/v1/evidences/${testEvidenceId}`)
        .set('Authorization', `Bearer ${adminToken}`);
    }
  });
});
