<template>
  <div class="backups-page">
    <div class="page-header">
      <h1>💾 Backups</h1>
      <button @click="showModal = true" class="btn-primary">+ Create Backup</button>
    </div>

    <div class="stats-row">
      <div class="stat"><span class="value">{{ stats.total_backups }}</span><span class="label">Total</span></div>
      <div class="stat success"><span class="value">{{ stats.completed_backups }}</span><span class="label">Completed</span></div>
      <div class="stat danger"><span class="value">{{ stats.failed_backups }}</span><span class="label">Failed</span></div>
      <div class="stat info"><span class="value">{{ stats.total_size_formatted }}</span><span class="label">Total Size</span></div>
    </div>

    <div class="table-card">
      <table>
        <thead>
          <tr><th>Type</th><th>School</th><th>Size</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <tr v-for="b in backups" :key="b.id">
            <td><span class="type-badge">{{ b.backup_type }}</span></td>
            <td>{{ b.school?.name || 'Full System' }}</td>
            <td>{{ b.formatted_size || '-' }}</td>
            <td><span class="status" :class="b.status">{{ b.status }}</span></td>
            <td>{{ formatDate(b.created_at) }}</td>
            <td class="actions">
              <button v-if="b.status==='completed'" @click="download(b)" title="Download">📥</button>
              <button v-if="b.status==='completed'" @click="restore(b)" title="Restore">🔄</button>
              <button @click="deleteBackup(b)" title="Delete" class="danger">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header"><h2>Create Backup</h2><button @click="showModal = false">×</button></div>
        <form @submit.prevent="createBackup" class="modal-body">
          <div class="form-group">
            <label>Backup Type</label>
            <select v-model="form.backup_type">
              <option value="full">Full System</option>
              <option value="database_only">Database Only</option>
              <option value="files_only">Files Only</option>
              <option value="school_only">Specific School</option>
            </select>
          </div>
          <div class="form-group" v-if="form.backup_type === 'school_only'">
            <label>School</label>
            <select v-model="form.school_id"><option v-for="s in schools" :key="s.id" :value="s.id">{{ s.name }}</option></select>
          </div>
          <div class="form-group"><label>Notes</label><textarea v-model="form.notes" rows="2"></textarea></div>
          <div class="modal-footer">
            <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Creating...' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const backups = ref([])
const schools = ref([])
const stats = ref({ total_backups: 0, completed_backups: 0, failed_backups: 0, total_size_formatted: '0 MB' })
const showModal = ref(false)
const saving = ref(false)
const form = reactive({ backup_type: 'full', school_id: null, notes: '' })

const fetchBackups = async () => {
  try {
    const [b, s, st] = await Promise.all([
      axios.get(`${API}/super-admin/backups`),
      axios.get(`${API}/super-admin/schools?per_page=100`),
      axios.get(`${API}/super-admin/backups/stats`)
    ])
    backups.value = b.data.data.data || b.data.data
    schools.value = s.data.data.data || s.data.data
    stats.value = st.data.data
  } catch (e) { console.error(e) }
}

const createBackup = async () => {
  try {
    saving.value = true
    await axios.post(`${API}/super-admin/backups`, form)
    showModal.value = false
    fetchBackups()
  } catch (e) { alert('Failed to create backup') }
  finally { saving.value = false }
}

const download = (b) => window.open(`${API}/super-admin/backups/${b.id}/download`)
const restore = async (b) => { if (confirm('Restore this backup?')) await axios.post(`${API}/super-admin/backups/${b.id}/restore`, { confirm: true }) }
const deleteBackup = async (b) => { if (confirm('Delete this backup?')) { await axios.delete(`${API}/super-admin/backups/${b.id}`); fetchBackups() } }
const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '-'

onMounted(fetchBackups)
</script>

<style scoped>
.backups-page { max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; color: #f1f5f9; }
.btn-primary { padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; border-radius: 8px; cursor: pointer; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #334155; color: #e2e8f0; border: none; border-radius: 8px; cursor: pointer; }
.stats-row { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.stat { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 1rem 1.5rem; min-width: 120px; }
.stat .value { display: block; font-size: 1.5rem; font-weight: 700; color: #f1f5f9; }
.stat.success .value { color: #10b981; }
.stat.danger .value { color: #ef4444; }
.stat.info .value { color: #3b82f6; }
.stat .label { font-size: 0.75rem; color: #64748b; }
.table-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { padding: 1rem; text-align: left; font-size: 0.7rem; text-transform: uppercase; color: #64748b; background: #0f172a; }
td { padding: 1rem; border-top: 1px solid #334155; color: #e2e8f0; }
.type-badge { background: #334155; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; text-transform: uppercase; }
.status { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.7rem; text-transform: uppercase; }
.status.completed { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.status.pending, .status.in_progress { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.status.failed { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.actions { display: flex; gap: 0.5rem; }
.actions button { width: 32px; height: 32px; border: none; background: #334155; border-radius: 6px; cursor: pointer; }
.actions button.danger:hover { background: rgba(239, 68, 68, 0.2); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 450px; }
.modal-header { display: flex; justify-content: space-between; padding: 1.5rem; border-bottom: 1px solid #334155; }
.modal-header h2 { color: #f1f5f9; }
.modal-header button { background: #334155; border: none; width: 32px; height: 32px; border-radius: 6px; color: #94a3b8; cursor: pointer; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; border-top: 1px solid #334155; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 0.5rem; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #f1f5f9; }
</style>
