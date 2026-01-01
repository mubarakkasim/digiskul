<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Class Timetable</h1>
        <p class="text-gray-600">Manage and view weekly class schedules</p>
      </div>
      <button v-if="isAdmin" @click="showAddModal = true" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Period
      </button>
    </div>

    <!-- Filters -->
    <div class="card flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Class</label>
        <select v-model="filters.class_id" class="input-field">
          <option value="">All Classes</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Teacher</label>
        <select v-model="filters.teacher_id" class="input-field">
          <option value="">All Teachers</option>
          <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>
      <button @click="fetchTimetable" class="btn-secondary">Filter</button>
    </div>

    <!-- Timetable Grid -->
    <div class="card overflow-x-auto">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200">
            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200">Day / Period</th>
            <th v-for="p in maxPeriods" :key="p" class="p-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
              Period {{ p }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="day in days" :key="day" class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
            <td class="p-4 font-bold text-gray-900 capitalize bg-gray-50 border-r border-gray-200">{{ day }}</td>
            <td v-for="p in maxPeriods" :key="p" class="p-4 border-r border-gray-100 last:border-r-0 min-w-[150px]">
              <div v-if="getItem(day, p)" class="p-3 rounded-lg bg-blue-50 border border-blue-100 group relative">
                <p class="font-bold text-blue-900 text-sm">{{ getItem(day, p).subject.name }}</p>
                <p class="text-xs text-blue-700">{{ getItem(day, p).class_model.name }}</p>
                <p class="text-[10px] text-blue-600 mt-1">{{ getItem(day, p).teacher.name }}</p>
                <p class="text-[10px] text-gray-400 mt-1">{{ formatTime(getItem(day, p).start_time) }} - {{ formatTime(getItem(day, p).end_time) }}</p>
                
                <button v-if="isAdmin" @click="deleteItem(getItem(day, p).id)" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              <div v-else-if="isAdmin" class="h-full flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                 <button @click="openAddForSlot(day, p)" class="text-gray-400 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                 </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add Modal Placeholder -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Add Period</h3>
        <form @submit.prevent="savePeriod" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Day</label>
            <select v-model="newItem.day" class="input-field" required>
              <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Period Number</label>
            <input type="number" v-model="newItem.period" class="input-field" required min="1" max="10">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Class</label>
              <select v-model="newItem.class_id" class="input-field" required>
                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Subject</label>
              <select v-model="newItem.subject_id" class="input-field" required>
                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Teacher</label>
            <select v-model="newItem.teacher_id" class="input-field" required>
              <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Start Time</label>
              <input type="time" v-model="newItem.start_time" class="input-field" required>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">End Time</label>
              <input type="time" v-model="newItem.end_time" class="input-field" required>
            </div>
          </div>
          <div class="flex gap-4 pt-4">
            <button type="button" @click="showAddModal = false" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" class="btn-primary flex-1">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const toast = useToast()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const isAdmin = computed(() => ['school_admin', 'super_admin'].includes(authStore.user?.role))
const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
const maxPeriods = 8

const timetable = ref([])
const classes = ref([])
const teachers = ref([])
const subjects = ref([])
const showAddModal = ref(false)

const filters = ref({
  class_id: '',
  teacher_id: ''
})

const newItem = ref({
  day: 'monday',
  period: 1,
  class_id: '',
  subject_id: '',
  teacher_id: '',
  start_time: '08:00',
  end_time: '08:40'
})

const fetchTimetable = async () => {
  try {
    const params = {}
    if (filters.value.class_id) params.class_id = filters.value.class_id
    if (filters.value.teacher_id) params.teacher_id = filters.value.teacher_id
    
    const response = await axios.get(`${API_BASE_URL}/timetable`, { params })
    timetable.value = response.data.data
  } catch (error) {
    toast.error('Failed to load timetable')
  }
}

const fetchMetadata = async () => {
  try {
    // These should ideally be in a central store
    const [cRes, tRes, sRes] = await Promise.all([
      axios.get(`${API_BASE_URL}/classes`),
      axios.get(`${API_BASE_URL}/subjects`), // Assuming subjects API exists
      axios.get(`${API_BASE_URL}/students`) // Using students to get school-related data for now or a meta endpoint
    ])
    classes.value = cRes.data.data || []
    subjects.value = tRes.data.data || []
    // For teachers, we might need a specific endpoint, using a placeholder for now
    teachers.value = [{ id: authStore.user.id, name: authStore.user.name }]
  } catch (error) {
    console.error('Failed to load metadata')
  }
}

const getItem = (day, period) => {
  return timetable.value.find(item => item.day === day && item.period === period)
}

const formatTime = (time) => {
  if (!time) return ''
  return time.substring(0, 5)
}

const openAddForSlot = (day, period) => {
  newItem.value.day = day
  newItem.value.period = period
  showAddModal.value = true
}

const savePeriod = async () => {
  try {
    await axios.post(`${API_BASE_URL}/timetable`, newItem.value)
    toast.success('Period added successfully')
    showAddModal.value = false
    fetchTimetable()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save period')
  }
}

const deleteItem = async (id) => {
  if (!confirm('Are you sure you want to delete this period?')) return
  try {
    await axios.delete(`${API_BASE_URL}/timetable/${id}`)
    toast.success('Period deleted')
    fetchTimetable()
  } catch (error) {
    toast.error('Failed to delete period')
  }
}

onMounted(() => {
  fetchTimetable()
  fetchMetadata()
})
</script>
