<template>
  <div class="min-h-screen bg-slate-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-3xl shadow-lg border border-slate-200">
      <h1 class="text-xl font-black text-center text-slate-800 uppercase tracking-wider mb-6">
        LAPORAN PEMBAYARAN MEMBER
      </h1>

      <div class="flex justify-between items-center mb-6 gap-4 print:hidden">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search..." 
          class="w-64 p-2.5 bg-slate-200 rounded-xl text-sm border-none focus:ring-2 focus:ring-slate-400"
        />

        <NuxtLink 
          to="/petugas/laporan/non-member" 
          class="bg-slate-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800"
        >
          Non Member
        </NuxtLink>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-center border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-300 text-xs font-extrabold text-slate-600 uppercase">
              <th class="p-3">No Plat</th>
              <th class="p-3">Nama</th>
              <th class="p-3">Bulan</th>
              <th class="p-3">PAYMENT</th>
              <th class="p-3">CASH</th>
              <th class="p-3">KEMBALI</th>
              <th class="p-3">PETUGAS</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <tr v-if="filteredList.length === 0">
              <td colspan="7" class="p-6 text-slate-400">Tidak ada data pembayaran member.</td>
            </tr>
            <tr v-else v-for="item in filteredList" :key="item.id" class="border-b border-slate-100 font-semibold text-slate-700">
              <td class="p-3 uppercase">{{ item.no_plat || 'B 1234 ABC' }}</td>
              <td class="p-3">{{ item.nama_member }}</td>
              <td class="p-3">{{ item.bulan || '08/2026' }}</td>
              <td class="p-3">Rp {{ formatRupiah(item.total_harga || 150000) }}</td>
              <td class="p-3">Rp {{ formatRupiah(item.jumlah_bayar || 150000) }}</td>
              <td class="p-3">Rp {{ formatRupiah(item.kembalian || 0) }}</td>
              <td class="p-3 capitalize">{{ item.petugas?.nama || 'Admin' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

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
  middleware: ['auth'] 
})

const { $api } = useNuxtApp()
const laporanList = ref<any[]>([])
const searchQuery = ref('')

const fetchLaporan = async () => {
  try {
    const res = await $api.get('/laporan/member')
    laporanList.value = res.data.data || []
  } catch (err) {
    console.error('Gagal mengambil laporan member:', err)
  }
}

const filteredList = computed(() => {
  return laporanList.value.filter(item => 
    item.nama_member?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    item.no_plat?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const cetak = () => window.print()
const formatRupiah = (val: any) => new Intl.NumberFormat('id-ID').format(Number(val || 0))

onMounted(() => fetchLaporan())
</script>