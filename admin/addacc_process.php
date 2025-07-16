<?php
session_start();
require '../config.php'; // Pastikan file ini ada dan koneksi `$conn` tersimpan di situ

// Cek role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data dari form
$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$gender = $_POST['gender'] ?? '';
$nik = $_POST['nik'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Redirect dengan isi form sebelumnya
function redirectWithError($msg) {
    global $fullName, $email, $gender, $nik, $phone;
    $params = http_build_query([
        'error' => $msg,
        'fullName' => $fullName,
        'email' => $email,
        'gender' => $gender,
        'nik' => $nik,
        'phone' => $phone
    ]);
    header("Location: addaccount.php?$params");
    exit();
}

// Validasi
if (empty($fullName) || empty($email) || empty($gender) || empty($nik) || empty($phone) || empty($password) || empty($confirmPassword)) {
    redirectWithError("Semua field wajib diisi.");
}

if ($password !== $confirmPassword) {
    redirectWithError("Password and confirmation do not match.");
}

// Cek email/nik sudah terdaftar
$checkSql = "SELECT id FROM users WHERE email = ? OR nik = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ss", $email, $nik);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    redirectWithError("Email or NIK is already registered.");
}
$stmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Simpan data ke database
$insertSql = "INSERT INTO users (name, email, gender, nik, phone, password, role) VALUES (?, ?, ?, ?, ?, ?, 'employee')";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param("ssssss", $fullName, $email, $gender, $nik, $phone, $hashedPassword);

if ($stmt->execute()) {
    header("Location: addaccount.php?success=Account successfully added!");
} else {
    redirectWithError("Failed to add account. Please try again.");
}
$stmt->close();
$conn->close();
