<?php
session_start();
require '../config.php'; // Pastikan file ini ada dan koneksi `$conn` tersimpan di situ

// Cek role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data dari form

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$gender = $_POST['gender'] ?? '';
$nik = $_POST['nik'] ?? '';
$phone = $_POST['phone'] ?? '';
$role = $_POST['role'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Redirect dengan isi form sebelumnya
function redirectWithError($msg) {
    global $name, $email, $gender, $nik, $phone, $role;
    $params = http_build_query([
        'error' => $msg,
        'name' => $name,
        'email' => $email,
        'gender' => $gender,
        'nik' => $nik,
        'phone' => $phone,
        'role' => $role
    ]);
    header("Location: addaccount.php?$params");
    exit();
}

// Validasi

if (empty($name) || empty($email) || empty($gender) || empty($nik) || empty($phone) || empty($role) || empty($password) || empty($confirmPassword)) {
    redirectWithError("All fields are required.");
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
$insertSql = "INSERT INTO users (name, email, gender, nik, phone, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param("sssssss", $name, $email, $gender, $nik, $phone, $hashedPassword, $role);

if ($stmt->execute()) {
    header("Location: addaccount.php?success=1");
} else {
    redirectWithError("Failed to add account. Please try again.");
}
$stmt->close();
$conn->close();
