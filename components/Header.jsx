"use client"

import { useState } from "react"
import { useAuth } from "../context/AuthContext"
import { useNavigate } from "react-router-dom"
import {
  Menu,
  Search,
  Bell,
  Settings,
  User,
  LogOut,
  ChevronDown,
  MessageCircle,
  FileText,
  Download,
} from "lucide-react"
import GlobalSearch from "./GlobalSearch"
import ReportGenerator from "./ReportGenerator"
import DataExport from "./DataExport"

const Header = ({ onToggleSidebar, sidebarOpen }) => {
  const { user, isAdmin, logout } = useAuth()
  const navigate = useNavigate()
  const [showUserMenu, setShowUserMenu] = useState(false)
  const [showGlobalSearch, setShowGlobalSearch] = useState(false)
  const [showReportGenerator, setShowReportGenerator] = useState(false)
  const [showDataExport, setShowDataExport] = useState(false)
  const [notifications] = useState([
    {
      id: 1,
      title: "New Evidence Submitted",
      message: "John Doe submitted new evidence for review",
      time: "5 min ago",
      unread: true,
    },
    {
      id: 2,
      title: "Task Completed",
      message: "Data analysis task has been completed",
      time: "1 hour ago",
      unread: true,
    },
    {
      id: 3,
      title: "Group Invitation",
      message: "You've been invited to join Research Team Beta",
      time: "2 hours ago",
      unread: false,
    },
  ])

  const handleLogout = () => {
    logout()
    navigate("/login")
    setShowUserMenu(false)
  }

  const handleProfileClick = () => {
    navigate("/profile")
    setShowUserMenu(false)
  }

  const handleNotificationClick = () => {
    navigate("/notifications")
  }

  const unreadCount = notifications.filter((n) => n.unread).length

  return (
    <>
      <header className="bg-white border-b border-gray-200 px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-30">
        {/* Left Section */}
        <div className="flex items-center space-x-4">
          <button onClick={onToggleSidebar} className="p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden">
            <Menu className="w-5 h-5 text-gray-600" />
          </button>

          {/* Search Button */}
          <button
            onClick={() => setShowGlobalSearch(true)}
            className="flex items-center space-x-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            <Search className="w-4 h-4 text-gray-500" />
            <span className="text-sm text-gray-500 hidden sm:inline">Search everything...</span>
            <kbd className="hidden sm:inline-flex items-center px-2 py-1 text-xs font-mono bg-white border border-gray-300 rounded">
              ⌘K
            </kbd>
          </button>
        </div>

        {/* Right Section */}
        <div className="flex items-center space-x-4">
          {/* Admin Tools */}
          {isAdmin && (
            <div className="hidden md:flex items-center space-x-2">
              <button
                onClick={() => setShowReportGenerator(true)}
                className="p-2 rounded-lg hover:bg-gray-100 transition-colors tooltip"
                data-tooltip="Generate Reports"
              >
                <FileText className="w-5 h-5 text-gray-600" />
              </button>
              <button
                onClick={() => setShowDataExport(true)}
                className="p-2 rounded-lg hover:bg-gray-100 transition-colors tooltip"
                data-tooltip="Export Data"
              >
                <Download className="w-5 h-5 text-gray-600" />
              </button>
              <button
                onClick={() => navigate("/settings")}
                className="p-2 rounded-lg hover:bg-gray-100 transition-colors tooltip"
                data-tooltip="System Settings"
              >
                <Settings className="w-5 h-5 text-gray-600" />
              </button>
            </div>
          )}

          {/* Messages */}
          <button
            onClick={() => navigate("/messages")}
            className="p-2 rounded-lg hover:bg-gray-100 transition-colors relative tooltip"
            data-tooltip="Messages"
          >
            <MessageCircle className="w-5 h-5 text-gray-600" />
            <span className="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full"></span>
          </button>

          {/* Notifications */}
          <button
            onClick={handleNotificationClick}
            className="p-2 rounded-lg hover:bg-gray-100 transition-colors relative tooltip"
            data-tooltip="Notifications"
          >
            <Bell className="w-5 h-5 text-gray-600" />
            {unreadCount > 0 && (
              <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {unreadCount}
              </span>
            )}
          </button>

          {/* User Menu */}
          <div className="relative">
            <button
              onClick={() => setShowUserMenu(!showUserMenu)}
              className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <img
                src={user?.avatar || "/placeholder.svg?height=32&width=32"}
                alt={user?.name}
                className="w-8 h-8 rounded-full object-cover"
              />
              <div className="hidden sm:block text-left">
                <p className="text-sm font-medium text-gray-900">{user?.name}</p>
                <p className="text-xs text-gray-500 capitalize">{user?.role}</p>
              </div>
              <ChevronDown
                className={`w-4 h-4 text-gray-500 transition-transform duration-200 ${
                  showUserMenu ? "rotate-180" : ""
                }`}
              />
            </button>

            {/* User Dropdown */}
            {showUserMenu && (
              <div className="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                <div className="px-4 py-3 border-b border-gray-200">
                  <p className="text-sm font-medium text-gray-900">{user?.name}</p>
                  <p className="text-sm text-gray-500">{user?.email}</p>
                  <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2 capitalize">
                    {user?.role}
                  </span>
                </div>

                <button
                  onClick={handleProfileClick}
                  className="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  <User className="w-4 h-4 mr-3" />
                  View Profile
                </button>

                {isAdmin && (
                  <button
                    onClick={() => {
                      navigate("/settings")
                      setShowUserMenu(false)
                    }}
                    className="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  >
                    <Settings className="w-4 h-4 mr-3" />
                    System Settings
                  </button>
                )}

                <div className="border-t border-gray-200 mt-2 pt-2">
                  <button
                    onClick={handleLogout}
                    className="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <LogOut className="w-4 h-4 mr-3" />
                    Sign Out
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </header>

      {/* Global Search Modal */}
      <GlobalSearch isOpen={showGlobalSearch} onClose={() => setShowGlobalSearch(false)} />

      {/* Report Generator Modal */}
      <ReportGenerator isOpen={showReportGenerator} onClose={() => setShowReportGenerator(false)} />

      {/* Data Export Modal */}
      <DataExport isOpen={showDataExport} onClose={() => setShowDataExport(false)} />

      {/* Keyboard Shortcuts */}
      <div className="hidden">
        <div
          onKeyDown={(e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === "k") {
              e.preventDefault()
              setShowGlobalSearch(true)
            }
          }}
          tabIndex={-1}
        />
      </div>
    </>
  )
}

export default Header
