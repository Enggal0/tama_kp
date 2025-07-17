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
    gender ENUM('male', 'female') NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// ------------------------
// Create `tasks` table
// ------------------------
$sql_tasks = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('numeric', 'text') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// ------------------------
// Create `user_tasks` table
// ------------------------
$sql_user_tasks = "CREATE TABLE IF NOT EXISTS user_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    description VARCHAR(255),
    target_int INT,
    target_str VARCHAR(255),
    progress_int INT DEFAULT 0,
    deadline DATE,
    status ENUM('In Progress', 'Achieved', 'Non Achieved') DEFAULT 'In Progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// ------------------------
// Create `task_achievements` table
// ------------------------
$sql_task_achievements = "CREATE TABLE IF NOT EXISTS task_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_task_id INT,
    achievement_date DATE,
    value_int INT,
    value_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_task_id) REFERENCES user_tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// Eksekusi semua
$queries = [
    'users' => $sql_users,
    'tasks' => $sql_tasks,
    'user_tasks' => $sql_user_tasks,
    'task_achievements' => $sql_task_achievements
];


?>
