<template>
  <div class="profile-page">
    <div class="page-header">
      <div class="header-content">
        <h1>👤 Super Admin Profile</h1>
        <p>Manage your account settings and credentials</p>
      </div>
    </div>

    <div class="profile-content">
      <!-- Profile Sidebar -->
      <div class="profile-sidebar">
        <div class="card user-card">
          <div class="avatar-large">{{ userInitials }}</div>
          <h2 class="user-name">{{ user.full_name }}</h2>
          <span class="user-role">System Administrator</span>
          <div class="user-meta">
            <div class="meta-item">
              <span class="label">Last Login</span>
              <span class="val">{{ formatDate(user.last_login_at) }}</span>
            </div>
            <div class="meta-item">
              <span class="label">Member Since</span>
              <span class="val">{{ formatDate(user.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="card security-card">
          <h3>🔐 Security Level</h3>
          <p>Your account has unrestricted system access.</p>
          <div class="security-meter">
            <div class="fill" style="width: 100%"></div>
          </div>
          <small>Status: Fully Protected</small>
        </div>
      </div>

      <!-- Main Form Area -->
      <div class="profile-main">
        <div class="card settings-card">
          <div class="tabs">
            <button :class="{ active: activeTab === 'basic' }" @click="activeTab = 'basic'">Basic Info</button>
            <button :class="{ active: activeTab === 'password' }" @click="activeTab = 'password'">Change Password</button>
          </div>

          <!-- Basic Info Tab -->
          <form v-if="activeTab === 'basic'" @submit.prevent="updateProfile" class="settings-form">
            <div class="form-grid">
              <div class="form-group">
                <label>Full Name</label>
                <input v-model="profileForm.full_name" required />
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" v-model="profileForm.email" required />
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <input v-model="profileForm.phone" placeholder="e.g. +234..." />
              </div>
              <div class="form-group">
                <label>Staff ID (System)</label>
                <input v-model="profileForm.staff_id" disabled />
                <small>Staff ID is managed by the core system.</small>
              </div>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn-primary" :disabled="saving">
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>

          <!-- Password Tab -->
          <form v-else @submit.prevent="updatePassword" class="settings-form">
            <div class="form-stacked">
              <div class="form-group">
                <label>Current Password</label>
                <input type="password" v-model="passwordForm.current_password" required />
              </div>
              <div class="form-group">
                <label>New Password</label>
                <input type="password" v-model="passwordForm.new_password" required />
              </div>
              <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" v-model="passwordForm.password_confirmation" required />
              </div>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn-primary" :disabled="saving">
                {{ saving ? 'Updating Password...' : 'Update Password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const toast = useToast()
const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const activeTab = ref('basic')
const saving = ref(false)
const user = computed(() => authStore.user || {})

const userInitials = computed(() => {
  if (!user.value.full_name) return 'SA'
  return user.value.full_name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const profileForm = reactive({
  full_name: '',
  email: '',
  phone: '',
  staff_id: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  password_confirmation: ''
})

const fetchProfile = async () => {
  try {
    // Usually, the profile info comes from the auth user
    Object.assign(profileForm, {
      full_name: user.value.full_name,
      email: user.value.email,
      phone: user.value.phone || '',
      staff_id: user.value.staff_id || 'SA-001'
    })
  } catch (e) {
    console.error(e)
  }
}

const updateProfile = async () => {
  saving.value = true
  try {
    const res = await axios.put(`${API}/profile`, profileForm)
    authStore.user = res.data.data
    toast.success('Profile updated successfully!')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to update profile.')
  } finally {
    saving.value = false
  }
}

const updatePassword = async () => {
  if (passwordForm.new_password !== passwordForm.password_confirmation) {
    return toast.error('Passwords do not match!')
  }
  
  saving.value = true
  try {
    await axios.put(`${API}/password`, passwordForm)
    toast.success('Password updated successfully!')
    Object.assign(passwordForm, { current_password: '', new_password: '', password_confirmation: '' })
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to update password.')
  } finally {
    saving.value = false
  }
}

const formatDate = (d) => d ? new Date(d).toLocaleString() : 'N/A'

onMounted(fetchProfile)
</script>

<style scoped>
.profile-page { max-width: 1200px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.75rem; font-weight: 800; color: #1A4FC4; }
.page-header p { color: #64748B; }

.profile-content { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; }

.card { background: white; border-radius: 20px; border: 1px solid #E2E8F0; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }

/* User Card */
.user-card { text-align: center; margin-bottom: 2rem; }
.avatar-large { 
  width: 100px; height: 100px; background: #1A4FC4; color: white; border-radius: 50%;
  margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center;
  font-size: 2.5rem; font-weight: 800; border: 4px solid #FDBB2B;
}
.user-name { font-size: 1.5rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem; }
.user-role { font-size: 0.85rem; color: #1A4FC4; font-weight: 700; text-transform: uppercase; }

.user-meta { margin-top: 2rem; text-align: left; background: #F8FAFC; padding: 1rem; border-radius: 12px; }
.meta-item { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.meta-item .label { font-size: 0.75rem; color: #64748B; font-weight: 600; }
.meta-item .val { font-size: 0.75rem; color: #1E293B; font-weight: 700; }

/* Security Card */
.security-card h3 { font-size: 1rem; margin-bottom: 0.75rem; color: #1E293B; }
.security-card p { font-size: 0.85rem; color: #64748B; margin-bottom: 1rem; }
.security-meter { height: 6px; background: #F1F5F9; border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem; }
.security-meter .fill { height: 100%; background: #10B981; }
.security-card small { font-size: 0.75rem; color: #10B981; font-weight: 700; }

/* Main Forms */
.tabs { display: flex; gap: 1rem; border-bottom: 1px solid #F1F5F9; margin-bottom: 2rem; }
.tabs button { 
  padding: 1rem 0.5rem; border: none; background: none; font-weight: 700; color: #64748B;
  cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s;
}
.tabs button.active { color: #1A4FC4; border-bottom-color: #FDBB2B; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.form-stacked { display: flex; flex-direction: column; gap: 1.5rem; max-width: 400px; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-size: 0.85rem; font-weight: 700; color: #475569; }
.form-group input { padding: 0.75rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; }

.form-footer { margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #F1F5F9; }
.btn-primary { background: #1A4FC4; color: white; border: none; padding: 0.75rem 2rem; border-radius: 10px; font-weight: 700; cursor: pointer; }
.btn-primary:active { opacity: 0.8; }
</style>
