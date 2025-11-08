<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Debtors</h1>
    <p class="text-gray-600 mb-6">Students with outstanding fees</p>
    
    <div class="card overflow-hidden p-0">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Outstanding Fees</h2>
        <button @click="sendBulkReminders" class="btn-secondary">
          Send Reminders to All
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
                <button @click="sendReminder(debtor.id)" class="text-green-600 hover:text-green-900 mr-4">
                  Send Reminder
                </button>
                <button @click="recordPayment(debtor.student_id)" class="text-blue-600 hover:text-blue-900">
                  Record Payment
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
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const toast = useToast()

const debtors = ref([])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-NG').format(amount)
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

const sendReminder = async (debtorId) => {
  try {
    await axios.post(`${API_BASE_URL}/debtors/${debtorId}/remind`)
    toast.success('Reminder sent successfully!')
  } catch (error) {
    toast.error('Failed to send reminder')
    console.error(error)
  }
}

const sendBulkReminders = async () => {
  try {
    await axios.post(`${API_BASE_URL}/debtors/remind`)
    toast.success('Reminders sent to all debtors!')
  } catch (error) {
    toast.error('Failed to send reminders')
    console.error(error)
  }
}

const recordPayment = (studentId) => {
  // Navigate to payment recording page
  console.log('Record payment for student:', studentId)
}

onMounted(() => {
  fetchDebtors()
})
</script>

