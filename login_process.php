<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE nik = '$nik'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Redirect sesuai role
        switch ($user['role']) {
            case 'admin':
                header('Location: admin/dashboard.php');
                break;
            case 'employee':
                header('Location: karyawan/dashboard.php');
                break;
            case 'manager':
                header('Location: manager/dashboard.php');
                break;
            default:
                header('Location: login.php?error=role'); // role gak dikenal
                break;
        }
        exit();
    } else {
        header('Location: login.php?error=invalid');
        exit();
    }
}
?>
