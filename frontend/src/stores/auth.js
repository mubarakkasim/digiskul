import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/v1'

// Role hierarchy for permission checking
const ROLE_HIERARCHY = {
  super_admin: 100,
  school_admin: 80,
  class_teacher: 60,
  teacher: 50,
  bursar: 45,
  librarian: 40,
  ict_officer: 35,
  parent: 20,
  student: 10,
}

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  const toast = useToast()

  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)

  // Computed properties
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRole = computed(() => user.value?.role || null)
  const userName = computed(() => user.value?.name || '')
  const userSchool = computed(() => user.value?.school || null)

  // Role checks
  const isSuperAdmin = computed(() => userRole.value === 'super_admin')
  const isSchoolAdmin = computed(() => userRole.value === 'school_admin')
  const isTeacher = computed(() => ['teacher', 'class_teacher'].includes(userRole.value))
  const isClassTeacher = computed(() => userRole.value === 'class_teacher')
  const isStudent = computed(() => userRole.value === 'student')
  const isParent = computed(() => userRole.value === 'parent')
  const isBursar = computed(() => userRole.value === 'bursar')
  const isStaff = computed(() => ['teacher', 'class_teacher', 'bursar', 'librarian', 'ict_officer'].includes(userRole.value))

  // Permission checking
  const hasRole = (roles) => {
    if (!userRole.value) return false
    if (Array.isArray(roles)) {
      return roles.includes(userRole.value)
    }
    return userRole.value === roles
  }

  const hasMinRole = (minRole) => {
    if (!userRole.value) return false
    const userLevel = ROLE_HIERARCHY[userRole.value] || 0
    const minLevel = ROLE_HIERARCHY[minRole] || 0
    return userLevel >= minLevel
  }

  const canAccess = (feature) => {
    if (!userRole.value) return false

    // Super admin can access everything
    if (isSuperAdmin.value) return true

    // Feature-based access control
    const accessMap = {
      // School management
      'schools': ['super_admin'],
      'system_settings': ['super_admin'],

      // User management
      'users': ['super_admin', 'school_admin'],
      'teacher_assignments': ['super_admin', 'school_admin'],

      // Class & Subject management
      'classes_manage': ['super_admin', 'school_admin'],
      'subjects_manage': ['super_admin', 'school_admin'],

      // Student management
      'students_manage': ['super_admin', 'school_admin'],
      'students_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher'],

      // Attendance
      'attendance_mark': ['super_admin', 'school_admin', 'teacher', 'class_teacher'],
      'attendance_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'student', 'parent'],

      // Grades
      'grades_record': ['super_admin', 'school_admin', 'teacher', 'class_teacher'],
      'grades_approve': ['super_admin', 'school_admin', 'class_teacher'],
      'grades_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'student', 'parent'],

      // Report cards
      'report_cards_generate': ['super_admin', 'school_admin', 'class_teacher'],
      'report_cards_approve': ['super_admin', 'school_admin'],
      'report_cards_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'student', 'parent'],

      // Timetable
      'timetable_manage': ['super_admin', 'school_admin'],
      'timetable_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'student', 'parent'],

      // Duties
      'duties_manage': ['super_admin', 'school_admin'],
      'duties_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher'],

      // Fees & Payments
      'fees_manage': ['super_admin', 'school_admin', 'bursar'],
      'fees_view': ['super_admin', 'school_admin', 'bursar', 'student', 'parent'],
      'payments_manage': ['super_admin', 'school_admin', 'bursar'],
      'payments_view': ['super_admin', 'school_admin', 'bursar', 'student', 'parent'],

      // Reports
      'reports_generate': ['super_admin', 'school_admin', 'class_teacher'],
      'reports_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher'],

      // Archive
      'archive_manage': ['super_admin', 'school_admin'],
      'archive_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher'],

      // Settings
      'settings_manage': ['super_admin', 'school_admin'],

      // Announcements
      'announcements_manage': ['super_admin', 'school_admin'],
      'announcements_view': ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'student', 'parent', 'bursar'],
    }

    const allowedRoles = accessMap[feature]
    if (!allowedRoles) return false

    return allowedRoles.includes(userRole.value)
  }

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

  // Get dashboard route based on role
  const getDashboardRoute = () => {
    const role = userRole.value
    switch (role) {
      case 'super_admin':
        return '/super-admin/dashboard'
      case 'school_admin':
        return '/dashboard'
      case 'teacher':
      case 'class_teacher':
        return '/dashboard'
      case 'student':
        return '/student/dashboard'
      case 'parent':
        return '/parent/dashboard'
      case 'bursar':
        return '/bursar/dashboard'
      case 'librarian':
        return '/librarian/dashboard'
      case 'ict_officer':
        return '/dashboard'
      default:
        return '/dashboard'
    }
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

      toast.success(`Welcome back, ${userData.name}!`)

      // Redirect based on role
      const dashboardRoute = getDashboardRoute()
      router.push(dashboardRoute)

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
      localStorage.removeItem('user')
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
    userRole,
    userName,
    userSchool,

    // Role checks
    isSuperAdmin,
    isSchoolAdmin,
    isTeacher,
    isClassTeacher,
    isStudent,
    isParent,
    isBursar,
    isStaff,

    // Permission methods
    hasRole,
    hasMinRole,
    canAccess,
    getDashboardRoute,

    // Actions
    login,
    logout,
    fetchUser,
    setToken,
    setUser
  }
})
