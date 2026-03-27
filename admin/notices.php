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

// Get user data
$user = get_user_data();
$full_name = $user ? $user['full_name'] : $_SESSION['username'];
$email     = $user ? $user['email']     : 'admin@srmap.edu.in';

// Process success and error messages
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = sanitize_input($_GET['success']);
}

if (isset($_GET['error'])) {
    $error_message = sanitize_input($_GET['error']);
}

// Get search query if provided
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Get category filter if provided
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query
$sql    = "SELECT n.*, u.full_name as author_name FROM notices n JOIN users u ON n.created_by = u.id WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $sql      .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $params[]  = "%$search_query%";
    $params[]  = "%$search_query%";
}

if (!empty($category_filter)) {
    $sql     .= " AND n.category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY n.importance = 'high' DESC, n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$notices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notices - SRM University AP Notice Board</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-title {
            color: #102542;
            margin: 0;
        }
        
        .create-notice-btn {
            background-color: #4e8aff;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .create-notice-btn:hover {
            background-color: #3a7df0;
        }
        
        .search-filters {
            margin-bottom: 2rem;
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .search-btn {
            background-color: #4e8aff;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .search-btn:hover {
            background-color: #3a7df0;
        }
        
        .reset-btn {
            background-color: #f0f0f0;
            color: #333;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .reset-btn:hover {
            background-color: #e0e0e0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .notice-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .notice-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .notice-header {
            padding: 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notice-badges {
            display: flex;
            gap: 0.5rem;
        }
        
        .notice-body {
            padding: 1.5rem;
        }
        
        .notice-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #102542;
        }
        
        .notice-title a {
            color: #102542;
        }
        
        .notice-title a:hover {
            color: #4e8aff;
        }
        
        .notice-excerpt {
            color: #666;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .notice-meta {
            display: flex;
            justify-content: space-between;
            color: #777;
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .notice-actions {
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-academic {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .badge-admission {
            background-color: #e8f5e9;
            color: #1b5e20;
        }
        
        .badge-examination {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .badge-event {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-placement {
            background-color: #e8eaf6;
            color: #303f9f;
        }
        
        .badge-scholarship {
            background-color: #fce4ec;
            color: #c2185b;
        }
        
        .badge-general {
            background-color: #eeeeee;
            color: #424242;
        }
        
        .badge-important {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .badge-pinned {
            background-color: #e0f7fa;
            color: #006064;
        }
        
        .action-btn {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .view-btn {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .view-btn:hover {
            background-color: #bbdefb;
        }
        
        .edit-btn {
            background-color: #e8f5e9;
            color: #1b5e20;
        }
        
        .edit-btn:hover {
            background-color: #c8e6c9;
        }
        
        .delete-btn {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .delete-btn:hover {
            background-color: #ffcdd2;
        }
        
        .no-notices {
            text-align: center;
            padding: 3rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
        }
        
        .no-notices p {
            margin-bottom: 1rem;
        }
        
        .notice-attachment-icon {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8rem;
            color: #555;
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

    <div class="dashboard-container">
        <div class="sidebar">
            <div class="user-profile">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo $user['profile_image']; ?>" alt="Profile" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                <?php endif; ?>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($full_name); ?></h3>
                    <p>Administrator</p>
                </div>
            </div>
            <div class="admin-navigation">
                <div class="sidebar-section-label">Admin Panel</div>
                <ul>
                    <li><a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
                    <li><a href="notices.php" class="active"><span class="nav-icon">📋</span> Manage Notices</a></li>
                    <li><a href="create_notice.php"><span class="nav-icon">✏️</span> Create Notice</a></li>
                    <li><a href="users.php"><span class="nav-icon">👥</span> Manage Users</a></li>
                    <li><a href="profile.php"><span class="nav-icon">⚙️</span> Profile Settings</a></li>
                </ul>
            </div>
            <div class="sidebar-footer">
                <a href="../includes/logout.php"><span class="nav-icon">🚪</span> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="font-size:1.6rem; font-weight:800; color:#1a1a2e; letter-spacing:-0.02em; margin-bottom:1.5rem;">Manage Notices</h1>
                <a href="create_notice.php" style="background-color: #4e8aff; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500;">+ Create Notice</a>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div style="background-color: white; border-radius: 8px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <form action="notices.php" method="GET">
                    <div style="display: flex; gap: 20px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label for="search" style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Search Notices</label>
                            <input type="text" id="search" name="search" placeholder="Title or Content" value="<?php echo $search_query; ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        
                        <div style="flex: 1;">
                            <label for="category" style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Filter by Category</label>
                            <select id="category" name="category" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">All Categories</option>
                                <?php foreach ($notice_categories as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $category_filter === $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <button type="submit" class="search-btn" style="background-color: #4e8aff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Search</button>
                            <a href="notices.php" class="reset-btn" style="background-color: #f0f0f0; color: #333; border: none; padding: 8px 16px; border-radius: 4px; text-decoration: none; margin-left: 5px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($notices)): ?>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <?php foreach ($notices as $notice): ?>
                <div style="background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <div style="padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                        <div style="font-size: 14px;">
                            <?php echo ucfirst($notice['category']); ?>
                        </div>
                        
                        <div>
                            <?php if ($notice['importance'] == 'high'): ?>
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 12px; background-color: #ffebee; color: #c62828;">High Importance</span>
                            <?php elseif ($notice['importance'] == 'medium'): ?>
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 12px; background-color: #e0f7fa; color: #006064;">Medium Importance</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="padding: 15px;">
                        <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 18px;">
                            <a href="view_notice.php?id=<?php echo $notice['id']; ?>" style="color: #333; text-decoration: none;">
                                <?php echo $notice['title']; ?>
                            </a>
                        </h3>
                        
                        <p style="color: #666; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo substr($notice['content'], 0, 100) . (strlen($notice['content']) > 100 ? '...' : ''); ?>
                        </p>
                        
                        <div style="color: #888; font-size: 12px; margin-bottom: 10px;">
                            <span>👤 <?php echo $notice['author_name']; ?></span>
                            <span style="margin-left: 10px;">📅 <?php echo format_date($notice['created_at']); ?></span>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 5px; padding: 10px 15px; border-top: 1px solid #eee;">
                        <a href="view_notice.php?id=<?php echo $notice['id']; ?>" style="flex: 1; text-align: center; padding: 5px; background-color: #e3f2fd; color: #0d47a1; border-radius: 4px; text-decoration: none; font-size: 13px;">View</a>
                        <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" style="flex: 1; text-align: center; padding: 5px; background-color: #e8f5e9; color: #1b5e20; border-radius: 4px; text-decoration: none; font-size: 13px;">Edit</a>
                        <a href="delete_notice.php?id=<?php echo $notice['id']; ?>" style="flex: 1; text-align: center; padding: 5px; background-color: #ffebee; color: #c62828; border-radius: 4px; text-decoration: none; font-size: 13px;">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; background-color: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <h2 style="margin-top: 0; color: #333;">No notices found</h2>
                <p style="color: #666; margin-bottom: 20px;">Try adjusting your search criteria or create a new notice.</p>
                <a href="create_notice.php" style="background-color: #4e8aff; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block;">Create Notice</a>
            </div>
            <?php endif; ?>
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