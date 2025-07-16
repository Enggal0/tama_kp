<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "tama_kp";

// Buat koneksi
$conn = mysqli_connect($host, $user, $password, $dbname);

// Cek koneksi
// if (!$conn) {
//     die("Koneksi gagal: " . mysqli_connect_error());
// } else {
//     echo "Koneksi berhasil ke database '$dbname'";
// }

// Tutup koneksi (optional untuk tes singkat)
// mysqli_close($conn);
?>
