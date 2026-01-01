<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Report Cards</h1>
      <p class="text-gray-600">Generate individual student report cards</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Controls -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Generate Report Card</h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
            <select v-model="selectedStudent" class="input-field">
              <option value="">Choose a student</option>
              <option v-for="student in students" :key="student.id" :value="student.id">
                {{ student.full_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
            <select v-model="selectedTerm" class="input-field">
              <option value="first">First Term</option>
              <option value="second">Second Term</option>
              <option value="third">Third Term</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Session</label>
            <input v-model="selectedSession" type="text" class="input-field" placeholder="2024/2025" />
          </div>

          <div class="pt-4 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">AI Comment Options</h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input v-model="aiOptions.performanceAnalysis" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                <span class="ml-2 text-sm text-gray-700">Include performance analysis</span>
              </label>
              <label class="flex items-center">
                <input v-model="aiOptions.improvement" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                <span class="ml-2 text-sm text-gray-700">Suggest areas for improvement</span>
              </label>
              <label class="flex items-center">
                <input v-model="aiOptions.encouragement" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                <span class="ml-2 text-sm text-gray-700">Add encouragement</span>
              </label>
              <label class="flex items-center">
                <input v-model="aiOptions.subjectFeedback" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                <span class="ml-2 text-sm text-gray-700">Subject-specific feedback</span>
              </label>
            </div>
          </div>

          <div class="flex flex-col gap-2 pt-4">
            <button @click="generateReport" class="btn-primary w-full">
              <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Generate Report
            </button>
            <button @click="generateAIComments" class="btn-secondary w-full">
              <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Generate AI Comments
            </button>
          </div>
            <button @click="printReportCard" class="btn-secondary">
              <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print
            </button>
          </div>
        </div>
      </div>

      <!-- Preview -->
      <div class="lg:col-span-2">
        <div class="card border-4 border-purple-200 p-8 bg-white">
          <!-- Report Card Preview Content -->
          <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">NUR LIGHT ACADEMY</h1>
            <p class="text-sm text-gray-600 mb-4">Excellence in Islamic and Modern Education</p>
            <h2 class="text-xl font-semibold mb-6">SENIOR SECONDARY SCHOOL REPORT</h2>
          </div>

          <div v-if="reportCardData" class="space-y-6">
            <!-- Student Info -->
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div><strong>Name:</strong> {{ reportCardData.student.full_name }}</div>
              <div><strong>Class:</strong> {{ reportCardData.student.class_model?.name }}</div>
              <div><strong>Admission No:</strong> {{ reportCardData.student.admission_no }}</div>
              <div><strong>Attendance:</strong> {{ reportCardData.attendance.days_present }} / {{ reportCardData.attendance.total_days }} days ({{ reportCardData.attendance.percentage }}%)</div>
              <div class="col-span-2"><strong>Term/Session:</strong> {{ reportCardData.term }} / {{ reportCardData.session }}</div>
            </div>

            <!-- Subjects Table -->
            <div class="overflow-x-auto">
              <table class="w-full text-xs border border-gray-300">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="border border-gray-300 p-2">SUBJECTS</th>
                    <th class="border border-gray-300 p-2 text-center">C.A. (40)</th>
                    <th class="border border-gray-300 p-2 text-center">EXAM (60)</th>
                    <th class="border border-gray-300 p-2 text-center">TOTAL (100)</th>
                    <th class="border border-gray-300 p-2 text-center">GRADE</th>
                    <th class="border border-gray-300 p-2 text-center">REMARK</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="subject in reportCardData.academic_performance" :key="subject.name">
                    <td class="border border-gray-300 p-2 font-medium">{{ subject.name }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ subject.ca1 + subject.ca2 }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ subject.exam }}</td>
                    <td class="border border-gray-300 p-2 text-center font-bold">{{ subject.total }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ subject.grade }}</td>
                    <td class="border border-gray-300 p-2 text-xs text-center">{{ getRemark(subject.grade) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Non-Academic Performance -->
            <div v-if="reportCardData.non_academic">
              <h3 class="font-bold border-b border-gray-200 pb-1 mb-3 text-sm">NON-ACADEMIC PERFORMANCE</h3>
              <div class="grid grid-cols-5 gap-2 text-[10px]">
                <div v-for="(val, key) in metrics" :key="key" class="text-center p-1 border border-gray-100 rounded">
                  <p class="uppercase text-gray-500 mb-1">{{ key }}</p>
                  <p class="text-lg font-bold">{{ reportCardData.non_academic[key] }}</p>
                </div>
              </div>
            </div>

            <!-- Comments -->
            <div class="grid grid-cols-1 gap-4 mt-6 pt-4 border-t border-gray-200">
              <div v-if="reportCardData.non_academic?.teacher_comment" class="bg-gray-50 p-3 rounded">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Teacher's Comment</p>
                <p class="text-sm italic">{{ reportCardData.non_academic.teacher_comment }}</p>
              </div>
              <div v-if="aiComment" class="bg-blue-50 p-3 rounded border border-blue-100 italic text-sm text-blue-800">
                 <p class="text-xs font-bold text-blue-600 uppercase mb-1 not-italic">AI Suggested Comment</p>
                 {{ aiComment }}
              </div>
            </div>
          </div>

          <div v-else class="text-center text-gray-500 py-12">
            Select a student to generate report card
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

const students = ref([])
const selectedStudent = ref('')
const selectedTerm = ref('first')
const selectedSession = ref('2024/2025')
const reportCardData = ref(null)
const aiComment = ref('')

const metrics = {
  leadership: 1,
  honesty: 1,
  cooperation: 1,
  punctuality: 1,
  neatness: 1
}

const aiOptions = ref({
  performanceAnalysis: true,
  improvement: true,
  encouragement: true,
  subjectFeedback: false
})

const getRemark = (grade) => {
  const remarks = {
    'A1': 'Excellent',
    'B2': 'Very Good',
    'B3': 'Good',
    'C4': 'Credit',
    'C5': 'Credit',
    'C6': 'Credit',
    'D7': 'Pass',
    'E8': 'Pass',
    'F9': 'Fail'
  }
  return remarks[grade] || 'Fair'
}

const fetchStudents = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/students`)
    students.value = response.data.data
  } catch (error) {
    toast.error('Failed to fetch students')
    console.error(error)
  }
}

const generateReport = async () => {
  if (!selectedStudent.value) {
    toast.warning('Please select a student')
    return
  }

  try {
    const response = await axios.post(`${API_BASE_URL}/report-cards/generate`, {
      student_id: selectedStudent.value,
      term: selectedTerm.value,
      session: selectedSession.value
    })
    reportCardData.value = response.data.data
    aiComment.value = '' // Reset AI comment when generating new report
  } catch (error) {
    toast.error('Failed to generate report card')
    console.error(error)
  }
}

const generateAIComments = async () => {
  if (!reportCardData.value) {
    await generateReport()
  }

  try {
    const response = await axios.post(`${API_BASE_URL}/report-cards/generate-ai-comments`, {
      student_id: selectedStudent.value,
      term: selectedTerm.value,
      session: selectedSession.value,
      options: aiOptions.value
    })
    aiComment.value = response.data.data.suggested_comment
    toast.success('AI comments generated!')
  } catch (error) {
    toast.error('Failed to generate AI comments')
    console.error(error)
  }
}

const printReportCard = () => {
  window.print()
}

onMounted(() => {
  fetchStudents()
})
</script>

<style media="print">
  @media print {
    .no-print {
      display: none;
    }
  }
</style>

