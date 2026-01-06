<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">User Management</h1>
      <p class="text-gray-600">Manage teachers, staff, students, and parents</p>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search users..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @input="debounceSearch"
          />
        </div>
        <select v-model="selectedRole" class="px-4 py-2 border border-gray-300 rounded-lg" @change="fetchUsers">
          <option value="">All Roles</option>
          <option value="school_admin">School Admin</option>
          <option value="class_teacher">Class Teacher</option>
          <option value="teacher">Teacher</option>
          <option value="student">Student</option>
          <option value="parent">Parent</option>
          <option value="bursar">Bursar</option>
          <option value="librarian">Librarian</option>
          <option value="ict_officer">ICT Officer</option>
        </select>
        <router-link to="/users/add" class="btn-primary">
          + Add User
        </router-link>
      </div>
    </div>

    <!-- Role Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-6">
      <div v-for="(count, role) in roleStats" :key="role" 
        class="card text-center cursor-pointer hover:shadow-md transition-shadow"
        :class="{ 'ring-2 ring-blue-500': selectedRole === role }"
        @click="selectedRole = role; fetchUsers()"
      >
        <p class="text-2xl font-bold text-gray-900">{{ count }}</p>
        <p class="text-xs text-gray-500 capitalize">{{ role.replace('_', ' ') }}s</p>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">User</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Role</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Staff ID</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Phone</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ getInitials(user.name) }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">{{ user.name }}</p>
                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getRoleBadgeClass(user.role)">
                  {{ formatRole(user.role) }}
                </span>
              </td>
              <td class="py-4 px-4 text-gray-600">{{ user.staff_id || '-' }}</td>
              <td class="py-4 px-4 text-gray-600">{{ user.phone || '-' }}</td>
              <td class="py-4 px-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="user.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ user.active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-4 px-4">
                <div class="flex gap-2">
                  <router-link :to="`/users/${user.id}/edit`" class="text-blue-600 hover:text-blue-800">Edit</router-link>
                  <button @click="toggleUserStatus(user)" class="text-gray-600 hover:text-gray-800">
                    {{ user.active ? 'Deactivate' : 'Activate' }}
                  </button>
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

const users = ref([])
const searchQuery = ref('')
const selectedRole = ref('')
const roleStats = ref({})

let searchTimeout = null

const fetchUsers = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedRole.value) params.role = selectedRole.value
    
    const response = await axios.get(`${API_BASE_URL}/users`, { params })
    users.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch users:', error)
    toast.error('Failed to load users')
  }
}

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchUsers, 300)
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const formatRole = (role) => {
  const names = {
    'super_admin': 'Super Admin',
    'school_admin': 'School Admin',
    'class_teacher': 'Class Teacher',
    'teacher': 'Teacher',
    'student': 'Student',
    'parent': 'Parent',
    'bursar': 'Bursar',
    'librarian': 'Librarian',
    'ict_officer': 'ICT Officer',
  }
  return names[role] || role
}

const getRoleBadgeClass = (role) => {
  const classes = {
    'super_admin': 'bg-purple-100 text-purple-800',
    'school_admin': 'bg-blue-100 text-blue-800',
    'class_teacher': 'bg-green-100 text-green-800',
    'teacher': 'bg-teal-100 text-teal-800',
    'student': 'bg-yellow-100 text-yellow-800',
    'parent': 'bg-orange-100 text-orange-800',
    'bursar': 'bg-pink-100 text-pink-800',
    'librarian': 'bg-indigo-100 text-indigo-800',
    'ict_officer': 'bg-gray-100 text-gray-800',
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const toggleUserStatus = async (user) => {
  const action = user.active ? 'deactivate' : 'activate'
  if (!confirm(`Are you sure you want to ${action} ${user.name}?`)) return
  
  try {
    await axios.put(`${API_BASE_URL}/users/${user.id}`, { active: !user.active })
    toast.success(`User ${action}d successfully`)
    fetchUsers()
  } catch (error) {
    toast.error(`Failed to ${action} user`)
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
