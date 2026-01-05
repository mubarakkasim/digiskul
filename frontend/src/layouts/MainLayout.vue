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
            <p class="text-xs text-blue-200">SWMS v1.0</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4">
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

        <div v-if="showActions" class="border-t border-blue-800 pt-4">
          <p class="text-xs uppercase tracking-wider text-blue-300 px-4 mb-2">Actions</p>
          <router-link
            v-for="item in actionItems"
            :key="item.path"
            :to="item.path"
            class="sidebar-link"
            :class="{ active: $route.path === item.path }"
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
          <span class="text-sm text-gray-600">Current Session: <span class="font-semibold">{{ currentSession }}</span></span>
          
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
            </button>
            
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
            >
              <router-link
                to="/settings"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="showUserMenu = false"
              >
                Settings
              </router-link>
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                Logout
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

const mainMenuItems = computed(() => {
  const role = authStore.user?.role
  const items = [
    { path: '/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
    { path: '/classes', label: 'Classes', icon: 'ClassesIcon' },
    { path: '/students', label: 'Students', icon: 'StudentsIcon' },
    { path: '/subjects', label: 'Subjects', icon: 'SubjectsIcon' },
    { path: '/attendance', label: 'Attendance', icon: 'AttendanceIcon' },
    { path: '/timetable', label: 'Timetable', icon: 'TimetableIcon' },
    { path: '/duties', label: 'Duty Roster', icon: 'DutyIcon' },
    { path: '/grades', label: 'Grades', icon: 'GradesIcon' },
    { path: '/reports', label: 'Reports', icon: 'ReportsIcon' },
    { path: '/report-cards', label: 'Report Cards', icon: 'ReportCardsIcon' },
    { path: '/archive', label: 'Archive', icon: 'ArchiveIcon' }
  ]

  if (role === 'bursar' || role === 'school_admin') {
    items.splice(4, 0, { path: '/fees', label: 'Fees', icon: 'FeesIcon' })
    items.splice(5, 0, { path: '/payments', label: 'Payments', icon: 'PaymentsIcon' })
  }

  return items
})

const showActions = computed(() => {
  const role = authStore.user?.role
  return ['school_admin', 'super_admin'].includes(role)
})

const actionItems = [
  { path: '/export', label: 'Export Data', icon: 'ExportIcon' },
  { path: '/settings', label: 'Settings', icon: 'SettingsIcon' }
]

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
const ExportIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>' }
const SettingsIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' }

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

