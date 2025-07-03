"use client"

import { useState } from "react"
import { useAuth } from "../context/AuthContext"
import { useNavigate, useLocation } from "react-router-dom"
import {
  Home,
  Upload,
  Users,
  FileText,
  Shield,
  MessageCircle,
  Bell,
  CheckSquare,
  BarChart3,
  Settings,
  User,
  LogOut,
  X,
  ChevronDown,
  ChevronRight,
} from "lucide-react"

const Sidebar = ({ isOpen, onClose }) => {
  const { user, isAdmin, logout } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [expandedSections, setExpandedSections] = useState({})

  const toggleSection = (sectionId) => {
    setExpandedSections((prev) => ({
      ...prev,
      [sectionId]: !prev[sectionId],
    }))
  }

  const handleNavigation = (path) => {
    navigate(path)
    if (window.innerWidth < 1024) {
      onClose()
    }
  }

  const handleLogout = () => {
    logout()
    navigate("/login")
  }

  const userMenuItems = [
    { id: "dashboard", label: "Dashboard", icon: Home, path: "/dashboard" },
    { id: "upload", label: "Upload Evidence", icon: Upload, path: "/upload" },
    { id: "groups", label: "Groups", icon: Users, path: "/groups" },
    { id: "files", label: "Files", icon: FileText, path: "/files" },
    { id: "evidences", label: "My Evidences", icon: Shield, path: "/evidences" },
    { id: "messages", label: "Messages", icon: MessageCircle, path: "/messages" },
    { id: "notifications", label: "Notifications", icon: Bell, path: "/notifications" },
    { id: "profile", label: "Profile", icon: User, path: "/profile" },
  ]

  const adminMenuItems = [
    { id: "dashboard", label: "Dashboard", icon: Home, path: "/dashboard" },
    {
      id: "management",
      label: "Management",
      icon: Settings,
      isSection: true,
      children: [
        { id: "tasks", label: "Task Management", icon: CheckSquare, path: "/admin/tasks" },
        { id: "analytics", label: "Analytics", icon: BarChart3, path: "/admin/analytics" },
        { id: "admin-groups", label: "Group Management", icon: Users, path: "/admin/groups" },
        { id: "settings", label: "System Settings", icon: Settings, path: "/settings" },
      ],
    },
    { id: "upload", label: "Upload Evidence", icon: Upload, path: "/upload" },
    { id: "groups", label: "Groups", icon: Users, path: "/groups" },
    { id: "files", label: "All Files", icon: FileText, path: "/files" },
    { id: "evidences", label: "All Evidences", icon: Shield, path: "/evidences" },
    { id: "messages", label: "Messages", icon: MessageCircle, path: "/messages" },
    { id: "notifications", label: "Notifications", icon: Bell, path: "/notifications" },
    { id: "profile", label: "Profile", icon: User, path: "/profile" },
  ]

  const menuItems = isAdmin ? adminMenuItems : userMenuItems

  const isActiveRoute = (path) => {
    return location.pathname === path
  }

  const MenuItem = ({ item, level = 0 }) => {
    const Icon = item.icon
    const isActive = isActiveRoute(item.path)
    const isExpanded = expandedSections[item.id]

    if (item.isSection) {
      return (
        <div className="mb-2">
          <button
            onClick={() => toggleSection(item.id)}
            className={`
              w-full flex items-center justify-between px-4 py-3 text-left
              text-gray-700 hover:bg-gray-100 hover:text-gray-900
              transition-all duration-200 rounded-lg
              ${level > 0 ? "ml-4" : ""}
            `}
          >
            <div className="flex items-center">
              <Icon className="w-5 h-5 mr-3" />
              <span className="font-medium">{item.label}</span>
            </div>
            {isExpanded ? (
              <ChevronDown className="w-4 h-4 transition-transform duration-200" />
            ) : (
              <ChevronRight className="w-4 h-4 transition-transform duration-200" />
            )}
          </button>

          <div
            className={`
              overflow-hidden transition-all duration-300 ease-in-out
              ${isExpanded ? "max-h-96 opacity-100" : "max-h-0 opacity-0"}
            `}
          >
            <div className="ml-4 mt-2 space-y-1">
              {item.children?.map((child) => (
                <MenuItem key={child.id} item={child} level={level + 1} />
              ))}
            </div>
          </div>
        </div>
      )
    }

    return (
      <button
        onClick={() => handleNavigation(item.path)}
        className={`
          w-full flex items-center px-4 py-3 text-left rounded-lg
          transition-all duration-200 group
          ${level > 0 ? "ml-4" : ""}
          ${
            isActive
              ? "bg-blue-100 text-blue-700 border-r-2 border-blue-500"
              : "text-gray-700 hover:bg-gray-100 hover:text-gray-900"
          }
        `}
      >
        <Icon
          className={`
            w-5 h-5 mr-3 transition-colors duration-200
            ${isActive ? "text-blue-600" : "text-gray-500 group-hover:text-gray-700"}
          `}
        />
        <span className={`font-medium ${isActive ? "text-blue-700" : ""}`}>{item.label}</span>
        {isActive && <div className="ml-auto w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>}
      </button>
    )
  }

  return (
    <>
      {/* Mobile Overlay */}
      {isOpen && <div className="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" onClick={onClose} />}

      {/* Sidebar */}
      <div
        className={`
          fixed top-0 left-0 h-full w-64 bg-white shadow-xl z-50
          transform transition-transform duration-300 ease-in-out
          lg:relative lg:translate-x-0 lg:shadow-none lg:border-r lg:border-gray-200
          ${isOpen ? "translate-x-0" : "-translate-x-full"}
        `}
      >
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b border-gray-200">
          <div className="flex items-center">
            <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
              <Shield className="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 className="text-lg font-bold text-gray-900">Evidence</h1>
              <p className="text-xs text-gray-500">Management</p>
            </div>
          </div>
          <button onClick={onClose} className="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <X className="w-5 h-5 text-gray-500" />
          </button>
        </div>

        {/* User Info */}
        <div className="p-4 border-b border-gray-200">
          <div className="flex items-center">
            <img
              src={user?.avatar || "/placeholder.svg?height=40&width=40"}
              alt={user?.name}
              className="w-10 h-10 rounded-full object-cover mr-3"
            />
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium text-gray-900 truncate">{user?.name}</p>
              <p className="text-xs text-gray-500 capitalize">{user?.role}</p>
            </div>
            <div className="w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
          </div>
        </div>

        {/* Navigation Menu */}
        <nav className="flex-1 overflow-y-auto p-4">
          <div className="space-y-2">
            {menuItems.map((item) => (
              <MenuItem key={item.id} item={item} />
            ))}
          </div>
        </nav>

        {/* Footer */}
        <div className="p-4 border-t border-gray-200">
          <button
            onClick={handleLogout}
            className="w-full flex items-center px-4 py-3 text-left text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200 group"
          >
            <LogOut className="w-5 h-5 mr-3 group-hover:transform group-hover:translate-x-1 transition-transform duration-200" />
            <span className="font-medium">Sign Out</span>
          </button>

          <div className="mt-4 text-center">
            <p className="text-xs text-gray-400">Version 1.0.0</p>
            <p className="text-xs text-gray-400">© 2024 Evidence Platform</p>
          </div>
        </div>
      </div>
    </>
  )
}

export default Sidebar
