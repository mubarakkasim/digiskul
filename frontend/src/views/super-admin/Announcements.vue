<template>
  <div class="announcements-page">
    <div class="page-header">
      <h1>📢 System Announcements</h1>
      <button @click="showModal = true" class="btn-primary">+ New Announcement</button>
    </div>

    <div class="announcements-list">
      <div v-for="a in announcements" :key="a.id" class="announcement-card" :class="a.priority">
        <div class="card-header">
          <h3>{{ a.title }}</h3>
          <span class="priority-badge" :class="a.priority">{{ a.priority }}</span>
        </div>
        <p class="content">{{ a.content }}</p>
        <div class="card-footer">
          <span class="meta">Created {{ formatDate(a.created_at) }}</span>
          <span class="status" :class="a.is_published ? 'published' : 'draft'">
            {{ a.is_published ? 'Published' : 'Draft' }}
          </span>
          <div class="actions">
            <button @click="a.is_published ? unpublish(a) : publish(a)">
              {{ a.is_published ? '📥 Unpublish' : '📤 Publish' }}
            </button>
            <button @click="editAnnouncement(a)">✏️</button>
            <button @click="deleteAnnouncement(a)" class="danger">🗑️</button>
          </div>
        </div>
      </div>
      <div v-if="!announcements.length" class="empty">No announcements yet</div>
    </div>

    <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header"><h2>{{ editing ? 'Edit' : 'New' }} Announcement</h2><button @click="closeModal">×</button></div>
        <form @submit.prevent="saveAnnouncement" class="modal-body">
          <div class="form-group"><label>Title *</label><input v-model="form.title" required /></div>
          <div class="form-group"><label>Content *</label><textarea v-model="form.content" rows="4" required></textarea></div>
          <div class="form-row">
            <div class="form-group"><label>Priority</label>
              <select v-model="form.priority"><option value="normal">Normal</option><option value="important">Important</option><option value="critical">Critical</option></select>
            </div>
            <div class="form-group"><label>Target</label>
              <select v-model="form.target_type"><option value="all">All Users</option><option value="specific_schools">Specific Schools</option><option value="specific_roles">Specific Roles</option></select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group"><label><input type="checkbox" v-model="form.is_dismissible" /> Dismissible</label></div>
            <div class="form-group"><label><input type="checkbox" v-model="form.show_on_login" /> Show on Login</label></div>
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
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
const announcements = ref([])
const showModal = ref(false)
const editing = ref(null)
const saving = ref(false)
const form = reactive({ title: '', content: '', priority: 'normal', target_type: 'all', is_dismissible: true, show_on_login: false })

const fetchAnnouncements = async () => {
  try {
    const res = await axios.get(`${API}/super-admin/announcements`)
    announcements.value = res.data.data.data || res.data.data
  } catch (e) { console.error(e) }
}

const saveAnnouncement = async () => {
  try {
    saving.value = true
    if (editing.value) await axios.put(`${API}/super-admin/announcements/${editing.value}`, form)
    else await axios.post(`${API}/super-admin/announcements`, form)
    closeModal()
    fetchAnnouncements()
  } catch (e) { alert('Failed to save') }
  finally { saving.value = false }
}

const editAnnouncement = (a) => {
  editing.value = a.id
  Object.assign(form, { title: a.title, content: a.content, priority: a.priority, target_type: a.target_type, is_dismissible: a.is_dismissible, show_on_login: a.show_on_login })
  showModal.value = true
}

const publish = async (a) => { await axios.post(`${API}/super-admin/announcements/${a.id}/publish`); fetchAnnouncements() }
const unpublish = async (a) => { await axios.post(`${API}/super-admin/announcements/${a.id}/unpublish`); fetchAnnouncements() }
const deleteAnnouncement = async (a) => { if (confirm('Delete?')) { await axios.delete(`${API}/super-admin/announcements/${a.id}`); fetchAnnouncements() } }

const closeModal = () => { showModal.value = false; editing.value = null; Object.assign(form, { title: '', content: '', priority: 'normal', target_type: 'all', is_dismissible: true, show_on_login: false }) }
const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '-'

onMounted(fetchAnnouncements)
</script>

<style scoped>
.announcements-page { max-width: 1000px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; color: #f1f5f9; }
.btn-primary { padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; border-radius: 8px; cursor: pointer; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #334155; color: #e2e8f0; border: none; border-radius: 8px; cursor: pointer; }
.announcements-list { display: flex; flex-direction: column; gap: 1rem; }
.announcement-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 1.5rem; border-left: 4px solid #3b82f6; }
.announcement-card.important { border-left-color: #f59e0b; }
.announcement-card.critical { border-left-color: #ef4444; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.card-header h3 { color: #f1f5f9; font-size: 1.1rem; }
.priority-badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; text-transform: uppercase; }
.priority-badge.normal { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.priority-badge.important { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.priority-badge.critical { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.content { color: #94a3b8; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; }
.card-footer { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.meta { font-size: 0.75rem; color: #64748b; }
.status { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.7rem; }
.status.published { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.status.draft { background: rgba(100, 116, 139, 0.1); color: #64748b; }
.actions { margin-left: auto; display: flex; gap: 0.5rem; }
.actions button { padding: 0.5rem 0.75rem; background: #334155; border: none; border-radius: 6px; color: #e2e8f0; font-size: 0.8rem; cursor: pointer; }
.actions button.danger:hover { background: rgba(239, 68, 68, 0.2); }
.empty { text-align: center; padding: 3rem; color: #64748b; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 550px; }
.modal-header { display: flex; justify-content: space-between; padding: 1.5rem; border-bottom: 1px solid #334155; }
.modal-header h2 { color: #f1f5f9; }
.modal-header button { background: #334155; border: none; width: 32px; height: 32px; border-radius: 6px; color: #94a3b8; cursor: pointer; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 0.5rem; }
.form-group input[type="text"], .form-group input:not([type]), .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #f1f5f9; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
</style>
