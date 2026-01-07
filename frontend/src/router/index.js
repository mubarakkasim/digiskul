import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  // ========================================
  // PUBLIC ROUTES
  // ========================================
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/auth/LoginPage.vue'),
    meta: { requiresAuth: false }
  },

  // ========================================
  // AUTHENTICATED ROUTES
  // ========================================
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: to => {
          const auth = useAuthStore()
          if (auth.user?.role === 'super_admin') return '/super-admin/dashboard'
          return '/dashboard'
        }
      },

      // --- COMMON DASHBOARD ---
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: {
          title: 'Dashboard',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'bursar', 'librarian', 'ict_officer']
        }
      },

      // ========================================
      // LEGACY SUPER ADMIN ROUTES (kept for backward compatibility)
      // ========================================
      {
        path: '/admin/schools',
        redirect: '/super-admin/schools'
      },
      {
        path: '/admin/licenses',
        redirect: '/super-admin/licenses'
      },
      {
        path: '/admin/system-settings',
        redirect: '/super-admin/settings'
      },
      {
        path: '/admin/activity-logs',
        redirect: '/super-admin/logs'
      },
      {
        path: '/admin/analytics',
        redirect: '/super-admin/analytics'
      },

      // ========================================
      // SCHOOL ADMIN ROUTES
      // ========================================
      {
        path: '/users',
        name: 'Users',
        component: () => import('../views/users/UsersPage.vue'),
        meta: {
          title: 'User Management',
          roles: ['super_admin', 'school_admin']
        }
      },
      {
        path: '/users/add',
        name: 'AddUser',
        component: () => import('../views/users/AddUserPage.vue'),
        meta: {
          title: 'Add User',
          roles: ['super_admin', 'school_admin']
        }
      },
      {
        path: '/users/:id/edit',
        name: 'EditUser',
        component: () => import('../views/users/EditUserPage.vue'),
        meta: {
          title: 'Edit User',
          roles: ['super_admin', 'school_admin']
        }
      },
      {
        path: '/teacher-assignments',
        name: 'TeacherAssignments',
        component: () => import('../views/assignments/TeacherAssignmentsPage.vue'),
        meta: {
          title: 'Teacher Assignments',
          roles: ['super_admin', 'school_admin']
        }
      },

      // ========================================
      // ACADEMIC ROUTES (Teachers & Admins)
      // ========================================
      {
        path: '/classes',
        name: 'Classes',
        component: () => import('../views/classes/ClassesPage.vue'),
        meta: {
          title: 'Classes',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/subjects',
        name: 'Subjects',
        component: () => import('../views/subjects/SubjectsPage.vue'),
        meta: {
          title: 'Subjects',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/students',
        name: 'Students',
        component: () => import('../views/students/StudentList.vue'),
        meta: {
          title: 'Students',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/students/add',
        name: 'AddStudent',
        component: () => import('../views/students/AddStudent.vue'),
        meta: {
          title: 'Add Student',
          roles: ['super_admin', 'school_admin']
        }
      },
      {
        path: '/students/:id/edit',
        name: 'EditStudent',
        component: () => import('../views/students/EditStudent.vue'),
        meta: {
          title: 'Edit Student',
          roles: ['super_admin', 'school_admin', 'class_teacher']
        }
      },
      {
        path: '/attendance',
        name: 'Attendance',
        component: () => import('../views/attendance/AttendancePage.vue'),
        meta: {
          title: 'Attendance',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/timetable',
        name: 'Timetable',
        component: () => import('../views/timetable/TimetablePage.vue'),
        meta: {
          title: 'Timetable',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/duties',
        name: 'Duties',
        component: () => import('../views/duties/DutyRoster.vue'),
        meta: {
          title: 'Duty Roster',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/grades',
        name: 'Grades',
        component: () => import('../views/grades/GradesPage.vue'),
        meta: {
          title: 'Grades',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher']
        }
      },
      {
        path: '/reports',
        name: 'Reports',
        component: () => import('../views/reports/ReportsPage.vue'),
        meta: {
          title: 'Reports',
          roles: ['super_admin', 'school_admin', 'class_teacher']
        }
      },
      {
        path: '/report-cards',
        name: 'ReportCards',
        component: () => import('../views/report-cards/ReportCardsPage.vue'),
        meta: {
          title: 'Report Cards',
          roles: ['super_admin', 'school_admin', 'class_teacher']
        }
      },
      {
        path: '/archive',
        name: 'Archive',
        component: () => import('../views/archive/ArchivePage.vue'),
        meta: {
          title: 'Archive',
          roles: ['super_admin', 'school_admin']
        }
      },

      // ========================================
      // FINANCIAL ROUTES (Bursar & Admins)
      // ========================================
      {
        path: '/fees',
        name: 'Fees',
        component: () => import('../views/fees/FeesPage.vue'),
        meta: {
          title: 'Fees Management',
          roles: ['super_admin', 'school_admin', 'bursar']
        }
      },
      {
        path: '/fees/debtors',
        name: 'Debtors',
        component: () => import('../views/fees/DebtorsPage.vue'),
        meta: {
          title: 'Debtors',
          roles: ['super_admin', 'school_admin', 'bursar']
        }
      },
      {
        path: '/payments',
        name: 'Payments',
        component: () => import('../views/payments/PaymentsPage.vue'),
        meta: {
          title: 'Payments',
          roles: ['super_admin', 'school_admin', 'bursar']
        }
      },

      // ========================================
      // ANNOUNCEMENTS
      // ========================================
      {
        path: '/announcements',
        name: 'Announcements',
        component: () => import('../views/announcements/AnnouncementsPage.vue'),
        meta: {
          title: 'Announcements',
          roles: ['super_admin', 'school_admin', 'teacher', 'class_teacher', 'student', 'parent', 'bursar']
        }
      },

      // ========================================
      // SETTINGS
      // ========================================
      {
        path: '/settings',
        name: 'Settings',
        component: () => import('../views/settings/SettingsPage.vue'),
        meta: {
          title: 'Settings',
          roles: ['super_admin', 'school_admin']
        }
      },

      // ========================================
      // STUDENT PORTAL ROUTES
      // ========================================
      {
        path: '/student/dashboard',
        name: 'StudentDashboard',
        component: () => import('../views/student/StudentDashboard.vue'),
        meta: {
          title: 'My Dashboard',
          roles: ['student']
        }
      },
      {
        path: '/student/profile',
        name: 'StudentProfile',
        component: () => import('../views/student/StudentProfile.vue'),
        meta: {
          title: 'My Profile',
          roles: ['student']
        }
      },
      {
        path: '/student/timetable',
        name: 'StudentTimetable',
        component: () => import('../views/student/StudentTimetable.vue'),
        meta: {
          title: 'My Timetable',
          roles: ['student']
        }
      },
      {
        path: '/student/grades',
        name: 'StudentGrades',
        component: () => import('../views/student/StudentGrades.vue'),
        meta: {
          title: 'My Grades',
          roles: ['student']
        }
      },
      {
        path: '/student/attendance',
        name: 'StudentAttendance',
        component: () => import('../views/student/StudentAttendance.vue'),
        meta: {
          title: 'My Attendance',
          roles: ['student']
        }
      },

      // ========================================
      // PARENT PORTAL ROUTES
      // ========================================
      {
        path: '/parent/dashboard',
        name: 'ParentDashboard',
        component: () => import('../views/parent/ParentDashboard.vue'),
        meta: {
          title: 'Dashboard',
          roles: ['parent']
        }
      },
      {
        path: '/parent/children',
        name: 'ParentChildren',
        component: () => import('../views/parent/ChildrenPage.vue'),
        meta: {
          title: 'My Children',
          roles: ['parent']
        }
      },
      {
        path: '/parent/child/:id',
        name: 'ChildDetails',
        component: () => import('../views/parent/ChildDetailsPage.vue'),
        meta: {
          title: 'Child Details',
          roles: ['parent']
        }
      },

      // ========================================
      // BURSAR DASHBOARD
      // ========================================
      {
        path: '/bursar/dashboard',
        name: 'BursarDashboard',
        component: () => import('../views/bursar/BursarDashboard.vue'),
        meta: {
          title: 'Bursar Dashboard',
          roles: ['bursar']
        }
      },
    ]
  },

  // ========================================
  // SUPER ADMIN PANEL (Dedicated Layout)
  // ========================================
  {
    path: '/super-admin',
    component: () => import('../layouts/SuperAdminLayout.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'] },
    children: [
      {
        path: '',
        redirect: '/super-admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'SuperAdminDashboard',
        component: () => import('../views/super-admin/Dashboard.vue'),
        meta: { title: 'Super Admin Dashboard', roles: ['super_admin'] }
      },
      {
        path: 'schools',
        name: 'SuperAdminSchools',
        component: () => import('../views/super-admin/Schools.vue'),
        meta: { title: 'School Management', roles: ['super_admin'] }
      },
      {
        path: 'licenses',
        name: 'SuperAdminLicenses',
        component: () => import('../views/super-admin/Licenses.vue'),
        meta: { title: 'License Management', roles: ['super_admin'] }
      },
      {
        path: 'users',
        name: 'SuperAdminUsers',
        component: () => import('../views/super-admin/Users.vue'),
        meta: { title: 'Global User Directory', roles: ['super_admin'] }
      },
      {
        path: 'settings',
        name: 'SuperAdminSettings',
        component: () => import('../views/super-admin/Settings.vue'),
        meta: { title: 'Platform Settings', roles: ['super_admin'] }
      },
      {
        path: 'health',
        name: 'SuperAdminHealth',
        component: () => import('../views/super-admin/Health.vue'),
        meta: { title: 'System Health', roles: ['super_admin'] }
      },
      {
        path: 'logs',
        name: 'SuperAdminLogs',
        component: () => import('../views/super-admin/Logs.vue'),
        meta: { title: 'Activity Logs', roles: ['super_admin'] }
      },
      {
        path: 'backups',
        name: 'SuperAdminBackups',
        component: () => import('../views/super-admin/Backups.vue'),
        meta: { title: 'Backups', roles: ['super_admin'] }
      },
      {
        path: 'announcements',
        name: 'SuperAdminAnnouncements',
        component: () => import('../views/super-admin/Announcements.vue'),
        meta: { title: 'System Announcements', roles: ['super_admin'] }
      },
      {
        path: 'profile',
        name: 'SuperAdminProfile',
        component: () => import('../views/super-admin/Profile.vue'),
        meta: { title: 'My Profile', roles: ['super_admin'] }
      },
      {
        path: 'analytics',
        name: 'SuperAdminAnalytics',
        component: () => import('../views/super-admin/Dashboard.vue'),
        meta: { title: 'Platform Analytics', roles: ['super_admin'] }
      }
    ]
  },

  // 404 Not Found
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check if route requires authentication
  if (to.meta.requiresAuth !== false) {
    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      // Try to initialize user from localStorage
      const storedUser = localStorage.getItem('user')
      const storedToken = localStorage.getItem('auth_token')

      if (storedToken && storedUser) {
        try {
          authStore.setToken(storedToken)
          authStore.setUser(JSON.parse(storedUser))
        } catch (e) {
          console.error('Failed to restore auth state:', e)
          next('/login')
          return
        }
      } else {
        next('/login')
        return
      }
    }

    // Check role-based access
    if (to.meta.roles && Array.isArray(to.meta.roles)) {
      const userRole = authStore.user?.role

      // Super admin always has access
      if (userRole === 'super_admin') {
        next()
        return
      }

      if (!to.meta.roles.includes(userRole)) {
        // Redirect to appropriate dashboard based on role
        const dashboardRoute = authStore.getDashboardRoute()
        if (dashboardRoute && dashboardRoute !== to.path) {
          next(dashboardRoute)
        } else {
          next('/login')
        }
        return
      }
    }

    next()
  } else {
    // Route doesn't require auth (like login page)
    if (to.path === '/login' && authStore.isAuthenticated) {
      // If already logged in, redirect to appropriate dashboard
      const dashboardRoute = authStore.getDashboardRoute()
      next(dashboardRoute)
    } else {
      next()
    }
  }
})

// Update page title
router.afterEach((to) => {
  const title = to.meta.title ? `${to.meta.title} | DIGISKUL` : 'DIGISKUL'
  document.title = title
})

export default router
