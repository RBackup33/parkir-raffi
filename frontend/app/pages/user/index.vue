<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden">

    <div class="absolute inset-0 bg-gradient-to-br from-green-700 to-green-900"></div>

    <div class="absolute inset-0 bg-blue-900/40"></div>

    <div
      class="
      relative z-10
      w-[420px]
      min-h-[650px]
      bg-white/40
      backdrop-blur-md
      rounded-3xl
      shadow-2xl
      flex
      flex-col
      items-center
      pt-14
      "
    >

      <h1 class="text-white text-3xl font-bold text-center drop-shadow-lg">
        PARKIR<br>
        PLAZA ANDALAS
      </h1>

      <p class="mt-8 text-gray-700 text-sm text-center font-semibold">
        Pencet Tombol PRINT untuk<br>
        mengambil tiket
      </p>

      <button
        @click="printTicket"
        class="
        mt-4
        w-36
        h-11
        rounded-full
        bg-lime-900
        text-white
        font-bold
        shadow-lg
        hover:bg-lime-700
        transition
        "
      >
        PRINT
      </button>


      <div class="w-44 border-t-2 border-black mt-8"></div>


      <p class="mt-8 text-gray-700 text-sm text-center font-semibold">
        Scan kartu Member Untuk<br>
        Buka Pintu
      </p>


      <input
        v-model="scanCard"
        autofocus
        type="text"
        class="
        mt-5
        w-44
        h-14
        rounded-xl
        bg-white
        text-center
        text-xl
        shadow-lg
        "
        @keyup.enter="openGate"
      />

    </div>

  </div>
</template>


<script setup lang="ts">

import { ref } from "vue";
import { jsPDF } from "jspdf";
import QRCode from "qrcode";


const { $api } = useNuxtApp();


const scanCard = ref("");

const tiket = ref<any>(null);


const buatTiket = async () => {
  try {
    const response = await $api.post("/api/tiket");
    

    const hasil = response.data.data || response.data;
    
    return hasil;
  } catch (error) {
    console.log("Error detail:", error);
    alert("Gagal membuat tiket, cek terminal backend!");
  }
};


const printTicket = async () => {


  const data = await buatTiket();


  if (!data) return;



  const nomorTiket = data.kode_tiket;



  const qrData = `
PARKIR PLAZA ANDALAS

Kode Tiket : ${nomorTiket}

Waktu Masuk :
${data.waktu_masuk}

Status :
${data.status}
`;



  const qrImage = await QRCode.toDataURL(qrData);



  const pdf = new jsPDF({

    orientation: "portrait",

    unit: "mm",

    format: [80,140]

  });



  pdf.setFontSize(12);


  pdf.text(
    "PARKIR",
    40,
    12,
    {
      align:"center"
    }
  );


  pdf.text(
    "PLAZA ANDALAS",
    40,
    20,
    {
      align:"center"
    }
  );



  pdf.setFontSize(7);


  pdf.text(
    "Jl. Dr KRT Radjiman Widyodiningrat",
    40,
    35,
    {
      align:"center"
    }
  );


  pdf.text(
    "Jakarta Timur",
    40,
    40,
    {
      align:"center"
    }
  );



  pdf.addImage(
    qrImage,
    "PNG",
    25,
    48,
    30,
    30
  );



  pdf.setFontSize(16);


  pdf.text(
    nomorTiket,
    40,
    90,
    {
      align:"center"
    }
  );



  pdf.setFontSize(8);


  pdf.text(
`
Informasi :

1. Non Member
Parkir Rp.3000

2. Member
Parkir Rp.150.000/bulan

Terima Kasih
`,
    40,
    105,
    {
      align:"center"
    }
  );



  pdf.save(
    `Tiket-${nomorTiket}.pdf`
  );


};



const openGate = () => {


  if(scanCard.value){


    alert(
      "Kartu Member : "
      +
      scanCard.value
      +
      "\nPintu terbuka"
    );


    scanCard.value = "";


  }


};


</script>