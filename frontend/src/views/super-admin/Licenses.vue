<template>
  <div class="licenses-page">
    <div class="page-header">
      <div class="header-content">
        <h1>📜 License Plans & Subscriptions</h1>
        <p>Manage pricing tiers and school subscriptions</p>
      </div>
      <button class="btn-primary" @click="showPlanModal = true">+ Create New Plan</button>
    </div>

    <!-- Tabs -->
    <div class="tabs-bar">
      <button :class="{ active: activeTab === 'plans' }" @click="activeTab = 'plans'">Pricing Plans</button>
      <button :class="{ active: activeTab === 'subs' }" @click="activeTab = 'subs'">Active Subscriptions</button>
    </div>

    <!-- Plans View -->
    <div v-if="activeTab === 'plans'" class="plans-grid">
      <div v-for="plan in plans" :key="plan.id" class="plan-card" :class="{ featured: plan.code === 'standard' }">
        <div class="featured-badge" v-if="plan.code === 'standard'">Popular Choice</div>
        <div class="plan-header">
          <span class="plan-name">{{ plan.name }}</span>
          <div class="plan-price">
            <span class="currency">₦</span>
            <span class="amount">{{ formatNumber(plan.price) }}</span>
            <span class="period">/yr</span>
          </div>
        </div>
        <ul class="plan-features">
          <li><strong>{{ plan.user_limit || 'Unlimited' }}</strong> Users</li>
          <li><strong>{{ plan.student_limit || 'Unlimited' }}</strong> Students</li>
          <li><strong>{{ plan.storage_gb }}GB</strong> Cloud Storage</li>
          <li v-for="feat in plan.features?.slice(0, 4)" :key="feat">✅ {{ formatFeature(feat) }}</li>
        </ul>
        <div class="plan-footer">
          <button class="btn-outline-blue">Edit Details</button>
        </div>
      </div>
    </div>

    <!-- Subscriptions View -->
    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Institution</th>
            <th>Plan</th>
            <th>Valid From</th>
            <th>Expiry Date</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sub in subscriptions" :key="sub.id">
            <td><strong>{{ sub.school?.name }}</strong></td>
            <td><span class="plan-badge">{{ sub.plan?.name }}</span></td>
            <td>{{ formatDate(sub.start_date) }}</td>
            <td>{{ formatDate(sub.end_date) }}</td>
            <td>₦{{ formatNumber(sub.amount_paid) }}</td>
            <td>
              <span class="status-pill" :class="sub.status">{{ sub.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const activeTab = ref('plans')
const plans = ref([])
const subscriptions = ref([])

const fetchData = async () => {
  try {
    const [p, s] = await Promise.all([
      axios.get(`${API}/super-admin/licenses/plans`),
      axios.get(`${API}/super-admin/licenses/subscriptions`)
    ])
    plans.value = p.data.data
    subscriptions.value = s.data.data.data || s.data.data
  } catch (e) { console.error(e) }
}

const formatNumber = (n) => new Intl.NumberFormat().format(n || 0)
const formatDate = (d) => new Date(d).toLocaleDateString()
const formatFeature = (f) => f.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

onMounted(fetchData)
</script>

<style scoped>
.licenses-page { max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.header-content h1 { font-size: 1.75rem; font-weight: 800; color: #1A4FC4; }
.header-content p { color: #64748B; }

.tabs-bar { display: flex; gap: 1rem; border-bottom: 1px solid #E2E8F0; margin-bottom: 2rem; }
.tabs-bar button { padding: 1rem 1.5rem; border: none; background: transparent; font-weight: 700; color: #64748B; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; }
.tabs-bar button.active { color: #1A4FC4; border-bottom-color: #FDBB2B; }

.plans-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
.plan-card { background: white; border-radius: 20px; border: 1px solid #E2E8F0; padding: 2rem; position: relative; display: flex; flex-direction: column; }
.plan-card.featured { border: 2px solid #FDBB2B; box-shadow: 0 10px 25px rgba(253, 187, 43, 0.1); }
.featured-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #FDBB2B; color: #1E293B; padding: 0.25rem 1rem; border-radius: 20px; font-weight: 800; font-size: 0.7rem; }

.plan-name { font-size: 1.25rem; font-weight: 800; color: #1E293B; display: block; margin-bottom: 1.5rem; }
.plan-price { margin-bottom: 2rem; }
.plan-price .currency { font-size: 1.25rem; font-weight: 700; color: #1A4FC4; vertical-align: top; }
.plan-price .amount { font-size: 2.5rem; font-weight: 800; color: #1E293B; line-height: 1; }
.plan-price .period { color: #64748B; font-size: 0.9rem; font-weight: 600; }

.plan-features { flex: 1; list-style: none; padding: 0; margin-bottom: 2rem; }
.plan-features li { padding: 0.75rem 0; border-bottom: 1px solid #F8FAFC; color: #475569; font-size: 0.9rem; }
.plan-features li:last-child { border: none; }

.table-card { background: white; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 1.25rem 1.5rem; background: #F8FAFC; color: #64748B; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
td { padding: 1.25rem 1.5rem; border-top: 1px solid #F1F5F9; color: #1E293B; }

.status-pill { padding: 0.25rem 0.75rem; border-radius: 30px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
.status-pill.active { background: rgba(16, 185, 129, 0.1); color: #10B981; }

.btn-primary { background: #1A4FC4; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer; }
.btn-outline-blue { width: 100%; border: 1.5px solid #1A4FC4; color: #1A4FC4; padding: 0.75rem; border-radius: 10px; font-weight: 700; background: transparent; cursor: pointer; transition: all 0.2s; }
.btn-outline-blue:hover { background: #1A4FC4; color: white; }
</style>
