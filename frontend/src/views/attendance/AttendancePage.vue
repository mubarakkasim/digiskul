<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Attendance</h1>
      <p class="text-gray-600">Track daily student attendance</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Date Selection -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Date</h2>
        <div class="calendar">
          <div class="flex items-center justify-between mb-4">
            <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <h3 class="font-semibold text-gray-900">{{ currentMonthYear }}</h3>
            <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
          <div class="grid grid-cols-7 gap-2 mb-2">
            <div v-for="day in daysOfWeek" :key="day" class="text-center text-xs font-medium text-gray-500 py-2">
              {{ day }}
            </div>
          </div>
          <div class="grid grid-cols-7 gap-2">
            <button
              v-for="date in calendarDates"
              :key="date.key"
              @click="selectDate(date.date)"
              class="p-2 text-sm rounded-lg hover:bg-gray-100 transition-colors"
              :class="{
                'bg-blue-600 text-white hover:bg-blue-700': date.isSelected,
                'text-gray-400': !date.isCurrentMonth,
                'font-semibold': date.isToday && !date.isSelected
              }"
            >
              {{ date.day }}
            </button>
          </div>
        </div>
      </div>

      <!-- Attendance List -->
      <div class="lg:col-span-2 card">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Attendance - {{ formattedSelectedDate }}</h2>
            <p class="text-sm text-gray-600">{{ attendanceSummary }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="markAllPresent" class="btn-secondary text-sm">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              All Present
            </button>
            <button @click="markAllAbsent" class="btn-secondary text-sm">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              All Absent
            </button>
          </div>
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
          <div
            v-for="student in students"
            :key="student.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-semibold text-blue-600">{{ getInitials(student.full_name) }}</span>
              </div>
              <span class="font-medium text-gray-900">{{ student.full_name }}</span>
            </div>
            <div class="flex items-center gap-3">
              <span
                class="px-4 py-2 rounded-lg text-sm font-semibold"
                :class="student.attendance_status === 'present' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'"
              >
                {{ student.attendance_status === 'present' ? 'Present' : 'Absent' }}
              </span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="student.attendance_status === 'present'"
                  @change="toggleAttendance(student.id)"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button @click="saveAttendance" class="btn-primary" :disabled="saving">
            <svg v-if="!saving" class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span v-if="saving">Saving...</span>
            <span v-else>Save Attendance</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { format, startOfMonth, endOfMonth, eachDayOfInterval, isSameMonth, isSameDay, isToday, addMonths, subMonths } from 'date-fns'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useSyncStore } from '../../stores/sync'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()
const syncStore = useSyncStore()

const currentDate = ref(new Date())
const selectedDate = ref(new Date())
const students = ref([])
const saving = ref(false)

const daysOfWeek = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const currentMonthYear = computed(() => format(currentDate.value, 'MMMM yyyy'))

const formattedSelectedDate = computed(() => format(selectedDate.value, 'MMMM dd, yyyy'))

const attendanceSummary = computed(() => {
  const present = students.value.filter(s => s.attendance_status === 'present').length
  const total = students.value.length
  const percentage = total > 0 ? Math.round((present / total) * 100) : 0
  return `${present} of ${total} students present (${percentage}%)`
})

const calendarDates = computed(() => {
  const monthStart = startOfMonth(currentDate.value)
  const monthEnd = endOfMonth(currentDate.value)
  const days = eachDayOfInterval({ start: monthStart, end: monthEnd })
  
  // Add padding days from previous month
  const startDay = monthStart.getDay()
  const paddingDays = []
  for (let i = 0; i < startDay; i++) {
    const date = new Date(monthStart)
    date.setDate(date.getDate() - (startDay - i))
    paddingDays.push({
      date,
      day: date.getDate(),
      isCurrentMonth: false,
      isToday: false,
      isSelected: false
    })
  }

  return [
    ...paddingDays.map(d => ({ ...d, key: `prev-${d.day}` })),
    ...days.map(date => ({
      date,
      day: date.getDate(),
      isCurrentMonth: true,
      isToday: isToday(date),
      isSelected: isSameDay(date, selectedDate.value),
      key: `curr-${date.getDate()}`
    }))
  ]
})

const previousMonth = () => {
  currentDate.value = subMonths(currentDate.value, 1)
}

const nextMonth = () => {
  currentDate.value = addMonths(currentDate.value, 1)
}

const selectDate = (date) => {
  selectedDate.value = date
  fetchAttendance()
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const toggleAttendance = (studentId) => {
  const student = students.value.find(s => s.id === studentId)
  if (student) {
    student.attendance_status = student.attendance_status === 'present' ? 'absent' : 'present'
  }
}

const markAllPresent = () => {
  students.value.forEach(student => {
    student.attendance_status = 'present'
  })
}

const markAllAbsent = () => {
  students.value.forEach(student => {
    student.attendance_status = 'absent'
  })
}

const fetchAttendance = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/attendance`, {
      params: {
        date: format(selectedDate.value, 'yyyy-MM-dd')
      }
    })
    students.value = response.data.data
  } catch (error) {
    toast.error('Failed to fetch attendance data')
    console.error(error)
  }
}

const saveAttendance = async () => {
  saving.value = true
  try {
    const attendanceData = students.value.map(student => ({
      student_id: student.id,
      date: format(selectedDate.value, 'yyyy-MM-dd'),
      status: student.attendance_status
    }))

    if (navigator.onLine) {
      await axios.post(`${API_BASE_URL}/attendance/bulk`, {
        date: format(selectedDate.value, 'yyyy-MM-dd'),
        attendance: attendanceData
      })
      toast.success('Attendance saved successfully!')
    } else {
      // Store offline
      for (const data of attendanceData) {
        await syncStore.addToSyncQueue('INSERT', 'attendance', data)
      }
      toast.info('Attendance saved offline. Will sync when online.')
    }
  } catch (error) {
    toast.error('Failed to save attendance')
    console.error(error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchAttendance()
})
</script>

