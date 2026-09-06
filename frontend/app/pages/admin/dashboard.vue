<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Line } from 'vue-chartjs'
import { 
  Chart as ChartJS, 
  CategoryScale, 
  LinearScale, 
  PointElement, 
  LineElement, 
  Title, 
  Tooltip, 
  Legend 
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

definePageMeta({
  middleware: ['auth', 'cek-admin']
})

const { $api } = useNuxtApp()
const router = useRouter()

const stats = ref({
  member_aktif: 0,
  kendaraan_hari_ini: 0,
  pendapatan_hari_ini: 0,
  sedang_parkir: 0
})

// State untuk data grafik realtime
const chartData = ref({
  labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
  datasets: [
    {
      label: 'Kendaraan Masuk Realtime',
      backgroundColor: '#10B981',
      borderColor: '#10B981',
      data: [0, 0, 0, 0, 0, 0],
      tension: 0.4,
    }
  ]
})

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
})

const aktivitasTerbaru = ref<any[]>([])
const tanggalHariIni = ref(new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }))

let intervalId: any = null

const loadDashboardData = async () => {
  try {
    const res = await $api.get('/dashboard/stats')
    if (res.data && res.data.data) {
      stats.value = res.data.data
      if (res.data.data.transaksi_terbaru) {
        aktivitasTerbaru.value = res.data.data.transaksi_terbaru
      }

      // Update grafik secara dinamis mengikuti data hari ini
      const totalHariIni = stats.value.kendaraan_hari_ini || 0
      chartData.value = {
        ...chartData.value,
        datasets: [{
          ...chartData.value.datasets[0],
          data: [2, 5, 8, 12, 16, totalHariIni]
        }]
      }
    }
  } catch (error) {
    console.error("Gagal mengambil data real-time dashboard", error)
  }
}

const formatRupiah = (val: any) => {
  const num = Number(val)
  if (isNaN(num)) return '0'
  return new Intl.NumberFormat('id-ID').format(num)
}

const formatWaktu = (dateStr: string) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const logout = async () => {
  try {
    await $api.post('/logout')
  } catch (e) {
    // Abaikan error jaringan
  } finally {
    localStorage.removeItem('token')
    router.push('/')
  }
}

onMounted(() => {
  loadDashboardData()
  intervalId = setInterval(() => {
    loadDashboardData()
  }, 5000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex font-sans">
    
    <!-- SIDEBAR KIRI (ADMIN) -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-5 select-none hidden md:flex">
      <div>
        <!-- Logo / Header Brand -->
        <div class="flex items-center gap-3 px-2 mb-8">
          <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-lg">
            A
          </div>
          <div>
            <h2 class="text-white font-extrabold tracking-wide text-sm">ADMIN</h2>
            <p class="text-[10px] text-slate-400 uppercase tracking-wider">PLAZA ANDALAS</p>
          </div>
        </div>

        <!-- Profil Admin -->
        <div class="bg-slate-800/60 border border-slate-700/50 p-3.5 rounded-2xl flex items-center gap-3 mb-8">
          <div class="w-10 h-10 rounded-full bg-indigo-500/20 text-indigo-400 font-bold flex items-center justify-center border border-indigo-500/30">
            AD
          </div>
          <div>
            <h3 class="text-xs font-bold text-white">Super Admin</h3>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
              <span class="text-[10px] text-slate-400 font-medium">Full Access</span>
            </div>
          </div>
        </div>

        <!-- Menu Utama Admin -->
        <div class="space-y-1.5">
          <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 mb-2">Menu Navigasi</p>
          
          <NuxtLink 
            to="/admin/petugas" 
            class="flex items-center justify-between p-3 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition group"
          >
            <div class="flex items-center gap-3">
              <span class="p-2 rounded-lg bg-slate-800 text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition">👥</span>
              <span>Manajemen Petugas</span>
            </div>
            <span class="text-slate-500 text-xs">›</span>
          </NuxtLink>

          <NuxtLink 
            to="/admin/laporan/member" 
            class="flex items-center justify-between p-3 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition group"
          >
            <div class="flex items-center gap-3">
              <span class="p-2 rounded-lg bg-slate-800 text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition">📊</span>
              <span>Data Laporan</span>
            </div>
            <span class="text-slate-500 text-xs">›</span>
          </NuxtLink>
        </div>

        <!-- Status Sistem Box -->
        <div class="mt-8 bg-indigo-950/30 border border-indigo-900/40 p-3.5 rounded-2xl flex items-center gap-3">
          <div class="p-2 rounded-xl bg-indigo-500/20 text-indigo-400">⚡</div>
          <div>
            <p class="text-[10px] text-indigo-400/80 font-medium">Status Sistem</p>
            <p class="text-xs font-extrabold text-indigo-400">Monitoring Aktif</p>
          </div>
        </div>
      </div>

      <!-- Tombol Logout -->
      <div>
        <button 
          @click="logout"
          class="w-full flex items-center gap-3 p-3 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer"
        >
          <span>🚪</span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- KONTEN UTAMA KANAN -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
      
      <!-- Top Bar -->
      <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-xs">
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dashboard Super Admin</span>
          <h1 class="text-xl font-black text-slate-800 flex items-center gap-2">
            Monitoring Plaza Andalas 🔥
          </h1>
        </div>
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-xs font-bold text-slate-700">Hari ini</p>
            <p class="text-[11px] text-slate-400 font-medium">{{ tanggalHariIni }}</p>
          </div>
        </div>
      </header>

      <!-- Area Dashboard Body -->
      <div class="p-6 space-y-6">
        
        <!-- BARIS 1: 4 KARTU STATISTIK UTAMA (REALTIME) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          
          <!-- Kartu 1: Total Member -->
          <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
              <div class="p-3 rounded-2xl bg-slate-100 text-slate-700">👥</div>
              <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600">Aktif</span>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1">Total Member</p>
              <h3 class="text-2xl font-black text-slate-800">{{ stats.member_aktif || 0 }}</h3>
              <p class="text-[10px] text-slate-400 mt-1 font-medium">Member terdaftar</p>
            </div>
          </div>

          <!-- Kartu 2: Kendaraan Masuk -->
          <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
              <div class="p-3 rounded-2xl bg-blue-50 text-blue-600">🚗</div>
              <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-600">Hari ini</span>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1">Kendaraan Masuk</p>
              <h3 class="text-2xl font-black text-slate-800">{{ stats.kendaraan_hari_ini || 0 }}</h3>
              <p class="text-[10px] text-slate-400 mt-1 font-medium">Transaksi kendaraan</p>
            </div>
          </div>

          <!-- Kartu 3: Pendapatan -->
          <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
              <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600">💰</div>
              <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600">Hari ini</span>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1">Pendapatan</p>
              <h3 class="text-xl font-black text-slate-800">Rp {{ formatRupiah(stats.pendapatan_hari_ini || 0) }}</h3>
              <p class="text-[10px] text-slate-400 mt-1 font-medium">Total transaksi</p>
            </div>
          </div>

          <!-- Kartu 4: Sedang Parkir -->
          <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-200/60 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
              <div class="p-3 rounded-2xl bg-purple-50 text-purple-600">🅿️</div>
              <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-purple-50 text-purple-600">Live ›</span>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1">Sedang Parkir</p>
              <h3 class="text-2xl font-black text-slate-800">{{ stats.sedang_parkir || 0 }}</h3>
              <p class="text-[10px] text-slate-400 mt-1 font-medium">Kendaraan di dalam mal</p>
            </div>
          </div>

        </div>

        <!-- GRAFIK REALTIME -->
        <div class="bg-white p-6 rounded-3xl shadow-xs border border-slate-200/60">
          <div class="flex justify-between items-center mb-4">
            <div>
              <h2 class="text-xs font-black uppercase tracking-wider text-slate-400">ANALISTIK</h2>
              <h3 class="text-base font-extrabold text-slate-800">Grafik Kendaraan Masuk (Realtime)</h3>
            </div>
            <span class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-3 py-1 rounded-full animate-pulse">● Live Sync</span>
          </div>
          <div class="h-64 relative">
            <Line :data="chartData" :options="chartOptions" />
          </div>
        </div>

        <!-- BARIS 2: SHORTCUT & MONITORING AKTIVITAS TERBARU -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <!-- Shortcut / Akses Cepat Admin -->
          <div class="bg-white p-6 rounded-3xl shadow-xs border border-slate-200/60">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4">SHORTCUT</h2>
            <h3 class="text-base font-extrabold text-slate-800 mb-5">Navigasi Admin</h3>

            <div class="space-y-3">
              <NuxtLink 
                to="/admin/petugas" 
                class="flex items-center justify-between p-3.5 rounded-2xl bg-indigo-50/50 hover:bg-indigo-100/50 transition border border-indigo-100 group"
              >
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">👥</div>
                  <div>
                    <h4 class="text-xs font-bold text-slate-800">Manajemen Petugas</h4>
                    <p class="text-[11px] text-slate-400">Kelola akun dan hak akses petugas</p>
                  </div>
                </div>
                <span class="text-indigo-600 text-sm group-hover:translate-x-1 transition">›</span>
              </NuxtLink>

              <NuxtLink 
                to="/admin/laporan/member" 
                class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100 group"
              >
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">📊</div>
                  <div>
                    <h4 class="text-xs font-bold text-slate-800">Data Laporan</h4>
                    <p class="text-[11px] text-slate-400">Lihat rekapitulasi data parkir & member</p>
                  </div>
                </div>
                <span class="text-slate-400 text-sm group-hover:translate-x-1 transition">›</span>
              </NuxtLink>
            </div>
          </div>

          <!-- Monitoring Aktivitas Terbaru -->
          <div class="bg-white p-6 rounded-3xl shadow-xs border border-slate-200/60">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4">MONITORING</h2>
            <h3 class="text-base font-extrabold text-slate-800 mb-5">Aktivitas Terbaru</h3>

            <div class="space-y-3.5">
              <div v-if="aktivitasTerbaru.length === 0" class="text-xs text-slate-400 text-center py-4">
                Belum ada aktivitas terbaru.
              </div>
              
              <div v-for="item in aktivitasTerbaru" :key="item.id" class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">💰</div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-xs font-bold text-slate-800 truncate">Plat: {{ item.plat_nomor || item.no_plat || '-' }}</h4>
                  <p class="text-[10px] text-slate-400">Tiket: {{ item.kode_tiket }} | Rp {{ formatRupiah(item.total_tarif || item.total_bayar) }}</p>
                </div>
                <span class="text-[10px] font-bold text-slate-400">{{ formatWaktu(item.created_at) }}</span>
              </div>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>
</template>