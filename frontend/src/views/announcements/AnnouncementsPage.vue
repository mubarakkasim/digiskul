<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Announcements</h1>
      <p class="text-gray-600">School notices and important updates</p>
    </div>

    <!-- Admin Actions -->
    <div v-if="canManage" class="card mb-6">
      <div class="flex justify-between items-center">
        <span class="text-gray-600">Manage school announcements</span>
        <button @click="showAddModal = true" class="btn-primary">
          + New Announcement
        </button>
      </div>
    </div>

    <!-- Announcements List -->
    <div class="space-y-4">
      <div v-for="announcement in announcements" :key="announcement.id" class="card hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-bold text-gray-900">{{ announcement.title }}</h3>
            <p class="text-sm text-gray-500">
              Posted by {{ announcement.author?.name }} on {{ formatDate(announcement.published_at) }}
            </p>
          </div>
          <span v-if="announcement.is_global" class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">
            System-wide
          </span>
        </div>
        <p class="text-gray-600 whitespace-pre-line">{{ announcement.content }}</p>
        
        <div v-if="canManage" class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
          <button @click="editAnnouncement(announcement)" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
          <button @click="deleteAnnouncement(announcement)" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="announcements.length === 0" class="card text-center py-12">
      <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No Announcements</h3>
      <p class="text-gray-500">There are no announcements at this time.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const toast = useToast()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const announcements = ref([])
const showAddModal = ref(false)

const canManage = computed(() => {
  return authStore.hasRole(['super_admin', 'school_admin'])
})

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/announcements`)
    announcements.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch announcements:', error)
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const editAnnouncement = (announcement) => {
  // Implement edit functionality
}

const deleteAnnouncement = async (announcement) => {
  if (!confirm(`Delete announcement "${announcement.title}"?`)) return
  
  try {
    await axios.delete(`${API_BASE_URL}/announcements/${announcement.id}`)
    toast.success('Announcement deleted')
    fetchAnnouncements()
  } catch (error) {
    toast.error('Failed to delete announcement')
  }
}

onMounted(() => {
  fetchAnnouncements()
})
</script>
