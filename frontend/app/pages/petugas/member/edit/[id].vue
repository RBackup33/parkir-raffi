<template>
  <div class="edit-member-container">
    <h2>Edit Member</h2>

    <form @submit.prevent="updateMember">
      <div class="form-group">
        <label>Nama Member:</label>
        <input v-model="form.nama_member" type="text" required />
      </div>

      <div class="form-group">
        <label>Nama Perusahaan:</label>
        <input v-model="form.nama_perusahaan" type="text" required />
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
      </button>
      <button type="button" @click="$router.back()">Batal</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const form = ref({
  nama_member: '',
  nama_perusahaan: ''
})

const loading = ref(false)

// Ambil data member yang akan diedit
const fetchMemberDetail = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/member/${route.params.id}`)
    const data = res.data.data
    form.value.nama_member = data.nama_member
    form.value.nama_perusahaan = data.nama_perusahaan
  } catch (err) {
    alert('Gagal mengambil data member')
  }
}

// Update data member via API
const updateMember = async () => {
  loading.value = true
  try {
    await axios.put(`http://localhost:8000/api/member/${route.params.id}`, form.value)
    alert('Data member berhasil diperbarui!')
    router.push('/petugas/member')
  } catch (err) {
    alert('Gagal memperbarui member')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchMemberDetail()
})
</script>

<style scoped>
.edit-member-container {
  max-width: 500px;
  margin: 20px auto;
  padding: 20px;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
}
.form-group input {
  width: 100%;
  padding: 8px;
  box-sizing: border-block;
}
button {
  margin-right: 10px;
  padding: 8px 16px;
  cursor: pointer;
}
</style>