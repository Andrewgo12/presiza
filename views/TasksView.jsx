"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { evidencesAPI, usersAPI, groupsAPI } from "../services/api"
import {
  Plus,
  Search,
  Filter,
  Calendar,
  User,
  Users,
  CheckSquare,
  Clock,
  AlertCircle,
  Star,
  Edit,
  Trash2,
  Eye,
  XCircle
} from "lucide-react"

const TasksView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [tasks, setTasks] = useState([])
  const [filteredTasks, setFilteredTasks] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [statusFilter, setStatusFilter] = useState("all")
  const [showCreateModal, setShowCreateModal] = useState(false)
  const [showEvaluateModal, setShowEvaluateModal] = useState(false)
  const [selectedTask, setSelectedTask] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [pagination, setPagination] = useState({ page: 1, limit: 20, total: 0 })

  useEffect(() => {
    const loadTasks = async () => {
      try {
        setLoading(true)
        setError(null)

        // For now, we'll use evidences as tasks since there's no specific tasks API
        // In a real implementation, you would have a dedicated tasks API
        const params = {
          page: pagination.page,
          limit: pagination.limit
        }

        if (statusFilter !== 'all') {
          params.status = statusFilter
        }

        if (searchTerm) {
          params.search = searchTerm
        }

        // Using evidences API as a placeholder for tasks
        const response = await evidencesAPI.getEvidences(params)
        
        // Transform evidences to task-like structure
        const tasksFromEvidences = (response.evidences || []).map(evidence => ({
          id: evidence._id,
          title: evidence.title || 'Task from Evidence',
          description: evidence.description || 'No description',
          assignedTo: [{ id: evidence.authorId, name: evidence.author }],
          createdBy: evidence.author,
          createdDate: evidence.submissionDate,
          dueDate: evidence.submissionDate,
          status: evidence.status === 'approved' ? 'completed' : 
                  evidence.status === 'rejected' ? 'cancelled' : 'in_progress',
          priority: 'medium',
          attachments: [evidence.fileName],
          submissions: []
        }))

        setTasks(tasksFromEvidences)
        setFilteredTasks(tasksFromEvidences)
        setPagination(response.pagination || { page: 1, limit: 20, total: 0 })

      } catch (err) {
        console.error('Error loading tasks:', err)
        setError('Error cargando tareas')
        // Fallback to empty data
        setTasks([])
        setFilteredTasks([])
      } finally {
        setLoading(false)
      }
    }

    loadTasks()
  }, [pagination.page, pagination.limit, statusFilter, searchTerm])

  // Function to create task
  const handleCreateTask = async (taskData) => {
    try {
      // In a real implementation, you would call a tasks API
      // For now, we'll just add to local state
      const newTask = {
        id: Date.now(),
        ...taskData,
        createdBy: user.name,
        createdDate: new Date().toISOString(),
        status: 'pending',
        submissions: []
      }

      setTasks(prev => [newTask, ...prev])
      setFilteredTasks(prev => [newTask, ...prev])
      setShowCreateModal(false)
    } catch (err) {
      console.error('Error creating task:', err)
      setError('Error creando tarea')
    }
  }

  // Function to update task status
  const handleUpdateTaskStatus = async (taskId, newStatus) => {
    try {
      // In a real implementation, you would call a tasks API
      const updatedTasks = tasks.map(t => 
        t.id === taskId ? { ...t, status: newStatus } : t
      )
      setTasks(updatedTasks)
      setFilteredTasks(updatedTasks)
    } catch (err) {
      console.error('Error updating task:', err)
      setError('Error actualizando tarea')
    }
  }

  // If there's an error, show error message
  if (error) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Header onMenuClick={() => setSidebarOpen(!sidebarOpen)} />
        <div className="flex">
          <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
          <main className="flex-1 p-6">
            <div className="text-center py-12">
              <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">Error cargando tareas</h3>
              <p className="text-gray-500 mb-4">{error}</p>
              <button
                onClick={() => window.location.reload()}
                className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
              >
                Reintentar
              </button>
            </div>
          </main>
        </div>
      </div>
    )
  }

  const getStatusIcon = (status) => {
    switch (status) {
      case "completed":
        return CheckSquare
      case "in_progress":
        return Clock
      case "pending":
        return AlertCircle
      case "cancelled":
        return XCircle
      default:
        return Clock
    }
  }

  const getStatusColor = (status) => {
    switch (status) {
      case "completed":
        return "bg-green-100 text-green-800"
      case "in_progress":
        return "bg-blue-100 text-blue-800"
      case "pending":
        return "bg-yellow-100 text-yellow-800"
      case "cancelled":
        return "bg-red-100 text-red-800"
      default:
        return "bg-gray-100 text-gray-800"
    }
  }

  const getPriorityColor = (priority) => {
    switch (priority) {
      case "high":
        return "bg-red-100 text-red-800"
      case "medium":
        return "bg-yellow-100 text-yellow-800"
      case "low":
        return "bg-green-100 text-green-800"
      default:
        return "bg-gray-100 text-gray-800"
    }
  }

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("es-ES", {
      year: "numeric",
      month: "short",
      day: "numeric",
    })
  }

  const TaskCard = ({ task }) => {
    const StatusIcon = getStatusIcon(task.status)

    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div className="flex items-start justify-between mb-4">
          <div className="flex-1">
            <h3 className="font-semibold text-gray-900 mb-2">{task.title}</h3>
            <p className="text-sm text-gray-600 line-clamp-2 mb-3">{task.description}</p>
          </div>
          <div className={`flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(task.status)}`}>
            <StatusIcon className="w-4 h-4 mr-1" />
            {task.status.replace("_", " ")}
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
          <div className="flex items-center text-gray-600">
            <User className="w-4 h-4 mr-2" />
            {task.createdBy}
          </div>
          <div className="flex items-center text-gray-600">
            <Calendar className="w-4 h-4 mr-2" />
            {formatDate(task.dueDate)}
          </div>
          <div className="flex items-center text-gray-600">
            <Users className="w-4 h-4 mr-2" />
            {task.assignedTo?.length || 0} asignados
          </div>
          <div className={`flex items-center px-2 py-1 rounded text-xs font-medium ${getPriorityColor(task.priority)}`}>
            <Star className="w-3 h-3 mr-1" />
            {task.priority}
          </div>
        </div>

        <div className="flex items-center justify-between">
          <span className="text-xs text-gray-400">
            Creado: {formatDate(task.createdDate)}
          </span>
          
          <div className="flex space-x-2">
            <button
              onClick={() => setSelectedTask(task)}
              className="p-2 text-gray-400 hover:text-blue-600 rounded-full hover:bg-blue-50"
            >
              <Eye className="w-4 h-4" />
            </button>
            {isAdmin && (
              <>
                <button
                  onClick={() => setSelectedTask(task)}
                  className="p-2 text-gray-400 hover:text-green-600 rounded-full hover:bg-green-50"
                >
                  <Edit className="w-4 h-4" />
                </button>
                <button
                  onClick={() => handleUpdateTaskStatus(task.id, 'cancelled')}
                  className="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-red-50"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </>
            )}
          </div>
        </div>
      </div>
    )
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
              <h1 className="text-3xl font-bold text-gray-900">
                {isAdmin ? "Gestión de Tareas" : "Mis Tareas"}
              </h1>
              <p className="text-gray-600 mt-2">
                {isAdmin ? "Administra todas las tareas del sistema" : "Gestiona tus tareas asignadas"}
              </p>
            </div>

            {/* Controls */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <div className="flex flex-wrap items-center justify-between gap-4">
                <div className="flex items-center space-x-4">
                  <div className="relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    <input
                      type="text"
                      placeholder="Buscar tareas..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  
                  <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="all">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="in_progress">En progreso</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                  </select>
                </div>

                {isAdmin && (
                  <button
                    onClick={() => setShowCreateModal(true)}
                    className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                  >
                    <Plus className="w-4 h-4 mr-2" />
                    Nueva Tarea
                  </button>
                )}
              </div>
            </div>

            {/* Tasks Grid */}
            {loading ? (
              <div className="text-center py-12">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto mb-4"></div>
                <p className="text-gray-600">Cargando tareas...</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {filteredTasks.map((task) => (
                  <TaskCard key={task.id} task={task} />
                ))}
              </div>
            )}

            {filteredTasks.length === 0 && !loading && (
              <div className="text-center py-12">
                <CheckSquare className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No hay tareas</h3>
                <p className="text-gray-500">
                  {searchTerm || statusFilter !== "all"
                    ? "Intenta ajustar tus filtros de búsqueda"
                    : isAdmin
                      ? "No se han creado tareas aún"
                      : "No tienes tareas asignadas"}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  )
}

export default TasksView
