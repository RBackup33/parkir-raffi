<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-slate-900">
    <div class="absolute inset-0 bg-gradient-to-br from-green-700 to-green-900"></div>
    <div class="absolute inset-0 bg-blue-900/40"></div>

    <div
      class="
      relative z-10
      w-[420px]
      min-h-[650px]
      bg-white/40
      backdrop-blur-md
      rounded-3xl
      shadow-2xl
      flex
      flex-col
      items-center
      pt-14
      p-6
      "
    >
      <h1 class="text-white text-3xl font-bold text-center drop-shadow-lg">
        PARKIR<br>
        PLAZA ANDALAS
      </h1>

      <p class="mt-8 text-gray-700 text-sm text-center font-semibold">
        Pencet Tombol PRINT untuk<br>
        download PDF otomatis & QR Code
      </p>

      <button
        @click="printTicket"
        :disabled="loading"
        class="
        mt-4
        w-36
        h-11
        rounded-full
        bg-lime-900
        text-white
        font-bold
        shadow-lg
        hover:bg-lime-700
        transition
        disabled:bg-gray-500
        cursor-pointer
        "
      >
        {{ loading ? 'MEN-DOWNLOAD...' : 'PRINT PDF' }}
      </button>

      <div class="w-44 border-t-2 border-black mt-8"></div>

      <p class="mt-8 text-gray-700 text-sm text-center font-semibold">
        Scan kartu Member Untuk<br>
        Buka Pintu
      </p>

      <input
        v-model="cardInput"
        autofocus
        type="text"
        placeholder="Scan Member"
        class="
        mt-5
        w-44
        h-14
        rounded-xl
        bg-white
        text-center
        text-xl
        shadow-lg
        focus:outline-none
        "
        @keyup.enter="openGate"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { jsPDF } from 'jspdf'

definePageMeta({
  middleware: 'auth'
})

const { $api } = useNuxtApp()

const loading = ref(false)
const cardInput = ref('')

const printTicket = async () => {
  loading.value = true
  try {
    const res = await $api.post('/tiket')
    if (res.data && res.data.data) {
      const tiket = res.data.data

      // Inisialisasi jsPDF ukuran struk kecil (80x110 mm)
      const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: [80, 110]
      })

      // Header Struk
      doc.setFont('Helvetica', 'bold')
      doc.setFontSize(14)
      doc.text('PLAZA ANDALAS', 40, 12, { align: 'center' })

      doc.setFontSize(9)
      doc.setFont('Helvetica', 'normal')
      doc.text('TIKET PARKIR NON-MEMBER', 40, 18, { align: 'center' })

      doc.setLineDash([1, 1], 0)
      doc.line(10, 22, 70, 22)

      // ==========================================
      // RENDER QR CODE LANGSUNG DARI BASE64 BACKEND
      // ==========================================
      try {
        if (tiket.qr_code) {
          // Jika backend mengirimkan string SVG base64 (seperti data:image/svg+xml;base64,...),
          // jsPDF butuh format PNG/JPEG. Kita konversi SVG base64 ke Image element di browser secara instan!
          const canvas = document.createElement('canvas')
          canvas.width = 200
          canvas.height = 200
          const ctx = canvas.getContext('2d')
          
          const img = new Image()
          img.src = tiket.qr_code // Menggunakan qr_code yang sudah digenerate controller Laravel

          await new Promise((resolve, reject) => {
            img.onload = () => {
              ctx?.drawImage(img, 0, 0, 200, 200)
              const pngData = canvas.toDataURL('image/png')
              // Masukkan ke PDF (Posisi X=25, Y=25, Ukuran 30x30 mm)
              doc.addImage(pngData, 'PNG', 25, 25, 30, 30)
              resolve(true)
            }
            img.onerror = reject
          })
        }
      } catch (e) {
        console.warn('Gagal merender QR Code ke PDF, lanjut cetak teks.', e)
      }

      // Kode Tiket Teks di bawah QR
      doc.setFont('Helvetica', 'bold')
      doc.setFontSize(14)
      doc.text(tiket.kode_tiket.toUpperCase(), 40, 62, { align: 'center' })

      doc.line(10, 67, 70, 67)

      // Waktu Masuk
      doc.setFontSize(8)
      doc.setFont('Helvetica', 'normal')
      const waktuMasuk = tiket.waktu_masuk ? new Date(tiket.waktu_masuk).toLocaleString('id-ID') : new Date().toLocaleString('id-ID')
      doc.text(`Masuk: ${waktuMasuk}`, 40, 75, { align: 'center' })

      doc.line(10, 81, 70, 81)

      // Footer
      doc.setFontSize(8)
      doc.setFont('Helvetica', 'bold')
      doc.text('SIMPAN TIKET INI UNTUK KELUAR', 40, 92, { align: 'center' })
      doc.setFont('Helvetica', 'normal')
      doc.setFontSize(7)
      doc.text('PARKIR PLAZA ANDALAS SELALU DEPAN', 40, 97, { align: 'center' })

      // Langsung download file PDF otomatis
      doc.save(`Tiket-Parkir-${tiket.kode_tiket}.pdf`)
    }
  } catch (err: any) {
    console.error('Gagal cetak tiket:', err)
    alert(err?.response?.data?.message || 'Gagal terhubung ke backend Laravel!')
  } finally {
    loading.value = false
  }
}

const openGate = async () => {
  if (!cardInput.value) return
  try {
    const res = await $api.post('/member/check', {
      kode_member: cardInput.value
    })
    if (res.data && res.data.status) {
      alert('Kartu Member Valid! Pintu Barrier Terbuka.')
    } else {
      alert('Kartu Member Tidak Terdaftar / Kadaluarsa!')
    }
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Member tidak ditemukan!')
  } finally {
    cardInput.value = ''
  }
}
</script>