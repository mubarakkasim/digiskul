<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Fees Management</h1>
      <p class="text-gray-600">Manage student fees and payments</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card">
        <p class="text-sm text-gray-600 mb-1">Total Fees</p>
        <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(fees.total) }}</p>
      </div>
      <div class="card">
        <p class="text-sm text-gray-600 mb-1">Paid</p>
        <p class="text-2xl font-bold text-green-600">₦{{ formatCurrency(fees.paid) }}</p>
      </div>
      <div class="card">
        <p class="text-sm text-gray-600 mb-1">Outstanding</p>
        <p class="text-2xl font-bold text-red-600">₦{{ formatCurrency(fees.outstanding) }}</p>
      </div>
      <div class="card">
        <p class="text-sm text-gray-600 mb-1">Debtors</p>
        <p class="text-2xl font-bold text-orange-600">{{ fees.debtor_count }}</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b border-gray-200">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-4 py-2 font-medium border-b-2 transition-colors"
        :class="activeTab === tab.key ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Fees List -->
    <div v-if="activeTab === 'fees'" class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="fee in feesList" :key="fee.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ fee.student_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ fee.fee_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₦{{ formatCurrency(fee.amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(fee.due_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-semibold rounded-full"
                  :class="fee.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                  {{ fee.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="recordPayment(fee.id)" class="text-blue-600 hover:text-blue-900">
                  Record Payment
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Debtors List -->
    <div v-if="activeTab === 'debtors'" class="card overflow-hidden p-0">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Outstanding Fees</h2>
        <button @click="sendReminders" class="btn-secondary">
          Send Reminders
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Overdue</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="debtor in debtors" :key="debtor.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ debtor.student_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">₦{{ formatCurrency(debtor.outstanding) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ debtor.days_overdue }} days</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="recordPayment(debtor.student_id)" class="text-blue-600 hover:text-blue-900 mr-4">
                  Record Payment
                </button>
                <button @click="sendReminder(debtor.id)" class="text-green-600 hover:text-green-900">
                  Send Reminder
                </button>
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
import { format } from 'date-fns'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()

const activeTab = ref('fees')
const fees = ref({
  total: 0,
  paid: 0,
  outstanding: 0,
  debtor_count: 0
})
const feesList = ref([])
const debtors = ref([])

const tabs = [
  { key: 'fees', label: 'All Fees' },
  { key: 'debtors', label: 'Debtors' }
]

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-NG').format(amount)
}

const formatDate = (date) => {
  return format(new Date(date), 'dd/MM/yyyy')
}

const fetchFees = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/fees`)
    fees.value = response.data.summary
    feesList.value = response.data.data
  } catch (error) {
    toast.error('Failed to fetch fees')
    console.error(error)
  }
}

const fetchDebtors = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/debtors`)
    debtors.value = response.data.data
  } catch (error) {
    toast.error('Failed to fetch debtors')
    console.error(error)
  }
}

const recordPayment = (feeId) => {
  // Open payment modal
  console.log('Record payment for:', feeId)
}

const sendReminder = async (debtorId) => {
  try {
    await axios.post(`${API_BASE_URL}/debtors/${debtorId}/remind`)
    toast.success('Reminder sent successfully!')
  } catch (error) {
    toast.error('Failed to send reminder')
    console.error(error)
  }
}

const sendReminders = async () => {
  try {
    await axios.post(`${API_BASE_URL}/debtors/remind`)
    toast.success('Reminders sent successfully!')
  } catch (error) {
    toast.error('Failed to send reminders')
    console.error(error)
  }
}

onMounted(() => {
  fetchFees()
  fetchDebtors()
})
</script>

