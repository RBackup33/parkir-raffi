<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center p-5">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-[450px]">
      <h1 class="text-2xl font-bold mb-6 text-center">Pembayaran Member</h1>

      <!-- Informasi Member -->
      <div class="bg-slate-50 rounded-xl p-4 mb-6 space-y-3">
        <div>
          <p class="text-sm text-gray-500">Kode Member</p>
          <p class="font-bold">{{ form.kode_member }}</p>
        </div>

        <div>
          <p class="text-sm text-gray-500">Nama Member</p>
          <p class="font-bold">{{ form.nama_member }}</p>
        </div>

        <div>
          <p class="text-sm text-gray-500">Perusahaan</p>
          <p class="font-bold">{{ form.nama_perusahaan }}</p>
        </div>

        <div>
          <p class="text-sm text-gray-500">Tagihan Bulanan</p>
          <p class="font-bold text-blue-600 text-lg">Rp {{ formatRupiah(form.total_harga) }}</p>
        </div>

        <div>
          <p class="text-sm text-gray-500">Pembayaran Saat Ini</p>
          <p class="font-bold text-lg">Rp {{ formatRupiah(pembayaranTerakhir) }}</p>
        </div>

        <div>
          <p class="text-sm text-gray-500">Status</p>
          <span
            class="inline-block px-3 py-1 rounded-full text-sm font-bold"
            :class="form.status === 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
          >
            {{ form.status }}
          </span>
        </div>
      </div>

      <!-- Input Pembayaran -->
      <label class="block font-semibold mb-2">Masukkan Pembayaran</label>
      <input
        v-model.number="nominalInput"
        type="number"
        min="0"
        :max="form.total_harga"
        class="w-full p-3 rounded-xl border border-gray-300 mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
        placeholder="Masukkan jumlah pembayaran"
      />
      <p class="text-sm text-gray-500 mb-5">Maksimal pembayaran: Rp {{ formatRupiah(form.total_harga) }}</p>

      <!-- Tombol Aksi -->
      <button
        @click="updatePembayaran"
        :disabled="loading"
        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white w-full py-3 rounded-xl font-bold"
      >
        {{ loading ? 'Menyimpan...' : 'Simpan Pembayaran' }}
      </button>

      <button
        @click="router.push('/petugas/member/select')"
        class="bg-gray-500 hover:bg-gray-600 text-white w-full py-3 rounded-xl font-bold mt-3"
      >
        Kembali
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';

definePageMeta({
  middleware: 'auth'
});

const { $api } = useNuxtApp();
const router = useRouter();
const route = useRoute();

const loading = ref<boolean>(false);
const nominalInput = ref<number>(0);
const pembayaranTerakhir = ref<number>(0);

const form = reactive({
  id: null,
  kode_member: '',
  nama_member: '',
  nama_perusahaan: '',
  total_harga: 0,
  jumlah_bayar: 0,
  status: '',
  tanggal_mulai: null,
  tanggal_expired: null
});

const formatRupiah = (angka: number | string): string => {
  return new Intl.NumberFormat('id-ID').format(Number(angka || 0));
};

const fetchDetailMember = async (): Promise<void> => {
  try {
    const response = await $api.get(`/member/${route.params.id}`);
    const result = response.data;

    if (!result.status) {
      alert(result.message || 'Data member tidak ditemukan');
      router.push('/petugas/member/select');
      return;
    }

    Object.assign(form, result.data);
    pembayaranTerakhir.value = result.data.jumlah_bayar || 0;
    nominalInput.value = result.data.jumlah_bayar || 0;
  } catch (error: any) {
    console.error('Gagal mengambil data member:', error);
    alert(error?.response?.data?.message || 'Gagal mengambil data member');
  }
};

const updatePembayaran = async (): Promise<void> => {
  const bayar = Number(nominalInput.value || 0);
  const tagihan = Number(form.total_harga || 0);

  if (bayar < 0) {
    alert('Pembayaran tidak boleh kurang dari 0');
    return;
  }

  if (bayar > tagihan) {
    alert(`Pembayaran tidak boleh lebih dari Rp ${formatRupiah(tagihan)}`);
    return;
  }

  try {
    loading.value = true;
    const response = await $api.put(`/member/${route.params.id}/pembayaran`, {
      jumlah_bayar: bayar
    });

    const result = response.data;
    if (!result.status) {
      alert(result.message || 'Pembayaran gagal');
      return;
    }

    alert(result.message || 'Pembayaran berhasil diperbarui');
    router.push('/petugas/member/select');
  } catch (error: any) {
    console.error('Gagal update pembayaran:', error);
    alert(error?.response?.data?.message || 'Gagal memperbarui pembayaran');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDetailMember();
});
</script>