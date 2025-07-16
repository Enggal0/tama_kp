<?php
include 'config.php';

$name = $_POST['name'];
$email = $_POST['email'];
$nik = $_POST['nik'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];

if ($password !== $confirm_password) {
    header("Location: register.php?error=Password tidak cocok");
    exit();
}

// Optional: hash password
$password = password_hash($password, PASSWORD_DEFAULT);

// Simpan ke database
$query = "INSERT INTO users (name, email, nik, phone, password, role) VALUES ('$name', '$email', '$nik', '$phone', '$password', '$role')";
$result = mysqli_query($conn, $query);

if ($result) {
    header("Location: login.php");
    exit();
} else {
    header("Location: register.php?error=Registrasi gagal. Cek kembali data.");
    exit();
}
?>
