"use client"

import { useState, useCallback } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { filesAPI, evidencesAPI } from "../services/api"
import { Upload, X, File, ImageIcon, Video, FileText, Archive } from "lucide-react"

const UploadView = () => {
  const { user } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [files, setFiles] = useState([])
  const [formData, setFormData] = useState({
    title: "",
    description: "",
    project: "",
    evidenceType: "",
    tags: "",
  })
  const [dragActive, setDragActive] = useState(false)
  const [uploading, setUploading] = useState(false)

  // Supported file types (100+ types)
  const supportedTypes = {
    documents: [".pdf", ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".txt", ".rtf", ".odt"],
    images: [".jpg", ".jpeg", ".png", ".gif", ".bmp", ".svg", ".webp", ".tiff"],
    videos: [".mp4", ".avi", ".mov", ".wmv", ".flv", ".webm", ".mkv"],
    audio: [".mp3", ".wav", ".flac", ".aac", ".ogg"],
    archives: [".zip", ".rar", ".7z", ".tar", ".gz"],
    code: [".js", ".jsx", ".ts", ".tsx", ".html", ".css", ".json", ".xml"],
    other: [".csv", ".log", ".md", ".yaml", ".yml"],
  }

  const maxFileSize = 2 * 1024 * 1024 * 1024 // 2GB in bytes

  const getFileIcon = (fileName) => {
    const extension = fileName.toLowerCase().substring(fileName.lastIndexOf("."))

    if (supportedTypes.images.includes(extension)) return ImageIcon
    if (supportedTypes.videos.includes(extension)) return Video
    if (supportedTypes.documents.includes(extension)) return FileText
    if (supportedTypes.archives.includes(extension)) return Archive
    return File
  }

  const validateFile = (file) => {
    const errors = []

    // Check file size
    if (file.size > maxFileSize) {
      errors.push(`File size exceeds 2GB limit`)
    }

    // Check file type
    const extension = file.name.toLowerCase().substring(file.name.lastIndexOf("."))
    const allSupportedTypes = Object.values(supportedTypes).flat()

    if (!allSupportedTypes.includes(extension)) {
      errors.push(`File type ${extension} is not supported`)
    }

    return errors
  }

  const handleDrag = useCallback((e) => {
    e.preventDefault()
    e.stopPropagation()
    if (e.type === "dragenter" || e.type === "dragover") {
      setDragActive(true)
    } else if (e.type === "dragleave") {
      setDragActive(false)
    }
  }, [])

  const handleDrop = useCallback((e) => {
    e.preventDefault()
    e.stopPropagation()
    setDragActive(false)

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFiles(Array.from(e.dataTransfer.files))
    }
  }, [])

  const handleFiles = (fileList) => {
    const newFiles = []

    Array.from(fileList).forEach((file) => {
      const errors = validateFile(file)

      const fileObj = {
        id: Date.now() + Math.random(),
        file,
        name: file.name,
        size: file.size,
        type: file.type,
        errors,
        preview: null,
      }

      // Generate preview for images
      if (file.type.startsWith("image/")) {
        const reader = new FileReader()
        reader.onload = (e) => {
          setFiles((prev) => prev.map((f) => (f.id === fileObj.id ? { ...f, preview: e.target.result } : f)))
        }
        reader.readAsDataURL(file)
      }

      newFiles.push(fileObj)
    })

    setFiles((prev) => [...prev, ...newFiles])
  }

  const removeFile = (fileId) => {
    setFiles((prev) => prev.filter((f) => f.id !== fileId))
  }

  const handleInputChange = (e) => {
    const { name, value } = e.target
    setFormData((prev) => ({ ...prev, [name]: value }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (files.length === 0) {
      alert("Por favor selecciona al menos un archivo para subir")
      return
    }

    const hasErrors = files.some((f) => f.errors.length > 0)
    if (hasErrors) {
      alert("Por favor corrige los errores de archivos antes de subir")
      return
    }

    setUploading(true)

    try {
      // Upload each file
      const uploadedFiles = []

      for (let i = 0; i < files.length; i++) {
        const file = files[i]

        // Create FormData for file upload
        const fileFormData = new FormData()
        fileFormData.append('file', file.file)
        fileFormData.append('title', formData.title)
        fileFormData.append('description', formData.description)
        fileFormData.append('project', formData.project)
        fileFormData.append('evidenceType', formData.evidenceType)
        fileFormData.append('tags', formData.tags)
        fileFormData.append('author', user.name)
        fileFormData.append('authorId', user.id)

        // Upload file via API
        const response = await filesAPI.uploadFile(fileFormData)
        uploadedFiles.push(response)

        console.log(`Archivo subido: ${file.name}`)
      }

      // Create evidence entry if needed
      if (formData.evidenceType) {
        const evidenceData = {
          title: formData.title,
          description: formData.description,
          project: formData.project,
          evidenceType: formData.evidenceType,
          tags: formData.tags.split(',').map(tag => tag.trim()),
          files: uploadedFiles.map(f => f._id),
          author: user.name,
          authorId: user.id,
          uploadDate: new Date().toISOString(),
          status: "pending",
        }

        await evidencesAPI.createEvidence(evidenceData)
        console.log("Evidence created successfully")
      }

      console.log("Upload completed successfully")

      // Reset form
      setFiles([])
      setFormData({
        title: "",
        description: "",
        project: "",
        evidenceType: "",
        tags: "",
      })

      alert("¡Archivos subidos exitosamente!")

    } catch (error) {
      console.error("Upload failed:", error)
      alert(`Error en la subida: ${error.message || 'Por favor intenta de nuevo.'}`)
    } finally {
      setUploading(false)
    }
  }

  const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
  }

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-4xl mx-auto">
            <div className="mb-6">
              <h1 className="text-2xl font-bold text-gray-900">Upload Evidence</h1>
              <p className="text-gray-600 mt-2">Upload files and evidence for your projects</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              {/* File Upload Area */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Select Files</h2>

                <div
                  className={`border-2 border-dashed rounded-lg p-8 text-center transition-colors ${dragActive ? "border-blue-500 bg-blue-50" : "border-gray-300 hover:border-gray-400"
                    }`}
                  onDragEnter={handleDrag}
                  onDragLeave={handleDrag}
                  onDragOver={handleDrag}
                  onDrop={handleDrop}
                >
                  <Upload className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                  <p className="text-lg font-medium text-gray-900 mb-2">Drop files here or click to browse</p>
                  <p className="text-sm text-gray-500 mb-4">Supports 100+ file types, up to 2GB per file</p>
                  <input
                    type="file"
                    multiple
                    onChange={(e) => handleFiles(e.target.files)}
                    className="hidden"
                    id="file-upload"
                  />
                  <label
                    htmlFor="file-upload"
                    className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 cursor-pointer"
                  >
                    Choose Files
                  </label>
                </div>

                {/* File List */}
                {files.length > 0 && (
                  <div className="mt-6">
                    <h3 className="text-sm font-medium text-gray-900 mb-3">Selected Files</h3>
                    <div className="space-y-3">
                      {files.map((file) => {
                        const FileIcon = getFileIcon(file.name)
                        return (
                          <div key={file.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div className="flex items-center space-x-3">
                              {file.preview ? (
                                <img
                                  src={file.preview || "/placeholder.svg"}
                                  alt=""
                                  className="w-10 h-10 object-cover rounded"
                                />
                              ) : (
                                <FileIcon className="w-10 h-10 text-gray-400" />
                              )}
                              <div>
                                <p className="text-sm font-medium text-gray-900">{file.name}</p>
                                <p className="text-xs text-gray-500">{formatFileSize(file.size)}</p>
                                {file.errors.length > 0 && (
                                  <p className="text-xs text-red-600">{file.errors.join(", ")}</p>
                                )}
                              </div>
                            </div>
                            <button
                              type="button"
                              onClick={() => removeFile(file.id)}
                              className="p-1 text-gray-400 hover:text-red-600"
                            >
                              <X className="w-4 h-4" />
                            </button>
                          </div>
                        )
                      })}
                    </div>
                  </div>
                )}
              </div>

              {/* Metadata Form */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">File Information</h2>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
                      Title *
                    </label>
                    <input
                      type="text"
                      id="title"
                      name="title"
                      value={formData.title}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Enter file title"
                    />
                  </div>

                  <div>
                    <label htmlFor="project" className="block text-sm font-medium text-gray-700 mb-2">
                      Project *
                    </label>
                    <select
                      id="project"
                      name="project"
                      value={formData.project}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="">Select project</option>
                      <option value="research">Research Project</option>
                      <option value="development">Development Project</option>
                      <option value="analysis">Data Analysis</option>
                      <option value="documentation">Documentation</option>
                    </select>
                  </div>

                  <div>
                    <label htmlFor="evidenceType" className="block text-sm font-medium text-gray-700 mb-2">
                      Evidence Type *
                    </label>
                    <select
                      id="evidenceType"
                      name="evidenceType"
                      value={formData.evidenceType}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="">Select type</option>
                      <option value="document">Document</option>
                      <option value="image">Image</option>
                      <option value="video">Video</option>
                      <option value="data">Data File</option>
                      <option value="code">Source Code</option>
                    </select>
                  </div>

                  <div>
                    <label htmlFor="tags" className="block text-sm font-medium text-gray-700 mb-2">
                      Tags
                    </label>
                    <input
                      type="text"
                      id="tags"
                      name="tags"
                      value={formData.tags}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Enter tags separated by commas"
                    />
                  </div>
                </div>

                <div className="mt-6">
                  <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                    Description *
                  </label>
                  <textarea
                    id="description"
                    name="description"
                    value={formData.description}
                    onChange={handleInputChange}
                    required
                    rows={4}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Describe the files and their purpose"
                  />
                </div>
              </div>

              {/* Submit Button */}
              <div className="flex justify-end">
                <button
                  type="submit"
                  disabled={uploading || files.length === 0}
                  className={`px-6 py-3 rounded-md font-medium transition-colors ${uploading || files.length === 0
                    ? "bg-gray-300 text-gray-500 cursor-not-allowed"
                    : "bg-blue-600 hover:bg-blue-700 text-white"
                    }`}
                >
                  {uploading ? "Uploading..." : "Upload Files"}
                </button>
              </div>
            </form>
          </div>
        </main>
      </div>
    </div>
  )
}

export default UploadView
