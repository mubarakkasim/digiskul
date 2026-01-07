<template>
  <div class="dashboard-page">
    <div class="page-header">
      <div class="header-content">
        <h1>📊 Platform Overview</h1>
        <p>Real-time metrics across all institutions</p>
      </div>
      <div class="quick-actions">
        <button @click="reloadData" class="btn-outline" :disabled="loading">
          {{ loading ? '⏳ Refreshing...' : '🔄 Refresh Data' }}
        </button>
        <router-link to="/super-admin/schools" class="btn-primary">+ Register School</router-link>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-icon">🏫</div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(stats.total_schools) }}</span>
          <span class="stat-label">Total Schools</span>
        </div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(stats.total_users) }}</span>
          <span class="stat-label">Active Users</span>
        </div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon">👨‍🎓</div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(stats.total_students) }}</span>
          <span class="stat-label">Total Students</span>
        </div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-icon">💳</div>
        <div class="stat-info">
          <span class="stat-value">₦{{ formatShortNumber(stats.monthly_revenue) }}</span>
          <span class="stat-label">M. Revenue</span>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content" v-if="!loading">
      <div class="content-left">
        <!-- Live Metrics -->
        <div class="card metrics-card">
          <div class="card-header">
            <h3>📈 Live Performance</h3>
            <span class="health-badge" :class="healthStatusClass">Health: {{ stats.health_score }}%</span>
          </div>
          <div class="metrics-grid">
            <div class="metric">
              <div class="metric-top">
                <label>Database Connection</label>
                <span>99.9%</span>
              </div>
              <div class="progress"><div class="fill" style="width: 99.9%"></div></div>
            </div>
            <div class="metric">
              <div class="metric-top">
                <label>CPU Usage</label>
                <span>{{ stats.realtime_metrics?.cpu || 12 }}%</span>
              </div>
              <div class="progress"><div class="fill" :style="{ width: (stats.realtime_metrics?.cpu || 12) + '%' }"></div></div>
            </div>
            <div class="metric">
              <div class="metric-top">
                <label>Storage Used ({{ (stats.realtime_metrics?.storage?.used_gb || 0).toFixed(1) }}GB)</label>
                <span>{{ stats.realtime_metrics?.storage?.used_percent || 0 }}%</span>
              </div>
              <div class="progress"><div class="fill" :style="{ width: (stats.realtime_metrics?.storage?.used_percent || 0) + '%' }"></div></div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="card activity-card">
          <div class="card-header">
            <h3>📝 Recent Activity</h3>
            <router-link to="/super-admin/logs" class="view-all">Logs →</router-link>
          </div>
          <div class="activity-list">
            <div v-for="log in stats.recent_activity" :key="log.id" class="activity-item">
              <div class="activity-icon" :class="log.action_type">{{ getActionIcon(log.action_type) }}</div>
              <div class="activity-details">
                <p class="description">{{ log.description }}</p>
                <div class="meta">
                  <span>👤 {{ log.user?.full_name || 'System' }}</span>
                  <span>🕒 {{ formatTimeAgo(log.created_at) }}</span>
                </div>
              </div>
            </div>
            <div v-if="!stats.recent_activity?.length" class="empty-list">No recent activity detected.</div>
          </div>
        </div>
      </div>

      <div class="content-right">
        <!-- School Signups -->
        <div class="card signup-card">
          <div class="card-header">
            <h3>🆕 Recent Signups</h3>
            <router-link to="/super-admin/schools" class="view-all">All →</router-link>
          </div>
          <div class="signup-list">
            <div v-for="school in stats.recent_schools" :key="school.id" class="signup-item">
              <div class="school-logo">{{ school.name[0] }}</div>
              <div class="school-info">
                <span class="name">{{ school.name }}</span>
                <span class="plan">{{ school.subscription_plan }}</span>
              </div>
              <div class="signup-date">
                {{ formatDay(school.created_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- System Alerts -->
        <div class="card alerts-card">
          <div class="card-header">
            <h3>⚠️ System Alerts</h3>
            <span class="badge red" v-if="alerts.length">{{ alerts.length }} New</span>
          </div>
          <div class="alerts-list">
            <div v-for="alert in alerts" :key="alert.id" class="alert-item" :class="alert.severity">
              <div class="alert-info">
                <strong>{{ alert.title }}</strong>
                <p>{{ alert.message }}</p>
              </div>
              <span class="alert-time">{{ formatTimeAgo(alert.created_at) }}</span>
            </div>
            <div v-if="!alerts.length" class="empty-list">System is secure. No alerts.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else class="dashboard-loading">
      <div class="loader"></div>
      <p>Synchronizing platform data...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const loading = ref(true)
const stats = ref({})
const alerts = ref([])

const healthStatusClass = computed(() => {
  const score = stats.value.health_score || 100
  if (score >= 90) return 'excellent'
  if (score >= 70) return 'good'
  return 'warning'
})

const reloadData = async () => {
  loading.value = true
  try {
    const [stRes, alRes] = await Promise.all([
      axios.get(`${API}/super-admin/dashboard/stats`),
      axios.get(`${API}/super-admin/dashboard/alerts`)
    ])
    stats.value = stRes.data.data
    alerts.value = alRes.data.data
  } catch (e) {
    toast.error('Data sync failed. Check server status.')
    console.error(e)
  } finally {
    loading.value = false
  }
}

const formatNumber = (n) => new Intl.NumberFormat().format(n || 0)
const formatShortNumber = (n) => {
  if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M'
  if (n >= 1000) return (n / 1000).toFixed(1) + 'K'
  return n
}

const formatDay = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '-'
const formatTimeAgo = (d) => {
  if (!d) return '-'
  const diff = Math.floor((new Date() - new Date(d)) / 1000 / 60)
  if (diff < 1) return 'Just now'
  if (diff < 60) return `${diff}m ago`
  if (diff < 1440) return `${Math.floor(diff / 60)}h ago`
  return `${Math.floor(diff / 1440)}d ago`
}

const getActionIcon = (type) => {
  const icons = { create: '➕', update: '✏️', delete: '🗑️', login: '🔑', logout: '🚪', audit: '📋' }
  return icons[type] || '⚡'
}

onMounted(reloadData)
</script>

<style scoped>
.dashboard-page { max-width: 1400px; margin: 0 auto; }

.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
.header-content h1 { font-size: 1.75rem; font-weight: 800; color: #1A4FC4; margin-bottom: 0.25rem; }
.header-content p { color: #64748B; font-weight: 500; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
.stat-card {
  background: white; padding: 1.75rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem;
  border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-5px); }
.stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
.stat-card.blue .stat-icon { background: rgba(26, 79, 196, 0.1); }
.stat-card.yellow .stat-icon { background: rgba(253, 187, 43, 0.1); }
.stat-info .stat-value { display: block; font-size: 1.5rem; font-weight: 800; color: #1E293B; line-height: 1.2; }
.stat-info .stat-label { font-size: 0.75rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; }

.dashboard-content { display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; }
.card { background: white; border-radius: 20px; border: 1px solid #E2E8F0; padding: 1.75rem; margin-bottom: 2rem; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.card-header h3 { font-size: 1rem; font-weight: 800; color: #1E293B; }
.view-all { font-size: 0.8rem; font-weight: 700; color: #1A4FC4; text-decoration: none; }

.health-badge { padding: 0.4rem 1rem; border-radius: 30px; font-weight: 800; font-size: 0.75rem; }
.health-badge.excellent { background: rgba(16, 185, 129, 0.1); color: #10B981; }
.health-badge.good { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }

.metrics-grid { display: flex; flex-direction: column; gap: 1.5rem; }
.metric-top { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.metric-top label { font-size: 0.85rem; font-weight: 700; color: #475569; }
.metric-top span { font-size: 0.85rem; font-weight: 800; color: #1E293B; }
.progress { height: 10px; background: #F1F5F9; border-radius: 5px; overflow: hidden; }
.progress .fill { height: 100%; background: #1A4FC4; border-radius: 5px; }

.activity-list { display: flex; flex-direction: column; gap: 1rem; }
.activity-item { display: flex; gap: 1rem; padding: 1rem; background: #F8FAFC; border-radius: 12px; }
.activity-icon { width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.activity-details .description { font-size: 0.875rem; color: #1E293B; font-weight: 600; margin-bottom: 0.25rem; }
.activity-details .meta { display: flex; gap: 1rem; font-size: 0.7rem; color: #64748B; font-weight: 600; }

.signup-list { display: flex; flex-direction: column; gap: 0.75rem; }
.signup-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #F1F5F9; border-radius: 12px; }
.school-logo { width: 38px; height: 38px; background: #1A4FC4; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.school-info .name { display: block; font-size: 0.85rem; font-weight: 700; color: #1E293B; }
.school-info .plan { font-size: 0.75rem; color: #64748B; text-transform: capitalize; }
.signup-date { margin-left: auto; font-size: 0.75rem; color: #94A3B8; font-weight: 600; }

.alerts-list { display: flex; flex-direction: column; gap: 0.75rem; }
.alert-item { padding: 1rem; border-radius: 12px; border-left: 4px solid #E2E8F0; display: flex; justify-content: space-between; align-items: flex-start; }
.alert-item.critical { background: rgba(239, 68, 68, 0.05); border-left-color: #EF4444; }
.alert-item.warning { background: rgba(245, 158, 11, 0.05); border-left-color: #F59E0B; }
.alert-info strong { display: block; font-size: 0.85rem; color: #1E293B; margin-bottom: 0.2rem; }
.alert-info p { font-size: 0.75rem; color: #64748B; margin: 0; }
.alert-time { font-size: 0.7rem; color: #94A3B8; font-weight: 600; }

.btn-primary { background: #1A4FC4; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer; text-decoration: none; }
.btn-outline { background: white; border: 1.5px solid #E2E8F0; padding: 0.65rem 1.25rem; border-radius: 10px; color: #475569; font-weight: 700; cursor: pointer; }

.dashboard-loading { height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; color: #64748B; font-weight: 600; }
.loader { border: 4px solid #f3f3f3; border-top: 4px solid #1A4FC4; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.empty-list { padding: 2rem; text-align: center; color: #94A3B8; font-size: 0.85rem; font-style: italic; }
</style>
