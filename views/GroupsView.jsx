"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { groupsAPI, usersAPI } from "../services/api"
import { Search, Plus, Users, Lock, Globe, Key, Filter } from "lucide-react"

const GroupsView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [groups, setGroups] = useState([])
  const [filteredGroups, setFilteredGroups] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [filterType, setFilterType] = useState("all")
  const [showCreateModal, setShowCreateModal] = useState(false)
  const [showJoinModal, setShowJoinModal] = useState(false)
  const [selectedGroup, setSelectedGroup] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [pagination, setPagination] = useState({ page: 1, limit: 20, total: 0 })

  useEffect(() => {
    const loadGroups = async () => {
      try {
        setLoading(true)
        setError(null)

        const params = {
          page: pagination.page,
          limit: pagination.limit
        }

        if (filterType !== 'all') {
          params.type = filterType
        }

        if (searchTerm) {
          params.search = searchTerm
        }

        const response = await groupsAPI.getGroups(params)

        setGroups(response.groups || [])
        setFilteredGroups(response.groups || [])
        setPagination(response.pagination || { page: 1, limit: 20, total: 0 })

      } catch (err) {
        console.error('Error loading groups:', err)
        setError('Error cargando grupos')
        // Fallback a datos vacíos
        setGroups([])
        setFilteredGroups([])
      } finally {
        setLoading(false)
      }
    }

    loadGroups()
  }, [pagination.page, pagination.limit, filterType, searchTerm])

  // Función para crear grupo
  const handleCreateGroup = async (groupData) => {
    try {
      const newGroup = await groupsAPI.createGroup(groupData)
      setGroups(prev => [newGroup.group, ...prev])
      setFilteredGroups(prev => [newGroup.group, ...prev])
      setShowCreateModal(false)
    } catch (err) {
      console.error('Error creating group:', err)
      setError('Error creando grupo')
    }
  }

  // Función para unirse a grupo
  const handleJoinGroup = async (groupId) => {
    try {
      await groupsAPI.addMember(groupId, user._id, 'member')
      // Recargar grupos
      const updatedGroups = groups.map(g =>
        g._id === groupId
          ? { ...g, members: [...g.members, { user: user, role: 'member' }] }
          : g
      )
      setGroups(updatedGroups)
      setFilteredGroups(updatedGroups)
    } catch (err) {
      console.error('Error joining group:', err)
      setError('Error uniéndose al grupo')
    }
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
              <h3 className="text-lg font-medium text-gray-900 mb-2">Error cargando grupos</h3>
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
  if (searchTerm) {
    filtered = filtered.filter(
      (group) =>
        group.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        group.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
        group.category.toLowerCase().includes(searchTerm.toLowerCase()),
    )
  }

  // Filter by type
  if (filterType !== "all") {
    filtered = filtered.filter((group) => group.type === filterType)
  }

  // Componente GroupCard
  const GroupCard = ({ group }) => {
    const Icon = getGroupIcon(group.type)

    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div className="flex items-start justify-between mb-4">
          <div className="flex items-center space-x-3">
            <div className="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <Icon className="w-6 h-6 text-gray-600" />
            </div>
            <div>
              <h3 className="text-lg font-semibold text-gray-900">{group.name}</h3>
              <p className="text-sm text-gray-500">{group.category}</p>
            </div>
          </div>
          <span className={`px-2 py-1 text-xs font-medium rounded-full ${getGroupTypeColor(group.type)}`}>
            {group.type}
          </span>
        </div>

        <p className="text-gray-600 text-sm mb-4 line-clamp-2">{group.description}</p>

        <div className="flex items-center justify-between text-sm text-gray-500 mb-4">
          <div className="flex items-center space-x-4">
            <span className="flex items-center">
              <Users className="w-4 h-4 mr-1" />
              {group.members?.length || 0} members
            </span>
            <span>{group.location}</span>
          </div>
        </div>

        <div className="flex items-center justify-between">
          <span className="text-xs text-gray-400">
            Created by {group.createdBy}
          </span>

          {group.isJoined ? (
            <button
              onClick={() => handleLeaveGroup(group.id)}
              className="px-4 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-md hover:bg-red-50"
            >
              Leave
            </button>
          ) : (
            <button
              onClick={() => handleJoinGroup(group)}
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Join
            </button>
          )}
        </div>
      </div>
    )
  }

  const getGroupIcon = (type) => {
    switch (type) {
      case "public":
        return Globe
      case "private":
        return Lock
      case "protected":
        return Key
      default:
        return Users
    }
  }

  const getGroupTypeColor = (type) => {
    switch (type) {
      case "public":
        return "text-green-600 bg-green-100"
      case "private":
        return "text-red-600 bg-red-100"
      case "protected":
        return "text-yellow-600 bg-yellow-100"
      default:
        return "text-gray-600 bg-gray-100"
    }
  }



  const handleLeaveGroup = (groupId) => {
    if (window.confirm("Are you sure you want to leave this group?")) {
      setGroups((prev) => prev.map((g) => (g.id === groupId ? { ...g, isJoined: false, members: g.members - 1 } : g)))
    }
  }

  const CreateGroupModal = () => {
    const [formData, setFormData] = useState({
      name: "",
      description: "",
      type: "public",
      category: "",
      password: "",
    })

    const handleSubmit = (e) => {
      e.preventDefault()

      const newGroup = {
        id: Date.now(),
        ...formData,
        members: 1,
        location: "Global",
        image: "/placeholder.svg?height=60&width=60",
        isJoined: true,
        createdBy: user.name,
      }

      setGroups((prev) => [newGroup, ...prev])
      setShowCreateModal(false)
      setFormData({
        name: "",
        description: "",
        type: "public",
        category: "",
        password: "",
      })
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg p-6 w-full max-w-md">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Create New Group</h2>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Group Name *</label>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => setFormData((prev) => ({ ...prev, name: e.target.value }))}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Description *</label>
              <textarea
                value={formData.description}
                onChange={(e) => setFormData((prev) => ({ ...prev, description: e.target.value }))}
                required
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Category *</label>
              <select
                value={formData.category}
                onChange={(e) => setFormData((prev) => ({ ...prev, category: e.target.value }))}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select category</option>
                <option value="Research">Research</option>
                <option value="Development">Development</option>
                <option value="Analytics">Analytics</option>
                <option value="Design">Design</option>
                <option value="Marketing">Marketing</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Group Type *</label>
              <select
                value={formData.type}
                onChange={(e) => setFormData((prev) => ({ ...prev, type: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="public">Public - Anyone can join</option>
                <option value="private">Private - Requires approval</option>
                <option value="protected">Protected - Requires password</option>
              </select>
            </div>

            {formData.type === "protected" && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData((prev) => ({ ...prev, password: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            )}

            <div className="flex justify-end space-x-3 pt-4">
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
                Create Group
              </button>
            </div>
          </form>
        </div>
      </div>
    )
  }

  const JoinProtectedGroupModal = () => {
    const [password, setPassword] = useState("")

    const handleSubmit = (e) => {
      e.preventDefault()

      // Simulate password validation
      if (password === "demo123") {
        setGroups((prev) =>
          prev.map((g) => (g.id === selectedGroup.id ? { ...g, isJoined: true, members: g.members + 1 } : g)),
        )
        setShowJoinModal(false)
        setPassword("")
        setSelectedGroup(null)
      } else {
        alert("Incorrect password")
      }
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg p-6 w-full max-w-sm">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Join Protected Group</h2>
          <p className="text-sm text-gray-600 mb-4">Enter the password to join "{selectedGroup?.name}"</p>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter group password"
              />
            </div>

            <div className="flex justify-end space-x-3">
              <button
                type="button"
                onClick={() => {
                  setShowJoinModal(false)
                  setPassword("")
                  setSelectedGroup(null)
                }}
                className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
              >
                Join Group
              </button>
            </div>
          </form>
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
                <h1 className="text-2xl font-bold text-gray-900">Groups</h1>
                <p className="text-gray-600 mt-1">Discover and join collaborative workspaces</p>
              </div>

              {(isAdmin || user.role === "user") && (
                <button
                  onClick={() => setShowCreateModal(true)}
                  className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                  <Plus className="w-4 h-4 mr-2" />
                  Create Group
                </button>
              )}
            </div>

            {/* Search and Filters */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
              <div className="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div className="flex-1 relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <input
                    type="text"
                    placeholder="Search groups by name, description, or category..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div className="flex items-center space-x-4">
                  <div className="flex items-center space-x-2">
                    <Filter className="w-4 h-4 text-gray-400" />
                    <select
                      value={filterType}
                      onChange={(e) => setFilterType(e.target.value)}
                      className="border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="all">All Types</option>
                      <option value="public">Public</option>
                      <option value="private">Private</option>
                      <option value="protected">Protected</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            {/* Groups Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredGroups.map((group) => (
                <GroupCard key={group.id} group={group} />
              ))}
            </div>

            {filteredGroups.length === 0 && (
              <div className="text-center py-12">
                <Users className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No groups found</h3>
                <p className="text-gray-500">
                  {searchTerm || filterType !== "all"
                    ? "Try adjusting your search or filters"
                    : "Be the first to create a group!"}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Modals */}
      {showCreateModal && <CreateGroupModal />}
      {showJoinModal && <JoinProtectedGroupModal />}
    </div>
  )
}

export default GroupsView
