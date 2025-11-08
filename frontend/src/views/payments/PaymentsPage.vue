<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Payments</h1>
    <p class="text-gray-600 mb-6">View and manage payment records</p>
    
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt No.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(payment.payment_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ payment.student_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₦{{ formatCurrency(payment.amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ payment.method }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ payment.receipt_no }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="downloadReceipt(payment.id)" class="text-blue-600 hover:text-blue-900">
                  Download Receipt
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

const payments = ref([])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-NG').format(amount)
}

const formatDate = (date) => {
  return format(new Date(date), 'dd/MM/yyyy')
}

const fetchPayments = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/payments`)
    payments.value = response.data.data
  } catch (error) {
    toast.error('Failed to fetch payments')
    console.error(error)
  }
}

const downloadReceipt = async (id) => {
  try {
    const response = await axios.get(`${API_BASE_URL}/payments/${id}/receipt`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `receipt-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    toast.success('Receipt downloaded successfully!')
  } catch (error) {
    toast.error('Failed to download receipt')
    console.error(error)
  }
}

onMounted(() => {
  fetchPayments()
})
</script>

