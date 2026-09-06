<template>
  <div class="min-h-screen bg-slate-100 p-6 flex flex-col items-center">
    <h1 class="text-2xl font-bold text-slate-700 mb-1">PARKIR PLAZA ANDALAS</h1>
    <p class="text-sm text-slate-500 mb-6">Gate Keluar - Pos Penjagaan (Auto-Scan & Kasir)</p>

    <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-md space-y-4">
      
      <!-- FITUR KAMERA SCANNER QR/BARCODE -->
      <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-center">
        <button 
          @click="toggleScanner" 
          type="button"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold text-xs shadow transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <span>📷</span> {{ scannerActive ? 'Tutup Kamera Scanner' : 'Buka Kamera Scanner' }}
        </button>
        
        <div v-show="scannerActive" id="reader" class="mt-3 overflow-hidden rounded-xl border border-slate-300"></div>
      </div>

      <!-- Input Kode Tiket / Member -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Scan Barcode / Kode Tiket / Member</label>
        <div class="flex gap-2">
          <input 
            v-model="form.kode" 
            type="text" 
            @keyup.enter="prosesScan"
            placeholder="Contoh: A123 atau MBR-xxxx" 
            class="w-full p-2.5 bg-slate-100 rounded-xl border border-slate-300 text-sm font-bold uppercase text-slate-800 focus:outline-none"
            autofocus
          />
          <button 
            @click="prosesScan" 
            :disabled="loading"
            type="button" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 rounded-xl font-bold text-xs shadow transition cursor-pointer"
          >
            {{ loading ? '...' : 'Cari' }}
          </button>
        </div>
      </div>

      <!-- Detail Informasi Akses / Tiket -->
      <div v-if="detailTransaksi" class="space-y-4 border-t border-slate-200 pt-4">
        
        <div class="bg-slate-50 p-3 rounded-xl space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-slate-500">Tipe Akses:</span>
            <span class="font-bold uppercase text-indigo-600">{{ tipeAkses }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Kode / Identitas:</span>
            <span class="font-bold text-slate-800">{{ detailTransaksi.kode_tiket || detailTransaksi.kode_member }}</span>
          </div>
          <div class="flex justify-between" v-if="tipeAkses === 'tiket'">
            <span class="text-slate-500">Waktu Masuk:</span>
            <span class="font-bold">{{ formatWaktu(detailTransaksi.waktu_masuk) }}</span>
          </div>
          <div class="flex justify-between" v-if="tipeAkses === 'tiket'">
            <span class="text-slate-500">Durasi Parkir:</span>
            <span class="font-bold text-blue-700">{{ durasiJam }} Jam</span>
          </div>
        </div>

        <!-- Jika Tipe Tiket Umum -->
        <template v-if="tipeAkses === 'tiket'">
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
              v-model="form.nopol" 
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

            <div class="flex flex-wrap gap-1.5">
              <button 
                v-for="nominal in opsiPecahan" 
                :key="nominal"
                @click="setNominal(nominal)"
                type="button"
                class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer"
                :class="Number(form.uang_bayar) === nominal ? 'bg-slate-800 text-white border-slate-800' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'"
              >
                Rp {{ formatRupiah(nominal) }}
              </button>
            </div>
          </div>

          <div class="bg-slate-900 text-white p-4 rounded-2xl space-y-2 shadow-inner">
            <div class="flex justify-between items-center">
              <span class="text-xs font-medium text-slate-400">TOTAL TARIF ({{ durasiJam }} JAM)</span>
              <span class="text-lg font-black text-emerald-400">Rp {{ formatRupiah(totalTarif) }}</span>
            </div>
            <hr class="border-slate-800" />
            <div class="flex justify-between items-center">
              <span class="text-xs font-medium text-slate-400">KEMBALIAN</span>
              <span class="text-sm font-extrabold text-emerald-400">
                Rp {{ formatRupiah(hitungKembalian) }}
              </span>
            </div>
          </div>

          <button 
            @click="konfirmasiBayar" 
            :disabled="loading"
            type="button"
            class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-400 text-white font-bold p-3 rounded-xl shadow transition cursor-pointer uppercase tracking-wider text-xs"
          >
            {{ loading ? 'Memproses...' : 'BAYAR & BUKA GATE' }}
          </button>
        </template>

        <!-- Jika Tipe Member Valid -->
        <div v-else class="p-4 bg-emerald-50 text-emerald-700 rounded-xl text-center font-bold text-xs">
          Akses Member Valid! Pintu Terbuka Otomatis.
        </div>

      </div>

      <button 
        @click="batal" 
        type="button"
        class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold p-3 rounded-xl transition cursor-pointer text-xs"
      >
        SELESAI / BATAL
      </button>

    </div>

    <!-- MODAL POPUP -->
    <div v-if="modal.show" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl">
        <h3 class="text-lg font-black text-slate-800 mb-2">{{ modal.title }}</h3>
        <p class="text-xs text-slate-600 mb-6 leading-relaxed whitespace-pre-line">{{ modal.message }}</p>
        <button @click="closeModal" class="w-full py-3 rounded-xl font-bold text-xs text-white bg-slate-800 hover:bg-slate-900 cursor-pointer">
          Tutup / Mengerti
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onUnmounted } from 'vue'
import { Html5QrcodeScanner } from 'html5-qrcode'

definePageMeta({ middleware: 'auth' })

const { $api } = useNuxtApp()
const loading = ref(false)
const detailTransaksi = ref<any>(null)
const tipeAkses = ref('')
const durasiJam = ref<number>(1)
const scannerActive = ref(false)
let html5QrcodeScanner: Html5QrcodeScanner | null = null

const modal = reactive({ show: false, title: '', message: '', onClose: null as any })
const triggerModal = (title: string, message: string, onCloseCallback: any = null) => {
  modal.title = title; modal.message = message; modal.onClose = onCloseCallback; modal.show = true
}
const closeModal = () => { modal.show = false; if (modal.onClose) modal.onClose() }

const form = reactive({ kode: '', kategori: 'motor', nopol: '', uang_bayar: 2000 })

const toggleScanner = () => {
  scannerActive.value = !scannerActive.value
  if (scannerActive.value) {
    setTimeout(() => {
      html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 250, height: 150 } }, false)
      html5QrcodeScanner.render((text) => { form.kode = text.trim(); stopScanner(); prosesScan() }, () => {})
    }, 100)
  } else { stopScanner() }
}

const stopScanner = () => {
  if (html5QrcodeScanner) { html5QrcodeScanner.clear().catch(() => {}); html5QrcodeScanner = null }
  scannerActive.value = false
}
onUnmounted(() => stopScanner())

const opsiPecahan = computed(() => form.kategori === 'mobil' ? [5000, 10000, 20000, 50000, 100000] : [2000, 5000, 10000, 20000, 50000])
const setNominal = (val: number) => { form.uang_bayar = Number(val) }
const updateKategori = () => { form.uang_bayar = form.kategori === 'mobil' ? 5000 : 2000 }

const prosesScan = async () => {
  if (!form.kode) { triggerModal('Peringatan', 'Masukkan kode tiket atau member!'); return }
  loading.value = true
  try {
    const res = await $api.post('/gate/scan', { kode: form.kode })
    tipeAkses.value = res.data.type
    detailTransaksi.value = res.data.data
    durasiJam.value = Number(res.data.data.durasi_jam) || 1
    if (res.data.type === 'member') {
      triggerModal('Akses Member Valid', `${res.data.message}\nPalang pintu terbuka otomatis.`, () => batal())
    }
  } catch (err: any) {
    triggerModal('Gagal', err?.response?.data?.message || 'Data tidak ditemukan!')
    detailTransaksi.value = null
  } finally { loading.value = false }
}

const totalTarif = computed(() => (form.kategori === 'mobil' ? 5000 : 2000) * Number(durasiJam.value || 1))
const hitungKembalian = computed(() => Math.max(0, Number(form.uang_bayar || 0) - totalTarif.value))

const konfirmasiBayar = async () => {
  if (!detailTransaksi.value) return
  if (Number(form.uang_bayar) < totalTarif.value) { triggerModal('Pembayaran Kurang', 'Uang tunai kurang dari total tarif!'); return }
  
  loading.value = true
  try {
    await $api.post('/transaksi/bayar', {
      tiket_id: detailTransaksi.value.id,
      nopol: form.nopol,
      total_bayar: totalTarif.value,
      bayar: Number(form.uang_bayar)
    })
    triggerModal('Berhasil!', 'Pembayaran lunas! Gate terbuka otomatis.', () => batal())
  } catch (err: any) {
    triggerModal('Gagal', err?.response?.data?.message || 'Terjadi kesalahan sistem.')
  } finally { loading.value = false }
}

const batal = () => {
  detailTransaksi.value = null; tipeAkses.value = ''; form.kode = ''; form.nopol = ''; form.uang_bayar = 2000; durasiJam.value = 1
}
const formatWaktu = (d: any) => d ? new Date(d).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-'
const formatRupiah = (v: any) => new Intl.NumberFormat('id-ID').format(Number(v || 0))
</script>