import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/auth/LoginPage.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard'
      },
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: '/students',
        name: 'Students',
        component: () => import('../views/students/StudentList.vue')
      },
      {
        path: '/students/add',
        name: 'AddStudent',
        component: () => import('../views/students/AddStudent.vue')
      },
      {
        path: '/students/:id/edit',
        name: 'EditStudent',
        component: () => import('../views/students/EditStudent.vue')
      },
      {
        path: '/attendance',
        name: 'Attendance',
        component: () => import('../views/attendance/AttendancePage.vue')
      },
      {
        path: '/timetable',
        name: 'Timetable',
        component: () => import('../views/timetable/TimetablePage.vue')
      },
      {
        path: '/duties',
        name: 'Duties',
        component: () => import('../views/duties/DutyRoster.vue')
      },
      {
        path: '/grades',
        name: 'Grades',
        component: () => import('../views/grades/GradesPage.vue')
      },
      {
        path: '/reports',
        name: 'Reports',
        component: () => import('../views/reports/ReportsPage.vue')
      },
      {
        path: '/report-cards',
        name: 'ReportCards',
        component: () => import('../views/report-cards/ReportCardsPage.vue')
      },
      {
        path: '/archive',
        name: 'Archive',
        component: () => import('../views/archive/ArchivePage.vue')
      },
      {
        path: '/fees',
        name: 'Fees',
        component: () => import('../views/fees/FeesPage.vue')
      },
      {
        path: '/fees/debtors',
        name: 'Debtors',
        component: () => import('../views/fees/DebtorsPage.vue')
      },
      {
        path: '/payments',
        name: 'Payments',
        component: () => import('../views/payments/PaymentsPage.vue')
      },
      {
        path: '/settings',
        name: 'Settings',
        component: () => import('../views/settings/SettingsPage.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      // Try to initialize user from localStorage
      const storedUser = localStorage.getItem('user')
      const storedToken = localStorage.getItem('auth_token')

      if (storedToken && storedUser) {
        try {
          authStore.setToken(storedToken)
          authStore.setUser(JSON.parse(storedUser))
          // Allow navigation if token exists
          next()
        } catch (e) {
          console.error('Failed to restore auth state:', e)
          // If parsing fails, redirect to login
          next('/login')
        }
      } else {
        // No token or user, redirect to login
        next('/login')
      }
    } else {
      // User is authenticated, allow navigation
      next()
    }
  } else {
    // Route doesn't require auth (like login page)
    if (to.path === '/login' && authStore.isAuthenticated) {
      // If already logged in, redirect to dashboard
      next('/dashboard')
    } else {
      // Allow navigation to public routes
      next()
    }
  }
})

export default router

