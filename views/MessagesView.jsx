"use client"

import { useState, useEffect, useRef } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { Search, Send, Paperclip, Smile, Phone, Video, MoreVertical, Circle, CheckCircle2 } from "lucide-react"

const MessagesView = () => {
  const { user } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [conversations, setConversations] = useState([])
  const [activeConversation, setActiveConversation] = useState(null)
  const [messages, setMessages] = useState({})
  const [newMessage, setNewMessage] = useState("")
  const [searchTerm, setSearchTerm] = useState("")
  const [showNewChatModal, setShowNewChatModal] = useState(false)
  const messagesEndRef = useRef(null)

  useEffect(() => {
    // Load mock conversations
    const mockConversations = [
      {
        id: 1,
        type: "individual",
        name: "Dr. Smith",
        avatar: "/placeholder.svg?height=40&width=40",
        lastMessage: "Thanks for the research data!",
        lastMessageTime: "2024-01-15T14:30:00Z",
        unreadCount: 2,
        isOnline: true,
        participants: [1, user.id],
      },
      {
        id: 2,
        type: "group",
        name: "Research Team Alpha",
        avatar: "/placeholder.svg?height=40&width=40",
        lastMessage: "Meeting scheduled for tomorrow",
        lastMessageTime: "2024-01-15T12:15:00Z",
        unreadCount: 0,
        isOnline: false,
        participants: [1, 2, 3, user.id],
      },
      {
        id: 3,
        type: "individual",
        name: "Jane Wilson",
        avatar: "/placeholder.svg?height=40&width=40",
        lastMessage: "Could you review my latest design?",
        lastMessageTime: "2024-01-14T16:45:00Z",
        unreadCount: 1,
        isOnline: true,
        participants: [3, user.id],
      },
      {
        id: 4,
        type: "group",
        name: "Development Squad",
        avatar: "/placeholder.svg?height=40&width=40",
        lastMessage: "Code review completed",
        lastMessageTime: "2024-01-14T10:20:00Z",
        unreadCount: 0,
        isOnline: false,
        participants: [2, 4, 5, user.id],
      },
    ]

    // Load mock messages
    const mockMessages = {
      1: [
        {
          id: 1,
          senderId: 1,
          senderName: "Dr. Smith",
          content: "Hi! I've reviewed your latest research submission.",
          timestamp: "2024-01-15T13:30:00Z",
          type: "text",
          status: "read",
        },
        {
          id: 2,
          senderId: user.id,
          senderName: user.name,
          content: "Thank you! What are your thoughts on the methodology?",
          timestamp: "2024-01-15T13:35:00Z",
          type: "text",
          status: "read",
        },
        {
          id: 3,
          senderId: 1,
          senderName: "Dr. Smith",
          content: "The approach is solid. I have some suggestions for the data analysis section.",
          timestamp: "2024-01-15T14:20:00Z",
          type: "text",
          status: "delivered",
        },
        {
          id: 4,
          senderId: 1,
          senderName: "Dr. Smith",
          content: "Thanks for the research data!",
          timestamp: "2024-01-15T14:30:00Z",
          type: "text",
          status: "delivered",
        },
      ],
      2: [
        {
          id: 5,
          senderId: 1,
          senderName: "Dr. Smith",
          content: "Team meeting scheduled for tomorrow at 10 AM",
          timestamp: "2024-01-15T12:15:00Z",
          type: "text",
          status: "read",
        },
        {
          id: 6,
          senderId: 2,
          senderName: "John Doe",
          content: "I'll be there. Should I prepare the quarterly report?",
          timestamp: "2024-01-15T12:20:00Z",
          type: "text",
          status: "read",
        },
      ],
      3: [
        {
          id: 7,
          senderId: 3,
          senderName: "Jane Wilson",
          content: "Hi! I've uploaded the new UI mockups.",
          timestamp: "2024-01-14T16:40:00Z",
          type: "text",
          status: "read",
        },
        {
          id: 8,
          senderId: 3,
          senderName: "Jane Wilson",
          content: "Could you review my latest design?",
          timestamp: "2024-01-14T16:45:00Z",
          type: "text",
          status: "delivered",
        },
      ],
    }

    setConversations(mockConversations)
    setMessages(mockMessages)
    setActiveConversation(mockConversations[0])
  }, [user.id, user.name])

  useEffect(() => {
    scrollToBottom()
  }, [messages, activeConversation])

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" })
  }

  const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)

    if (diffInHours < 24) {
      return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      })
    } else {
      return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
      })
    }
  }

  const handleSendMessage = (e) => {
    e.preventDefault()
    if (!newMessage.trim() || !activeConversation) return

    const message = {
      id: Date.now(),
      senderId: user.id,
      senderName: user.name,
      content: newMessage,
      timestamp: new Date().toISOString(),
      type: "text",
      status: "sent",
    }

    setMessages((prev) => ({
      ...prev,
      [activeConversation.id]: [...(prev[activeConversation.id] || []), message],
    }))

    // Update conversation last message
    setConversations((prev) =>
      prev.map((conv) =>
        conv.id === activeConversation.id
          ? {
              ...conv,
              lastMessage: newMessage,
              lastMessageTime: new Date().toISOString(),
            }
          : conv,
      ),
    )

    setNewMessage("")
  }

  const filteredConversations = conversations.filter(
    (conv) =>
      conv.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      conv.lastMessage.toLowerCase().includes(searchTerm.toLowerCase()),
  )

  const ConversationItem = ({ conversation }) => (
    <div
      onClick={() => setActiveConversation(conversation)}
      className={`flex items-center p-4 cursor-pointer hover:bg-gray-50 ${
        activeConversation?.id === conversation.id ? "bg-blue-50 border-r-2 border-blue-500" : ""
      }`}
    >
      <div className="relative">
        <img
          src={conversation.avatar || "/placeholder.svg"}
          alt={conversation.name}
          className="w-12 h-12 rounded-full object-cover"
        />
        {conversation.isOnline && (
          <div className="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
        )}
      </div>

      <div className="ml-3 flex-1 min-w-0">
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-medium text-gray-900 truncate">{conversation.name}</h3>
          <span className="text-xs text-gray-500">{formatTime(conversation.lastMessageTime)}</span>
        </div>
        <div className="flex items-center justify-between mt-1">
          <p className="text-sm text-gray-600 truncate">{conversation.lastMessage}</p>
          {conversation.unreadCount > 0 && (
            <span className="ml-2 bg-blue-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">
              {conversation.unreadCount}
            </span>
          )}
        </div>
      </div>
    </div>
  )

  const MessageBubble = ({ message, isOwn }) => (
    <div className={`flex ${isOwn ? "justify-end" : "justify-start"} mb-4`}>
      <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${isOwn ? "bg-blue-500 text-white" : "bg-gray-200"}`}>
        {!isOwn && <p className="text-xs font-medium mb-1 text-gray-600">{message.senderName}</p>}
        <p className="text-sm">{message.content}</p>
        <div className={`flex items-center justify-end mt-1 space-x-1`}>
          <span className={`text-xs ${isOwn ? "text-blue-100" : "text-gray-500"}`}>
            {formatTime(message.timestamp)}
          </span>
          {isOwn && (
            <div className="text-blue-100">
              {message.status === "sent" && <Circle className="w-3 h-3" />}
              {message.status === "delivered" && <CheckCircle2 className="w-3 h-3" />}
              {message.status === "read" && <CheckCircle2 className="w-3 h-3 fill-current" />}
            </div>
          )}
        </div>
      </div>
    </div>
  )

  const NewChatModal = () => {
    const [selectedUsers, setSelectedUsers] = useState([])
    const [chatName, setChatName] = useState("")
    const [chatType, setChatType] = useState("individual")

    const availableUsers = [
      { id: 1, name: "Dr. Smith", avatar: "/placeholder.svg?height=32&width=32" },
      { id: 3, name: "Jane Wilson", avatar: "/placeholder.svg?height=32&width=32" },
      { id: 4, name: "Mike Chen", avatar: "/placeholder.svg?height=32&width=32" },
      { id: 5, name: "Sarah Johnson", avatar: "/placeholder.svg?height=32&width=32" },
    ]

    const handleCreateChat = () => {
      if (selectedUsers.length === 0) return

      const newConversation = {
        id: Date.now(),
        type: chatType,
        name: chatType === "group" ? chatName : availableUsers.find((u) => u.id === selectedUsers[0])?.name,
        avatar: "/placeholder.svg?height=40&width=40",
        lastMessage: "Chat created",
        lastMessageTime: new Date().toISOString(),
        unreadCount: 0,
        isOnline: false,
        participants: [user.id, ...selectedUsers],
      }

      setConversations((prev) => [newConversation, ...prev])
      setMessages((prev) => ({ ...prev, [newConversation.id]: [] }))
      setActiveConversation(newConversation)
      setShowNewChatModal(false)
      setSelectedUsers([])
      setChatName("")
    }

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg p-6 w-full max-w-md">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">New Conversation</h2>

          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Chat Type</label>
              <select
                value={chatType}
                onChange={(e) => setChatType(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="individual">Individual Chat</option>
                <option value="group">Group Chat</option>
              </select>
            </div>

            {chatType === "group" && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                <input
                  type="text"
                  value={chatName}
                  onChange={(e) => setChatName(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter group name"
                />
              </div>
            )}

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Select {chatType === "individual" ? "User" : "Users"}
              </label>
              <div className="space-y-2 max-h-40 overflow-y-auto">
                {availableUsers.map((user) => (
                  <label key={user.id} className="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                    <input
                      type={chatType === "individual" ? "radio" : "checkbox"}
                      name="selectedUsers"
                      value={user.id}
                      checked={selectedUsers.includes(user.id)}
                      onChange={(e) => {
                        if (chatType === "individual") {
                          setSelectedUsers(e.target.checked ? [user.id] : [])
                        } else {
                          setSelectedUsers((prev) =>
                            e.target.checked ? [...prev, user.id] : prev.filter((id) => id !== user.id),
                          )
                        }
                      }}
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <img src={user.avatar || "/placeholder.svg"} alt={user.name} className="w-8 h-8 rounded-full" />
                    <span className="text-sm text-gray-900">{user.name}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          <div className="flex justify-end space-x-3 mt-6">
            <button
              onClick={() => setShowNewChatModal(false)}
              className="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              onClick={handleCreateChat}
              disabled={selectedUsers.length === 0 || (chatType === "group" && !chatName.trim())}
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Create Chat
            </button>
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

        <div className="flex-1 flex overflow-hidden">
          {/* Conversations List */}
          <div className="w-80 bg-white border-r border-gray-200 flex flex-col">
            <div className="p-4 border-b border-gray-200">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-lg font-semibold text-gray-900">Messages</h2>
                <button
                  onClick={() => setShowNewChatModal(true)}
                  className="p-2 text-blue-600 hover:bg-blue-50 rounded-full"
                >
                  <Send className="w-5 h-5" />
                </button>
              </div>

              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <input
                  type="text"
                  placeholder="Search conversations..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto">
              {filteredConversations.map((conversation) => (
                <ConversationItem key={conversation.id} conversation={conversation} />
              ))}
            </div>
          </div>

          {/* Chat Area */}
          <div className="flex-1 flex flex-col">
            {activeConversation ? (
              <>
                {/* Chat Header */}
                <div className="bg-white border-b border-gray-200 p-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                      <img
                        src={activeConversation.avatar || "/placeholder.svg"}
                        alt={activeConversation.name}
                        className="w-10 h-10 rounded-full object-cover"
                      />
                      <div>
                        <h3 className="font-medium text-gray-900">{activeConversation.name}</h3>
                        <p className="text-sm text-gray-500">
                          {activeConversation.type === "group"
                            ? `${activeConversation.participants.length} members`
                            : activeConversation.isOnline
                              ? "Online"
                              : "Last seen recently"}
                        </p>
                      </div>
                    </div>

                    <div className="flex items-center space-x-2">
                      <button className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                        <Phone className="w-5 h-5" />
                      </button>
                      <button className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                        <Video className="w-5 h-5" />
                      </button>
                      <button className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                        <MoreVertical className="w-5 h-5" />
                      </button>
                    </div>
                  </div>
                </div>

                {/* Messages */}
                <div className="flex-1 overflow-y-auto p-4 bg-gray-50">
                  {(messages[activeConversation.id] || []).map((message) => (
                    <MessageBubble key={message.id} message={message} isOwn={message.senderId === user.id} />
                  ))}
                  <div ref={messagesEndRef} />
                </div>

                {/* Message Input */}
                <div className="bg-white border-t border-gray-200 p-4">
                  <form onSubmit={handleSendMessage} className="flex items-center space-x-3">
                    <button
                      type="button"
                      className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100"
                    >
                      <Paperclip className="w-5 h-5" />
                    </button>

                    <div className="flex-1 relative">
                      <input
                        type="text"
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        placeholder="Type a message..."
                        className="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                      <button
                        type="button"
                        className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <Smile className="w-5 h-5" />
                      </button>
                    </div>

                    <button
                      type="submit"
                      disabled={!newMessage.trim()}
                      className="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <Send className="w-5 h-5" />
                    </button>
                  </form>
                </div>
              </>
            ) : (
              <div className="flex-1 flex items-center justify-center bg-gray-50">
                <div className="text-center">
                  <div className="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Send className="w-8 h-8 text-gray-400" />
                  </div>
                  <h3 className="text-lg font-medium text-gray-900 mb-2">Select a conversation</h3>
                  <p className="text-gray-500">Choose a conversation from the list to start messaging</p>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* New Chat Modal */}
      {showNewChatModal && <NewChatModal />}
    </div>
  )
}

export default MessagesView
