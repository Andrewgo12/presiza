/**
 * Servidor Principal - Sistema de Gestión de Evidencias
 * Configuración y arranque del servidor Express con todas las funcionalidades
 */

require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const morgan = require('morgan');
const rateLimit = require('express-rate-limit');
const { createServer } = require('http');
const { Server } = require('socket.io');

// Importar rutas
const authRoutes = require('./routes/auth');
const userRoutes = require('./routes/users');
const fileRoutes = require('./routes/files');
const groupRoutes = require('./routes/groups');
const messageRoutes = require('./routes/messages');
const evidenceRoutes = require('./routes/evidences');
const analyticsRoutes = require('./routes/analytics');
const notificationRoutes = require('./routes/notifications');

// Importar middleware personalizado
const errorHandler = require('./middleware/errorHandler');
const authMiddleware = require('./middleware/auth');

// Configuración de la aplicación
const app = express();
const server = createServer(app);
const io = new Server(server, {
  cors: {
    origin: process.env.CORS_ORIGINS?.split(',') || ['http://localhost:3000'],
    methods: ['GET', 'POST', 'PUT', 'DELETE'],
    credentials: true
  }
});

const PORT = process.env.PORT || 5000;
const API_VERSION = process.env.API_VERSION || 'v1';

// Configuración de seguridad
app.use(helmet({
  crossOriginEmbedderPolicy: false,
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      scriptSrc: ["'self'"],
      imgSrc: ["'self'", "data:", "https:"],
    },
  },
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 15 * 60 * 1000, // 15 minutos
  max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100, // límite de requests por ventana
  message: {
    error: 'Demasiadas solicitudes desde esta IP, intente nuevamente más tarde.',
    code: 'RATE_LIMIT_EXCEEDED'
  },
  standardHeaders: true,
  legacyHeaders: false,
});

app.use(limiter);

// Middleware general
app.use(compression());
app.use(morgan('combined'));
app.use(cors({
  origin: process.env.CORS_ORIGINS?.split(',') || ['http://localhost:3000'],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));

app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Conexión a MongoDB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/evidence_management', {
  useNewUrlParser: true,
  useUnifiedTopology: true,
})
  .then(() => {
    console.log('✅ Conectado a MongoDB exitosamente');
  })
  .catch((error) => {
    console.error('❌ Error conectando a MongoDB:', error);
    process.exit(1);
  });

// Configuración de Socket.IO para tiempo real
io.use((socket, next) => {
  // Middleware de autenticación para WebSockets
  const token = socket.handshake.auth.token;
  if (token) {
    // Verificar JWT token aquí
    next();
  } else {
    next(new Error('Authentication error'));
  }
});

io.on('connection', (socket) => {
  console.log('👤 Usuario conectado:', socket.id);

  socket.on('join-room', (roomId) => {
    socket.join(roomId);
    console.log(`👥 Usuario ${socket.id} se unió a la sala ${roomId}`);
  });

  socket.on('disconnect', () => {
    console.log('👋 Usuario desconectado:', socket.id);
  });
});

// Hacer io disponible en las rutas
app.set('io', io);

// Rutas de la API
app.use(`/api/${API_VERSION}/auth`, authRoutes);
app.use(`/api/${API_VERSION}/users`, authMiddleware.authenticateToken, userRoutes);
app.use(`/api/${API_VERSION}/files`, authMiddleware.authenticateToken, fileRoutes);
app.use(`/api/${API_VERSION}/groups`, authMiddleware.authenticateToken, groupRoutes);
app.use(`/api/${API_VERSION}/messages`, authMiddleware.authenticateToken, messageRoutes);
app.use(`/api/${API_VERSION}/evidences`, authMiddleware.authenticateToken, evidenceRoutes);
app.use(`/api/${API_VERSION}/analytics`, authMiddleware.authenticateToken, analyticsRoutes);
app.use(`/api/${API_VERSION}/notifications`, authMiddleware.authenticateToken, notificationRoutes);

// Ruta de salud del servidor
app.get('/health', (req, res) => {
  res.json({
    status: 'OK',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    environment: process.env.NODE_ENV,
    version: API_VERSION
  });
});

// Ruta raíz
app.get('/', (req, res) => {
  res.json({
    message: 'API del Sistema de Gestión de Evidencias',
    version: API_VERSION,
    documentation: `/api/${API_VERSION}/docs`,
    health: '/health'
  });
});

// Middleware de manejo de errores (debe ir al final)
app.use(errorHandler.errorHandler);

// Manejo de rutas no encontradas
app.use('*', (req, res) => {
  res.status(404).json({
    error: 'Ruta no encontrada',
    message: `La ruta ${req.originalUrl} no existe en esta API`,
    availableRoutes: [
      `/api/${API_VERSION}/auth`,
      `/api/${API_VERSION}/users`,
      `/api/${API_VERSION}/files`,
      `/api/${API_VERSION}/groups`,
      `/api/${API_VERSION}/messages`,
      `/api/${API_VERSION}/evidences`,
      `/api/${API_VERSION}/analytics`,
      `/api/${API_VERSION}/notifications`
    ]
  });
});

// Iniciar servidor
server.listen(PORT, () => {
  console.log(`🚀 Servidor ejecutándose en puerto ${PORT}`);
  console.log(`📚 API disponible en: http://localhost:${PORT}/api/${API_VERSION}`);
  console.log(`🏥 Health check: http://localhost:${PORT}/health`);
  console.log(`🌍 Entorno: ${process.env.NODE_ENV || 'development'}`);
});

// Manejo de errores no capturados
process.on('unhandledRejection', (err) => {
  console.error('❌ Unhandled Promise Rejection:', err);
  server.close(() => {
    process.exit(1);
  });
});

process.on('uncaughtException', (err) => {
  console.error('❌ Uncaught Exception:', err);
  process.exit(1);
});

module.exports = { app, server, io };
