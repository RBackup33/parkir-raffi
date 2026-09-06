<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({ 
  middleware: ['auth', 'cek-admin'] 
})

const { $api } = useNuxtApp()
const router = useRouter()

const rekapData = ref({
  total_pendapatan: 0,
  total_transaksi: 0,
  total_motor: 0,
  total_mobil: 0,
  riwayat_laporan: []
})

const isLoading = ref(false)

// Fungsi mengambil data rekap dari backend
const fetchRekap = async () => {
  isLoading.value = true
  try {
    const res = await $api.get('/admin/laporan') // Endpoint backend Laravel
    if (res.data && res.data.data) {
      rekapData.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal memuat data rekap', err)
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (val: any) => {
  const num = Number(val)
  if (isNaN(num)) return '0'
  return new Intl.NumberFormat('id-ID').format(num)
}

// Fitur Cetak Laporan langsung dari browser
const cetakLaporan = () => {
  window.print()
}

onMounted(() => {
  fetchRekap()
})
</script>

<template>
  <div class="min-h-screen bg-slate-100 p-6 md:p-8">
    <div class="max-w-6xl mx-auto space-y-6">
      
      <!-- Header Laporan & Tombol Aksi -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-3xl shadow-sm gap-4 border border-slate-200/60">
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pusat Rekapitulasi</span>
          <h1 class="text-xl font-black text-slate-800 uppercase tracking-wider">Laporan & Rekap Pendapatan</h1>
          <p class="text-xs text-slate-500 font-semibold mt-0.5">Sistem E-Parkir Plaza Andalas</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="cetakLaporan" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition cursor-pointer shadow-sm">
            🖨️ Cetak / Print Laporan
          </button>
          <button @click="router.push('/admin/dashboard')" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl transition cursor-pointer">
            ← Kembali ke Dashboard
          </button>
        </div>
      </div>

      <!-- Ringkasan Kartu Rekap -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60">
          <p class="text-xs font-bold text-slate-400 mb-1">Total Pendapatan</p>
          <h3 class="text-xl font-black text-emerald-600">Rp {{ formatRupiah(rekapData.total_pendapatan) }}</h3>
          <p class="text-[10px] text-slate-400 mt-1 font-medium">Akumulasi kasir keseluruhan</p>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60">
          <p class="text-xs font-bold text-slate-400 mb-1">Total Transaksi</p>
          <h3 class="text-2xl font-black text-slate-800">{{ rekapData.total_transaksi || 0 }}</h3>
          <p class="text-[10px] text-slate-400 mt-1 font-medium">Kendaraan keluar terproses</p>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60">
          <p class="text-xs font-bold text-slate-400 mb-1">Motor Keluar</p>
          <h3 class="text-2xl font-black text-blue-600">{{ rekapData.total_motor || 0 }}</h3>
          <p class="text-[10px] text-slate-400 mt-1 font-medium">Unit kendaraan roda dua</p>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60">
          <p class="text-xs font-bold text-slate-400 mb-1">Mobil Keluar</p>
          <h3 class="text-2xl font-black text-purple-600">{{ rekapData.total_mobil || 0 }}</h3>
          <p class="text-[10px] text-slate-400 mt-1 font-medium">Unit kendaraan roda empat</p>
        </div>

      </div>

      <!-- Tabel Detail Riwayat Rekap -->
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200/60">
        <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-4">Detail Riwayat Transaksi Laporan</h3>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                <th class="p-3">Kode Tiket</th>
                <th class="p-3">Plat Nomor</th>
                <th class="p-3">Durasi</th>
                <th class="p-3">Tarif Bayar</th>
                <th class="p-3">Waktu Keluar</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
              <tr v-for="item in rekapData.riwayat_laporan" :key="item.id" class="hover:bg-slate-50">
                <td class="p-3 font-bold text-slate-900">{{ item.kode_tiket }}</td>
                <td class="p-3 uppercase font-semibold">{{ item.plat_nomor || item.no_plat || '-' }}</td>
                <td class="p-3">{{ item.durasi_jam || '-' }} Jam</td>
                <td class="p-3 font-bold text-emerald-600">Rp {{ formatRupiah(item.total_tarif || item.total_bayar || 0) }}</td>
                <td class="p-3 text-slate-500">{{ item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '-' }}</td>
              </tr>
              <tr v-if="!rekapData.riwayat_laporan || rekapData.riwayat_laporan.length === 0">
                <td colspan="5" class="p-6 text-center text-slate-400 font-bold">Belum ada data rekap transaksi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>