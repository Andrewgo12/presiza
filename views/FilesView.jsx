"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
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

  useEffect(() => {
    // Load mock files data
    const mockFiles = [
      {
        id: 1,
        name: "Research_Report_2024.pdf",
        type: "application/pdf",
        size: 2048576,
        author: "Dr. Smith",
        authorId: 1,
        uploadDate: "2024-01-15T10:30:00Z",
        group: "Research Team Alpha",
        groupId: 1,
        category: "documents",
        status: "approved",
        downloads: 45,
        likes: 12,
        comments: 3,
        tags: ["research", "analysis", "2024"],
        thumbnail: "/placeholder.svg?height=120&width=120",
      },
      {
        id: 2,
        name: "Project_Screenshots.zip",
        type: "application/zip",
        size: 15728640,
        author: "John Doe",
        authorId: 2,
        uploadDate: "2024-01-14T14:20:00Z",
        group: "Development Squad",
        groupId: 2,
        category: "archives",
        status: "pending",
        downloads: 8,
        likes: 5,
        comments: 1,
        tags: ["screenshots", "project", "ui"],
        thumbnail: "/placeholder.svg?height=120&width=120",
      },
      {
        id: 3,
        name: "Demo_Video.mp4",
        type: "video/mp4",
        size: 52428800,
        author: "Jane Wilson",
        authorId: 3,
        uploadDate: "2024-01-13T09:15:00Z",
        group: "Data Analytics Hub",
        groupId: 3,
        category: "videos",
        status: "approved",
        downloads: 23,
        likes: 18,
        comments: 7,
        tags: ["demo", "tutorial", "analytics"],
        thumbnail: "/placeholder.svg?height=120&width=120",
      },
      {
        id: 4,
        name: "UI_Mockups.png",
        type: "image/png",
        size: 1048576,
        author: "Mike Chen",
        authorId: 4,
        uploadDate: "2024-01-12T16:45:00Z",
        group: "Design Collective",
        groupId: 4,
        category: "images",
        status: "approved",
        downloads: 31,
        likes: 24,
        comments: 9,
        tags: ["ui", "mockup", "design"],
        thumbnail: "/placeholder.svg?height=120&width=120",
      },
    ]

    setFiles(mockFiles)
    setFilteredFiles(mockFiles)
  }, [])

  useEffect(() => {
    let filtered = files

    // Filter by category/tab
    if (activeTab !== "all") {
      filtered = filtered.filter((file) => file.category === activeTab)
    }

    // Filter by search term
    if (searchTerm) {
      filtered = filtered.filter(
        (file) =>
          file.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          file.author.toLowerCase().includes(searchTerm.toLowerCase()) ||
          file.group.toLowerCase().includes(searchTerm.toLowerCase()) ||
          file.tags.some((tag) => tag.toLowerCase().includes(searchTerm.toLowerCase())),
      )
    }

    // Sort files
    filtered.sort((a, b) => {
      switch (sortBy) {
        case "name":
          return a.name.localeCompare(b.name)
        case "size":
          return b.size - a.size
        case "downloads":
          return b.downloads - a.downloads
        case "likes":
          return b.likes - a.likes
        case "date":
        default:
          return new Date(b.uploadDate) - new Date(a.uploadDate)
      }
    })

    setFilteredFiles(filtered)
  }, [searchTerm, activeTab, sortBy, files])

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

  const handleDownload = (file) => {
    // Simulate file download
    console.log(`Downloading ${file.name}`)
    setFiles((prev) => prev.map((f) => (f.id === file.id ? { ...f, downloads: f.downloads + 1 } : f)))
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
                      className={`flex items-center py-2 px-1 border-b-2 font-medium text-sm ${
                        activeTab === tab.id
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
