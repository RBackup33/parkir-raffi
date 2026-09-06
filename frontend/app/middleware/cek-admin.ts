export default defineNuxtRouteMiddleware((to, from) => {
  if (process.client) {
    const token = localStorage.getItem('token')
    const role = localStorage.getItem('role')

    // 1. Jika belum login sama sekali
    if (!token) {
      alert('Sesi habis atau belum login, silakan login terlebih dahulu!')
      return navigateTo('/')
    }

    // 2. Pengaman khusus rute Admin
    if (to.path.startsWith('/admin') && role !== 'admin') {
      alert('Akses ditolak! Kamu bukan admin.')
      return navigateTo('/petugas')
    }

    // 3. Pengaman khusus rute Petugas
    if (to.path.startsWith('/petugas') && role !== 'petugas' && role !== 'admin') {
      alert('Akses ditolak! Silakan login sebagai petugas yang sah.')
      return navigateTo('/')
    }
  }
})