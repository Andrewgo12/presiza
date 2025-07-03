"use client"

import { useState, useEffect } from "react"

/**
 * Hook to detect if the component is running on the client side
 * Helps prevent hydration mismatches by ensuring consistent rendering
 * between server and client
 */
export function useIsClient() {
  const [isClient, setIsClient] = useState(false)

  useEffect(() => {
    setIsClient(true)
  }, [])

  return isClient
}

/**
 * Hook to safely access localStorage only on the client side
 * Returns null during SSR to prevent hydration mismatches
 */
export function useLocalStorage(key, defaultValue = null) {
  const isClient = useIsClient()
  const [value, setValue] = useState(defaultValue)

  useEffect(() => {
    if (isClient && typeof window !== 'undefined') {
      try {
        const item = localStorage.getItem(key)
        setValue(item ? JSON.parse(item) : defaultValue)
      } catch (error) {
        console.error(`Error reading localStorage key "${key}":`, error)
        setValue(defaultValue)
      }
    }
  }, [isClient, key, defaultValue])

  const setStoredValue = (newValue) => {
    try {
      setValue(newValue)
      if (isClient && typeof window !== 'undefined') {
        if (newValue === null) {
          localStorage.removeItem(key)
        } else {
          localStorage.setItem(key, JSON.stringify(newValue))
        }
      }
    } catch (error) {
      console.error(`Error setting localStorage key "${key}":`, error)
    }
  }

  return [value, setStoredValue]
}

/**
 * Component wrapper that only renders children on the client side
 * Prevents hydration mismatches for components that use browser APIs
 */
export function ClientOnly({ children, fallback = null }) {
  const isClient = useIsClient()
  
  if (!isClient) {
    return fallback
  }
  
  return children
}
