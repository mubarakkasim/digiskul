<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="max-w-md w-full">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $t('app.name') }}</h1>
        <p class="text-gray-600">{{ $t('app.subtitle') }}</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">{{ $t('auth.login') }}</h2>
          <select
            v-model="selectedLocale"
            @change="changeLanguage"
            class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
          >
            <option value="en">English</option>
            <option value="ha">Hausa</option>
            <option value="ar">العربية</option>
            <option value="fr">Français</option>
          </select>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('auth.email') }}
            </label>
            <input
              v-model="form.email"
              type="text"
              required
              class="input-field"
              :placeholder="$t('auth.email')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('auth.password') }}
            </label>
            <input
              v-model="form.password"
              type="password"
              required
              class="input-field"
              :placeholder="$t('auth.password')"
            />
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center">
              <input
                v-model="form.remember"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-600">{{ $t('auth.remember') }}</span>
            </label>
            <a href="#" class="text-sm text-blue-600 hover:text-blue-700">
              {{ $t('auth.forgotPassword') }}
            </a>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary py-3 text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading">{{ $t('auth.signingIn') }}</span>
            <span v-else>{{ $t('auth.signIn') }}</span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            {{ $t('auth.needHelp') || 'Need help? Contact your administrator' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useI18n } from 'vue-i18n'

const { locale, t } = useI18n()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const loading = ref(false)
const selectedLocale = ref(locale.value || 'en')

const changeLanguage = () => {
  locale.value = selectedLocale.value
  localStorage.setItem('locale', selectedLocale.value)
}

const handleLogin = async () => {
  loading.value = true
  try {
    await authStore.login({
      email: form.value.email,
      password: form.value.password
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const savedLocale = localStorage.getItem('locale') || 'en'
  selectedLocale.value = savedLocale
  locale.value = savedLocale
})
</script>
