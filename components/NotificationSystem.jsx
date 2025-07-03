"use client"

import { useState, useEffect } from "react"
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from "lucide-react"

const NotificationSystem = () => {
  const [notifications, setNotifications] = useState([])

  useEffect(() => {
    // Listen for custom notification events
    const handleNotification = (event) => {
      addNotification(event.detail)
    }

    window.addEventListener("showNotification", handleNotification)

    // Add some demo notifications on mount
    setTimeout(() => {
      addNotification({
        type: "success",
        title: "Welcome!",
        message: "Evidence Management Platform loaded successfully",
        duration: 5000,
      })
    }, 1000)

    return () => {
      window.removeEventListener("showNotification", handleNotification)
    }
  }, [])

  const addNotification = (notification) => {
    const id = Date.now() + Math.random()
    const newNotification = {
      id,
      type: notification.type || "info",
      title: notification.title || "",
      message: notification.message || "",
      duration: notification.duration || 5000,
      timestamp: new Date(),
    }

    setNotifications((prev) => [...prev, newNotification])

    // Auto remove after duration
    if (newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.duration)
    }
  }

  const removeNotification = (id) => {
    setNotifications((prev) => prev.filter((notification) => notification.id !== id))
  }

  const getIcon = (type) => {
    switch (type) {
      case "success":
        return CheckCircle
      case "warning":
        return AlertTriangle
      case "error":
        return AlertCircle
      case "info":
      default:
        return Info
    }
  }

  const getColors = (type) => {
    switch (type) {
      case "success":
        return "bg-green-50 border-green-200 text-green-800"
      case "warning":
        return "bg-yellow-50 border-yellow-200 text-yellow-800"
      case "error":
        return "bg-red-50 border-red-200 text-red-800"
      case "info":
      default:
        return "bg-blue-50 border-blue-200 text-blue-800"
    }
  }

  const getIconColors = (type) => {
    switch (type) {
      case "success":
        return "text-green-600"
      case "warning":
        return "text-yellow-600"
      case "error":
        return "text-red-600"
      case "info":
      default:
        return "text-blue-600"
    }
  }

  if (notifications.length === 0) return null

  return (
    <div className="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full">
      {notifications.map((notification) => {
        const Icon = getIcon(notification.type)
        return (
          <div
            key={notification.id}
            className={`
              ${getColors(notification.type)}
              border rounded-lg p-4 shadow-lg
              animate-slide-in-right
              transition-all duration-300 ease-out
              hover:shadow-xl
            `}
          >
            <div className="flex items-start">
              <Icon className={`w-5 h-5 ${getIconColors(notification.type)} mt-0.5 mr-3 flex-shrink-0`} />
              <div className="flex-1 min-w-0">
                {notification.title && <h4 className="text-sm font-semibold mb-1 truncate">{notification.title}</h4>}
                <p className="text-sm leading-relaxed">{notification.message}</p>
                <p className="text-xs opacity-75 mt-1">{notification.timestamp.toLocaleTimeString()}</p>
              </div>
              <button
                onClick={() => removeNotification(notification.id)}
                className="ml-2 flex-shrink-0 p-1 rounded-full hover:bg-black hover:bg-opacity-10 transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
          </div>
        )
      })}
    </div>
  )
}

// Helper function to show notifications from anywhere in the app
export const showNotification = (notification) => {
  window.dispatchEvent(
    new CustomEvent("showNotification", {
      detail: notification,
    }),
  )
}

// Add the keyframes for the slide-in animation
const style = document.createElement("style")
style.textContent = `
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
`
document.head.appendChild(style)

export default NotificationSystem
