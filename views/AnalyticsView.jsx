"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { analyticsAPI, logsAPI, filesAPI, usersAPI } from "../services/api"
import { TrendingUp, Users, FileText, CheckCircle, Clock, Download, Calendar, AlertCircle } from "lucide-react"

const AnalyticsView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [dateRange, setDateRange] = useState("30")
  const [analytics, setAnalytics] = useState({})
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const loadAnalytics = async () => {
      try {
        setLoading(true)
        setError(null)

        // Cargar datos de múltiples fuentes
        const [dashboardData, logsData, filesData, usersData] = await Promise.allSettled([
          analyticsAPI.getDashboard(),
          logsAPI.getAnalyticsData({
            startDate: getDateByPeriod(dateRange).start,
            endDate: getDateByPeriod(dateRange).end
          }),
          filesAPI.getFileStats(),
          usersAPI.getUsers({ limit: 1 })
        ])

        // Procesar datos reales
        const processedAnalytics = {
          overview: {
            totalFiles: dashboardData.status === 'fulfilled' ? dashboardData.value.totalFiles || 0 : 0,
            totalUsers: usersData.status === 'fulfilled' ? usersData.value.pagination?.total || 0 : 0,
            activeUsers: dashboardData.status === 'fulfilled' ? dashboardData.value.activeUsers || 0 : 0,
            pendingValidations: logsData.status === 'fulfilled' ? logsData.value.dailyStats?.filter(s => s.metricType === 'file_upload').length || 0 : 0,
            approvedEvidences: filesData.status === 'fulfilled' ? filesData.value.totalFiles || 0 : 0,
            rejectedEvidences: 0,
            totalGroups: dashboardData.status === 'fulfilled' ? dashboardData.value.totalGroups || 0 : 0,
            totalTasks: 0,
          },
          fileStats: {
            byType: dashboardData.status === 'fulfilled' ? dashboardData.value.filesByCategory || [] : [],
            uploadTrend: logsData.status === 'fulfilled' ? processUploadTrend(logsData.value.dailyStats) : []
          },
          userActivity: {
            topUsers: dashboardData.status === 'fulfilled' ? dashboardData.value.userActivity || [] : [],
            activityTrend: logsData.status === 'fulfilled' ? processUserActivity(logsData.value.dailyStats) : []
          },
          groupStats: {
            mostActive: dashboardData.status === 'fulfilled' ? dashboardData.value.topGroups || [] : []
          },
          performance: {
            avgResponseTime: 0,
            avgValidationTime: 0,
            systemUptime: 99.9,
            storageUsed: 0
          }
        }

        setAnalytics(processedAnalytics)

      } catch (err) {
        console.error('Error loading analytics:', err)
        setError('Error cargando analytics')
        setAnalytics({
          overview: { totalFiles: 0, totalUsers: 0, activeUsers: 0, pendingValidations: 0, approvedEvidences: 0, rejectedEvidences: 0, totalGroups: 0, totalTasks: 0 },
          fileStats: { byType: [], uploadTrend: [] },
          userActivity: { topUsers: [], activityTrend: [] },
          groupStats: { mostActive: [] },
          performance: { avgResponseTime: 0, avgValidationTime: 0, systemUptime: 0, storageUsed: 0 }
        })
      } finally {
        setLoading(false)
      }
    }

    loadAnalytics()
  }, [dateRange])

  // Funciones helper para procesar datos
  const getDateByPeriod = (period) => {
    const end = new Date()
    const start = new Date()

    switch (period) {
      case '7d':
        start.setDate(start.getDate() - 7)
        break
      case '30d':
        start.setDate(start.getDate() - 30)
        break
      case '90d':
        start.setDate(start.getDate() - 90)
        break
      default:
        start.setDate(start.getDate() - 7)
    }

    return {
      start: start.toISOString().split('T')[0],
      end: end.toISOString().split('T')[0]
    }
  }

  const processUploadTrend = (dailyStats) => {
    if (!dailyStats) return []
    return dailyStats
      .filter(stat => stat.metricType === 'file_upload')
      .map(stat => ({ date: stat.date, uploads: stat.totalCount || 0 }))
  }

  const processUserActivity = (dailyStats) => {
    if (!dailyStats) return []
    return dailyStats
      .filter(stat => stat.metricType === 'user_login')
      .map(stat => ({ date: stat.date, active: stat.totalCount || 0 }))
  }

  const StatCard = ({ title, value, icon: Icon, color = "blue", trend = null, subtitle = null }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
          {subtitle && <p className="text-sm text-gray-500 mt-1">{subtitle}</p>}
          {trend && (
            <div className={`flex items-center mt-2 text-sm ${trend > 0 ? "text-green-600" : "text-red-600"}`}>
              <TrendingUp className="w-4 h-4 mr-1" />
              {trend > 0 ? "+" : ""}
              {trend}% from last period
            </div>
          )}
        </div>
        <div className={`w-12 h-12 bg-${color}-100 rounded-lg flex items-center justify-center`}>
          <Icon className={`w-6 h-6 text-${color}-600`} />
        </div>
      </div>
    </div>
  )

  const SimpleBarChart = ({ data, title, color = "blue" }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-4">{title}</h3>
      <div className="space-y-3">
        {data.map((item, index) => (
          <div key={index} className="flex items-center justify-between">
            <span className="text-sm text-gray-600">{item.type || item.name}</span>
            <div className="flex items-center space-x-3">
              <div className="w-32 bg-gray-200 rounded-full h-2">
                <div
                  className={`bg-${color}-500 h-2 rounded-full`}
                  style={{
                    width: `${item.percentage || (item.count / Math.max(...data.map((d) => d.count || d.uploads || d.members))) * 100}%`,
                  }}
                ></div>
              </div>
              <span className="text-sm font-medium text-gray-900 w-12 text-right">
                {item.count || item.uploads || item.members}
              </span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )

  const LineChart = ({ data, title, color = "blue" }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-4">{title}</h3>
      <div className="h-64 flex items-end space-x-2">
        {data.map((item, index) => {
          const maxValue = Math.max(...data.map((d) => d.uploads || d.active))
          const height = ((item.uploads || item.active) / maxValue) * 100

          return (
            <div key={index} className="flex-1 flex flex-col items-center">
              <div
                className={`w-full bg-${color}-500 rounded-t transition-all duration-300 hover:bg-${color}-600`}
                style={{ height: `${height}%` }}
                title={`${new Date(item.date).toLocaleDateString()}: ${item.uploads || item.active}`}
              ></div>
              <span className="text-xs text-gray-500 mt-2">{new Date(item.date).getDate()}</span>
            </div>
          )
        })}
      </div>
    </div>
  )

  const exportData = (format) => {
    // Simulate data export
    console.log(`Exporting analytics data as ${format}`)
    alert(`Analytics data exported as ${format}`)
  }

  if (!isAdmin) {
    return (
      <div className="flex h-screen bg-gray-50">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        <div className="flex-1 flex flex-col overflow-hidden">
          <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />
          <main className="flex-1 flex items-center justify-center">
            <div className="text-center">
              <AlertCircle className="mx-auto h-12 w-12 text-red-500 mb-4" />
              <h2 className="text-xl font-semibold text-gray-900 mb-2">Access Denied</h2>
              <p className="text-gray-600">This page is only accessible to administrators.</p>
            </div>
          </main>
        </div>
      </div>
    )
  }

  if (loading) {
    return (
      <div className="flex h-screen bg-gray-50">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        <div className="flex-1 flex flex-col overflow-hidden">
          <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />
          <main className="flex-1 flex items-center justify-center">
            <div className="text-center">
              <div className="spinner mx-auto mb-4"></div>
              <p className="text-gray-600">Loading analytics data...</p>
            </div>
          </main>
        </div>
      </div>
    )
  }

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-7xl mx-auto">
            <div className="flex items-center justify-between mb-6">
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
                <p className="text-gray-600 mt-1">Comprehensive system analytics and performance metrics</p>
              </div>

              <div className="flex items-center space-x-4">
                <div className="flex items-center space-x-2">
                  <Calendar className="w-4 h-4 text-gray-400" />
                  <select
                    value={dateRange}
                    onChange={(e) => setDateRange(e.target.value)}
                    className="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                  </select>
                </div>

                <div className="flex items-center space-x-2">
                  <button
                    onClick={() => exportData("PDF")}
                    className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                  >
                    <Download className="w-4 h-4 mr-2" />
                    Export PDF
                  </button>
                  <button
                    onClick={() => exportData("CSV")}
                    className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                  >
                    <Download className="w-4 h-4 mr-2" />
                    Export CSV
                  </button>
                </div>
              </div>
            </div>

            {/* Overview Stats */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <StatCard
                title="Total Files"
                value={analytics.overview?.totalFiles?.toLocaleString()}
                icon={FileText}
                color="blue"
                trend={12.5}
              />
              <StatCard
                title="Active Users"
                value={analytics.overview?.activeUsers}
                icon={Users}
                color="green"
                trend={8.3}
                subtitle={`of ${analytics.overview?.totalUsers} total`}
              />
              <StatCard
                title="Approved Evidences"
                value={analytics.overview?.approvedEvidences}
                icon={CheckCircle}
                color="green"
                trend={15.2}
              />
              <StatCard
                title="Pending Validations"
                value={analytics.overview?.pendingValidations}
                icon={Clock}
                color="yellow"
                trend={-5.7}
              />
            </div>

            {/* Charts Row 1 */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              <SimpleBarChart data={analytics.fileStats?.byType || []} title="Files by Type" color="blue" />
              <LineChart
                data={analytics.fileStats?.uploadTrend || []}
                title="Upload Trend (Last 10 Days)"
                color="green"
              />
            </div>

            {/* Charts Row 2 */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              <LineChart
                data={analytics.userActivity?.activityTrend || []}
                title="User Activity Trend"
                color="purple"
              />
              <SimpleBarChart data={analytics.groupStats?.mostActive || []} title="Most Active Groups" color="indigo" />
            </div>

            {/* Performance Metrics */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <StatCard
                title="Avg Response Time"
                value={`${analytics.performance?.avgResponseTime}s`}
                icon={Clock}
                color="blue"
                subtitle="System response"
              />
              <StatCard
                title="Avg Validation Time"
                value={`${analytics.performance?.avgValidationTime}h`}
                icon={CheckCircle}
                color="green"
                subtitle="Evidence review"
              />
              <StatCard
                title="System Uptime"
                value={`${analytics.performance?.systemUptime}%`}
                icon={TrendingUp}
                color="green"
                subtitle="Last 30 days"
              />
              <StatCard
                title="Storage Used"
                value={`${analytics.performance?.storageUsed}%`}
                icon={FileText}
                color="yellow"
                subtitle="of total capacity"
              />
            </div>

            {/* Top Users Table */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Top Contributors</h3>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Uploads
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Approvals
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Activity Score
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {analytics.userActivity?.topUsers?.map((user, index) => (
                      <tr key={index} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <img
                              src={user.avatar || "/placeholder.svg"}
                              alt={user.name}
                              className="w-8 h-8 rounded-full mr-3"
                            />
                            <div>
                              <div className="text-sm font-medium text-gray-900">{user.name}</div>
                              <div className="text-sm text-gray-500">Rank #{index + 1}</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{user.uploads}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{user.approvals}</td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <div className="w-16 bg-gray-200 rounded-full h-2 mr-2">
                              <div
                                className="bg-blue-500 h-2 rounded-full"
                                style={{ width: `${(user.uploads + user.approvals) / 2}%` }}
                              ></div>
                            </div>
                            <span className="text-sm text-gray-900">
                              {Math.round((user.uploads + user.approvals) / 2)}
                            </span>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}

export default AnalyticsView
