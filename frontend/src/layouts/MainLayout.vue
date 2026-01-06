<template>
  <div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white flex flex-col">
      <!-- Logo -->
      <div class="p-6 border-b border-blue-800">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div>
            <h1 class="text-lg font-bold">DIGISKUL</h1>
            <p class="text-xs text-blue-200">{{ roleDisplayName }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4">
        <!-- Main Menu -->
        <div class="mb-6">
          <p class="text-xs uppercase tracking-wider text-blue-300 px-4 mb-2">Main Menu</p>
          <router-link
            v-for="item in mainMenuItems"
            :key="item.path"
            :to="item.path"
            class="sidebar-link"
            :class="{ active: $route.path === item.path }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </router-link>
        </div>

        <!-- Academic Section (Teachers & Admins) -->
        <div v-if="showAcademicSection" class="border-t border-blue-800 pt-4 mb-6">
          <p class="text-xs uppercase tracking-wider text-blue-300 px-4 mb-2">Academic</p>
          <router-link
            v-for="item in academicMenuItems"
            :key="item.path"
            :to="item.path"
            class="sidebar-link"
            :class="{ active: $route.path === item.path }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </router-link>
        </div>

        <!-- Financial Section (Bursar & Admins) -->
        <div v-if="showFinancialSection" class="border-t border-blue-800 pt-4 mb-6">
          <p class="text-xs uppercase tracking-wider text-blue-300 px-4 mb-2">Financial</p>
          <router-link
            v-for="item in financialMenuItems"
            :key="item.path"
            :to="item.path"
            class="sidebar-link"
            :class="{ active: $route.path === item.path }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </router-link>
        </div>

        <!-- Admin Section -->
        <div v-if="showAdminSection" class="border-t border-blue-800 pt-4">
          <p class="text-xs uppercase tracking-wider text-blue-300 px-4 mb-2">Administration</p>
          <router-link
            v-for="item in adminMenuItems"
            :key="item.path"
            :to="item.path"
            class="sidebar-link"
            :class="{ active: $route.path === item.path }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </router-link>
        </div>

        <!-- Super Admin Section -->
        <div v-if="authStore.isSuperAdmin" class="border-t border-blue-800 pt-4">
          <p class="text-xs uppercase tracking-wider text-yellow-300 px-4 mb-2">System</p>
          <router-link
            v-for="item in superAdminMenuItems"
            :key="item.path"
            :to="item.path"
            class="sidebar-link bg-blue-800/50"
            :class="{ 'active bg-yellow-500/20': $route.path === item.path }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </router-link>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-4 border-t border-blue-800">
        <p class="text-xs text-blue-300">Current Session</p>
        <p class="text-sm font-semibold">{{ currentSession }}</p>
        <p v-if="authStore.userSchool" class="text-xs text-blue-300 mt-1">
          {{ authStore.userSchool.name }}
        </p>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <button
          @click="toggleSidebar"
          class="lg:hidden p-2 rounded-lg hover:bg-gray-100"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        
        <div class="flex items-center gap-4">
          <!-- Role Badge -->
          <span class="px-3 py-1 text-xs font-semibold rounded-full" :class="roleBadgeClass">
            {{ roleDisplayName }}
          </span>
          
          <!-- Sync Status -->
          <div v-if="syncStatus" class="flex items-center gap-2">
            <div
              class="w-2 h-2 rounded-full"
              :class="syncStatus.isOnline ? 'bg-green-500' : 'bg-red-500'"
            ></div>
            <span class="text-xs text-gray-500">
              {{ syncStatus.pending }} pending
            </span>
          </div>

          <!-- Notifications (placeholder) -->
          <button class="relative p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>

          <!-- User Menu -->
          <div class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100"
            >
              <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                {{ userInitials }}
              </div>
              <span class="text-sm font-medium">{{ authStore.user?.name }}</span>
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
            >
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
              </div>
              <router-link
                to="/settings"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="showUserMenu = false"
              >
                <span class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Settings
                </span>
              </router-link>
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              >
                <span class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
                </span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useSyncStore } from '../stores/sync'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const syncStore = useSyncStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const showUserMenu = ref(false)
const syncStatus = ref(null)

const currentSession = ref('2024/2025 First Term')

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
})

const roleDisplayName = computed(() => {
  const role = authStore.user?.role
  const roleNames = {
    'super_admin': 'Super Admin',
    'school_admin': 'School Admin',
    'class_teacher': 'Class Teacher',
    'teacher': 'Teacher',
    'student': 'Student',
    'parent': 'Parent/Guardian',
    'bursar': 'Bursar',
    'librarian': 'Librarian',
    'ict_officer': 'ICT Officer',
  }
  return roleNames[role] || 'User'
})

const roleBadgeClass = computed(() => {
  const role = authStore.user?.role
  const classes = {
    'super_admin': 'bg-purple-100 text-purple-800',
    'school_admin': 'bg-blue-100 text-blue-800',
    'class_teacher': 'bg-green-100 text-green-800',
    'teacher': 'bg-teal-100 text-teal-800',
    'student': 'bg-yellow-100 text-yellow-800',
    'parent': 'bg-orange-100 text-orange-800',
    'bursar': 'bg-pink-100 text-pink-800',
    'librarian': 'bg-indigo-100 text-indigo-800',
    'ict_officer': 'bg-gray-100 text-gray-800',
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
})

// Show sections based on role
const showAcademicSection = computed(() => {
  return authStore.hasRole(['super_admin', 'school_admin', 'teacher', 'class_teacher'])
})

const showFinancialSection = computed(() => {
  return authStore.hasRole(['super_admin', 'school_admin', 'bursar'])
})

const showAdminSection = computed(() => {
  return authStore.hasRole(['super_admin', 'school_admin'])
})

// Main menu items (visible to all or most users)
const mainMenuItems = computed(() => {
  const role = authStore.user?.role
  const items = [
    { path: '/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
  ]

  // Student-specific menu
  if (role === 'student') {
    items.push(
      { path: '/student/profile', label: 'My Profile', icon: 'ProfileIcon' },
      { path: '/student/timetable', label: 'My Timetable', icon: 'TimetableIcon' },
      { path: '/student/grades', label: 'My Grades', icon: 'GradesIcon' },
      { path: '/student/attendance', label: 'My Attendance', icon: 'AttendanceIcon' },
    )
    return items
  }

  // Parent-specific menu
  if (role === 'parent') {
    items.push(
      { path: '/parent/children', label: 'My Children', icon: 'StudentsIcon' },
      { path: '/announcements', label: 'Announcements', icon: 'AnnouncementIcon' },
    )
    return items
  }

  // Teacher and above
  if (['teacher', 'class_teacher', 'school_admin', 'super_admin'].includes(role)) {
    items.push(
      { path: '/classes', label: 'Classes', icon: 'ClassesIcon' },
      { path: '/students', label: 'Students', icon: 'StudentsIcon' },
    )
  }

  return items
})

// Academic menu items
const academicMenuItems = computed(() => {
  const role = authStore.user?.role
  const items = []

  if (['teacher', 'class_teacher', 'school_admin', 'super_admin'].includes(role)) {
    items.push(
      { path: '/subjects', label: 'Subjects', icon: 'SubjectsIcon' },
      { path: '/attendance', label: 'Attendance', icon: 'AttendanceIcon' },
      { path: '/timetable', label: 'Timetable', icon: 'TimetableIcon' },
      { path: '/grades', label: 'Grades', icon: 'GradesIcon' },
    )
  }

  if (['class_teacher', 'school_admin', 'super_admin'].includes(role)) {
    items.push(
      { path: '/report-cards', label: 'Report Cards', icon: 'ReportCardsIcon' },
      { path: '/reports', label: 'Reports', icon: 'ReportsIcon' },
    )
  }

  if (['school_admin', 'super_admin'].includes(role)) {
    items.push(
      { path: '/duties', label: 'Duty Roster', icon: 'DutyIcon' },
    )
  }

  return items
})

// Financial menu items
const financialMenuItems = computed(() => {
  const role = authStore.user?.role
  const items = []

  if (['bursar', 'school_admin', 'super_admin'].includes(role)) {
    items.push(
      { path: '/fees', label: 'Fees', icon: 'FeesIcon' },
      { path: '/payments', label: 'Payments', icon: 'PaymentsIcon' },
      { path: '/fees/debtors', label: 'Debtors', icon: 'DebtorsIcon' },
    )
  }

  return items
})

// Admin menu items
const adminMenuItems = computed(() => {
  const role = authStore.user?.role
  const items = []

  if (['school_admin', 'super_admin'].includes(role)) {
    items.push(
      { path: '/users', label: 'User Management', icon: 'UsersIcon' },
      { path: '/teacher-assignments', label: 'Teacher Assignments', icon: 'AssignmentIcon' },
      { path: '/announcements', label: 'Announcements', icon: 'AnnouncementIcon' },
      { path: '/archive', label: 'Archive', icon: 'ArchiveIcon' },
      { path: '/settings', label: 'Settings', icon: 'SettingsIcon' },
    )
  }

  return items
})

// Super Admin menu items
const superAdminMenuItems = computed(() => {
  if (!authStore.isSuperAdmin) return []
  
  return [
    { path: '/admin/schools', label: 'Schools', icon: 'SchoolIcon' },
    { path: '/admin/licenses', label: 'Licenses', icon: 'LicenseIcon' },
    { path: '/admin/system-settings', label: 'System Settings', icon: 'SystemIcon' },
    { path: '/admin/activity-logs', label: 'Activity Logs', icon: 'LogsIcon' },
    { path: '/admin/analytics', label: 'System Analytics', icon: 'AnalyticsIcon' },
  ]
})

const toggleSidebar = () => {
  // Mobile sidebar toggle logic
}

const handleLogout = async () => {
  showUserMenu.value = false
  await authStore.logout()
}

const updateSyncStatus = async () => {
  syncStatus.value = await syncStore.getSyncStatus()
}

// Icons as components
const DashboardIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>' }
const ProfileIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>' }
const ClassesIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>' }
const SubjectsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>' }
const StudentsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>' }
const AttendanceIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>' }
const TimetableIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
const DutyIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2zM14 4v4h4" /></svg>' }
const GradesIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' }
const ReportsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' }
const ReportCardsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' }
const ArchiveIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>' }
const FeesIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>' }
const PaymentsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>' }
const DebtorsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>' }
const UsersIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>' }
const AssignmentIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>' }
const AnnouncementIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>' }
const SettingsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' }
const SchoolIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>' }
const LicenseIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>' }
const SystemIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>' }
const LogsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>' }
const AnalyticsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' }
const ExportIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>' }

onMounted(async () => {
  await updateSyncStatus()
  const interval = setInterval(updateSyncStatus, 30000) // Update every 30 seconds
  
  // Listen for online/offline events
  window.addEventListener('online', async () => {
    toast.success('Connection restored. Syncing...')
    await syncStore.sync()
    await updateSyncStatus()
  })
  
  window.addEventListener('offline', () => {
    toast.warning('No internet connection. Working offline...')
    updateSyncStatus()
  })
  
  onUnmounted(() => {
    clearInterval(interval)
  })
})
</script>

<style>
.sidebar-link {
  @apply flex items-center gap-3 px-4 py-3 rounded-lg text-blue-100 hover:bg-blue-800 transition-colors duration-200;
}

.sidebar-link.active {
  @apply bg-blue-700 text-white font-medium;
}
</style>
