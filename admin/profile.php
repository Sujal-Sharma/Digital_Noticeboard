<?php
// Include configuration
require_once '../includes/config.php';

// Check if user is admin
check_admin();

// Get user data
$user = get_user_data();

// Initialize messages
$success_message = '';
$error_message = '';

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    
    // Validate input
    if (empty($full_name) || empty($email)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Check if email is already used by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $error_message = "Email is already in use by another account.";
        } else {
            // Process profile image if uploaded
            $profile_image = $user['profile_image'];
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $upload_result = upload_file($_FILES['profile_image'], '../uploads/profile/');
                
                if ($upload_result['success']) {
                    $profile_image = $upload_result['file_path'];
                } else {
                    $error_message = $upload_result['message'];
                }
            }
            
            if (empty($error_message)) {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE id = ?");
                if ($stmt->execute([$full_name, $email, $profile_image, $user['id']])) {
                    $success_message = "Profile updated successfully.";
                    $_SESSION['user_name'] = $full_name;
                    $user = get_user_data();
                } else {
                    $error_message = "Error updating profile. Please try again.";
                }
            }
        }
    }
}

// Handle password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user['id']])) {
                $success_message = "Password updated successfully.";
            } else {
                $error_message = "Error updating password. Please try again.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

// Get notice stats
$notices_stmt = $conn->prepare("SELECT COUNT(*) FROM notices WHERE created_by = ?");
$notices_stmt->execute([$user['id']]);
$notices_count = $notices_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SRM University AP Notice Board</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            margin: 0;
            color: #102542;
            font-size: 1.8rem;
        }
        
        .page-title p {
            margin: 0.5rem 0 0;
            color: #6c757d;
        }
        
        .admin-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background-color: #f8d7da;
            color: #58151c;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .profile-section-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .profile-section-header h2 {
            margin: 0;
            color: #102542;
            font-size: 1.3rem;
        }
        
        .profile-section-body {
            padding: 1.5rem;
        }
        
        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #4e8aff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
            overflow: hidden;
        }
        
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-upload label {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .avatar-upload label:hover {
            background-color: #e9ecef;
        }
        
        .avatar-upload input[type="file"] {
            display: none;
        }
        
        .profile-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #495057;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group input:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .btn-submit {
            background-color: #4e8aff;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .btn-submit:hover {
            background-color: #3a7df0;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #4e8aff;
            margin: 0 0 0.5rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .account-info {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .account-info li {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .account-info li:last-child {
            border-bottom: none;
        }
        
        .account-info-label {
            width: 150px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .account-info-value {
            flex: 1;
            color: #102542;
            font-weight: 600;
        }
        
        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
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
                <li><a href="dashboard.php">Dashboard</a></li>
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
                    <p>Administrator</p>
                </div>
            </div>
            <div class="admin-navigation">
                <div class="sidebar-section-label">Admin Panel</div>
                <ul>
                    <li><a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
                    <li><a href="notices.php"><span class="nav-icon">📋</span> Manage Notices</a></li>
                    <li><a href="create_notice.php"><span class="nav-icon">✏️</span> Create Notice</a></li>
                    <li><a href="users.php"><span class="nav-icon">👥</span> Manage Users</a></li>
                    <li><a href="profile.php" class="active"><span class="nav-icon">⚙️</span> Profile Settings</a></li>
                </ul>
            </div>
            <div class="sidebar-footer">
                <a href="../includes/logout.php"><span class="nav-icon">🚪</span> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <h1 style="font-size:1.6rem; font-weight:800; color:#1a1a2e; letter-spacing:-0.02em; margin-bottom:1.5rem;">My Profile
                    <span style="display: inline-block; background-color: #f8d7da; color: #721c24; font-size: 12px; padding: 3px 10px; border-radius: 20px; margin-left: 10px; font-weight: 500;">Administrator</span>
                </h1>
                <p style="margin-top: 5px; color: #666; font-size: 14px;">Manage your account information and preferences</p>

                <?php if (!empty($success_message)): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 12px 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #c3e6cb;">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 12px 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #f5c6cb;">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
            
            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <div style="flex: 1; background-color: white; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; color: #4e8aff; font-size: 28px;"><?php echo $notices_count; ?></h2>
                    <p style="margin: 0; color: #666; font-size: 14px;">Notices Created</p>
                </div>
                <div style="flex: 1; background-color: white; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; color: #4e8aff; font-size: 22px; word-break: break-word;"><?php echo strtoupper(format_date($user['created_at'])); ?></h2>
                    <p style="margin: 0; color: #666; font-size: 14px;">Member Since</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div>
                    <div style="background-color: white; border-radius: 8px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <div style="padding: 15px; border-bottom: 1px solid #eee;">
                            <h2 style="margin: 0; font-size: 18px; color: #333;">Personal Information</h2>
                        </div>
                        <div style="padding: 20px;">
                            <form method="post" action="" enctype="multipart/form-data">
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <img src="<?php echo $user['profile_image']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px;">
                                    <?php else: ?>
                                        <div style="width: 100px; height: 100px; border-radius: 50%; background-color: #4e8aff; color: white; font-size: 36px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                            <?php echo substr($user['full_name'], 0, 1); ?>
                                        </div>
                                    <?php endif; ?>
                                    <label for="profile_image" style="border: none; background-color: #f0f0f0; padding: 5px 10px; border-radius: 4px; font-size: 12px; cursor: pointer;">Change Profile Picture</label>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display:none;">
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 14px;">Username</label>
                                    <input type="text" value="<?php echo $user['username']; ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; background-color: #f8f9fa;" disabled>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="full_name" style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 14px;">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" required>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="email" style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 14px;">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" required>
                                </div>
                                
                                <div style="text-align: right;">
                                    <button type="submit" name="update_profile" style="background-color: #4e8aff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div style="background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <div style="padding: 15px; border-bottom: 1px solid #eee;">
                            <h2 style="margin: 0; font-size: 18px; color: #333;">Account Information</h2>
                        </div>
                        <div style="padding: 0;">
                            <div style="display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid #eee;">
                                <span style="color: #666; font-size: 14px;">Account ID</span>
                                <span style="color: #333; font-weight: 500; font-size: 14px;">#<?php echo $user['id']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid #eee;">
                                <span style="color: #666; font-size: 14px;">Role</span>
                                <span style="color: #333; font-weight: 500; font-size: 14px;"><?php echo ucfirst($user['role']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid #eee;">
                                <span style="color: #666; font-size: 14px;">Created</span>
                                <span style="color: #333; font-weight: 500; font-size: 14px;"><?php echo format_datetime($user['created_at']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 15px;">
                                <span style="color: #666; font-size: 14px;">Last Updated</span>
                                <span style="color: #333; font-weight: 500; font-size: 14px;"><?php echo format_datetime($user['updated_at']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Change Section -->
                    <div style="background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 20px;">
                        <div style="padding: 15px; border-bottom: 1px solid #eee;">
                            <h2 style="margin: 0; font-size: 18px; color: #333;">Change Password</h2>
                        </div>
                        <div style="padding: 20px;">
                            <form method="post" action="">
                                <div style="margin-bottom: 15px;">
                                    <label for="current_password" style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 14px;">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" required>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="new_password" style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 14px;">New Password</label>
                                    <input type="password" id="new_password" name="new_password" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" required>
                                    <small style="display: block; color: #777; margin-top: 5px; font-size: 12px;">Password must be at least 6 characters long</small>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="confirm_password" style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 14px;">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" required>
                                </div>
                                
                                <div style="text-align: right;">
                                    <button type="submit" name="update_password" style="background-color: #4e8aff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
    
    <script>
        // Preview uploaded image before submitting
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const avatarPreview = document.querySelector('.avatar-preview');
                    
                    // Check if there's already an image
                    let img = avatarPreview.querySelector('img');
                    if (!img) {
                        // Create image element if it doesn't exist
                        avatarPreview.textContent = '';
                        img = document.createElement('img');
                        avatarPreview.appendChild(img);
                    }
                    
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>