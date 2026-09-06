<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center p-5">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-[440px]">
      <h1 class="text-xl font-black text-center text-slate-800 uppercase tracking-wider mb-6">
        Form Pembayaran Kasir
      </h1>

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Kode Tiket / Kode Member</label>
          <div class="flex gap-2">
            <input 
              v-model="form.kode_tiket" 
              type="text" 
              @keyup.enter="cekTiket"
              placeholder="Contoh: A789" 
              class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold uppercase text-slate-800 focus:outline-none"
              autofocus
            />
            <button 
              @click="cekTiket" 
              type="button" 
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-xl font-bold text-xs shadow transition cursor-pointer"
            >
              Cek Jam
            </button>
          </div>
        </div>

        <!-- Detail Durasi -->
        <div v-if="detailTiket" class="bg-blue-50 border border-blue-200 p-3.5 rounded-2xl space-y-1.5 text-xs text-slate-700">
          <div class="flex justify-between">
            <span class="text-slate-500">Waktu Masuk:</span>
            <span class="font-bold">{{ formatWaktu(detailTiket.waktu_masuk) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Durasi Parkir:</span>
            <span class="font-bold text-blue-700">{{ durasiJam }} Jam</span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Kendaraan</label>
          <select 
            v-model="form.kategori" 
            @change="updateKategori"
            class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:outline-none"
          >
            <option value="motor">Motor (Rp 2.000 / jam)</option>
            <option value="mobil">Mobil (Rp 5.000 / jam)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">No. Plat Kendaraan</label>
          <input 
            v-model="form.no_plat" 
            type="text" 
            placeholder="B 1234 ABC" 
            class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold uppercase text-slate-800 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Uang Tunai Diterima (Rp)</label>
          <input 
            v-model.number="form.uang_bayar" 
            type="number" 
            min="0"
            placeholder="0" 
            class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 mb-2 focus:outline-none"
          />
        </div>

        <div class="bg-slate-900 text-white p-4 rounded-2xl space-y-2 shadow-inner">
          <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-slate-400">TOTAL TARIF</span>
            <span class="text-lg font-black text-emerald-400">Rp {{ formatRupiah(totalTarif) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-slate-400">KEMBALIAN</span>
            <span class="text-sm font-extrabold text-emerald-400">Rp {{ formatRupiah(hitungKembalian) }}</span>
          </div>
        </div>

        <div class="pt-2 flex flex-col gap-2">
          <button 
            @click="prosesBayar" 
            :disabled="loading" 
            type="button" 
            class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl font-bold text-xs uppercase tracking-wider cursor-pointer"
          >
            {{ loading ? 'Memproses...' : 'Proses Pembayaran' }}
          </button>

          <button 
            @click="router.push('/petugas/transaksi')" 
            type="button" 
            class="w-full bg-slate-200 hover:bg-slate-300 text-slate-700 py-2.5 rounded-xl font-bold text-xs cursor-pointer"
          >
            Kembali
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'

definePageMeta({ middleware: 'auth' })

const { $api } = useNuxtApp()
const router = useRouter()

const loading = ref(false)
const detailTiket = ref<any>(null)
const durasiJam = ref<number>(1)

const form = reactive({
  kode_tiket: '',
  kategori: 'motor',
  no_plat: '',
  uang_bayar: 2000
})

const updateKategori = () => {
  form.uang_bayar = form.kategori === 'mobil' ? 5000 : 2000
}

const cekTiket = async () => {
  if (!form.kode_tiket) { alert('Masukkan kode tiket!'); return }
  try {
    const res = await $api.post('/scan', { kode: form.kode_tiket, jenis_kendaraan: form.kategori })
    if (res.data.type === 'member') {
      alert('Ini adalah kode member. Gunakan gate utama untuk akses member!')
      return
    }
    detailTiket.value = res.data.data
    durasiJam.value = Number(res.data.data.durasi_jam) || 1
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Tiket tidak ditemukan!')
    detailTiket.value = null
  }
}

const totalTarif = computed(() => (form.kategori === 'mobil' ? 5000 : 2000) * Number(durasiJam.value || 1))
const hitungKembalian = computed(() => Math.max(0, Number(form.uang_bayar || 0) - totalTarif.value))

const prosesBayar = async () => {
  if (!form.kode_tiket) { alert('Kode tiket wajib diisi!'); return }
  if (Number(form.uang_bayar) < totalTarif.value) { alert('Uang tunai kurang!'); return }

  loading.value = true
  try {
    await $api.post('/payment', {
      kode_tiket: form.kode_tiket,
      kategori: form.kategori,
      no_plat: form.no_plat,
      uang_bayar: Number(form.uang_bayar),
      total_tarif: totalTarif.value,
      durasi_jam: durasiJam.value
    })
    alert('Pembayaran Berhasil Disimpan!')
    router.push('/petugas/transaksi')
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Gagal memproses pembayaran.')
  } finally { loading.value = false }
}

const formatWaktu = (d: any) => d ? new Date(d).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-'
const formatRupiah = (v: any) => new Intl.NumberFormat('id-ID').format(Number(v || 0))
</script>