"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import { useNavigate } from "react-router-dom"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import {
  Upload,
  Users,
  FileText,
  Shield,
  MessageCircle,
  CheckSquare,
  BarChart3,
  Clock,
  AlertCircle,
} from "lucide-react"

const HomeView = () => {
  const { user, isAdmin } = useAuth()
  const navigate = useNavigate()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [stats, setStats] = useState({})

  useEffect(() => {
    // Load user-specific stats
    const loadStats = () => {
      if (isAdmin) {
        setStats({
          totalFiles: 1247,
          pendingTasks: 23,
          activeUsers: 156,
          completedValidations: 89,
        })
      } else {
        setStats({
          myFiles: 12,
          pendingTasks: 3,
          myGroups: 5,
          notifications: 7,
        })
      }
    }

    loadStats()
  }, [isAdmin])

  const QuickActionCard = ({ icon: Icon, title, description, onClick, color = "blue" }) => (
    <div
      onClick={onClick}
      className={`bg-white rounded-lg shadow-sm border border-gray-200 p-6 cursor-pointer hover:shadow-md transition-shadow hover:border-${color}-200`}
    >
      <div className={`w-12 h-12 bg-${color}-100 rounded-lg flex items-center justify-center mb-4`}>
        <Icon className={`w-6 h-6 text-${color}-600`} />
      </div>
      <h3 className="font-semibold text-gray-900 mb-2">{title}</h3>
      <p className="text-sm text-gray-600">{description}</p>
    </div>
  )

  const StatCard = ({ title, value, icon: Icon, color = "blue" }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
        </div>
        <div className={`w-12 h-12 bg-${color}-100 rounded-lg flex items-center justify-center`}>
          <Icon className={`w-6 h-6 text-${color}-600`} />
        </div>
      </div>
    </div>
  )

  const AdminDashboard = () => (
    <div className="space-y-6">
      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard title="Total Files" value={stats.totalFiles?.toLocaleString()} icon={FileText} color="blue" />
        <StatCard title="Pending Tasks" value={stats.pendingTasks} icon={Clock} color="orange" />
        <StatCard title="Active Users" value={stats.activeUsers} icon={Users} color="green" />
        <StatCard title="Validations" value={stats.completedValidations} icon={CheckSquare} color="purple" />
      </div>

      {/* Quick Actions */}
      <div>
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <QuickActionCard
            icon={BarChart3}
            title="View Analytics"
            description="Access detailed system analytics and reports"
            onClick={() => navigate("/admin/analytics")}
            color="blue"
          />
          <QuickActionCard
            icon={CheckSquare}
            title="Manage Tasks"
            description="Create, assign, and evaluate user tasks"
            onClick={() => navigate("/admin/tasks")}
            color="green"
          />
          <QuickActionCard
            icon={Users}
            title="Admin Groups"
            description="Manage all groups and their members"
            onClick={() => navigate("/admin/groups")}
            color="purple"
          />
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
        <div className="space-y-4">
          {[
            { user: "John Doe", action: "uploaded evidence", item: "Project Report.pdf", time: "2 hours ago" },
            { user: "Jane Smith", action: "completed task", item: "Data Analysis", time: "4 hours ago" },
            { user: "Mike Johnson", action: "joined group", item: "Research Team", time: "6 hours ago" },
          ].map((activity, index) => (
            <div key={index} className="flex items-center space-x-3 py-2">
              <div className="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                <span className="text-xs font-medium text-gray-600">
                  {activity.user
                    .split(" ")
                    .map((n) => n[0])
                    .join("")}
                </span>
              </div>
              <div className="flex-1">
                <p className="text-sm text-gray-900">
                  <span className="font-medium">{activity.user}</span> {activity.action}{" "}
                  <span className="font-medium">{activity.item}</span>
                </p>
                <p className="text-xs text-gray-500">{activity.time}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )

  const UserDashboard = () => (
    <div className="space-y-6">
      {/* Welcome Section */}
      <div className="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white">
        <h2 className="text-2xl font-bold mb-2">Welcome back, {user?.name}!</h2>
        <p className="text-blue-100">Here's what's happening with your projects today.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard title="My Files" value={stats.myFiles} icon={FileText} color="blue" />
        <StatCard title="Pending Tasks" value={stats.pendingTasks} icon={AlertCircle} color="orange" />
        <StatCard title="My Groups" value={stats.myGroups} icon={Users} color="green" />
        <StatCard title="Notifications" value={stats.notifications} icon={MessageCircle} color="purple" />
      </div>

      {/* Quick Actions */}
      <div>
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <QuickActionCard
            icon={Upload}
            title="Upload Evidence"
            description="Upload new files and evidence to your projects"
            onClick={() => navigate("/upload")}
            color="blue"
          />
          <QuickActionCard
            icon={Users}
            title="My Groups"
            description="View and manage your group memberships"
            onClick={() => navigate("/groups")}
            color="green"
          />
          <QuickActionCard
            icon={Shield}
            title="My Evidences"
            description="Review your submitted evidences and feedback"
            onClick={() => navigate("/evidences")}
            color="purple"
          />
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Your Recent Activity</h3>
        <div className="space-y-4">
          {[
            { action: "Uploaded", item: "Research Document.pdf", status: "Approved", time: "1 day ago" },
            { action: "Submitted", item: "Task: Data Collection", status: "Under Review", time: "2 days ago" },
            { action: "Joined", item: "Development Team Group", status: "Active", time: "3 days ago" },
          ].map((activity, index) => (
            <div key={index} className="flex items-center justify-between py-2">
              <div className="flex items-center space-x-3">
                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div>
                  <p className="text-sm text-gray-900">
                    {activity.action} <span className="font-medium">{activity.item}</span>
                  </p>
                  <p className="text-xs text-gray-500">{activity.time}</p>
                </div>
              </div>
              <span
                className={`px-2 py-1 text-xs rounded-full ${
                  activity.status === "Approved"
                    ? "bg-green-100 text-green-800"
                    : activity.status === "Under Review"
                      ? "bg-yellow-100 text-yellow-800"
                      : "bg-blue-100 text-blue-800"
                }`}
              >
                {activity.status}
              </span>
            </div>
          ))}
        </div>
      </div>
    </div>
  )

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-7xl mx-auto">{isAdmin ? <AdminDashboard /> : <UserDashboard />}</div>
        </main>
      </div>
    </div>
  )
}

export default HomeView
