<template>
  <div class="tambah-container">
    <h2>Tambah Member Baru</h2>

    <div class="card">
      <form @submit.prevent="submitForm">
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
          <button type="button" @click="$router.back()" class="btn-secondary">
            Batal
          </button>
        </div>
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
  nama_member: '',
  nama_perusahaan: '',
  uang_bayar: 0
})

const loading = ref(false)

const submitForm = async () => {
  loading.value = true
  try {
    const res = await axios.post('http://localhost:8000/api/member', form.value)
    alert(res.data.message || 'Member berhasil ditambahkan!')
    router.push('/petugas/member')
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menambahkan member')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.tambah-container {
  max-width: 550px;
  margin: 30px auto;
  padding: 20px;
}

.card {
  background: #ffffff;
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

.button-group {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

button {
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.btn-primary {
  background-color: #16a34a;
  color: white;
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}
</style>