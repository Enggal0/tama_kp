<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "tama_kp";

// Buat koneksi
$conn = mysqli_connect($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
// ------------------------
// Create `users` table
// ------------------------
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    nik VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20),
    email VARCHAR(100) NOT NULL UNIQUE,
    gender ENUM('male', 'female') NOT NULL,
    nik VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// ------------------------
// Create `tasks` table
// ------------------------
$sql_tasks = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
)";

// ------------------------
// Create `user_tasks` table (pivot table)
// ------------------------
$sql_user_tasks = "CREATE TABLE IF NOT EXISTS user_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
)";

// Eksekusi semua
$queries = [
    'users' => $sql_users,
    'tasks' => $sql_tasks,
    'user_tasks' => $sql_user_tasks
];

foreach ($queries as $name => $query) {
    if ($conn->query($query) === TRUE) {
        echo "✅ Table `$name` berhasil dibuat.<br>";
    } else {
        echo "❌ Error membuat table `$name`: " . $conn->error . "<br>";
    }
}
