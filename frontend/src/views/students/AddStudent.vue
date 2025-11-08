<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Add Student</h1>
    <p class="text-gray-600 mb-6">Create a new student record</p>
    
    <div class="card max-w-2xl">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input v-model="form.full_name" type="text" required class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Admission Number *</label>
            <input v-model="form.admission_no" type="text" required class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Class *</label>
            <select v-model="form.class_id" required class="input-field">
              <option value="">Select Class</option>
              <option v-for="classItem in classes" :key="classItem.id" :value="classItem.id">
                {{ classItem.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
            <select v-model="form.gender" required class="input-field">
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
            <input v-model="form.date_of_birth" type="date" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Parent Phone</label>
            <input v-model="form.parent_phone" type="tel" class="input-field" />
          </div>
        </div>
        
        <div class="flex gap-3">
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Student' }}
          </button>
          <router-link to="/students" class="btn-secondary">Cancel</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const router = useRouter()
const toast = useToast()

const form = ref({
  full_name: '',
  admission_no: '',
  class_id: '',
  gender: '',
  date_of_birth: '',
  parent_phone: ''
})

const classes = ref([])
const saving = ref(false)

const fetchClasses = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/classes`)
    classes.value = response.data.data
  } catch (error) {
    console.error(error)
  }
}

const handleSubmit = async () => {
  saving.value = true
  try {
    await axios.post(`${API_BASE_URL}/students`, form.value)
    toast.success('Student added successfully!')
    router.push('/students')
  } catch (error) {
    toast.error('Failed to add student')
    console.error(error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchClasses()
})
</script>

