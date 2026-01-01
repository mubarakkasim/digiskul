import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/v1'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  const toast = useToast()
  
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Set auth token
  const setToken = (newToken) => {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
      axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else {
      localStorage.removeItem('auth_token')
      delete axios.defaults.headers.common['Authorization']
    }
  }

  // Set user data
  const setUser = (userData) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
  }

  // Initialize axios defaults
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  // Login
  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await axios.post(`${API_BASE_URL}/auth/login`, credentials)
      const { token: authToken, user: userData } = response.data.data
      
      setToken(authToken)
      setUser(userData)
      
      toast.success('Login successful!')
      
      // Redirect based on role
      const role = userData.role
      if (role === 'super_admin') {
        router.push('/dashboard')
      } else if (role === 'school_admin') {
        router.push('/dashboard')
      } else if (role === 'teacher') {
        router.push('/dashboard')
      } else if (role === 'bursar') {
        router.push('/fees')
      } else if (role === 'parent') {
        router.push('/parent/dashboard')
      } else {
        router.push('/dashboard')
      }
      
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Login failed. Please try again.'
      toast.error(message)
      return { success: false, error: message }
    } finally {
      loading.value = false
    }
  }

  // Logout
  const logout = async () => {
    try {
      await axios.post(`${API_BASE_URL}/auth/logout`)
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      setToken(null)
      setUser(null)
      router.push('/login')
      toast.info('Logged out successfully')
    }
  }

  // Fetch current user
  const fetchUser = async () => {
    if (!token.value) return
    
    try {
      const response = await axios.get(`${API_BASE_URL}/auth/me`)
      setUser(response.data.data)
      return response.data.data
    } catch (error) {
      if (error.response?.status === 401) {
        logout()
      }
      throw error
    }
  }

  // Initialize user from localStorage
  const storedUser = localStorage.getItem('user')
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser)
    } catch (e) {
      console.error('Failed to parse stored user:', e)
    }
  }

  // Initialize token if exists
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    login,
    logout,
    fetchUser,
    setToken,
    setUser
  }
})

