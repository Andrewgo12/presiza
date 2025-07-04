"use client"

import { useState, useEffect, useRef } from "react"
import { useAuth } from "../context/AuthContext"
import Header from "../components/Header"
import Sidebar from "../components/Sidebar"
import { messagesAPI, usersAPI } from "../services/api"
import { Search, Send, Paperclip, Smile, Phone, Video, MoreVertical, Circle, CheckCircle2, Users, MessageCircle, AlertCircle } from "lucide-react"

const MessagesView = () => {
  const { user } = useAuth()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [conversations, setConversations] = useState([])
  const [activeConversation, setActiveConversation] = useState(null)
  const [messages, setMessages] = useState({})
  const [newMessage, setNewMessage] = useState("")
  const [searchTerm, setSearchTerm] = useState("")
  const [showNewChatModal, setShowNewChatModal] = useState(false)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [sendingMessage, setSendingMessage] = useState(false)
  const messagesEndRef = useRef(null)

  useEffect(() => {
    const loadMessages = async () => {
      try {
        setLoading(true)
        setError(null)

        const response = await messagesAPI.getMessages({
          page: 1,
          limit: 50
        })

        // Procesar mensajes para el formato esperado
        const processedConversations = response.messages || []
        setConversations(processedConversations)

        // Seleccionar primera conversación si existe
        if (processedConversations.length > 0) {
          setActiveConversation(processedConversations[0])
        }

      } catch (err) {
        console.error('Error loading messages:', err)
        setError('Error cargando mensajes')
        // Fallback a datos vacíos
        setConversations([])
        setMessages({})
      } finally {
        setLoading(false)
      }
    }

    loadMessages()
  }, [])

  // Función para enviar mensaje
  const handleSendMessage = async () => {
    if (!newMessage.trim() || !activeConversation || sendingMessage) return

    try {
      setSendingMessage(true)

      const messageData = {
        content: newMessage.trim(),
        recipientId: activeConversation.id
      }

      const response = await messagesAPI.sendMessage(messageData)

      // Actualizar mensajes localmente
      setNewMessage("")

      // Aquí podrías actualizar la lista de mensajes
      // dependiendo de cómo esté estructurada tu API

    } catch (err) {
      console.error('Error sending message:', err)
      setError('Error enviando mensaje')
    } finally {
      setSendingMessage(false)
    }
  }

  // Función para scroll automático
  useEffect(() => {
    scrollToBottom()
  }, [messages, activeConversation])

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" })
  }

  const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString('es-ES', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  const formatDate = (timestamp) => {
    return new Date(timestamp).toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }

  // Componentes auxiliares
  const ConversationItem = ({ conversation }) => (
    <div
      onClick={() => setActiveConversation(conversation)}
      className={`flex items-center p-4 cursor-pointer hover:bg-gray-50 ${activeConversation?.id === conversation.id ? "bg-blue-50 border-r-2 border-blue-500" : ""
        }`}
    >
      <div className="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center mr-3">
        <Users className="w-6 h-6 text-gray-600" />
      </div>
      <div className="flex-1 min-w-0">
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-medium text-gray-900 truncate">{conversation.name}</h3>
          <span className="text-xs text-gray-500">{formatTime(conversation.timestamp)}</span>
        </div>
        <p className="text-sm text-gray-600 truncate">{conversation.lastMessage || 'Nueva conversación'}</p>
      </div>
    </div>
  )

  const MessageBubble = ({ message }) => {
    const isOwn = message.sender?.id === user._id

    return (
      <div className={`flex ${isOwn ? "justify-end" : "justify-start"} mb-4`}>
        <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${isOwn ? "bg-blue-500 text-white" : "bg-white border border-gray-200"
          }`}>
          {!isOwn && (
            <p className="text-xs font-medium mb-1 text-gray-600">{message.sender?.name}</p>
          )}
          <p className="text-sm">{message.content}</p>
          <div className="flex items-center justify-end mt-1">
            <span className={`text-xs ${isOwn ? "text-blue-100" : "text-gray-500"}`}>
              {formatTime(message.timestamp)}
            </span>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Header onMenuClick={() => setSidebarOpen(!sidebarOpen)} />

      <div className="flex">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

        <main className="flex-1 p-6">
          <div className="max-w-7xl mx-auto">
            <div className="mb-8">
              <h1 className="text-3xl font-bold text-gray-900">Mensajes</h1>
              <p className="text-gray-600 mt-2">Comunicación en tiempo real</p>
            </div>

            {loading ? (
              <div className="text-center py-12">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto mb-4"></div>
                <p className="text-gray-600">Cargando mensajes...</p>
              </div>
            ) : error ? (
              <div className="text-center py-12">
                <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">Error cargando mensajes</h3>
                <p className="text-gray-500 mb-4">{error}</p>
                <button
                  onClick={() => window.location.reload()}
                  className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                >
                  Reintentar
                </button>
              </div>
            ) : (
              <div className="flex h-full">
                {/* Lista de Conversaciones */}
                <div className="w-80 bg-white border-r border-gray-200 flex flex-col">
                  <div className="p-4 border-b border-gray-200">
                    <div className="flex items-center justify-between mb-4">
                      <h2 className="text-lg font-semibold text-gray-900">Mensajes</h2>
                      <button className="p-2 text-blue-600 hover:bg-blue-50 rounded-full">
                        <Send className="w-5 h-5" />
                      </button>
                    </div>
                    <div className="relative">
                      <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                      <input
                        type="text"
                        placeholder="Buscar conversaciones..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>
                  </div>

                  <div className="flex-1 overflow-y-auto">
                    {conversations.length === 0 ? (
                      <div className="p-4 text-center text-gray-500">
                        No hay conversaciones
                      </div>
                    ) : (
                      conversations.map((conversation) => (
                        <ConversationItem key={conversation.id} conversation={conversation} />
                      ))
                    )}
                  </div>
                </div>

                {/* Área de Chat */}
                <div className="flex-1 flex flex-col">
                  {activeConversation ? (
                    <>
                      {/* Header del Chat */}
                      <div className="bg-white border-b border-gray-200 p-4">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                              <Users className="w-5 h-5 text-gray-600" />
                            </div>
                            <div>
                              <h3 className="font-medium text-gray-900">{activeConversation.name}</h3>
                              <p className="text-sm text-gray-500">En línea</p>
                            </div>
                          </div>
                          <div className="flex items-center space-x-2">
                            <button className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                              <Phone className="w-5 h-5" />
                            </button>
                            <button className="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                              <Video className="w-5 h-5" />
                            </button>
                          </div>
                        </div>
                      </div>

                      {/* Mensajes */}
                      <div className="flex-1 overflow-y-auto p-4 bg-gray-50">
                        {messages.length === 0 ? (
                          <div className="text-center text-gray-500 mt-8">
                            No hay mensajes en esta conversación
                          </div>
                        ) : (
                          messages.map((message) => (
                            <MessageBubble key={message.id} message={message} />
                          ))
                        )}
                        <div ref={messagesEndRef} />
                      </div>

                      {/* Input de Mensaje */}
                      <div className="bg-white border-t border-gray-200 p-4">
                        <form onSubmit={(e) => { e.preventDefault(); handleSendMessage(); }} className="flex items-center space-x-3">
                          <div className="flex-1 relative">
                            <input
                              type="text"
                              value={newMessage}
                              onChange={(e) => setNewMessage(e.target.value)}
                              placeholder="Escribe un mensaje..."
                              className="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              disabled={sendingMessage}
                            />
                          </div>
                          <button
                            type="submit"
                            disabled={!newMessage.trim() || sendingMessage}
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
                          <MessageCircle className="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">Selecciona una conversación</h3>
                        <p className="text-gray-500">Elige una conversación para comenzar a chatear</p>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  )
}

export default MessagesView
