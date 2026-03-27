<?php
// Include the configuration file
require_once '../includes/config.php';

// Check if user is logged in
check_login();

// Set default values
$category_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$importance = isset($_GET['importance']) ? sanitize_input($_GET['importance']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get logged in user data
$user = get_user_data();

// Build query
$query  = "SELECT n.*, u.username, u.full_name FROM notices n JOIN users u ON n.created_by = u.id WHERE 1=1";
$params = [];

if (!empty($category_filter)) {
    $query   .= " AND n.category = ?";
    $params[] = $category_filter;
}

if (!empty($search)) {
    $query   .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $sp       = "%$search%";
    $params[] = $sp; $params[] = $sp;
}

if (!empty($importance)) {
    $query   .= " AND n.importance = ?";
    $params[] = $importance;
}

// Count total records for pagination
$count_stmt = $conn->prepare($query);
$count_stmt->execute($params);
$total_records = count($count_stmt->fetchAll());
$total_pages   = ceil($total_records / $per_page);

// Add pagination
$query   .= " ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->execute($params);
$notices = $stmt->fetchAll();

// Get categories for filter dropdown
$categories = get_categories();

// Track notice views
if (isset($_GET['viewed']) && is_numeric($_GET['viewed'])) {
    $viewed_id = (int)$_GET['viewed'];
    $uid = $user['id'];
    $chk = $conn->prepare("SELECT id FROM notice_views WHERE notice_id = ? AND user_id = ?");
    $chk->execute([$viewed_id, $uid]);
    if (!$chk->fetch()) {
        $conn->prepare("INSERT INTO notice_views (notice_id, user_id) VALUES (?, ?)")->execute([$viewed_id, $uid]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SRMAP Noticeboard</title>
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
                    <p>Student</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="student-navigation">
                <div class="sidebar-section-label">Navigation</div>
                <ul>
                    <li><a href="dashboard.php" class="active"><span class="nav-icon">🏠</span> Dashboard</a></li>
                    <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
                </ul>

                <div class="sidebar-section-label" style="margin-top:1rem;">Filters</div>

                <!-- Category Filter -->
                <form action="dashboard.php" method="GET" style="padding: 0 0.75rem;">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                    <?php if (!empty($importance)): ?><input type="hidden" name="importance" value="<?php echo htmlspecialchars($importance); ?>"><?php endif; ?>
                    <div style="margin-bottom:0.8rem;">
                        <label style="display:block; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:rgba(167,139,250,0.45); margin-bottom:0.4rem;">Category</label>
                        <select name="category" onchange="this.form.submit()" style="width:100%; padding:0.55rem 0.75rem; background:rgba(124,58,237,0.10); border:1px solid rgba(124,58,237,0.20); border-radius:8px; color:rgba(255,255,255,0.80); font-size:0.85rem; font-family:inherit; outline:none; cursor:pointer;">
                            <option value="" style="background:#1a0f3d;">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" <?php echo ($category_filter === $category) ? 'selected' : ''; ?> style="background:#1a0f3d;"><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <!-- Importance Filter -->
                <form action="dashboard.php" method="GET" style="padding: 0 0.75rem;">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                    <?php if (!empty($category_filter)): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>"><?php endif; ?>
                    <div style="margin-bottom:0.8rem;">
                        <label style="display:block; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:rgba(167,139,250,0.45); margin-bottom:0.4rem;">Importance</label>
                        <select name="importance" onchange="this.form.submit()" style="width:100%; padding:0.55rem 0.75rem; background:rgba(124,58,237,0.10); border:1px solid rgba(124,58,237,0.20); border-radius:8px; color:rgba(255,255,255,0.80); font-size:0.85rem; font-family:inherit; outline:none; cursor:pointer;">
                            <option value="" style="background:#1a0f3d;">All Levels</option>
                            <option value="high" <?php echo ($importance === 'high') ? 'selected' : ''; ?> style="background:#1a0f3d;">🔴 High</option>
                            <option value="medium" <?php echo ($importance === 'medium') ? 'selected' : ''; ?> style="background:#1a0f3d;">🟡 Medium</option>
                            <option value="low" <?php echo ($importance === 'low') ? 'selected' : ''; ?> style="background:#1a0f3d;">🟢 Low</option>
                        </select>
                    </div>
                </form>

                <?php if (!empty($category_filter) || !empty($search) || !empty($importance)): ?>
                    <div style="padding: 0 0.75rem;">
                        <a href="dashboard.php" style="display:block; text-align:center; padding:0.55rem; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.20); border-radius:8px; color:#fca5a5; font-size:0.82rem; font-weight:600; text-decoration:none; transition:all 0.3s;">✕ Clear Filters</a>
                    </div>
                <?php endif; ?>
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
                    <h1 class="page-title">Student Dashboard</h1>
                    <p class="page-subtitle">Browse and filter university notices</p>
                </div>
            </div>
                <!-- Search Form -->
                <form action="dashboard.php" method="GET" class="search-form">
                    <?php if (!empty($category_filter)): ?>
                        <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                    <?php endif; ?>
                    <?php if (!empty($importance)): ?>
                        <input type="hidden" name="importance" value="<?php echo $importance; ?>">
                    <?php endif; ?>
                    
                    <input type="text" name="search" placeholder="Search notices..." value="<?php echo $search; ?>">
                    <button type="submit">Search</button>
                </form>
                
                <!-- Notices -->
                <?php if (empty($notices)): ?>
                    <div class="card" style="text-align:center; padding:3rem;">
                        <div style="font-size:3rem; margin-bottom:1rem;">📭</div>
                        <h3 style="color:var(--db-text-dark); margin-bottom:0.5rem;">No notices found</h3>
                        <p style="color:var(--db-text-muted);">There are no notices matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notices as $notice): ?>
                        <div class="notice-card importance-<?php echo $notice['importance']; ?>">
                            <div class="notice-header">
                                <h3 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h3>
                                <div style="display:flex; gap:0.5rem; flex-shrink:0;">
                                    <span class="importance-badge importance-<?php echo $notice['importance']; ?>"><?php echo $notice['importance']; ?></span>
                                    <span class="category-badge"><?php echo htmlspecialchars($notice['category']); ?></span>
                                </div>
                            </div>
                            
                            <div class="notice-meta">
                                <span>Posted by: <?php echo $notice['full_name']; ?></span>
                                <span>Date: <?php echo format_date($notice['created_at']); ?></span>
                            </div>
                            
                            <div class="notice-content">
                                <?php 
                                // Show a preview of the content
                                $content = $notice['content'];
                                if (strlen($content) > 200) {
                                    $content = substr($content, 0, 200) . '...';
                                }
                                echo $content;
                                ?>
                            </div>
                            
                            <div class="notice-footer">
                                <a href="view_notice.php?id=<?php echo $notice['id']; ?>&viewed=<?php echo $notice['id']; ?>" class="btn">Read More</a>
                                <?php if (!empty($notice['attachment'])): ?>
                                    <a href="<?php echo $notice['attachment']; ?>" class="btn btn-outline" download>Download Attachment</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?><?php echo (!empty($category_filter)) ? '&category=' . $category_filter : ''; ?><?php echo (!empty($search)) ? '&search=' . $search : ''; ?><?php echo (!empty($importance)) ? '&importance=' . $importance : ''; ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo (!empty($category_filter)) ? '&category=' . $category_filter : ''; ?><?php echo (!empty($search)) ? '&search=' . $search : ''; ?><?php echo (!empty($importance)) ? '&importance=' . $importance : ''; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo ($page + 1); ?><?php echo (!empty($category_filter)) ? '&category=' . $category_filter : ''; ?><?php echo (!empty($search)) ? '&search=' . $search : ''; ?><?php echo (!empty($importance)) ? '&importance=' . $importance : ''; ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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