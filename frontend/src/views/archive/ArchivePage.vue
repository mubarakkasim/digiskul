<template>
  <div>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Archive</h1>
        <p class="text-gray-600">Access completed term records</p>
      </div>
      <button @click="showArchiveModal = true" class="btn-primary">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
        </svg>
        Archive Current Term
      </button>
    </div>

    <!-- Archived Terms -->
    <div class="space-y-4">
      <div
        v-for="archive in archivedTerms"
        :key="archive.id"
        class="card flex items-center justify-between"
      >
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h3 class="text-lg font-semibold text-gray-900">{{ archive.term }} - {{ archive.session }}</h3>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
              Archived
            </span>
          </div>
          <div class="grid grid-cols-3 gap-4 text-sm text-gray-600">
            <div>
              <span class="font-medium">Archived on:</span> {{ formatDate(archive.archived_at) }}
            </div>
            <div>
              <span class="font-medium">Students:</span> {{ archive.student_count }}
            </div>
            <div>
              <span class="font-medium">Size:</span> {{ archive.size }}
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click="viewArchive(archive.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
          <button @click="downloadArchive(archive.id)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </button>
          <button @click="deleteArchive(archive.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Archive Modal -->
    <div v-if="showArchiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Archive Current Term</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to archive the current term? This action cannot be undone.</p>
        <div class="flex gap-3">
          <button @click="confirmArchive" class="btn-primary flex-1">Confirm Archive</button>
          <button @click="showArchiveModal = false" class="btn-secondary flex-1">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { format } from 'date-fns'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()

const archivedTerms = ref([])
const showArchiveModal = ref(false)

const formatDate = (date) => {
  return format(new Date(date), 'dd/MM/yyyy')
}

const fetchArchivedTerms = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/archive`)
    archivedTerms.value = response.data.data
  } catch (error) {
    toast.error('Failed to fetch archived terms')
    console.error(error)
  }
}

const viewArchive = (id) => {
  // Navigate to archive detail view
  console.log('View archive:', id)
}

const downloadArchive = async (id) => {
  try {
    const response = await axios.get(`${API_BASE_URL}/archive/${id}/download`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `archive-${id}.zip`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast.success('Archive downloaded successfully!')
  } catch (error) {
    toast.error('Failed to download archive')
    console.error(error)
  }
}

const deleteArchive = async (id) => {
  if (!confirm('Are you sure you want to delete this archive?')) return

  try {
    await axios.delete(`${API_BASE_URL}/archive/${id}`)
    toast.success('Archive deleted successfully!')
    fetchArchivedTerms()
  } catch (error) {
    toast.error('Failed to delete archive')
    console.error(error)
  }
}

const confirmArchive = async () => {
  try {
    await axios.post(`${API_BASE_URL}/archive/current-term`)
    toast.success('Current term archived successfully!')
    showArchiveModal.value = false
    fetchArchivedTerms()
  } catch (error) {
    toast.error('Failed to archive current term')
    console.error(error)
  }
}

onMounted(() => {
  fetchArchivedTerms()
})
</script>

