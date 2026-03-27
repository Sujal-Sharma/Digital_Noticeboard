<?php
// Include the configuration file
require_once '../includes/config.php';

// Check if user is admin
check_admin();

// Get logged in user data
$user = get_user_data();

// Initialize variables
$title = '';
$content = '';
$category = '';
$importance = 'medium';
$error = '';
$success = '';

// Get categories for dropdown
$categories = get_categories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $title = sanitize_input($_POST['title'] ?? '');
    $content = sanitize_input($_POST['content'] ?? '');
    $importance = sanitize_input($_POST['importance'] ?? '');
    $created_by = $user['id'];

    // Handle category
    $category = '';
    if (isset($_POST['category'])) {
        $category = sanitize_input($_POST['category']);
        // If "other" is selected and other_category is provided, use that instead
        if ($category === 'other') {
            if (isset($_POST['other_category']) && !empty($_POST['other_category'])) {
                $category = sanitize_input($_POST['other_category']);
            }
        }
    }

    // Validate inputs
    if (empty($title) || empty($content) || empty($importance)) {
        $error = 'All fields are required.';
    } else if ($category === 'other' || empty($category)) {
        $error = 'Please select a category or specify a custom one.';
    } else {
        // Process attachment if provided
        $attachment = null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['size'] > 0) {
            $upload_result = upload_file($_FILES['attachment'], '../uploads/attachments/');

            if ($upload_result['success']) {
                $attachment = $upload_result['file_path'];
            } else {
                $error = $upload_result['message'];
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO notices (title, content, category, importance, created_by, attachment) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $content, $category, $importance, $created_by, $attachment])) {
                $success = 'Notice created successfully.';
                $title = $content = $category = '';
                $importance = 'medium';
            } else {
                $error = 'Error creating notice. Please try again.';
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
    <title>Create Notice - SRMAP Noticeboard</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/notice.css">
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
                <!-- User Profile -->
                <div class="user-profile">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?php echo $user['profile_image']; ?>" alt="Profile" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar"><?php echo substr($user['full_name'], 0, 1); ?></div>
                    <?php endif; ?>
                    <div class="user-info">
                        <h3><?php echo $user['full_name']; ?></h3>
                        <p><?php echo ucfirst($user['role']); ?></p>
                    </div>
                </div>

                <!-- Admin Navigation -->
                <div class="admin-navigation">
                    <div class="sidebar-section-label">Admin Panel</div>
                    <ul>
                        <li><a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
                        <li><a href="notices.php"><span class="nav-icon">📋</span> Manage Notices</a></li>
                        <li><a href="create_notice.php" class="active"><span class="nav-icon">✏️</span> Create Notice</a></li>
                        <li><a href="users.php"><span class="nav-icon">👥</span> Manage Users</a></li>
                        <li><a href="profile.php"><span class="nav-icon">⚙️</span> Profile Settings</a></li>
                    </ul>
                </div>
                <div class="sidebar-footer">
                    <a href="../includes/logout.php"><span class="nav-icon">🚪</span> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <h1 style="font-size:1.6rem; font-weight:800; color:#1a1a2e; letter-spacing:-0.02em; margin-bottom:1.5rem;">Create New Notice</h1>

                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a>
                    <span>></span>
                    <span>Create Notice</span>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="notice-form">
                    <h2>Create New Notice</h2>

                    <form action="create_notice.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label" for="title">Notice Title</label>
                            <input class="form-control" type="text" id="title" name="title" value="<?php echo $title; ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="category">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                        <?php endforeach; ?>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="importance">Importance Level</label>
                                    <select class="form-select" id="importance" name="importance" required>
                                        <option value="low" <?php echo ($importance === 'low') ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo ($importance === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo ($importance === 'high') ? 'selected' : ''; ?>>High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="other-category-group" class="form-group" style="display: none;">
                            <label class="form-label" for="other-category">Specify Category</label>
                            <input class="form-control" type="text" id="other-category" name="other_category">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="content">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo $content; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="attachment">Attachment (Optional)</label>
                            <input class="form-control" type="file" id="attachment" name="attachment">
                            <small>Max size: 5MB. Allowed types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, TXT</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-notice btn-edit">Create Notice</button>
                            <a href="dashboard.php" class="btn btn-notice btn-back">Cancel</a>
                        </div>
                    </form>
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
        // Show/hide custom category field
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            const otherCategoryGroup = document.getElementById('other-category-group');
            const otherCategoryInput = document.getElementById('other-category');

            // Initial check
            if (categorySelect.value === 'other') {
                otherCategoryGroup.style.display = 'block';
                otherCategoryInput.setAttribute('required', 'required');
            } else {
                otherCategoryGroup.style.display = 'none';
                otherCategoryInput.removeAttribute('required');
            }

            // Change event
            categorySelect.addEventListener('change', function() {
                if (this.value === 'other') {
                    otherCategoryGroup.style.display = 'block';
                    otherCategoryInput.setAttribute('required', 'required');
                } else {
                    otherCategoryGroup.style.display = 'none';
                    otherCategoryInput.removeAttribute('required');
                }
            });

            // Form submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (categorySelect.value === 'other' && otherCategoryInput.value.trim() !== '') {
                    // Override category value with custom input
                    categorySelect.value = otherCategoryInput.value.trim();
                }
            });
        });
    </script>
</body>
</html>