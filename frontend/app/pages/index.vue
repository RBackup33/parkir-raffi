<script setup lang="ts">
import { ref } from 'vue'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/solid'
import { useRouter } from 'vue-router'

const router = useRouter()
const { $api } = useNuxtApp() as any

const form = ref({
  email: '',
  password: '',
})

const isLoading = ref(false)
const errorMessage = ref('') // <--- INI YANG SEBELUMNYA KURANG / BELUM ADA
const showPassword = ref(false)

const handleLogin = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const response = await $api.post('/login', {
      email: form.value.email,
      password: form.value.password,
    })

    if (response.data && response.data.token) {
      localStorage.setItem('token', response.data.token)
      
      const userRole = response.data.user?.role || 'petugas'
      localStorage.setItem('role', userRole)

      if (userRole === 'admin') {
        router.push('/admin/dashboard')
      } else {
        router.push('/petugas/')
      }
    }
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat login.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md">
      <!-- Header Form -->
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang</h1>
        <p class="text-sm text-gray-500 mt-1">Silakan masuk ke akun Anda</p>
      </div>

      <!-- Pesan Error (Sekarang aman karena errorMessage sudah dideklarasikan) -->
      <div
        v-if="errorMessage"
        class="bg-red-100 border border-red-400 text-red-700 px-5 py-3 rounded-full text-sm mb-4 text-center"
      >
        {{ errorMessage }}
      </div>

      <!-- Form Login -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1 ml-3">
            Email
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="username@gmail.com"
            class="w-full border border-gray-300 rounded-full px-5 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1 ml-3">
            Password
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="••••••••"
              class="w-full border border-gray-300 rounded-full pl-5 pr-12 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
              title="Tampilkan/Sembunyikan Password"
            >
              <EyeIcon v-if="showPassword" class="w-5 h-5" />
              <EyeSlashIcon v-else class="w-5 h-5" />
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-full transition disabled:opacity-50 mt-2 shadow-md cursor-pointer"
        >
          {{ isLoading ? "Memproses..." : "Masuk" }}
        </button>
      </form>
    </div>
  </div>
</template>