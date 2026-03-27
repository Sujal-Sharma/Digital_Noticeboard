<?php
// Include the configuration file
require_once '../includes/config.php';

// Check if user is admin
check_admin();

// Get logged in user data
$user = get_user_data();

// Initialize variables
$error = '';
$success = '';

// Handle user role toggle
if (isset($_GET['toggle_role']) && is_numeric($_GET['toggle_role'])) {
    $toggle_id = (int)$_GET['toggle_role'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$toggle_id]);
    $row = $stmt->fetch();
    if ($row) {
        $new_role = ($row['role'] === 'admin') ? 'student' : 'admin';
        $conn->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $toggle_id]);
        $success = "User role updated to " . ucfirst($new_role) . ".";
    } else {
        $error = "User not found.";
    }
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id === $user['id']) {
        $error = "You cannot delete your own account.";
    } else {
        $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
        $success = "User deleted successfully.";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';

// Build query
$query  = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query   .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $sp       = "%$search%";
    $params[] = $sp; $params[] = $sp; $params[] = $sp;
}

if (!empty($role_filter)) {
    $query   .= " AND role = ?";
    $params[] = $role_filter;
}

// Count total records for pagination
$count_stmt = $conn->prepare($query);
$count_stmt->execute($params);
$total_records = count($count_stmt->fetchAll());
$total_pages   = ceil($total_records / $per_page);

// Add sorting and pagination
$query   .= " ORDER BY full_name ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SRMAP Noticeboard</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            margin-top: 0;
            color: #102542;
            margin-bottom: 1.5rem;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .user-table th, .user-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .user-table th {
            background-color: #f5f5f7;
            font-weight: 600;
        }
        
        .user-table tr:hover {
            background-color: #f5f5f7;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .role-admin {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .role-student {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        
        .search-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .search-filters .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover {
            background-color: #f5f5f7;
        }
        
        .pagination .active {
            background-color: #4e8aff;
            color: white;
        }
        
        .breadcrumb {
            display: flex;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: #4e8aff;
        }
        
        .breadcrumb span {
            margin: 0 0.5rem;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo-container">
            <img src="../images/srmap-logo.png" alt="SRMAP Logo" class="logo">
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="../about.php">About</a></li>
                <li><a href="../contact.php">Contact</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="dashboard-container">
            <!-- Sidebar -->
            <div class="sidebar">
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
                <div class="admin-navigation">
                    <div class="sidebar-section-label">Admin Panel</div>
                    <ul>
                        <li><a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
                        <li><a href="notices.php"><span class="nav-icon">📋</span> Manage Notices</a></li>
                        <li><a href="create_notice.php"><span class="nav-icon">✏️</span> Create Notice</a></li>
                        <li><a href="users.php" class="active"><span class="nav-icon">👥</span> Manage Users</a></li>
                        <li><a href="profile.php"><span class="nav-icon">⚙️</span> Profile Settings</a></li>
                    </ul>
                </div>
                <div class="sidebar-footer">
                    <a href="../includes/logout.php"><span class="nav-icon">🚪</span> Logout</a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <h1 style="font-size:1.6rem; font-weight:800; color:#1a1a2e; letter-spacing:-0.02em; margin-bottom:1.5rem;">Manage Users</h1>

                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a>
                    <span>></span>
                    <span>Manage Users</span>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="card">
                    <h2>User Management</h2>
                    
                    <!-- Search and Filters -->
                    <form action="users.php" method="GET" class="search-filters">
                        <div class="form-group">
                            <label for="search">Search Users</label>
                            <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Search by name, username or email">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Filter by Role</label>
                            <select id="role" name="role" onchange="this.form.submit()">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo ($role_filter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="student" <?php echo ($role_filter === 'student') ? 'selected' : ''; ?>>Student</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn">Search</button>
                            <?php if (!empty($search) || !empty($role_filter)): ?>
                                <a href="users.php" class="btn btn-outline" style="margin-left: 0.5rem;">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <!-- Users Table -->
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo $u['full_name']; ?></td>
                                        <td><?php echo $u['username']; ?></td>
                                        <td><?php echo $u['email']; ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $u['role']; ?>">
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($u['created_at']); ?></td>
                                        <td class="action-buttons">
                                            <a href="users.php?toggle_role=<?php echo $u['id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to change this user\'s role to <?php echo ($u['role'] === 'admin') ? 'Student' : 'Admin'; ?>?')">
                                                Make <?php echo ($u['role'] === 'admin') ? 'Student' : 'Admin'; ?>
                                            </a>
                                            
                                            <?php if ($u['id'] !== $user['id']): ?>
                                                <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    Delete
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?><?php echo (!empty($search)) ? '&search=' . $search : ''; ?><?php echo (!empty($role_filter)) ? '&role=' . $role_filter : ''; ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo (!empty($search)) ? '&search=' . $search : ''; ?><?php echo (!empty($role_filter)) ? '&role=' . $role_filter : ''; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo ($page + 1); ?><?php echo (!empty($search)) ? '&search=' . $search : ''; ?><?php echo (!empty($role_filter)) ? '&role=' . $role_filter : ''; ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- User Statistics -->
                <div class="card">
                    <h2>User Statistics</h2>
                    
                    <?php
                    // Get user statistics
                    $admin_count   = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                    $student_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
                    $recent_users  = $conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
                    $active_users  = $conn->query("SELECT COUNT(DISTINCT user_id) FROM notice_views WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
                    ?>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1; min-width: 200px; background-color: #f5f5f7; padding: 1.5rem; border-radius: 5px;">
                            <h3 style="margin: 0; color: #102542; font-size: 1.1rem;">Admin Users</h3>
                            <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 600; color: #4e8aff;"><?php echo $admin_count; ?></p>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px; background-color: #f5f5f7; padding: 1.5rem; border-radius: 5px;">
                            <h3 style="margin: 0; color: #102542; font-size: 1.1rem;">Student Users</h3>
                            <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 600; color: #4e8aff;"><?php echo $student_count; ?></p>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px; background-color: #f5f5f7; padding: 1.5rem; border-radius: 5px;">
                            <h3 style="margin: 0; color: #102542; font-size: 1.1rem;">New Users (30 days)</h3>
                            <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 600; color: #4e8aff;"><?php echo $recent_users; ?></p>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px; background-color: #f5f5f7; padding: 1.5rem; border-radius: 5px;">
                            <h3 style="margin: 0; color: #102542; font-size: 1.1rem;">Active Users (7 days)</h3>
                            <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 600; color: #4e8aff;"><?php echo $active_users; ?></p>
                        </div>
                    </div>
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