"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import {
  User,
  Mail,
  Phone,
  MapPin,
  Calendar,
  Edit,
  Save,
  X,
  Camera,
  Github,
  Linkedin,
  Globe,
  MessageCircle,
  FileText,
  Users,
  CheckCircle,
  Clock,
  Star,
  Shield,
  Key,
  Trash2,
  Eye,
  EyeOff,
} from "lucide-react"

const ProfileView = () => {
  const { user, logout } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [isEditing, setIsEditing] = useState(false)
  const [activeTab, setActiveTab] = useState("overview")
  const [profileData, setProfileData] = useState({})
  const [editData, setEditData] = useState({})
  const [showPasswordModal, setShowPasswordModal] = useState(false)
  const [showDeleteModal, setShowDeleteModal] = useState(false)

  useEffect(() => {
    // Load user profile data
    const mockProfileData = {
      ...user,
      bio: "Experienced researcher with a passion for AI and machine learning. Currently working on advanced analytics and data visualization projects.",
      phone: "+1 (555) 123-4567",
      location: "San Francisco, CA",
      joinDate: "2023-06-15T10:00:00Z",
      lastActive: "2024-01-15T14:30:00Z",
      socialLinks: {
        github: "https://github.com/johndoe",
        linkedin: "https://linkedin.com/in/johndoe",
        website: "https://johndoe.dev",
        twitter: "https://twitter.com/johndoe",
      },
      privacy: {
        profilePublic: true,
        showInSearch: true,
        allowMessages: true,
      },
      stats: {
        filesUploaded: 45,
        evidencesSubmitted: 32,
        groupsJoined: 8,
        commentsPosted: 127,
        likesReceived: 89,
        averageRating: 4.3,
      },
      recentActivity: [
        {
          id: 1,
          type: "upload",
          description: "Uploaded research document 'Q4_Analysis.pdf'",
          timestamp: "2024-01-15T10:30:00Z",
          status: "approved",
        },
        {
          id: 2,
          type: "comment",
          description: "Commented on Jane Wilson's evidence submission",
          timestamp: "2024-01-14T16:45:00Z",
          status: "active",
        },
        {
          id: 3,
          type: "group",
          description: "Joined 'Advanced Analytics Team' group",
          timestamp: "2024-01-13T09:15:00Z",
          status: "active",
        },
        {
          id: 4,
          type: "task",
          description: "Completed task 'Data Migration Testing'",
          timestamp: "2024-01-12T14:20:00Z",
          status: "completed",
        },
      ],
      groups: [
        { id: 1, name: "Research Team Alpha", role: "member", joinDate: "2023-12-01T10:00:00Z" },
        { id: 2, name: "Development Squad", role: "member", joinDate: "2023-11-15T08:30:00Z" },
        { id: 3, name: "Data Analytics Hub", role: "moderator", joinDate: "2023-10-20T12:45:00Z" },
      ],
    }

    setProfileData(mockProfileData)
    setEditData(mockProfileData)
  }, [user])

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  }

  const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
  }

  const handleSaveProfile = () => {
    setProfileData(editData)
    setIsEditing(false)
    // Here you would normally save to backend
    console.log("Profile updated:", editData)
  }

  const handleCancelEdit = () => {
    setEditData(profileData)
    setIsEditing(false)
  }

  const getActivityIcon = (type) => {
    switch (type) {
      case "upload":
        return FileText
      case "comment":
        return MessageCircle
      case "group":
        return Users
      case "task":
        return CheckCircle
      default:
        return Clock
    }
  }

  const getActivityColor = (status) => {
    switch (status) {
      case "approved":
        return "text-green-600 bg-green-100"
      case "completed":
        return "text-blue-600 bg-blue-100"
      case "active":
        return "text-gray-600 bg-gray-100"
      default:
        return "text-gray-600 bg-gray-100"
    }
  }

  const PasswordChangeModal = () => {
    const [passwordData, setPasswordData] = useState({
      currentPassword: "",
      newPassword: "",
      confirmPassword: "",
    })
    const [showPasswords, setShowPasswords] = useState({
      current: false,
      new: false,
      confirm: false,
    })

    const handlePasswordChange = (e) => {
      e.preventDefault()
      if (passwordData.newPassword !== passwordData.confirmPassword) {
        alert("New passwords don't match")
        return
      }
      // Here you would normally validate and update password
      console.log("Password change requested")
      setShowPasswordModal(false)
      setPasswordData({ currentPassword: "", newPassword: "", confirmPassword: "" })
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg p-6 w-full max-w-md">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>

          <form onSubmit={handlePasswordChange} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
              <div className="relative">
                <input
                  type={showPasswords.current ? "text" : "password"}
                  value={passwordData.currentPassword}
                  onChange={(e) => setPasswordData((prev) => ({ ...prev, currentPassword: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                />
                <button
                  type="button"
                  onClick={() => setShowPasswords((prev) => ({ ...prev, current: !prev.current }))}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                >
                  {showPasswords.current ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <div className="relative">
                <input
                  type={showPasswords.new ? "text" : "password"}
                  value={passwordData.newPassword}
                  onChange={(e) => setPasswordData((prev) => ({ ...prev, newPassword: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                />
                <button
                  type="button"
                  onClick={() => setShowPasswords((prev) => ({ ...prev, new: !prev.new }))}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                >
                  {showPasswords.new ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <div className="relative">
                <input
                  type={showPasswords.confirm ? "text" : "password"}
                  value={passwordData.confirmPassword}
                  onChange={(e) => setPasswordData((prev) => ({ ...prev, confirmPassword: e.target.value }))}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                />
                <button
                  type="button"
                  onClick={() => setShowPasswords((prev) => ({ ...prev, confirm: !prev.confirm }))}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                >
                  {showPasswords.confirm ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            <div className="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                onClick={() => setShowPasswordModal(false)}
                className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
              >
                Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    )
  }

  const DeleteAccountModal = () => {
    const [confirmText, setConfirmText] = useState("")

    const handleDeleteAccount = () => {
      if (confirmText === "DELETE") {
        // Here you would normally delete the account
        console.log("Account deletion requested")
        logout()
      } else {
        alert("Please type 'DELETE' to confirm")
      }
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg p-6 w-full max-w-md">
          <div className="flex items-center mb-4">
            <div className="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
              <Trash2 className="w-5 h-5 text-red-600" />
            </div>
            <h2 className="text-lg font-semibold text-gray-900">Delete Account</h2>
          </div>

          <div className="mb-6">
            <p className="text-gray-700 mb-4">
              This action cannot be undone. This will permanently delete your account and remove all your data from our
              servers.
            </p>
            <p className="text-sm text-gray-600 mb-4">
              Type <strong>DELETE</strong> to confirm:
            </p>
            <input
              type="text"
              value={confirmText}
              onChange={(e) => setConfirmText(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
              placeholder="Type DELETE here"
            />
          </div>

          <div className="flex justify-end space-x-3">
            <button
              onClick={() => setShowDeleteModal(false)}
              className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              onClick={handleDeleteAccount}
              disabled={confirmText !== "DELETE"}
              className="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Delete Account
            </button>
          </div>
        </div>
      </div>
    )
  }

  const tabs = [
    { id: "overview", label: "Overview", icon: User },
    { id: "activity", label: "Activity", icon: Clock },
    { id: "groups", label: "Groups", icon: Users },
    { id: "settings", label: "Settings", icon: Shield },
  ]

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-4xl mx-auto">
            {/* Profile Header */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <div className="flex items-center justify-between mb-6">
                <div className="flex items-center space-x-6">
                  <div className="relative">
                    <img
                      src={profileData.avatar || "/placeholder.svg?height=80&width=80"}
                      alt={profileData.name}
                      className="w-20 h-20 rounded-full object-cover"
                    />
                    {isEditing && (
                      <button className="absolute bottom-0 right-0 p-1 bg-blue-600 text-white rounded-full hover:bg-blue-700">
                        <Camera className="w-4 h-4" />
                      </button>
                    )}
                  </div>
                  <div>
                    <h1 className="text-2xl font-bold text-gray-900">{profileData.name}</h1>
                    <p className="text-gray-600 capitalize">{profileData.role}</p>
                    <p className="text-sm text-gray-500 mt-1">Member since {formatDate(profileData.joinDate)}</p>
                  </div>
                </div>

                <button
                  onClick={() => setIsEditing(!isEditing)}
                  className="flex items-center px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50"
                >
                  <Edit className="w-4 h-4 mr-2" />
                  {isEditing ? "Cancel" : "Edit Profile"}
                </button>
              </div>

              {/* Stats */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="text-center p-4 bg-blue-50 rounded-lg">
                  <p className="text-2xl font-bold text-blue-600">{profileData.stats?.filesUploaded}</p>
                  <p className="text-sm text-blue-800">Files Uploaded</p>
                </div>
                <div className="text-center p-4 bg-green-50 rounded-lg">
                  <p className="text-2xl font-bold text-green-600">{profileData.stats?.evidencesSubmitted}</p>
                  <p className="text-sm text-green-800">Evidences Submitted</p>
                </div>
                <div className="text-center p-4 bg-purple-50 rounded-lg">
                  <p className="text-2xl font-bold text-purple-600">{profileData.stats?.groupsJoined}</p>
                  <p className="text-sm text-purple-800">Groups Joined</p>
                </div>
                <div className="text-center p-4 bg-yellow-50 rounded-lg">
                  <div className="flex items-center justify-center space-x-1">
                    <p className="text-2xl font-bold text-yellow-600">{profileData.stats?.averageRating}</p>
                    <Star className="w-5 h-5 text-yellow-400 fill-current" />
                  </div>
                  <p className="text-sm text-yellow-800">Average Rating</p>
                </div>
              </div>
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

            {/* Tab Content */}
            {activeTab === "overview" && (
              <div className="space-y-6">
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <h2 className="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>

                  {isEditing ? (
                    <div className="space-y-4">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                          <input
                            type="text"
                            value={editData.name || ""}
                            onChange={(e) => setEditData((prev) => ({ ...prev, name: e.target.value }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                          <input
                            type="email"
                            value={editData.email || ""}
                            onChange={(e) => setEditData((prev) => ({ ...prev, email: e.target.value }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                          <input
                            type="tel"
                            value={editData.phone || ""}
                            onChange={(e) => setEditData((prev) => ({ ...prev, phone: e.target.value }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                          <input
                            type="text"
                            value={editData.location || ""}
                            onChange={(e) => setEditData((prev) => ({ ...prev, location: e.target.value }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                        <textarea
                          value={editData.bio || ""}
                          onChange={(e) => setEditData((prev) => ({ ...prev, bio: e.target.value }))}
                          rows={4}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                      </div>
                      <div className="flex justify-end space-x-3">
                        <button
                          onClick={handleCancelEdit}
                          className="flex items-center px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                          <X className="w-4 h-4 mr-2" />
                          Cancel
                        </button>
                        <button
                          onClick={handleSaveProfile}
                          className="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                        >
                          <Save className="w-4 h-4 mr-2" />
                          Save Changes
                        </button>
                      </div>
                    </div>
                  ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div className="space-y-4">
                        <div className="flex items-center space-x-3">
                          <Mail className="w-5 h-5 text-gray-400" />
                          <span className="text-gray-900">{profileData.email}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <Phone className="w-5 h-5 text-gray-400" />
                          <span className="text-gray-900">{profileData.phone || "Not provided"}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <MapPin className="w-5 h-5 text-gray-400" />
                          <span className="text-gray-900">{profileData.location || "Not provided"}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                          <Calendar className="w-5 h-5 text-gray-400" />
                          <span className="text-gray-900">Last active: {formatDateTime(profileData.lastActive)}</span>
                        </div>
                      </div>
                      <div>
                        <h3 className="font-medium text-gray-900 mb-2">Bio</h3>
                        <p className="text-gray-700">{profileData.bio || "No bio provided"}</p>
                      </div>
                    </div>
                  )}
                </div>

                {/* Social Links */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <h2 className="text-lg font-semibold text-gray-900 mb-4">Social Links</h2>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a
                      href={profileData.socialLinks?.github}
                      className="flex items-center space-x-2 text-gray-600 hover:text-gray-900"
                    >
                      <Github className="w-5 h-5" />
                      <span>GitHub</span>
                    </a>
                    <a
                      href={profileData.socialLinks?.linkedin}
                      className="flex items-center space-x-2 text-gray-600 hover:text-gray-900"
                    >
                      <Linkedin className="w-5 h-5" />
                      <span>LinkedIn</span>
                    </a>
                    <a
                      href={profileData.socialLinks?.website}
                      className="flex items-center space-x-2 text-gray-600 hover:text-gray-900"
                    >
                      <Globe className="w-5 h-5" />
                      <span>Website</span>
                    </a>
                  </div>
                </div>
              </div>
            )}

            {activeTab === "activity" && (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
                <div className="space-y-4">
                  {profileData.recentActivity?.map((activity) => {
                    const ActivityIcon = getActivityIcon(activity.type)
                    return (
                      <div key={activity.id} className="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div className={`p-2 rounded-full ${getActivityColor(activity.status)}`}>
                          <ActivityIcon className="w-4 h-4" />
                        </div>
                        <div className="flex-1">
                          <p className="text-gray-900">{activity.description}</p>
                          <p className="text-sm text-gray-500">{formatDateTime(activity.timestamp)}</p>
                        </div>
                        <span
                          className={`px-2 py-1 text-xs font-medium rounded-full ${getActivityColor(activity.status)}`}
                        >
                          {activity.status}
                        </span>
                      </div>
                    )
                  })}
                </div>
              </div>
            )}

            {activeTab === "groups" && (
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">My Groups</h2>
                <div className="space-y-4">
                  {profileData.groups?.map((group) => (
                    <div key={group.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                      <div>
                        <h3 className="font-medium text-gray-900">{group.name}</h3>
                        <p className="text-sm text-gray-500">
                          {group.role} • Joined {formatDate(group.joinDate)}
                        </p>
                      </div>
                      <span
                        className={`px-3 py-1 text-sm font-medium rounded-full ${
                          group.role === "admin"
                            ? "bg-purple-100 text-purple-800"
                            : group.role === "moderator"
                              ? "bg-blue-100 text-blue-800"
                              : "bg-gray-100 text-gray-800"
                        }`}
                      >
                        {group.role}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {activeTab === "settings" && (
              <div className="space-y-6">
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <h2 className="text-lg font-semibold text-gray-900 mb-4">Account Settings</h2>
                  <div className="space-y-4">
                    <button
                      onClick={() => setShowPasswordModal(true)}
                      className="flex items-center justify-between w-full p-4 bg-gray-50 rounded-lg hover:bg-gray-100"
                    >
                      <div className="flex items-center space-x-3">
                        <Key className="w-5 h-5 text-gray-400" />
                        <div className="text-left">
                          <p className="font-medium text-gray-900">Change Password</p>
                          <p className="text-sm text-gray-500">Update your account password</p>
                        </div>
                      </div>
                      <Edit className="w-5 h-5 text-gray-400" />
                    </button>
                  </div>
                </div>

                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <h2 className="text-lg font-semibold text-gray-900 mb-4">Privacy Settings</h2>
                  <div className="space-y-4">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="font-medium text-gray-900">Public Profile</p>
                        <p className="text-sm text-gray-500">Allow others to view your profile</p>
                      </div>
                      <input
                        type="checkbox"
                        checked={profileData.privacy?.profilePublic}
                        onChange={(e) =>
                          setProfileData((prev) => ({
                            ...prev,
                            privacy: { ...prev.privacy, profilePublic: e.target.checked },
                          }))
                        }
                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                    </div>
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="font-medium text-gray-900">Show in Search</p>
                        <p className="text-sm text-gray-500">Allow your profile to appear in search results</p>
                      </div>
                      <input
                        type="checkbox"
                        checked={profileData.privacy?.showInSearch}
                        onChange={(e) =>
                          setProfileData((prev) => ({
                            ...prev,
                            privacy: { ...prev.privacy, showInSearch: e.target.checked },
                          }))
                        }
                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                    </div>
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="font-medium text-gray-900">Allow Messages</p>
                        <p className="text-sm text-gray-500">Allow other users to send you messages</p>
                      </div>
                      <input
                        type="checkbox"
                        checked={profileData.privacy?.allowMessages}
                        onChange={(e) =>
                          setProfileData((prev) => ({
                            ...prev,
                            privacy: { ...prev.privacy, allowMessages: e.target.checked },
                          }))
                        }
                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                    </div>
                  </div>
                </div>

                <div className="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                  <h2 className="text-lg font-semibold text-red-900 mb-4">Danger Zone</h2>
                  <div className="space-y-4">
                    <button
                      onClick={() => setShowDeleteModal(true)}
                      className="flex items-center justify-between w-full p-4 bg-red-50 rounded-lg hover:bg-red-100 border border-red-200"
                    >
                      <div className="flex items-center space-x-3">
                        <Trash2 className="w-5 h-5 text-red-600" />
                        <div className="text-left">
                          <p className="font-medium text-red-900">Delete Account</p>
                          <p className="text-sm text-red-700">Permanently delete your account and all data</p>
                        </div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Modals */}
      {showPasswordModal && <PasswordChangeModal />}
      {showDeleteModal && <DeleteAccountModal />}
    </div>
  )
}

export default ProfileView
