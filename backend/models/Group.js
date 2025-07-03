/**
 * Modelo de Grupo - Sistema de Gestión de Evidencias
 * Define la estructura para grupos de colaboración
 */

const mongoose = require('mongoose');

const groupSchema = new mongoose.Schema({
  // Información básica del grupo
  name: {
    type: String,
    required: [true, 'El nombre del grupo es requerido'],
    trim: true,
    maxlength: [100, 'El nombre no puede exceder 100 caracteres']
  },
  
  description: {
    type: String,
    trim: true,
    maxlength: [500, 'La descripción no puede exceder 500 caracteres']
  },
  
  // Configuración del grupo
  type: {
    type: String,
    enum: ['investigation', 'analysis', 'review', 'collaboration', 'project'],
    default: 'collaboration'
  },
  
  status: {
    type: String,
    enum: ['active', 'inactive', 'archived', 'suspended'],
    default: 'active'
  },
  
  privacy: {
    type: String,
    enum: ['public', 'private', 'restricted'],
    default: 'private'
  },
  
  // Miembros del grupo
  members: [{
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: true
    },
    role: {
      type: String,
      enum: ['owner', 'admin', 'moderator', 'member', 'viewer'],
      default: 'member'
    },
    joinedAt: {
      type: Date,
      default: Date.now
    },
    invitedBy: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User'
    },
    permissions: {
      canInvite: {
        type: Boolean,
        default: false
      },
      canRemove: {
        type: Boolean,
        default: false
      },
      canUpload: {
        type: Boolean,
        default: true
      },
      canDownload: {
        type: Boolean,
        default: true
      },
      canEdit: {
        type: Boolean,
        default: false
      },
      canDelete: {
        type: Boolean,
        default: false
      }
    }
  }],
  
  // Configuración de permisos
  defaultPermissions: {
    canInvite: {
      type: Boolean,
      default: false
    },
    canRemove: {
      type: Boolean,
      default: false
    },
    canUpload: {
      type: Boolean,
      default: true
    },
    canDownload: {
      type: Boolean,
      default: true
    },
    canEdit: {
      type: Boolean,
      default: false
    },
    canDelete: {
      type: Boolean,
      default: false
    }
  },
  
  // Información del creador
  createdBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, 'El creador del grupo es requerido']
  },
  
  // Configuración de notificaciones
  notifications: {
    newMember: {
      type: Boolean,
      default: true
    },
    newFile: {
      type: Boolean,
      default: true
    },
    newMessage: {
      type: Boolean,
      default: true
    },
    memberLeft: {
      type: Boolean,
      default: true
    }
  },
  
  // Estadísticas del grupo
  stats: {
    totalFiles: {
      type: Number,
      default: 0
    },
    totalMessages: {
      type: Number,
      default: 0
    },
    totalEvidences: {
      type: Number,
      default: 0
    },
    lastActivity: {
      type: Date,
      default: Date.now
    }
  },
  
  // Configuración de archivos
  fileSettings: {
    maxFileSize: {
      type: Number,
      default: 2147483648 // 2GB
    },
    allowedFileTypes: [{
      type: String,
      lowercase: true
    }],
    requireApproval: {
      type: Boolean,
      default: false
    },
    autoDeleteAfterDays: {
      type: Number,
      default: null
    }
  },
  
  // Tags y categorización
  tags: [{
    type: String,
    trim: true,
    maxlength: [50, 'Cada tag no puede exceder 50 caracteres']
  }],
  
  category: {
    type: String,
    enum: ['legal', 'forensic', 'investigation', 'analysis', 'research', 'other'],
    default: 'other'
  },
  
  // Información de proyecto (si aplica)
  project: {
    startDate: {
      type: Date,
      default: null
    },
    endDate: {
      type: Date,
      default: null
    },
    budget: {
      type: Number,
      default: null
    },
    priority: {
      type: String,
      enum: ['low', 'medium', 'high', 'critical'],
      default: 'medium'
    }
  },
  
  // Configuración de acceso
  accessCode: {
    type: String,
    default: null,
    select: false
  },
  
  inviteOnly: {
    type: Boolean,
    default: true
  },
  
  // Información de archivado/eliminación
  archivedAt: {
    type: Date,
    default: null
  },
  
  archivedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    default: null
  },
  
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

// Índices para optimización
groupSchema.index({ createdBy: 1 });
groupSchema.index({ 'members.user': 1 });
groupSchema.index({ status: 1 });
groupSchema.index({ type: 1 });
groupSchema.index({ category: 1 });
groupSchema.index({ tags: 1 });
groupSchema.index({ createdAt: -1 });

// Virtual para contar miembros activos
groupSchema.virtual('memberCount').get(function() {
  return this.members ? this.members.length : 0;
});

// Virtual para obtener propietarios
groupSchema.virtual('owners').get(function() {
  return this.members ? this.members.filter(member => member.role === 'owner') : [];
});

// Virtual para obtener administradores
groupSchema.virtual('admins').get(function() {
  return this.members ? this.members.filter(member => 
    member.role === 'owner' || member.role === 'admin'
  ) : [];
});

// Método para verificar si un usuario es miembro
groupSchema.methods.isMember = function(userId) {
  return this.members.some(member => 
    member.user.toString() === userId.toString()
  );
};

// Método para obtener el rol de un usuario
groupSchema.methods.getUserRole = function(userId) {
  const member = this.members.find(member => 
    member.user.toString() === userId.toString()
  );
  return member ? member.role : null;
};

// Método para verificar permisos de un usuario
groupSchema.methods.hasPermission = function(userId, permission) {
  const member = this.members.find(member => 
    member.user.toString() === userId.toString()
  );
  
  if (!member) return false;
  
  // Los propietarios y admins tienen todos los permisos
  if (member.role === 'owner' || member.role === 'admin') {
    return true;
  }
  
  return member.permissions[permission] || false;
};

// Método para agregar miembro
groupSchema.methods.addMember = function(userId, role = 'member', invitedBy = null) {
  // Verificar si ya es miembro
  if (this.isMember(userId)) {
    throw new Error('El usuario ya es miembro del grupo');
  }
  
  this.members.push({
    user: userId,
    role: role,
    invitedBy: invitedBy,
    permissions: { ...this.defaultPermissions }
  });
  
  return this.save();
};

// Método para remover miembro
groupSchema.methods.removeMember = function(userId) {
  this.members = this.members.filter(member => 
    member.user.toString() !== userId.toString()
  );
  
  return this.save();
};

// Método para actualizar rol de miembro
groupSchema.methods.updateMemberRole = function(userId, newRole) {
  const member = this.members.find(member => 
    member.user.toString() === userId.toString()
  );
  
  if (!member) {
    throw new Error('Usuario no encontrado en el grupo');
  }
  
  member.role = newRole;
  return this.save();
};

// Método para actualizar estadísticas
groupSchema.methods.updateStats = function(type, increment = 1) {
  if (this.stats[type] !== undefined) {
    this.stats[type] += increment;
  }
  this.stats.lastActivity = new Date();
  return this.save();
};

// Query helper para grupos activos
groupSchema.query.active = function() {
  return this.where({ status: 'active', deletedAt: null });
};

// Query helper para grupos de un usuario
groupSchema.query.byUser = function(userId) {
  return this.where({ 'members.user': userId });
};

module.exports = mongoose.model('Group', groupSchema);
