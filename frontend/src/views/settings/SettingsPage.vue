<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings</h1>
    <p class="text-gray-600 mb-6">Manage your account and system preferences</p>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Profile Settings -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Settings</h2>
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input v-model="profile.name" type="text" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input v-model="profile.email" type="email" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input v-model="profile.phone" type="tel" class="input-field" />
            </div>
            <button type="submit" class="btn-primary">Update Profile</button>
          </form>
        </div>

        <!-- Password Change -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>
          <form @submit.prevent="changePassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
              <input v-model="password.current" type="password" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
              <input v-model="password.new" type="password" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
              <input v-model="password.confirm" type="password" class="input-field" />
            </div>
            <button type="submit" class="btn-primary">Change Password</button>
          </form>
        </div>
      </div>

      <!-- Preferences -->
      <div>
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Preferences</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('settings.language') }}</label>
              <select v-model="preferences.language" @change="changeLanguage" class="input-field">
                <option value="en">English</option>
                <option value="ha">Hausa</option>
                <option value="ar">العربية</option>
                <option value="fr">Français</option>
              </select>
            </div>
            <div>
              <label class="flex items-center">
                <input v-model="preferences.notifications" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                <span class="ml-2 text-sm text-gray-700">Enable Notifications</span>
              </label>
            </div>
            <button @click="savePreferences" class="btn-primary w-full">Save Preferences</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../../stores/auth'
import { useI18n } from 'vue-i18n'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()
const authStore = useAuthStore()
const { locale } = useI18n()

const profile = ref({
  name: '',
  email: '',
  phone: ''
})

const password = ref({
  current: '',
  new: '',
  confirm: ''
})

const preferences = ref({
  language: 'en',
  notifications: true
})

const updateProfile = async () => {
  try {
    await axios.put(`${API_BASE_URL}/settings/profile`, profile.value)
    toast.success('Profile updated successfully!')
    authStore.fetchUser()
  } catch (error) {
    toast.error('Failed to update profile')
    console.error(error)
  }
}

const changePassword = async () => {
  if (password.value.new !== password.value.confirm) {
    toast.error('New passwords do not match')
    return
  }

  try {
    await axios.post(`${API_BASE_URL}/settings/password`, {
      current_password: password.value.current,
      new_password: password.value.new
    })
    toast.success('Password changed successfully!')
    password.value = { current: '', new: '', confirm: '' }
  } catch (error) {
    toast.error('Failed to change password')
    console.error(error)
  }
}

const changeLanguage = async () => {
  locale.value = preferences.value.language
  localStorage.setItem('locale', preferences.value.language)
  try {
    await axios.post(`${API_BASE_URL}/locale`, { locale: preferences.value.language })
  } catch (error) {
    console.error('Failed to update locale on server:', error)
  }
}

const savePreferences = async () => {
  try {
    await axios.put(`${API_BASE_URL}/settings/preferences`, preferences.value)
    toast.success('Preferences saved successfully!')
  } catch (error) {
    toast.error('Failed to save preferences')
    console.error(error)
  }
}

onMounted(async () => {
  if (authStore.user) {
    profile.value = {
      name: authStore.user.name || '',
      email: authStore.user.email || '',
      phone: authStore.user.phone || ''
    }
    
    // Load user preferences
    const savedLocale = authStore.user.meta?.locale || localStorage.getItem('locale') || 'en'
    preferences.value.language = savedLocale
    locale.value = savedLocale
  }
})
</script>

