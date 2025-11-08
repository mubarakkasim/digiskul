<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
      <p class="text-gray-600">Welcome back! Here's your class overview</p>
    </div>

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

    <!-- Quick Actions -->
    <div class="mb-8">
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

    <!-- Recent Activity & Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity -->
      <div class="card">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Activity</h2>
        <div class="space-y-4">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-900">Marked attendance</p>
              <p class="text-sm text-gray-600">45 students present</p>
              <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-900">Updated grades</p>
              <p class="text-sm text-gray-600">Mathematics CA2</p>
              <p class="text-xs text-gray-400 mt-1">Yesterday</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-900">Generated report</p>
              <p class="text-sm text-gray-600">Mid-term summary</p>
              <p class="text-xs text-gray-400 mt-1">3 days ago</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Class Performance -->
      <div class="card">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Class Performance</h2>
        <div class="space-y-6">
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Average Grade</span>
              <span class="text-lg font-bold text-gray-900">78%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div class="bg-blue-600 h-3 rounded-full" style="width: 78%"></div>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Attendance Rate</span>
              <span class="text-lg font-bold text-gray-900">89%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div class="bg-green-600 h-3 rounded-full" style="width: 89%"></div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
            <div class="text-center">
              <p class="text-2xl font-bold text-blue-600">12</p>
              <p class="text-sm text-gray-600">Excellent</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-green-600">23</p>
              <p class="text-sm text-gray-600">Good</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-yellow-600">10</p>
              <p class="text-sm text-gray-600">Fair</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

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

