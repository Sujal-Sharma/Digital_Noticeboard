<?php
// Include the configuration file
require_once '../includes/config.php';

// Check if user is admin
check_admin();

// Get logged in user data
$user = get_user_data();

// Handle notice deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notice_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    if ($stmt->execute([$notice_id])) {
        $success = 'Notice deleted successfully.';
    } else {
        $error = 'Error deleting notice.';
    }
}

// Get statistics
$notices_count  = $conn->query("SELECT COUNT(*) FROM notices")->fetchColumn();
$users_count    = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$comments_count = $conn->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$views_count    = $conn->query("SELECT COUNT(*) FROM notice_views")->fetchColumn();

// Get recent notices
$recent_notices = $conn->query(
    "SELECT n.*, u.username, u.full_name FROM notices n
     JOIN users u ON n.created_by = u.id
     ORDER BY n.created_at DESC LIMIT 5"
)->fetchAll();

// Get recent comments
$recent_comments = $conn->query(
    "SELECT c.*, n.title, n.id as notice_id, u.full_name, u.username
     FROM comments c
     JOIN notices n ON c.notice_id = n.id
     JOIN users u ON c.user_id = u.id
     ORDER BY c.created_at DESC LIMIT 5"
)->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SRMAP Noticeboard</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo-container">
            <img src="../images/logo.png" alt="SRMAP Logo" class="logo" onerror="this.style.display='none'">
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="../about.php">About</a></li>
                <li><a href="../contact.php">Contact</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../includes/logout.php" class="login-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- User Profile -->
            <div class="user-profile">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo $user['profile_image']; ?>" alt="Profile" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                <?php endif; ?>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p>Administrator</p>
                </div>
            </div>

            <!-- Admin Navigation -->
            <div class="admin-navigation">
                <div class="sidebar-section-label">Admin Panel</div>
                <ul>
                    <li><a href="dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard</a></li>
                    <li><a href="notices.php"><span class="nav-icon">📋</span> Manage Notices</a></li>
                    <li><a href="create_notice.php"><span class="nav-icon">✏️</span> Create Notice</a></li>
                    <li><a href="users.php"><span class="nav-icon">👥</span> Manage Users</a></li>
                    <li><a href="profile.php"><span class="nav-icon">⚙️</span> Profile Settings</a></li>
                </ul>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <a href="../includes/logout.php"><span class="nav-icon">🚪</span> Logout</a>
            </div>
        </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Admin Dashboard</h1>
                        <p class="page-subtitle">Overview of all notices, users, and activity</p>
                    </div>
                    <a href="create_notice.php" class="btn">+ Create Notice</a>
                </div>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <!-- Statistics -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-card-inner">
                            <div class="stat-card-info">
                                <h3>Total Notices</h3>
                                <p><?php echo $notices_count; ?></p>
                            </div>
                            <div class="stat-card-icon">📋</div>
                        </div>
                    </div>
                    <div class="stat-card stat-success">
                        <div class="stat-card-inner">
                            <div class="stat-card-info">
                                <h3>Total Users</h3>
                                <p><?php echo $users_count; ?></p>
                            </div>
                            <div class="stat-card-icon">👥</div>
                        </div>
                    </div>
                    <div class="stat-card stat-accent">
                        <div class="stat-card-inner">
                            <div class="stat-card-info">
                                <h3>Total Comments</h3>
                                <p><?php echo $comments_count; ?></p>
                            </div>
                            <div class="stat-card-icon">💬</div>
                        </div>
                    </div>
                    <div class="stat-card stat-warning">
                        <div class="stat-card-inner">
                            <div class="stat-card-info">
                                <h3>Total Views</h3>
                                <p><?php echo $views_count; ?></p>
                            </div>
                            <div class="stat-card-icon">👁️</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Notices -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Notices</h3>
                        <a href="notices.php" class="btn btn-small btn-outline">View All</a>
                    </div>
                    
                    <table class="notice-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Importance</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_notices)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No notices found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_notices as $notice): ?>
                                    <tr>
                                        <td><a href="view_notice.php?id=<?php echo $notice['id']; ?>"><?php echo $notice['title']; ?></a></td>
                                        <td><?php echo $notice['category']; ?></td>
                                        <td><span class="importance-badge importance-<?php echo $notice['importance']; ?>"><?php echo $notice['importance']; ?></span></td>
                                        <td><?php echo format_date($notice['created_at']); ?></td>
                                        <td class="action-buttons">
                                            <a href="view_notice.php?id=<?php echo $notice['id']; ?>" class="btn btn-small">View</a>
                                            <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" class="btn btn-small">Edit</a>
                                            <a href="dashboard.php?delete=<?php echo $notice['id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to delete this notice?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                </div>

                <!-- Recent Comments -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Comments</h3>
                    </div>
                    
                    <?php if (empty($recent_comments)): ?>
                        <p style="text-align: center;">No comments found.</p>
                    <?php else: ?>
                        <?php foreach ($recent_comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-meta">
                                    <span>By <strong><?php echo $comment['full_name']; ?></strong> on <a href="view_notice.php?id=<?php echo $comment['notice_id']; ?>"><?php echo $comment['title']; ?></a></span>
                                    <span><?php echo format_datetime($comment['created_at']); ?></span>
                                </div>
                                <p><?php echo nl2br($comment['comment'] ?? ''); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="../images/srmap-logo-white.png" alt="SRMAP Logo" class="small-logo">
                    <p>SRM University AP</p>
                </div>
                <p>A digital platform for all university notices and announcements.</p>
            </div>
            <div class="footer-section footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../about.php">About</a></li>
                    <li><a href="../contact.php">Contact</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">Profile</a></li>
                </ul>
            </div>
            <div class="footer-section footer-contact">
                <h3>Contact Us</h3>
                <p>SRM University AP</p>
                <p>Andhra Pradesh, India</p>
                <p>Email: info@srmap.edu.in</p>
                <p>Phone: +91 123 456 7890</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> SRM University AP Noticeboard. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>