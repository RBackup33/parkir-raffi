<template>
  <div class="min-h-screen bg-slate-100 p-8">
    <div class="max-w-5xl mx-auto space-y-6">
      
      <!-- Header & Tombol Aksi -->
      <div class="bg-white p-6 rounded-3xl shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-black text-slate-800 uppercase tracking-wider">Manajemen Akun Petugas</h1>
          <p class="text-xs text-slate-500 font-semibold mt-1">Kelola daftar akun petugas e-parkir</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button @click="router.push('/admin/dashboard')" class="flex-1 sm:flex-none bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-3 rounded-xl transition cursor-pointer text-center">
            Kembali
          </button>
          
          <!-- TOMBOL TAMBAH PETUGAS -->
          <button @click="router.push('/admin/petugas/tambah')" class="flex-1 sm:flex-none bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-3 rounded-xl transition cursor-pointer shadow-md text-center">
            + Tambah Petugas
          </button>
        </div>
      </div>

      <!-- Tabel Petugas -->
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                <th class="p-3">No</th>
                <th class="p-3">Nama Petugas</th>
                <th class="p-3">Email</th>
                <th class="p-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
              <tr v-for="(petugas, index) in listPetugas" :key="petugas.id" class="hover:bg-slate-50">
                <td class="p-3 font-bold text-slate-900">{{ index + 1 }}</td>
                <td class="p-3 font-semibold text-slate-800">{{ petugas.name }}</td>
                <td class="p-3 text-slate-600">{{ petugas.email }}</td>
                <td class="p-3 text-center space-x-1.5">
                  <button
                    @click="openResetModal(petugas)"
                    class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer"
                  >
                    Reset Password
                  </button>
                  <button
                    @click="hapusPetugas(petugas.id, petugas.name)"
                    class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="listPetugas.length === 0">
                <td colspan="4" class="p-6 text-center text-slate-400 font-bold">Belum ada data petugas.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal Reset Password -->
    <div v-if="isResetModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-xl space-y-4">
        <h2 class="text-base font-bold text-slate-800">Reset Password: {{ activePetugas?.name }}</h2>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru</label>
          <input
            v-model="newPassword"
            type="password"
            placeholder="Minimal 6 karakter"
            class="w-full p-3 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900"
          />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            @click="isResetModalOpen = false"
            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitResetPassword"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer"
          >
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({ 
  middleware: ['auth', 'cek-admin'] 
})

const { $api } = useNuxtApp()
const router = useRouter()

const listPetugas = ref<any[]>([])
const isResetModalOpen = ref(false)
const activePetugas = ref<any>(null)
const newPassword = ref('')

const fetchPetugas = async () => {
  try {
    const res = await $api.get('/admin/petugas')
    if (res.data && res.data.status) {
      listPetugas.value = res.data.data
    }
  } catch (err: any) {
    console.error('Gagal mengambil data petugas:', err)
    if (err.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
    }
  }
}

const openResetModal = (petugas: any) => {
  activePetugas.value = petugas
  newPassword.value = ''
  isResetModalOpen.value = true
}

const submitResetPassword = async () => {
  if (!newPassword.value || newPassword.value.length < 6) {
    alert('Password baru minimal harus 6 karakter!')
    return
  }

  try {
    const res = await $api.put(`/admin/petugas/${activePetugas.value.id}/reset-password`, {
      password: newPassword.value
    })
    alert(res.data.message || 'Password berhasil direset!')
    isResetModalOpen.value = false
    fetchPetugas()
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Gagal mereset password.')
  }
}

// Fungsi Hapus Petugas
const hapusPetugas = async (id: number, nama: string) => {
  if (!confirm(`Apakah kamu yakin ingin menghapus akun petugas "${nama}"?`)) {
    return
  }

  try {
    const res = await $api.delete(`/admin/petugas/${id}`)
    alert(res.data.message || 'Akun petugas berhasil dihapus!')
    fetchPetugas() // Refresh ulang tabel
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Gagal menghapus akun petugas.')
  }
}

onMounted(() => {
  fetchPetugas()
})
</script>