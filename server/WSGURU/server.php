<?php
header('Content-Type: application/json');

// Mendapatkan data dari query string
$guruID = isset($_GET['guruID']) ? (int)$_GET['guruID'] : 0;
$tanggalMulai = isset($_GET['tanggalMulai']) ? $_GET['tanggalMulai'] : '';
$tanggalAkhir = isset($_GET['tanggalAkhir']) ? $_GET['tanggalAkhir'] : '';

// Fungsi untuk mendapatkan rekomendasi berdasarkan tanggal
function getRekomendasiByTanggal($tanggalMulai, $tanggalAkhir) {
    // Mengonversi string tanggal ke format timestamp
    $tanggalMulaiTimestamp = strtotime($tanggalMulai);
    $tanggalAkhirTimestamp = strtotime($tanggalAkhir);
    
    // Menghitung selisih durasi dalam detik
    $selisihDurasi = $tanggalAkhirTimestamp - $tanggalMulaiTimestamp;
    $durasiHari = $selisihDurasi / (60 * 60 * 24); // Menghitung durasi dalam hari

    // Cek status berdasarkan durasi
    if ($durasiHari > 90) {
        return [
            'status' => 'Baik',
            'recommendation' => 'Dipertimbangkan untuk promosi'
        ];
    } elseif ($durasiHari >= 60 && $durasiHari <= 90) {
        return [
            'status' => 'Cukup',
            'recommendation' => 'Tidak direkomendasikan untuk promosi'
        ];
    } else {
        return [
            'status' => 'Tidak Baik',
            'recommendation' => 'Harus diperbaiki'
        ];
    }
}

// Validasi input
if ($guruID && $tanggalMulai && $tanggalAkhir) {
    // Menghitung total durasi dalam detik
    $tanggalMulaiTimestamp = strtotime($tanggalMulai);
    $tanggalAkhirTimestamp = strtotime($tanggalAkhir);
    $totalDurasiDetik = $tanggalAkhirTimestamp - $tanggalMulaiTimestamp; // Durasi dalam detik

    // Mendapatkan rekomendasi berdasarkan tanggal
    $kinerja = getRekomendasiByTanggal($tanggalMulai, $tanggalAkhir);

    // Membuat response
    $response = [
        'status' => 'success',
        'data' => [
            'guruID' => $guruID,
            'totalDurasi' => $totalDurasiDetik, // Durasi dalam detik
            'kinerja' => $kinerja
        ]
    ];
} else {
    // Jika input tidak valid
    $response = ['status' => 'error', 'message' => 'Invalid input'];
}

// Kirimkan response dalam format JSON
echo json_encode($response);
?>