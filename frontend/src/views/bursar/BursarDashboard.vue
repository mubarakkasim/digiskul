<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Bursar Dashboard</h1>
      <p class="text-gray-600">Financial overview and payment management</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card bg-gradient-to-br from-green-500 to-green-700 text-white">
        <p class="text-sm opacity-80">Total Expected</p>
        <p class="text-3xl font-bold">₦{{ formatCurrency(stats.feesSummary?.total_due) }}</p>
      </div>
      <div class="card bg-gradient-to-br from-blue-500 to-blue-700 text-white">
        <p class="text-sm opacity-80">Total Collected</p>
        <p class="text-3xl font-bold">₦{{ formatCurrency(stats.feesSummary?.total_paid) }}</p>
      </div>
      <div class="card bg-gradient-to-br from-red-500 to-red-700 text-white">
        <p class="text-sm opacity-80">Outstanding</p>
        <p class="text-3xl font-bold">₦{{ formatCurrency(stats.feesSummary?.total_balance) }}</p>
      </div>
      <div class="card bg-gradient-to-br from-purple-500 to-purple-700 text-white">
        <p class="text-sm opacity-80">Collection Rate</p>
        <p class="text-3xl font-bold">{{ stats.feesSummary?.collection_rate || 0 }}%</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <router-link to="/payments" class="card hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-green-500">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Record Payment</h3>
            <p class="text-sm text-gray-600">Add new payment entry</p>
          </div>
        </div>
      </router-link>

      <router-link to="/fees/debtors" class="card hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-red-500">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Debtors List</h3>
            <p class="text-sm text-gray-600">{{ stats.debtorsCount || 0 }} students with balance</p>
          </div>
        </div>
      </router-link>

      <router-link to="/fees" class="card hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-blue-500">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Fee Structures</h3>
            <p class="text-sm text-gray-600">Manage fee templates</p>
          </div>
        </div>
      </router-link>
    </div>

    <!-- Recent Payments -->
    <div class="card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Recent Payments</h2>
        <router-link to="/payments" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
          View All →
        </router-link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Student</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Amount</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Method</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Date</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Receipt</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in stats.recentPayments" :key="payment.id" class="border-b border-gray-100">
              <td class="py-4 px-4">
                <p class="font-medium text-gray-900">{{ payment.student?.full_name }}</p>
                <p class="text-sm text-gray-500">{{ payment.student?.admission_no }}</p>
              </td>
              <td class="py-4 px-4 font-semibold text-green-600">₦{{ formatCurrency(payment.amount) }}</td>
              <td class="py-4 px-4 text-gray-600 capitalize">{{ payment.method?.replace('_', ' ') }}</td>
              <td class="py-4 px-4 text-gray-600">{{ formatDate(payment.payment_date) }}</td>
              <td class="py-4 px-4">
                <button class="text-blue-600 hover:text-blue-800 text-sm">Print</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const stats = ref({
  feesSummary: {},
  recentPayments: [],
  debtorsCount: 0,
})

const fetchDashboardData = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/dashboard/stats`)
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
  }
}

const formatCurrency = (amount) => {
  return (amount || 0).toLocaleString()
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

onMounted(() => {
  fetchDashboardData()
})
</script>
