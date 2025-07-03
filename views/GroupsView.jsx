"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
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

  useEffect(() => {
    // Load mock groups data
    const mockGroups = [
      {
        id: 1,
        name: "Research Team Alpha",
        description: "Advanced research in AI and machine learning",
        type: "public",
        category: "Research",
        members: 24,
        location: "Global",
        image: "/placeholder.svg?height=60&width=60",
        isJoined: true,
        createdBy: "Dr. Smith",
      },
      {
        id: 2,
        name: "Development Squad",
        description: "Full-stack development team working on enterprise solutions",
        type: "private",
        category: "Development",
        members: 12,
        location: "USA",
        image: "/placeholder.svg?height=60&width=60",
        isJoined: false,
        createdBy: "John Doe",
      },
      {
        id: 3,
        name: "Data Analytics Hub",
        description: "Data science and analytics collaborative workspace",
        type: "protected",
        category: "Analytics",
        members: 18,
        location: "Europe",
        image: "/placeholder.svg?height=60&width=60",
        isJoined: true,
        createdBy: "Jane Wilson",
      },
      {
        id: 4,
        name: "Design Collective",
        description: "UI/UX designers sharing resources and feedback",
        type: "public",
        category: "Design",
        members: 31,
        location: "Global",
        image: "/placeholder.svg?height=60&width=60",
        isJoined: false,
        createdBy: "Mike Chen",
      },
    ]

    setGroups(mockGroups)
    setFilteredGroups(mockGroups)
  }, [])

  useEffect(() => {
    let filtered = groups

    // Filter by search term
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

    setFilteredGroups(filtered)
  }, [searchTerm, filterType, groups])

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

  const handleJoinGroup = (group) => {
    if (group.type === "protected") {
      setSelectedGroup(group)
      setShowJoinModal(true)
    } else if (group.type === "private") {
      // Send join request
      alert("Join request sent to group administrator")
    } else {
      // Join immediately for public groups
      setGroups((prev) => prev.map((g) => (g.id === group.id ? { ...g, isJoined: true, members: g.members + 1 } : g)))
    }
  }

  const handleLeaveGroup = (groupId) => {
    if (window.confirm("Are you sure you want to leave this group?")) {
      setGroups((prev) => prev.map((g) => (g.id === groupId ? { ...g, isJoined: false, members: g.members - 1 } : g)))
    }
  }

  const GroupCard = ({ group }) => {
    const GroupIcon = getGroupIcon(group.type)

    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div className="flex items-start justify-between mb-4">
          <div className="flex items-center space-x-3">
            <img
              src={group.image || "/placeholder.svg"}
              alt={group.name}
              className="w-12 h-12 rounded-lg object-cover"
            />
            <div>
              <h3 className="font-semibold text-gray-900">{group.name}</h3>
              <p className="text-sm text-gray-500">
                {group.category} • {group.location}
              </p>
            </div>
          </div>
          <div
            className={`flex items-center px-2 py-1 rounded-full text-xs font-medium ${getGroupTypeColor(group.type)}`}
          >
            <GroupIcon className="w-3 h-3 mr-1" />
            {group.type}
          </div>
        </div>

        <p className="text-sm text-gray-600 mb-4 line-clamp-2">{group.description}</p>

        <div className="flex items-center justify-between">
          <div className="flex items-center text-sm text-gray-500">
            <Users className="w-4 h-4 mr-1" />
            {group.members} members
          </div>

          {group.isJoined ? (
            <button
              onClick={() => handleLeaveGroup(group.id)}
              className="px-4 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-md hover:bg-red-50"
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
