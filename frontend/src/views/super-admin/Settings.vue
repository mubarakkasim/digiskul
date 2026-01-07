<template>
  <div class="settings-page">
    <div class="page-header">
      <div class="header-content">
        <h1>⚙️ Platform Settings</h1>
        <p>Configure global system behavior and integrations</p>
      </div>
      <button class="btn-primary" @click="saveAllSettings" :disabled="saving">
        {{ saving ? 'Saving Changes...' : 'Save All Settings' }}
      </button>
    </div>

    <div class="settings-content">
      <!-- Categories Sidebar -->
      <div class="settings-nav">
        <button v-for="cat in categories" :key="cat.id" :class="{ active: activeCategory === cat.id }" @click="activeCategory = cat.id">
          <span class="icon">{{ cat.icon }}</span>
          {{ cat.label }}
        </button>
      </div>

      <!-- Settings Form -->
      <div class="settings-main card">
        <div class="category-header">
          <h2>{{ currentCategoryLabel }}</h2>
          <p>{{ currentCategoryDescription }}</p>
        </div>

        <div class="settings-list" v-if="!loading">
          <div v-for="setting in filteredSettings" :key="setting.id" class="setting-item">
            <div class="setting-info">
              <label>{{ formatKey(setting.key_name) }}</label>
              <p>{{ setting.description || 'System configuration setting.' }}</p>
            </div>
            <div class="setting-input">
              <!-- Boolean toggle -->
              <div v-if="setting.type === 'boolean'" class="toggle" @click="toggleSetting(setting)">
                <div class="track" :class="{ active: setting.value === '1' }">
                  <div class="knob"></div>
                </div>
              </div>
              
              <!-- Number input -->
              <input v-else-if="setting.type === 'integer'" type="number" v-model="setting.value" />
              
              <!-- Password input -->
              <input v-else-if="setting.type === 'password'" type="password" v-model="setting.value" placeholder="••••••••" />
              
              <!-- Text input -->
              <input v-else v-model="setting.value" />
            </div>
          </div>
          
          <div v-if="!filteredSettings.length" class="empty-settings">
            No settings found for this category.
          </div>
        </div>
        
        <div v-else class="loading-settings">
          <div class="loader"></div>
          <p>Loading configurations...</p>
        </div>
      </div>
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
const saving = ref(false)
const settings = ref([])
const activeCategory = ref('general')

const categories = [
  { id: 'general', label: 'General', icon: '🌐', desc: 'Core platform identity and basic operations.' },
  { id: 'email', label: 'Email (SMTP)', icon: '📧', desc: 'Configure system-wide mailing services.' },
  { id: 'sms', label: 'SMS Gateway', icon: '📱', desc: 'Transactional SMS alerts and notifications.' },
  { id: 'payment', label: 'Payments', icon: '💳', desc: 'Manage payment gateways (Paystack, Flutterwave).' },
  { id: 'security', label: 'Security', icon: '🔒', desc: 'Login throttling, MFA and audit settings.' },
  { id: 'storage', label: 'Storage', icon: '💾', desc: 'Cloud storage and backup configurations.' }
]

const currentCategoryLabel = computed(() => categories.find(c => c.id === activeCategory.value)?.label)
const currentCategoryDescription = computed(() => categories.find(c => c.id === activeCategory.value)?.desc)

const filteredSettings = computed(() => {
  return settings.value.filter(s => s.category === activeCategory.value)
})

const fetchSettings = async () => {
  loading.value = true
  try {
    const res = await axios.get(`${API}/super-admin/settings`)
    settings.value = res.data.data
  } catch (e) {
    toast.error('Could not fetch platform settings.')
  } finally {
    loading.value = false
  }
}

const toggleSetting = (setting) => {
  setting.value = setting.value === '1' ? '0' : '1'
}

const saveAllSettings = async () => {
  saving.value = true
  try {
    const payload = settings.value.map(s => ({ key: s.key_name, value: s.value }))
    await axios.post(`${API}/super-admin/settings/batch`, { settings: payload })
    toast.success('All settings saved successfully!')
  } catch (e) {
    toast.error('Error saving settings. Check console for details.')
  } finally {
    saving.value = false
  }
}

const formatKey = (key) => key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')

onMounted(fetchSettings)
</script>

<style scoped>
.settings-page { max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.header-content h1 { font-size: 1.75rem; font-weight: 800; color: #1A4FC4; }
.header-content p { color: #64748B; }

.settings-content { display: grid; grid-template-columns: 280px 1fr; gap: 2rem; }

.settings-nav { display: flex; flex-direction: column; gap: 0.5rem; }
.settings-nav button {
  display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem; border: none;
  background: white; border-radius: 12px; font-weight: 700; color: #475569;
  cursor: pointer; text-align: left; transition: all 0.2s; border: 1px solid #E2E8F0;
}
.settings-nav button:hover { background: #F8FAFC; }
.settings-nav button.active { background: #1A4FC4; color: white; border-color: #1A4FC4; }
.settings-nav button .icon { font-size: 1.25rem; }

.settings-main { background: white; border-radius: 20px; border: 1px solid #E2E8F0; padding: 2.5rem; }
.category-header { margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #F1F5F9; }
.category-header h2 { font-size: 1.25rem; font-weight: 800; color: #1E293B; margin-bottom: 0.25rem; }
.category-header p { font-size: 0.9rem; color: #64748B; }

.settings-list { display: flex; flex-direction: column; gap: 1.5rem; }
.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #F8FAFC; border-radius: 16px; }
.setting-info label { display: block; font-size: 0.9rem; font-weight: 800; color: #1E293B; margin-bottom: 0.2rem; }
.setting-info p { font-size: 0.75rem; color: #64748B; margin: 0; }

.setting-input { width: 300px; display: flex; justify-content: flex-end; }
.setting-input input { width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-weight: 600; font-family: inherit; }

/* Toggle Switch */
.toggle { cursor: pointer; }
.track { width: 50px; height: 26px; background: #CBD5E1; border-radius: 15px; position: relative; transition: all 0.3s; }
.track.active { background: #10B981; }
.knob { width: 20px; height: 20px; background: white; border-radius: 50%; position: absolute; top: 3px; left: 3px; transition: all 0.3s; }
.track.active .knob { transform: translateX(24px); }

.btn-primary { background: #1A4FC4; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer; }
.loading-settings { padding: 5rem; text-align: center; color: #64748B; }
.loader { border: 4px solid #f3f3f3; border-top: 4px solid #1A4FC4; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
