<template>
  <div class="tambah-container">
    <h2>Tambah Member Baru</h2>

    <div class="card">
      <!-- Form Input Member -->
      <form v-if="!suksesData" @submit.prevent="submitForm">
        <div class="form-group">
          <label>Nama Member:</label>
          <input 
            v-model="form.nama_member" 
            type="text" 
            placeholder="Masukkan nama member" 
            required 
          />
        </div>

        <div class="form-group">
          <label>Nama Perusahaan:</label>
          <input 
            v-model="form.nama_perusahaan" 
            type="text" 
            placeholder="Masukkan nama perusahaan" 
            required 
          />
        </div>

        <div class="form-group">
          <label>Uang Bayar (Rp):</label>
          <input 
            v-model.number="form.uang_bayar" 
            type="number" 
            min="0" 
            placeholder="Masukkan nominal bayar" 
            required 
          />
        </div>

        <div class="button-group">
          <button type="submit" :disabled="loading" class="btn-primary">
            {{ loading ? 'Memproses...' : 'Simpan Member' }}
          </button>
          <button type="button" @click="router.back()" class="btn-secondary">
            Batal
          </button>
        </div>
      </form>

      <!-- Tampilan Kartu Member & QR Code -->
      <div v-else class="qr-preview">
        <div class="alert-success">✓ Member Berhasil Ditambahkan!</div>

        <div class="member-card">
          <h3>KARTU MEMBER PARKIR</h3>
          
          <div class="qr-box">
            <img 
              :src="qrCodeUrl" 
              alt="QR Code Member" 
              class="qr-code-img"
              @error="handleImageError"
            />
          </div>

          <div class="member-details">
            <p><strong>Nama:</strong> {{ suksesData.nama_member }}</p>
            <p><strong>Perusahaan:</strong> {{ suksesData.nama_perusahaan }}</p>
            <p><strong>Kode Member:</strong> {{ memberCode }}</p>
            <hr class="card-divider" />
            <p><strong>Tanggal Mulai:</strong> {{ formatDate(suksesData.tanggal_mulai || new Date()) }}</p>
<p><strong>Berlaku Sampai:</strong> {{ formatDate(suksesData.tanggal_expired) }}</p>
            <p>
              <strong>Status:</strong> 
              <span :class="isAktif ? 'text-green' : 'text-red'">
                {{ isAktif ? 'Lunas (Aktif)' : 'Belum Lunas / Perpanjang' }}
              </span>
            </p>
          </div>
        </div>

        <div class="button-group center">
          <button @click="cetakKartu" class="btn-primary">Cetak Kartu</button>
          <button @click="router.push('/petugas/member/select')" class="btn-secondary">Kembali ke Daftar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

definePageMeta({
  middleware: 'auth'
});

const router = useRouter()

const form = ref({
  nama_member: '',
  nama_perusahaan: '',
  uang_bayar: 0
})

const loading = ref(false)
const suksesData = ref(null)

const memberCode = computed(() => {
  const data = suksesData.value
  if (!data) return 'DEFAULT'
  return data.kode_member || data.kode || data.id_member || (data.id ? `MBR-${data.id}` : 'DEFAULT')
})

const qrCodeUrl = computed(() => `http://localhost:8000/api/qrcode/${memberCode.value}`)

const isAktif = computed(() => {
  if (!suksesData.value?.berlaku_sampai) return true
  return new Date(suksesData.value.berlaku_sampai) >= new Date()
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const d = new Date(dateString)
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const handleImageError = (e) => {
  if (suksesData.value?.id) {
    e.target.src = `http://localhost:8000/api/qrcode/${suksesData.value.id}`
  }
}

const submitForm = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.post('http://localhost:8000/api/member', form.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    suksesData.value = res.data.data || res.data
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menambahkan member')
  } finally {
    loading.value = false
  }
}

const cetakKartu = () => window.print()
</script>

<style scoped>
.tambah-container { max-width: 550px; margin: 30px auto; padding: 20px; }
.card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; margin-bottom: 6px; font-weight: 600; }
.form-group input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
.button-group { display: flex; gap: 10px; margin-top: 20px; }
.button-group.center { justify-content: center; }
button { padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
.btn-primary { background-color: #16a34a; color: white; }
.btn-secondary { background-color: #e5e7eb; color: #374151; }
.alert-success { background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; text-align: center; font-weight: bold; margin-bottom: 20px; }
.member-card { border: 2px dashed #16a34a; border-radius: 12px; padding: 20px; text-align: center; background: #f9fafb; }
.member-card h3 { margin-top: 0; color: #1f2937; font-size: 18px; }
.qr-box { margin: 15px 0; display: flex; justify-content: center; }
.qr-code-img { width: 160px; height: 160px; object-fit: contain; }
.member-details p { margin: 6px 0; color: #4b5563; text-align: left; }
.card-divider { border: 0; border-top: 1px dashed #d1d5db; margin: 10px 0; }
.text-green { color: #16a34a; font-weight: bold; }
.text-red { color: #dc2626; font-weight: bold; }

@media print {
  body * { visibility: hidden; }
  .member-card, .member-card * { visibility: visible; }
  .member-card { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>  