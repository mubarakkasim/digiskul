<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Subjects</h1>
        <p class="text-gray-600">Manage school subjects and curriculum</p>
      </div>
      <button @click="openAddModal" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Subject
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card bg-gradient-to-br from-indigo-500 to-indigo-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-indigo-100 text-sm">Total Subjects</p>
            <p class="text-3xl font-bold">{{ subjects.length }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-emerald-500 to-emerald-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-emerald-100 text-sm">Core Subjects</p>
            <p class="text-3xl font-bold">{{ coreSubjectsCount }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-amber-500 to-amber-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-amber-100 text-sm">Elective Subjects</p>
            <p class="text-3xl font-bold">{{ electiveSubjectsCount }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-rose-500 to-rose-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-rose-100 text-sm">Active Teachers</p>
            <p class="text-3xl font-bold">{{ activeTeachersCount }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Subjects Table -->
    <div class="card overflow-hidden">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">All Subjects</h2>
        <div class="flex items-center gap-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search subjects..."
            class="input-field w-64"
          />
          <select v-model="filterType" class="input-field w-40">
            <option value="">All Types</option>
            <option value="core">Core</option>
            <option value="elective">Elective</option>
          </select>
        </div>
      </div>

      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Subject</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Classes</th>
            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="subject in filteredSubjects" :key="subject.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br" :class="getSubjectColor(subject.name)">
                  <div class="w-full h-full flex items-center justify-center text-white font-bold">
                    {{ subject.name.charAt(0) }}
                  </div>
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ subject.name }}</p>
                  <p v-if="subject.description" class="text-sm text-gray-500 truncate max-w-xs">{{ subject.description }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm font-mono">{{ subject.code || 'N/A' }}</span>
            </td>
            <td class="px-6 py-4">
              <span 
                class="px-3 py-1 rounded-full text-xs font-semibold"
                :class="subject.type === 'core' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
              >
                {{ subject.type === 'core' ? 'Core' : 'Elective' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-600">{{ subject.classes_count || 'All' }}</span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button @click="openEditModal(subject)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button @click="confirmDelete(subject)" class="p-2 hover:bg-red-100 rounded-lg text-red-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="filteredSubjects.length === 0 && !loading">
            <td colspan="5" class="px-6 py-12 text-center">
              <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <p class="text-gray-500">No subjects found</p>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">{{ isEditing ? 'Edit Subject' : 'Add New Subject' }}</h3>
          <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveSubject" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name *</label>
            <input
              v-model="form.name"
              type="text"
              class="input-field"
              placeholder="e.g., Mathematics, English Language"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
            <input
              v-model="form.code"
              type="text"
              class="input-field"
              placeholder="e.g., MTH, ENG"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="form.type" class="input-field">
              <option value="core">Core Subject</option>
              <option value="elective">Elective Subject</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              class="input-field"
              rows="3"
              placeholder="Brief description of this subject..."
            ></textarea>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="closeModal" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" class="btn-primary flex-1" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>{{ isEditing ? 'Update Subject' : 'Create Subject' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4">
        <div class="text-center">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Subject?</h3>
          <p class="text-gray-600 mb-6">Are you sure you want to delete "{{ subjectToDelete?.name }}"? This action cannot be undone.</p>
          <div class="flex gap-3">
            <button @click="showDeleteModal = false" class="btn-secondary flex-1">Cancel</button>
            <button @click="deleteSubject" class="btn-danger flex-1" :disabled="deleting">
              <span v-if="deleting">Deleting...</span>
              <span v-else>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()

const subjects = ref([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)

const searchQuery = ref('')
const filterType = ref('')

const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const subjectToDelete = ref(null)

const form = ref({
  id: null,
  name: '',
  code: '',
  type: 'core',
  description: ''
})

const coreSubjectsCount = computed(() => subjects.value.filter(s => s.type === 'core').length)
const electiveSubjectsCount = computed(() => subjects.value.filter(s => s.type === 'elective').length)
const activeTeachersCount = computed(() => {
  const teacherIds = new Set(subjects.value.filter(s => s.teacher_id).map(s => s.teacher_id))
  return teacherIds.size
})

const filteredSubjects = computed(() => {
  let result = subjects.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(s => 
      s.name.toLowerCase().includes(query) || 
      (s.code && s.code.toLowerCase().includes(query))
    )
  }

  if (filterType.value) {
    result = result.filter(s => s.type === filterType.value)
  }

  return result
})

const getSubjectColor = (name) => {
  const colors = [
    'from-blue-500 to-blue-600',
    'from-green-500 to-green-600',
    'from-purple-500 to-purple-600',
    'from-orange-500 to-orange-600',
    'from-pink-500 to-pink-600',
    'from-teal-500 to-teal-600',
    'from-indigo-500 to-indigo-600',
    'from-red-500 to-red-600'
  ]
  const index = name.charCodeAt(0) % colors.length
  return colors[index]
}

const fetchSubjects = async () => {
  loading.value = true
  try {
    const response = await axios.get(`${API_BASE_URL}/subjects`)
    subjects.value = response.data.data || []
  } catch (error) {
    toast.error('Failed to load subjects')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', code: '', type: 'core', description: '' }
  showModal.value = true
}

const openEditModal = (subject) => {
  isEditing.value = true
  form.value = {
    id: subject.id,
    name: subject.name,
    code: subject.code || '',
    type: subject.type || 'core',
    description: subject.description || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.value = { id: null, name: '', code: '', type: 'core', description: '' }
}

const saveSubject = async () => {
  if (!form.value.name.trim()) {
    toast.error('Subject name is required')
    return
  }

  saving.value = true
  try {
    const payload = {
      name: form.value.name,
      code: form.value.code || null,
      type: form.value.type,
      description: form.value.description || null
    }

    if (isEditing.value) {
      await axios.put(`${API_BASE_URL}/subjects/${form.value.id}`, payload)
      toast.success('Subject updated successfully')
    } else {
      await axios.post(`${API_BASE_URL}/subjects`, payload)
      toast.success('Subject created successfully')
    }

    closeModal()
    fetchSubjects()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save subject')
    console.error(error)
  } finally {
    saving.value = false
  }
}

const confirmDelete = (subject) => {
  subjectToDelete.value = subject
  showDeleteModal.value = true
}

const deleteSubject = async () => {
  if (!subjectToDelete.value) return

  deleting.value = true
  try {
    await axios.delete(`${API_BASE_URL}/subjects/${subjectToDelete.value.id}`)
    toast.success('Subject deleted successfully')
    showDeleteModal.value = false
    subjectToDelete.value = null
    fetchSubjects()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete subject')
    console.error(error)
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchSubjects()
})
</script>

<style scoped>
.btn-danger {
  @apply px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium disabled:opacity-50;
}
</style>
