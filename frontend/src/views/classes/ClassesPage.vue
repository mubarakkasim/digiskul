<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Classes</h1>
        <p class="text-gray-600">Manage school classes and arms</p>
      </div>
      <button @click="openAddModal" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Class
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-100 text-sm">Total Classes</p>
            <p class="text-3xl font-bold">{{ classes.length }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-green-500 to-green-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm">Total Students</p>
            <p class="text-3xl font-bold">{{ totalStudents }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-100 text-sm">Active Teachers</p>
            <p class="text-3xl font-bold">{{ activeTeachers }}</p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="classItem in classes"
        :key="classItem.id"
        class="card hover:shadow-lg transition-shadow duration-200 cursor-pointer group"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
              <span class="text-xl font-bold text-blue-600">{{ getClassInitial(classItem.name) }}</span>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ classItem.name }}</h3>
              <p class="text-sm text-gray-500">{{ classItem.arm || 'No arm' }}</p>
            </div>
          </div>
          <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click.stop="openEditModal(classItem)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click.stop="confirmDelete(classItem)" class="p-2 hover:bg-red-100 rounded-lg text-red-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span>{{ classItem.students_count || 0 }} students</span>
            </div>
            <div v-if="classItem.teacher" class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>{{ classItem.teacher?.name || 'No teacher' }}</span>
            </div>
          </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-1">
          <span v-for="subject in (classItem.subjects || []).slice(0, 3)" :key="subject.id" class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
            {{ subject.name }}
          </span>
          <span v-if="(classItem.subjects || []).length > 3" class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
            +{{ classItem.subjects.length - 3 }} more
          </span>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="classes.length === 0 && !loading" class="col-span-full">
        <div class="card text-center py-12">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <h3 class="text-lg font-semibold text-gray-700 mb-2">No Classes Added</h3>
          <p class="text-gray-500 mb-4">Get started by creating your first class</p>
          <button @click="openAddModal" class="btn-primary">Add Your First Class</button>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="col-span-full flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">{{ isEditing ? 'Edit Class' : 'Add New Class' }}</h3>
          <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveClass" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Class Name *</label>
            <input
              v-model="form.name"
              type="text"
              class="input-field"
              placeholder="e.g., JSS 1, SS 2, Primary 3"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arm (Optional)</label>
            <input
              v-model="form.arm"
              type="text"
              class="input-field"
              placeholder="e.g., A, B, Gold, Silver"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Class Teacher (Optional)</label>
            <select v-model="form.teacher_id" class="input-field">
              <option value="">Select Teacher</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
            <textarea
              v-model="form.description"
              class="input-field"
              rows="3"
              placeholder="Brief description of this class..."
            ></textarea>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="closeModal" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" class="btn-primary flex-1" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>{{ isEditing ? 'Update Class' : 'Create Class' }}</span>
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
          <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Class?</h3>
          <p class="text-gray-600 mb-6">Are you sure you want to delete "{{ classToDelete?.name }}"? This action cannot be undone.</p>
          <div class="flex gap-3">
            <button @click="showDeleteModal = false" class="btn-secondary flex-1">Cancel</button>
            <button @click="deleteClass" class="btn-danger flex-1" :disabled="deleting">
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

const classes = ref([])
const teachers = ref([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)

const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const classToDelete = ref(null)

const form = ref({
  id: null,
  name: '',
  arm: '',
  teacher_id: '',
  description: ''
})

const totalStudents = computed(() => {
  return classes.value.reduce((sum, c) => sum + (c.students_count || 0), 0)
})

const activeTeachers = computed(() => {
  const teacherIds = new Set(classes.value.filter(c => c.teacher_id).map(c => c.teacher_id))
  return teacherIds.size
})

const getClassInitial = (name) => {
  return name.charAt(0).toUpperCase()
}

const fetchClasses = async () => {
  loading.value = true
  try {
    const response = await axios.get(`${API_BASE_URL}/classes`)
    classes.value = response.data.data || []
  } catch (error) {
    toast.error('Failed to load classes')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchTeachers = async () => {
  try {
    // Using a placeholder endpoint - adjust based on actual API
    const response = await axios.get(`${API_BASE_URL}/teachers`)
    teachers.value = response.data.data || []
  } catch (error) {
    console.log('Teachers endpoint not available')
    teachers.value = []
  }
}

const openAddModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', arm: '', teacher_id: '', description: '' }
  showModal.value = true
}

const openEditModal = (classItem) => {
  isEditing.value = true
  form.value = {
    id: classItem.id,
    name: classItem.name,
    arm: classItem.arm || '',
    teacher_id: classItem.teacher_id || '',
    description: classItem.description || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.value = { id: null, name: '', arm: '', teacher_id: '', description: '' }
}

const saveClass = async () => {
  if (!form.value.name.trim()) {
    toast.error('Class name is required')
    return
  }

  saving.value = true
  try {
    const payload = {
      name: form.value.name,
      arm: form.value.arm || null,
      teacher_id: form.value.teacher_id || null,
      description: form.value.description || null
    }

    if (isEditing.value) {
      await axios.put(`${API_BASE_URL}/classes/${form.value.id}`, payload)
      toast.success('Class updated successfully')
    } else {
      await axios.post(`${API_BASE_URL}/classes`, payload)
      toast.success('Class created successfully')
    }

    closeModal()
    fetchClasses()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save class')
    console.error(error)
  } finally {
    saving.value = false
  }
}

const confirmDelete = (classItem) => {
  classToDelete.value = classItem
  showDeleteModal.value = true
}

const deleteClass = async () => {
  if (!classToDelete.value) return

  deleting.value = true
  try {
    await axios.delete(`${API_BASE_URL}/classes/${classToDelete.value.id}`)
    toast.success('Class deleted successfully')
    showDeleteModal.value = false
    classToDelete.value = null
    fetchClasses()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete class')
    console.error(error)
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchClasses()
  fetchTeachers()
})
</script>

<style scoped>
.btn-danger {
  @apply px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium disabled:opacity-50;
}
</style>
