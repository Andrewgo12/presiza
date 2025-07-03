/**
 * Rutas de Analytics - Sistema de Gestión de Evidencias
 */

const express = require('express');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

const router = express.Router();

/**
 * GET /api/v1/analytics/dashboard
 * Obtener datos para el dashboard
 */
router.get('/dashboard', asyncHandler(async (req, res) => {
  // Mock data para analytics
  const dashboardData = {
    totalFiles: 1247,
    totalUsers: 45,
    totalGroups: 12,
    totalEvidences: 89,
    recentActivity: [
      { date: '2024-01-01', files: 15, users: 8 },
      { date: '2024-01-02', files: 23, users: 12 },
      { date: '2024-01-03', files: 18, users: 10 }
    ],
    filesByCategory: [
      { category: 'documents', count: 450 },
      { category: 'images', count: 320 },
      { category: 'videos', count: 180 },
      { category: 'audio', count: 120 },
      { category: 'other', count: 177 }
    ],
    userActivity: [
      { user: 'Ana García', actions: 45 },
      { user: 'Dr. Smith', actions: 38 },
      { user: 'Carlos López', actions: 32 }
    ]
  };

  res.json(dashboardData);
}));

/**
 * GET /api/v1/analytics/reports
 * Obtener datos para reportes
 */
router.get('/reports', asyncHandler(async (req, res) => {
  const { startDate, endDate, type } = req.query;

  // Mock data para reportes
  const reportData = {
    period: { startDate, endDate },
    type,
    summary: {
      totalFiles: 156,
      totalSize: '2.4 GB',
      averageFileSize: '15.4 MB',
      mostActiveUser: 'Ana García'
    },
    details: [
      { date: '2024-01-01', uploads: 12, downloads: 45 },
      { date: '2024-01-02', uploads: 18, downloads: 52 },
      { date: '2024-01-03', uploads: 15, downloads: 38 }
    ]
  };

  res.json(reportData);
}));

module.exports = router;
