<?php
// Include the configuration file
require_once '../includes/config.php';

// Check if user is logged in
check_login();

// Get logged in user data
$user = get_user_data();

// Initialize variables
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        
        // Validate inputs
        if (empty($full_name) || empty($email)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email already exists (but not for this user)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $error = 'Email already registered by another user.';
            } else {
                // Process image upload if provided
                $profile_image = $user['profile_image']; // Default to current image
                
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
                    $upload_result = upload_file($_FILES['profile_image'], '../uploads/profile_images/');
                    
                    if ($upload_result['success']) {
                        $profile_image = $upload_result['file_path'];
                    } else {
                        $error = $upload_result['message'];
                    }
                }
                
                if (empty($error)) {
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE id = ?");
                    if ($stmt->execute([$full_name, $email, $profile_image, $user['id']])) {
                        $success = 'Profile updated successfully.';
                        $user = get_user_data();
                    } else {
                        $error = 'Error updating profile. Please try again.';
                    }
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user['id']])) {
                    $success = 'Password changed successfully.';
                } else {
                    $error = 'Error changing password. Please try again.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SRMAP Noticeboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }
        
        .profile-sidebar {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .profile-main {
            flex: 2;
            min-width: 300px;
        }
        
        .profile-card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1.5rem;
            display: block;
            background-color: #4e8aff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }
        
        .profile-info {
            text-align: center;
        }
        
        .profile-info h2 {
            margin-bottom: 0.5rem;
            color: #102542;
        }
        
        .profile-info p {
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .profile-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .profile-tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            border-radius: 5px;
            background-color: #f5f5f7;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .profile-tab.active {
            background-color: #4e8aff;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .stats-item {
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f5f5f7;
            border-radius: 5px;
        }
        
        .stats-item h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #102542;
        }
        
        .stats-item p {
            margin: 0.5rem 0 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: #4e8aff;
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
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Profile Content -->
    <div class="container">
        <h1>User Profile</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-container">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-info">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?php echo $user['profile_image']; ?>" alt="Profile" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar"><?php echo substr($user['full_name'], 0, 1); ?></div>
                    <?php endif; ?>
                    
                    <h2><?php echo $user['full_name']; ?></h2>
                    <p><?php echo $user['username']; ?></p>
                    <p><?php echo $user['email']; ?></p>
                    <p>Role: <?php echo ucfirst($user['role']); ?></p>
                    <p>Joined: <?php echo format_date($user['created_at']); ?></p>
                </div>
            </div>
            
            <!-- Profile Main Content -->
            <div class="profile-main">
                <!-- Profile Tabs -->
                <div class="profile-tabs">
                    <div class="profile-tab active" data-tab="edit-profile">Edit Profile</div>
                    <div class="profile-tab" data-tab="change-password">Change Password</div>
                    <div class="profile-tab" data-tab="activity">Activity</div>
                </div>
                
                <!-- Edit Profile Tab -->
                <div class="tab-content active" id="edit-profile">
                    <div class="profile-card">
                        <h2>Edit Profile Information</h2>
                        
                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_image">Profile Image</label>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                <small>Leave empty to keep current image. Max size: 5MB. Allowed types: JPG, JPEG, PNG, GIF.</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-content" id="change-password">
                    <div class="profile-card">
                        <h2>Change Password</h2>
                        
                        <form action="profile.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                                <small>Password must be at least 6 characters long.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="change_password" class="btn">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Activity Tab -->
                <div class="tab-content" id="activity">
                    <div class="profile-card">
                        <h2>Your Activity</h2>
                        
                        <?php
                        // Get user statistics
                        
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM notice_views WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $views_count = $stmt->fetchColumn();

                        $stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $comments_count = $stmt->fetchColumn();

                        $stmt = $conn->prepare("SELECT MAX(viewed_at) FROM notice_views WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $last_login = $stmt->fetchColumn();
                        ?>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                            <div class="stats-item" style="flex: 1; min-width: 120px;">
                                <h3>Notices Viewed</h3>
                                <p><?php echo $views_count; ?></p>
                            </div>
                            
                            <div class="stats-item" style="flex: 1; min-width: 120px;">
                                <h3>Comments Posted</h3>
                                <p><?php echo $comments_count; ?></p>
                            </div>
                            
                            <div class="stats-item" style="flex: 2; min-width: 250px;">
                                <h3>Last Activity</h3>
                                <p style="font-size: 1rem;"><?php echo $last_login ? format_datetime($last_login) : 'No activity yet'; ?></p>
                            </div>
                        </div>
                        
                        <!-- Recent Comments -->
                        <h3 style="margin-top: 2rem;">Your Recent Comments</h3>
                        
                        <?php
                        $stmt = $conn->prepare("SELECT c.*, n.title, n.id as notice_id FROM comments c JOIN notices n ON c.notice_id = n.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 5");
                        $stmt->execute([$user['id']]);
                        $recent_comments = $stmt->fetchAll();
                        ?>
                        
                        <?php if (empty($recent_comments)): ?>
                            <p>You haven't posted any comments yet.</p>
                        <?php else: ?>
                            <?php foreach ($recent_comments as $comment): ?>
                                <div style="padding: 1rem; border-bottom: 1px solid #eee; margin-bottom: 1rem;">
                                    <p style="margin: 0; font-size: 0.9rem; color: #666;">
                                        On <a href="view_notice.php?id=<?php echo $comment['notice_id']; ?>"><?php echo $comment['title']; ?></a> - 
                                        <?php echo format_datetime($comment['created_at']); ?>
                                    </p>
                                    <p style="margin: 0.5rem 0 0;"><?php echo nl2br($comment['comment'] ?? ''); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.profile-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>