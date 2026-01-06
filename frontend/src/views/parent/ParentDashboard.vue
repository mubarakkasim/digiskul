<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Parent Dashboard</h1>
      <p class="text-gray-600">Welcome, {{ authStore.userName }}. Monitor your children's progress.</p>
    </div>

    <!-- Children Cards -->
    <h2 class="text-xl font-bold text-gray-900 mb-4">My Children</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div v-for="child in children" :key="child.id" 
        class="card hover:shadow-lg transition-shadow border-t-4 border-blue-600">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-14 h-14 bg-blue-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
            {{ getInitials(child.name) }}
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-lg text-gray-900">{{ child.name }}</h3>
            <p class="text-sm text-gray-600">{{ child.class }} | Adm No: {{ child.admission_no }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div class="p-3 bg-blue-50 rounded-lg text-center">
            <p class="text-xs text-gray-500 uppercase">Avg Grade</p>
            <p class="text-2xl font-bold text-blue-600">{{ child.performance_average || 0 }}%</p>
          </div>
          <div class="p-3 bg-green-50 rounded-lg text-center">
            <p class="text-xs text-gray-500 uppercase">Attendance</p>
            <p class="text-2xl font-bold text-green-600">{{ child.attendance_percentage || 0 }}%</p>
          </div>
        </div>

        <div class="flex gap-2">
          <router-link :to="`/parent/child/${child.id}`" class="btn-primary flex-1 text-center">
            View Details
          </router-link>
          <button @click="downloadReportCard(child.id)" class="btn-secondary">
            Report Card
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="children.length === 0" class="card text-center py-12">
      <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No Children Linked</h3>
      <p class="text-gray-500">Contact the school administration to link your children to your account.</p>
    </div>

    <!-- Recent Announcements -->
    <div class="card">
      <h2 class="text-xl font-bold text-gray-900 mb-4">School Announcements</h2>
      <div v-if="announcements.length > 0" class="space-y-4">
        <div v-for="announcement in announcements" :key="announcement.id" 
          class="p-4 bg-gray-50 rounded-lg">
          <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-gray-900">{{ announcement.title }}</h3>
            <span class="text-xs text-gray-500">{{ formatDate(announcement.published_at) }}</span>
          </div>
          <p class="text-sm text-gray-600 line-clamp-2">{{ announcement.content }}</p>
        </div>
      </div>
      <div v-else class="text-center py-6 text-gray-500">
        <p>No announcements at this time.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const toast = useToast()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const children = ref([])
const announcements = ref([])

const fetchDashboardData = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/dashboard/stats`)
    children.value = response.data.data.children || []
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
  }
}

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/announcements`, { params: { per_page: 5 } })
    announcements.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch announcements:', error)
  }
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const downloadReportCard = async (studentId) => {
  toast.info('Downloading report card...')
  // Implement report card download
}

onMounted(() => {
  fetchDashboardData()
  fetchAnnouncements()
})
</script>
