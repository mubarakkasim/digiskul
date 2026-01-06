<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome, {{ studentProfile?.name }}</h1>
      <p class="text-gray-600">{{ studentProfile?.class }} | Admission No: {{ studentProfile?.admission_no }}</p>
    </div>

    <!-- Performance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="card bg-gradient-to-br from-blue-600 to-blue-800 text-white">
        <h2 class="text-xl font-bold mb-4">My Performance</h2>
        <div class="flex items-end justify-between">
          <div>
            <p class="text-4xl font-bold">{{ stats.performance?.average || 0 }}%</p>
            <p class="text-blue-100">Overall Average</p>
          </div>
          <div class="text-right">
            <p class="text-lg font-semibold">Rank: {{ stats.performance?.rank || 'N/A' }}</p>
            <p class="text-blue-100">in {{ studentProfile?.class }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-green-600 to-green-800 text-white">
        <h2 class="text-xl font-bold mb-4">My Attendance</h2>
        <div class="flex items-end justify-between">
          <div>
            <p class="text-4xl font-bold">{{ stats.attendance?.percentage || 0 }}%</p>
            <p class="text-green-100">Term Attendance</p>
          </div>
          <div class="text-right">
            <p class="text-lg font-semibold">{{ stats.attendance?.present_days || 0 }} Days</p>
            <p class="text-green-100">Present this term</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Schedule -->
    <div class="card mb-8">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Today's Schedule</h2>
      <div v-if="stats.todaySchedule && stats.todaySchedule.length > 0" class="space-y-4">
        <div v-for="period in stats.todaySchedule" :key="period.id" 
          class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
          <div class="text-center min-w-[70px]">
            <p class="text-xs font-bold text-blue-600 uppercase">Period {{ period.period }}</p>
            <p class="text-sm text-gray-500">{{ period.start_time?.substring(0, 5) }}</p>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-900">{{ period.subject?.name }}</p>
            <p class="text-sm text-gray-600">{{ period.teacher?.name }}</p>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        <p>No classes scheduled for today.</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <router-link to="/student/grades" class="card hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">View Grades</h3>
            <p class="text-sm text-gray-600">Check your assessment scores</p>
          </div>
        </div>
      </router-link>

      <router-link to="/student/attendance" class="card hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Attendance Record</h3>
            <p class="text-sm text-gray-600">View your attendance history</p>
          </div>
        </div>
      </router-link>

      <router-link to="/student/timetable" class="card hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Full Timetable</h3>
            <p class="text-sm text-gray-600">View weekly class schedule</p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const stats = ref({
  profile: {},
  attendance: {},
  performance: {},
  todaySchedule: []
})

const studentProfile = ref(null)

const fetchDashboardData = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/dashboard/stats`)
    stats.value = response.data.data
    studentProfile.value = stats.value.profile
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
