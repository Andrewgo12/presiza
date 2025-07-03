"use client"

import { useState } from "react"
import { Download, FileText, BarChart3, PieChart, TrendingUp, X } from "lucide-react"

const ReportGenerator = ({ isOpen, onClose }) => {
  const [reportConfig, setReportConfig] = useState({
    type: "activity",
    dateRange: "30",
    format: "pdf",
    includeCharts: true,
    includeDetails: true,
    customDateRange: {
      start: "",
      end: "",
    },
    filters: {
      groups: [],
      users: [],
      fileTypes: [],
      status: "all",
    },
  })
  const [generating, setGenerating] = useState(false)
  const [progress, setProgress] = useState(0)

  const reportTypes = [
    {
      id: "activity",
      name: "Activity Report",
      description: "User activity, uploads, and engagement metrics",
      icon: TrendingUp,
      color: "blue",
    },
    {
      id: "files",
      name: "File Management Report",
      description: "File uploads, downloads, and storage analytics",
      icon: FileText,
      color: "green",
    },
    {
      id: "groups",
      name: "Group Performance Report",
      description: "Group activity, membership, and collaboration metrics",
      icon: BarChart3,
      color: "purple",
    },
    {
      id: "evidences",
      name: "Evidence Evaluation Report",
      description: "Evidence submissions, approvals, and quality metrics",
      icon: PieChart,
      color: "orange",
    },
  ]

  const availableGroups = [
    { id: 1, name: "Research Team Alpha" },
    { id: 2, name: "Development Squad" },
    { id: 3, name: "Data Analytics Hub" },
    { id: 4, name: "Design Collective" },
  ]

  const availableUsers = [
    { id: 1, name: "Dr. Smith" },
    { id: 2, name: "John Doe" },
    { id: 3, name: "Jane Wilson" },
    { id: 4, name: "Mike Chen" },
  ]

  const handleGenerateReport = async () => {
    setGenerating(true)
    setProgress(0)

    // Simulate report generation with progress
    const steps = [
      "Collecting data...",
      "Processing analytics...",
      "Generating charts...",
      "Formatting report...",
      "Finalizing document...",
    ]

    for (let i = 0; i < steps.length; i++) {
      await new Promise((resolve) => setTimeout(resolve, 1000))
      setProgress(((i + 1) / steps.length) * 100)
    }

    // Create mock report data
    const reportData = {
      title: reportTypes.find((t) => t.id === reportConfig.type)?.name,
      generatedAt: new Date().toISOString(),
      dateRange: reportConfig.dateRange,
      config: reportConfig,
      summary: {
        totalRecords: 1247,
        processedItems: 892,
        successRate: "95.2%",
        generationTime: "4.2 seconds",
      },
    }

    // Simulate file download
    const blob = new Blob([JSON.stringify(reportData, null, 2)], {
      type: reportConfig.format === "pdf" ? "application/pdf" : "application/json",
    })
    const url = URL.createObjectURL(blob)
    const a = document.createElement("a")
    a.href = url
    a.download = `${reportConfig.type}_report_${new Date().toISOString().split("T")[0]}.${reportConfig.format}`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)

    // Show success notification
    window.dispatchEvent(
      new CustomEvent("showNotification", {
        detail: {
          type: "success",
          title: "Report Generated",
          message: `${reportTypes.find((t) => t.id === reportConfig.type)?.name} has been generated successfully`,
          duration: 5000,
        },
      }),
    )

    setGenerating(false)
    setProgress(0)
    onClose()
  }

  const handleConfigChange = (key, value) => {
    setReportConfig((prev) => ({
      ...prev,
      [key]: value,
    }))
  }

  const handleFilterChange = (filterType, value) => {
    setReportConfig((prev) => ({
      ...prev,
      filters: {
        ...prev.filters,
        [filterType]: value,
      },
    }))
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto animate-scale-in">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Generate Report</h2>
              <p className="text-sm text-gray-600 mt-1">Create comprehensive reports with customizable options</p>
            </div>
            <button
              onClick={onClose}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              disabled={generating}
            >
              <X className="w-5 h-5 text-gray-500" />
            </button>
          </div>
        </div>

        <div className="p-6 space-y-8">
          {/* Report Type Selection */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-4">Report Type</label>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {reportTypes.map((type) => {
                const Icon = type.icon
                return (
                  <button
                    key={type.id}
                    onClick={() => handleConfigChange("type", type.id)}
                    disabled={generating}
                    className={`p-4 border-2 rounded-lg text-left transition-all ${
                      reportConfig.type === type.id
                        ? `border-${type.color}-500 bg-${type.color}-50`
                        : "border-gray-200 hover:border-gray-300 hover:bg-gray-50"
                    } ${generating ? "opacity-50 cursor-not-allowed" : ""}`}
                  >
                    <div className="flex items-start space-x-3">
                      <div
                        className={`p-2 rounded-lg ${
                          reportConfig.type === type.id ? `bg-${type.color}-100` : "bg-gray-100"
                        }`}
                      >
                        <Icon
                          className={`w-5 h-5 ${
                            reportConfig.type === type.id ? `text-${type.color}-600` : "text-gray-600"
                          }`}
                        />
                      </div>
                      <div className="flex-1">
                        <h3 className="font-medium text-gray-900">{type.name}</h3>
                        <p className="text-sm text-gray-500 mt-1">{type.description}</p>
                      </div>
                    </div>
                  </button>
                )
              })}
            </div>
          </div>

          {/* Date Range */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
              <select
                value={reportConfig.dateRange}
                onChange={(e) => handleConfigChange("dateRange", e.target.value)}
                disabled={generating}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
              >
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
                <option value="custom">Custom range</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
              <select
                value={reportConfig.format}
                onChange={(e) => handleConfigChange("format", e.target.value)}
                disabled={generating}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
              >
                <option value="pdf">PDF Document</option>
                <option value="excel">Excel Spreadsheet</option>
                <option value="csv">CSV Data</option>
                <option value="json">JSON Data</option>
              </select>
            </div>
          </div>

          {/* Custom Date Range */}
          {reportConfig.dateRange === "custom" && (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input
                  type="date"
                  value={reportConfig.customDateRange.start}
                  onChange={(e) =>
                    setReportConfig((prev) => ({
                      ...prev,
                      customDateRange: { ...prev.customDateRange, start: e.target.value },
                    }))
                  }
                  disabled={generating}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input
                  type="date"
                  value={reportConfig.customDateRange.end}
                  onChange={(e) =>
                    setReportConfig((prev) => ({
                      ...prev,
                      customDateRange: { ...prev.customDateRange, end: e.target.value },
                    }))
                  }
                  disabled={generating}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                />
              </div>
            </div>
          )}

          {/* Report Options */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-4">Report Options</label>
            <div className="space-y-3">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={reportConfig.includeCharts}
                  onChange={(e) => handleConfigChange("includeCharts", e.target.checked)}
                  disabled={generating}
                  className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50"
                />
                <div>
                  <span className="text-sm font-medium text-gray-700">Include Charts and Visualizations</span>
                  <p className="text-xs text-gray-500">Add graphs, charts, and visual analytics to the report</p>
                </div>
              </label>
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={reportConfig.includeDetails}
                  onChange={(e) => handleConfigChange("includeDetails", e.target.checked)}
                  disabled={generating}
                  className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50"
                />
                <div>
                  <span className="text-sm font-medium text-gray-700">Include Detailed Data Tables</span>
                  <p className="text-xs text-gray-500">Add comprehensive data tables with raw information</p>
                </div>
              </label>
            </div>
          </div>

          {/* Filters */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-4">Filters</label>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-2">Status Filter</label>
                <select
                  value={reportConfig.filters.status}
                  onChange={(e) => handleFilterChange("status", e.target.value)}
                  disabled={generating}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                >
                  <option value="all">All Status</option>
                  <option value="approved">Approved Only</option>
                  <option value="pending">Pending Only</option>
                  <option value="rejected">Rejected Only</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-600 mb-2">Specific Groups</label>
                <select
                  multiple
                  size="4"
                  value={reportConfig.filters.groups}
                  onChange={(e) =>
                    handleFilterChange(
                      "groups",
                      Array.from(e.target.selectedOptions, (option) => option.value),
                    )
                  }
                  disabled={generating}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                >
                  {availableGroups.map((group) => (
                    <option key={group.id} value={group.id}>
                      {group.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {/* Progress Bar */}
          {generating && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-2">
                <span className="text-sm font-medium text-blue-900">Generating Report...</span>
                <span className="text-sm text-blue-700">{Math.round(progress)}%</span>
              </div>
              <div className="w-full bg-blue-200 rounded-full h-2">
                <div
                  className="bg-blue-600 h-2 rounded-full transition-all duration-300 progress-fill animated"
                  style={{ width: `${progress}%` }}
                ></div>
              </div>
              <p className="text-xs text-blue-700 mt-2">This may take a few moments...</p>
            </div>
          )}

          {/* Preview */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="font-medium text-gray-900 mb-3">Report Preview</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <span className="text-gray-600">Type:</span>
                <span className="ml-2 font-medium">{reportTypes.find((t) => t.id === reportConfig.type)?.name}</span>
              </div>
              <div>
                <span className="text-gray-600">Period:</span>
                <span className="ml-2 font-medium">
                  {reportConfig.dateRange === "custom" ? "Custom Range" : `Last ${reportConfig.dateRange} days`}
                </span>
              </div>
              <div>
                <span className="text-gray-600">Format:</span>
                <span className="ml-2 font-medium uppercase">{reportConfig.format}</span>
              </div>
              <div>
                <span className="text-gray-600">Charts:</span>
                <span className="ml-2 font-medium">{reportConfig.includeCharts ? "Included" : "Excluded"}</span>
              </div>
            </div>
          </div>
        </div>

        <div className="p-6 border-t border-gray-200 bg-gray-50">
          <div className="flex justify-end space-x-3">
            <button
              onClick={onClose}
              disabled={generating}
              className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Cancel
            </button>
            <button
              onClick={handleGenerateReport}
              disabled={generating}
              className="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {generating ? (
                <>
                  <div className="spinner w-4 h-4 mr-2"></div>
                  Generating...
                </>
              ) : (
                <>
                  <Download className="w-4 h-4 mr-2" />
                  Generate Report
                </>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default ReportGenerator
