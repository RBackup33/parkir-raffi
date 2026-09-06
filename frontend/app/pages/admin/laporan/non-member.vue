<template>
  <div class="min-h-screen bg-slate-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-3xl shadow-lg border border-slate-200">
      <h1 class="text-xl font-black text-center text-slate-800 uppercase tracking-wider mb-6">
        LAPORAN PEMBAYARAN NON MEMBER
      </h1>

      <!-- Top Filter Bar -->
      <div class="flex justify-between items-center mb-6 gap-4 print:hidden">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search..." 
          class="w-64 p-2.5 bg-slate-200 rounded-xl text-sm border-none focus:ring-2 focus:ring-slate-400"
        />

        <NuxtLink 
          to="/admin/laporan/member" 
          class="bg-slate-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800"
        >
          Member
        </NuxtLink>
      </div>

      <!-- Tabel Laporan Non-Member -->
      <div class="overflow-x-auto">
        <table class="w-full text-center border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-300 text-xs font-extrabold text-slate-600 uppercase">
              <th class="p-3">No Plat</th>
              <th class="p-3">Tiket</th>
              <th class="p-3">PAYMENT</th>
              <th class="p-3">CASH</th>
              <th class="p-3">KEMBALI</th>
              <th class="p-3">PETUGAS</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <tr v-if="filteredList.length === 0">
              <td colspan="6" class="p-6 text-slate-400">Tidak ada data transaksi non member.</td>
            </tr>
            <tr v-else v-for="item in filteredList" :key="item.id" class="border-b border-slate-100 font-semibold text-slate-700">
              <td class="p-3 uppercase font-bold">{{ item.no_plat }}</td>
              <td class="p-3">{{ item.kode_tiket }}</td>
              <td class="p-3">Rp {{ formatRupiah(item.total_bayar) }}</td>
              <td class="p-3">Rp {{ formatRupiah(item.uang_bayar) }}</td>
              <td class="p-3">Rp {{ formatRupiah(item.kembalian) }}</td>
              <td class="p-3 capitalize">{{ item.petugas?.nama || 'Petugas 1' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Action Button Cetak -->
      <div class="mt-6 flex flex-col items-center print:hidden">
        <button 
          @click="cetak" 
          class="flex items-center gap-2 bg-slate-100 border border-slate-300 px-6 py-2.5 rounded-xl font-bold text-xs text-slate-700 hover:bg-slate-200"
        >
          🖨️ Cetak
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

definePageMeta({ 
  middleware: ['auth', 'cek-admin'] 
})

const { $api } = useNuxtApp()
const laporanList = ref<any[]>([])
const searchQuery = ref('')

const fetchLaporan = async () => {
  try {
    const res = await $api.get('/laporan/non-member')
    laporanList.value = res.data.data || []
  } catch (err) {
    console.error('Gagal mengambil laporan non-member:', err)
  }
}

const filteredList = computed(() => {
  return laporanList.value.filter(item => 
    item.no_plat?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    item.kode_tiket?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const cetak = () => window.print()
const formatRupiah = (val: any) => new Intl.NumberFormat('id-ID').format(Number(val || 0))

onMounted(() => fetchLaporan())
</script>

<style scoped>
@media print {
  body * { visibility: hidden; }
  .max-w-5xl, .max-w-5xl * { visibility: visible; }
  .max-w-5xl { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none; }
}
</style>