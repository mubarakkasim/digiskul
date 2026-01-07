<template>
  <div class="health-page">
    <div class="page-header">
      <div class="header-content">
        <h1>❤️ System Health</h1>
        <p>Real-time platform monitoring</p>
      </div>
      <div class="health-score" :class="healthClass">
        <span class="score-value">{{ health.score }}%</span>
        <span class="score-label">Health Score</span>
      </div>
    </div>

    <div class="health-checks">
      <div v-for="(check, key) in health.checks" :key="key" 
           class="check-card" :class="check.healthy ? 'healthy' : 'unhealthy'">
        <div class="check-icon">{{ getCheckIcon(key) }}</div>
        <div class="check-content">
          <h3>{{ key }}</h3>
          <span :class="check.healthy ? 'ok' : 'error'">
            {{ check.healthy ? '✓ Healthy' : '✗ Issues' }}
          </span>
        </div>
      </div>
    </div>

    <div class="metrics-grid">
      <div class="metric-card">
        <h3>💾 Storage</h3>
        <span class="value">{{ storage.used_percent }}%</span>
        <div class="progress"><div :style="{ width: storage.used_percent + '%' }"></div></div>
        <small>{{ storage.used_gb }} / {{ storage.total_gb }} GB</small>
      </div>
      <div class="metric-card">
        <h3>🧠 Memory</h3>
        <span class="value">{{ memory.used_mb }} MB</span>
        <small>Peak: {{ memory.peak_mb }} MB</small>
      </div>
      <div class="metric-card">
        <h3>🗄️ Database</h3>
        <span class="value">{{ db.size_mb }} MB</span>
      </div>
      <div class="metric-card">
        <h3>🐘 PHP</h3>
        <span class="value">v{{ php.version }}</span>
      </div>
    </div>

    <div class="actions-row">
      <button @click="refresh" :disabled="loading">🔄 Refresh</button>
      <button @click="clearCache" :disabled="loading">🧹 Clear Cache</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const loading = ref(false)
const health = ref({ score: 100, checks: {} })
const storage = ref({ used_percent: 0, used_gb: 0, total_gb: 0 })
const memory = ref({ used_mb: 0, peak_mb: 0 })
const db = ref({ size_mb: 0 })
const php = ref({ version: '8.x' })

const healthClass = computed(() => {
  if (health.value.score >= 90) return 'excellent'
  if (health.value.score >= 70) return 'good'
  return 'warning'
})

const getCheckIcon = (k) => ({ database: '🗄️', cache: '💨', queue: '📦', storage: '💾' }[k] || '✓')

const fetchHealth = async () => {
  try {
    loading.value = true
    const res = await axios.get(`${API}/super-admin/health`)
    health.value = res.data.data
    if (health.value.checks?.storage) storage.value = health.value.checks.storage
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

const fetchMetrics = async () => {
  try {
    const res = await axios.get(`${API}/super-admin/health/realtime`)
    const d = res.data.data
    if (d.memory) memory.value = d.memory
    if (d.disk) storage.value = d.disk
    if (d.database) db.value = d.database
    if (d.php) php.value = d.php
  } catch (e) { console.error(e) }
}

const refresh = async () => { await fetchHealth(); await fetchMetrics() }
const clearCache = async () => {
  try {
    await axios.post(`${API}/super-admin/settings/cache/clear`)
    alert('Cache cleared!')
  } catch (e) { alert('Failed') }
}

onMounted(() => { fetchHealth(); fetchMetrics() })
</script>

<style scoped>
.health-page { max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; color: #f1f5f9; }
.page-header p { color: #94a3b8; }
.health-score { text-align: center; padding: 1.5rem; border-radius: 12px; background: #1e293b; border: 1px solid #334155; }
.health-score.excellent { border-color: #10b981; }
.health-score.good { border-color: #3b82f6; }
.health-score.warning { border-color: #f59e0b; }
.score-value { display: block; font-size: 2rem; font-weight: 700; color: #10b981; }
.score-label { font-size: 0.7rem; color: #64748b; }
.health-checks { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
.check-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 1rem; display: flex; gap: 1rem; }
.check-card.healthy { border-left: 3px solid #10b981; }
.check-card.unhealthy { border-left: 3px solid #ef4444; }
.check-icon { font-size: 1.5rem; }
.check-content h3 { font-size: 0.9rem; color: #f1f5f9; text-transform: capitalize; }
.ok { color: #10b981; font-size: 0.75rem; }
.error { color: #ef4444; font-size: 0.75rem; }
.metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
.metric-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 1.5rem; }
.metric-card h3 { font-size: 0.9rem; color: #f1f5f9; margin-bottom: 0.5rem; }
.metric-card .value { font-size: 1.5rem; font-weight: 700; color: #3b82f6; }
.metric-card small { color: #64748b; font-size: 0.75rem; }
.progress { height: 6px; background: #334155; border-radius: 3px; margin: 0.5rem 0; }
.progress div { height: 100%; background: #10b981; border-radius: 3px; }
.actions-row { display: flex; gap: 1rem; }
.actions-row button { padding: 0.75rem 1.5rem; background: #334155; border: none; border-radius: 8px; color: #e2e8f0; cursor: pointer; }
.actions-row button:hover { background: #475569; }
.actions-row button:disabled { opacity: 0.5; }
</style>
