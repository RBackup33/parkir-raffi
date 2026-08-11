<template>
  <div class="transaksi-container">
    <div class="header">
      <h2>Daftar Transaksi Parkir</h2>
      <NuxtLink to="/petugas/transaksi/payment" class="btn-add">
        + Transaksi Baru
      </NuxtLink>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>QR Code</th>
          <th>Kode Tiket</th>
          <th>Kategori</th>
          <th>No Plat</th>
          <th>Total Bayar</th>
          <th>Uang Bayar</th>
          <th>Kembalian</th>
          <th>Status</th>
          <th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="10" class="text-center">Memuat data transaksi...</td>
        </tr>
        <tr v-else-if="transaksis.length === 0">
          <td colspan="10" class="text-center">Belum ada transaksi recorded.</td>
        </tr>
        <tr v-else v-for="(item, index) in transaksis" :key="item.id">
          <td>{{ index + 1 }}</td>
          <td>
            <!-- Menampilkan Gambar QR Code dari Route Backend -->
            <img 
              v-if="item.kode_tiket" 
              :src="`http://localhost:8000/api/qrcode/${item.kode_tiket}`" 
              alt="QR Code" 
              class="qr-img"
            />
            <span v-else class="text-muted">-</span>
          </td>
          <td><strong>{{ item.kode_tiket }}</strong></td>
          <td>{{ item.kategori }}</td>
          <td>{{ item.no_plat }}</td>
          <td>Rp {{ Number(item.total_bayar).toLocaleString('id-ID') }}</td>
          <td>Rp {{ Number(item.uang_bayar).toLocaleString('id-ID') }}</td>
          <td>Rp {{ Number(item.kembalian).toLocaleString('id-ID') }}</td>
          <td>
            <span class="badge badge-success">{{ item.status }}</span>
          </td>
          <td>{{ formatDate(item.created_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const transaksis = ref([])
const loading = ref(true)

const fetchTransaksi = async () => {
  loading.value = true
  try {
    const res = await fetch('http://localhost:8000/api/transaksi')
    const data = await res.json()
    transaksis.value = data.data || []
  } catch (err) {
    console.error('Gagal mengambil data transaksi:', err)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('id-ID')
}

onMounted(() => {
  fetchTransaksi()
})
</script>

<style scoped>
.transaksi-container {
  padding: 24px;
  max-width: 1100px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.btn-add {
  background: #2563eb;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
}

.table th, .table td {
  border: 1px solid #e5e7eb;
  padding: 12px;
  text-align: left;
  vertical-align: middle;
}

.table th {
  background: #f9fafb;
  font-weight: 600;
}

.qr-img {
  width: 60px;
  height: 60px;
  object-fit: contain;
  display: block;
}

.text-muted {
  color: #9ca3af;
}

.text-center {
  text-align: center;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}
</style>