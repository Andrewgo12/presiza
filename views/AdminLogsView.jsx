"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { logsAPI, databaseAPI } from "../services/api"
import {
  Activity,
  AlertCircle,
  Database,
  Download,
  Eye,
  Filter,
  RefreshCw,
  Search,
  Server,
  Trash2,
  Users
} from "lucide-react"

const AdminLogsView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [activeTab, setActiveTab] = useState("audit")
  const [logs, setLogs] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [pagination, setPagination] = useState({ page: 1, limit: 50, total: 0 })
  const [filters, setFilters] = useState({
    search: "",
    level: "",
    action: "",
    resource: "",
    success: ""
  })
  const [dbStatus, setDbStatus] = useState(null)
  const [summary, setSummary] = useState(null)

  // Verificar permisos de admin
  useEffect(() => {
    if (!isAdmin) {
      window.location.href = '/dashboard'
      return
    }
  }, [isAdmin])

  // Cargar estado de base de datos
  useEffect(() => {
    const loadDatabaseStatus = async () => {
      try {
        const status = await databaseAPI.getStatus()
        setDbStatus(status)
      } catch (err) {
        console.error('Error loading database status:', err)
      }
    }

    loadDatabaseStatus()
  }, [])

  // Cargar logs según la pestaña activa
  useEffect(() => {
    const loadLogs = async () => {
      try {
        setLoading(true)
        setError(null)

        const params = {
          page: pagination.page,
          limit: pagination.limit,
          ...filters
        }

        let response
        switch (activeTab) {
          case 'audit':
            response = await logsAPI.getAuditLogs(params)
            break
          case 'system':
            response = await logsAPI.getSystemLogs(params)
            break
          case 'performance':
            response = await logsAPI.getPerformanceMetrics(params)
            break
          case 'sessions':
            response = await logsAPI.getUserSessions(params)
            break
          default:
            response = await logsAPI.getAuditLogs(params)
        }

        setLogs(response.logs || response.sessions || [])
        setPagination(response.pagination || { page: 1, limit: 50, total: 0 })

      } catch (err) {
        console.error('Error loading logs:', err)
        setError('Error cargando logs')
        setLogs([])
      } finally {
        setLoading(false)
      }
    }

    loadLogs()
  }, [activeTab, pagination.page, pagination.limit, filters])

  // Cargar resumen general
  useEffect(() => {
    const loadSummary = async () => {
      try {
        const summaryData = await logsAPI.getSummary()
        setSummary(summaryData.summary)
      } catch (err) {
        console.error('Error loading summary:', err)
      }
    }

    loadSummary()
  }, [])

  // Función para limpiar logs
  const handleCleanupLogs = async () => {
    if (!window.confirm('¿Estás seguro de que quieres limpiar logs antiguos? Esta acción no se puede deshacer.')) {
      return
    }

    try {
      const daysToKeep = prompt('¿Cuántos días de logs quieres mantener?', '90')
      if (!daysToKeep) return

      await logsAPI.cleanupLogs(parseInt(daysToKeep))
      alert('Logs limpiados exitosamente')
      
      // Recargar datos
      window.location.reload()
    } catch (err) {
      console.error('Error cleaning logs:', err)
      alert('Error limpiando logs')
    }
  }

  // Función para exportar logs
  const handleExportLogs = async () => {
    try {
      const startDate = prompt('Fecha de inicio (YYYY-MM-DD):', new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0])
      const endDate = prompt('Fecha de fin (YYYY-MM-DD):', new Date().toISOString().split('T')[0])
      
      if (!startDate || !endDate) return

      const blob = await logsAPI.exportLogs(activeTab, startDate, endDate)
      
      // Crear URL de descarga
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.style.display = 'none'
      a.href = url
      a.download = `${activeTab}_logs_${startDate}_${endDate}.csv`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      
    } catch (err) {
      console.error('Error exporting logs:', err)
      alert('Error exportando logs')
    }
  }

  // Función para cambiar filtros
  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }))
    setPagination(prev => ({ ...prev, page: 1 }))
  }

  // Función para cambiar página
  const handlePageChange = (newPage) => {
    setPagination(prev => ({ ...prev, page: newPage }))
  }

  if (!isAdmin) {
    return null // O un componente de acceso denegado
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Header onMenuClick={() => setSidebarOpen(!sidebarOpen)} />
      
      <div className="flex">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        
        <main className="flex-1 p-6">
          <div className="max-w-7xl mx-auto">
            {/* Header */}
            <div className="mb-8">
              <h1 className="text-3xl font-bold text-gray-900">Administración de Logs</h1>
              <p className="text-gray-600 mt-2">Monitoreo y gestión de logs del sistema</p>
            </div>

            {/* Estado de Base de Datos */}
            {dbStatus && (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <Database className="h-5 w-5 mr-2" />
                  Estado de Bases de Datos
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="flex items-center">
                    <div className={`w-3 h-3 rounded-full mr-3 ${dbStatus.databases.mongodb.connected ? 'bg-green-500' : 'bg-red-500'}`}></div>
                    <span className="text-sm">MongoDB Atlas: {dbStatus.databases.mongodb.connected ? 'Conectado' : 'Desconectado'}</span>
                  </div>
                  <div className="flex items-center">
                    <div className={`w-3 h-3 rounded-full mr-3 ${dbStatus.databases.mysql.connected ? 'bg-green-500' : 'bg-red-500'}`}></div>
                    <span className="text-sm">MySQL/XAMPP: {dbStatus.databases.mysql.connected ? 'Conectado' : 'Desconectado'}</span>
                  </div>
                </div>
              </div>
            )}

            {/* Resumen de Estadísticas */}
            {summary && (
              <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center">
                    <Activity className="h-8 w-8 text-blue-600" />
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-600">Audit Logs</p>
                      <p className="text-2xl font-bold text-gray-900">{summary.auditLogs?.total || 0}</p>
                    </div>
                  </div>
                </div>
                
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center">
                    <Server className="h-8 w-8 text-green-600" />
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-600">System Logs</p>
                      <p className="text-2xl font-bold text-gray-900">{summary.systemLogs?.total || 0}</p>
                    </div>
                  </div>
                </div>
                
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center">
                    <Users className="h-8 w-8 text-purple-600" />
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-600">Sesiones Activas</p>
                      <p className="text-2xl font-bold text-gray-900">{summary.sessions?.active || 0}</p>
                    </div>
                  </div>
                </div>
                
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center">
                    <AlertCircle className="h-8 w-8 text-red-600" />
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-600">Errores Hoy</p>
                      <p className="text-2xl font-bold text-gray-900">{summary.systemLogs?.errors || 0}</p>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Controles */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <div className="flex flex-wrap items-center justify-between gap-4">
                <div className="flex items-center space-x-4">
                  {/* Pestañas */}
                  <div className="flex space-x-1">
                    {[
                      { id: 'audit', label: 'Auditoría', icon: Activity },
                      { id: 'system', label: 'Sistema', icon: Server },
                      { id: 'performance', label: 'Rendimiento', icon: RefreshCw },
                      { id: 'sessions', label: 'Sesiones', icon: Users }
                    ].map(tab => (
                      <button
                        key={tab.id}
                        onClick={() => setActiveTab(tab.id)}
                        className={`flex items-center px-3 py-2 rounded-md text-sm font-medium ${
                          activeTab === tab.id
                            ? 'bg-blue-100 text-blue-700'
                            : 'text-gray-500 hover:text-gray-700'
                        }`}
                      >
                        <tab.icon className="h-4 w-4 mr-2" />
                        {tab.label}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="flex items-center space-x-2">
                  <button
                    onClick={handleExportLogs}
                    className="flex items-center px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                  >
                    <Download className="h-4 w-4 mr-2" />
                    Exportar
                  </button>
                  
                  <button
                    onClick={handleCleanupLogs}
                    className="flex items-center px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                  >
                    <Trash2 className="h-4 w-4 mr-2" />
                    Limpiar
                  </button>
                </div>
              </div>

              {/* Filtros */}
              <div className="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <input
                    type="text"
                    placeholder="Buscar..."
                    value={filters.search}
                    onChange={(e) => handleFilterChange('search', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                
                {activeTab === 'system' && (
                  <div>
                    <select
                      value={filters.level}
                      onChange={(e) => handleFilterChange('level', e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">Todos los niveles</option>
                      <option value="error">Error</option>
                      <option value="warn">Warning</option>
                      <option value="info">Info</option>
                      <option value="debug">Debug</option>
                    </select>
                  </div>
                )}
                
                {activeTab === 'audit' && (
                  <>
                    <div>
                      <select
                        value={filters.action}
                        onChange={(e) => handleFilterChange('action', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Todas las acciones</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="file_upload">Upload</option>
                        <option value="file_download">Download</option>
                        <option value="user_create">Crear Usuario</option>
                        <option value="user_update">Actualizar Usuario</option>
                      </select>
                    </div>
                    
                    <div>
                      <select
                        value={filters.success}
                        onChange={(e) => handleFilterChange('success', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Todos los resultados</option>
                        <option value="true">Exitoso</option>
                        <option value="false">Fallido</option>
                      </select>
                    </div>
                  </>
                )}
              </div>
            </div>

            {/* Tabla de Logs */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
              {loading ? (
                <div className="p-8 text-center">
                  <RefreshCw className="h-8 w-8 animate-spin mx-auto text-gray-400" />
                  <p className="text-gray-500 mt-2">Cargando logs...</p>
                </div>
              ) : error ? (
                <div className="p-8 text-center">
                  <AlertCircle className="h-8 w-8 text-red-500 mx-auto" />
                  <p className="text-red-600 mt-2">{error}</p>
                </div>
              ) : logs.length === 0 ? (
                <div className="p-8 text-center">
                  <Eye className="h-8 w-8 text-gray-400 mx-auto" />
                  <p className="text-gray-500 mt-2">No hay logs disponibles</p>
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Timestamp
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {activeTab === 'audit' ? 'Acción' : activeTab === 'system' ? 'Nivel' : 'Métrica'}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {activeTab === 'audit' ? 'Usuario' : activeTab === 'system' ? 'Componente' : 'Endpoint'}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Detalles
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Estado
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {logs.map((log, index) => (
                        <tr key={log.id || index} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {new Date(log.timestamp || log.createdAt).toLocaleString()}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {log.action || log.level || log.metricName}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {log.userEmail || log.component || log.endpoint || 'N/A'}
                          </td>
                          <td className="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                            {log.message || log.resource || JSON.stringify(log.details) || 'N/A'}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            {log.success !== undefined ? (
                              <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                log.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                              }`}>
                                {log.success ? 'Exitoso' : 'Fallido'}
                              </span>
                            ) : log.level ? (
                              <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                log.level === 'error' ? 'bg-red-100 text-red-800' :
                                log.level === 'warn' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-blue-100 text-blue-800'
                              }`}>
                                {log.level.toUpperCase()}
                              </span>
                            ) : (
                              <span className="text-gray-500">N/A</span>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}

              {/* Paginación */}
              {pagination.total > 0 && (
                <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                  <div className="flex-1 flex justify-between sm:hidden">
                    <button
                      onClick={() => handlePageChange(pagination.page - 1)}
                      disabled={pagination.page <= 1}
                      className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                      Anterior
                    </button>
                    <button
                      onClick={() => handlePageChange(pagination.page + 1)}
                      disabled={pagination.page >= pagination.pages}
                      className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                      Siguiente
                    </button>
                  </div>
                  <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                      <p className="text-sm text-gray-700">
                        Mostrando <span className="font-medium">{((pagination.page - 1) * pagination.limit) + 1}</span> a{' '}
                        <span className="font-medium">{Math.min(pagination.page * pagination.limit, pagination.total)}</span> de{' '}
                        <span className="font-medium">{pagination.total}</span> resultados
                      </p>
                    </div>
                    <div>
                      <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button
                          onClick={() => handlePageChange(pagination.page - 1)}
                          disabled={pagination.page <= 1}
                          className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                        >
                          Anterior
                        </button>
                        <span className="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                          {pagination.page} de {pagination.pages}
                        </span>
                        <button
                          onClick={() => handlePageChange(pagination.page + 1)}
                          disabled={pagination.page >= pagination.pages}
                          className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                        >
                          Siguiente
                        </button>
                      </nav>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}

export default AdminLogsView
