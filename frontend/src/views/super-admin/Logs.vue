<template>
  <div class="logs-page">
    <div class="page-header">
      <h1>📋 Activity Logs</h1>
      <div class="tabs">
        <button @click="tab='activity'" :class="{ active: tab==='activity' }">Activity</button>
        <button @click="tab='security'" :class="{ active: tab==='security' }">Security</button>
        <button @click="tab='system'" :class="{ active: tab==='system' }">System</button>
      </div>
    </div>

    <div class="filters">
      <input v-model="search" placeholder="Search logs..." @input="debouncedSearch" />
      <input type="date" v-model="fromDate" @change="fetchLogs" />
      <input type="date" v-model="toDate" @change="fetchLogs" />
      <button @click="exportLogs">📥 Export</button>
    </div>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Description</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id">
            <td class="time">{{ formatTime(log.created_at) }}</td>
            <td>{{ log.user?.full_name || 'System' }}</td>
            <td><span class="action-badge" :class="getActionClass(log.action)">{{ log.action }}</span></td>
            <td>{{ log.description || '-' }}</td>
            <td class="ip">{{ log.ip_address || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination" v-if="pagination.last_page > 1">
      <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">← Prev</button>
      <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">Next →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const tab = ref('activity')
const logs = ref([])
const search = ref('')
const fromDate = ref('')
const toDate = ref('')
const pagination = ref({ current_page: 1, last_page: 1 })

const fetchLogs = async (page = 1) => {
  try {
    const params = new URLSearchParams({ page, per_page: 50 })
    if (search.value) params.append('search', search.value)
    if (fromDate.value) params.append('from_date', fromDate.value)
    if (toDate.value) params.append('to_date', toDate.value)
    
    const endpoint = tab.value === 'security' ? 'security' : tab.value === 'system' ? 'system' : 'activity'
    const res = await axios.get(`${API}/super-admin/logs/${endpoint}?${params}`)
    logs.value = res.data.data.data || res.data.data
    pagination.value = { current_page: res.data.data.current_page || 1, last_page: res.data.data.last_page || 1 }
  } catch (e) { console.error(e) }
}

const debouncedSearch = (() => { let t; return () => { clearTimeout(t); t = setTimeout(fetchLogs, 300) } })()
const goToPage = (p) => { if (p >= 1 && p <= pagination.value.last_page) fetchLogs(p) }
const formatTime = (d) => d ? new Date(d).toLocaleString() : '-'
const getActionClass = (a) => ['error', 'login_failed'].includes(a) ? 'danger' : ['create', 'login'].includes(a) ? 'success' : 'default'
const exportLogs = () => alert('Export feature - coming soon')

onMounted(fetchLogs)
</script>

<style scoped>
.logs-page { max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; color: #f1f5f9; }
.tabs { display: flex; gap: 0.5rem; }
.tabs button { padding: 0.5rem 1rem; background: #334155; border: none; border-radius: 8px; color: #94a3b8; cursor: pointer; }
.tabs button.active { background: #3b82f6; color: white; }
.filters { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.filters input { padding: 0.75rem 1rem; background: #1e293b; border: 1px solid #334155; border-radius: 8px; color: #f1f5f9; flex: 1; min-width: 150px; }
.filters button { padding: 0.75rem 1rem; background: #3b82f6; border: none; border-radius: 8px; color: white; cursor: pointer; }
.table-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { padding: 1rem; text-align: left; font-size: 0.7rem; text-transform: uppercase; color: #64748b; background: #0f172a; }
td { padding: 1rem; border-top: 1px solid #334155; color: #e2e8f0; font-size: 0.85rem; }
.time { color: #64748b; font-size: 0.8rem; white-space: nowrap; }
.ip { font-family: monospace; color: #64748b; font-size: 0.8rem; }
.action-badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.7rem; text-transform: uppercase; }
.action-badge.success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.action-badge.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.action-badge.default { background: rgba(100, 116, 139, 0.1); color: #94a3b8; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1rem; }
.pagination button { padding: 0.5rem 1rem; background: #334155; border: none; border-radius: 6px; color: #e2e8f0; cursor: pointer; }
.pagination button:disabled { opacity: 0.5; }
.pagination span { color: #64748b; font-size: 0.8rem; }
</style>
