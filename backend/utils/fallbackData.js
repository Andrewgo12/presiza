/**
 * =====================================================
 * FALLBACK DATA UTILITIES
 * Evidence Management System
 * =====================================================
 * 
 * Comprehensive fallback data for all endpoints when MongoDB is unavailable
 */

// Fallback Users Data
const getFallbackUsers = () => [
  {
    _id: '507f1f77bcf86cd799439011',
    email: 'admin@test.com',
    firstName: 'Admin',
    lastName: 'User',
    role: 'admin',
    department: 'IT',
    position: 'System Administrator',
    isActive: true,
    avatar: null,
    createdAt: new Date('2023-11-01'),
    lastLogin: new Date('2024-07-04')
  },
  {
    _id: '507f1f77bcf86cd799439012',
    email: 'user@test.com',
    firstName: 'Test',
    lastName: 'User',
    role: 'user',
    department: 'General',
    position: 'User',
    isActive: true,
    avatar: null,
    createdAt: new Date('2024-01-15'),
    lastLogin: new Date('2024-07-03')
  },
  {
    _id: '507f1f77bcf86cd799439013',
    email: 'analyst@test.com',
    firstName: 'Data',
    lastName: 'Analyst',
    role: 'analyst',
    department: 'Analytics',
    position: 'Senior Analyst',
    isActive: true,
    avatar: null,
    createdAt: new Date('2023-12-01'),
    lastLogin: new Date('2024-07-04')
  },
  {
    _id: '507f1f77bcf86cd799439014',
    email: 'investigator@test.com',
    firstName: 'John',
    lastName: 'Investigator',
    role: 'investigator',
    department: 'Investigation',
    position: 'Lead Investigator',
    isActive: true,
    avatar: null,
    createdAt: new Date('2023-11-15'),
    lastLogin: new Date('2024-07-04')
  }
];

// Fallback Messages Data
const getFallbackMessages = () => [
  {
    _id: '507f1f77bcf86cd799439060',
    content: 'Welcome everyone to the Research Team Alpha! Let\'s start by sharing our current projects and goals.',
    sender: '507f1f77bcf86cd799439011',
    recipient: '507f1f77bcf86cd799439040',
    recipientType: 'Group',
    messageType: 'text',
    status: 'read',
    isRead: true,
    readAt: new Date('2024-01-16'),
    createdAt: new Date('2024-01-15')
  },
  {
    _id: '507f1f77bcf86cd799439061',
    content: 'I\'ve uploaded the Q4 research analysis. Please review and provide feedback.',
    sender: '507f1f77bcf86cd799439013',
    recipient: '507f1f77bcf86cd799439040',
    recipientType: 'Group',
    messageType: 'text',
    status: 'read',
    isRead: true,
    readAt: new Date('2024-06-02'),
    createdAt: new Date('2024-06-01')
  },
  {
    _id: '507f1f77bcf86cd799439062',
    content: 'Great work on the analysis! The statistical models are very comprehensive. 👍',
    sender: '507f1f77bcf86cd799439011',
    recipient: '507f1f77bcf86cd799439040',
    recipientType: 'Group',
    messageType: 'text',
    status: 'read',
    isRead: true,
    readAt: new Date('2024-06-03'),
    createdAt: new Date('2024-06-02')
  },
  {
    _id: '507f1f77bcf86cd799439063',
    content: 'Hi! Could you review the security audit findings when you have a moment?',
    sender: '507f1f77bcf86cd799439014',
    recipient: '507f1f77bcf86cd799439011',
    recipientType: 'User',
    messageType: 'text',
    status: 'read',
    isRead: true,
    readAt: new Date('2024-06-06'),
    createdAt: new Date('2024-06-05')
  },
  {
    _id: '507f1f77bcf86cd799439064',
    content: 'Absolutely! I\'ll review it today and get back to you with feedback.',
    sender: '507f1f77bcf86cd799439011',
    recipient: '507f1f77bcf86cd799439014',
    recipientType: 'User',
    messageType: 'text',
    status: 'read',
    isRead: true,
    readAt: new Date('2024-06-06'),
    createdAt: new Date('2024-06-06')
  }
];

// Fallback Evidences Data
const getFallbackEvidences = () => [
  {
    _id: '507f1f77bcf86cd799439070',
    title: 'Q4 Research Analysis Report',
    description: 'Comprehensive analysis of research data collected during Q4 2023, including statistical models and predictive analytics',
    evidenceType: 'document',
    category: 'research',
    submittedBy: '507f1f77bcf86cd799439013',
    status: 'approved',
    priority: 'high',
    project: 'AI Research Initiative',
    caseNumber: 'RES-2024-001',
    tags: ['research', 'analysis', 'Q4', 'data', 'statistics'],
    reviewedBy: '507f1f77bcf86cd799439011',
    reviewDate: new Date('2024-06-15'),
    feedback: 'Excellent work! The analysis is thorough and well-documented. Approved for publication.',
    submissionDate: new Date('2024-06-01'),
    incidentDate: new Date('2023-12-31'),
    files: ['507f1f77bcf86cd799439020'],
    comments: [
      {
        _id: '507f1f77bcf86cd799439080',
        author: '507f1f77bcf86cd799439011',
        content: 'Great statistical approach. The methodology is sound.',
        createdAt: new Date('2024-06-10')
      }
    ],
    createdAt: new Date('2024-06-01'),
    updatedAt: new Date('2024-06-15')
  },
  {
    _id: '507f1f77bcf86cd799439071',
    title: 'Security Audit Findings',
    description: 'Complete security assessment of network infrastructure and identified vulnerabilities',
    evidenceType: 'document',
    category: 'audit',
    submittedBy: '507f1f77bcf86cd799439014',
    status: 'approved',
    priority: 'critical',
    project: 'Security Enhancement',
    caseNumber: 'SEC-2024-003',
    tags: ['security', 'audit', 'vulnerabilities', 'network'],
    reviewedBy: '507f1f77bcf86cd799439011',
    reviewDate: new Date('2024-06-20'),
    feedback: 'Critical findings addressed. Implementation plan approved.',
    submissionDate: new Date('2024-06-05'),
    incidentDate: new Date('2024-05-15'),
    files: ['507f1f77bcf86cd799439023'],
    comments: [],
    createdAt: new Date('2024-06-05'),
    updatedAt: new Date('2024-06-20')
  },
  {
    _id: '507f1f77bcf86cd799439072',
    title: 'User Interface Mockups v2.0',
    description: 'Updated UI/UX designs for the new dashboard interface with improved accessibility features',
    evidenceType: 'image',
    category: 'investigation',
    submittedBy: '507f1f77bcf86cd799439012',
    status: 'under_review',
    priority: 'medium',
    project: 'Dashboard Redesign',
    caseNumber: 'UI-2024-007',
    tags: ['ui', 'ux', 'mockup', 'dashboard', 'accessibility'],
    submissionDate: new Date('2024-07-01'),
    incidentDate: new Date('2024-06-25'),
    files: ['507f1f77bcf86cd799439021'],
    comments: [
      {
        _id: '507f1f77bcf86cd799439081',
        author: '507f1f77bcf86cd799439013',
        content: 'The color scheme looks good, but consider contrast ratios.',
        createdAt: new Date('2024-07-02')
      }
    ],
    createdAt: new Date('2024-07-01'),
    updatedAt: new Date('2024-07-02')
  }
];

// Fallback Notifications Data
const getFallbackNotifications = () => [
  {
    _id: '507f1f77bcf86cd799439090',
    title: 'Evidence Approved',
    message: 'Your Q4 Research Analysis Report has been approved by the admin',
    recipient: '507f1f77bcf86cd799439013',
    type: 'success',
    category: 'evidence',
    actionUrl: '/evidences',
    priority: 'normal',
    isRead: true,
    readAt: new Date('2024-06-16'),
    createdAt: new Date('2024-06-15')
  },
  {
    _id: '507f1f77bcf86cd799439091',
    title: 'New Comment on Evidence',
    message: 'Someone commented on your UI Mockups evidence.',
    recipient: '507f1f77bcf86cd799439012',
    type: 'info',
    category: 'comment',
    actionUrl: '/evidences/ui-mockups',
    priority: 'normal',
    isRead: false,
    createdAt: new Date('2024-07-02')
  },
  {
    _id: '507f1f77bcf86cd799439092',
    title: 'System Maintenance Scheduled',
    message: 'Scheduled maintenance will occur tonight from 2-4 AM. Some services may be unavailable.',
    recipient: '507f1f77bcf86cd799439011',
    type: 'warning',
    category: 'system',
    actionUrl: '/admin/maintenance',
    priority: 'high',
    isRead: true,
    readAt: new Date('2024-07-01'),
    createdAt: new Date('2024-06-30')
  },
  {
    _id: '507f1f77bcf86cd799439093',
    title: 'New Message',
    message: 'You have a new message from Admin in the Research Team Alpha group.',
    recipient: '507f1f77bcf86cd799439013',
    type: 'info',
    category: 'message',
    actionUrl: '/messages/research-team-alpha',
    priority: 'normal',
    isRead: true,
    readAt: new Date('2024-07-04'),
    createdAt: new Date('2024-07-03')
  },
  {
    _id: '507f1f77bcf86cd799439094',
    title: 'Evidence Review Due',
    message: 'You have 3 evidence submissions pending review. Due date: July 10, 2024.',
    recipient: '507f1f77bcf86cd799439011',
    type: 'reminder',
    category: 'task',
    actionUrl: '/evidences?status=pending',
    priority: 'high',
    isRead: false,
    createdAt: new Date('2024-07-04')
  }
];

// Helper function to get user info by ID
const getUserById = (userId) => {
  const users = getFallbackUsers();
  return users.find(user => user._id === userId) || {
    _id: userId,
    firstName: 'Unknown',
    lastName: 'User',
    email: 'unknown@test.com'
  };
};

// Helper function to enrich data with user information
const enrichWithUserData = (items, userField = 'userId') => {
  return items.map(item => ({
    ...item,
    [userField]: getUserById(item[userField])
  }));
};

module.exports = {
  getFallbackUsers,
  getFallbackMessages,
  getFallbackEvidences,
  getFallbackNotifications,
  getUserById,
  enrichWithUserData
};
