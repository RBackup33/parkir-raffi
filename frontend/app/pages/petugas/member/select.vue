<template>
  <div class="min-h-screen bg-slate-100 p-5 md:p-10">
    <!-- Header & Tombol Kembali -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="text-3xl font-bold text-blue-700">Data Member</h1>
      
      <button
        @click="router.push('/petugas')"
        class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl font-semibold text-sm flex items-center gap-2 transition shadow-sm"
      >
        <span>←</span> Kembali ke Dashboard
      </button>
    </div>

    <!-- Tombol Tambah -->
    <button
      @click="router.push('/petugas/member/tambah')"
      class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl mb-5 font-semibold transition shadow-sm"
    >
      + Tambah Member
    </button>

    <!-- Tabel -->
    <div class="bg-white rounded-xl shadow p-5 overflow-x-auto">
      <table class="w-full min-w-[1000px]">
        <thead>
          <tr class="border-b bg-slate-50">
            <th class="p-3 text-left">Kode</th>
            <th class="p-3 text-left">Nama Member</th>
            <th class="p-3 text-left">Perusahaan</th>
            <th class="p-3 text-left">Tagihan</th>
            <th class="p-3 text-left">Dibayar</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Expired</th>
            <th class="p-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in members" :key="item.id" class="border-b hover:bg-slate-50">
            <td class="p-3">{{ item.kode_member }}</td>
            <td class="p-3">{{ item.nama_member }}</td>
            <td class="p-3">{{ item.nama_perusahaan }}</td>
            <td class="p-3 font-semibold">Rp {{ formatRupiah(item.total_harga) }}</td>
            <td class="p-3 font-semibold">Rp {{ formatRupiah(item.jumlah_bayar) }}</td>
            <td class="p-3">
              <span
                class="px-3 py-1 rounded-full text-sm font-semibold"
                :class="item.status === 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ item.status === 'lunas' ? 'Lunas' : 'Belum Lunas (Expired)' }}
              </span>
            </td>
            <td class="p-3">{{ formatTanggal(item.tanggal_expired) }}</td>
            <td class="p-3">
              <div class="flex justify-center gap-2">
                <!-- Detail (Selalu bisa dibuka) -->
                <button
                  type="button"
                  @click.stop="lihatDetail(item.id)"
                  class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg cursor-pointer"
                  title="Detail"
                >
                  <EyeIcon class="w-5 h-5" />
                </button>

                <!-- Edit Pembayaran: Hanya muncul jika BELUM LUNAS -->
                <button
                  v-if="item.status !== 'lunas'"
                  type="button"
                  @click.stop="edit(item.id)"
                  class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg cursor-pointer"
                  title="Pembayaran"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>

                <!-- Hapus: Hanya muncul jika BELUM LUNAS -->
                <button
                  v-if="item.status !== 'lunas'"
                  type="button"
                  @click.stop="hapus(item.id)"
                  class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg cursor-pointer"
                  title="Hapus"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>

                <!-- Keterangan jika sudah lunas -->
                <span v-if="item.status === 'lunas'" class="text-xs font-bold text-green-600 self-center px-2">
                  Lunas
                </span>
              </div>
            </td>
          </tr>

          <!-- Jika Kosong -->
          <tr v-if="members.length === 0">
            <td colspan="8" class="p-10 text-center text-gray-500">
              Belum ada data member
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Detail -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-5"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-[420px] shadow-xl">
        <h2 class="text-xl font-bold text-center mb-5">Kartu Member</h2>

        <!-- QR -->
        <div class="flex justify-center mb-4">
          <img v-if="qr" :src="qr" class="w-48 h-48 object-contain" />
          <div v-else class="w-48 h-48 flex items-center justify-center bg-gray-100 text-gray-500">
            QR belum tersedia
          </div>
        </div>

        <!-- Detail Informasi Lengkap -->
        <div class="space-y-2">
          <p><b>Kode :</b> {{ detailMember.kode_member }}</p>
          <p><b>Nama :</b> {{ detailMember.nama_member }}</p>
          <p><b>Perusahaan :</b> {{ detailMember.nama_perusahaan }}</p>
          <p><b>Tagihan :</b> Rp {{ formatRupiah(detailMember.total_harga) }}</p>
          <p><b>Dibayar :</b> Rp {{ formatRupiah(detailMember.jumlah_bayar) }}</p>
          <p>
            <b>Status :</b>
            <span
              :class="detailMember.status === 'lunas' ? 'text-green-600' : 'text-red-600'"
              class="font-bold"
            >
              {{ detailMember.status }}
            </span>
          </p>
          <p><b>Tanggal Mulai :</b> {{ formatTanggal(detailMember.tanggal_mulai) }}</p>
          <p><b>Tanggal Bayar :</b> {{ formatTanggal(detailMember.tanggal_bayar) }}</p>
          <p><b>Berlaku Sampai :</b> {{ formatTanggal(detailMember.tanggal_expired) }}</p>
        </div>

        <!-- Download -->
        <button
          @click="downloadMember"
          class="mt-5 w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold cursor-pointer"
        >
          ⬇ Download Kartu Member
        </button>

        <!-- Tutup -->
        <button
          @click="tutupModal"
          class="mt-3 w-full bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { EyeIcon, PencilIcon, TrashIcon } from "@heroicons/vue/24/solid";

definePageMeta({
  middleware: 'auth'
});

const { $api } = useNuxtApp();
const router = useRouter();

const members = ref<any[]>([]);
const showModal = ref(false);
const detailMember = ref<any>({});
const qr = ref("");

// Load Data Member
const load = async () => {
  try {
    const res = await $api.get("/member");
    members.value = res.data.data;
  } catch (error: any) {
    console.error("Gagal mengambil member:", error);
    alert(error?.response?.data?.message || "Gagal mengambil data member");
  }
};

// Detail Member
const lihatDetail = async (id: number) => {
  try {
    const res = await $api.get(`/member/${id}`);
    if (!res.data.status) {
      alert(res.data.message || "Data member tidak ditemukan");
      return;
    }
    detailMember.value = res.data.data;
    qr.value = res.data.data.qr;
    showModal.value = true;
  } catch (error: any) {
    console.error("ERROR DETAIL MEMBER:", error);
    alert(error?.response?.data?.message || "Gagal mengambil detail member");
  }
};

// Tutup Modal
const tutupModal = () => {
  showModal.value = false;
  detailMember.value = {};
  qr.value = "";
};

// Edit / Pembayaran
const edit = (id: number) => {
  router.push(`/petugas/member/edit/${id}`);
};

// Hapus Member
const hapus = async (id: number) => {
  if (!confirm("Yakin hapus member?")) return;

  try {
    await $api.delete(`/member/${id}`);
    alert("Member berhasil dihapus");
    await load();
  } catch (error: any) {
    console.error("Gagal hapus:", error);
    alert(error?.response?.data?.message || "Gagal menghapus member");
  }
};

// Download Kartu Member (Canvas)
const downloadMember = () => {
  if (!detailMember.value?.kode_member || !qr.value) {
    alert("Data atau QR Code belum tersedia");
    return;
  }

  const canvas = document.createElement("canvas");
  const ctx = canvas.getContext("2d");
  if (!ctx) return;

  canvas.width = 500;
  canvas.height = 700;

  // Background
  ctx.fillStyle = "white";
  ctx.fillRect(0, 0, 500, 700);

  // Judul
  ctx.fillStyle = "black";
  ctx.font = "bold 30px Arial";
  ctx.textAlign = "center";
  ctx.fillText("MEMBER PARKIR", 250, 70);

  // Data Member
  ctx.textAlign = "left";
  ctx.font = "20px Arial";
  ctx.fillText(`Kode : ${detailMember.value.kode_member}`, 50, 150);
  ctx.fillText(`Nama : ${detailMember.value.nama_member}`, 50, 200);
  ctx.fillText(`Perusahaan : ${detailMember.value.nama_perusahaan}`, 50, 250);
  ctx.fillText(`Status : ${detailMember.value.status}`, 50, 300);

  // QR
  const img = new Image();
  img.onload = () => {
    ctx.drawImage(img, 150, 350, 200, 200);
    const link = document.createElement("a");
    link.download = `member-${detailMember.value.kode_member}.png`;
    link.href = canvas.toDataURL("image/png");  
    link.click();
  };
  img.onerror = () => {
    alert("QR Code gagal dimuat");
  };
  img.src = qr.value;
};

const formatRupiah = (angka: any) => {
  return new Intl.NumberFormat("id-ID").format(Number(angka || 0));
};

const formatTanggal = (tanggal: any) => {
  if (!tanggal) return "-";
  return new Date(tanggal).toLocaleDateString("id-ID");
};

onMounted(load);
</script>