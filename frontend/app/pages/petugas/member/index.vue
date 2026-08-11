<template>
  <div class="member-container">
    <div class="header">
      <h2>Daftar Member Parkir</h2>
      <NuxtLink to="/petugas/tambah" class="btn-add">+ Tambah Member</NuxtLink>
    </div>

    <!-- Tabel Daftar Member -->
    <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Member</th>
          <th>Nama Member</th>
          <th>Perusahaan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="6">Memuat data member...</td>
        </tr>
        <tr v-else-if="members.length === 0">
          <td colspan="6">Belum ada data member.</td>
        </tr>
        <tr v-else v-for="(item, index) in members" :key="item.id">
          <td>{{ index + 1 }}</td>
          <td><strong>{{ item.kode_member }}</strong></td>
          <td>{{ item.nama_member }}</td>
          <td>{{ item.nama_perusahaan }}</td>
          <td>
            <span :class="['badge', item.status === 'lunas' ? 'badge-success' : 'badge-danger']">
              {{ item.status }}
            </span>
          </td>
          <td>
            <NuxtLink :to="`/petugas/member/edit/${item.id}`" class="btn-edit">Edit</NuxtLink>
            <button @click="deleteMember(item.id)" class="btn-delete">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const members = ref([])
const loading = ref(true)

const fetchMembers = async () => {
  loading.value = true
  try {
    const res = await fetch('http://localhost:8000/api/member')
    const data = await res.json()
    members.value = data.data || []
  } catch (err) {
    console.error('Gagal mengambil data member:', err)
  } finally {
    loading.value = false
  }
}

const deleteMember = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus member ini?')) return

  try {
    await fetch(`http://localhost:8000/api/member/${id}`, { method: 'DELETE' })
    alert('Member berhasil dihapus')
    fetchMembers()
  } catch (err) {
    alert('Gagal menghapus member')
  }
}

onMounted(() => {
  fetchMembers()
})
</script>

<style scoped>
.member-container {
  padding: 24px;
  max-width: 1000px;
  margin: 0 auto;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.btn-add {
  background: #16a34a;
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
}
.table th, .table td {
  border: 1px solid #e5e7eb;
  padding: 12px;
  text-align: left;
}
.table th {
  background: #f9fafb;
}
.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.btn-edit {
  color: #2563eb;
  margin-right: 10px;
  text-decoration: none;
  font-weight: bold;
}
.btn-delete {
  background: none;
  border: none;
  color: #dc2626;
  cursor: pointer;
  font-weight: bold;
}
</style>