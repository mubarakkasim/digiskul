<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Duty Roster</h1>
        <p class="text-gray-600">Track teacher supervision and daily activities</p>
      </div>
      <button v-if="isAdmin" @click="showAddModal = true" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Assign Duty
      </button>
    </div>

    <!-- Calendar / List Toggle -->
    <div class="card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">Upcoming Duties</h2>
        <div class="flex gap-2">
          <input type="date" v-model="filters.start_date" class="input-field py-1">
          <input type="date" v-model="filters.end_date" class="input-field py-1">
          <button @click="fetchDuties" class="btn-secondary py-1 px-4">Filter</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
              <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Teacher</th>
              <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Activity</th>
              <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Description</th>
              <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
              <th v-if="isAdmin" class="p-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="duty in duties" :key="duty.id" class="hover:bg-gray-50 transition-colors">
              <td class="p-4 text-sm font-medium text-gray-900">{{ formatDate(duty.date) }}</td>
              <td class="p-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-xs">
                    {{ duty.teacher.name.substring(0,2).toUpperCase() }}
                  </div>
                  <span class="text-sm text-gray-900">{{ duty.teacher.name }}</span>
                </div>
              </td>
              <td class="p-4">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getActivityClass(duty.activity)">
                  {{ duty.activity }}
                </span>
              </td>
              <td class="p-4 text-sm text-gray-600">{{ duty.description || '-' }}</td>
              <td class="p-4 text-sm text-gray-500">
                <span v-if="duty.start_time">{{ formatTime(duty.start_time) }} - {{ formatTime(duty.end_time) }}</span>
                <span v-else>All Day</span>
              </td>
              <td v-if="isAdmin" class="p-4 text-right">
                <button @click="deleteDuty(duty.id)" class="text-red-500 hover:text-red-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>
            <tr v-if="duties.length === 0">
              <td colspan="6" class="p-8 text-center text-gray-500">No duties assigned for the selected period.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Duty Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Assign Supervision Duty</h3>
        <form @submit.prevent="saveDuty" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Teacher</label>
            <select v-model="newItem.teacher_id" class="input-field" required>
              <option value="">Select Teacher</option>
              <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Date</label>
            <input type="date" v-model="newItem.date" class="input-field" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Activity</label>
            <select v-model="newItem.activity" class="input-field" required>
              <option value="Assembly">Assembly</option>
              <option value="Sport">Sport</option>
              <option value="Interhouse">Interhouse</option>
              <option value="Religious">Religious</option>
              <option value="Lunch Supervision">Lunch Supervision</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea v-model="newItem.description" class="input-field" rows="2" placeholder="Specific instructions..."></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Start Time</label>
              <input type="time" v-model="newItem.start_time" class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">End Time</label>
              <input type="time" v-model="newItem.end_time" class="input-field">
            </div>
          </div>
          <div class="flex gap-4 pt-4">
            <button type="button" @click="showAddModal = false" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" class="btn-primary flex-1">Assign</button>
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
import { format } from 'date-fns'

const authStore = useAuthStore()
const toast = useToast()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const isAdmin = computed(() => ['school_admin', 'super_admin'].includes(authStore.user?.role))
const duties = ref([])
const teachers = ref([])
const showAddModal = ref(false)

const filters = ref({
  start_date: format(new Date(), 'yyyy-MM-dd'),
  end_date: format(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000), 'yyyy-MM-dd')
})

const newItem = ref({
  teacher_id: '',
  date: format(new Date(), 'yyyy-MM-dd'),
  activity: 'Assembly',
  description: '',
  start_time: '07:30',
  end_time: '08:15'
})

const fetchDuties = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/duties`, { params: filters.value })
    duties.value = response.data.data
  } catch (error) {
    toast.error('Failed to load duties')
  }
}

const fetchTeachers = async () => {
  try {
    // Placeholder using auth user and assuming a students endpoint can return something or mock
    teachers.value = [{ id: authStore.user.id, name: authStore.user.name }]
  } catch (error) {
    console.error('Failed to load teachers')
  }
}

const saveDuty = async () => {
  try {
    await axios.post(`${API_BASE_URL}/duties`, newItem.value)
    toast.success('Duty assigned successfully')
    showAddModal.value = false
    fetchDuties()
  } catch (error) {
    toast.error('Failed to assign duty')
  }
}

const deleteDuty = async (id) => {
  if (!confirm('Are you sure you want to delete this duty assignment?')) return
  try {
    await axios.delete(`${API_BASE_URL}/duties/${id}`)
    toast.success('Duty deleted')
    fetchDuties()
  } catch (error) {
    toast.error('Failed to delete duty')
  }
}

const formatDate = (dateStr) => {
  return format(new Date(dateStr), 'EEE, MMM d, yyyy')
}

const formatTime = (time) => {
  if (!time) return ''
  return time.substring(0, 5)
}

const getActivityClass = (activity) => {
  const map = {
    'Assembly': 'bg-blue-100 text-blue-800',
    'Sport': 'bg-green-100 text-green-800',
    'Interhouse': 'bg-purple-100 text-purple-800',
    'Religious': 'bg-yellow-100 text-yellow-800',
    'Lunch Supervision': 'bg-orange-100 text-orange-800'
  }
  return map[activity] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchDuties()
  fetchTeachers()
})
</script>
