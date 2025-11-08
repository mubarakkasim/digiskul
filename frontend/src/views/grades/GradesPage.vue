<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Grades</h1>
      <p class="text-gray-600">Record and manage student assessments</p>
    </div>

    <!-- Grade Entry Card -->
    <div class="card">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Grade Entry</h2>
      <p class="text-sm text-gray-600 mb-6">CA1 (20), CA2 (20), Exam (60) = Total (100)</p>

      <!-- Assessment Tabs -->
      <div class="flex gap-2 mb-6 border-b border-gray-200">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-4 py-2 font-medium border-b-2 transition-colors"
          :class="activeTab === tab.key ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Summary View -->
      <div v-if="activeTab === 'summary'" class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CA1</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CA2</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="student in students" :key="student.id">
              <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ student.full_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.ca1 || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.ca2 || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.exam || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ calculateTotal(student) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-semibold rounded-lg"
                  :class="getGradeClass(calculateTotal(student))"
                >
                  {{ calculateGrade(calculateTotal(student)) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Entry View -->
      <div v-else class="space-y-4">
        <div
          v-for="student in students"
          :key="student.id"
          class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
        >
          <span class="font-medium text-gray-900">{{ student.full_name }}</span>
          <div class="flex items-center gap-4">
            <input
              v-model="student[activeTab]"
              type="number"
              min="0"
              :max="activeTab === 'exam' ? 60 : 20"
              class="w-24 input-field text-center"
              placeholder="0"
            />
            <span class="text-sm text-gray-500">/ {{ activeTab === 'exam' ? 60 : 20 }}</span>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button @click="saveGrades" class="btn-primary" :disabled="saving">
            <svg v-if="!saving" class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span v-if="saving">Saving...</span>
            <span v-else>Save Grades</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useSyncStore } from '../../stores/sync'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()
const syncStore = useSyncStore()

const activeTab = ref('ca1')
const students = ref([])
const saving = ref(false)

const tabs = [
  { key: 'ca1', label: 'CA 1 (20)' },
  { key: 'ca2', label: 'CA 2 (20)' },
  { key: 'exam', label: 'Exam (60)' },
  { key: 'summary', label: 'Summary' }
]

const calculateTotal = (student) => {
  const ca1 = parseFloat(student.ca1) || 0
  const ca2 = parseFloat(student.ca2) || 0
  const exam = parseFloat(student.exam) || 0
  return ca1 + ca2 + exam
}

const calculateGrade = (total) => {
  if (total >= 90) return 'A'
  if (total >= 80) return 'B'
  if (total >= 70) return 'C'
  if (total >= 60) return 'D'
  if (total >= 50) return 'E'
  return 'F'
}

const getGradeClass = (total) => {
  const grade = calculateGrade(total)
  if (grade === 'A' || grade === 'B') return 'bg-blue-100 text-blue-800'
  if (grade === 'C' || grade === 'D') return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const fetchStudents = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/students`)
    students.value = response.data.data.map(student => ({
      ...student,
      ca1: student.ca1 || '',
      ca2: student.ca2 || '',
      exam: student.exam || ''
    }))
  } catch (error) {
    toast.error('Failed to fetch students')
    console.error(error)
  }
}

const saveGrades = async () => {
  saving.value = true
  try {
    const gradesData = students.value
      .filter(student => student[activeTab.value])
      .map(student => ({
        student_id: student.id,
        assessment_type: activeTab.value,
        score: parseFloat(student[activeTab.value]),
        term: 'First Term',
        session: '2024/2025'
      }))

    if (navigator.onLine) {
      await axios.post(`${API_BASE_URL}/grades/bulk`, {
        grades: gradesData
      })
      toast.success('Grades saved successfully!')
    } else {
      for (const data of gradesData) {
        await syncStore.addToSyncQueue('INSERT', 'grades', data)
      }
      toast.info('Grades saved offline. Will sync when online.')
    }
  } catch (error) {
    toast.error('Failed to save grades')
    console.error(error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchStudents()
})
</script>

