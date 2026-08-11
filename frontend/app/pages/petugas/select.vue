<template>
  <div class="member-print-container">
    <!-- Header khusus print & action button -->
    <div class="no-print header-actions">
      <h2>Cetak QR Member</h2>
      <button @click="printPage" class="btn-print">🖨️ Cetak / Simpan PDF</button>
    </div>

    <!-- Tabel Daftar QR Member -->
    <table class="table-member">
      <thead>
        <tr>
          <th>No</th>
          <th>QR Code</th>
          <th>Kode Member</th>
          <th>Nama Member</th>
          <th>Perusahaan</th>
          <th>Status</th>
          <th>Masa Aktif</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in members" :key="item.id">
          <td>{{ index + 1 }}</td>
          <td>
            <img :src="item.qr" alt="QR Code" class="qr-img" />
          </td>
          <td class="font-bold">{{ item.kode_member }}</td>
          <td>{{ item.nama_member }}</td>
          <td>{{ item.nama_perusahaan }}</td>
          <td>
            <span :class="['badge', item.status === 'lunas' ? 'badge-success' : 'badge-danger']">
              {{ item.status }}
            </span>
          </td>
          <td>
            <small>{{ item.tanggal_mulai }} s/d {{ item.tanggal_expired }}</small>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SelectVue',
  data() {
    return {
      members: []
    }
  },
  mounted() {
    this.fetchMembers();
  },
  methods: {
    async fetchMembers() {
      try {
        // Ambil list member dari API
        const res = await axios.get('/api/member');
        const list = res.data.data;

        // Ambil data QR (Base64) untuk setiap member via endpoint show/detail
        const detailedMembers = await Promise.all(
          list.map(async (m) => {
            const detailRes = await axios.get(`/api/member/${m.id}`);
            return detailRes.data.data;
          })
        );

        this.members = detailedMembers;
      } catch (err) {
        console.error("Gagal mengambil data member:", err);
      }
    },
    printPage() {
      window.print();
    }
  }
}
</script>

<style scoped>
.member-print-container {
  padding: 20px;
  background-color: #fff;
  font-family: Arial, sans-serif;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.btn-print {
  background-color: #4f46e5;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.table-member {
  width: 100%;
  border-collapse: collapse;
}

.table-member th,
.table-member td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
  vertical-align: middle;
}

.table-member th {
  background-color: #f3f4f6;
  font-weight: bold;
}

.qr-img {
  width: 90px;
  height: 90px;
  object-fit: contain;
}

.font-bold {
  font-weight: bold;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  text-transform: capitalize;
}

.badge-success {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-danger {
  background-color: #fee2e2;
  color: #991b1b;
}

/* ===================================================
   STYLING KHUSUS TAMPILAN PRINT (PRINT MEDIA QUERY)
   =================================================== */
@media print {
  /* Sembunyikan elemen navbar, sidebar, atau tombol yang tidak perlu di-print */
  .no-print,
  button,
  header,
  sidebar {
    display: none !important;
  }

  body {
    background: #fff;
  }

  .member-print-container {
    padding: 0;
  }

  .table-member {
    border: 1px solid #000;
  }

  .table-member th,
  .table-member td {
    border: 1px solid #000 !important;
    padding: 8px;
  }

  .qr-img {
    width: 80px;
    height: 80px;
  }
}
</style>