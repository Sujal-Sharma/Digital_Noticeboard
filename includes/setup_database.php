<?php
// Database setup file - runs automatically when needed

if (!defined('DB_HOST')) {
    require_once 'config.php';
}

// $conn is already available (PDO) from db_connect.php

// Create tables if they don't exist

$conn->exec("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  role VARCHAR(10) NOT NULL DEFAULT 'student',
  profile_image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->exec("CREATE TABLE IF NOT EXISTS notices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  importance VARCHAR(10) NOT NULL DEFAULT 'medium',
  important TINYINT(1) NOT NULL DEFAULT 0,
  pinned TINYINT(1) NOT NULL DEFAULT 0,
  attachment VARCHAR(255) DEFAULT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
)");

$conn->exec("CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notice_id INT NOT NULL,
  user_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (notice_id) REFERENCES notices(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$conn->exec("CREATE TABLE IF NOT EXISTS notice_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notice_id INT NOT NULL,
  user_id INT NOT NULL,
  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (notice_id) REFERENCES notices(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_notice_user (notice_id, user_id)
)");

$conn->exec("CREATE TABLE IF NOT EXISTS login_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Hash password for default accounts
$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);

// Check if admin user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if (!$admin) {
    // Create admin user
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $hashed_password, 'System Administrator', 'admin@srmap.edu.in', 'admin']);
    $admin_id = (int)$conn->lastInsertId();

    // Create sample students
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['student1', $hashed_password, 'Sample Student 1', 'student1@srmap.edu.in', 'student']);
    $student1_id = (int)$conn->lastInsertId();
    $stmt->execute(['student2', $hashed_password, 'Sample Student 2', 'student2@srmap.edu.in', 'student']);
    $student2_id = (int)$conn->lastInsertId();

    // Sample notices
    $notice_stmt = $conn->prepare("INSERT INTO notices (title, content, category, importance, important, pinned, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $notice_stmt->execute(['Welcome to SRMAP Noticeboard', 'This is the official noticeboard system for SRM University AP.', 'General', 'high', 1, 1, $admin_id]);
    $n1 = (int)$conn->lastInsertId();
    $notice_stmt->execute(['Campus Maintenance Schedule', 'The campus will undergo maintenance on Saturday from 9 AM to 5 PM.', 'Maintenance', 'medium', 0, 0, $admin_id]);
    $n2 = (int)$conn->lastInsertId();
    $notice_stmt->execute(['Final Exam Schedule Released', 'The final examination schedule for the Spring semester has been released.', 'Academic', 'high', 1, 0, $admin_id]);
    $n3 = (int)$conn->lastInsertId();
    $notice_stmt->execute(['Career Fair Next Week', 'The annual career fair will be held next week on Wednesday and Thursday.', 'Placement', 'medium', 0, 0, $admin_id]);
    $n4 = (int)$conn->lastInsertId();
    $notice_stmt->execute(['Library Extended Hours', 'The library will have extended hours during the exam period until midnight.', 'Library', 'low', 0, 0, $admin_id]);

    // Sample comments
    $c_stmt = $conn->prepare("INSERT INTO comments (notice_id, user_id, comment) VALUES (?, ?, ?)");
    $c_stmt->execute([$n1, $student1_id, 'Thank you for the information!']);
    $c_stmt->execute([$n1, $student2_id, 'Looking forward to using this new system.']);
    $c_stmt->execute([$n3, $student1_id, 'When will the detailed schedule be available?']);
    $c_stmt->execute([$n4, $student2_id, 'Are there companies recruiting CS graduates?']);

    // Sample views
    $v_stmt = $conn->prepare("INSERT IGNORE INTO notice_views (notice_id, user_id) VALUES (?, ?)");
    foreach ([[$n1,$student1_id],[$n1,$student2_id],[$n2,$student1_id],[$n3,$student1_id],[$n3,$student2_id],[$n4,$student2_id]] as $v) {
        $v_stmt->execute($v);
    }
} else {
    // Update admin password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashed_password]);
}
?>
