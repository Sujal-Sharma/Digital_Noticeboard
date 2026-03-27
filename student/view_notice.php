<?php
// Include the configuration file
require_once '../includes/config.php';

// Check if user is logged in
check_login();

// Get the notice ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('dashboard.php');
}

$notice_id = (int)$_GET['id'];

// Get the notice
$stmt = $conn->prepare("SELECT n.*, u.username, u.full_name FROM notices n JOIN users u ON n.created_by = u.id WHERE n.id = ?");
$stmt->execute([$notice_id]);
$notice = $stmt->fetch();

if (!$notice) {
    redirect('dashboard.php');
}

// Get comments for this notice
$stmt = $conn->prepare("SELECT c.*, u.username, u.full_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.notice_id = ? ORDER BY c.created_at ASC");
$stmt->execute([$notice_id]);
$comments = $stmt->fetchAll();

// Get logged in user data
$user = get_user_data();

// Handle comment submission
$comment_error = '';
$comment_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment_content = sanitize_input($_POST['comment']);
    
    if (empty($comment_content)) {
        $comment_error = 'Comment cannot be empty.';
    } else {
        // Insert comment
        $stmt = $conn->prepare("INSERT INTO comments (notice_id, user_id, comment) VALUES (?, ?, ?)");
        if ($stmt->execute([$notice_id, $user['id'], $comment_content])) {
            $comment_success = 'Comment added successfully.';
            // Redirect to avoid form resubmission
            redirect("view_notice.php?id=$notice_id&success=comment_added");
        } else {
            $comment_error = 'Error adding comment. Please try again.';
        }
    }
}

// Check for success message in URL
if (isset($_GET['success']) && $_GET['success'] === 'comment_added') {
    $comment_success = 'Comment added successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $notice['title']; ?> - SRMAP Noticeboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Notice Content -->
    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>></span>
            <span>Notice Details</span>
        </div>
        
        <div class="notice-container fade-in">
            <div class="notice-header">
                <div>
                    <h1 class="notice-title"><?php echo $notice['title']; ?></h1>
                    <div>
                        <span class="importance-badge importance-<?php echo $notice['importance']; ?>"><?php echo $notice['importance']; ?></span>
                        <span class="category-badge"><?php echo $notice['category']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="notice-meta">
                <span><strong>Posted by:</strong> <?php echo $notice['full_name']; ?></span>
                <span><strong>Date:</strong> <?php echo format_date($notice['created_at']); ?></span>
                <?php if ($notice['created_at'] !== $notice['updated_at']): ?>
                    <span><strong>Updated:</strong> <?php echo format_date($notice['updated_at']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="notice-content">
                <?php echo nl2br($notice['content']); ?>
            </div>
            
            <?php if (!empty($notice['attachment'])): ?>
                <div class="attachments">
                    <h3>Attachment</h3>
                    <a href="<?php echo $notice['attachment']; ?>" class="btn" download>Download Attachment</a>
                </div>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
        
        <!-- Comments Section -->
        <div class="comments-section">
            <h2>Comments</h2>
            
            <!-- Comment Form -->
            <div class="comment-container">
                <?php if (!empty($comment_error)): ?>
                    <div class="alert alert-error"><?php echo $comment_error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($comment_success)): ?>
                    <div class="alert alert-success"><?php echo $comment_success; ?></div>
                <?php endif; ?>
                
                <form action="view_notice.php?id=<?php echo $notice_id; ?>" method="POST" class="comment-form">
                    <div class="form-group">
                        <label for="comment" class="form-label">Add a Comment</label>
                        <textarea id="comment" name="comment" rows="3" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-notice btn-edit">Post Comment</button>
                </form>
                
                <!-- Comments List -->
                <?php if (empty($comments)): ?>
                    <div class="no-comments">
                        <p>No comments yet. Be the first to comment!</p>
                    </div>
                <?php else: ?>
                    <h3>All Comments (<?php echo count($comments); ?>)</h3>
                    
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <?php if (!empty($comment['profile_image'])): ?>
                                    <img src="<?php echo $comment['profile_image']; ?>" alt="Profile" class="comment-avatar">
                                <?php else: ?>
                                    <div class="comment-avatar"><?php echo substr($comment['full_name'], 0, 1); ?></div>
                                <?php endif; ?>
                                
                                <div class="comment-info">
                                    <h4 class="comment-author"><?php echo $comment['full_name']; ?></h4>
                                    <p class="comment-date"><?php echo format_date($comment['created_at']); ?></p>
                                </div>
                            </div>
                            
                            <div class="comment-content">
                                <?php echo nl2br($comment['comment']); ?>
                            </div>
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