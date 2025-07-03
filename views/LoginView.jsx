"use client"

import { useState } from "react"
import { useAuth } from "../context/AuthContext"
import { useNavigate } from "react-router-dom"
import { Eye, EyeOff, Lock, Mail } from "lucide-react"

const LoginView = () => {
  const [formData, setFormData] = useState({
    email: "",
    password: "",
  })
  const [showPassword, setShowPassword] = useState(false)
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const { login } = useAuth()
  const navigate = useNavigate()

  // Real-time validation
  const validateField = (name, value) => {
    const newErrors = { ...errors }

    switch (name) {
      case "email":
        if (!value) {
          newErrors.email = "Email is required"
        } else if (!/\S+@\S+\.\S+/.test(value)) {
          newErrors.email = "Email is invalid"
        } else {
          delete newErrors.email
        }
        break
      case "password":
        if (!value) {
          newErrors.password = "Password is required"
        } else if (value.length < 6) {
          newErrors.password = "Password must be at least 6 characters"
        } else {
          delete newErrors.password
        }
        break
      default:
        break
    }

    setErrors(newErrors)
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prev) => ({ ...prev, [name]: value }))
  }

  const handleBlur = (e) => {
    const { name, value } = e.target
    validateField(name, value)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    // Validate all fields
    Object.keys(formData).forEach((key) => {
      validateField(key, formData[key])
    })

    if (Object.keys(errors).length > 0) {
      return
    }

    setLoading(true)

    try {
      const result = await login(formData.email, formData.password)

      if (result.success) {
        // Redirect based on user role
        if (result.user.role === "admin") {
          navigate("/admin/tasks")
        } else {
          navigate("/dashboard")
        }
      } else {
        setErrors({ submit: result.error })
      }
    } catch (error) {
      setErrors({ submit: "Login failed. Please try again." })
    } finally {
      setLoading(false)
    }
  }

  const isFormValid = formData.email && formData.password && Object.keys(errors).length === 0

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
      <div className="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
        <div className="text-center mb-8">
          <div className="mx-auto w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-4">
            <Lock className="w-8 h-8 text-white" />
          </div>
          <h1 className="text-2xl font-bold text-gray-900">Welcome Back</h1>
          <p className="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Email Field */}
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <div className="relative">
              <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                onBlur={handleBlur}
                className={`w-full pl-10 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                  errors.email ? "border-red-500" : "border-gray-300"
                }`}
                placeholder="Enter your email"
              />
            </div>
            {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
          </div>

          {/* Password Field */}
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <div className="relative">
              <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type={showPassword ? "text" : "password"}
                id="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                onBlur={handleBlur}
                className={`w-full pl-10 pr-12 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                  errors.password ? "border-red-500" : "border-gray-300"
                }`}
                placeholder="Enter your password"
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
              </button>
            </div>
            {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
          </div>

          {/* Submit Error */}
          {errors.submit && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-3">
              <p className="text-sm text-red-600">{errors.submit}</p>
            </div>
          )}

          {/* Submit Button */}
          <button
            type="submit"
            disabled={!isFormValid || loading}
            className={`w-full py-3 px-4 rounded-lg font-medium transition-colors ${
              isFormValid && !loading
                ? "bg-blue-600 hover:bg-blue-700 text-white"
                : "bg-gray-300 text-gray-500 cursor-not-allowed"
            }`}
          >
            {loading ? "Signing in..." : "Sign In"}
          </button>
        </form>

        {/* Demo Credentials */}
        <div className="mt-8 p-4 bg-gray-50 rounded-lg">
          <h3 className="text-sm font-medium text-gray-700 mb-2">Demo Credentials:</h3>
          <div className="text-xs text-gray-600 space-y-1">
            <p>
              <strong>Admin:</strong> admin@company.com / admin123
            </p>
            <p>
              <strong>User:</strong> user@company.com / user123
            </p>
          </div>
        </div>

        {/* Forgot Password Link */}
        <div className="mt-6 text-center">
          <button className="text-sm text-blue-600 hover:text-blue-800">Forgot your password?</button>
        </div>
      </div>
    </div>
  )
}

export default LoginView
