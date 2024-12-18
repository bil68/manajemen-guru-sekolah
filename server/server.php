<?php
header('Content-Type: application/json');

// Koneksi ke database manajemen sekolah dan manajemen guru
$host = "localhost:3308";
$user = "root";
$pass = "";
$db_sekolah = "manajemen_sekolah";  // Menggunakan manajemen_sekolah
$db_guru = "manajemen_guru";

// Koneksi ke database manajemen sekolah
$conn_sekolah = mysqli_connect($host, $user, $pass, $db_sekolah);
if (!$conn_sekolah) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi ke manajemen_sekolah gagal: ' . mysqli_connect_error()]));
}

// Koneksi ke database manajemen guru
$conn_guru = mysqli_connect($host, $user, $pass, $db_guru);
if (!$conn_guru) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi ke manajemen_guru gagal: ' . mysqli_connect_error()]));
}

// Cek apakah parameter NIP diterima
if (isset($_GET['nip'])) {
    $nip = $_GET['nip'];

    // Query untuk mengambil semua data tanggal, jam_masuk, jam_keluar dari manajemen_guru
    $query = "SELECT tanggal, jam_masuk, jam_keluar FROM presensi WHERE nip = '$nip' ORDER BY tanggal DESC";
    $result = mysqli_query($conn_guru, $query);

    if (mysqli_num_rows($result) > 0) {
        $total_gaji = 0; // Variabel untuk menghitung total gaji
        $gaji_data = []; // Array untuk menyimpan semua data gaji yang dihitung

        while ($row = mysqli_fetch_assoc($result)) {
            $tanggal = $row['tanggal'];
            $jam_masuk = $row['jam_masuk'];
            $jam_keluar = $row['jam_keluar'];

            // Hitung durasi kerja dalam menit
            $masuk = new DateTime($jam_masuk);
            $keluar = new DateTime($jam_keluar);
            $interval = $masuk->diff($keluar);
            $durasi_menit = $interval->h * 60 + $interval->i; // Durasi dalam menit

            // Gaji per menit (misalnya Rp. 10.000 per jam atau Rp. 166,67 per menit)
            $gaji_per_menit = 166.67;
            $gaji = $durasi_menit * $gaji_per_menit;
            $total_gaji += $gaji; // Menambahkan gaji ke total gaji

            // Menyimpan data gaji yang dihitung untuk setiap tanggal
            $gaji_data[] = [
                'nip' => $nip,
                'tanggal' => $tanggal,
                'gaji' => number_format($gaji, 2, ',', '.'),
                'durasi_menit' => $durasi_menit
            ];

            // Simpan data gaji ke manajemen_sekolah
            $query_insert = "INSERT INTO gaji (nip, tanggal, gaji) VALUES ('$nip', '$tanggal', '$gaji')";
            if (!mysqli_query($conn_sekolah, $query_insert)) {
                // Jika gagal menyimpan ke manajemen_sekolah
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data gaji ke manajemen_sekolah'
                ]);
                exit;
            }
        }

        // Jika berhasil menyimpan semua data
        echo json_encode([
            'status' => 'success',
            'message' => 'Data gaji berhasil disimpan',
            'nip' => $nip,
            'total_gaji' => number_format($total_gaji, 2, ',', '.'),
            'gaji_data' => $gaji_data
        ]);
    } else {
        // Jika NIP tidak ditemukan atau data presensi tidak ada
        echo json_encode([
            'status' => 'error',
            'message' => 'Data absensi tidak ditemukan untuk NIP tersebut'
        ]);
    }
} else {
    // Jika parameter NIP tidak ditemukan
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter NIP tidak ditemukan'
    ]);
}

// Menutup koneksi ke database
mysqli_close($conn_sekolah);
mysqli_close($conn_guru);
