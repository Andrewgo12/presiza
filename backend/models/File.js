/**
 * Modelo de Archivo - Sistema de Gestión de Evidencias
 * Define la estructura para archivos y documentos del sistema
 */

const mongoose = require('mongoose');

const fileSchema = new mongoose.Schema({
  // Información básica del archivo
  originalName: {
    type: String,
    required: [true, 'El nombre original del archivo es requerido'],
    trim: true,
    maxlength: [255, 'El nombre del archivo no puede exceder 255 caracteres']
  },

  filename: {
    type: String,
    required: [true, 'El nombre del archivo es requerido'],
    unique: true
  },

  mimetype: {
    type: String,
    required: [true, 'El tipo MIME es requerido']
  },

  size: {
    type: Number,
    required: [true, 'El tamaño del archivo es requerido'],
    max: [2147483648, 'El archivo no puede exceder 2GB'] // 2GB en bytes
  },

  extension: {
    type: String,
    required: [true, 'La extensión del archivo es requerida'],
    lowercase: true
  },

  // Ubicación y almacenamiento
  path: {
    type: String,
    required: [true, 'La ruta del archivo es requerida']
  },

  url: {
    type: String,
    required: [true, 'La URL del archivo es requerida']
  },

  storageType: {
    type: String,
    enum: ['local', 's3', 'azure', 'gcp'],
    default: 'local'
  },

  // Metadatos del archivo
  hash: {
    type: String,
    required: [true, 'El hash del archivo es requerido'],
    unique: true
  },

  encoding: {
    type: String,
    default: 'utf8'
  },

  // Información de seguridad
  isEncrypted: {
    type: Boolean,
    default: false
  },

  encryptionKey: {
    type: String,
    select: false // No incluir en consultas por defecto
  },

  // Clasificación y categorización
  category: {
    type: String,
    enum: [
      'document', 'image', 'video', 'audio',
      'evidence', 'report', 'legal', 'other'
    ],
    default: 'document'
  },

  tags: [{
    type: String,
    trim: true,
    maxlength: [50, 'Cada tag no puede exceder 50 caracteres']
  }],

  description: {
    type: String,
    trim: true,
    maxlength: [1000, 'La descripción no puede exceder 1000 caracteres']
  },

  // Relaciones
  uploadedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, 'El usuario que subió el archivo es requerido']
  },

  group: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Group',
    default: null
  },

  evidence: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Evidence',
    default: null
  },

  // Estado del archivo
  status: {
    type: String,
    enum: ['uploading', 'processing', 'ready', 'error', 'deleted'],
    default: 'uploading'
  },

  isPublic: {
    type: Boolean,
    default: false
  },

  // Información de procesamiento
  processingStatus: {
    thumbnailGenerated: {
      type: Boolean,
      default: false
    },
    virusScanned: {
      type: Boolean,
      default: false
    },
    virusScanResult: {
      type: String,
      enum: ['clean', 'infected', 'suspicious', 'pending'],
      default: 'pending'
    },
    textExtracted: {
      type: Boolean,
      default: false
    },
    metadataExtracted: {
      type: Boolean,
      default: false
    }
  },

  // Metadatos extraídos
  extractedMetadata: {
    type: mongoose.Schema.Types.Mixed,
    default: {}
  },

  extractedText: {
    type: String,
    default: null
  },

  // Información de acceso
  downloadCount: {
    type: Number,
    default: 0
  },

  lastAccessed: {
    type: Date,
    default: null
  },

  accessLog: [{
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User'
    },
    action: {
      type: String,
      enum: ['view', 'download', 'edit', 'delete']
    },
    timestamp: {
      type: Date,
      default: Date.now
    },
    ipAddress: String,
    userAgent: String
  }],

  // Versionado
  version: {
    type: Number,
    default: 1
  },

  parentFile: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'File',
    default: null
  },

  // Información de eliminación
  deletedAt: {
    type: Date,
    default: null
  },

  deletedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    default: null
  }
}, {
  timestamps: true,
  toJSON: { virtuals: true },
  toObject: { virtuals: true }
});

// Índices para optimización (hash ya tiene índice único)
fileSchema.index({ uploadedBy: 1 });
fileSchema.index({ group: 1 });
fileSchema.index({ evidence: 1 });
fileSchema.index({ status: 1 });
fileSchema.index({ category: 1 });
fileSchema.index({ createdAt: -1 });
fileSchema.index({ tags: 1 });

// Virtual para obtener el tamaño formateado
fileSchema.virtual('formattedSize').get(function () {
  const bytes = this.size;
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
});

// Virtual para verificar si es una imagen
fileSchema.virtual('isImage').get(function () {
  const imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
  return imageTypes.includes(this.mimetype);
});

// Virtual para verificar si es un video
fileSchema.virtual('isVideo').get(function () {
  const videoTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv'];
  return videoTypes.includes(this.mimetype);
});

// Virtual para verificar si es un audio
fileSchema.virtual('isAudio').get(function () {
  const audioTypes = ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'];
  return audioTypes.includes(this.mimetype);
});

// Método para registrar acceso al archivo
fileSchema.methods.logAccess = function (userId, action, ipAddress, userAgent) {
  this.accessLog.push({
    user: userId,
    action: action,
    timestamp: new Date(),
    ipAddress: ipAddress,
    userAgent: userAgent
  });

  this.lastAccessed = new Date();

  if (action === 'download') {
    this.downloadCount += 1;
  }

  return this.save();
};

// Método para marcar como eliminado (soft delete)
fileSchema.methods.softDelete = function (userId) {
  this.deletedAt = new Date();
  this.deletedBy = userId;
  this.status = 'deleted';
  return this.save();
};

// Método para restaurar archivo eliminado
fileSchema.methods.restore = function () {
  this.deletedAt = null;
  this.deletedBy = null;
  this.status = 'ready';
  return this.save();
};

// Query helper para archivos no eliminados
fileSchema.query.notDeleted = function () {
  return this.where({ deletedAt: null });
};

module.exports = mongoose.model('File', fileSchema);
