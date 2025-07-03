"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
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

  useEffect(() => {
    // Load mock tasks
    const mockTasks = [
      {
        id: 1,
        title: "Complete Q4 Data Analysis",
        description: "Analyze quarterly data and prepare comprehensive report with visualizations",
        assignedTo: [
          { id: 2, name: "John Doe", avatar: "/placeholder.svg?height=32&width=32" },
          { id: 3, name: "Jane Wilson", avatar: "/placeholder.svg?height=32&width=32" },
        ],
        assignedGroup: { id: 1, name: "Research Team Alpha" },
        createdBy: user.name,
        createdDate: "2024-01-10T09:00:00Z",
        dueDate: "2024-01-20T17:00:00Z",
        status: "in_progress",
        priority: "high",
        attachments: ["data_template.xlsx", "analysis_guidelines.pdf"],
        submissions: [
          {
            id: 1,
            userId: 2,
            userName: "John Doe",
            submissionDate: "2024-01-18T14:30:00Z",
            files: ["q4_analysis_draft.pdf"],
            status: "submitted",
            rating: null,
            feedback: null,
          },
        ],
        category: "Analysis",
        estimatedHours: 20,
      },
      {
        id: 2,
        title: "UI/UX Design Review",
        description: "Review and provide feedback on new dashboard interface designs",
        assignedTo: [{ id: 4, name: "Mike Chen", avatar: "/placeholder.svg?height=32&width=32" }],
        assignedGroup: { id: 4, name: "Design Collective" },
        createdBy: user.name,
        createdDate: "2024-01-12T10:30:00Z",
        dueDate: "2024-01-25T12:00:00Z",
        status: "pending",
        priority: "medium",
        attachments: ["design_requirements.pdf"],
        submissions: [],
        category: "Design",
        estimatedHours: 8,
      },
      {
        id: 3,
        title: "Database Migration Testing",
        description: "Test database migration scripts and document any issues found",
        assignedTo: [
          { id: 2, name: "John Doe", avatar: "/placeholder.svg?height=32&width=32" },
          { id: 5, name: "Sarah Johnson", avatar: "/placeholder.svg?height=32&width=32" },
        ],
        assignedGroup: { id: 2, name: "Development Squad" },
        createdBy: user.name,
        createdDate: "2024-01-08T15:45:00Z",
        dueDate: "2024-01-15T09:00:00Z",
        status: "completed",
        priority: "high",
        attachments: ["migration_scripts.sql", "test_checklist.pdf"],
        submissions: [
          {
            id: 2,
            userId: 2,
            userName: "John Doe",
            submissionDate: "2024-01-14T16:20:00Z",
            files: ["migration_test_results.pdf", "issues_log.xlsx"],
            status: "approved",
            rating: 4,
            feedback: "Excellent work! All issues were properly documented and resolved.",
          },
          {
            id: 3,
            userId: 5,
            userName: "Sarah Johnson",
            submissionDate: "2024-01-14T18:45:00Z",
            files: ["performance_analysis.pdf"],
            status: "approved",
            rating: 5,
            feedback: "Outstanding performance analysis. Very thorough testing.",
          },
        ],
        category: "Development",
        estimatedHours: 16,
      },
    ]

    setTasks(mockTasks)
    setFilteredTasks(mockTasks)
  }, [user.name])

  useEffect(() => {
    let filtered = tasks

    // Filter by search term
    if (searchTerm) {
      filtered = filtered.filter(
        (task) =>
          task.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
          task.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
          task.assignedGroup.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          task.assignedTo.some((user) => user.name.toLowerCase().includes(searchTerm.toLowerCase())),
      )
    }

    // Filter by status
    if (statusFilter !== "all") {
      filtered = filtered.filter((task) => task.status === statusFilter)
    }

    setFilteredTasks(filtered)
  }, [searchTerm, statusFilter, tasks])

  const getStatusIcon = (status) => {
    switch (status) {
      case "completed":
        return CheckSquare
      case "in_progress":
        return Clock
      case "pending":
        return AlertCircle
      default:
        return Clock
    }
  }

  const getStatusColor = (status) => {
    switch (status) {
      case "completed":
        return "text-green-600 bg-green-100"
      case "in_progress":
        return "text-blue-600 bg-blue-100"
      case "pending":
        return "text-yellow-600 bg-yellow-100"
      default:
        return "text-gray-600 bg-gray-100"
    }
  }

  const getPriorityColor = (priority) => {
    switch (priority) {
      case "high":
        return "text-red-600 bg-red-100"
      case "medium":
        return "text-yellow-600 bg-yellow-100"
      case "low":
        return "text-green-600 bg-green-100"
      default:
        return "text-gray-600 bg-gray-100"
    }
  }

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
  }

  const isOverdue = (dueDate, status) => {
    return status !== "completed" && new Date(dueDate) < new Date()
  }

  const TaskCard = ({ task }) => {
    const StatusIcon = getStatusIcon(task.status)
    const overdue = isOverdue(task.dueDate, task.status)

    return (
      <div
        className={`bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow ${overdue ? "border-l-4 border-l-red-500" : ""}`}
      >
        <div className="flex items-start justify-between mb-4">
          <div className="flex-1">
            <h3 className="font-semibold text-gray-900 mb-2">{task.title}</h3>
            <p className="text-sm text-gray-600 line-clamp-2 mb-3">{task.description}</p>
          </div>
          <div className="flex items-center space-x-2">
            <span className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(task.status)}`}>
              <StatusIcon className="w-3 h-3 inline mr-1" />
              {task.status.replace("_", " ")}
            </span>
            <span className={`px-2 py-1 text-xs font-medium rounded-full ${getPriorityColor(task.priority)}`}>
              {task.priority}
            </span>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
          <div className="flex items-center text-gray-600">
            <Users className="w-4 h-4 mr-2" />
            {task.assignedGroup.name}
          </div>
          <div className="flex items-center text-gray-600">
            <Calendar className="w-4 h-4 mr-2" />
            Due: {formatDate(task.dueDate)}
            {overdue && <span className="ml-2 text-red-600 font-medium">(Overdue)</span>}
          </div>
          <div className="flex items-center text-gray-600">
            <User className="w-4 h-4 mr-2" />
            {task.assignedTo.length} assigned
          </div>
          <div className="flex items-center text-gray-600">
            <Clock className="w-4 h-4 mr-2" />
            {task.estimatedHours}h estimated
          </div>
        </div>

        <div className="flex items-center justify-between mb-4">
          <div className="flex -space-x-2">
            {task.assignedTo.slice(0, 3).map((user) => (
              <img
                key={user.id}
                src={user.avatar || "/placeholder.svg"}
                alt={user.name}
                className="w-8 h-8 rounded-full border-2 border-white"
                title={user.name}
              />
            ))}
            {task.assignedTo.length > 3 && (
              <div className="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600">
                +{task.assignedTo.length - 3}
              </div>
            )}
          </div>

          <div className="text-sm text-gray-500">
            {task.submissions.length} / {task.assignedTo.length} submitted
          </div>
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4 text-sm text-gray-500">
            <span>{task.category}</span>
            <span>{task.attachments.length} attachments</span>
          </div>

          <div className="flex items-center space-x-2">
            <button
              onClick={() => {
                setSelectedTask(task)
                setShowEvaluateModal(true)
              }}
              className="p-2 text-gray-400 hover:text-blue-600 rounded-full hover:bg-blue-50"
              title="View/Evaluate"
            >
              <Eye className="w-4 h-4" />
            </button>
            <button className="p-2 text-gray-400 hover:text-green-600 rounded-full hover:bg-green-50" title="Edit">
              <Edit className="w-4 h-4" />
            </button>
            <button className="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-red-50" title="Delete">
              <Trash2 className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    )
  }

  const CreateTaskModal = () => {
    const [formData, setFormData] = useState({
      title: "",
      description: "",
      assignedGroup: "",
      assignedUsers: [],
      dueDate: "",
      priority: "medium",
      category: "",
      estimatedHours: "",
      attachments: [],
    })

    const availableGroups = [
      { id: 1, name: "Research Team Alpha" },
      { id: 2, name: "Development Squad" },
      { id: 3, name: "Data Analytics Hub" },
      { id: 4, name: "Design Collective" },
    ]

    const availableUsers = [
      { id: 2, name: "John Doe", avatar: "/placeholder.svg?height=32&width=32" },
      { id: 3, name: "Jane Wilson", avatar: "/placeholder.svg?height=32&width=32" },
      { id: 4, name: "Mike Chen", avatar: "/placeholder.svg?height=32&width=32" },
      { id: 5, name: "Sarah Johnson", avatar: "/placeholder.svg?height=32&width=32" },
    ]

    const handleSubmit = (e) => {
      e.preventDefault()

      const newTask = {
        id: Date.now(),
        ...formData,
        assignedGroup: availableGroups.find((g) => g.id === Number.parseInt(formData.assignedGroup)),
        assignedTo: availableUsers.filter((u) => formData.assignedUsers.includes(u.id)),
        createdBy: user.name,
        createdDate: new Date().toISOString(),
        status: "pending",
        submissions: [],
        estimatedHours: Number.parseInt(formData.estimatedHours) || 0,
      }

      setTasks((prev) => [newTask, ...prev])
      setShowCreateModal(false)
      setFormData({
        title: "",
        description: "",
        assignedGroup: "",
        assignedUsers: [],
        dueDate: "",
        priority: "medium",
        category: "",
        estimatedHours: "",
        attachments: [],
      })
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div className="p-6 border-b border-gray-200">
            <h2 className="text-xl font-semibold text-gray-900">Create New Task</h2>
          </div>

          <form onSubmit={handleSubmit} className="p-6 space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
                <input
                  type="text"
                  value={formData.title}
                  onChange={(e) => setFormData((prev) => ({ ...prev, title: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData((prev) => ({ ...prev, description: e.target.value }))}
                  required
                  rows={4}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Assigned Group *</label>
                <select
                  value={formData.assignedGroup}
                  onChange={(e) => setFormData((prev) => ({ ...prev, assignedGroup: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select group</option>
                  {availableGroups.map((group) => (
                    <option key={group.id} value={group.id}>
                      {group.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                <input
                  type="datetime-local"
                  value={formData.dueDate}
                  onChange={(e) => setFormData((prev) => ({ ...prev, dueDate: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select
                  value={formData.priority}
                  onChange={(e) => setFormData((prev) => ({ ...prev, priority: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select
                  value={formData.category}
                  onChange={(e) => setFormData((prev) => ({ ...prev, category: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select category</option>
                  <option value="Analysis">Analysis</option>
                  <option value="Development">Development</option>
                  <option value="Design">Design</option>
                  <option value="Research">Research</option>
                  <option value="Testing">Testing</option>
                </select>
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Assign to Users</label>
                <div className="space-y-2 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-3">
                  {availableUsers.map((user) => (
                    <label key={user.id} className="flex items-center space-x-3">
                      <input
                        type="checkbox"
                        checked={formData.assignedUsers.includes(user.id)}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setFormData((prev) => ({ ...prev, assignedUsers: [...prev.assignedUsers, user.id] }))
                          } else {
                            setFormData((prev) => ({
                              ...prev,
                              assignedUsers: prev.assignedUsers.filter((id) => id !== user.id),
                            }))
                          }
                        }}
                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <img src={user.avatar || "/placeholder.svg"} alt={user.name} className="w-6 h-6 rounded-full" />
                      <span className="text-sm text-gray-900">{user.name}</span>
                    </label>
                  ))}
                </div>
              </div>
            </div>

            <div className="flex justify-end space-x-3 pt-4 border-t border-gray-200">
              <button
                type="button"
                onClick={() => setShowCreateModal(false)}
                className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
              >
                Create Task
              </button>
            </div>
          </form>
        </div>
      </div>
    )
  }

  const EvaluateTaskModal = () => {
    const [selectedSubmission, setSelectedSubmission] = useState(null)
    const [evaluationData, setEvaluationData] = useState({
      rating: 0,
      feedback: "",
      status: "approved",
    })

    if (!selectedTask) return null

    const handleEvaluateSubmission = (submission) => {
      setSelectedSubmission(submission)
      setEvaluationData({
        rating: submission.rating || 0,
        feedback: submission.feedback || "",
        status: submission.status || "submitted",
      })
    }

    const handleSubmitEvaluation = () => {
      if (!selectedSubmission) return

      setTasks((prev) =>
        prev.map((task) =>
          task.id === selectedTask.id
            ? {
                ...task,
                submissions: task.submissions.map((sub) =>
                  sub.id === selectedSubmission.id
                    ? {
                        ...sub,
                        rating: evaluationData.rating,
                        feedback: evaluationData.feedback,
                        status: evaluationData.status,
                      }
                    : sub,
                ),
              }
            : task,
        ),
      )

      setSelectedSubmission(null)
      setEvaluationData({ rating: 0, feedback: "", status: "approved" })
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div className="p-6 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-semibold text-gray-900">{selectedTask.title}</h2>
              <button onClick={() => setShowEvaluateModal(false)} className="text-gray-400 hover:text-gray-600">
                ×
              </button>
            </div>
          </div>

          <div className="p-6 space-y-6">
            {/* Task Details */}
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-4">Task Details</h3>
              <div className="bg-gray-50 rounded-lg p-4">
                <p className="text-gray-700 mb-4">{selectedTask.description}</p>
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="font-medium text-gray-700">Due Date:</span>
                    <span className="ml-2 text-gray-600">{formatDate(selectedTask.dueDate)}</span>
                  </div>
                  <div>
                    <span className="font-medium text-gray-700">Priority:</span>
                    <span className={`ml-2 px-2 py-1 text-xs rounded-full ${getPriorityColor(selectedTask.priority)}`}>
                      {selectedTask.priority}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* Submissions */}
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-4">
                Submissions ({selectedTask.submissions.length})
              </h3>

              {selectedTask.submissions.length === 0 ? (
                <div className="text-center py-8 bg-gray-50 rounded-lg">
                  <CheckSquare className="mx-auto h-8 w-8 text-gray-400 mb-2" />
                  <p className="text-gray-500">No submissions yet</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {selectedTask.submissions.map((submission) => (
                    <div key={submission.id} className="border border-gray-200 rounded-lg p-4">
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center space-x-3">
                          <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span className="text-sm font-medium text-blue-600">{submission.userName.charAt(0)}</span>
                          </div>
                          <div>
                            <h4 className="font-medium text-gray-900">{submission.userName}</h4>
                            <p className="text-sm text-gray-500">Submitted {formatDate(submission.submissionDate)}</p>
                          </div>
                        </div>
                        <div className="flex items-center space-x-2">
                          {submission.rating && (
                            <div className="flex items-center">
                              {[1, 2, 3, 4, 5].map((star) => (
                                <Star
                                  key={star}
                                  className={`w-4 h-4 ${
                                    star <= submission.rating ? "text-yellow-400 fill-current" : "text-gray-300"
                                  }`}
                                />
                              ))}
                            </div>
                          )}
                          <span
                            className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(submission.status)}`}
                          >
                            {submission.status}
                          </span>
                        </div>
                      </div>

                      <div className="mb-3">
                        <p className="text-sm text-gray-700 mb-2">Files:</p>
                        <div className="flex flex-wrap gap-2">
                          {submission.files.map((file, index) => (
                            <span key={index} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                              {file}
                            </span>
                          ))}
                        </div>
                      </div>

                      {submission.feedback && (
                        <div className="mb-3 p-3 bg-gray-50 rounded">
                          <p className="text-sm text-gray-700">{submission.feedback}</p>
                        </div>
                      )}

                      <button
                        onClick={() => handleEvaluateSubmission(submission)}
                        className="text-sm text-blue-600 hover:text-blue-800 font-medium"
                      >
                        {submission.status === "submitted" ? "Evaluate" : "Update Evaluation"}
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Evaluation Form */}
            {selectedSubmission && (
              <div className="border-t border-gray-200 pt-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4">
                  Evaluate Submission by {selectedSubmission.userName}
                </h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                      value={evaluationData.status}
                      onChange={(e) => setEvaluationData((prev) => ({ ...prev, status: e.target.value }))}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="submitted">Under Review</option>
                      <option value="approved">Approved</option>
                      <option value="rejected">Rejected</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Rating (1-5)</label>
                    <div className="flex items-center space-x-2">
                      {[1, 2, 3, 4, 5].map((star) => (
                        <button
                          key={star}
                          onClick={() => setEvaluationData((prev) => ({ ...prev, rating: star }))}
                          className={`p-1 ${
                            star <= evaluationData.rating ? "text-yellow-400" : "text-gray-300 hover:text-yellow-400"
                          }`}
                        >
                          <Star className="w-6 h-6 fill-current" />
                        </button>
                      ))}
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                    <textarea
                      value={evaluationData.feedback}
                      onChange={(e) => setEvaluationData((prev) => ({ ...prev, feedback: e.target.value }))}
                      rows={4}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Provide detailed feedback..."
                    />
                  </div>

                  <div className="flex justify-end space-x-3">
                    <button
                      onClick={() => setSelectedSubmission(null)}
                      className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                      Cancel
                    </button>
                    <button
                      onClick={handleSubmitEvaluation}
                      className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                    >
                      Submit Evaluation
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    )
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

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-7xl mx-auto">
            <div className="flex items-center justify-between mb-6">
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Task Management</h1>
                <p className="text-gray-600 mt-1">Create, assign, and evaluate tasks for your teams</p>
              </div>

              <button
                onClick={() => setShowCreateModal(true)}
                className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                <Plus className="w-4 h-4 mr-2" />
                Create Task
              </button>
            </div>

            {/* Search and Filters */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
              <div className="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div className="flex-1 relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <input
                    type="text"
                    placeholder="Search tasks by title, description, group, or assignee..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div className="flex items-center space-x-4">
                  <div className="flex items-center space-x-2">
                    <Filter className="w-4 h-4 text-gray-400" />
                    <select
                      value={statusFilter}
                      onChange={(e) => setStatusFilter(e.target.value)}
                      className="border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="all">All Status</option>
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            {/* Tasks Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {filteredTasks.map((task) => (
                <TaskCard key={task.id} task={task} />
              ))}
            </div>

            {filteredTasks.length === 0 && (
              <div className="text-center py-12">
                <CheckSquare className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
                <p className="text-gray-500">
                  {searchTerm || statusFilter !== "all"
                    ? "Try adjusting your search or filters"
                    : "Create your first task to get started"}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Modals */}
      {showCreateModal && <CreateTaskModal />}
      {showEvaluateModal && <EvaluateTaskModal />}
    </div>
  )
}

export default TasksView
