<script setup lang="ts">
import { ref } from "vue";
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/24/solid";

const form = ref({
  email: "",
  password: "",
});

const isLoading = ref(false);
const errorMessage = ref("");
const showPassword = ref(false);

const handleLogin = async () => {
  errorMessage.value = "";
  isLoading.value = true;

  try {
    // Ubah URL '/api/login' sesuai dengan endpoint backend/API login Anda
    const response = await $fetch("/api/login", {
      method: "POST",
      body: form.value,
    });

    // Jika login berhasil, arahkan ke halaman utama
    navigateTo("/");
  } catch (error: any) {
    // Menangkap pesan error dari backend
    errorMessage.value = error.data?.message || "Terjadi kesalahan saat login.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md">
      <!-- Header Form -->
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang</h1>
        <p class="text-sm text-gray-500 mt-1">Silakan masuk ke akun Anda</p>
      </div>

      <!-- Pesan Error -->
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
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-full transition disabled:opacity-50 mt-2 shadow-md"
        >
          {{ isLoading ? "Memproses..." : "Masuk" }}
        </button>
      </form>

      <!-- Pilihan Registrasi -->
      <div class="text-center mt-6 pt-4 border-t border-gray-100">
        <p class="text-sm text-gray-600">
          Belum punya akun?
          <NuxtLink
            to="/register"
            class="text-blue-600 hover:text-blue-700 font-semibold underline transition ml-1"
          >
            Daftar sekarang
          </NuxtLink>
        </p>
      </div>
    </div>
  </div>
</template>