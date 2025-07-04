/**
 * =====================================================
 * EVIDENCE MANAGEMENT SYSTEM - MongoDB Schema
 * Base de Datos: MongoDB Atlas (Principal)
 * Propósito: Usuarios, Archivos, Grupos, Mensajes, Evidencias, Notificaciones
 * =====================================================
 */

const mongoose = require('mongoose');

// =====================================================
// SCHEMA: Users
// Propósito: Gestión de usuarios del sistema
// Usado por: LoginView, ProfileView, AdminGroupsView, HomeView
// =====================================================
const userSchema = new mongoose.Schema({
  // Información básica
  email: {
    type: String,
    required: [true, 'El email es requerido'],
    unique: true,
    lowercase: true,
    trim: true,
    match: [/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/, 'Email inválido']
  },

  password: {
    type: String,
    required: [true, 'La contraseña es requerida'],
    minlength: [6, 'La contraseña debe tener al menos 6 caracteres'],
    select: false // No incluir en consultas por defecto
  },

  // Información personal
  firstName: {
    type: String,
    required: [true, 'El nombre es requerido'],
    trim: true,
    maxlength: [50, 'El nombre no puede exceder 50 caracteres']
  },

  lastName: {
    type: String,
    required: [true, 'El apellido es requerido'],
    trim: true,
    maxlength: [50, 'El apellido no puede exceder 50 caracteres']
  },

  // Información profesional
  role: {
    type: String,
    enum: {
      values: ['admin', 'user', 'analyst', 'investigator'],
      message: 'Rol inválido'
    },
    default: 'user'
  },

  department: {
    type: String,
    trim: true,
    maxlength: [100, 'El departamento no puede exceder 100 caracteres']
  },

  position: {
    type: String,
    trim: true,
    maxlength: [100, 'El cargo no puede exceder 100 caracteres']
  },

  // Configuración de cuenta
  isActive: {
    type: Boolean,
    default: true
  },

  avatar: {
    type: String, // URL del avatar
    default: null
  },

  // Configuración de notificaciones
  notificationSettings: {
    email: { type: Boolean, default: true },
    push: { type: Boolean, default: true },
    sms: { type: Boolean, default: false }
  },

  // Configuración de privacidad
  privacySettings: {
    profileVisible: { type: Boolean, default: true },
    emailVisible: { type: Boolean, default: false },
    lastSeenVisible: { type: Boolean, default: true }
  },

  // Metadatos
  lastLogin: Date,
  loginCount: { type: Number, default: 0 },
  createdAt: { type: Date, default: Date.now },
  updatedAt: { type: Date, default: Date.now }
}, {
  timestamps: true,
  toJSON: { virtuals: true },
  toObject: { virtuals: true }
});

// Índices para optimización (email ya tiene índice único)
userSchema.index({ role: 1 });
userSchema.index({ department: 1 });
userSchema.index({ isActive: 1 });
userSchema.index({ createdAt: -1 });

// =====================================================
// SCHEMA: Files
// Propósito: Gestión de archivos del sistema
// Usado por: FilesView, UploadView, EvidencesView
// =====================================================
const fileSchema = new mongoose.Schema({
  // Información básica del archivo
  filename: {
    type: String,
    required: [true, 'El nombre del archivo es requerido'],
    trim: true
  },

  originalName: {
    type: String,
    required: [true, 'El nombre original es requerido']
  },

  mimeType: {
    type: String,
    required: [true, 'El tipo MIME es requerido']
  },

  size: {
    type: Number,
    required: [true, 'El tamaño del archivo es requerido'],
    min: [0, 'El tamaño no puede ser negativo']
  },

  // Ubicación y almacenamiento
  path: {
    type: String,
    required: [true, 'La ruta del archivo es requerida']
  },

  url: String, // URL pública si está disponible

  // Metadatos del archivo
  checksum: String, // Hash MD5 o SHA256 para verificación de integridad

  // Información del propietario
  uploadedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, 'El usuario que subió el archivo es requerido']
  },

  // Categorización
  category: {
    type: String,
    enum: ['document', 'image', 'video', 'audio', 'archive', 'other'],
    default: 'other'
  },

  tags: [String],

  // Control de acceso
  isPublic: {
    type: Boolean,
    default: false
  },

  accessLevel: {
    type: String,
    enum: ['public', 'internal', 'restricted', 'confidential'],
    default: 'internal'
  },

  // Estado del archivo
  status: {
    type: String,
    enum: ['active', 'archived', 'deleted'],
    default: 'active'
  },

  // Metadatos adicionales
  description: String,
  version: { type: Number, default: 1 },

  // Estadísticas
  downloadCount: { type: Number, default: 0 },
  viewCount: { type: Number, default: 0 },

  // Fechas importantes
  lastAccessed: Date,
  expiresAt: Date, // Para archivos temporales

  createdAt: { type: Date, default: Date.now },
  updatedAt: { type: Date, default: Date.now }
}, {
  timestamps: true
});

// Índices para optimización
fileSchema.index({ uploadedBy: 1 });
fileSchema.index({ category: 1 });
fileSchema.index({ status: 1 });
fileSchema.index({ createdAt: -1 });
fileSchema.index({ filename: 'text', originalName: 'text', description: 'text' });

// =====================================================
// SCHEMA: Groups
// Propósito: Gestión de grupos y equipos
// Usado por: GroupsView, AdminGroupsView, MessagesView
// =====================================================
const groupSchema = new mongoose.Schema({
  // Información básica
  name: {
    type: String,
    required: [true, 'El nombre del grupo es requerido'],
    trim: true,
    maxlength: [100, 'El nombre no puede exceder 100 caracteres']
  },

  description: {
    type: String,
    maxlength: [500, 'La descripción no puede exceder 500 caracteres']
  },

  // Tipo y configuración
  type: {
    type: String,
    enum: ['public', 'private', 'protected'],
    default: 'public'
  },

  category: {
    type: String,
    enum: ['project', 'department', 'team', 'research', 'general'],
    default: 'general'
  },

  // Miembros
  members: [{
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: true
    },
    role: {
      type: String,
      enum: ['owner', 'admin', 'moderator', 'member'],
      default: 'member'
    },
    joinedAt: {
      type: Date,
      default: Date.now
    },
    permissions: {
      canInvite: { type: Boolean, default: false },
      canRemove: { type: Boolean, default: false },
      canEdit: { type: Boolean, default: false }
    }
  }],

  // Configuración del grupo
  settings: {
    maxMembers: { type: Number, default: 100 },
    allowInvites: { type: Boolean, default: true },
    requireApproval: { type: Boolean, default: false },
    isArchived: { type: Boolean, default: false }
  },

  // Metadatos
  avatar: String, // URL del avatar del grupo
  location: String,
  website: String,

  // Estadísticas
  messageCount: { type: Number, default: 0 },
  fileCount: { type: Number, default: 0 },

  // Fechas importantes
  lastActivity: { type: Date, default: Date.now },
  createdAt: { type: Date, default: Date.now },
  updatedAt: { type: Date, default: Date.now }
}, {
  timestamps: true
});

// Índices para optimización
groupSchema.index({ type: 1 });
groupSchema.index({ category: 1 });
groupSchema.index({ 'members.user': 1 });
groupSchema.index({ lastActivity: -1 });
groupSchema.index({ name: 'text', description: 'text' });

// =====================================================
// SCHEMA: Messages
// Propósito: Sistema de mensajería
// Usado por: MessagesView, GroupsView
// =====================================================
const messageSchema = new mongoose.Schema({
  // Información básica
  content: {
    type: String,
    required: [true, 'El contenido del mensaje es requerido'],
    maxlength: [2000, 'El mensaje no puede exceder 2000 caracteres']
  },

  // Remitente
  sender: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, 'El remitente es requerido']
  },

  // Destinatario (puede ser usuario o grupo)
  recipient: {
    type: mongoose.Schema.Types.ObjectId,
    refPath: 'recipientType',
    required: [true, 'El destinatario es requerido']
  },

  recipientType: {
    type: String,
    enum: ['User', 'Group'],
    required: [true, 'El tipo de destinatario es requerido']
  },

  // Tipo de mensaje
  messageType: {
    type: String,
    enum: ['text', 'file', 'image', 'system'],
    default: 'text'
  },

  // Archivos adjuntos
  attachments: [{
    file: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'File'
    },
    filename: String,
    size: Number,
    mimeType: String
  }],

  // Estado del mensaje
  status: {
    type: String,
    enum: ['sent', 'delivered', 'read'],
    default: 'sent'
  },

  // Metadatos
  isEdited: { type: Boolean, default: false },
  editedAt: Date,

  // Respuesta a otro mensaje
  replyTo: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Message'
  },

  // Fechas importantes
  readAt: Date,
  deliveredAt: Date,
  createdAt: { type: Date, default: Date.now }
}, {
  timestamps: true
});

// Índices para optimización
messageSchema.index({ sender: 1 });
messageSchema.index({ recipient: 1, recipientType: 1 });
messageSchema.index({ createdAt: -1 });
messageSchema.index({ status: 1 });

// =====================================================
// SCHEMA: Evidences
// Propósito: Gestión de evidencias y documentos
// Usado por: EvidencesView, TasksView, AnalyticsView
// =====================================================
const evidenceSchema = new mongoose.Schema({
  // Información básica
  title: {
    type: String,
    required: [true, 'El título es requerido'],
    trim: true,
    maxlength: [200, 'El título no puede exceder 200 caracteres']
  },

  description: {
    type: String,
    required: [true, 'La descripción es requerida'],
    maxlength: [1000, 'La descripción no puede exceder 1000 caracteres']
  },

  // Clasificación
  evidenceType: {
    type: String,
    enum: ['document', 'image', 'video', 'audio', 'data', 'testimony', 'physical'],
    required: [true, 'El tipo de evidencia es requerido']
  },

  category: {
    type: String,
    enum: ['investigation', 'research', 'audit', 'compliance', 'legal', 'other'],
    default: 'other'
  },

  // Archivos asociados
  files: [{
    type: mongoose.Schema.Types.ObjectId,
    ref: 'File'
  }],

  // Información del autor
  submittedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, 'El usuario que envió la evidencia es requerido']
  },

  // Proceso de revisión
  status: {
    type: String,
    enum: ['pending', 'under_review', 'approved', 'rejected', 'requires_changes'],
    default: 'pending'
  },

  reviewedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User'
  },

  reviewDate: Date,

  feedback: String,

  // Metadatos
  tags: [String],
  priority: {
    type: String,
    enum: ['low', 'medium', 'high', 'critical'],
    default: 'medium'
  },

  // Información del proyecto/caso
  project: String,
  caseNumber: String,

  // Fechas importantes
  incidentDate: Date,
  submissionDate: { type: Date, default: Date.now },
  dueDate: Date,

  // Comentarios y notas
  comments: [{
    author: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: true
    },
    content: {
      type: String,
      required: true,
      maxlength: [500, 'El comentario no puede exceder 500 caracteres']
    },
    createdAt: {
      type: Date,
      default: Date.now
    }
  }],

  // Control de versiones
  version: { type: Number, default: 1 },

  createdAt: { type: Date, default: Date.now },
  updatedAt: { type: Date, default: Date.now }
}, {
  timestamps: true
});

// Índices para optimización
evidenceSchema.index({ submittedBy: 1 });
evidenceSchema.index({ status: 1 });
evidenceSchema.index({ evidenceType: 1 });
evidenceSchema.index({ category: 1 });
evidenceSchema.index({ priority: 1 });
evidenceSchema.index({ submissionDate: -1 });
evidenceSchema.index({ title: 'text', description: 'text', tags: 'text' });

// =====================================================
// SCHEMA: Notifications
// Propósito: Sistema de notificaciones
// Usado por: NotificationsView, HomeView
// =====================================================
const notificationSchema = new mongoose.Schema({
  // Información básica
  title: {
    type: String,
    required: [true, 'El título es requerido'],
    maxlength: [100, 'El título no puede exceder 100 caracteres']
  },

  message: {
    type: String,
    required: [true, 'El mensaje es requerido'],
    maxlength: [500, 'El mensaje no puede exceder 500 caracteres']
  },

  // Destinatario
  recipient: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, 'El destinatario es requerido']
  },

  // Tipo de notificación
  type: {
    type: String,
    enum: ['info', 'success', 'warning', 'error', 'system', 'reminder'],
    default: 'info'
  },

  category: {
    type: String,
    enum: ['upload', 'comment', 'task', 'system', 'group', 'evidence', 'message'],
    required: [true, 'La categoría es requerida']
  },

  // Estado
  isRead: {
    type: Boolean,
    default: false
  },

  readAt: Date,

  // Acción relacionada
  actionUrl: String, // URL para redirigir al hacer click

  relatedResource: {
    resourceType: {
      type: String,
      enum: ['User', 'File', 'Group', 'Message', 'Evidence']
    },
    resourceId: mongoose.Schema.Types.ObjectId
  },

  // Metadatos
  priority: {
    type: String,
    enum: ['low', 'normal', 'high'],
    default: 'normal'
  },

  // Configuración de entrega
  deliveryMethods: {
    inApp: { type: Boolean, default: true },
    email: { type: Boolean, default: false },
    push: { type: Boolean, default: false }
  },

  // Estado de entrega
  deliveryStatus: {
    inApp: { type: String, enum: ['pending', 'delivered', 'failed'], default: 'pending' },
    email: { type: String, enum: ['pending', 'delivered', 'failed'], default: 'pending' },
    push: { type: String, enum: ['pending', 'delivered', 'failed'], default: 'pending' }
  },

  // Fechas importantes
  scheduledFor: Date, // Para notificaciones programadas
  expiresAt: Date, // Para notificaciones temporales

  createdAt: { type: Date, default: Date.now }
}, {
  timestamps: true
});

// Índices para optimización
notificationSchema.index({ recipient: 1 });
notificationSchema.index({ type: 1 });
notificationSchema.index({ category: 1 });
notificationSchema.index({ isRead: 1 });
notificationSchema.index({ createdAt: -1 });
notificationSchema.index({ expiresAt: 1 }, { expireAfterSeconds: 0 }); // TTL index

// =====================================================
// CREAR MODELOS
// =====================================================
const User = mongoose.model('User', userSchema);
const File = mongoose.model('File', fileSchema);
const Group = mongoose.model('Group', groupSchema);
const Message = mongoose.model('Message', messageSchema);
const Evidence = mongoose.model('Evidence', evidenceSchema);
const Notification = mongoose.model('Notification', notificationSchema);

// =====================================================
// FUNCIONES DE INICIALIZACIÓN
// =====================================================

/**
 * Crear usuario administrador por defecto
 */
const createDefaultAdmin = async () => {
  try {
    // Verificar si ya existe un admin
    const existingAdmin = await User.findOne({ role: 'admin' });
    if (existingAdmin) {
      console.log('✅ Usuario administrador ya existe:', existingAdmin.email);
      return existingAdmin;
    }

    const bcrypt = require('bcryptjs');
    const hashedPassword = await bcrypt.hash('admin123', 12);

    // Crear usuario admin por defecto
    const adminUser = new User({
      email: 'admin@test.com',
      password: hashedPassword,
      firstName: 'Admin',
      lastName: 'User',
      role: 'admin',
      department: 'IT',
      position: 'System Administrator',
      isActive: true,
      notificationSettings: {
        email: true,
        push: true,
        sms: false
      },
      privacySettings: {
        profileVisible: true,
        emailVisible: false,
        lastSeenVisible: true
      }
    });

    await adminUser.save();
    console.log('✅ Usuario administrador creado:', adminUser.email);
    return adminUser;

  } catch (error) {
    console.error('❌ Error creando usuario administrador:', error);
    return null;
  }
};

/**
 * Crear usuarios de ejemplo - EXPANDIDO CON 12+ USUARIOS
 */
const createSampleUsers = async () => {
  try {
    const bcrypt = require('bcryptjs');

    const sampleUsers = [
      // Usuarios regulares
      {
        email: 'user@test.com',
        password: await bcrypt.hash('user123', 12),
        firstName: 'Test',
        lastName: 'User',
        role: 'user',
        department: 'General',
        position: 'User',
        isActive: true,
        createdAt: new Date('2024-01-15'),
        lastLogin: new Date('2024-07-03')
      },
      {
        email: 'maria.garcia@company.com',
        password: await bcrypt.hash('maria123', 12),
        firstName: 'María',
        lastName: 'García',
        role: 'user',
        department: 'HR',
        position: 'HR Specialist',
        isActive: true,
        createdAt: new Date('2024-02-10'),
        lastLogin: new Date('2024-07-04')
      },
      {
        email: 'carlos.rodriguez@company.com',
        password: await bcrypt.hash('carlos123', 12),
        firstName: 'Carlos',
        lastName: 'Rodríguez',
        role: 'user',
        department: 'Operations',
        position: 'Operations Coordinator',
        isActive: true,
        createdAt: new Date('2024-01-20'),
        lastLogin: new Date('2024-07-02')
      },
      {
        email: 'ana.martinez@company.com',
        password: await bcrypt.hash('ana123', 12),
        firstName: 'Ana',
        lastName: 'Martínez',
        role: 'user',
        department: 'Legal',
        position: 'Legal Assistant',
        isActive: true,
        createdAt: new Date('2024-03-05'),
        lastLogin: new Date('2024-07-01')
      },

      // Analistas
      {
        email: 'analyst@test.com',
        password: await bcrypt.hash('analyst123', 12),
        firstName: 'Data',
        lastName: 'Analyst',
        role: 'analyst',
        department: 'Analytics',
        position: 'Senior Analyst',
        isActive: true,
        createdAt: new Date('2023-12-01'),
        lastLogin: new Date('2024-07-04')
      },
      {
        email: 'sofia.lopez@company.com',
        password: await bcrypt.hash('sofia123', 12),
        firstName: 'Sofía',
        lastName: 'López',
        role: 'analyst',
        department: 'Research',
        position: 'Research Analyst',
        isActive: true,
        createdAt: new Date('2024-01-08'),
        lastLogin: new Date('2024-07-03')
      },
      {
        email: 'miguel.torres@company.com',
        password: await bcrypt.hash('miguel123', 12),
        firstName: 'Miguel',
        lastName: 'Torres',
        role: 'analyst',
        department: 'IT',
        position: 'Systems Analyst',
        isActive: true,
        createdAt: new Date('2024-02-15'),
        lastLogin: new Date('2024-07-04')
      },

      // Investigadores
      {
        email: 'investigator@test.com',
        password: await bcrypt.hash('investigator123', 12),
        firstName: 'John',
        lastName: 'Investigator',
        role: 'investigator',
        department: 'Investigation',
        position: 'Lead Investigator',
        isActive: true,
        createdAt: new Date('2023-11-15'),
        lastLogin: new Date('2024-07-04')
      },
      {
        email: 'laura.fernandez@company.com',
        password: await bcrypt.hash('laura123', 12),
        firstName: 'Laura',
        lastName: 'Fernández',
        role: 'investigator',
        department: 'Legal',
        position: 'Legal Investigator',
        isActive: true,
        createdAt: new Date('2024-01-12'),
        lastLogin: new Date('2024-07-02')
      },
      {
        email: 'david.sanchez@company.com',
        password: await bcrypt.hash('david123', 12),
        firstName: 'David',
        lastName: 'Sánchez',
        role: 'investigator',
        department: 'Investigation',
        position: 'Senior Investigator',
        isActive: true,
        createdAt: new Date('2023-12-20'),
        lastLogin: new Date('2024-07-03')
      },

      // Usuarios adicionales con diferentes estados
      {
        email: 'elena.morales@company.com',
        password: await bcrypt.hash('elena123', 12),
        firstName: 'Elena',
        lastName: 'Morales',
        role: 'user',
        department: 'Research',
        position: 'Research Assistant',
        isActive: true,
        createdAt: new Date('2024-03-20'),
        lastLogin: new Date('2024-06-15')
      },
      {
        email: 'inactive.user@company.com',
        password: await bcrypt.hash('inactive123', 12),
        firstName: 'Inactive',
        lastName: 'User',
        role: 'user',
        department: 'General',
        position: 'Former Employee',
        isActive: false,
        createdAt: new Date('2023-10-01'),
        lastLogin: new Date('2024-05-01')
      },
      {
        email: 'roberto.jimenez@company.com',
        password: await bcrypt.hash('roberto123', 12),
        firstName: 'Roberto',
        lastName: 'Jiménez',
        role: 'analyst',
        department: 'Operations',
        position: 'Operations Analyst',
        isActive: true,
        createdAt: new Date('2024-04-10'),
        lastLogin: new Date('2024-07-01')
      }
    ];

    for (const userData of sampleUsers) {
      const existingUser = await User.findOne({ email: userData.email });
      if (!existingUser) {
        const user = new User(userData);
        await user.save();
        console.log('✅ Usuario de ejemplo creado:', user.email);
      }
    }

  } catch (error) {
    console.error('❌ Error creando usuarios de ejemplo:', error);
  }
};

/**
 * Crear grupos de ejemplo - EXPANDIDO CON 8-10 GRUPOS
 */
const createSampleGroups = async () => {
  try {
    const users = await User.find({ isActive: true }).limit(10);

    if (users.length < 5) {
      console.log('⚠️ No hay suficientes usuarios para crear grupos');
      return;
    }

    const adminUser = users.find(u => u.role === 'admin') || users[0];
    const analysts = users.filter(u => u.role === 'analyst');
    const investigators = users.filter(u => u.role === 'investigator');
    const regularUsers = users.filter(u => u.role === 'user');

    const sampleGroups = [
      // Grupos de investigación
      {
        name: 'Research Team Alpha',
        description: 'Equipo principal de investigación y análisis de datos científicos',
        type: 'public',
        category: 'research',
        members: [
          { user: adminUser._id, role: 'owner', joinedAt: new Date('2024-01-01') },
          ...(analysts.slice(0, 2).map(u => ({ user: u._id, role: 'admin', joinedAt: new Date('2024-01-15') }))),
          ...(regularUsers.slice(0, 3).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-02-01') })))
        ],
        settings: {
          maxMembers: 50,
          allowInvites: true,
          requireApproval: false
        },
        messageCount: 45,
        fileCount: 12,
        lastActivity: new Date('2024-07-03')
      },

      // Grupos de desarrollo
      {
        name: 'Development Squad',
        description: 'Equipo de desarrollo de software y sistemas',
        type: 'private',
        category: 'team',
        members: [
          { user: adminUser._id, role: 'owner', joinedAt: new Date('2023-12-01') },
          ...(analysts.slice(0, 1).map(u => ({ user: u._id, role: 'admin', joinedAt: new Date('2024-01-10') }))),
          ...(regularUsers.slice(1, 2).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-02-15') })))
        ],
        settings: {
          maxMembers: 20,
          allowInvites: false,
          requireApproval: true
        },
        messageCount: 78,
        fileCount: 25,
        lastActivity: new Date('2024-07-04')
      },

      // Grupos de diseño
      {
        name: 'Design Collective',
        description: 'Grupo colaborativo de diseño y experiencia de usuario',
        type: 'protected',
        category: 'project',
        members: [
          { user: adminUser._id, role: 'admin', joinedAt: new Date('2024-01-05') },
          ...(regularUsers.slice(2, 4).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-02-20') })))
        ],
        settings: {
          maxMembers: 30,
          allowInvites: true,
          requireApproval: true
        },
        messageCount: 32,
        fileCount: 18,
        lastActivity: new Date('2024-07-02')
      },

      // Departamentos
      {
        name: 'Legal Department',
        description: 'Departamento legal - Asuntos jurídicos y compliance',
        type: 'private',
        category: 'department',
        members: [
          { user: investigators.find(u => u.department === 'Legal')?._id || adminUser._id, role: 'owner', joinedAt: new Date('2024-01-01') },
          ...(regularUsers.filter(u => u.department === 'Legal').slice(0, 2).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-01-20') })))
        ],
        settings: {
          maxMembers: 15,
          allowInvites: false,
          requireApproval: true
        },
        messageCount: 23,
        fileCount: 8,
        lastActivity: new Date('2024-06-30')
      },

      {
        name: 'IT Operations',
        description: 'Operaciones de tecnología y sistemas informáticos',
        type: 'public',
        category: 'department',
        members: [
          { user: adminUser._id, role: 'owner', joinedAt: new Date('2023-11-01') },
          ...(analysts.filter(u => u.department === 'IT').slice(0, 1).map(u => ({ user: u._id, role: 'admin', joinedAt: new Date('2024-01-01') }))),
          ...(regularUsers.filter(u => u.department === 'Operations').slice(0, 2).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-02-01') })))
        ],
        settings: {
          maxMembers: 25,
          allowInvites: true,
          requireApproval: false
        },
        messageCount: 67,
        fileCount: 15,
        lastActivity: new Date('2024-07-04')
      },

      // Proyectos específicos
      {
        name: 'Project Phoenix',
        description: 'Proyecto de modernización de sistemas legacy',
        type: 'protected',
        category: 'project',
        members: [
          { user: adminUser._id, role: 'owner', joinedAt: new Date('2024-03-01') },
          ...(analysts.slice(1, 2).map(u => ({ user: u._id, role: 'admin', joinedAt: new Date('2024-03-05') }))),
          ...(investigators.slice(0, 1).map(u => ({ user: u._id, role: 'moderator', joinedAt: new Date('2024-03-10') }))),
          ...(regularUsers.slice(0, 2).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-03-15') })))
        ],
        settings: {
          maxMembers: 40,
          allowInvites: true,
          requireApproval: true
        },
        messageCount: 89,
        fileCount: 22,
        lastActivity: new Date('2024-07-03')
      },

      {
        name: 'Data Analytics Hub',
        description: 'Centro de análisis de datos y business intelligence',
        type: 'public',
        category: 'research',
        members: [
          { user: analysts[0]?._id || adminUser._id, role: 'owner', joinedAt: new Date('2024-02-01') },
          ...(analysts.slice(1, 3).map(u => ({ user: u._id, role: 'admin', joinedAt: new Date('2024-02-10') }))),
          ...(regularUsers.slice(3, 5).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-02-20') })))
        ],
        settings: {
          maxMembers: 35,
          allowInvites: true,
          requireApproval: false
        },
        messageCount: 156,
        fileCount: 34,
        lastActivity: new Date('2024-07-04')
      },

      // Grupos generales
      {
        name: 'General Discussion',
        description: 'Espacio abierto para discusiones generales y anuncios',
        type: 'public',
        category: 'general',
        members: [
          { user: adminUser._id, role: 'owner', joinedAt: new Date('2023-12-01') },
          ...users.slice(1, 8).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-01-01') }))
        ],
        settings: {
          maxMembers: 100,
          allowInvites: true,
          requireApproval: false
        },
        messageCount: 234,
        fileCount: 45,
        lastActivity: new Date('2024-07-04')
      },

      {
        name: 'Training & Development',
        description: 'Grupo para capacitación y desarrollo profesional',
        type: 'public',
        category: 'general',
        members: [
          { user: regularUsers.find(u => u.department === 'HR')?._id || adminUser._id, role: 'owner', joinedAt: new Date('2024-01-15') },
          ...users.slice(2, 6).map(u => ({ user: u._id, role: 'member', joinedAt: new Date('2024-02-01') }))
        ],
        settings: {
          maxMembers: 60,
          allowInvites: true,
          requireApproval: false
        },
        messageCount: 67,
        fileCount: 28,
        lastActivity: new Date('2024-07-01')
      }
    ];

    for (const groupData of sampleGroups) {
      const existingGroup = await Group.findOne({ name: groupData.name });
      if (!existingGroup) {
        const group = new Group(groupData);
        await group.save();
        console.log('✅ Grupo de ejemplo creado:', group.name);
      }
    }

  } catch (error) {
    console.error('❌ Error creando grupos de ejemplo:', error);
  }
};

/**
 * Crear evidencias de ejemplo - EXPANDIDO CON 10-12 EVIDENCIAS
 */
const createSampleEvidences = async () => {
  try {
    const users = await User.find({ isActive: true }).limit(10);
    if (users.length === 0) return;

    const adminUser = users.find(u => u.role === 'admin') || users[0];
    const analysts = users.filter(u => u.role === 'analyst');
    const investigators = users.filter(u => u.role === 'investigator');
    const regularUsers = users.filter(u => u.role === 'user');

    const sampleEvidences = [
      // Evidencias aprobadas
      {
        title: 'Q4 Research Analysis Report',
        description: 'Comprehensive analysis of research data collected during Q4 2023, including statistical models and predictive analytics',
        evidenceType: 'document',
        category: 'research',
        submittedBy: analysts[0]?._id || users[0]._id,
        status: 'approved',
        priority: 'high',
        project: 'AI Research Initiative',
        caseNumber: 'RES-2024-001',
        tags: ['research', 'analysis', 'Q4', 'data', 'statistics'],
        reviewedBy: adminUser._id,
        reviewDate: new Date('2024-06-15'),
        feedback: 'Excellent work! The analysis is thorough and well-documented. Approved for publication.',
        submissionDate: new Date('2024-06-01'),
        incidentDate: new Date('2023-12-31'),
        comments: [
          {
            author: adminUser._id,
            content: 'Great statistical approach. The methodology is sound.',
            createdAt: new Date('2024-06-10')
          },
          {
            author: analysts[1]?._id || users[1]._id,
            content: 'The visualizations are very clear and helpful.',
            createdAt: new Date('2024-06-12')
          }
        ]
      },

      {
        title: 'Security Audit Findings',
        description: 'Complete security assessment of network infrastructure and identified vulnerabilities',
        evidenceType: 'document',
        category: 'audit',
        submittedBy: investigators[0]?._id || users[1]._id,
        status: 'approved',
        priority: 'critical',
        project: 'Security Enhancement',
        caseNumber: 'SEC-2024-003',
        tags: ['security', 'audit', 'vulnerabilities', 'network'],
        reviewedBy: adminUser._id,
        reviewDate: new Date('2024-06-20'),
        feedback: 'Critical findings addressed. Implementation plan approved.',
        submissionDate: new Date('2024-06-05'),
        incidentDate: new Date('2024-05-15'),
        comments: [
          {
            author: adminUser._id,
            content: 'Priority 1 vulnerabilities need immediate attention.',
            createdAt: new Date('2024-06-18')
          }
        ]
      },

      // Evidencias en revisión
      {
        title: 'User Interface Mockups v2.0',
        description: 'Updated UI/UX designs for the new dashboard interface with improved accessibility features',
        evidenceType: 'image',
        category: 'investigation',
        submittedBy: regularUsers[0]?._id || users[2]._id,
        status: 'under_review',
        priority: 'medium',
        project: 'Dashboard Redesign',
        caseNumber: 'UI-2024-007',
        tags: ['ui', 'ux', 'mockup', 'dashboard', 'accessibility'],
        submissionDate: new Date('2024-07-01'),
        incidentDate: new Date('2024-06-25'),
        comments: [
          {
            author: analysts[0]?._id || users[0]._id,
            content: 'The color scheme looks good, but consider contrast ratios.',
            createdAt: new Date('2024-07-02')
          }
        ]
      },

      {
        title: 'Performance Optimization Report',
        description: 'Analysis of system performance bottlenecks and proposed optimization strategies',
        evidenceType: 'document',
        category: 'research',
        submittedBy: analysts[1]?._id || users[3]._id,
        status: 'under_review',
        priority: 'high',
        project: 'System Performance',
        caseNumber: 'PERF-2024-012',
        tags: ['performance', 'optimization', 'bottlenecks', 'analysis'],
        submissionDate: new Date('2024-06-28'),
        incidentDate: new Date('2024-06-20'),
        comments: []
      },

      // Evidencias pendientes
      {
        title: 'Customer Feedback Analysis',
        description: 'Comprehensive analysis of customer feedback from Q2 2024 surveys and support tickets',
        evidenceType: 'data',
        category: 'research',
        submittedBy: regularUsers[1]?._id || users[4]._id,
        status: 'pending',
        priority: 'medium',
        project: 'Customer Experience',
        caseNumber: 'CX-2024-018',
        tags: ['customer', 'feedback', 'survey', 'analysis', 'Q2'],
        submissionDate: new Date('2024-07-03'),
        incidentDate: new Date('2024-06-30'),
        comments: []
      },

      {
        title: 'Compliance Training Video',
        description: 'Educational video content for new employee compliance training program',
        evidenceType: 'video',
        category: 'compliance',
        submittedBy: regularUsers[2]?._id || users[5]._id,
        status: 'pending',
        priority: 'low',
        project: 'HR Training Initiative',
        caseNumber: 'HR-2024-025',
        tags: ['compliance', 'training', 'video', 'education'],
        submissionDate: new Date('2024-07-04'),
        incidentDate: new Date('2024-07-01'),
        comments: []
      },

      // Evidencias rechazadas
      {
        title: 'Database Migration Script v1.0',
        description: 'Initial SQL scripts for migrating user data to new schema - requires optimization',
        evidenceType: 'document',
        category: 'audit',
        submittedBy: investigators[1]?._id || users[6]._id,
        status: 'rejected',
        priority: 'high',
        project: 'System Upgrade',
        caseNumber: 'DB-2024-009',
        tags: ['sql', 'migration', 'database', 'schema'],
        reviewedBy: adminUser._id,
        reviewDate: new Date('2024-06-25'),
        feedback: 'Script needs optimization. Please review the indexing strategy and add rollback procedures.',
        submissionDate: new Date('2024-06-10'),
        incidentDate: new Date('2024-06-05'),
        comments: [
          {
            author: adminUser._id,
            content: 'Missing error handling and transaction management.',
            createdAt: new Date('2024-06-22')
          },
          {
            author: investigators[1]?._id || users[6]._id,
            content: 'Working on the improvements. Will resubmit soon.',
            createdAt: new Date('2024-06-26')
          }
        ]
      },

      // Evidencias que requieren cambios
      {
        title: 'API Documentation Draft',
        description: 'Initial draft of REST API documentation for external developers',
        evidenceType: 'document',
        category: 'investigation',
        submittedBy: analysts[2]?._id || users[7]._id,
        status: 'requires_changes',
        priority: 'medium',
        project: 'API Development',
        caseNumber: 'API-2024-014',
        tags: ['api', 'documentation', 'rest', 'developers'],
        reviewedBy: adminUser._id,
        reviewDate: new Date('2024-06-30'),
        feedback: 'Good start, but needs more examples and error code documentation.',
        submissionDate: new Date('2024-06-20'),
        incidentDate: new Date('2024-06-15'),
        comments: [
          {
            author: adminUser._id,
            content: 'Add authentication examples and rate limiting info.',
            createdAt: new Date('2024-06-28')
          }
        ]
      },

      // Evidencias adicionales con diferentes tipos
      {
        title: 'Network Traffic Analysis',
        description: 'Audio recording of network monitoring session with anomaly detection results',
        evidenceType: 'audio',
        category: 'investigation',
        submittedBy: investigators[0]?._id || users[8]._id,
        status: 'approved',
        priority: 'high',
        project: 'Network Security',
        caseNumber: 'NET-2024-021',
        tags: ['network', 'traffic', 'monitoring', 'anomaly'],
        reviewedBy: adminUser._id,
        reviewDate: new Date('2024-06-18'),
        feedback: 'Valuable insights. Recommend implementing suggested monitoring rules.',
        submissionDate: new Date('2024-06-08'),
        incidentDate: new Date('2024-06-01'),
        comments: []
      },

      {
        title: 'Witness Interview Recording',
        description: 'Recorded testimony from key witness regarding the data breach incident',
        evidenceType: 'testimony',
        category: 'legal',
        submittedBy: investigators[1]?._id || users[9]._id,
        status: 'under_review',
        priority: 'critical',
        project: 'Incident Investigation',
        caseNumber: 'INC-2024-005',
        tags: ['witness', 'testimony', 'interview', 'breach'],
        submissionDate: new Date('2024-07-02'),
        incidentDate: new Date('2024-05-20'),
        dueDate: new Date('2024-07-10'),
        comments: [
          {
            author: adminUser._id,
            content: 'Reviewing for legal compliance before approval.',
            createdAt: new Date('2024-07-03')
          }
        ]
      },

      {
        title: 'Physical Evidence Catalog',
        description: 'Detailed catalog of physical evidence collected from the office break-in',
        evidenceType: 'physical',
        category: 'investigation',
        submittedBy: investigators[0]?._id || users[1]._id,
        status: 'pending',
        priority: 'high',
        project: 'Security Incident',
        caseNumber: 'PHY-2024-002',
        tags: ['physical', 'evidence', 'catalog', 'break-in'],
        submissionDate: new Date('2024-07-04'),
        incidentDate: new Date('2024-06-28'),
        dueDate: new Date('2024-07-15'),
        comments: []
      }
    ];

    for (const evidenceData of sampleEvidences) {
      const existingEvidence = await Evidence.findOne({ title: evidenceData.title });
      if (!existingEvidence) {
        const evidence = new Evidence(evidenceData);
        await evidence.save();
        console.log('✅ Evidencia de ejemplo creada:', evidence.title);
      }
    }

  } catch (error) {
    console.error('❌ Error creando evidencias de ejemplo:', error);
  }
};

/**
 * Crear notificaciones de ejemplo - EXPANDIDO CON 10-15 NOTIFICACIONES
 */
const createSampleNotifications = async () => {
  try {
    const users = await User.find({ isActive: true }).limit(10);
    if (users.length === 0) return;

    const adminUser = users.find(u => u.role === 'admin') || users[0];
    const analysts = users.filter(u => u.role === 'analyst');
    const investigators = users.filter(u => u.role === 'investigator');
    const regularUsers = users.filter(u => u.role === 'user');

    const sampleNotifications = [
      // Notificaciones de upload/evidencias
      {
        title: 'Evidence Approved',
        message: 'Your Q4 Research Analysis Report has been approved by the admin',
        recipient: analysts[0]?._id || users[0]._id,
        type: 'success',
        category: 'evidence',
        actionUrl: '/evidences',
        priority: 'normal',
        isRead: true,
        readAt: new Date('2024-06-16'),
        createdAt: new Date('2024-06-15'),
        deliveryMethods: { inApp: true, email: true, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'pending' }
      },

      {
        title: 'Evidence Requires Changes',
        message: 'Your API Documentation Draft needs revisions. Please check the feedback.',
        recipient: analysts[2]?._id || users[1]._id,
        type: 'warning',
        category: 'evidence',
        actionUrl: '/evidences/api-doc-draft',
        priority: 'high',
        isRead: false,
        createdAt: new Date('2024-06-30'),
        deliveryMethods: { inApp: true, email: true, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'delivered' }
      },

      {
        title: 'Evidence Rejected',
        message: 'Your Database Migration Script has been rejected. Please review the feedback and resubmit.',
        recipient: investigators[1]?._id || users[2]._id,
        type: 'error',
        category: 'evidence',
        actionUrl: '/evidences/db-migration-script',
        priority: 'high',
        isRead: true,
        readAt: new Date('2024-06-26'),
        createdAt: new Date('2024-06-25'),
        deliveryMethods: { inApp: true, email: true, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'delivered' }
      },

      // Notificaciones de comentarios
      {
        title: 'New Comment on Evidence',
        message: 'Admin commented on your Security Audit Findings: "Priority 1 vulnerabilities need immediate attention."',
        recipient: investigators[0]?._id || users[3]._id,
        type: 'info',
        category: 'comment',
        actionUrl: '/evidences/security-audit',
        priority: 'normal',
        isRead: true,
        readAt: new Date('2024-06-19'),
        createdAt: new Date('2024-06-18'),
        deliveryMethods: { inApp: true, email: false, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'delivered' }
      },

      {
        title: 'Comment Reply',
        message: 'Someone replied to your comment on the UI Mockups evidence.',
        recipient: analysts[0]?._id || users[4]._id,
        type: 'info',
        category: 'comment',
        actionUrl: '/evidences/ui-mockups',
        priority: 'normal',
        isRead: false,
        createdAt: new Date('2024-07-02'),
        deliveryMethods: { inApp: true, email: false, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'pending' }
      },

      // Notificaciones de grupos
      {
        title: 'Added to Group',
        message: 'You have been added to the "Data Analytics Hub" group.',
        recipient: regularUsers[0]?._id || users[5]._id,
        type: 'success',
        category: 'group',
        actionUrl: '/groups/data-analytics-hub',
        priority: 'normal',
        isRead: true,
        readAt: new Date('2024-02-21'),
        createdAt: new Date('2024-02-20'),
        deliveryMethods: { inApp: true, email: true, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'pending' }
      },

      {
        title: 'Group Role Updated',
        message: 'Your role in "Project Phoenix" has been updated to Moderator.',
        recipient: investigators[0]?._id || users[6]._id,
        type: 'info',
        category: 'group',
        actionUrl: '/groups/project-phoenix',
        priority: 'normal',
        isRead: false,
        createdAt: new Date('2024-03-12'),
        deliveryMethods: { inApp: true, email: true, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'pending' }
      },

      // Notificaciones de mensajes
      {
        title: 'New Message',
        message: 'You have a new message from Admin in the Research Team Alpha group.',
        recipient: analysts[1]?._id || users[7]._id,
        type: 'info',
        category: 'message',
        actionUrl: '/messages/research-team-alpha',
        priority: 'normal',
        isRead: true,
        readAt: new Date('2024-07-04'),
        createdAt: new Date('2024-07-03'),
        deliveryMethods: { inApp: true, email: false, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'delivered' }
      },

      {
        title: 'Direct Message',
        message: 'You received a direct message from Laura Fernández.',
        recipient: regularUsers[1]?._id || users[8]._id,
        type: 'info',
        category: 'message',
        actionUrl: '/messages/direct/laura-fernandez',
        priority: 'normal',
        isRead: false,
        createdAt: new Date('2024-07-04'),
        deliveryMethods: { inApp: true, email: false, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'delivered' }
      },

      // Notificaciones de sistema
      {
        title: 'System Maintenance Scheduled',
        message: 'Scheduled maintenance will occur tonight from 2-4 AM. Some services may be unavailable.',
        recipient: adminUser._id,
        type: 'warning',
        category: 'system',
        actionUrl: '/admin/maintenance',
        priority: 'high',
        isRead: true,
        readAt: new Date('2024-07-01'),
        createdAt: new Date('2024-06-30'),
        deliveryMethods: { inApp: true, email: true, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'delivered' }
      },

      {
        title: 'Database Backup Completed',
        message: 'Daily database backup completed successfully at 3:00 AM.',
        recipient: adminUser._id,
        type: 'success',
        category: 'system',
        actionUrl: '/admin/backups',
        priority: 'low',
        isRead: false,
        createdAt: new Date('2024-07-04'),
        deliveryMethods: { inApp: true, email: false, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'pending' }
      },

      {
        title: 'Security Alert',
        message: 'Multiple failed login attempts detected from IP 192.168.1.100.',
        recipient: adminUser._id,
        type: 'error',
        category: 'system',
        actionUrl: '/admin/security-logs',
        priority: 'high',
        isRead: true,
        readAt: new Date('2024-07-03'),
        createdAt: new Date('2024-07-03'),
        deliveryMethods: { inApp: true, email: true, push: true },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'delivered' }
      },

      // Notificaciones de tareas/recordatorios
      {
        title: 'Evidence Review Due',
        message: 'You have 3 evidence submissions pending review. Due date: July 10, 2024.',
        recipient: adminUser._id,
        type: 'reminder',
        category: 'task',
        actionUrl: '/evidences?status=pending',
        priority: 'high',
        isRead: false,
        createdAt: new Date('2024-07-04'),
        scheduledFor: new Date('2024-07-05'),
        deliveryMethods: { inApp: true, email: true, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'pending' }
      },

      {
        title: 'Weekly Report Due',
        message: 'Your weekly analytics report is due tomorrow. Please submit before 5 PM.',
        recipient: analysts[0]?._id || users[9]._id,
        type: 'reminder',
        category: 'task',
        actionUrl: '/reports/weekly',
        priority: 'normal',
        isRead: false,
        createdAt: new Date('2024-07-03'),
        deliveryMethods: { inApp: true, email: true, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'delivered', push: 'pending' }
      },

      // Notificaciones de upload/archivos
      {
        title: 'File Upload Successful',
        message: 'Your file "research_data_q2.xlsx" has been uploaded successfully.',
        recipient: regularUsers[2]?._id || users[1]._id,
        type: 'success',
        category: 'upload',
        actionUrl: '/files',
        priority: 'low',
        isRead: true,
        readAt: new Date('2024-07-01'),
        createdAt: new Date('2024-07-01'),
        deliveryMethods: { inApp: true, email: false, push: false },
        deliveryStatus: { inApp: 'delivered', email: 'pending', push: 'pending' }
      }
    ];

    for (const notificationData of sampleNotifications) {
      const notification = new Notification(notificationData);
      await notification.save();
      console.log('✅ Notificación de ejemplo creada:', notification.title);
    }

  } catch (error) {
    console.error('❌ Error creando notificaciones de ejemplo:', error);
  }
};

/**
 * Crear archivos de ejemplo - NUEVA FUNCIÓN
 */
const createSampleFiles = async () => {
  try {
    const users = await User.find({ isActive: true }).limit(10);
    if (users.length === 0) return;

    const adminUser = users.find(u => u.role === 'admin') || users[0];
    const analysts = users.filter(u => u.role === 'analyst');
    const investigators = users.filter(u => u.role === 'investigator');
    const regularUsers = users.filter(u => u.role === 'user');

    const sampleFiles = [
      // Documentos PDF
      {
        filename: 'research_analysis_q4_2023.pdf',
        originalName: 'Q4 Research Analysis Report.pdf',
        mimeType: 'application/pdf',
        size: 2048576, // 2MB
        path: '/uploads/2024/06/research_analysis_q4_2023.pdf',
        url: 'https://storage.company.com/files/research_analysis_q4_2023.pdf',
        checksum: 'a1b2c3d4e5f6789012345678901234567890abcd',
        uploadedBy: analysts[0]?._id || users[0]._id,
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
        createdAt: new Date('2024-06-01')
      },

      {
        filename: 'security_audit_findings.pdf',
        originalName: 'Security Audit Findings - June 2024.pdf',
        mimeType: 'application/pdf',
        size: 1536000, // 1.5MB
        path: '/uploads/2024/06/security_audit_findings.pdf',
        uploadedBy: investigators[0]?._id || users[1]._id,
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
        createdAt: new Date('2024-06-05')
      },

      // Imágenes
      {
        filename: 'ui_mockups_dashboard_v2.png',
        originalName: 'Dashboard UI Mockups v2.0.png',
        mimeType: 'image/png',
        size: 3145728, // 3MB
        path: '/uploads/2024/07/ui_mockups_dashboard_v2.png',
        uploadedBy: regularUsers[0]?._id || users[2]._id,
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
        createdAt: new Date('2024-07-01')
      },

      {
        filename: 'network_diagram_current.jpg',
        originalName: 'Current Network Architecture Diagram.jpg',
        mimeType: 'image/jpeg',
        size: 1024000, // 1MB
        path: '/uploads/2024/06/network_diagram_current.jpg',
        uploadedBy: analysts[1]?._id || users[3]._id,
        category: 'image',
        tags: ['network', 'architecture', 'diagram', 'infrastructure'],
        isPublic: false,
        accessLevel: 'restricted',
        status: 'active',
        description: 'Current network architecture and infrastructure layout',
        version: 1,
        downloadCount: 6,
        viewCount: 18,
        lastAccessed: new Date('2024-06-30'),
        createdAt: new Date('2024-06-15')
      },

      // Hojas de cálculo
      {
        filename: 'customer_feedback_data_q2.xlsx',
        originalName: 'Customer Feedback Analysis Q2 2024.xlsx',
        mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        size: 512000, // 512KB
        path: '/uploads/2024/07/customer_feedback_data_q2.xlsx',
        uploadedBy: regularUsers[1]?._id || users[4]._id,
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
        createdAt: new Date('2024-07-03')
      },

      {
        filename: 'performance_metrics_june.xlsx',
        originalName: 'System Performance Metrics - June 2024.xlsx',
        mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        size: 768000, // 768KB
        path: '/uploads/2024/06/performance_metrics_june.xlsx',
        uploadedBy: analysts[2]?._id || users[5]._id,
        category: 'document',
        tags: ['performance', 'metrics', 'system', 'june'],
        isPublic: false,
        accessLevel: 'internal',
        status: 'active',
        description: 'Detailed system performance metrics and analysis for June',
        version: 1,
        downloadCount: 11,
        viewCount: 25,
        lastAccessed: new Date('2024-07-01'),
        createdAt: new Date('2024-06-28')
      },

      // Videos
      {
        filename: 'compliance_training_intro.mp4',
        originalName: 'Compliance Training Introduction.mp4',
        mimeType: 'video/mp4',
        size: 52428800, // 50MB
        path: '/uploads/2024/07/compliance_training_intro.mp4',
        uploadedBy: regularUsers[2]?._id || users[6]._id,
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
        createdAt: new Date('2024-07-04')
      },

      // Archivos de código/scripts
      {
        filename: 'database_migration_v2.sql',
        originalName: 'Database Migration Script v2.0.sql',
        mimeType: 'text/plain',
        size: 256000, // 256KB
        path: '/uploads/2024/06/database_migration_v2.sql',
        uploadedBy: investigators[1]?._id || users[7]._id,
        category: 'document',
        tags: ['database', 'migration', 'sql', 'script'],
        isPublic: false,
        accessLevel: 'restricted',
        status: 'active',
        description: 'Improved database migration script with error handling',
        version: 2,
        downloadCount: 4,
        viewCount: 9,
        lastAccessed: new Date('2024-06-26'),
        createdAt: new Date('2024-06-26')
      },

      // Archivos de audio
      {
        filename: 'network_monitoring_session.wav',
        originalName: 'Network Monitoring Session Recording.wav',
        mimeType: 'audio/wav',
        size: 15728640, // 15MB
        path: '/uploads/2024/06/network_monitoring_session.wav',
        uploadedBy: investigators[0]?._id || users[8]._id,
        category: 'audio',
        tags: ['network', 'monitoring', 'session', 'recording'],
        isPublic: false,
        accessLevel: 'confidential',
        status: 'active',
        description: 'Audio recording of network monitoring session with anomaly detection',
        version: 1,
        downloadCount: 2,
        viewCount: 5,
        lastAccessed: new Date('2024-06-20'),
        createdAt: new Date('2024-06-08')
      },

      // Archivos comprimidos
      {
        filename: 'evidence_collection_photos.zip',
        originalName: 'Physical Evidence Collection Photos.zip',
        mimeType: 'application/zip',
        size: 10485760, // 10MB
        path: '/uploads/2024/07/evidence_collection_photos.zip',
        uploadedBy: investigators[0]?._id || users[9]._id,
        category: 'archive',
        tags: ['evidence', 'photos', 'collection', 'physical'],
        isPublic: false,
        accessLevel: 'confidential',
        status: 'active',
        description: 'Compressed archive of physical evidence collection photographs',
        version: 1,
        downloadCount: 1,
        viewCount: 3,
        lastAccessed: new Date('2024-07-04'),
        createdAt: new Date('2024-07-04')
      },

      // Documentos Word
      {
        filename: 'api_documentation_draft.docx',
        originalName: 'REST API Documentation Draft.docx',
        mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        size: 1024000, // 1MB
        path: '/uploads/2024/06/api_documentation_draft.docx',
        uploadedBy: analysts[2]?._id || users[1]._id,
        category: 'document',
        tags: ['api', 'documentation', 'rest', 'draft'],
        isPublic: false,
        accessLevel: 'internal',
        status: 'active',
        description: 'Draft documentation for REST API endpoints and usage',
        version: 1,
        downloadCount: 9,
        viewCount: 21,
        lastAccessed: new Date('2024-06-30'),
        createdAt: new Date('2024-06-20')
      },

      // Archivo archivado
      {
        filename: 'old_system_backup.tar.gz',
        originalName: 'Legacy System Backup Archive.tar.gz',
        mimeType: 'application/gzip',
        size: 104857600, // 100MB
        path: '/uploads/2024/01/old_system_backup.tar.gz',
        uploadedBy: adminUser._id,
        category: 'archive',
        tags: ['backup', 'legacy', 'system', 'archive'],
        isPublic: false,
        accessLevel: 'restricted',
        status: 'archived',
        description: 'Complete backup of legacy system before migration',
        version: 1,
        downloadCount: 0,
        viewCount: 2,
        lastAccessed: new Date('2024-01-15'),
        expiresAt: new Date('2025-01-15'),
        createdAt: new Date('2024-01-10')
      }
    ];

    for (const fileData of sampleFiles) {
      const existingFile = await File.findOne({ filename: fileData.filename });
      if (!existingFile) {
        const file = new File(fileData);
        await file.save();
        console.log('✅ Archivo de ejemplo creado:', file.filename);
      }
    }

  } catch (error) {
    console.error('❌ Error creando archivos de ejemplo:', error);
  }
};

/**
 * Crear mensajes de ejemplo - NUEVA FUNCIÓN
 */
const createSampleMessages = async () => {
  try {
    const users = await User.find({ isActive: true }).limit(10);
    const groups = await Group.find().limit(5);
    const files = await File.find().limit(3);

    if (users.length === 0) return;

    const adminUser = users.find(u => u.role === 'admin') || users[0];
    const analysts = users.filter(u => u.role === 'analyst');
    const investigators = users.filter(u => u.role === 'investigator');
    const regularUsers = users.filter(u => u.role === 'user');

    const sampleMessages = [
      // Mensajes en grupos
      {
        content: 'Welcome everyone to the Research Team Alpha! Let\'s start by sharing our current projects and goals.',
        sender: adminUser._id,
        recipient: groups.find(g => g.name === 'Research Team Alpha')?._id || groups[0]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'read',
        deliveredAt: new Date('2024-01-16'),
        readAt: new Date('2024-01-16'),
        createdAt: new Date('2024-01-15')
      },

      {
        content: 'I\'ve uploaded the Q4 research analysis. Please review and provide feedback.',
        sender: analysts[0]?._id || users[1]._id,
        recipient: groups.find(g => g.name === 'Research Team Alpha')?._id || groups[0]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'read',
        attachments: files.length > 0 ? [{
          file: files[0]._id,
          filename: files[0].filename,
          size: files[0].size,
          mimeType: files[0].mimeType
        }] : [],
        deliveredAt: new Date('2024-06-02'),
        readAt: new Date('2024-06-02'),
        createdAt: new Date('2024-06-01')
      },

      {
        content: 'Great work on the analysis! The statistical models are very comprehensive. 👍',
        sender: adminUser._id,
        recipient: groups.find(g => g.name === 'Research Team Alpha')?._id || groups[0]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'read',
        deliveredAt: new Date('2024-06-03'),
        readAt: new Date('2024-06-03'),
        createdAt: new Date('2024-06-02')
      },

      // Mensajes en grupo de desarrollo
      {
        content: 'Team, we need to discuss the new API endpoints for the mobile app integration.',
        sender: adminUser._id,
        recipient: groups.find(g => g.name === 'Development Squad')?._id || groups[1]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'delivered',
        deliveredAt: new Date('2024-07-04'),
        createdAt: new Date('2024-07-04')
      },

      {
        content: 'I can handle the authentication endpoints. Should we use OAuth 2.0?',
        sender: analysts[1]?._id || users[2]._id,
        recipient: groups.find(g => g.name === 'Development Squad')?._id || groups[1]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'delivered',
        deliveredAt: new Date('2024-07-04'),
        createdAt: new Date('2024-07-04')
      },

      // Mensajes directos entre usuarios
      {
        content: 'Hi! Could you review the security audit findings when you have a moment?',
        sender: investigators[0]?._id || users[3]._id,
        recipient: adminUser._id,
        recipientType: 'User',
        messageType: 'text',
        status: 'read',
        deliveredAt: new Date('2024-06-06'),
        readAt: new Date('2024-06-06'),
        createdAt: new Date('2024-06-05')
      },

      {
        content: 'Absolutely! I\'ll review it today and get back to you with feedback.',
        sender: adminUser._id,
        recipient: investigators[0]?._id || users[3]._id,
        recipientType: 'User',
        messageType: 'text',
        status: 'read',
        replyTo: null, // Would reference previous message in real implementation
        deliveredAt: new Date('2024-06-06'),
        readAt: new Date('2024-06-06'),
        createdAt: new Date('2024-06-06')
      },

      {
        content: 'Thanks for the quick turnaround on the API documentation review!',
        sender: analysts[2]?._id || users[4]._id,
        recipient: adminUser._id,
        recipientType: 'User',
        messageType: 'text',
        status: 'read',
        deliveredAt: new Date('2024-07-01'),
        readAt: new Date('2024-07-01'),
        createdAt: new Date('2024-06-30')
      },

      // Mensaje con archivo adjunto
      {
        content: 'Here\'s the updated UI mockup with the accessibility improvements we discussed.',
        sender: regularUsers[0]?._id || users[5]._id,
        recipient: groups.find(g => g.name === 'Design Collective')?._id || groups[2]?._id,
        recipientType: 'Group',
        messageType: 'file',
        status: 'delivered',
        attachments: files.length > 2 ? [{
          file: files[2]._id,
          filename: files[2].filename,
          size: files[2].size,
          mimeType: files[2].mimeType
        }] : [],
        deliveredAt: new Date('2024-07-02'),
        createdAt: new Date('2024-07-01')
      },

      // Mensajes del sistema
      {
        content: 'System notification: New member added to the group.',
        sender: adminUser._id,
        recipient: groups.find(g => g.name === 'Data Analytics Hub')?._id || groups[3]?._id,
        recipientType: 'Group',
        messageType: 'system',
        status: 'delivered',
        deliveredAt: new Date('2024-02-21'),
        createdAt: new Date('2024-02-20')
      },

      {
        content: 'Welcome to the Legal Department group! Please review our current cases and procedures.',
        sender: investigators.find(u => u.department === 'Legal')?._id || adminUser._id,
        recipient: groups.find(g => g.name === 'Legal Department')?._id || groups[4]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'delivered',
        deliveredAt: new Date('2024-01-21'),
        createdAt: new Date('2024-01-20')
      },

      // Conversación sobre evidencias
      {
        content: 'The database migration script has been updated with proper error handling. Ready for review.',
        sender: investigators[1]?._id || users[6]._id,
        recipient: adminUser._id,
        recipientType: 'User',
        messageType: 'text',
        status: 'read',
        deliveredAt: new Date('2024-06-27'),
        readAt: new Date('2024-06-27'),
        createdAt: new Date('2024-06-26')
      },

      {
        content: 'Perfect! I\'ll test it in the staging environment first.',
        sender: adminUser._id,
        recipient: investigators[1]?._id || users[6]._id,
        recipientType: 'User',
        messageType: 'text',
        status: 'read',
        deliveredAt: new Date('2024-06-27'),
        readAt: new Date('2024-06-27'),
        createdAt: new Date('2024-06-27')
      },

      // Mensaje reciente sin leer
      {
        content: 'Team meeting tomorrow at 10 AM to discuss the Project Phoenix milestones.',
        sender: adminUser._id,
        recipient: groups.find(g => g.name === 'Project Phoenix')?._id || groups[0]?._id,
        recipientType: 'Group',
        messageType: 'text',
        status: 'sent',
        createdAt: new Date('2024-07-04')
      },

      {
        content: 'Could you send me the latest performance metrics when you get a chance?',
        sender: regularUsers[1]?._id || users[7]._id,
        recipient: analysts[2]?._id || users[8]._id,
        recipientType: 'User',
        messageType: 'text',
        status: 'delivered',
        deliveredAt: new Date('2024-07-04'),
        createdAt: new Date('2024-07-04')
      }
    ];

    for (const messageData of sampleMessages) {
      const message = new Message(messageData);
      await message.save();
      console.log('✅ Mensaje de ejemplo creado');
    }

  } catch (error) {
    console.error('❌ Error creando mensajes de ejemplo:', error);
  }
};

/**
 * Inicializar base de datos con datos de ejemplo
 */
const initializeDatabase = async () => {
  try {
    console.log('🚀 Inicializando base de datos MongoDB...');

    await createDefaultAdmin();
    await createSampleUsers();
    await createSampleGroups();
    await createSampleFiles();
    await createSampleEvidences();
    await createSampleMessages();
    await createSampleNotifications();

    console.log('✅ Base de datos MongoDB inicializada correctamente');

  } catch (error) {
    console.error('❌ Error inicializando base de datos:', error);
  }
};

// =====================================================
// EXPORTAR SCHEMAS Y FUNCIONES
// =====================================================
module.exports = {
  // Schemas
  userSchema,
  fileSchema,
  groupSchema,
  messageSchema,
  evidenceSchema,
  notificationSchema,

  // Modelos
  User,
  File,
  Group,
  Message,
  Evidence,
  Notification,

  // Funciones de inicialización
  createDefaultAdmin,
  createSampleUsers,
  createSampleGroups,
  createSampleFiles,
  createSampleMessages,
  createSampleEvidences,
  createSampleNotifications,
  initializeDatabase
};
