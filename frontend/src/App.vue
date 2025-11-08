<template>
  <div class="min-h-screen bg-gray-50">
    <router-view v-if="routerReady" />
    <div v-else class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const routerReady = ref(false)

onMounted(() => {
  // Small delay to ensure stores are initialized
  setTimeout(() => {
    routerReady.value = true
  }, 100)
})
</script>
