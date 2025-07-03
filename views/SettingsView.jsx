"use client"

import { useState, useEffect } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import {
  Settings,
  Shield,
  Users,
  Database,
  Mail,
  Bell,
  Globe,
  Server,
  FileText,
  AlertTriangle,
  CheckCircle,
  Save,
  RefreshCw,
  Download,
  Upload,
  Eye,
  EyeOff,
} from "lucide-react"

const SettingsView = () => {
  const { user, isAdmin } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [activeTab, setActiveTab] = useState("general")
  const [settings, setSettings] = useState({})
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [showPasswords, setShowPasswords] = useState({})

  useEffect(() => {
    // Load system settings
    const loadSettings = async () => {
      setLoading(true)

      // Simulate API delay
      await new Promise((resolve) => setTimeout(resolve, 1000))

      const mockSettings = {
        general: {
          siteName: "Evidence Management Platform",
          siteDescription: "Comprehensive evidence management and collaboration platform",
          defaultLanguage: "en",
          timezone: "UTC",
          dateFormat: "MM/DD/YYYY",
          timeFormat: "12h",
          maintenanceMode: false,
          registrationEnabled: true,
          maxFileSize: 2048, // MB
          allowedFileTypes: ["pdf", "doc", "docx", "jpg", "png", "mp4", "zip"],
        },
        security: {
          passwordMinLength: 8,
          passwordRequireUppercase: true,
          passwordRequireLowercase: true,
          passwordRequireNumbers: true,
          passwordRequireSymbols: false,
          sessionTimeout: 24, // hours
          maxLoginAttempts: 5,
          lockoutDuration: 30, // minutes
          twoFactorRequired: false,
          ipWhitelist: [],
          sslRequired: true,
          encryptionEnabled: true,
        },
        users: {
          defaultRole: "user",
          autoApproveUsers: false,
          allowProfilePictures: true,
          allowUsernameChange: true,
          allowEmailChange: true,
          requireEmailVerification: true,
          maxGroupsPerUser: 10,
          maxFilesPerUser: 1000,
        },
        content: {
          moderationEnabled: true,
          autoScanFiles: true,
          allowedDomains: [],
          blockedDomains: [],
          maxCommentLength: 1000,
          allowFileSharing: true,
          allowPublicGroups: true,
          requireEvidenceApproval: true,
        },
        notifications: {
          emailEnabled: true,
          pushEnabled: true,
          smsEnabled: false,
          digestFrequency: "daily",
          notifyOnUpload: true,
          notifyOnApproval: true,
          notifyOnComment: true,
          notifyOnGroupInvite: true,
          adminNotifications: true,
        },
        email: {
          smtpHost: "smtp.gmail.com",
          smtpPort: 587,
          smtpUsername: "admin@company.com",
          smtpPassword: "••••••••",
          smtpEncryption: "tls",
          fromName: "Evidence Platform",
          fromEmail: "noreply@company.com",
          testEmailSent: false,
        },
        storage: {
          provider: "local",
          localPath: "/uploads",
          s3Bucket: "",
          s3Region: "",
          s3AccessKey: "",
          s3SecretKey: "••••••••",
          maxStorageSize: 100, // GB
          compressionEnabled: true,
          backupEnabled: true,
          backupFrequency: "daily",
          retentionPeriod: 365, // days
        },
        integrations: {
          googleAnalytics: "",
          slackWebhook: "",
          discordWebhook: "",
          zapierEnabled: false,
          apiEnabled: true,
          webhooksEnabled: true,
          rateLimitEnabled: true,
          rateLimitRequests: 1000,
          rateLimitWindow: 60, // minutes
        },
        advanced: {
          debugMode: false,
          logLevel: "info",
          cacheEnabled: true,
          cacheTtl: 3600, // seconds
          compressionEnabled: true,
          minifyAssets: true,
          cdnEnabled: false,
          cdnUrl: "",
          customCss: "",
          customJs: "",
        },
      }

      setSettings(mockSettings)
      setLoading(false)
    }

    loadSettings()
  }, [])

  const handleSaveSettings = async (section) => {
    setSaving(true)

    try {
      // Simulate API call
      await new Promise((resolve) => setTimeout(resolve, 1500))

      // Here you would normally save to backend
      console.log(`Saving ${section} settings:`, settings[section])

      // Show success message
      alert(`${section.charAt(0).toUpperCase() + section.slice(1)} settings saved successfully!`)
    } catch (error) {
      console.error("Error saving settings:", error)
      alert("Failed to save settings. Please try again.")
    } finally {
      setSaving(false)
    }
  }

  const handleTestEmail = async () => {
    setSaving(true)

    try {
      await new Promise((resolve) => setTimeout(resolve, 2000))

      setSettings((prev) => ({
        ...prev,
        email: { ...prev.email, testEmailSent: true },
      }))

      alert("Test email sent successfully!")
    } catch (error) {
      alert("Failed to send test email.")
    } finally {
      setSaving(false)
    }
  }

  const handleResetSection = (section) => {
    if (window.confirm(`Are you sure you want to reset ${section} settings to default values?`)) {
      // Reset to default values
      console.log(`Resetting ${section} settings`)
      alert(`${section.charAt(0).toUpperCase() + section.slice(1)} settings reset to defaults.`)
    }
  }

  const togglePasswordVisibility = (field) => {
    setShowPasswords((prev) => ({
      ...prev,
      [field]: !prev[field],
    }))
  }

  const tabs = [
    { id: "general", label: "General", icon: Settings },
    { id: "security", label: "Security", icon: Shield },
    { id: "users", label: "Users", icon: Users },
    { id: "content", label: "Content", icon: FileText },
    { id: "notifications", label: "Notifications", icon: Bell },
    { id: "email", label: "Email", icon: Mail },
    { id: "storage", label: "Storage", icon: Database },
    { id: "integrations", label: "Integrations", icon: Globe },
    { id: "advanced", label: "Advanced", icon: Server },
  ]

  if (!isAdmin) {
    return (
      <div className="flex h-screen bg-gray-50">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        <div className="flex-1 flex flex-col overflow-hidden">
          <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />
          <main className="flex-1 flex items-center justify-center">
            <div className="text-center">
              <AlertTriangle className="mx-auto h-12 w-12 text-red-500 mb-4" />
              <h2 className="text-xl font-semibold text-gray-900 mb-2">Access Denied</h2>
              <p className="text-gray-600">This page is only accessible to administrators.</p>
            </div>
          </main>
        </div>
      </div>
    )
  }

  if (loading) {
    return (
      <div className="flex h-screen bg-gray-50">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        <div className="flex-1 flex flex-col overflow-hidden">
          <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />
          <main className="flex-1 flex items-center justify-center">
            <div className="text-center">
              <div className="spinner mx-auto mb-4"></div>
              <p className="text-gray-600">Loading system settings...</p>
            </div>
          </main>
        </div>
      </div>
    )
  }

  const renderGeneralSettings = () => (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Site Configuration</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
            <input
              type="text"
              value={settings.general?.siteName || ""}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  general: { ...prev.general, siteName: e.target.value },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Default Language</label>
            <select
              value={settings.general?.defaultLanguage || "en"}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  general: { ...prev.general, defaultLanguage: e.target.value },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="en">English</option>
              <option value="es">Spanish</option>
              <option value="fr">French</option>
              <option value="de">German</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
            <select
              value={settings.general?.timezone || "UTC"}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  general: { ...prev.general, timezone: e.target.value },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="UTC">UTC</option>
              <option value="America/New_York">Eastern Time</option>
              <option value="America/Chicago">Central Time</option>
              <option value="America/Denver">Mountain Time</option>
              <option value="America/Los_Angeles">Pacific Time</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Max File Size (MB)</label>
            <input
              type="number"
              value={settings.general?.maxFileSize || 2048}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  general: { ...prev.general, maxFileSize: Number.parseInt(e.target.value) },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
        <div className="mt-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">Site Description</label>
          <textarea
            value={settings.general?.siteDescription || ""}
            onChange={(e) =>
              setSettings((prev) => ({
                ...prev,
                general: { ...prev.general, siteDescription: e.target.value },
              }))
            }
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div className="mt-6 space-y-4">
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.general?.maintenanceMode || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  general: { ...prev.general, maintenanceMode: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Maintenance Mode</span>
          </label>
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.general?.registrationEnabled || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  general: { ...prev.general, registrationEnabled: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Allow User Registration</span>
          </label>
        </div>
      </div>
    </div>
  )

  const renderSecuritySettings = () => (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Password Policy</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Minimum Length</label>
            <input
              type="number"
              min="6"
              max="32"
              value={settings.security?.passwordMinLength || 8}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, passwordMinLength: Number.parseInt(e.target.value) },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Session Timeout (hours)</label>
            <input
              type="number"
              min="1"
              max="168"
              value={settings.security?.sessionTimeout || 24}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, sessionTimeout: Number.parseInt(e.target.value) },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
        <div className="mt-6 space-y-4">
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.security?.passwordRequireUppercase || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, passwordRequireUppercase: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Require Uppercase Letters</span>
          </label>
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.security?.passwordRequireLowercase || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, passwordRequireLowercase: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Require Lowercase Letters</span>
          </label>
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.security?.passwordRequireNumbers || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, passwordRequireNumbers: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Require Numbers</span>
          </label>
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.security?.passwordRequireSymbols || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, passwordRequireSymbols: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Require Special Characters</span>
          </label>
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={settings.security?.twoFactorRequired || false}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, twoFactorRequired: e.target.checked },
                }))
              }
              className="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm text-gray-700">Require Two-Factor Authentication</span>
          </label>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Login Security</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
            <input
              type="number"
              min="3"
              max="10"
              value={settings.security?.maxLoginAttempts || 5}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, maxLoginAttempts: Number.parseInt(e.target.value) },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (minutes)</label>
            <input
              type="number"
              min="5"
              max="1440"
              value={settings.security?.lockoutDuration || 30}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  security: { ...prev.security, lockoutDuration: Number.parseInt(e.target.value) },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
      </div>
    </div>
  )

  const renderEmailSettings = () => (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">SMTP Configuration</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
            <input
              type="text"
              value={settings.email?.smtpHost || ""}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  email: { ...prev.email, smtpHost: e.target.value },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
            <input
              type="number"
              value={settings.email?.smtpPort || 587}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  email: { ...prev.email, smtpPort: Number.parseInt(e.target.value) },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input
              type="email"
              value={settings.email?.smtpUsername || ""}
              onChange={(e) =>
                setSettings((prev) => ({
                  ...prev,
                  email: { ...prev.email, smtpUsername: e.target.value },
                }))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <div className="relative">
              <input
                type={showPasswords.smtp ? "text" : "password"}
                value={settings.email?.smtpPassword || ""}
                onChange={(e) =>
                  setSettings((prev) => ({
                    ...prev,
                    email: { ...prev.email, smtpPassword: e.target.value },
                  }))
                }
                className="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button
                type="button"
                onClick={() => togglePasswordVisibility("smtp")}
                className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                {showPasswords.smtp ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>
        </div>
        <div className="mt-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
          <select
            value={settings.email?.smtpEncryption || "tls"}
            onChange={(e) =>
              setSettings((prev) => ({
                ...prev,
                email: { ...prev.email, smtpEncryption: e.target.value },
              }))
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="none">None</option>
            <option value="tls">TLS</option>
            <option value="ssl">SSL</option>
          </select>
        </div>
        <div className="mt-6 flex justify-end">
          <button
            onClick={handleTestEmail}
            disabled={saving}
            className="flex items-center px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50 disabled:opacity-50"
          >
            {saving ? (
              <>
                <div className="spinner w-4 h-4 mr-2"></div>
                Sending...
              </>
            ) : (
              <>
                <Mail className="w-4 h-4 mr-2" />
                Send Test Email
              </>
            )}
          </button>
        </div>
        {settings.email?.testEmailSent && (
          <div className="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <div className="flex items-center">
              <CheckCircle className="w-4 h-4 text-green-600 mr-2" />
              <span className="text-sm text-green-800">Test email sent successfully!</span>
            </div>
          </div>
        )}
      </div>
    </div>
  )

  const renderTabContent = () => {
    switch (activeTab) {
      case "general":
        return renderGeneralSettings()
      case "security":
        return renderSecuritySettings()
      case "email":
        return renderEmailSettings()
      default:
        return (
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="text-center py-12">
              <Settings className="mx-auto h-12 w-12 text-gray-400 mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">
                {tabs.find((tab) => tab.id === activeTab)?.label} Settings
              </h3>
              <p className="text-gray-500">This section is under development.</p>
            </div>
          </div>
        )
    }
  }

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex-1 flex flex-col overflow-hidden">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} sidebarOpen={sidebarOpen} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="max-w-7xl mx-auto">
            <div className="mb-6">
              <h1 className="text-2xl font-bold text-gray-900">System Settings</h1>
              <p className="text-gray-600 mt-1">Configure and manage system-wide settings</p>
            </div>

            <div className="flex flex-col lg:flex-row gap-6">
              {/* Settings Navigation */}
              <div className="lg:w-64 flex-shrink-0">
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                  <nav className="space-y-2">
                    {tabs.map((tab) => {
                      const TabIcon = tab.icon
                      return (
                        <button
                          key={tab.id}
                          onClick={() => setActiveTab(tab.id)}
                          className={`w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                            activeTab === tab.id
                              ? "bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                              : "text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                          }`}
                        >
                          <TabIcon className="w-4 h-4 mr-3" />
                          {tab.label}
                        </button>
                      )
                    })}
                  </nav>
                </div>
              </div>

              {/* Settings Content */}
              <div className="flex-1">
                {renderTabContent()}

                {/* Action Buttons */}
                <div className="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <button
                        onClick={() => handleSaveSettings(activeTab)}
                        disabled={saving}
                        className="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                      >
                        {saving ? (
                          <>
                            <div className="spinner w-4 h-4 mr-2"></div>
                            Saving...
                          </>
                        ) : (
                          <>
                            <Save className="w-4 h-4 mr-2" />
                            Save Changes
                          </>
                        )}
                      </button>

                      <button
                        onClick={() => handleResetSection(activeTab)}
                        className="flex items-center px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                      >
                        <RefreshCw className="w-4 h-4 mr-2" />
                        Reset to Defaults
                      </button>
                    </div>

                    <div className="flex items-center space-x-2">
                      <button className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                        <Download className="w-4 h-4 mr-2" />
                        Export
                      </button>
                      <button className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                        <Upload className="w-4 h-4 mr-2" />
                        Import
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}

export default SettingsView
