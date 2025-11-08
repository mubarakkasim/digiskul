<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Reports</h1>
      <p class="text-gray-600">Generate and export term summaries</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="card">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">Student Report Cards</h3>
            <p class="text-sm text-gray-600">Generate individual report cards with grades and attendance for all students</p>
          </div>
        </div>
        <button @click="generateReportCards" class="btn-primary w-full">
          <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Generate Report Cards
        </button>
      </div>

      <div class="card">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">Class Summary</h3>
            <p class="text-sm text-gray-600">Overall class performance statistics, attendance trends, and grade distribution</p>
          </div>
        </div>
        <button @click="generateClassSummary" class="btn-primary w-full">
          <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Generate Summary
        </button>
      </div>

      <div class="card">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">Attendance Report</h3>
            <p class="text-sm text-gray-600">Detailed attendance records for the entire term, including daily breakdowns</p>
          </div>
        </div>
        <button @click="generateAttendanceReport" class="btn-primary w-full">
          <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Generate Report
        </button>
      </div>

      <div class="card">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">Grade Analysis</h3>
            <p class="text-sm text-gray-600">Comprehensive grade analysis with top performers and areas needing attention</p>
          </div>
        </div>
        <button @click="generateGradeAnalysis" class="btn-primary w-full">
          <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Generate Analysis
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()

const generating = ref(false)

const generateReportCards = async () => {
  generating.value = true
  try {
    const response = await axios.post(`${API_BASE_URL}/reports/report-cards`, {}, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'report-cards.pdf')
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast.success('Report cards generated successfully!')
  } catch (error) {
    toast.error('Failed to generate report cards')
    console.error(error)
  } finally {
    generating.value = false
  }
}

const generateClassSummary = async () => {
  generating.value = true
  try {
    const response = await axios.post(`${API_BASE_URL}/reports/class-summary`, {}, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'class-summary.pdf')
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast.success('Class summary generated successfully!')
  } catch (error) {
    toast.error('Failed to generate class summary')
    console.error(error)
  } finally {
    generating.value = false
  }
}

const generateAttendanceReport = async () => {
  generating.value = true
  try {
    const response = await axios.post(`${API_BASE_URL}/reports/attendance`, {}, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'attendance-report.pdf')
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast.success('Attendance report generated successfully!')
  } catch (error) {
    toast.error('Failed to generate attendance report')
    console.error(error)
  } finally {
    generating.value = false
  }
}

const generateGradeAnalysis = async () => {
  generating.value = true
  try {
    const response = await axios.post(`${API_BASE_URL}/reports/grade-analysis`, {}, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'grade-analysis.pdf')
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast.success('Grade analysis generated successfully!')
  } catch (error) {
    toast.error('Failed to generate grade analysis')
    console.error(error)
  } finally {
    generating.value = false
  }
}
</script>

