<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
      <p class="text-gray-600">Welcome back, {{ authStore.user?.name }}! Here's your overview</p>
    </div>

    <!-- Teacher/Admin Dashboard -->
    <div v-if="['teacher', 'school_admin'].includes(authStore.user?.role)">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Total Students</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.totalStudents }}</p>
              <p class="text-sm text-gray-500 mt-1">Active this term</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Attendance Today</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.attendanceToday }}%</p>
              <p class="text-sm text-green-600 mt-1">+5% from yesterday</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Pending Assessments</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.pendingAssessments }}</p>
              <p class="text-sm text-gray-500 mt-1">Due this week</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Schedule & Duty -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="card">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Today's Schedule</h2>
          <div v-if="stats.todayPeriods && stats.todayPeriods.length > 0" class="space-y-4">
            <div v-for="period in stats.todayPeriods" :key="period.id" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
              <div class="text-center min-w-[60px]">
                <p class="text-xs font-bold text-blue-600 uppercase">Period {{ period.period }}</p>
                <p class="text-xs text-gray-500">{{ period.start_time.substring(0, 5) }}</p>
              </div>
              <div class="flex-1">
                <p class="font-semibold text-gray-900">{{ period.subject.name }}</p>
                <p class="text-sm text-gray-600">{{ period.class_model.name }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            <p>No classes scheduled for today.</p>
          </div>
        </div>

        <div class="card">
          <h2 class="text-xl font-bold text-gray-900 mb-4">On Duty Today</h2>
          <div v-if="stats.todayDuty" class="bg-blue-50 p-4 rounded-lg border border-blue-100">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="font-bold text-blue-900">{{ stats.todayDuty.activity }}</h3>
            </div>
            <p class="text-sm text-blue-800">{{ stats.todayDuty.description }}</p>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            <p>You have no supervision duties today.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Dashboard -->
    <div v-else-if="authStore.user?.role === 'student'">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="card bg-gradient-to-br from-blue-600 to-blue-800 text-white">
          <h2 class="text-xl font-bold mb-4">My Performance</h2>
          <div class="flex items-end justify-between">
            <div>
              <p class="text-4xl font-bold">84%</p>
              <p class="text-blue-100">Overall Average</p>
            </div>
            <div class="text-right">
              <p class="text-lg font-semibold">Rank: 5th</p>
              <p class="text-blue-100">in JSS 3A</p>
            </div>
          </div>
        </div>
        <div class="card bg-gradient-to-br from-green-600 to-green-800 text-white">
          <h2 class="text-xl font-bold mb-4">My Attendance</h2>
          <div class="flex items-end justify-between">
            <div>
              <p class="text-4xl font-bold">96%</p>
              <p class="text-green-100">Term Attendance</p>
            </div>
            <div class="text-right">
              <p class="text-lg font-semibold">48 Days</p>
              <p class="text-green-100">Present this term</p>
            </div>
          </div>
        </div>
      </div>
      <div class="card mb-8">
        <h2 class="text-xl font-bold mb-4">Upcoming Subjects Today</h2>
        <!-- Student's schedule would go here -->
        <p class="text-gray-500">Check your timetable for full details.</p>
      </div>
    </div>

    <!-- Parent Dashboard -->
    <div v-else-if="authStore.user?.role === 'parent'">
      <h2 class="text-xl font-bold mb-4">My Children</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card hover:shadow-lg transition-shadow border-t-4 border-blue-600">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
            <div>
              <h3 class="font-bold text-lg">Mubarak Kasim</h3>
              <p class="text-sm text-gray-600">JSS 3A | Adm No: 2024001</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="p-3 bg-gray-50 rounded-lg text-center">
              <p class="text-xs text-gray-500 uppercase">Avg Grade</p>
              <p class="text-xl font-bold text-blue-600">84%</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg text-center">
              <p class="text-xs text-gray-500 uppercase">Attendance</p>
              <p class="text-xl font-bold text-green-600">96%</p>
            </div>
          </div>
          <button @click="$router.push('/report-cards')" class="btn-secondary w-full">View Report Card</button>
        </div>
      </div>
    </div>

    <!-- General Quick Actions (Visible to Teachers/Admins) -->
    <div v-if="['teacher', 'school_admin'].includes(authStore.user?.role)" class="mb-8 mt-8">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/attendance')">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Mark Attendance</h3>
              <p class="text-sm text-gray-600">Record today's student attendance</p>
            </div>
          </div>
          <button class="btn-primary w-full">Mark Attendance</button>
        </div>

        <div class="card hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/grades')">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Record Grades</h3>
              <p class="text-sm text-gray-600">Enter assessment scores</p>
            </div>
          </div>
          <button class="btn-secondary w-full">Record Grades</button>
        </div>

        <div class="card hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/reports')">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">View Reports</h3>
              <p class="text-sm text-gray-600">Generate term summaries</p>
            </div>
          </div>
          <button class="btn-secondary w-full">View Reports</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()

const stats = ref({
  totalStudents: 45,
  attendanceToday: 92,
  pendingAssessments: 3
})

onMounted(async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/dashboard/stats`)
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
    // Use default values if API fails
  }
})
</script>

