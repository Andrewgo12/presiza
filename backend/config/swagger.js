/**
 * =====================================================
 * SWAGGER API DOCUMENTATION CONFIGURATION
 * Evidence Management System
 * =====================================================
 */

const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');

const options = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'Evidence Management System API',
      version: '1.0.0',
      description: 'Comprehensive API documentation for the Evidence Management System',
      contact: {
        name: 'Evidence Management Team',
        email: 'admin@evidencemanagement.com'
      },
      license: {
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
      }
    },
    servers: [
      {
        url: 'http://localhost:5002',
        description: 'Development server'
      },
      {
        url: 'https://api.evidencemanagement.com',
        description: 'Production server'
      }
    ],
    components: {
      securitySchemes: {
        bearerAuth: {
          type: 'http',
          scheme: 'bearer',
          bearerFormat: 'JWT',
          description: 'JWT token for authentication'
        }
      },
      schemas: {
        User: {
          type: 'object',
          properties: {
            _id: {
              type: 'string',
              description: 'User unique identifier'
            },
            email: {
              type: 'string',
              format: 'email',
              description: 'User email address'
            },
            firstName: {
              type: 'string',
              description: 'User first name'
            },
            lastName: {
              type: 'string',
              description: 'User last name'
            },
            role: {
              type: 'string',
              enum: ['admin', 'user', 'analyst', 'investigator'],
              description: 'User role in the system'
            },
            department: {
              type: 'string',
              description: 'User department'
            },
            position: {
              type: 'string',
              description: 'User position/title'
            },
            isActive: {
              type: 'boolean',
              description: 'Whether the user account is active'
            },
            createdAt: {
              type: 'string',
              format: 'date-time',
              description: 'Account creation timestamp'
            },
            lastLogin: {
              type: 'string',
              format: 'date-time',
              description: 'Last login timestamp'
            }
          }
        },
        File: {
          type: 'object',
          properties: {
            _id: {
              type: 'string',
              description: 'File unique identifier'
            },
            filename: {
              type: 'string',
              description: 'System filename'
            },
            originalName: {
              type: 'string',
              description: 'Original filename as uploaded'
            },
            mimeType: {
              type: 'string',
              description: 'File MIME type'
            },
            size: {
              type: 'integer',
              description: 'File size in bytes'
            },
            category: {
              type: 'string',
              enum: ['document', 'image', 'video', 'audio', 'archive'],
              description: 'File category'
            },
            accessLevel: {
              type: 'string',
              enum: ['public', 'internal', 'restricted', 'confidential'],
              description: 'File access level'
            },
            uploadedBy: {
              $ref: '#/components/schemas/User'
            },
            downloadCount: {
              type: 'integer',
              description: 'Number of times file was downloaded'
            },
            viewCount: {
              type: 'integer',
              description: 'Number of times file was viewed'
            },
            createdAt: {
              type: 'string',
              format: 'date-time'
            }
          }
        },
        Group: {
          type: 'object',
          properties: {
            _id: {
              type: 'string',
              description: 'Group unique identifier'
            },
            name: {
              type: 'string',
              description: 'Group name'
            },
            description: {
              type: 'string',
              description: 'Group description'
            },
            type: {
              type: 'string',
              enum: ['public', 'private', 'protected'],
              description: 'Group visibility type'
            },
            category: {
              type: 'string',
              enum: ['research', 'team', 'department', 'project', 'general'],
              description: 'Group category'
            },
            members: {
              type: 'array',
              items: {
                type: 'object',
                properties: {
                  user: {
                    $ref: '#/components/schemas/User'
                  },
                  role: {
                    type: 'string',
                    enum: ['owner', 'admin', 'moderator', 'member']
                  },
                  joinedAt: {
                    type: 'string',
                    format: 'date-time'
                  }
                }
              }
            },
            messageCount: {
              type: 'integer',
              description: 'Total number of messages in group'
            },
            fileCount: {
              type: 'integer',
              description: 'Total number of files shared in group'
            },
            lastActivity: {
              type: 'string',
              format: 'date-time',
              description: 'Last activity timestamp'
            }
          }
        },
        Evidence: {
          type: 'object',
          properties: {
            _id: {
              type: 'string',
              description: 'Evidence unique identifier'
            },
            title: {
              type: 'string',
              description: 'Evidence title'
            },
            description: {
              type: 'string',
              description: 'Evidence description'
            },
            evidenceType: {
              type: 'string',
              enum: ['document', 'image', 'video', 'audio', 'data', 'testimony', 'physical'],
              description: 'Type of evidence'
            },
            category: {
              type: 'string',
              enum: ['research', 'investigation', 'audit', 'compliance', 'legal'],
              description: 'Evidence category'
            },
            status: {
              type: 'string',
              enum: ['pending', 'under_review', 'approved', 'rejected', 'requires_changes'],
              description: 'Evidence review status'
            },
            priority: {
              type: 'string',
              enum: ['low', 'medium', 'high', 'critical'],
              description: 'Evidence priority level'
            },
            submittedBy: {
              $ref: '#/components/schemas/User'
            },
            reviewedBy: {
              $ref: '#/components/schemas/User'
            },
            caseNumber: {
              type: 'string',
              description: 'Associated case number'
            },
            tags: {
              type: 'array',
              items: {
                type: 'string'
              },
              description: 'Evidence tags for categorization'
            },
            files: {
              type: 'array',
              items: {
                type: 'string'
              },
              description: 'Associated file IDs'
            },
            submissionDate: {
              type: 'string',
              format: 'date-time'
            },
            reviewDate: {
              type: 'string',
              format: 'date-time'
            },
            feedback: {
              type: 'string',
              description: 'Review feedback'
            }
          }
        },
        Message: {
          type: 'object',
          properties: {
            _id: {
              type: 'string',
              description: 'Message unique identifier'
            },
            content: {
              type: 'string',
              description: 'Message content'
            },
            sender: {
              $ref: '#/components/schemas/User'
            },
            recipient: {
              type: 'string',
              description: 'Recipient ID (User or Group)'
            },
            recipientType: {
              type: 'string',
              enum: ['User', 'Group'],
              description: 'Type of recipient'
            },
            messageType: {
              type: 'string',
              enum: ['text', 'file', 'image', 'system'],
              description: 'Type of message'
            },
            status: {
              type: 'string',
              enum: ['sent', 'delivered', 'read'],
              description: 'Message delivery status'
            },
            isRead: {
              type: 'boolean',
              description: 'Whether message has been read'
            },
            readAt: {
              type: 'string',
              format: 'date-time',
              description: 'Timestamp when message was read'
            },
            createdAt: {
              type: 'string',
              format: 'date-time'
            }
          }
        },
        Notification: {
          type: 'object',
          properties: {
            _id: {
              type: 'string',
              description: 'Notification unique identifier'
            },
            title: {
              type: 'string',
              description: 'Notification title'
            },
            message: {
              type: 'string',
              description: 'Notification message'
            },
            recipient: {
              type: 'string',
              description: 'Recipient user ID'
            },
            type: {
              type: 'string',
              enum: ['info', 'success', 'warning', 'error', 'system', 'reminder'],
              description: 'Notification type'
            },
            category: {
              type: 'string',
              enum: ['upload', 'comment', 'task', 'system', 'group', 'evidence', 'message'],
              description: 'Notification category'
            },
            priority: {
              type: 'string',
              enum: ['low', 'normal', 'high'],
              description: 'Notification priority'
            },
            actionUrl: {
              type: 'string',
              description: 'URL for notification action'
            },
            isRead: {
              type: 'boolean',
              description: 'Whether notification has been read'
            },
            readAt: {
              type: 'string',
              format: 'date-time'
            },
            createdAt: {
              type: 'string',
              format: 'date-time'
            }
          }
        },
        Error: {
          type: 'object',
          properties: {
            error: {
              type: 'string',
              description: 'Error message'
            },
            code: {
              type: 'string',
              description: 'Error code'
            },
            details: {
              type: 'object',
              description: 'Additional error details'
            }
          }
        },
        PaginationResponse: {
          type: 'object',
          properties: {
            page: {
              type: 'integer',
              description: 'Current page number'
            },
            limit: {
              type: 'integer',
              description: 'Items per page'
            },
            total: {
              type: 'integer',
              description: 'Total number of items'
            },
            pages: {
              type: 'integer',
              description: 'Total number of pages'
            }
          }
        }
      }
    },
    security: [
      {
        bearerAuth: []
      }
    ]
  },
  apis: [
    './routes/*.js',
    './middleware/*.js'
  ]
};

const specs = swaggerJsdoc(options);

module.exports = {
  swaggerUi,
  specs
};
