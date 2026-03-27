<?php
// Include configuration and database connection
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!is_logged_in()) {
    redirect('../login.php');
}

if (!is_admin()) {
    redirect('../student/dashboard.php');
}

// Check if notice ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('notices.php');
}

$notice_id = (int)$_GET['id'];

// Check if confirmation is required
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // Display confirmation page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Delete Notice - SRM University AP Notice Board</title>
        <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
        <style>
            .confirmation-container {
                max-width: 600px;
                margin: 5rem auto;
                padding: 2rem;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                text-align: center;
            }
            
            .confirmation-icon {
                font-size: 4rem;
                color: #c62828;
                margin-bottom: 1rem;
            }
            
            .confirmation-title {
                color: #102542;
                margin-bottom: 1rem;
            }
            
            .confirmation-message {
                color: #666;
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            
            .confirmation-buttons {
                display: flex;
                justify-content: center;
                gap: 1rem;
            }
            
            .btn {
                padding: 0.8rem 1.5rem;
                border-radius: 4px;
                font-weight: 600;
                text-decoration: none;
                transition: background-color 0.3s ease;
            }
            
            .btn-danger {
                background-color: #c62828;
                color: white;
            }
            
            .btn-danger:hover {
                background-color: #b71c1c;
            }
            
            .btn-secondary {
                background-color: #f0f0f0;
                color: #333;
            }
            
            .btn-secondary:hover {
                background-color: #e0e0e0;
            }
        </style>
    </head>
    <body>
        <header>
            <div class="logo-container">
                <img src="../images/srmap-logo.png" alt="SRM University AP Logo" class="logo">
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../about.php">About</a></li>
                    <li><a href="../contact.php">Contact</a></li>
                    <li><a href="../includes/logout.php" class="login-btn">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <div class="confirmation-container">
            <div class="confirmation-icon">⚠️</div>
            <h1 class="confirmation-title">Delete Notice</h1>
            <p class="confirmation-message">
                Are you sure you want to delete this notice? This action cannot be undone.
                All associated comments will also be deleted.
            </p>
            <div class="confirmation-buttons">
                <a href="notices.php" class="btn btn-secondary">Cancel</a>
                <a href="delete_notice.php?id=<?php echo $notice_id; ?>&confirm=yes" class="btn btn-danger">Delete</a>
            </div>
        </div>
        
        <footer>
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="../images/srmap-logo.png" alt="SRM University AP Logo" class="small-logo">
                    <p>SRM University AP</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../about.php">About</a></li>
                        <li><a href="../contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p>SRM University AP</p>
                    <p>Andhra Pradesh, India</p>
                    <p>Email: info@srmap.edu.in</p>
                    <p>Phone: +91-1234567890</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date("Y"); ?> SRM University AP. All rights reserved.</p>
            </div>
        </footer>
    </body>
    </html>
    <?php
    exit;
}

// Get notice to check for attachments
$stmt = $conn->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->execute([$notice_id]);
$notice = $stmt->fetch();

if ($notice && !empty($notice['attachment'])) {
    $attachment_path = '../uploads/attachments/' . basename($notice['attachment']);
    if (file_exists($attachment_path)) {
        unlink($attachment_path);
    }
}

// Delete comments (cascades via FK, but explicit for safety)
$conn->prepare("DELETE FROM comments WHERE notice_id = ?")->execute([$notice_id]);

// Delete notice
$stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
if ($stmt->execute([$notice_id])) {
    redirect('notices.php?success=Notice deleted successfully');
} else {
    redirect('notices.php?error=Failed to delete notice');
}