"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { filesAPI } from "../services/api"
import {
  Search,
  Download,
  Eye,
  MessageCircle,
  Heart,
  FileText,
  ImageIcon,
  Video,
  Archive,
  File,
  Grid,
  List,
} from "lucide-react"

const FilesView = () => {
  const { user } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [files, setFiles] = useState([])
  const [filteredFiles, setFilteredFiles] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [activeTab, setActiveTab] = useState("all")
  const [viewMode, setViewMode] = useState("grid")
  const [sortBy, setSortBy] = useState("date")
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [pagination, setPagination] = useState({ page: 1, limit: 20, total: 0 })

  useEffect(() => {
    const loadFiles = async () => {
      try {
        setLoading(true)
        setError(null)

        const params = {
          page: pagination.page,
          limit: pagination.limit,
          sortBy: sortBy === 'date' ? 'createdAt' : sortBy,
          sortOrder: 'desc'
        }

        if (activeTab !== 'all') {
          params.category = activeTab
        }

        if (searchTerm) {
          params.search = searchTerm
        }

        const response = await filesAPI.getFiles(params)

        setFiles(response.files || [])
        setFilteredFiles(response.files || [])
        setPagination(response.pagination || { page: 1, limit: 20, total: 0 })

      } catch (err) {
        console.error('Error loading files:', err)
        setError('Error cargando archivos')
        setFiles([])
        setFilteredFiles([])
      } finally {
        setLoading(false)
      }
    }

    loadFiles()
  }, [pagination.page, pagination.limit, sortBy, activeTab, searchTerm])

  // Función para manejar descarga de archivos
  const handleDownload = async (file) => {
    try {
      const downloadUrl = filesAPI.getDownloadUrl(file.filename)
      window.open(downloadUrl, '_blank')
    } catch (err) {
      console.error('Error downloading file:', err)
      setError('Error descargando archivo')
    }
  }

  // Función para eliminar archivo
  const handleDelete = async (fileId) => {
    if (!window.confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
      return
    }

    try {
      await filesAPI.deleteFile(fileId)
      // Recargar la lista de archivos
      const updatedFiles = files.filter(f => f._id !== fileId)
      setFiles(updatedFiles)
      setFilteredFiles(updatedFiles)
    } catch (err) {
      console.error('Error deleting file:', err)
      setError('Error eliminando archivo')
    }
  }

  // Función para actualizar archivo
  const handleUpdate = async (fileId, updateData) => {
    try {
      const updatedFile = await filesAPI.updateFile(fileId, updateData)
      const updatedFiles = files.map(f => f._id === fileId ? updatedFile.file : f)
      setFiles(updatedFiles)
      setFilteredFiles(updatedFiles)
    } catch (err) {
      console.error('Error updating file:', err)
      setError('Error actualizando archivo')
    }
  }

  // Función para cambiar página
  const handlePageChange = (newPage) => {
    setPagination(prev => ({ ...prev, page: newPage }))
  }

  // Función para cambiar filtros
  const handleFilterChange = (newTab) => {
    setActiveTab(newTab)
    setPagination(prev => ({ ...prev, page: 1 })) // Reset a primera página
  }

  // Función para cambiar búsqueda
  const handleSearchChange = (newSearchTerm) => {
    setSearchTerm(newSearchTerm)
    setPagination(prev => ({ ...prev, page: 1 })) // Reset a primera página
  }

  // Si hay error, mostrar mensaje
  if (error) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Header onMenuClick={() => setSidebarOpen(!sidebarOpen)} />
        <div className="flex">
          <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
          <main className="flex-1 p-6">
            <div className="text-center py-12">
              <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">Error cargando archivos</h3>
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
    })
  }

  const getFileIcon = (type, category) => {
    switch (category) {
      case "documents":
        return FileText
      case "images":
        return ImageIcon
      case "videos":
        return Video
      case "archives":
        return Archive
      default:
        return File
    }
  }

  const getStatusColor = (status) => {
    switch (status) {
      case "approved":
        return "text-green-600 bg-green-100"
      case "pending":
        return "text-yellow-600 bg-yellow-100"
      case "rejected":
        return "text-red-600 bg-red-100"
      default:
        return "text-gray-600 bg-gray-100"
    }
  }



  const handleLike = (fileId) => {
    setFiles((prev) => prev.map((f) => (f.id === fileId ? { ...f, likes: f.likes + 1 } : f)))
  }

  const handleView = (file) => {
    // Open file viewer modal or navigate to file detail page
    console.log(`Viewing ${file.name}`)
  }

  const tabs = [
    { id: "all", label: "All Files", icon: File },
    { id: "documents", label: "Documents", icon: FileText },
    { id: "images", label: "Images", icon: ImageIcon },
    { id: "videos", label: "Videos", icon: Video },
    { id: "archives", label: "Archives", icon: Archive },
  ]

  const FileCard = ({ file }) => {
    const FileIcon = getFileIcon(file.type, file.category)

    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div className="flex items-start justify-between mb-3">
          <div className="flex items-center space-x-3">
            {file.category === "images" ? (
              <img
                src={file.thumbnail || "/placeholder.svg"}
                alt={file.name}
                className="w-12 h-12 rounded-lg object-cover"
              />
            ) : (
              <div className="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                <FileIcon className="w-6 h-6 text-gray-600" />
              </div>
            )}
            <div className="flex-1 min-w-0">
              <h3 className="font-medium text-gray-900 truncate">{file.name}</h3>
              <p className="text-sm text-gray-500">{formatFileSize(file.size)}</p>
            </div>
          </div>
          <span className={`px-2 py-1 text-xs rounded-full font-medium ${getStatusColor(file.status)}`}>
            {file.status}
          </span>
        </div>

        <div className="mb-3">
          <p className="text-sm text-gray-600 mb-1">
            By <span className="font-medium">{file.author}</span> in <span className="font-medium">{file.group}</span>
          </p>
          <p className="text-xs text-gray-500">{formatDate(file.uploadDate)}</p>
        </div>

        <div className="flex flex-wrap gap-1 mb-3">
          {file.tags.slice(0, 3).map((tag) => (
            <span key={tag} className="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
              {tag}
            </span>
          ))}
          {file.tags.length > 3 && (
            <span className="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">+{file.tags.length - 3}</span>
          )}
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4 text-sm text-gray-500">
            <span className="flex items-center">
              <Download className="w-4 h-4 mr-1" />
              {file.downloads}
            </span>
            <span className="flex items-center">
              <Heart className="w-4 h-4 mr-1" />
              {file.likes}
            </span>
            <span className="flex items-center">
              <MessageCircle className="w-4 h-4 mr-1" />
              {file.comments}
            </span>
          </div>

          <div className="flex items-center space-x-2">
            <button
              onClick={() => handleView(file)}
              className="p-2 text-gray-400 hover:text-blue-600 rounded-full hover:bg-blue-50"
              title="View"
            >
              <Eye className="w-4 h-4" />
            </button>
            <button
              onClick={() => handleDownload(file)}
              className="p-2 text-gray-400 hover:text-green-600 rounded-full hover:bg-green-50"
              title="Download"
            >
              <Download className="w-4 h-4" />
            </button>
            <button
              onClick={() => handleLike(file.id)}
              className="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-red-50"
              title="Like"
            >
              <Heart className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    )
  }

  const FileRow = ({ file }) => {
    const FileIcon = getFileIcon(file.type, file.category)

    return (
      <tr className="hover:bg-gray-50">
        <td className="px-6 py-4 whitespace-nowrap">
          <div className="flex items-center space-x-3">
            {file.category === "images" ? (
              <img
                src={file.thumbnail || "/placeholder.svg"}
                alt={file.name}
                className="w-8 h-8 rounded object-cover"
              />
            ) : (
              <div className="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                <FileIcon className="w-4 h-4 text-gray-600" />
              </div>
            )}
            <div>
              <div className="text-sm font-medium text-gray-900">{file.name}</div>
              <div className="text-sm text-gray-500">{formatFileSize(file.size)}</div>
            </div>
          </div>
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{file.author}</td>
        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{file.group}</td>
        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{formatDate(file.uploadDate)}</td>
        <td className="px-6 py-4 whitespace-nowrap">
          <span className={`px-2 py-1 text-xs rounded-full font-medium ${getStatusColor(file.status)}`}>
            {file.status}
          </span>
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          <div className="flex items-center space-x-4">
            <span>{file.downloads}</span>
            <span>{file.likes}</span>
            <span>{file.comments}</span>
          </div>
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <div className="flex items-center space-x-2">
            <button onClick={() => handleView(file)} className="text-blue-600 hover:text-blue-900">
              <Eye className="w-4 h-4" />
            </button>
            <button onClick={() => handleDownload(file)} className="text-green-600 hover:text-green-900">
              <Download className="w-4 h-4" />
            </button>
            <button onClick={() => handleLike(file.id)} className="text-red-600 hover:text-red-900">
              <Heart className="w-4 h-4" />
            </button>
          </div>
        </td>
      </tr>
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
              <h1 className="text-2xl font-bold text-gray-900">Files</h1>
              <p className="text-gray-600 mt-1">Browse and manage all files in the system</p>
            </div>

            {/* Tabs */}
            <div className="border-b border-gray-200 mb-6">
              <nav className="-mb-px flex space-x-8">
                {tabs.map((tab) => {
                  const TabIcon = tab.icon
                  return (
                    <button
                      key={tab.id}
                      onClick={() => setActiveTab(tab.id)}
                      className={`flex items-center py-2 px-1 border-b-2 font-medium text-sm ${activeTab === tab.id
                        ? "border-blue-500 text-blue-600"
                        : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                        }`}
                    >
                      <TabIcon className="w-4 h-4 mr-2" />
                      {tab.label}
                    </button>
                  )
                })}
              </nav>
            </div>

            {/* Search and Controls */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
              <div className="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div className="flex-1 relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <input
                    type="text"
                    placeholder="Search files by name, author, group, or tags..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div className="flex items-center space-x-4">
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value)}
                    className="border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="date">Sort by Date</option>
                    <option value="name">Sort by Name</option>
                    <option value="size">Sort by Size</option>
                    <option value="downloads">Sort by Downloads</option>
                    <option value="likes">Sort by Likes</option>
                  </select>

                  <div className="flex items-center border border-gray-300 rounded-md">
                    <button
                      onClick={() => setViewMode("grid")}
                      className={`p-2 ${viewMode === "grid" ? "bg-blue-100 text-blue-600" : "text-gray-400"}`}
                    >
                      <Grid className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => setViewMode("list")}
                      className={`p-2 ${viewMode === "list" ? "bg-blue-100 text-blue-600" : "text-gray-400"}`}
                    >
                      <List className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {/* Files Display */}
            {viewMode === "grid" ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {filteredFiles.map((file) => (
                  <FileCard key={file.id} file={file} />
                ))}
              </div>
            ) : (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        File
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Author
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Group
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Stats
                      </th>
                      <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {filteredFiles.map((file) => (
                      <FileRow key={file.id} file={file} />
                    ))}
                  </tbody>
                </table>
              </div>
            )}

            {filteredFiles.length === 0 && (
              <div className="text-center py-12">
                <File className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No files found</h3>
                <p className="text-gray-500">
                  {searchTerm || activeTab !== "all"
                    ? "Try adjusting your search or filters"
                    : "No files have been uploaded yet"}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  )
}

export default FilesView
