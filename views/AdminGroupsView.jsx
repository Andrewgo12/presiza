"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import {
  Search,
  Plus,
  Users,
  Eye,
  Edit,
  Trash2,
  UserPlus,
  UserMinus,
  Globe,
  Lock,
  Key,
  AlertCircle,
} from "lucide-react"

const AdminGroupsView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [groups, setGroups] = useState([])
  const [filteredGroups, setFilteredGroups] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [selectedGroup, setSelectedGroup] = useState(null)
  const [showDetailModal, setShowDetailModal] = useState(false)
  const [showCreateModal, setShowCreateModal] = useState(false)

  useEffect(() => {
    // Load mock groups with detailed admin data
    const mockGroups = [
      {
        id: 1,
        name: "Research Team Alpha",
        description: "Advanced research in AI and machine learning technologies",
        type: "public",
        category: "Research",
        createdBy: "Dr. Smith",
        createdDate: "2023-12-01T10:00:00Z",
        members: [
          {
            id: 1,
            name: "Dr. Smith",
            role: "admin",
            joinDate: "2023-12-01T10:00:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 2,
            name: "John Doe",
            role: "member",
            joinDate: "2023-12-05T14:30:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 3,
            name: "Jane Wilson",
            role: "member",
            joinDate: "2023-12-10T09:15:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 4,
            name: "Mike Chen",
            role: "moderator",
            joinDate: "2023-12-15T16:45:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
        ],
        files: 156,
        tasks: 12,
        activity: 89,
        settings: {
          maxMembers: 50,
          allowFileUpload: true,
          requireApproval: false,
          password: null,
        },
        stats: {
          totalUploads: 156,
          approvedFiles: 142,
          pendingFiles: 8,
          rejectedFiles: 6,
          avgResponseTime: 2.4,
        },
      },
      {
        id: 2,
        name: "Development Squad",
        description: "Full-stack development team working on enterprise solutions",
        type: "private",
        category: "Development",
        createdBy: "John Doe",
        createdDate: "2023-11-15T08:30:00Z",
        members: [
          {
            id: 2,
            name: "John Doe",
            role: "admin",
            joinDate: "2023-11-15T08:30:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 5,
            name: "Sarah Johnson",
            role: "member",
            joinDate: "2023-11-20T11:20:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 6,
            name: "Alex Rodriguez",
            role: "member",
            joinDate: "2023-11-25T15:10:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
        ],
        files: 134,
        tasks: 8,
        activity: 76,
        settings: {
          maxMembers: 25,
          allowFileUpload: true,
          requireApproval: true,
          password: null,
        },
        stats: {
          totalUploads: 134,
          approvedFiles: 120,
          pendingFiles: 10,
          rejectedFiles: 4,
          avgResponseTime: 1.8,
        },
      },
      {
        id: 3,
        name: "Data Analytics Hub",
        description: "Data science and analytics collaborative workspace",
        type: "protected",
        category: "Analytics",
        createdBy: "Jane Wilson",
        createdDate: "2023-10-20T12:45:00Z",
        members: [
          {
            id: 3,
            name: "Jane Wilson",
            role: "admin",
            joinDate: "2023-10-20T12:45:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 7,
            name: "David Kim",
            role: "member",
            joinDate: "2023-10-25T09:30:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
          {
            id: 8,
            name: "Lisa Zhang",
            role: "moderator",
            joinDate: "2023-11-01T14:15:00Z",
            avatar: "/placeholder.svg?height=32&width=32",
          },
        ],
        files: 98,
        tasks: 6,
        activity: 65,
        settings: {
          maxMembers: 30,
          allowFileUpload: true,
          requireApproval: false,
          password: "analytics2024",
        },
        stats: {
          totalUploads: 98,
          approvedFiles: 89,
          pendingFiles: 5,
          rejectedFiles: 4,
          avgResponseTime: 3.1,
        },
      },
    ]

    setGroups(mockGroups)
    setFilteredGroups(mockGroups)
  }, [])

  useEffect(() => {
    let filtered = groups

    if (searchTerm) {
      filtered = filtered.filter(
        (group) =>
          group.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          group.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
          group.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
          group.createdBy.toLowerCase().includes(searchTerm.toLowerCase()),
      )
    }

    setFilteredGroups(filtered)
  }, [searchTerm, groups])

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

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    })
  }

  const handleDeleteGroup = (groupId) => {
    if (window.confirm("Are you sure you want to delete this group? This action cannot be undone.")) {
      setGroups((prev) => prev.filter((g) => g.id !== groupId))
    }
  }

  const handleRemoveMember = (groupId, memberId) => {
    if (window.confirm("Are you sure you want to remove this member from the group?")) {
      setGroups((prev) =>
        prev.map((group) =>
          group.id === groupId
            ? {
                ...group,
                members: group.members.filter((m) => m.id !== memberId),
              }
            : group,
        ),
      )
    }
  }

  const GroupCard = ({ group }) => {
    const GroupIcon = getGroupIcon(group.type)

    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div className="flex items-start justify-between mb-4">
          <div className="flex items-center space-x-3">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Users className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h3 className="font-semibold text-gray-900">{group.name}</h3>
              <p className="text-sm text-gray-500">
                {group.category} • Created by {group.createdBy}
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

        <div className="grid grid-cols-3 gap-4 mb-4 text-sm">
          <div className="text-center">
            <p className="font-semibold text-gray-900">{group.members.length}</p>
            <p className="text-gray-500">Members</p>
          </div>
          <div className="text-center">
            <p className="font-semibold text-gray-900">{group.files}</p>
            <p className="text-gray-500">Files</p>
          </div>
          <div className="text-center">
            <p className="font-semibold text-gray-900">{group.tasks}</p>
            <p className="text-gray-500">Tasks</p>
          </div>
        </div>

        <div className="flex items-center justify-between">
          <div className="text-sm text-gray-500">Created {formatDate(group.createdDate)}</div>

          <div className="flex items-center space-x-2">
            <button
              onClick={() => {
                setSelectedGroup(group)
                setShowDetailModal(true)
              }}
              className="p-2 text-gray-400 hover:text-blue-600 rounded-full hover:bg-blue-50"
              title="View Details"
            >
              <Eye className="w-4 h-4" />
            </button>
            <button
              className="p-2 text-gray-400 hover:text-green-600 rounded-full hover:bg-green-50"
              title="Edit Group"
            >
              <Edit className="w-4 h-4" />
            </button>
            <button
              onClick={() => handleDeleteGroup(group.id)}
              className="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-red-50"
              title="Delete Group"
            >
              <Trash2 className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    )
  }

  const GroupDetailModal = () => {
    if (!selectedGroup) return null

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div className="p-6 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-semibold text-gray-900">{selectedGroup.name}</h2>
              <button onClick={() => setShowDetailModal(false)} className="text-gray-400 hover:text-gray-600">
                ×
              </button>
            </div>
          </div>

          <div className="p-6 space-y-6">
            {/* Group Info */}
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-4">Group Information</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Description</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedGroup.description}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Category</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedGroup.category}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Created By</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedGroup.createdBy}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Created Date</label>
                  <p className="mt-1 text-sm text-gray-900">{formatDate(selectedGroup.createdDate)}</p>
                </div>
              </div>
            </div>

            {/* Statistics */}
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="bg-blue-50 rounded-lg p-4 text-center">
                  <p className="text-2xl font-bold text-blue-600">{selectedGroup.stats.totalUploads}</p>
                  <p className="text-sm text-blue-800">Total Uploads</p>
                </div>
                <div className="bg-green-50 rounded-lg p-4 text-center">
                  <p className="text-2xl font-bold text-green-600">{selectedGroup.stats.approvedFiles}</p>
                  <p className="text-sm text-green-800">Approved</p>
                </div>
                <div className="bg-yellow-50 rounded-lg p-4 text-center">
                  <p className="text-2xl font-bold text-yellow-600">{selectedGroup.stats.pendingFiles}</p>
                  <p className="text-sm text-yellow-800">Pending</p>
                </div>
                <div className="bg-red-50 rounded-lg p-4 text-center">
                  <p className="text-2xl font-bold text-red-600">{selectedGroup.stats.rejectedFiles}</p>
                  <p className="text-sm text-red-800">Rejected</p>
                </div>
              </div>
            </div>

            {/* Members */}
            <div>
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900">Members ({selectedGroup.members.length})</h3>
                <button className="flex items-center px-3 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50">
                  <UserPlus className="w-4 h-4 mr-2" />
                  Add Member
                </button>
              </div>
              <div className="space-y-3">
                {selectedGroup.members.map((member) => (
                  <div key={member.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div className="flex items-center space-x-3">
                      <img
                        src={member.avatar || "/placeholder.svg"}
                        alt={member.name}
                        className="w-10 h-10 rounded-full"
                      />
                      <div>
                        <p className="font-medium text-gray-900">{member.name}</p>
                        <p className="text-sm text-gray-500">
                          {member.role} • Joined {formatDate(member.joinDate)}
                        </p>
                      </div>
                    </div>
                    <div className="flex items-center space-x-2">
                      <span
                        className={`px-2 py-1 text-xs font-medium rounded-full ${
                          member.role === "admin"
                            ? "bg-purple-100 text-purple-800"
                            : member.role === "moderator"
                              ? "bg-blue-100 text-blue-800"
                              : "bg-gray-100 text-gray-800"
                        }`}
                      >
                        {member.role}
                      </span>
                      {member.role !== "admin" && (
                        <button
                          onClick={() => handleRemoveMember(selectedGroup.id, member.id)}
                          className="p-1 text-gray-400 hover:text-red-600 rounded"
                          title="Remove member"
                        >
                          <UserMinus className="w-4 h-4" />
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Settings */}
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-4">Group Settings</h3>
              <div className="space-y-4">
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <p className="font-medium text-gray-900">Maximum Members</p>
                    <p className="text-sm text-gray-500">Current limit for group membership</p>
                  </div>
                  <span className="text-lg font-semibold text-gray-900">{selectedGroup.settings.maxMembers}</span>
                </div>
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <p className="font-medium text-gray-900">File Upload</p>
                    <p className="text-sm text-gray-500">Allow members to upload files</p>
                  </div>
                  <span
                    className={`px-3 py-1 text-sm font-medium rounded-full ${
                      selectedGroup.settings.allowFileUpload ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800"
                    }`}
                  >
                    {selectedGroup.settings.allowFileUpload ? "Enabled" : "Disabled"}
                  </span>
                </div>
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <p className="font-medium text-gray-900">Require Approval</p>
                    <p className="text-sm text-gray-500">New members need approval to join</p>
                  </div>
                  <span
                    className={`px-3 py-1 text-sm font-medium rounded-full ${
                      selectedGroup.settings.requireApproval
                        ? "bg-yellow-100 text-yellow-800"
                        : "bg-green-100 text-green-800"
                    }`}
                  >
                    {selectedGroup.settings.requireApproval ? "Required" : "Not Required"}
                  </span>
                </div>
                {selectedGroup.settings.password && (
                  <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                      <p className="font-medium text-gray-900">Group Password</p>
                      <p className="text-sm text-gray-500">Password protection is enabled</p>
                    </div>
                    <span className="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                      Protected
                    </span>
                  </div>
                )}
              </div>
            </div>
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
                <h1 className="text-2xl font-bold text-gray-900">Group Management</h1>
                <p className="text-gray-600 mt-1">Manage all groups, members, and settings</p>
              </div>

              <button
                onClick={() => setShowCreateModal(true)}
                className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                <Plus className="w-4 h-4 mr-2" />
                Create Group
              </button>
            </div>

            {/* Search */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <input
                  type="text"
                  placeholder="Search groups by name, description, category, or creator..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>

            {/* Groups Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
              {filteredGroups.map((group) => (
                <GroupCard key={group.id} group={group} />
              ))}
            </div>

            {filteredGroups.length === 0 && (
              <div className="text-center py-12">
                <Users className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No groups found</h3>
                <p className="text-gray-500">
                  {searchTerm ? "Try adjusting your search terms" : "Create your first group to get started"}
                </p>
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Group Detail Modal */}
      {showDetailModal && <GroupDetailModal />}
    </div>
  )
}

export default AdminGroupsView
