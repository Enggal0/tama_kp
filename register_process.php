<?php
include 'config.php';

$name = $_POST['name'];
$email = $_POST['email'];
$nik = $_POST['nik'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];
$role = 'employee';

if ($password !== $confirm) {
    header("Location: register.php?error=Password is not match");
    exit();
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah email sudah ada
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: register.php?error=Email already registered&email=" 
    . "&name=" . urlencode($name)
    . "&email=" . urlencode($email)
    . "&nik=" . urlencode($nik)
    . "&phone=" . urlencode($phone)
    );
    exit();
}

// Insert ke DB
$insert = "INSERT INTO users (name, nik, phone, email, password, role) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert);
$stmt->bind_param("ssssss", $name, $nik, $phone, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    header("Location: login.php?success=Register successful, please login");
} else {
    header("Location: register.php?error=Failed to register");
}
exit();
