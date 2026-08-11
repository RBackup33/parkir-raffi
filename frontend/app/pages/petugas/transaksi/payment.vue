<template>
  <div class="payment-container">
    <h2>Pembayaran Transaksi Parkir</h2>

    <div class="payment-card">
      <form @submit.prevent="processPayment">
        <div class="form-group">
          <label>Kode Tiket / QR Transaksi:</label>
          <input 
            v-model="form.kode_tiket" 
            type="text" 
            placeholder="Masukkan atau scan kode tiket" 
            required 
          />
        </div>

        <div class="form-group">
          <label>Uang Bayar (Rp):</label>
          <input 
            v-model.number="form.uang_bayar" 
            type="number" 
            min="0" 
            placeholder="Jumlah uang bayar" 
            required 
          />
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Bayar Transaksi' }}
        </button>
        <button type="button" @click="$router.back()">Kembali</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const form = ref({
  kode_tiket: '',
  uang_bayar: 0
})

const loading = ref(false)

const processPayment = async () => {
  loading.value = true
  try {
    const res = await axios.post('http://localhost:8000/api/transaksi/payment', form.value)
    alert(res.data.message || 'Pembayaran berhasil!')
    router.push('/petugas/transaksi')
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal melakukan pembayaran')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.payment-container {
  max-width: 600px;
  margin: 30px auto;
  padding: 20px;
}

.payment-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  box-sizing: border-box;
}

button {
  margin-right: 10px;
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

button[type="submit"] {
  background-color: #2563eb;
  color: white;
}

button[type="button"] {
  background-color: #e5e7eb;
  color: #374151;
}
</style>