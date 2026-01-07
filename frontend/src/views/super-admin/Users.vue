<template>
  <div class="users-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1>👥 Global User Directory</h1>
        <p>Manage users across all schools</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-item">
        <span class="stat-value">{{ formatNumber(stats.total_users) }}</span>
        <span class="stat-label">Total Users</span>
      </div>
      <div class="stat-item success">
        <span class="stat-value">{{ formatNumber(stats.active_users) }}</span>
        <span class="stat-label">Active</span>
      </div>
      <div class="stat-item danger">
        <span class="stat-value">{{ stats.suspended_users }}</span>
        <span class="stat-label">Suspended</span>
      </div>
      <div class="stat-item info">
        <span class="stat-value">{{ stats.recent_logins }}</span>
        <span class="stat-label">Online (24h)</span>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search users by name, email, or staff ID..."
          @input="debouncedSearch"
        />
      </div>
      <div class="filter-group">
        <select v-model="roleFilter" @change="fetchUsers">
          <option value="">All Roles</option>
          <option value="super_admin">Super Admin</option>
          <option value="school_admin">School Admin</option>
          <option value="teacher">Teacher</option>
          <option value="class_teacher">Class Teacher</option>
          <option value="student">Student</option>
          <option value="parent">Parent</option>
          <option value="bursar">Bursar</option>
        </select>
        <select v-model="statusFilter" @change="fetchUsers">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>
        <select v-model="schoolFilter" @change="fetchUsers">
          <option value="">All Schools</option>
          <option v-for="school in schools" :key="school.id" :value="school.id">
            {{ school.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-card">
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>School</th>
              <th>Staff ID</th>
              <th>Last Login</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td class="user-cell">
                <div class="user-info">
                  <div class="user-avatar" :class="getRoleColor(user.role)">
                    {{ getInitials(user.full_name) }}
                  </div>
                  <div>
                    <span class="user-name">{{ user.full_name }}</span>
                    <span class="user-email">{{ user.email }}</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="role-badge" :class="user.role">
                  {{ formatRole(user.role) }}
                </span>
              </td>
              <td>
                <span class="school-name">{{ user.school?.name || 'N/A' }}</span>
              </td>
              <td>{{ user.staff_id || '-' }}</td>
              <td class="last-login">
                {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}
              </td>
              <td>
                <span class="status-badge" :class="user.active ? 'active' : 'suspended'">
                  {{ user.active ? 'Active' : 'Suspended' }}
                </span>
              </td>
              <td class="actions-cell">
                <button @click="viewUser(user)" class="action-btn" title="View">👁️</button>
                <button @click="editUser(user)" class="action-btn" title="Edit">✏️</button>
                <button 
                  v-if="user.role !== 'super_admin'"
                  @click="impersonateUser(user)" 
                  class="action-btn warning" 
                  title="Impersonate"
                >🎭</button>
                <button 
                  v-if="user.active && user.role !== 'super_admin'" 
                  @click="suspendUser(user)" 
                  class="action-btn danger" 
                  title="Suspend"
                >⏸️</button>
                <button 
                  v-else-if="!user.active"
                  @click="activateUser(user)" 
                  class="action-btn success" 
                  title="Activate"
                >▶️</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="pagination.last_page > 1">
        <button 
          @click="goToPage(pagination.current_page - 1)" 
          :disabled="pagination.current_page === 1"
          class="page-btn"
        >← Prev</button>
        <span class="page-info">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        <button 
          @click="goToPage(pagination.current_page + 1)" 
          :disabled="pagination.current_page === pagination.last_page"
          class="page-btn"
        >Next →</button>
      </div>
    </div>

    <!-- User Detail Modal -->
    <div class="modal-overlay" v-if="showUserModal" @click.self="showUserModal = false">
      <div class="modal large">
        <div class="modal-header">
          <h2>{{ selectedUser?.full_name }}</h2>
          <button @click="showUserModal = false" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="user-detail-grid">
            <div class="detail-section">
              <h3>Basic Information</h3>
              <div class="detail-list">
                <div class="detail-item">
                  <span class="label">Email</span>
                  <span class="value">{{ selectedUser?.email }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Phone</span>
                  <span class="value">{{ selectedUser?.phone || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Staff ID</span>
                  <span class="value">{{ selectedUser?.staff_id || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Role</span>
                  <span class="value">
                    <span class="role-badge" :class="selectedUser?.role">
                      {{ formatRole(selectedUser?.role) }}
                    </span>
                  </span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h3>School & Account</h3>
              <div class="detail-list">
                <div class="detail-item">
                  <span class="label">School</span>
                  <span class="value">{{ selectedUser?.school?.name || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Status</span>
                  <span class="value">
                    <span class="status-badge" :class="selectedUser?.active ? 'active' : 'suspended'">
                      {{ selectedUser?.active ? 'Active' : 'Suspended' }}
                    </span>
                  </span>
                </div>
                <div class="detail-item">
                  <span class="label">Created At</span>
                  <span class="value">{{ formatDate(selectedUser?.created_at) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Last Login</span>
                  <span class="value">{{ selectedUser?.last_login_at ? formatDate(selectedUser?.last_login_at) : 'Never' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="quick-actions">
            <button @click="resetPassword" class="action-chip">🔑 Reset Password</button>
            <button @click="forceLogout" class="action-chip">🚪 Force Logout</button>
            <button v-if="selectedUser?.role !== 'super_admin'" @click="transferUser" class="action-chip">🔄 Transfer School</button>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showUserModal = false" class="btn-secondary">Close</button>
          <button @click="editUser(selectedUser)" class="btn-primary">Edit User</button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" v-if="showEditModal" @click.self="showEditModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Edit User</h2>
          <button @click="showEditModal = false" class="close-btn">×</button>
        </div>
        <form @submit.prevent="saveUser" class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" v-model="editForm.full_name" required />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" v-model="editForm.email" required />
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="tel" v-model="editForm.phone" />
            </div>
            <div class="form-group">
              <label>Staff ID</label>
              <input type="text" v-model="editForm.staff_id" />
            </div>
            <div class="form-group">
              <label>Role</label>
              <select v-model="editForm.role">
                <option value="school_admin">School Admin</option>
                <option value="teacher">Teacher</option>
                <option value="class_teacher">Class Teacher</option>
                <option value="student">Student</option>
                <option value="parent">Parent</option>
                <option value="bursar">Bursar</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" @click="showEditModal = false" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Impersonation Modal -->
    <div class="modal-overlay" v-if="showImpersonateModal" @click.self="showImpersonateModal = false">
      <div class="modal small">
        <div class="modal-header">
          <h2>🎭 Impersonate User</h2>
          <button @click="showImpersonateModal = false" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="warning-banner">
            <p>⚠️ You are about to impersonate <strong>{{ impersonateTarget?.full_name }}</strong>.</p>
            <p>All actions will be logged for audit purposes.</p>
          </div>
          <div class="form-group">
            <label>Reason for Impersonation *</label>
            <textarea 
              v-model="impersonateReason" 
              required 
              rows="3"
              placeholder="Explain why you need to impersonate this user..."
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showImpersonateModal = false" class="btn-secondary">Cancel</button>
          <button 
            @click="confirmImpersonate" 
            class="btn-warning" 
            :disabled="!impersonateReason || saving"
          >
            {{ saving ? 'Starting...' : 'Start Impersonation' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useRouter } from 'vue-router'

const toast = useToast()
const router = useRouter()
const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const users = ref([])
const schools = ref([])
const stats = ref({
  total_users: 0,
  active_users: 0,
  suspended_users: 0,
  recent_logins: 0,
})
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const loading = ref(false)
const saving = ref(false)

const searchQuery = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
const schoolFilter = ref('')

const showUserModal = ref(false)
const showEditModal = ref(false)
const showImpersonateModal = ref(false)
const selectedUser = ref(null)
const impersonateTarget = ref(null)
const impersonateReason = ref('')

const editForm = reactive({
  id: null,
  full_name: '',
  email: '',
  phone: '',
  staff_id: '',
  role: '',
})

const fetchUsers = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams({ page, per_page: 20 })
    
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (roleFilter.value) params.append('role', roleFilter.value)
    if (statusFilter.value) params.append('active', statusFilter.value === 'active' ? '1' : '0')
    if (schoolFilter.value) params.append('school_id', schoolFilter.value)

    const response = await axios.get(`${API_BASE}/super-admin/users?${params}`)
    const data = response.data.data
    
    users.value = data.data || data
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      total: data.total || users.value.length,
    }
  } catch (error) {
    console.error('Failed to fetch users:', error)
    toast.error('Failed to load users')
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get(`${API_BASE}/super-admin/users/stats`)
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

const fetchSchools = async () => {
  try {
    const response = await axios.get(`${API_BASE}/super-admin/schools?per_page=100`)
    schools.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Failed to fetch schools:', error)
  }
}

const debouncedSearch = (() => {
  let timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fetchUsers(), 300)
  }
})()

const viewUser = (user) => {
  selectedUser.value = user
  showUserModal.value = true
}

const editUser = (user) => {
  showUserModal.value = false
  Object.assign(editForm, {
    id: user.id,
    full_name: user.full_name,
    email: user.email,
    phone: user.phone || '',
    staff_id: user.staff_id || '',
    role: user.role,
  })
  showEditModal.value = true
}

const saveUser = async () => {
  try {
    saving.value = true
    await axios.put(`${API_BASE}/super-admin/users/${editForm.id}`, editForm)
    toast.success('User updated successfully')
    showEditModal.value = false
    fetchUsers()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to update user')
  } finally {
    saving.value = false
  }
}

const suspendUser = async (user) => {
  if (!confirm(`Suspend ${user.full_name}?`)) return
  
  try {
    await axios.post(`${API_BASE}/super-admin/users/${user.id}/suspend`)
    toast.success('User suspended')
    fetchUsers()
  } catch (error) {
    toast.error('Failed to suspend user')
  }
}

const activateUser = async (user) => {
  try {
    await axios.post(`${API_BASE}/super-admin/users/${user.id}/activate`)
    toast.success('User activated')
    fetchUsers()
  } catch (error) {
    toast.error('Failed to activate user')
  }
}

const impersonateUser = (user) => {
  impersonateTarget.value = user
  impersonateReason.value = ''
  showImpersonateModal.value = true
}

const confirmImpersonate = async () => {
  try {
    saving.value = true
    const response = await axios.post(
      `${API_BASE}/super-admin/users/${impersonateTarget.value.id}/impersonate`,
      { reason: impersonateReason.value }
    )
    
    // Store impersonation data
    localStorage.setItem('impersonation_token', response.data.data.token)
    localStorage.setItem('impersonation_log_id', response.data.data.impersonation_log_id)
    localStorage.setItem('original_user_id', response.data.data.original_user_id)
    
    toast.success('Impersonation started')
    showImpersonateModal.value = false
    
    // Redirect to impersonated user's dashboard
    window.location.href = '/dashboard'
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to impersonate')
  } finally {
    saving.value = false
  }
}

const resetPassword = async () => {
  const newPassword = prompt('Enter new password (min 8 characters):')
  if (!newPassword || newPassword.length < 8) {
    toast.error('Password must be at least 8 characters')
    return
  }
  
  try {
    await axios.post(`${API_BASE}/super-admin/users/${selectedUser.value.id}/reset-password`, {
      new_password: newPassword,
      send_email: confirm('Send email notification to user?'),
    })
    toast.success('Password reset successfully')
  } catch (error) {
    toast.error('Failed to reset password')
  }
}

const forceLogout = async () => {
  if (!confirm('Force logout this user from all sessions?')) return
  
  try {
    await axios.post(`${API_BASE}/super-admin/users/${selectedUser.value.id}/force-logout`)
    toast.success('User logged out from all sessions')
  } catch (error) {
    toast.error('Failed to force logout')
  }
}

const transferUser = () => {
  toast.info('Transfer feature - coming soon')
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatRole = (role) => {
  const roles = {
    super_admin: 'Super Admin',
    school_admin: 'School Admin',
    teacher: 'Teacher',
    class_teacher: 'Class Teacher',
    student: 'Student',
    parent: 'Parent',
    bursar: 'Bursar',
  }
  return roles[role] || role
}

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getRoleColor = (role) => {
  const colors = {
    super_admin: 'gold',
    school_admin: 'purple',
    teacher: 'blue',
    class_teacher: 'cyan',
    student: 'green',
    parent: 'orange',
    bursar: 'pink',
  }
  return colors[role] || 'gray'
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page)
  }
}

onMounted(() => {
  fetchUsers()
  fetchStats()
  fetchSchools()
})
</script>

<style scoped>
.users-page {
  max-width: 1600px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #94a3b8;
  font-size: 0.9rem;
}

/* Stats */
.stats-row {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.stat-item {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  min-width: 140px;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #f1f5f9;
}

.stat-item.success .stat-value { color: #10b981; }
.stat-item.danger .stat-value { color: #ef4444; }
.stat-item.info .stat-value { color: #3b82f6; }

.stat-label {
  font-size: 0.75rem;
  color: #64748b;
}

/* Filters */
.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
}

.search-box input {
  width: 100%;
  padding: 0.75rem 1rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f1f5f9;
}

.filter-group {
  display: flex;
  gap: 0.75rem;
}

.filter-group select {
  padding: 0.75rem 1rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f1f5f9;
  cursor: pointer;
}

/* Table */
.table-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 16px;
  overflow: hidden;
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748b;
  background: #0f172a;
}

.data-table td {
  padding: 1rem;
  border-top: 1px solid #334155;
  color: #e2e8f0;
  font-size: 0.875rem;
}

.user-cell {
  min-width: 220px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8rem;
  color: white;
}

.user-avatar.gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
.user-avatar.purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.user-avatar.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.user-avatar.cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.user-avatar.green { background: linear-gradient(135deg, #10b981, #059669); }
.user-avatar.orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.user-avatar.pink { background: linear-gradient(135deg, #ec4899, #db2777); }
.user-avatar.gray { background: linear-gradient(135deg, #64748b, #475569); }

.user-name {
  display: block;
  font-weight: 500;
  color: #f1f5f9;
}

.user-email {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
}

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.super_admin { background: rgba(251, 191, 36, 0.1); color: #fbbf24; }
.role-badge.school_admin { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.role-badge.teacher { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.role-badge.class_teacher { background: rgba(6, 182, 212, 0.1); color: #06b6d4; }
.role-badge.student { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.role-badge.parent { background: rgba(249, 115, 22, 0.1); color: #f97316; }
.role-badge.bursar { background: rgba(236, 72, 153, 0.1); color: #ec4899; }

.school-name {
  color: #94a3b8;
}

.last-login {
  font-size: 0.8rem;
  color: #64748b;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.status-badge.suspended { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #334155;
  border-radius: 6px;
  cursor: pointer;
}

.action-btn.warning:hover { background: rgba(245, 158, 11, 0.2); }
.action-btn.danger:hover { background: rgba(239, 68, 68, 0.2); }
.action-btn.success:hover { background: rgba(16, 185, 129, 0.2); }

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-top: 1px solid #334155;
}

.page-btn {
  padding: 0.5rem 1rem;
  background: #334155;
  border: none;
  border-radius: 6px;
  color: #e2e8f0;
  cursor: pointer;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.8rem;
  color: #64748b;
}

/* Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal.large { max-width: 800px; }
.modal.small { max-width: 450px; }

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #334155;
}

.modal-header h2 {
  color: #f1f5f9;
}

.close-btn {
  width: 32px;
  height: 32px;
  background: #334155;
  border: none;
  border-radius: 6px;
  color: #94a3b8;
  font-size: 1.25rem;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #334155;
}

/* User Detail Grid */
.user-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.detail-section h3 {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 1rem;
}

.detail-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item .label {
  font-size: 0.75rem;
  color: #64748b;
}

.detail-item .value {
  font-size: 0.875rem;
  color: #f1f5f9;
}

.quick-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #334155;
  flex-wrap: wrap;
}

.action-chip {
  padding: 0.5rem 1rem;
  background: #334155;
  border: none;
  border-radius: 20px;
  color: #e2e8f0;
  font-size: 0.8rem;
  cursor: pointer;
  transition: background 0.2s;
}

.action-chip:hover {
  background: #475569;
}

/* Form */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.8rem;
  color: #94a3b8;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.75rem 1rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f1f5f9;
}

/* Warning Banner */
.warning-banner {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.warning-banner p {
  color: #f59e0b;
  font-size: 0.875rem;
  margin: 0.25rem 0;
}

/* Buttons */
.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:disabled { opacity: 0.6; }

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #334155;
  color: #e2e8f0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn-warning {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-warning:disabled { opacity: 0.6; }
</style>
