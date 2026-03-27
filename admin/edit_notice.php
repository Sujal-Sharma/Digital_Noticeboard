<?php
// Include configuration
require_once '../includes/config.php';

// Check if user is admin
check_admin();

// Get user data
$user = get_user_data();

// Check if notice ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('dashboard.php');
}

$notice_id = (int)$_GET['id'];

// Get notice details
$stmt = $conn->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->execute([$notice_id]);
$notice = $stmt->fetch();

if (!$notice) {
    redirect('dashboard.php');
}

// Initialize messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $category = sanitize_input($_POST['category']);
    $importance = sanitize_input($_POST['importance']);
    $attachment = $notice['attachment']; // Keep existing attachment by default
    
    // Validate input
    if (empty($title) || empty($content) || empty($category) || empty($importance)) {
        $error_message = "All fields are required.";
    } else {
        // Process attachment if a new one is uploaded
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
            $upload_result = upload_file($_FILES['attachment'], '../uploads/notices/');
            
            if ($upload_result['success']) {
                // If previous attachment exists, we could delete it here
                // For now, just update to the new attachment
                $attachment = $upload_result['file_path'];
            } else {
                $error_message = $upload_result['message'];
            }
        }
        
        if (empty($error_message)) {
            $stmt = $conn->prepare("UPDATE notices SET title = ?, content = ?, category = ?, importance = ?, attachment = ? WHERE id = ?");
            if ($stmt->execute([$title, $content, $category, $importance, $attachment, $notice_id])) {
                $success_message = "Notice updated successfully.";
                // Refresh notice data
                $stmt = $conn->prepare("SELECT * FROM notices WHERE id = ?");
                $stmt->execute([$notice_id]);
                $notice = $stmt->fetch();
            } else {
                $error_message = "Error updating notice. Please try again.";
            }
        }
    }
}

// Get existing categories for dropdown
$categories = get_categories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Notice - SRM University AP Notice Board</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: #f8f9fa;
        }
        
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
        
        .notice-form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .form-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-header h2 {
            margin: 0;
            color: #102542;
            font-size: 1.3rem;
        }
        
        .form-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #495057;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #4e8aff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(78, 138, 255, 0.2);
        }
        
        .form-group .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.3rem;
        }
        
        .form-row {
            display: flex;
            gap: 1.5rem;
        }
        
        .form-col {
            flex: 1;
            min-width: 0;
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
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        .form-footer {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .custom-file-input input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .custom-file-input label {
            display: block;
            padding: 0.8rem;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .custom-file-input:hover label {
            background-color: #e9ecef;
        }
        
        .current-attachment {
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .attachment-link {
            color: #4e8aff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .attachment-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-row {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="../images/srm_logo.png" alt="SRM University AP Logo" class="logo">
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
            <h1 style="font-size:1.6rem; font-weight:800; color:#1a1a2e; letter-spacing:-0.02em; margin-bottom:1.5rem;">Edit Notice</h1>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="notice-form-container">
                <div class="form-header">
                    <h2>Edit Notice Details</h2>
                </div>
                
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-body">
                        <div class="form-group">
                            <label for="title">Notice Title</label>
                            <input type="text" id="title" name="title" value="<?php echo $notice['title']; ?>" required>
                            <p class="help-text">Keep the title concise and descriptive.</p>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select id="category" name="category" required>
                                        <option value="General" <?php echo ($notice['category'] === 'General') ? 'selected' : ''; ?>>General</option>
                                        <option value="Academics" <?php echo ($notice['category'] === 'Academics') ? 'selected' : ''; ?>>Academics</option>
                                        <option value="Events" <?php echo ($notice['category'] === 'Events') ? 'selected' : ''; ?>>Events</option>
                                        <option value="Placements" <?php echo ($notice['category'] === 'Placements') ? 'selected' : ''; ?>>Placements</option>
                                        <option value="Examinations" <?php echo ($notice['category'] === 'Examinations') ? 'selected' : ''; ?>>Examinations</option>
                                        <option value="Sports" <?php echo ($notice['category'] === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                                        <option value="Facilities" <?php echo ($notice['category'] === 'Facilities') ? 'selected' : ''; ?>>Facilities</option>
                                        <option value="Other" <?php echo ($notice['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <?php if (!in_array($cat, ['General', 'Academics', 'Events', 'Placements', 'Examinations', 'Sports', 'Facilities', 'Other'])): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo ($notice['category'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="importance">Importance Level</label>
                                    <select id="importance" name="importance" required>
                                        <option value="low" <?php echo ($notice['importance'] === 'low') ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo ($notice['importance'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo ($notice['importance'] === 'high') ? 'selected' : ''; ?>>High</option>
                                    </select>
                                    <p class="help-text">Select the importance level of this notice.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Notice Content</label>
                            <textarea id="content" name="content" required><?php echo $notice['content']; ?></textarea>
                            <p class="help-text">Provide detailed information about the notice.</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Attachment</label>
                            
                            <?php if (!empty($notice['attachment'])): ?>
                                <div class="current-attachment">
                                    <span>Current attachment:</span>
                                    <a href="<?php echo $notice['attachment']; ?>" class="attachment-link" target="_blank">
                                        <span>📎</span> View Attachment
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="custom-file-input">
                                <input type="file" id="attachment" name="attachment">
                                <label for="attachment"><?php echo !empty($notice['attachment']) ? 'Replace attachment' : 'Add attachment'; ?></label>
                            </div>
                            <p class="help-text">Upload a file (maximum 5MB). Accepted formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF.</p>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <a href="dashboard.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit">Update Notice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../images/srm_logo.png" alt="SRM University AP Logo" class="small-logo">
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
        // Show file name when file is selected
        document.getElementById('attachment').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose a file';
            document.querySelector('.custom-file-input label').textContent = fileName;
        });
    </script>
</body>
</html>