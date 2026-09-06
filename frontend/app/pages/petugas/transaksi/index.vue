<template>
  <div class="min-h-screen bg-slate-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-3xl shadow-lg border border-slate-200">
      
      <!-- Header & Tombol Kembali -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-xl font-black text-slate-800 uppercase tracking-wider">
          Daftar Transaksi Parkir
        </h1>

        <div class="flex items-center gap-3">
          <!-- Tombol Kembali ke Dashboard -->
          <button
            @click="router.push('/petugas')"
            class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-md transition flex items-center gap-2 cursor-pointer"
          >
            <span>←</span> Kembali ke Dashboard
          </button>

          <!-- Tombol Transaksi Baru -->
          <NuxtLink 
            to="/petugas/transaksi/payment" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition"
          >
            + Transaksi Baru
          </NuxtLink>
        </div>
      </div>

      <!-- Tabel Data Transaksi -->
      <div class="overflow-x-auto">
        <table class="w-full text-center border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-300 text-xs font-extrabold text-slate-600 uppercase">
              <th class="p-3">No</th>
              <th class="p-3">QR Code</th>
              <th class="p-3">Kode Tiket</th>
              <th class="p-3">Kategori</th>
              <th class="p-3">No Plat</th>
              <th class="p-3">Total Bayar</th>
              <th class="p-3">Uang Bayar</th>
              <th class="p-3">Kembalian</th>
              <th class="p-3">Status</th>
              <th class="p-3">Tanggal</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <tr v-if="loading">
              <td colspan="10" class="p-6 text-slate-400">Memuat data transaksi...</td>
            </tr>
            <tr v-else-if="transaksis.length === 0">
              <td colspan="10" class="p-6 text-slate-400">Belum ada transaksi recorded.</td>
            </tr>
            <tr v-else v-for="(item, index) in transaksis" :key="item.id" class="border-b border-slate-100 font-semibold text-slate-700">
              <td class="p-3">{{ index + 1 }}</td>
              <td class="p-3 flex justify-center">
                <img 
                  v-if="item.kode_tiket" 
                  :src="`http://localhost:8000/api/qrcode/${item.kode_tiket}`" 
                  alt="QR Code" 
                  class="w-12 h-12 object-contain"
                />
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="p-3 font-bold text-slate-800">{{ item.kode_tiket }}</td>
              <td class="p-3 capitalize">{{ item.kategori || 'Motor' }}</td>
              <td class="p-3 uppercase font-bold">{{ item.no_plat || '-' }}</td>
              <td class="p-3">Rp {{ Number(item.total_bayar || 0).toLocaleString('id-ID') }}</td>
              <td class="p-3">Rp {{ Number(item.uang_bayar || 0).toLocaleString('id-ID') }}</td>
              <td class="p-3">Rp {{ Number(item.kembalian || 0).toLocaleString('id-ID') }}</td>
              <td class="p-3">
                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700">
                  {{ item.status || 'lunas' }}
                </span>
              </td>
              <td class="p-3">{{ formatDate(item.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({
  middleware: 'auth'
});

const { $api } = useNuxtApp()
const router = useRouter()

const transaksis = ref<any[]>([])
const loading = ref(true)

const fetchTransaksi = async () => {
  loading.value = true
  try {
    const res = await $api.get('/transaksi')
    transaksis.value = res.data.data || res.data || []
  } catch (err) {
    console.error('Gagal mengambil data transaksi:', err)
    transaksis.value = []
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString: any) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('id-ID')
}

onMounted(() => {
  fetchTransaksi()
})
</script>