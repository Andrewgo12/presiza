"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { notificationsAPI } from "../services/api"
import {
  Bell,
  Filter,
  Check,
  Trash2,
  Archive,
  MessageCircle,
  CheckCircle,
  XCircle,
  Clock,
  Users,
  Star,
  AlertTriangle,
  CheckSquare
} from "lucide-react"

const NotificationsView = () => {
  const { user } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [notifications, setNotifications] = useState([])
  const [filteredNotifications, setFilteredNotifications] = useState([])
  const [filterType, setFilterType] = useState("all")
  const [filterStatus, setFilterStatus] = useState("all")
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const loadNotifications = async () => {
      try {
        setLoading(true)
        setError(null)

        const params = {
          type: filterType !== 'all' ? filterType : undefined,
          status: filterStatus !== 'all' ? filterStatus : undefined
        }

        const response = await notificationsAPI.getNotifications(params)
        
        setNotifications(response.notifications || [])
        setFilteredNotifications(response.notifications || [])

      } catch (err) {
        console.error('Error loading notifications:', err)
        setError('Error cargando notificaciones')
        // Fallback to empty data
        setNotifications([])
        setFilteredNotifications([])
      } finally {
        setLoading(false)
      }
    }

    loadNotifications()
  }, [filterType, filterStatus])

  // Function to mark notification as read
  const handleMarkAsRead = async (notificationId) => {
    try {
      await notificationsAPI.markAsRead(notificationId)
      const updatedNotifications = notifications.map(n => 
        n._id === notificationId ? { ...n, isRead: true } : n
      )
      setNotifications(updatedNotifications)
      setFilteredNotifications(updatedNotifications)
    } catch (err) {
      console.error('Error marking notification as read:', err)
      setError('Error marcando notificación como leída')
    }
  }

  // Function to delete notification
  const handleDeleteNotification = async (notificationId) => {
    try {
      await notificationsAPI.deleteNotification(notificationId)
      const updatedNotifications = notifications.filter(n => n._id !== notificationId)
      setNotifications(updatedNotifications)
      setFilteredNotifications(updatedNotifications)
    } catch (err) {
      console.error('Error deleting notification:', err)
      setError('Error eliminando notificación')
    }
  }

  // Function to mark all as read
  const handleMarkAllAsRead = async () => {
    try {
      await notificationsAPI.markAllAsRead()
      const updatedNotifications = notifications.map(n => ({ ...n, isRead: true }))
      setNotifications(updatedNotifications)
      setFilteredNotifications(updatedNotifications)
    } catch (err) {
      console.error('Error marking all as read:', err)
      setError('Error marcando todas como leídas')
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
              <AlertTriangle className="h-12 w-12 text-red-500 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">Error cargando notificaciones</h3>
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

  const getNotificationIcon = (type) => {
    switch (type) {
      case "upload":
        return CheckCircle
      case "comment":
        return MessageCircle
      case "task":
        return CheckSquare
      case "system":
        return AlertTriangle
      case "group":
        return Users
      default:
        return Bell
    }
  }

  const getNotificationColor = (type) => {
    switch (type) {
      case "upload":
        return { icon: "text-green-600", bg: "bg-green-50" }
      case "comment":
        return { icon: "text-blue-600", bg: "bg-blue-50" }
      case "task":
        return { icon: "text-purple-600", bg: "bg-purple-50" }
      case "system":
        return { icon: "text-red-600", bg: "bg-red-50" }
      case "group":
        return { icon: "text-indigo-600", bg: "bg-indigo-50" }
      default:
        return { icon: "text-gray-600", bg: "bg-gray-50" }
    }
  }

  const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)

    if (diffInHours < 1) {
      const diffInMinutes = Math.floor((now - date) / (1000 * 60))
      return `${diffInMinutes} min ago`
    } else if (diffInHours < 24) {
      return `${Math.floor(diffInHours)}h ago`
    } else {
      const diffInDays = Math.floor(diffInHours / 24)
      return `${diffInDays}d ago`
    }
  }

  const NotificationItem = ({ notification }) => {
    const Icon = getNotificationIcon(notification.type)
    const colors = getNotificationColor(notification.type)

    return (
      <div className={`p-4 border-l-4 ${notification.isRead ? 'border-gray-200 bg-white' : 'border-blue-500 bg-blue-50'} hover:bg-gray-50 transition-colors`}>
        <div className="flex items-start space-x-3">
          <div className={`flex-shrink-0 w-10 h-10 rounded-full ${colors.bg} flex items-center justify-center`}>
            <Icon className={`w-5 h-5 ${colors.icon}`} />
          </div>
          
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <h4 className={`text-sm font-medium ${notification.isRead ? 'text-gray-900' : 'text-gray-900 font-semibold'}`}>
                {notification.title}
              </h4>
              <span className="text-xs text-gray-500">
                {formatTime(notification.timestamp)}
              </span>
            </div>
            
            <p className="mt-1 text-sm text-gray-600">
              {notification.message}
            </p>
            
            {notification.relatedUser && (
              <p className="mt-1 text-xs text-gray-500">
                Por: {notification.relatedUser}
              </p>
            )}
          </div>
          
          <div className="flex-shrink-0 flex items-center space-x-2">
            {!notification.isRead && (
              <button
                onClick={() => handleMarkAsRead(notification._id)}
                className="p-1 text-gray-400 hover:text-green-600 rounded-full hover:bg-green-50"
                title="Marcar como leída"
              >
                <Check className="w-4 h-4" />
              </button>
            )}
            
            <button
              onClick={() => handleDeleteNotification(notification._id)}
              className="p-1 text-gray-400 hover:text-red-600 rounded-full hover:bg-red-50"
              title="Eliminar"
            >
              <Trash2 className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    )
  }

  const unreadCount = notifications.filter(n => !n.isRead).length

  return (
    <div className="min-h-screen bg-gray-50">
      <Header onMenuClick={() => setSidebarOpen(!sidebarOpen)} />
      
      <div className="flex">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        
        <main className="flex-1 p-6">
          <div className="max-w-4xl mx-auto">
            {/* Header */}
            <div className="mb-8">
              <div className="flex items-center justify-between">
                <div>
                  <h1 className="text-3xl font-bold text-gray-900 flex items-center">
                    <Bell className="w-8 h-8 mr-3" />
                    Notificaciones
                    {unreadCount > 0 && (
                      <span className="ml-2 bg-red-500 text-white text-sm px-2 py-1 rounded-full">
                        {unreadCount}
                      </span>
                    )}
                  </h1>
                  <p className="text-gray-600 mt-2">
                    Mantente al día con las últimas actualizaciones
                  </p>
                </div>
                
                {unreadCount > 0 && (
                  <button
                    onClick={handleMarkAllAsRead}
                    className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                  >
                    <Check className="w-4 h-4 mr-2" />
                    Marcar todas como leídas
                  </button>
                )}
              </div>
            </div>

            {/* Filters */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <div className="flex items-center space-x-4">
                <div className="flex items-center space-x-2">
                  <Filter className="w-4 h-4 text-gray-400" />
                  <span className="text-sm font-medium text-gray-700">Filtros:</span>
                </div>
                
                <select
                  value={filterType}
                  onChange={(e) => setFilterType(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="all">Todos los tipos</option>
                  <option value="upload">Subidas</option>
                  <option value="comment">Comentarios</option>
                  <option value="task">Tareas</option>
                  <option value="system">Sistema</option>
                  <option value="group">Grupos</option>
                </select>
                
                <select
                  value={filterStatus}
                  onChange={(e) => setFilterStatus(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="all">Todas</option>
                  <option value="unread">No leídas</option>
                  <option value="read">Leídas</option>
                </select>
              </div>
            </div>

            {/* Notifications List */}
            {loading ? (
              <div className="text-center py-12">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto mb-4"></div>
                <p className="text-gray-600">Cargando notificaciones...</p>
              </div>
            ) : (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                {filteredNotifications.length > 0 ? (
                  <div className="divide-y divide-gray-200">
                    {filteredNotifications.map((notification) => (
                      <NotificationItem key={notification._id} notification={notification} />
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <Bell className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                    <h3 className="text-lg font-medium text-gray-900 mb-2">No hay notificaciones</h3>
                    <p className="text-gray-500">
                      {filterType !== "all" || filterStatus !== "all"
                        ? "Intenta ajustar tus filtros"
                        : "No tienes notificaciones en este momento"}
                    </p>
                  </div>
                )}
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  )
}

export default NotificationsView
