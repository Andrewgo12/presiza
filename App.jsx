"use client"

import { BrowserRouter } from "react-router-dom"
import { AuthProvider } from "./context/AuthContext"
import AppRoutes from "./routes"
import NotificationSystem from "./components/NotificationSystem"
import { ClientOnly } from "./hooks/use-client"
import "./App.css"

function App() {
  return (
    <ClientOnly fallback={
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto mb-4"></div>
          <p className="text-gray-600">Preparando aplicación...</p>
        </div>
      </div>
    }>
      <BrowserRouter>
        <AuthProvider>
          <div className="App">
            <AppRoutes />
            <NotificationSystem />
          </div>
        </AuthProvider>
      </BrowserRouter>
    </ClientOnly>
  )
}

export default App
