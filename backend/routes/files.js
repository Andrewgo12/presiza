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

  // Construir filtros
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

module.exports = router;
