<template>
  <div class="schools-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1>🏫 School Management</h1>
        <p>Register and manage institutional partners</p>
      </div>
      <button class="btn-primary" @click="openAddModal">+ Register New School</button>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="label">Total Schools</span>
        <span class="value">{{ schools.length }}</span>
      </div>
      <div class="stat-item success">
        <span class="label">Active</span>
        <span class="value">{{ schools.filter(s => s.active).length }}</span>
      </div>
      <div class="stat-item danger">
        <span class="label">Suspended</span>
        <span class="value">{{ schools.filter(s => !s.active).length }}</span>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-card">
      <div class="search-box">
        <span class="icon">🔍</span>
        <input v-model="search" placeholder="Search by name or subdomain..." />
      </div>
      <div class="filter-actions">
        <select v-model="statusFilter">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>
        <button class="btn-outline" @click="fetchSchools">🔄 Refresh</button>
      </div>
    </div>

    <!-- Schools Table -->
    <div class="table-card" v-if="!loading">
      <table>
        <thead>
          <tr>
            <th>Institution</th>
            <th>Contact</th>
            <th>Subdomain</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="school in filteredSchools" :key="school.id">
            <td>
              <div class="school-cell">
                <div class="logo-avatar">{{ school.name[0] }}</div>
                <div class="info">
                  <span class="name">{{ school.name }}</span>
                  <span class="id">ID: #{{ school.id }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="contact-info">
                <span class="email">{{ school.email }}</span>
                <span class="phone">{{ school.phone }}</span>
              </div>
            </td>
            <td><code>{{ school.subdomain }}</code></td>
            <td><span class="plan-badge" :class="school.subscription_plan">{{ school.subscription_plan }}</span></td>
            <td>
              <span class="status-pill" :class="school.active ? 'active' : 'suspended'">
                {{ school.active ? 'Active' : 'Suspended' }}
              </span>
            </td>
            <td class="actions">
              <button class="action-btn" title="Edit" @click="editSchool(school)">✏️</button>
              <button class="action-btn danger" @click="toggleStatus(school)">
                {{ school.active ? '⏸️' : '▶️' }}
              </button>
              <button class="action-btn" @click="viewLogs(school)">📋</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!filteredSchools.length" class="empty-state">
        No schools found matching your criteria.
      </div>
    </div>
    <div v-else class="loading-state">
      <div class="loader"></div>
      <p>Fetching school data...</p>
    </div>

    <!-- Register/Edit Modal -->
    <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Edit School' : 'Register New School' }}</h2>
          <button @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveSchool" class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>School Name *</label>
              <input v-model="form.name" required placeholder="e.g. Nur Light Academy" />
            </div>
            <div class="form-group">
              <label>Subdomain *</label>
              <div class="subdomain-input">
                <input v-model="form.subdomain" required placeholder="nurlight" :disabled="isEditing" />
                <span>.digiskul.app</span>
              </div>
            </div>
            <div class="form-group">
              <label>Email Address *</label>
              <input type="email" v-model="form.email" required placeholder="admin@school.com" />
              <small v-if="!isEditing">This will be the primary admin email.</small>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input v-model="form.phone" placeholder="+234..." />
            </div>
            <div class="form-group">
              <label>Subscription Plan</label>
              <select v-model="form.subscription_plan">
                <option value="basic">Basic</option>
                <option value="standard">Standard</option>
                <option value="premium">Premium</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>
            <div class="form-group">
              <label>License Valid Until</label>
              <input type="date" v-model="form.license_valid_until" />
            </div>
            <div class="form-group full">
              <label>Address</label>
              <textarea v-model="form.address" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-outline" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : (isEditing ? 'Update School' : 'Register School') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const schools = ref([])
const search = ref('')
const statusFilter = ref('')
const loading = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)

const form = reactive({
  id: null,
  name: '',
  subdomain: '',
  email: '',
  phone: '',
  address: '',
  subscription_plan: 'standard',
  license_valid_until: '',
  active: true
})

const fetchSchools = async () => {
  loading.value = true
  try {
    const res = await axios.get(`${API}/super-admin/schools`)
    schools.value = res.data.data.data || res.data.data
  } catch (e) {
    toast.error('Error fetching schools. Check your internet or backend.')
    console.error(e)
  } finally {
    loading.value = false
  }
}

const filteredSchools = computed(() => {
  return schools.value.filter(s => {
    const matchesSearch = s.name.toLowerCase().includes(search.value.toLowerCase()) || 
                         s.subdomain.toLowerCase().includes(search.value.toLowerCase())
    const matchesStatus = statusFilter.value === '' || 
                         (statusFilter.value === 'active' ? s.active : !s.active)
    return matchesSearch && matchesStatus
  })
})

const openAddModal = () => {
  isEditing.value = false
  Object.assign(form, {
    id: null,
    name: '',
    subdomain: '',
    email: '',
    phone: '',
    address: '',
    subscription_plan: 'standard',
    license_valid_until: new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().split('T')[0],
    active: true
  })
  showModal.value = true
}

const editSchool = (school) => {
  isEditing.value = true
  Object.assign(form, {
    ...school,
    license_valid_until: school.license_valid_until ? school.license_valid_until.split('T')[0] : ''
  })
  showModal.value = true
}

const saveSchool = async () => {
  saving.value = true
  try {
    if (isEditing.value) {
      await axios.put(`${API}/super-admin/schools/${form.id}`, form)
      toast.success('School updated successfully!')
    } else {
      await axios.post(`${API}/super-admin/schools`, form)
      toast.success('School registered successfully!')
    }
    showModal.value = false
    fetchSchools()
  } catch (e) {
    const msg = e.response?.data?.message || 'Error saving school.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}

const toggleStatus = async (school) => {
  if (!confirm(`Are you sure you want to ${school.active ? 'suspend' : 'activate'} ${school.name}?`)) return
  
  try {
    const endpoint = school.active ? 'suspend' : 'activate'
    await axios.post(`${API}/super-admin/schools/${school.id}/${endpoint}`)
    toast.success(`${school.name} ${school.active ? 'suspended' : 'activated'}!`)
    fetchSchools()
  } catch (e) {
    toast.error('Failed to update status.')
  }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : 'N/A'

onMounted(fetchSchools)
</script>

<style scoped>
.schools-page { max-width: 1400px; margin: 0 auto; }

.page-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;
}

.header-content h1 { font-size: 1.75rem; font-weight: 800; color: #1A4FC4; }
.header-content p { color: #64748B; }

.stats-bar {
  display: flex; gap: 1rem; margin-bottom: 1.5rem;
}
.stat-item {
  background: white; padding: 1rem 1.5rem; border-radius: 12px; flex: 1;
  border: 1px solid #E2E8F0;
}
.stat-item .label { display: block; font-size: 0.75rem; color: #64748B; font-weight: 700; text-transform: uppercase; }
.stat-item .value { font-size: 1.5rem; font-weight: 800; color: #1E293B; }
.stat-item.success .value { color: #10B981; }
.stat-item.danger .value { color: #EF4444; }

.filters-card {
  background: white; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;
  display: flex; justify-content: space-between; border: 1px solid #E2E8F0;
}

.search-box {
  background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem 1rem;
  display: flex; align-items: center; gap: 0.75rem; width: 350px;
}
.search-box input { border: none; background: transparent; width: 100%; outline: none; }

.table-card {
  background: white; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 1.25rem 1.5rem; background: #F8FAFC; color: #64748B; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
td { padding: 1.25rem 1.5rem; border-top: 1px solid #F1F5F9; color: #1E293B; }

.school-cell { display: flex; align-items: center; gap: 1rem; }
.logo-avatar { width: 42px; height: 42px; background: #1A4FC4; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.info .name { display: block; font-weight: 700; color: #1E293B; }
.info .id { font-size: 0.7rem; color: #94A3B8; }

.contact-info .email { display: block; font-size: 0.85rem; color: #1E293B; }
.contact-info .phone { font-size: 0.75rem; color: #64748B; }

.plan-badge { padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
.plan-badge.premium { background: rgba(253, 187, 43, 0.1); color: #B45309; }
.plan-badge.standard { background: rgba(26, 79, 196, 0.1); color: #1A4FC4; }

.status-pill { padding: 0.25rem 0.75rem; border-radius: 30px; font-weight: 700; font-size: 0.7rem; }
.status-pill.active { background: rgba(16, 185, 129, 0.1); color: #10B981; }
.status-pill.suspended { background: rgba(239, 68, 68, 0.1); color: #EF4444; }

.btn-primary { background: #1A4FC4; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer; }
.btn-primary:active { opacity: 0.8; }
.btn-outline { background: white; border: 1px solid #E2E8F0; padding: 0.5rem 1rem; border-radius: 8px; color: #64748B; cursor: pointer; font-weight: 600; }

.action-btn { background: #F8FAFC; border: 1px solid #E2E8F0; width: 34px; height: 34px; border-radius: 8px; margin-right: 0.5rem; cursor: pointer; font-size: 0.9rem; }
.action-btn:hover { background: #F1F5F9; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5); 
  display: flex; align-items: center; justify-content: center; z-index: 2000;
}
.modal {
  background: white; width: 650px; border-radius: 20px; overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}
.modal-header {
  padding: 1.5rem; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;
}
.modal-header h2 { font-size: 1.25rem; font-weight: 800; color: #1E293B; }
.modal-header button { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94A3B8; }

.modal-body { padding: 2rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group.full { grid-column: span 2; }
.form-group label { font-size: 0.85rem; font-weight: 700; color: #475569; }
.form-group input, .form-group select, .form-group textarea {
  padding: 0.75rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 0.9rem;
}
.subdomain-input { display: flex; align-items: center; border: 1.5px solid #E2E8F0; border-radius: 10px; overflow: hidden; }
.subdomain-input input { border: none; flex: 1; padding: 0.75rem; }
.subdomain-input span { background: #F8FAFC; padding: 0.75rem; border-left: 1px solid #E2E8F0; color: #64748B; font-weight: 600; }

.modal-footer {
  padding: 1.5rem 2rem; background: #F8FAFC; display: flex; justify-content: flex-end; gap: 1rem;
}

.empty-state, .loading-state { padding: 4rem; text-align: center; color: #64748B; }
.loader { border: 4px solid #f3f3f3; border-top: 4px solid #1A4FC4; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
