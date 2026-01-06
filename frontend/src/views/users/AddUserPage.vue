<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Add New User</h1>
      <p class="text-gray-600">Create a new user account</p>
    </div>
    <div class="card max-w-2xl">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input v-model="form.email" type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="form.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Staff ID</label>
            <input v-model="form.staff_id" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <select v-model="form.role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="">Select Role</option>
              <option value="school_admin">School Admin</option>
              <option value="class_teacher">Class Teacher</option>
              <option value="teacher">Teacher</option>
              <option value="student">Student</option>
              <option value="parent">Parent</option>
              <option value="bursar">Bursar</option>
              <option value="librarian">Librarian</option>
              <option value="ict_officer">ICT Officer</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
            <input v-model="form.password" type="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
        <div class="flex gap-4">
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Creating...' : 'Create User' }}
          </button>
          <router-link to="/users" class="btn-secondary">Cancel</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const loading = ref(false)
const form = ref({
  name: '',
  email: '',
  phone: '',
  staff_id: '',
  role: '',
  password: '',
})

const handleSubmit = async () => {
  loading.value = true
  try {
    await axios.post(`${API_BASE_URL}/users`, form.value)
    toast.success('User created successfully')
    router.push('/users')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to create user')
  } finally {
    loading.value = false
  }
}
</script>
