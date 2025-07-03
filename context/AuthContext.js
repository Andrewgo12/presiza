"use client"

import { createContext, useContext, useState, useEffect } from "react"
import { useIsClient, useLocalStorage } from "../hooks/use-client"
import { authAPI } from "../services/api"

const AuthContext = createContext()

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider")
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const isClient = useIsClient()
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [storedUser, setStoredUser] = useLocalStorage("user", null)
  const [storedToken, setStoredToken] = useLocalStorage("token", null)

  useEffect(() => {
    // Check for existing session on mount - only on client side
    if (isClient && storedUser && storedToken) {
      try {
        setUser(storedUser)
        setIsAuthenticated(true)
      } catch (error) {
        console.error("Error parsing saved user data:", error)
        setStoredUser(null)
        setStoredToken(null)
      }
    }
    setLoading(false)
  }, [isClient, storedUser, storedToken, setStoredUser, setStoredToken])

  const login = async (email, password) => {
    try {
      setLoading(true)

      // Call real API
      const response = await authAPI.login(email, password)

      if (response.user && response.tokens) {
        const { user, tokens } = response

        // Save tokens and user data
        setStoredUser(user)
        setStoredToken(tokens.accessToken)

        // Store refresh token separately if needed
        if (typeof window !== 'undefined') {
          localStorage.setItem('refreshToken', tokens.refreshToken)
        }

        setUser(user)
        setIsAuthenticated(true)

        return {
          success: true,
          user,
          token: tokens.accessToken,
        }
      } else {
        return {
          success: false,
          error: "Invalid response from server",
        }
      }
    } catch (error) {
      console.error("Login error:", error)
      return {
        success: false,
        error: "An error occurred during login",
      }
    } finally {
      setLoading(false)
    }
  }

  const logout = () => {
    setStoredUser(null)
    setStoredToken(null)
    setUser(null)
    setIsAuthenticated(false)
  }

  const updateUser = (userData) => {
    const updatedUser = { ...user, ...userData }
    setUser(updatedUser)
    setStoredUser(updatedUser)
  }

  const isAdmin = user?.role === "admin"

  const value = {
    user,
    loading,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    updateUser,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
