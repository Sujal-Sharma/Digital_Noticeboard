<?php
// Include configuration and database connection
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

// Get user data
$user = get_user_data();

// Get categories for filter
$notice_categories = [];
foreach (get_categories() as $cat) {
    $notice_categories[$cat] = $cat;
}

// Get search query if provided
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Get category filter if provided
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query
$sql    = "SELECT n.*, u.full_name as author_name FROM notices n JOIN users u ON n.created_by = u.id WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $sql     .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if (!empty($category_filter)) {
    $sql     .= " AND n.category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$notices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notices - SRM University AP Notice Board</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 160px);
        }
        
        .sidebar {
            width: 250px;
            background-color: #102542;
            color: #fff;
            padding: 2rem 0;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: block;
            transition: background-color 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .user-info {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            object-fit: cover;
            background-color: #4e8aff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            color: white;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .user-role {
            background-color: #4e8aff;
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: #f8f9fa;
            overflow-y: auto;
        }
        
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
        
        .notice-footer {
            padding: 1rem;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .read-more {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #4e8aff;
            color: white;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .read-more:hover {
            background-color: #3a7df0;
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
        
        .no-notices {
            text-align: center;
            padding: 3rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
        }
        
        .notice-category-filter {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .category-filter-btn {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .category-filter-btn:hover {
            border-color: #4e8aff;
            color: #4e8aff;
        }
        
        .category-filter-btn.active {
            background-color: #4e8aff;
            border-color: #4e8aff;
            color: white;
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
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                <?php endif; ?>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p>Student</p>
                </div>
            </div>
            <div class="student-navigation">
                <div class="sidebar-section-label">Navigation</div>
                <ul>
                    <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
                    <li><a href="notices.php" class="active"><span class="nav-icon">📋</span> All Notices</a></li>
                    <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
                </ul>
            </div>
            <div class="sidebar-footer">
                <a href="../includes/logout.php"><span class="nav-icon">🚪</span> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">All Notices</h1>
            </div>
            
            <div class="search-filters">
                <form class="search-form" action="" method="get">
                    <div class="form-group">
                        <label for="search">Search Notices</label>
                        <input type="text" id="search" name="search" placeholder="Title or Content" value="<?php echo $search_query; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Filter by Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($notice_categories as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $category_filter === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="search-btn">Search</button>
                        <a href="notices.php" class="reset-btn">Reset</a>
                    </div>
                </form>
            </div>
            
            <div class="notice-category-filter">
                <a href="notices.php" class="category-filter-btn <?php echo empty($category_filter) ? 'active' : ''; ?>">All</a>
                
                <?php foreach ($notice_categories as $key => $value): ?>
                <a href="notices.php?category=<?php echo $key; ?>" class="category-filter-btn <?php echo $category_filter === $key ? 'active' : ''; ?>">
                    <?php echo $value; ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($notices)): ?>
            <div class="notice-grid">
                <?php foreach ($notices as $notice): ?>
                <div class="notice-card">
                    <?php if (!empty($notice['attachment'])): ?>
                    <div class="notice-attachment-icon" title="Has Attachment">📎</div>
                    <?php endif; ?>
                    
                    <div class="notice-header">
                        <span class="badge badge-<?php echo $notice['category']; ?>">
                            <?php echo ucfirst($notice['category']); ?>
                        </span>
                        
                        <div class="notice-badges">
                            <?php if (!empty($notice['importance']) && $notice['importance'] === 'high'): ?>
                                <span class="badge badge-important">Important</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="notice-body">
                        <h3 class="notice-title">
                            <a href="view_notice.php?id=<?php echo $notice['id']; ?>">
                                <?php echo $notice['title']; ?>
                            </a>
                        </h3>
                        
                        <div class="notice-excerpt">
                            <?php echo substr($notice['content'], 0, 150) . (strlen($notice['content']) > 150 ? '...' : ''); ?>
                        </div>
                        
                        <div class="notice-meta">
                            <span title="Author">👤 <?php echo $notice['author_name']; ?></span>
                            <span title="Posted Date">📅 <?php echo format_date($notice['created_at']); ?></span>
                        </div>
                    </div>
                    
                    <div class="notice-footer">
                        <a href="view_notice.php?id=<?php echo $notice['id']; ?>" class="read-more">Read More</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-notices">
                <h2>No notices found</h2>
                <p>Try adjusting your search criteria.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../images/srmap-logo-white.png" alt="SRM University AP Logo" class="small-logo">
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