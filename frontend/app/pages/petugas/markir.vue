<template>
  <div class="p-6 space-y-6">
    <h1 class="text-xl font-bold text-slate-800">Monitoring Kendaraan Sedang Parkir</h1>

    <!-- TABEL KENDARAAN AKTIF -->
    <div class="bg-white p-6 rounded-3xl shadow-xs border border-slate-200/60 overflow-x-auto">
      <h3 class="text-base font-extrabold text-slate-800 mb-4">Daftar Kendaraan Belum Keluar</h3>

      <table class="w-full text-left border-collapse min-w-[700px]">
        <thead>
          <tr class="border-b border-slate-200 text-xs font-bold text-slate-400 uppercase">
            <th class="p-3">Kode Tiket</th>
            <th class="p-3">Kategori</th>
            <th class="p-3">No. Plat</th>
            <th class="p-3">Waktu Masuk</th>
            <th class="p-3">Status</th>
          </tr>
        </thead>
        <tbody class="text-xs divide-y divide-slate-100">
          <tr v-if="kendaraanAktifList.length === 0">
            <td colspan="5" class="p-4 text-center text-slate-400">Tidak ada kendaraan yang sedang parkir.</td>
          </tr>
          <tr v-for="item in kendaraanAktifList" :key="item.id" class="hover:bg-slate-50 font-semibold text-slate-700">
            <td class="p-3 font-bold text-slate-800">{{ item.kode_tiket }}</td>
            <td class="p-3 capitalize">{{ item.kategori || 'Motor' }}</td>
            <td class="p-3 uppercase">{{ item.no_plat || '-' }}</td>
            <td class="p-3 text-slate-500">{{ formatTanggal(item.created_at) }}</td>
            <td class="p-3">
              <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full font-bold text-[10px]">
                Sedang Parkir
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const { $api } = useNuxtApp()
const kendaraanAktifList = ref<any[]>([])
let intervalId: any = null

// Fungsi ambil data kendaraan aktif
const fetchKendaraanAktif = async () => {
  try {
    const res = await $api.get('/parkir/aktif')
    if (res.data && res.data.data) {
      kendaraanAktifList.value = res.data.data
    }
  } catch (error) {
    console.error("Gagal mengambil data kendaraan aktif", error)
  }
}

const formatTanggal = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('id-ID', { 
    day: '2-digit', month: '2-digit', year: 'numeric', 
    hour: '2-digit', minute: '2-digit', second: '2-digit' 
  })
}

onMounted(() => {
  fetchKendaraanAktif()
  // Auto refresh tiap 3 detik agar sinkron realtime
  intervalId = setInterval(fetchKendaraanAktif, 3000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>