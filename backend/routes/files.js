/**
 * Rutas de Archivos - Sistema de Gestión de Evidencias
 * Maneja subida, descarga y gestión de archivos
 */

const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs').promises;
const crypto = require('crypto');
const { body, validationResult } = require('express-validator');
const File = require('../models/File');
const { requireGroupPermission, requireOwnership } = require('../middleware/auth');
const { AppError, asyncHandler } = require('../middleware/errorHandler');

// Import database status for fallback functionality
const { getDatabaseStatus } = require('../config/database');

// Fallback file data for development mode
const getFallbackFiles = () => [
  {
    _id: '507f1f77bcf86cd799439020',
    filename: 'research_analysis_q4_2023.pdf',
    originalName: 'Q4 Research Analysis Report.pdf',
    mimeType: 'application/pdf',
    size: 2048576,
    path: '/uploads/2024/06/research_analysis_q4_2023.pdf',
    url: 'https://storage.company.com/files/research_analysis_q4_2023.pdf',
    uploadedBy: '507f1f77bcf86cd799439012',
    category: 'document',
    tags: ['research', 'analysis', 'Q4', 'report'],
    isPublic: false,
    accessLevel: 'internal',
    status: 'active',
    description: 'Comprehensive Q4 research analysis with statistical models',
    version: 1,
    downloadCount: 15,
    viewCount: 45,
    lastAccessed: new Date('2024-07-03'),
    createdAt: new Date('2024-06-01'),
    updatedAt: new Date('2024-06-01')
  },
  {
    _id: '507f1f77bcf86cd799439021',
    filename: 'ui_mockups_dashboard_v2.png',
    originalName: 'Dashboard UI Mockups v2.0.png',
    mimeType: 'image/png',
    size: 3145728,
    path: '/uploads/2024/07/ui_mockups_dashboard_v2.png',
    uploadedBy: '507f1f77bcf86cd799439013',
    category: 'image',
    tags: ['ui', 'mockup', 'dashboard', 'design'],
    isPublic: false,
    accessLevel: 'internal',
    status: 'active',
    description: 'Updated dashboard interface mockups with accessibility improvements',
    version: 2,
    downloadCount: 12,
    viewCount: 38,
    lastAccessed: new Date('2024-07-04'),
    createdAt: new Date('2024-07-01'),
    updatedAt: new Date('2024-07-01')
  },
  {
    _id: '507f1f77bcf86cd799439022',
    filename: 'customer_feedback_data_q2.xlsx',
    originalName: 'Customer Feedback Analysis Q2 2024.xlsx',
    mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    size: 512000,
    path: '/uploads/2024/07/customer_feedback_data_q2.xlsx',
    uploadedBy: '507f1f77bcf86cd799439014',
    category: 'document',
    tags: ['customer', 'feedback', 'data', 'Q2', 'analysis'],
    isPublic: false,
    accessLevel: 'internal',
    status: 'active',
    description: 'Raw customer feedback data and analysis for Q2 2024',
    version: 1,
    downloadCount: 7,
    viewCount: 14,
    lastAccessed: new Date('2024-07-03'),
    createdAt: new Date('2024-07-03'),
    updatedAt: new Date('2024-07-03')
  },
  {
    _id: '507f1f77bcf86cd799439023',
    filename: 'security_audit_findings.pdf',
    originalName: 'Security Audit Findings - June 2024.pdf',
    mimeType: 'application/pdf',
    size: 1536000,
    path: '/uploads/2024/06/security_audit_findings.pdf',
    uploadedBy: '507f1f77bcf86cd799439011',
    category: 'document',
    tags: ['security', 'audit', 'findings', 'vulnerabilities'],
    isPublic: false,
    accessLevel: 'confidential',
    status: 'active',
    description: 'Complete security assessment findings and recommendations',
    version: 2,
    downloadCount: 8,
    viewCount: 22,
    lastAccessed: new Date('2024-07-02'),
    createdAt: new Date('2024-06-05'),
    updatedAt: new Date('2024-06-05')
  },
  {
    _id: '507f1f77bcf86cd799439024',
    filename: 'compliance_training_intro.mp4',
    originalName: 'Compliance Training Introduction.mp4',
    mimeType: 'video/mp4',
    size: 52428800,
    path: '/uploads/2024/07/compliance_training_intro.mp4',
    uploadedBy: '507f1f77bcf86cd799439012',
    category: 'video',
    tags: ['compliance', 'training', 'introduction', 'education'],
    isPublic: false,
    accessLevel: 'internal',
    status: 'active',
    description: 'Introduction video for new employee compliance training',
    version: 1,
    downloadCount: 3,
    viewCount: 28,
    lastAccessed: new Date('2024-07-04'),
    createdAt: new Date('2024-07-04'),
    updatedAt: new Date('2024-07-04')
  }
];

const router = express.Router();

// Configuración de multer para subida de archivos
const storage = multer.diskStorage({
  destination: async (req, file, cb) => {
    const uploadDir = path.join(__dirname, '../uploads');
    try {
      await fs.mkdir(uploadDir, { recursive: true });
      cb(null, uploadDir);
    } catch (error) {
      cb(error);
    }
  },
  filename: (req, file, cb) => {
    // Generar nombre único para el archivo
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    const extension = path.extname(file.originalname);
    cb(null, `${uniqueSuffix}${extension}`);
  }
});

// Filtro de tipos de archivo permitidos
const fileFilter = (req, file, cb) => {
  const allowedTypes = [
    // Imágenes
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
    // Documentos
    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain', 'text/csv',
    // Archivos comprimidos
    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
    // Videos
    'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv',
    // Audio
    'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'
  ];

  if (allowedTypes.includes(file.mimetype)) {
    cb(null, true);
  } else {
    cb(new AppError(`Tipo de archivo no permitido: ${file.mimetype}`, 400, 'INVALID_FILE_TYPE'));
  }
};

const upload = multer({
  storage: storage,
  fileFilter: fileFilter,
  limits: {
    fileSize: parseInt(process.env.MAX_FILE_SIZE) || 2147483648, // 2GB por defecto
    files: 10 // Máximo 10 archivos por request
  }
});

/**
 * POST /api/v1/files/upload
 * Subir uno o múltiples archivos
 */
router.post('/upload', upload.array('files', 10), asyncHandler(async (req, res) => {
  if (!req.files || req.files.length === 0) {
    throw new AppError('No se proporcionaron archivos', 400, 'NO_FILES');
  }

  const { groupId, category, tags, description } = req.body;
  const uploadedFiles = [];

  try {
    for (const file of req.files) {
      // Generar hash del archivo
      const fileBuffer = await fs.readFile(file.path);
      const hash = crypto.createHash('sha256').update(fileBuffer).digest('hex');

      // Verificar si el archivo ya existe (por hash)
      const existingFile = await File.findOne({ hash });
      if (existingFile) {
        // Eliminar archivo duplicado
        await fs.unlink(file.path);
        throw new AppError(`El archivo ${file.originalname} ya existe en el sistema`, 409, 'DUPLICATE_FILE');
      }

      // Crear registro en la base de datos
      const newFile = new File({
        originalName: file.originalname,
        filename: file.filename,
        mimetype: file.mimetype,
        size: file.size,
        extension: path.extname(file.originalname).toLowerCase().slice(1),
        path: file.path,
        url: `/api/v1/files/download/${file.filename}`,
        hash: hash,
        category: category || 'document',
        tags: tags ? tags.split(',').map(tag => tag.trim()) : [],
        description: description || '',
        uploadedBy: req.user._id,
        group: groupId || null,
        status: 'ready'
      });

      await newFile.save();
      uploadedFiles.push(newFile);

      // Actualizar estadísticas del grupo si aplica
      if (groupId) {
        const Group = require('../models/Group');
        const group = await Group.findById(groupId);
        if (group) {
          await group.updateStats('totalFiles', 1);
        }
      }
    }

    res.status(201).json({
      message: `${uploadedFiles.length} archivo(s) subido(s) exitosamente`,
      files: uploadedFiles
    });

  } catch (error) {
    // Limpiar archivos si hay error
    for (const file of req.files) {
      try {
        await fs.unlink(file.path);
      } catch (unlinkError) {
        console.error('Error eliminando archivo:', unlinkError);
      }
    }
    throw error;
  }
}));

/**
 * GET /api/v1/files
 * Obtener lista de archivos con filtros y paginación
 */
router.get('/', asyncHandler(async (req, res) => {
  const {
    page = 1,
    limit = 20,
    category,
    groupId,
    search,
    sortBy = 'createdAt',
    sortOrder = 'desc'
  } = req.query;

  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    let fallbackFiles = getFallbackFiles();

    // Aplicar filtros a los datos de fallback
    if (category) {
      fallbackFiles = fallbackFiles.filter(file => file.category === category);
    }

    if (search) {
      const searchRegex = new RegExp(search, 'i');
      fallbackFiles = fallbackFiles.filter(file =>
        searchRegex.test(file.originalName) ||
        searchRegex.test(file.description) ||
        file.tags.some(tag => searchRegex.test(tag))
      );
    }

    // Filtro de permisos para usuarios no admin
    if (req.user.role !== 'admin') {
      fallbackFiles = fallbackFiles.filter(file =>
        file.uploadedBy === req.user._id ||
        file.isPublic ||
        file.accessLevel === 'internal'
      );
    }

    // Aplicar ordenamiento
    fallbackFiles.sort((a, b) => {
      const aVal = a[sortBy];
      const bVal = b[sortBy];
      if (sortOrder === 'desc') {
        return bVal > aVal ? 1 : -1;
      }
      return aVal > bVal ? 1 : -1;
    });

    // Aplicar paginación
    const skip = (page - 1) * limit;
    const paginatedFiles = fallbackFiles.slice(skip, skip + parseInt(limit));

    return res.json({
      files: paginatedFiles.map(file => ({
        ...file,
        uploadedBy: {
          _id: file.uploadedBy,
          firstName: file.uploadedBy === '507f1f77bcf86cd799439011' ? 'Admin' : 'Test',
          lastName: 'User',
          email: file.uploadedBy === '507f1f77bcf86cd799439011' ? 'admin@test.com' : 'user@test.com'
        }
      })),
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total: fallbackFiles.length,
        pages: Math.ceil(fallbackFiles.length / limit)
      },
      mode: 'development'
    });
  }

  // Construir filtros para MongoDB
  const filters = { deletedAt: null };

  if (category) filters.category = category;
  if (groupId) filters.group = groupId;

  // Filtro de búsqueda
  if (search) {
    filters.$or = [
      { originalName: { $regex: search, $options: 'i' } },
      { description: { $regex: search, $options: 'i' } },
      { tags: { $in: [new RegExp(search, 'i')] } }
    ];
  }

  // Solo mostrar archivos del usuario o de grupos donde es miembro (excepto admins)
  if (req.user.role !== 'admin') {
    const Group = require('../models/Group');
    const userGroups = await Group.find({ 'members.user': req.user._id }).select('_id');
    const groupIds = userGroups.map(group => group._id);

    filters.$or = [
      { uploadedBy: req.user._id },
      { group: { $in: groupIds } },
      { isPublic: true }
    ];
  }

  // Configurar paginación
  const skip = (parseInt(page) - 1) * parseInt(limit);
  const sort = { [sortBy]: sortOrder === 'desc' ? -1 : 1 };

  // Ejecutar consulta
  const files = await File.find(filters)
    .populate('uploadedBy', 'firstName lastName email')
    .populate('group', 'name type')
    .sort(sort)
    .skip(skip)
    .limit(parseInt(limit));

  const total = await File.countDocuments(filters);

  res.json({
    files,
    pagination: {
      page: parseInt(page),
      limit: parseInt(limit),
      total,
      pages: Math.ceil(total / parseInt(limit))
    }
  });
}));

/**
 * GET /api/v1/files/:id
 * Obtener información de un archivo específico
 */
router.get('/:id', asyncHandler(async (req, res) => {
  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    const fallbackFiles = getFallbackFiles();
    const file = fallbackFiles.find(f => f._id === req.params.id);

    if (!file) {
      throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
    }

    // Verificar permisos básicos
    const hasAccess = file.uploadedBy === req.user._id ||
      req.user.role === 'admin' ||
      file.isPublic ||
      file.accessLevel === 'internal';

    if (!hasAccess) {
      throw new AppError('No tienes permisos para ver este archivo', 403, 'ACCESS_DENIED');
    }

    return res.json({
      file: {
        ...file,
        uploadedBy: {
          _id: file.uploadedBy,
          firstName: file.uploadedBy === '507f1f77bcf86cd799439011' ? 'Admin' : 'Test',
          lastName: 'User',
          email: file.uploadedBy === '507f1f77bcf86cd799439011' ? 'admin@test.com' : 'user@test.com'
        }
      },
      mode: 'development'
    });
  }

  const file = await File.findById(req.params.id)
    .populate('uploadedBy', 'firstName lastName email')
    .populate('group', 'name type');

  if (!file || file.deletedAt) {
    throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
  }

  // Verificar permisos de acceso
  const hasAccess = req.user.role === 'admin' ||
    file.uploadedBy._id.toString() === req.user._id.toString() ||
    file.isPublic;

  if (!hasAccess && file.group) {
    const Group = require('../models/Group');
    const group = await Group.findById(file.group._id);
    if (!group || !group.isMember(req.user._id)) {
      throw new AppError('No tienes permisos para acceder a este archivo', 403, 'ACCESS_DENIED');
    }
  } else if (!hasAccess) {
    throw new AppError('No tienes permisos para acceder a este archivo', 403, 'ACCESS_DENIED');
  }

  // Registrar acceso
  await file.logAccess(req.user._id, 'view', req.ip, req.get('User-Agent'));

  res.json({ file });
}));

/**
 * GET /api/v1/files/download/:filename
 * Descargar archivo
 */
router.get('/download/:filename', asyncHandler(async (req, res) => {
  const file = await File.findOne({ filename: req.params.filename });

  if (!file || file.deletedAt) {
    throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
  }

  // Verificar permisos (similar al endpoint anterior)
  const hasAccess = req.user.role === 'admin' ||
    file.uploadedBy.toString() === req.user._id.toString() ||
    file.isPublic;

  if (!hasAccess && file.group) {
    const Group = require('../models/Group');
    const group = await Group.findById(file.group);
    if (!group || !group.isMember(req.user._id)) {
      throw new AppError('No tienes permisos para descargar este archivo', 403, 'ACCESS_DENIED');
    }
  } else if (!hasAccess) {
    throw new AppError('No tienes permisos para descargar este archivo', 403, 'ACCESS_DENIED');
  }

  // Verificar que el archivo existe físicamente
  try {
    await fs.access(file.path);
  } catch (error) {
    throw new AppError('Archivo no encontrado en el sistema de archivos', 404, 'FILE_NOT_FOUND_DISK');
  }

  // Registrar descarga
  await file.logAccess(req.user._id, 'download', req.ip, req.get('User-Agent'));

  // Configurar headers para descarga
  res.setHeader('Content-Disposition', `attachment; filename="${file.originalName}"`);
  res.setHeader('Content-Type', file.mimetype);

  // Enviar archivo
  res.sendFile(path.resolve(file.path));
}));

/**
 * PUT /api/v1/files/:id
 * Actualizar metadatos de un archivo
 */
router.put('/:id', [
  body('category').optional().isIn(['document', 'image', 'video', 'audio', 'evidence', 'report', 'legal', 'other']),
  body('tags').optional().isArray(),
  body('description').optional().isLength({ max: 1000 })
], requireOwnership(File), asyncHandler(async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new AppError('Datos de entrada inválidos', 400, 'VALIDATION_ERROR');
  }

  const { category, tags, description, isPublic } = req.body;
  const file = req.resource;

  // Actualizar campos permitidos
  if (category) file.category = category;
  if (tags) file.tags = tags;
  if (description !== undefined) file.description = description;
  if (isPublic !== undefined) file.isPublic = isPublic;

  await file.save();

  res.json({
    message: 'Archivo actualizado exitosamente',
    file
  });
}));

/**
 * DELETE /api/v1/files/:id
 * Eliminar archivo (soft delete)
 */
router.delete('/:id', requireOwnership(File), asyncHandler(async (req, res) => {
  const file = req.resource;

  // Soft delete
  await file.softDelete(req.user._id);

  res.json({
    message: 'Archivo eliminado exitosamente'
  });
}));

/**
 * GET /api/v1/files/stats/summary
 * Obtener estadísticas de archivos
 */
router.get('/stats/summary', asyncHandler(async (req, res) => {
  const userId = req.user._id;
  const isAdmin = req.user.role === 'admin';

  // Filtros base
  const baseFilter = { deletedAt: null };
  if (!isAdmin) {
    baseFilter.uploadedBy = userId;
  }

  // Estadísticas generales
  const totalFiles = await File.countDocuments(baseFilter);
  const totalSize = await File.aggregate([
    { $match: baseFilter },
    { $group: { _id: null, totalSize: { $sum: '$size' } } }
  ]);

  // Archivos por categoría
  const filesByCategory = await File.aggregate([
    { $match: baseFilter },
    { $group: { _id: '$category', count: { $sum: 1 } } }
  ]);

  // Archivos recientes (últimos 7 días)
  const recentFiles = await File.countDocuments({
    ...baseFilter,
    createdAt: { $gte: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000) }
  });

  res.json({
    totalFiles,
    totalSize: totalSize[0]?.totalSize || 0,
    filesByCategory,
    recentFiles
  });
}));

// Endpoint para descargar archivos
router.get('/:id/download', asyncHandler(async (req, res) => {
  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    const fallbackFiles = getFallbackFiles();
    const file = fallbackFiles.find(f => f._id === req.params.id);

    if (!file) {
      throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
    }

    // Verificar permisos básicos
    const hasAccess = file.uploadedBy === req.user._id ||
      req.user.role === 'admin' ||
      file.isPublic ||
      file.accessLevel === 'internal';

    if (!hasAccess) {
      throw new AppError('No tienes permisos para descargar este archivo', 403, 'ACCESS_DENIED');
    }

    // Para modo fallback, retornar información del archivo
    return res.json({
      message: 'Archivo disponible para descarga (modo desarrollo)',
      file: {
        filename: file.filename,
        originalName: file.originalName,
        size: file.size,
        mimeType: file.mimeType,
        downloadUrl: `/api/v1/files/${file._id}/download`
      },
      mode: 'development'
    });
  }

  const file = await File.findById(req.params.id);

  if (!file || file.deletedAt) {
    throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
  }

  // Verificar permisos de acceso
  const hasAccess = req.user.role === 'admin' ||
    file.uploadedBy.toString() === req.user._id.toString() ||
    file.isPublic;

  if (!hasAccess && file.group) {
    const Group = require('../models/Group');
    const group = await Group.findById(file.group);
    if (!group || !group.isMember(req.user._id)) {
      throw new AppError('No tienes permisos para descargar este archivo', 403, 'ACCESS_DENIED');
    }
  } else if (!hasAccess) {
    throw new AppError('No tienes permisos para descargar este archivo', 403, 'ACCESS_DENIED');
  }

  // Verificar que el archivo existe en el sistema de archivos
  const filePath = path.join(process.cwd(), file.path);

  if (!fs.existsSync(filePath)) {
    throw new AppError('Archivo no encontrado en el servidor', 404, 'FILE_NOT_FOUND_ON_DISK');
  }

  // Registrar descarga
  await file.logAccess(req.user._id, 'download', req.ip, req.get('User-Agent'));

  // Incrementar contador de descargas
  await File.findByIdAndUpdate(req.params.id, {
    $inc: { downloadCount: 1 },
    lastAccessed: new Date()
  });

  // Configurar headers para la descarga
  res.setHeader('Content-Disposition', `attachment; filename="${file.originalName}"`);
  res.setHeader('Content-Type', file.mimeType);
  res.setHeader('Content-Length', file.size);

  // Enviar archivo
  res.sendFile(filePath);
}));

// Endpoint para servir archivos (visualización)
router.get('/:id/view', asyncHandler(async (req, res) => {
  // FALLBACK: Si MongoDB no está disponible, usar datos hardcodeados
  const dbStatus = getDatabaseStatus();

  if (!dbStatus.mongodb.connected) {
    const fallbackFiles = getFallbackFiles();
    const file = fallbackFiles.find(f => f._id === req.params.id);

    if (!file) {
      throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
    }

    // Verificar permisos básicos
    const hasAccess = file.uploadedBy === req.user._id ||
      req.user.role === 'admin' ||
      file.isPublic ||
      file.accessLevel === 'internal';

    if (!hasAccess) {
      throw new AppError('No tienes permisos para ver este archivo', 403, 'ACCESS_DENIED');
    }

    // Para modo fallback, retornar información del archivo
    return res.json({
      message: 'Archivo disponible para visualización (modo desarrollo)',
      file: {
        filename: file.filename,
        originalName: file.originalName,
        size: file.size,
        mimeType: file.mimeType,
        viewUrl: `/api/v1/files/${file._id}/view`
      },
      mode: 'development'
    });
  }

  const file = await File.findById(req.params.id);

  if (!file || file.deletedAt) {
    throw new AppError('Archivo no encontrado', 404, 'FILE_NOT_FOUND');
  }

  // Verificar permisos de acceso
  const hasAccess = req.user.role === 'admin' ||
    file.uploadedBy.toString() === req.user._id.toString() ||
    file.isPublic;

  if (!hasAccess && file.group) {
    const Group = require('../models/Group');
    const group = await Group.findById(file.group);
    if (!group || !group.isMember(req.user._id)) {
      throw new AppError('No tienes permisos para ver este archivo', 403, 'ACCESS_DENIED');
    }
  } else if (!hasAccess) {
    throw new AppError('No tienes permisos para ver este archivo', 403, 'ACCESS_DENIED');
  }

  // Verificar que el archivo existe en el sistema de archivos
  const filePath = path.join(process.cwd(), file.path);

  if (!fs.existsSync(filePath)) {
    throw new AppError('Archivo no encontrado en el servidor', 404, 'FILE_NOT_FOUND_ON_DISK');
  }

  // Registrar visualización
  await file.logAccess(req.user._id, 'view', req.ip, req.get('User-Agent'));

  // Incrementar contador de vistas
  await File.findByIdAndUpdate(req.params.id, {
    $inc: { viewCount: 1 },
    lastAccessed: new Date()
  });

  // Configurar headers para la visualización
  res.setHeader('Content-Type', file.mimeType);
  res.setHeader('Content-Length', file.size);
  res.setHeader('Cache-Control', 'public, max-age=3600'); // Cache por 1 hora

  // Enviar archivo
  res.sendFile(filePath);
}));

module.exports = router;
