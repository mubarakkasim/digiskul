<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">School Management</h1>
      <p class="text-gray-600">Manage all schools in the DIGISKUL ecosystem</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card bg-gradient-to-br from-blue-500 to-blue-700 text-white">
        <p class="text-sm opacity-80">Total Schools</p>
        <p class="text-3xl font-bold">{{ stats.totalSchools }}</p>
      </div>
      <div class="card bg-gradient-to-br from-green-500 to-green-700 text-white">
        <p class="text-sm opacity-80">Active Schools</p>
        <p class="text-3xl font-bold">{{ stats.activeSchools }}</p>
      </div>
      <div class="card bg-gradient-to-br from-yellow-500 to-yellow-700 text-white">
        <p class="text-sm opacity-80">Expiring Licenses</p>
        <p class="text-3xl font-bold">{{ stats.expiringLicenses }}</p>
      </div>
      <div class="card bg-gradient-to-br from-purple-500 to-purple-700 text-white">
        <p class="text-sm opacity-80">Total Users</p>
        <p class="text-3xl font-bold">{{ stats.totalUsers }}</p>
      </div>
    </div>

    <!-- Schools Table -->
    <div class="card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">All Schools</h2>
        <button @click="showAddModal = true" class="btn-primary">
          + Add School
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">School</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Subdomain</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Plan</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">License Until</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="school in schools" :key="school.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-4 px-4">
                <div>
                  <p class="font-semibold text-gray-900">{{ school.name }}</p>
                  <p class="text-sm text-gray-500">{{ school.email }}</p>
                </div>
              </td>
              <td class="py-4 px-4 text-gray-600">{{ school.subdomain }}.digiskul.app</td>
              <td class="py-4 px-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-purple-100 text-purple-800': school.subscription_plan === 'premium',
                    'bg-blue-100 text-blue-800': school.subscription_plan === 'standard',
                    'bg-gray-100 text-gray-800': school.subscription_plan === 'basic',
                  }">
                  {{ school.subscription_plan }}
                </span>
              </td>
              <td class="py-4 px-4 text-gray-600">{{ formatDate(school.license_valid_until) }}</td>
              <td class="py-4 px-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="school.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ school.active ? 'Active' : 'Suspended' }}
                </span>
              </td>
              <td class="py-4 px-4">
                <div class="flex gap-2">
                  <button @click="editSchool(school)" class="text-blue-600 hover:text-blue-800">Edit</button>
                  <button v-if="school.active" @click="suspendSchool(school)" class="text-red-600 hover:text-red-800">Suspend</button>
                  <button v-else @click="activateSchool(school)" class="text-green-600 hover:text-green-800">Activate</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const schools = ref([])
const stats = ref({
  totalSchools: 0,
  activeSchools: 0,
  expiringLicenses: 0,
  totalUsers: 0,
})
const showAddModal = ref(false)

const fetchSchools = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/admin/schools`)
    schools.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch schools:', error)
    toast.error('Failed to load schools')
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/admin/system/analytics`)
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const editSchool = (school) => {
  // Navigate to edit page or open modal
}

const suspendSchool = async (school) => {
  if (!confirm(`Are you sure you want to suspend ${school.name}?`)) return
  
  try {
    await axios.post(`${API_BASE_URL}/admin/schools/${school.id}/suspend`)
    toast.success('School suspended successfully')
    fetchSchools()
  } catch (error) {
    toast.error('Failed to suspend school')
  }
}

const activateSchool = async (school) => {
  try {
    await axios.post(`${API_BASE_URL}/admin/schools/${school.id}/activate`)
    toast.success('School activated successfully')
    fetchSchools()
  } catch (error) {
    toast.error('Failed to activate school')
  }
}

onMounted(() => {
  fetchSchools()
  fetchStats()
})
</script>
