<template>
  <div class="text-center py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ pageTitle }}</h1>
    <p class="text-gray-600 mb-8">{{ pageDescription }}</p>
    <div class="card max-w-md mx-auto">
      <p class="text-gray-500">This page is under development.</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const pageTitle = computed(() => route.meta.title || 'Page')
const pageDescription = computed(() => `The ${pageTitle.value} feature is coming soon.`)
</script>
