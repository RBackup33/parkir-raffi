<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center p-5">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-[440px]">
      <h1 class="text-xl font-black text-center text-slate-800 uppercase tracking-wider mb-6">
        Tambah Akun Petugas / Admin
      </h1>

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Nama Petugas</label>
          <input v-model="form.name" type="text" placeholder="Masukkan nama" class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:outline-none" />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
          <input v-model="form.email" type="email" placeholder="petugas@parkir.com" class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:outline-none" />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
          <input v-model="form.password" type="password" placeholder="Minimal 6 karakter" class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:outline-none" />
        </div>

        <!-- PILIHAN ROLE (ADMIN / PETUGAS) -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Hak Akses (Role)</label>
          <select v-model="form.role" class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:outline-none">
            <option value="petugas">Petugas (Kasir)</option>
            <option value="admin">Admin (Full Access)</option>
          </select>
        </div>

        <div class="pt-2 flex flex-col gap-2">
          <button @click="simpanPetugas" :disabled="loading" type="button" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl font-bold text-sm shadow-md transition uppercase tracking-wider cursor-pointer">
            {{ loading ? 'Menyimpan...' : 'Simpan Akun' }}
          </button>
          <button @click="router.push('/admin/petugas')" type="button" class="w-full bg-slate-200 hover:bg-slate-300 text-slate-700 py-2.5 rounded-xl font-bold text-xs transition cursor-pointer">
            Kembali
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

definePageMeta({ 
  middleware: ['auth', 'cek-admin'] 
})

const { $api } = useNuxtApp()
const router = useRouter()
const loading = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'petugas' // Default sebagai petugas
})

const simpanPetugas = async () => {
  if (!form.name || !form.email || !form.password) {
    alert('Semua kolom wajib diisi!')
    return
  }

  if (form.password.length < 6) {
    alert('Password minimal 6 karakter!')
    return
  }

  loading.value = true
  try {
    await $api.post('/admin/petugas', form)
    alert('Akun berhasil ditambahkan!')
    router.push('/admin/petugas')
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Gagal menyimpan akun.')
  } finally {
    loading.value = false
  }
}
</script>