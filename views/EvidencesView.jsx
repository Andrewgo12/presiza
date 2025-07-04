"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { evidencesAPI, filesAPI } from "../services/api"
import {
  Search,
  Filter,
  Eye,
  MessageCircle,
  Star,
  CheckCircle,
  XCircle,
  Clock,
  FileText,
  Calendar,
  User,
  Tag,
} from "lucide-react"

const EvidencesView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [evidences, setEvidences] = useState([])
  const [filteredEvidences, setFilteredEvidences] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [statusFilter, setStatusFilter] = useState("all")
  const [selectedEvidence, setSelectedEvidence] = useState(null)
  const [showDetailModal, setShowDetailModal] = useState(false)
  const [comments, setComments] = useState({})
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [pagination, setPagination] = useState({ page: 1, limit: 20, total: 0 })

  useEffect(() => {
    const loadEvidences = async () => {
      try {
        setLoading(true)
        setError(null)

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

        const response = await evidencesAPI.getEvidences(params)

        setEvidences(response.evidences || [])
        setFilteredEvidences(response.evidences || [])
        setPagination(response.pagination || { page: 1, limit: 20, total: 0 })

      } catch (err) {
        console.error('Error loading evidences:', err)
        setError('Error cargando evidencias')
        // Fallback to empty data
        setEvidences([])
        setFilteredEvidences([])
      } finally {
        setLoading(false)
      }
    }

    loadEvidences()
  }, [pagination.page, pagination.limit, statusFilter, searchTerm])

  // Function to approve evidence
  const handleApprove = async (evidenceId) => {
    try {
      await evidencesAPI.updateEvidence(evidenceId, { status: 'approved' })
      const updatedEvidences = evidences.map(e =>
        e._id === evidenceId ? { ...e, status: 'approved' } : e
      )
      setEvidences(updatedEvidences)
      setFilteredEvidences(updatedEvidences)
    } catch (err) {
      console.error('Error approving evidence:', err)
      setError('Error aprobando evidencia')
    }
  }

  // Function to reject evidence
  const handleReject = async (evidenceId, feedback) => {
    try {
      await evidencesAPI.updateEvidence(evidenceId, {
        status: 'rejected',
        feedback: feedback
      })
      const updatedEvidences = evidences.map(e =>
        e._id === evidenceId ? { ...e, status: 'rejected', feedback } : e
      )
      setEvidences(updatedEvidences)
      setFilteredEvidences(updatedEvidences)
    } catch (err) {
      console.error('Error rejecting evidence:', err)
      setError('Error rechazando evidencia')
    }
  }

  // Function to add comment
  const handleAddComment = async (evidenceId, comment) => {
    try {
      await evidencesAPI.addComment(evidenceId, comment)
      // Reload evidence details or update local state
    } catch (err) {
      console.error('Error adding comment:', err)
      setError('Error agregando comentario')
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
              <h3 className="text-lg font-medium text-gray-900 mb-2">Error cargando evidencias</h3>
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
      case "approved":
        return CheckCircle
      case "rejected":
        return XCircle
      case "under_review":
        return Clock
      default:
        return Clock
    }
  }

  const getStatusColor = (status) => {
    switch (status) {
      case "approved":
        return "bg-green-100 text-green-800"
      case "rejected":
        return "bg-red-100 text-red-800"
      case "under_review":
        return "bg-yellow-100 text-yellow-800"
      default:
        return "bg-gray-100 text-gray-800"
    }
  }

  const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
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

  const handleViewEvidence = (evidence) => {
    setSelectedEvidence(evidence)
    setShowDetailModal(true)
  }

  const handleUpdateStatus = (evidenceId, newStatus, rating = null, feedback = "") => {
    setEvidences((prev) =>
      prev.map((evidence) =>
        evidence.id === evidenceId
          ? {
            ...evidence,
            status: newStatus,
            rating,
            feedback,
            reviewedBy: user.name,
            reviewDate: new Date().toISOString(),
          }
          : evidence,
      ),
    )
    setShowDetailModal(false)
  }

  const EvidenceCard = ({ evidence }) => {
    const StatusIcon = getStatusIcon(evidence.status)

    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div className="flex items-start justify-between mb-4">
          <div className="flex-1">
            <h3 className="font-semibold text-gray-900 mb-2">{evidence.title}</h3>
            <p className="text-sm text-gray-600 line-clamp-2 mb-3">{evidence.description}</p>
          </div>
          <div
            className={`flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(evidence.status)}`}
          >
            <StatusIcon className="w-4 h-4 mr-1" />
            {evidence.status.replace("_", " ")}
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
          <div className="flex items-center text-gray-600">
            <User className="w-4 h-4 mr-2" />
            {evidence.author}
          </div>
          <div className="flex items-center text-gray-600">
            <Calendar className="w-4 h-4 mr-2" />
            {formatDate(evidence.submissionDate)}
          </div>
          <div className="flex items-center text-gray-600">
            <FileText className="w-4 h-4 mr-2" />
            {evidence.fileName} ({formatFileSize(evidence.fileSize)})
          </div>
          <div className="flex items-center text-gray-600">
            <Tag className="w-4 h-4 mr-2" />
            {evidence.project}
          </div>
        </div>

        {evidence.rating && (
          <div className="flex items-center mb-4">
            <span className="text-sm text-gray-600 mr-2">Rating:</span>
            <div className="flex items-center">
              {[1, 2, 3, 4, 5].map((star) => (
                <Star
                  key={star}
                  className={`w-4 h-4 ${star <= evidence.rating ? "text-yellow-400 fill-current" : "text-gray-300"}`}
                />
              ))}
              <span className="ml-2 text-sm text-gray-600">({evidence.rating}/5)</span>
            </div>
          </div>
        )}

        <div className="flex flex-wrap gap-2 mb-4">
          {evidence.tags.map((tag) => (
            <span key={tag} className="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
              {tag}
            </span>
          ))}
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4 text-sm text-gray-500">
            <span className="flex items-center">
              <MessageCircle className="w-4 h-4 mr-1" />
              {comments[evidence.id]?.length || 0} comments
            </span>
            <span>v{evidence.version}</span>
          </div>

          <button
            onClick={() => handleViewEvidence(evidence)}
            className="flex items-center px-3 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50"
          >
            <Eye className="w-4 h-4 mr-1" />
            View Details
          </button>
        </div>
      </div>
    )
  }

  const EvidenceDetailModal = () => {
    const [newComment, setNewComment] = useState("")
    const [reviewData, setReviewData] = useState({
      status: selectedEvidence?.status || "under_review",
      rating: selectedEvidence?.rating || 0,
      feedback: selectedEvidence?.feedback || "",
    })

    const handleAddComment = (e) => {
      e.preventDefault()
      if (!newComment.trim()) return

      const comment = {
        id: Date.now(),
        author: user.name,
        authorId: user.id,
        content: newComment,
        timestamp: new Date().toISOString(),
      }

      setComments((prev) => ({
        ...prev,
        [selectedEvidence.id]: [...(prev[selectedEvidence.id] || []), comment],
      }))

      setNewComment("")
    }

    const handleSubmitReview = () => {
      handleUpdateStatus(selectedEvidence.id, reviewData.status, reviewData.rating, reviewData.feedback)
    }

    if (!selectedEvidence) return null

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div className="p-6 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-semibold text-gray-900">{selectedEvidence.title}</h2>
              <button onClick={() => setShowDetailModal(false)} className="text-gray-400 hover:text-gray-600">
                <XCircle className="w-6 h-6" />
              </button>
            </div>
          </div>

          <div className="p-6 space-y-6">
            {/* Evidence Details */}
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-4">Evidence Details</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Author</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedEvidence.author}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Submission Date</label>
                  <p className="mt-1 text-sm text-gray-900">{formatDate(selectedEvidence.submissionDate)}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Project</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedEvidence.project}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">File</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {selectedEvidence.fileName} ({formatFileSize(selectedEvidence.fileSize)})
                  </p>
                </div>
              </div>
              <div className="mt-4">
                <label className="block text-sm font-medium text-gray-700">Description</label>
                <p className="mt-1 text-sm text-gray-900">{selectedEvidence.description}</p>
              </div>
            </div>

            {/* Admin Review Section */}
            {isAdmin && (
              <div className="border-t border-gray-200 pt-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4">Review Evidence</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                      value={reviewData.status}
                      onChange={(e) => setReviewData((prev) => ({ ...prev, status: e.target.value }))}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="under_review">Under Review</option>
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
                          onClick={() => setReviewData((prev) => ({ ...prev, rating: star }))}
                          className={`p-1 ${star <= reviewData.rating ? "text-yellow-400" : "text-gray-300 hover:text-yellow-400"
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
                      value={reviewData.feedback}
                      onChange={(e) => setReviewData((prev) => ({ ...prev, feedback: e.target.value }))}
                      rows={4}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Provide feedback for the author..."
                    />
                  </div>

                  <button
                    onClick={handleSubmitReview}
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                  >
                    Submit Review
                  </button>
                </div>
              </div>
            )}

            {/* Comments Section */}
            <div className="border-t border-gray-200 pt-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Comments</h3>

              <div className="space-y-4 mb-6">
                {(comments[selectedEvidence.id] || []).map((comment) => (
                  <div key={comment.id} className="bg-gray-50 rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="font-medium text-gray-900">{comment.author}</span>
                      <span className="text-sm text-gray-500">{formatDate(comment.timestamp)}</span>
                    </div>
                    <p className="text-gray-700">{comment.content}</p>
                  </div>
                ))}
              </div>

              <form onSubmit={handleAddComment} className="space-y-4">
                <textarea
                  value={newComment}
                  onChange={(e) => setNewComment(e.target.value)}
                  rows={3}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Add a comment..."
                />
                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                  Add Comment
                </button>
              </form>
            </div>
          </div>
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
            <div className="mb-6">
              <h1 className="text-2xl font-bold text-gray-900">{isAdmin ? "All Evidences" : "My Evidences"}</h1>
              <p className="text-gray-600 mt-1">
                {isAdmin ? "Review and manage all submitted evidences" : "View your submitted evidences and feedback"}
              </p>
            </div>

            {/* Search and Filters */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
              <div className="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div className="flex-1 relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <input
                    type="text"
                    placeholder="Search evidences by title, author, project, or tags..."
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
                      <option value="approved">Approved</option>
                      <option value="under_review">Under Review</option>
                      <option value="rejected">Rejected</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            {/* Evidences Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {filteredEvidences.map((evidence) => (
                <EvidenceCard key={evidence.id} evidence={evidence} />
              ))}
            </div>

            {filteredEvidences.length === 0 && (
              <div className="text-center py-12">
                <FileText className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No evidences found</h3>
                <p className="text-gray-500">
                  {searchTerm || statusFilter !== "all"
                    ? "Try adjusting your search or filters"
                    : isAdmin
                      ? "No evidences have been submitted yet"
                      : "You haven't submitted any evidences yet"}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Evidence Detail Modal */}
      {showDetailModal && <EvidenceDetailModal />}
    </div>
  )
}

export default EvidencesView
