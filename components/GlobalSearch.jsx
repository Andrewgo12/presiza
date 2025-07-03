"use client"

import { useState, useEffect, useRef } from "react"
import { Search, FileText, Users, MessageCircle, User, X } from "lucide-react"
import { useNavigate } from "react-router-dom"

const GlobalSearch = ({ isOpen, onClose }) => {
  const [query, setQuery] = useState("")
  const [results, setResults] = useState({})
  const [activeFilter, setActiveFilter] = useState("all")
  const [loading, setLoading] = useState(false)
  const [selectedIndex, setSelectedIndex] = useState(0)
  const searchRef = useRef(null)
  const navigate = useNavigate()

  useEffect(() => {
    if (isOpen && searchRef.current) {
      searchRef.current.focus()
    }
  }, [isOpen])

  useEffect(() => {
    const handleKeyDown = (e) => {
      if (!isOpen) return

      if (e.key === "Escape") {
        onClose()
      } else if (e.key === "ArrowDown") {
        e.preventDefault()
        setSelectedIndex((prev) => Math.min(prev + 1, getTotalResults() - 1))
      } else if (e.key === "ArrowUp") {
        e.preventDefault()
        setSelectedIndex((prev) => Math.max(prev - 1, 0))
      } else if (e.key === "Enter") {
        e.preventDefault()
        handleResultSelect(getSelectedResult())
      }
    }

    document.addEventListener("keydown", handleKeyDown)
    return () => document.removeEventListener("keydown", handleKeyDown)
  }, [isOpen, selectedIndex, results])

  useEffect(() => {
    if (query.length > 2) {
      performSearch(query)
    } else {
      setResults({})
      setSelectedIndex(0)
    }
  }, [query, activeFilter])

  const performSearch = async (searchQuery) => {
    setLoading(true)

    // Simulate API delay
    await new Promise((resolve) => setTimeout(resolve, 300))

    // Mock search results
    const mockResults = {
      files: [
        {
          id: 1,
          name: "Research_Analysis_Q4.pdf",
          type: "document",
          author: "Dr. Smith",
          group: "Research Team Alpha",
          uploadDate: "2024-01-15T10:30:00Z",
          size: "2.1 MB",
        },
        {
          id: 2,
          name: "UI_Mockups_Dashboard.png",
          type: "image",
          author: "Jane Wilson",
          group: "Design Collective",
          uploadDate: "2024-01-14T16:45:00Z",
          size: "1.5 MB",
        },
      ],
      users: [
        {
          id: 1,
          name: "Dr. Smith",
          role: "admin",
          avatar: "/placeholder.svg?height=32&width=32",
          lastActive: "2024-01-15T14:30:00Z",
        },
        {
          id: 2,
          name: "John Smith",
          role: "user",
          avatar: "/placeholder.svg?height=32&width=32",
          lastActive: "2024-01-14T12:20:00Z",
        },
      ],
      groups: [
        {
          id: 1,
          name: "Research Team Alpha",
          type: "public",
          members: 24,
          description: "Advanced research in AI and machine learning",
        },
      ],
      messages: [
        {
          id: 1,
          content: "Thanks for the research data analysis!",
          sender: "Dr. Smith",
          timestamp: "2024-01-15T14:30:00Z",
          conversationId: 1,
        },
      ],
      evidences: [
        {
          id: 1,
          title: "Q4 Research Analysis Report",
          author: "Dr. Smith",
          status: "approved",
          submissionDate: "2024-01-15T10:30:00Z",
          rating: 4.5,
        },
      ],
    }

    // Filter results based on activeFilter
    let filteredResults = {}
    if (activeFilter === "all") {
      filteredResults = mockResults
    } else {
      filteredResults[activeFilter] = mockResults[activeFilter] || []
    }

    setResults(filteredResults)
    setSelectedIndex(0)
    setLoading(false)
  }

  const getTotalResults = () => {
    return Object.values(results).reduce((total, items) => total + (items?.length || 0), 0)
  }

  const getSelectedResult = () => {
    let currentIndex = 0
    for (const [type, items] of Object.entries(results)) {
      if (!items || items.length === 0) continue

      for (const item of items) {
        if (currentIndex === selectedIndex) {
          return { type, item }
        }
        currentIndex++
      }
    }
    return null
  }

  const handleResultSelect = (result) => {
    if (!result) return

    onClose()
    setQuery("")

    const { type, item } = result

    switch (type) {
      case "files":
        navigate("/files")
        break
      case "users":
        navigate("/profile")
        break
      case "groups":
        navigate("/groups")
        break
      case "messages":
        navigate("/messages")
        break
      case "evidences":
        navigate("/evidences")
        break
      default:
        break
    }
  }

  const getIcon = (type) => {
    switch (type) {
      case "files":
        return FileText
      case "users":
        return User
      case "groups":
        return Users
      case "messages":
        return MessageCircle
      case "evidences":
        return FileText
      default:
        return Search
    }
  }

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
  }

  const filters = [
    { id: "all", label: "All", icon: Search },
    { id: "files", label: "Files", icon: FileText },
    { id: "users", label: "Users", icon: User },
    { id: "groups", label: "Groups", icon: Users },
    { id: "messages", label: "Messages", icon: MessageCircle },
    { id: "evidences", label: "Evidences", icon: FileText },
  ]

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start justify-center pt-20 px-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden animate-scale-in">
        {/* Search Header */}
        <div className="p-4 border-b border-gray-200">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              ref={searchRef}
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search files, users, groups, messages..."
              className="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
            />
            <button
              onClick={onClose}
              className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Filters */}
        <div className="p-4 border-b border-gray-200">
          <div className="flex items-center space-x-2 overflow-x-auto">
            {filters.map((filter) => {
              const FilterIcon = filter.icon
              return (
                <button
                  key={filter.id}
                  onClick={() => setActiveFilter(filter.id)}
                  className={`flex items-center px-3 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors ${
                    activeFilter === filter.id
                      ? "bg-blue-100 text-blue-700"
                      : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                  }`}
                >
                  <FilterIcon className="w-4 h-4 mr-2" />
                  {filter.label}
                </button>
              )
            })}
          </div>
        </div>

        {/* Results */}
        <div className="max-h-96 overflow-y-auto">
          {loading ? (
            <div className="p-8 text-center">
              <div className="spinner mx-auto mb-4"></div>
              <p className="text-gray-500">Searching...</p>
            </div>
          ) : Object.keys(results).length === 0 && query.length > 2 ? (
            <div className="p-8 text-center">
              <Search className="mx-auto h-8 w-8 text-gray-400 mb-4" />
              <p className="text-gray-500">No results found for "{query}"</p>
              <p className="text-sm text-gray-400 mt-2">Try adjusting your search terms or filters</p>
            </div>
          ) : query.length <= 2 ? (
            <div className="p-8 text-center">
              <Search className="mx-auto h-8 w-8 text-gray-400 mb-4" />
              <p className="text-gray-500">Start typing to search</p>
              <p className="text-sm text-gray-400 mt-2">Search across files, users, groups, and more</p>
            </div>
          ) : (
            <div className="p-4 space-y-6">
              {Object.entries(results).map(([type, items]) => {
                if (!items || items.length === 0) return null

                const Icon = getIcon(type)
                let currentIndex = 0

                // Calculate the starting index for this section
                for (const [prevType, prevItems] of Object.entries(results)) {
                  if (prevType === type) break
                  currentIndex += prevItems?.length || 0
                }

                return (
                  <div key={type}>
                    <div className="flex items-center mb-3">
                      <Icon className="w-4 h-4 text-gray-400 mr-2" />
                      <h3 className="text-sm font-medium text-gray-900 capitalize">
                        {type} ({items.length})
                      </h3>
                    </div>

                    <div className="space-y-2">
                      {items.map((item, index) => {
                        const isSelected = currentIndex + index === selectedIndex
                        return (
                          <button
                            key={item.id}
                            onClick={() => handleResultSelect({ type, item })}
                            className={`w-full text-left p-3 rounded-lg border transition-colors ${
                              isSelected ? "border-blue-200 bg-blue-50" : "border-gray-100 hover:bg-gray-50"
                            }`}
                          >
                            {type === "files" && (
                              <div>
                                <p className="font-medium text-gray-900">{item.name}</p>
                                <p className="text-sm text-gray-500">
                                  by {item.author} in {item.group} • {item.size}
                                </p>
                                <p className="text-xs text-gray-400">{formatDate(item.uploadDate)}</p>
                              </div>
                            )}

                            {type === "users" && (
                              <div className="flex items-center space-x-3">
                                <img
                                  src={item.avatar || "/placeholder.svg"}
                                  alt={item.name}
                                  className="w-8 h-8 rounded-full"
                                />
                                <div>
                                  <p className="font-medium text-gray-900">{item.name}</p>
                                  <p className="text-sm text-gray-500 capitalize">
                                    {item.role} • Last active {formatDate(item.lastActive)}
                                  </p>
                                </div>
                              </div>
                            )}

                            {type === "groups" && (
                              <div>
                                <p className="font-medium text-gray-900">{item.name}</p>
                                <p className="text-sm text-gray-500">
                                  {item.members} members • {item.type}
                                </p>
                                <p className="text-xs text-gray-400">{item.description}</p>
                              </div>
                            )}

                            {type === "messages" && (
                              <div>
                                <p className="text-gray-900">{item.content}</p>
                                <p className="text-sm text-gray-500">
                                  from {item.sender} • {formatDate(item.timestamp)}
                                </p>
                              </div>
                            )}

                            {type === "evidences" && (
                              <div>
                                <p className="font-medium text-gray-900">{item.title}</p>
                                <p className="text-sm text-gray-500">
                                  by {item.author} • {item.status} • ⭐ {item.rating}
                                </p>
                                <p className="text-xs text-gray-400">{formatDate(item.submissionDate)}</p>
                              </div>
                            )}
                          </button>
                        )
                      })}
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="p-4 border-t border-gray-200 bg-gray-50">
          <div className="flex items-center justify-between text-sm text-gray-500">
            <div className="flex items-center space-x-4">
              <span>Press ESC to close</span>
              <span>Use ↑↓ to navigate</span>
              <span>Press Enter to select</span>
            </div>
            <kbd className="px-2 py-1 text-xs font-mono bg-white border border-gray-300 rounded">⌘K</kbd>
          </div>
        </div>
      </div>
    </div>
  )
}

export default GlobalSearch
