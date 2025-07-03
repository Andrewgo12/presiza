"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
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
} from "lucide-react"

const NotificationsView = () => {
  const { user } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [notifications, setNotifications] = useState([])
  const [filteredNotifications, setFilteredNotifications] = useState([])
  const [filterType, setFilterType] = useState("all")
  const [filterStatus, setFilterStatus] = useState("all")

  useEffect(() => {
    // Load mock notifications
    const mockNotifications = [
      {
        id: 1,
        type: "upload",
        title: "File Upload Approved",
        message: "Your research document 'Q4_Analysis.pdf' has been approved by Dr. Smith",
        timestamp: "2024-01-15T14:30:00Z",
        isRead: false,
        actionUrl: "/evidences",
        icon: CheckCircle,
        iconColor: "text-green-600",
        bgColor: "bg-green-50",
        priority: "normal",
        relatedUser: "Dr. Smith",
        relatedItem: "Q4_Analysis.pdf",
      },
      {
        id: 2,
        type: "comment",
        title: "New Comment",
        message: "Jane Wilson commented on your evidence submission",
        timestamp: "2024-01-15T12:15:00Z",
        isRead: false,
        actionUrl: "/evidences/1",
        icon: MessageCircle,
        iconColor: "text-blue-600",
        bgColor: "bg-blue-50",
        priority: "normal",
        relatedUser: "Jane Wilson",
        relatedItem: "UI Mockups Design",
      },
      {
        id: 3,
        type: "rejection",
        title: "Evidence Rejected",
        message: "Your submission 'Database Migration Script' needs revision",
        timestamp: "2024-01-14T16:45:00Z",
        isRead: true,
        actionUrl: "/evidences/3",
        icon: XCircle,
        iconColor: "text-red-600",
        bgColor: "bg-red-50",
        priority: "high",
        relatedUser: "Admin User",
        relatedItem: "Database Migration Script",
      },
      {
        id: 4,
        type: "task",
        title: "Task Reminder",
        message: "Task 'Complete Data Analysis' is due tomorrow",
        timestamp: "2024-01-14T10:20:00Z",
        isRead: true,
        actionUrl: "/tasks",
        icon: Clock,
        iconColor: "text-yellow-600",
        bgColor: "bg-yellow-50",
        priority: "high",
        relatedUser: null,
        relatedItem: "Complete Data Analysis",
      },
      {
        id: 5,
        type: "group",
        title: "Group Invitation",
        message: "You've been invited to join 'Advanced Analytics Team'",
        timestamp: "2024-01-13T09:30:00Z",
        isRead: true,
        actionUrl: "/groups",
        icon: Users,
        iconColor: "text-purple-600",
        bgColor: "bg-purple-50",
        priority: "normal",
        relatedUser: "Mike Chen",
        relatedItem: "Advanced Analytics Team",
      },
      {
        id: 6,
        type: "rating",
        title: "Evidence Rated",
        message: "Your evidence received a 5-star rating from Dr. Smith",
        timestamp: "2024-01-12T15:45:00Z",
        isRead: true,
        actionUrl: "/evidences/1",
        icon: Star,
        iconColor: "text-yellow-500",
        bgColor: "bg-yellow-50",
        priority: "low",
        relatedUser: "Dr. Smith",
        relatedItem: "Research Analysis Report",
      },
      {
        id: 7,
        type: "system",
        title: "System Maintenance",
        message: "Scheduled maintenance will occur tonight from 2-4 AM",
        timestamp: "2024-01-12T08:00:00Z",
        isRead: true,
        actionUrl: null,
        icon: AlertTriangle,
        iconColor: "text-orange-600",
        bgColor: "bg-orange-50",
        priority: "normal",
        relatedUser: null,
        relatedItem: null,
      },
    ]

    setNotifications(mockNotifications)
    setFilteredNotifications(mockNotifications)
  }, [])

  useEffect(() => {
    let filtered = notifications

    // Filter by type
    if (filterType !== "all") {
      filtered = filtered.filter((notification) => notification.type === filterType)
    }

    // Filter by status
    if (filterStatus === "unread") {
      filtered = filtered.filter((notification) => !notification.isRead)
    } else if (filterStatus === "read") {
      filtered = filtered.filter((notification) => notification.isRead)
    }

    setFilteredNotifications(filtered)
  }, [filterType, filterStatus, notifications])

  const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)
    const diffInDays = diffInHours / 24

    if (diffInHours < 1) {
      const diffInMinutes = Math.floor((now - date) / (1000 * 60))
      return `${diffInMinutes} minutes ago`
    } else if (diffInHours < 24) {
      return `${Math.floor(diffInHours)} hours ago`
    } else if (diffInDays < 7) {
      return `${Math.floor(diffInDays)} days ago`
    } else {
      return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
      })
    }
  }

  const markAsRead = (notificationId) => {
    setNotifications((prev) => prev.map((notif) => (notif.id === notificationId ? { ...notif, isRead: true } : notif)))
  }

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((notif) => ({ ...notif, isRead: true })))
  }

  const deleteNotification = (notificationId) => {
    setNotifications((prev) => prev.filter((notif) => notif.id !== notificationId))
  }

  const archiveNotification = (notificationId) => {
    // In a real app, this would move to archived notifications
    deleteNotification(notificationId)
  }

  const handleNotificationClick = (notification) => {
    if (!notification.isRead) {
      markAsRead(notification.id)
    }
    if (notification.actionUrl) {
      // Navigate to the related page
      console.log(`Navigate to: ${notification.actionUrl}`)
    }
  }

  const getPriorityColor = (priority) => {
    switch (priority) {
      case "high":
        return "border-l-red-500"
      case "normal":
        return "border-l-blue-500"
      case "low":
        return "border-l-gray-300"
      default:
        return "border-l-gray-300"
    }
  }

  const NotificationCard = ({ notification }) => {
    const IconComponent = notification.icon

    return (
      <div
        className={`bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer border-l-4 ${getPriorityColor(
          notification.priority,
        )} ${!notification.isRead ? "bg-blue-50" : ""}`}
        onClick={() => handleNotificationClick(notification)}
      >
        <div className="flex items-start space-x-4">
          <div className={`p-2 rounded-full ${notification.bgColor}`}>
            <IconComponent className={`w-5 h-5 ${notification.iconColor}`} />
          </div>

          <div className="flex-1 min-w-0">
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <h3 className={`text-sm font-medium ${!notification.isRead ? "text-gray-900" : "text-gray-700"}`}>
                  {notification.title}
                </h3>
                <p className={`text-sm mt-1 ${!notification.isRead ? "text-gray-800" : "text-gray-600"}`}>
                  {notification.message}
                </p>

                <div className="flex items-center mt-2 space-x-4 text-xs text-gray-500">
                  <span>{formatTime(notification.timestamp)}</span>
                  {notification.relatedUser && <span>by {notification.relatedUser}</span>}
                  {notification.priority === "high" && (
                    <span className="px-2 py-1 bg-red-100 text-red-800 rounded-full">High Priority</span>
                  )}
                </div>
              </div>

              {!notification.isRead && <div className="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>}
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <button
              onClick={(e) => {
                e.stopPropagation()
                markAsRead(notification.id)
              }}
              className="p-1 text-gray-400 hover:text-green-600 rounded"
              title="Mark as read"
            >
              <Check className="w-4 h-4" />
            </button>
            <button
              onClick={(e) => {
                e.stopPropagation()
                archiveNotification(notification.id)
              }}
              className="p-1 text-gray-400 hover:text-blue-600 rounded"
              title="Archive"
            >
              <Archive className="w-4 h-4" />
            </button>
            <button
              onClick={(e) => {
                e.stopPropagation()
                deleteNotification(notification.id)
              }}
              className="p-1 text-gray-400 hover:text-red-600 rounded"
              title="Delete"
            >
              <Trash2 className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    )
  }

  const unreadCount = notifications.filter((n) => !n.isRead).length

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-4xl mx-auto">
            <div className="flex items-center justify-between mb-6">
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Notifications</h1>
                <p className="text-gray-600 mt-1">
                  {unreadCount > 0 ? `You have ${unreadCount} unread notifications` : "All caught up!"}
                </p>
              </div>

              {unreadCount > 0 && (
                <button
                  onClick={markAllAsRead}
                  className="flex items-center px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50"
                >
                  <Check className="w-4 h-4 mr-2" />
                  Mark all as read
                </button>
              )}
            </div>

            {/* Filters */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
              <div className="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div className="flex items-center space-x-2">
                  <Filter className="w-4 h-4 text-gray-400" />
                  <span className="text-sm font-medium text-gray-700">Filter by:</span>
                </div>

                <div className="flex flex-wrap gap-4">
                  <select
                    value={filterType}
                    onChange={(e) => setFilterType(e.target.value)}
                    className="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="all">All Types</option>
                    <option value="upload">File Uploads</option>
                    <option value="comment">Comments</option>
                    <option value="rejection">Rejections</option>
                    <option value="task">Tasks</option>
                    <option value="group">Groups</option>
                    <option value="rating">Ratings</option>
                    <option value="system">System</option>
                  </select>

                  <select
                    value={filterStatus}
                    onChange={(e) => setFilterStatus(e.target.value)}
                    className="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="all">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                  </select>
                </div>
              </div>
            </div>

            {/* Notifications List */}
            <div className="space-y-4">
              {filteredNotifications.map((notification) => (
                <NotificationCard key={notification.id} notification={notification} />
              ))}
            </div>

            {filteredNotifications.length === 0 && (
              <div className="text-center py-12">
                <Bell className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No notifications found</h3>
                <p className="text-gray-500">
                  {filterType !== "all" || filterStatus !== "all"
                    ? "Try adjusting your filters"
                    : "You're all caught up! No new notifications."}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  )
}

export default NotificationsView
