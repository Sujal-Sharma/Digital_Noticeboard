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
$stmt = $conn->prepare("SELECT n.*, u.full_name as author_name FROM notices n JOIN users u ON n.created_by = u.id WHERE n.id = ?");
$stmt->execute([$notice_id]);
$notice = $stmt->fetch();

if (!$notice) {
    redirect('dashboard.php');
}

// Get comments
$comment_stmt = $conn->prepare("SELECT c.*, u.full_name as author_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.notice_id = ? ORDER BY c.created_at DESC");
$comment_stmt->execute([$notice_id]);
$comments = $comment_stmt->fetchAll();

// Get view count
$view_stmt = $conn->prepare("SELECT COUNT(*) FROM notice_views WHERE notice_id = ?");
$view_stmt->execute([$notice_id]);
$view_count = (int)$view_stmt->fetchColumn();

// Record view if not already viewed
$user_id = $user['id'];
$check_stmt = $conn->prepare("SELECT id FROM notice_views WHERE notice_id = ? AND user_id = ?");
$check_stmt->execute([$notice_id, $user_id]);
if (!$check_stmt->fetch()) {
    $conn->prepare("INSERT INTO notice_views (notice_id, user_id) VALUES (?, ?)")->execute([$notice_id, $user_id]);
    $view_count++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notice - SRM University AP Notice Board</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/notice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Logo styling */
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo {
            height: 50px;
            width: auto;
            object-fit: contain;
        }
        
        /* Main page styling */
        .view-notice-page {
            background-color: #f9fafc;
            min-height: calc(100vh - 75px);
        }
        
        .notice-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #102542;
            margin: 0;
        }
        
        .notice-paper {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .notice-header-area {
            padding: 2rem;
            border-bottom: 1px solid #eaecf0;
        }
        
        .notice-title-area {
            margin-bottom: 1.5rem;
        }
        
        .notice-title-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: #102542;
            margin: 0 0 1rem 0;
            line-height: 1.3;
        }
        
        .notice-metadata {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            color: #5a6475;
            font-size: 0.9rem;
        }
        
        .metadata-divider {
            color: #d0d5dd;
        }
        
        .notice-author-name {
            color: #4e8aff;
            font-weight: 600;
        }
        
        .notice-category {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            background-color: #eaecf0;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .notice-priority {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .priority-high {
            background-color: #feedeb;
            color: #b42318;
        }
        
        .priority-medium {
            background-color: #fff4ea;
            color: #b93815;
        }
        
        .priority-low {
            background-color: #ecfdf3;
            color: #027a48;
        }
        
        .priority-icon {
            margin-right: 0.3rem;
            font-size: 0.7rem;
        }
        
        .notice-body {
            padding: 2rem;
            line-height: 1.7;
            color: #1d2939;
        }
        
        .notice-content p {
            margin-bottom: 1.2rem;
        }
        
        .attachments-area {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eaecf0;
        }
        
        .attachments-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #102542;
            margin-bottom: 1rem;
        }
        
        .attachment-tile {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            background-color: #f9fafc;
            border: 1px solid #eaecf0;
            border-radius: 8px;
            margin-bottom: 0.8rem;
            transition: all 0.2s ease;
        }
        
        .attachment-tile:hover {
            background-color: #f0f2f5;
            border-color: #d0d5dd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .attachment-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #eaecf0;
            border-radius: 6px;
            margin-right: 0.8rem;
            color: #4e8aff;
        }
        
        .attachment-details {
            flex: 1;
        }
        
        .attachment-filename {
            font-weight: 500;
            color: #1d2939;
            margin-bottom: 0.2rem;
        }
        
        .attachment-size {
            font-size: 0.8rem;
            color: #667085;
        }
        
        .attachment-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .attachment-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-view {
            background-color: #f0f2f5;
            color: #1d2939;
        }
        
        .btn-download {
            background-color: #4e8aff;
            color: white;
        }
        
        .notice-stats-area {
            display: flex;
            border-top: 1px solid #eaecf0;
        }
        
        .stat-block {
            flex: 1;
            text-align: center;
            padding: 1.2rem;
            border-right: 1px solid #eaecf0;
        }
        
        .stat-block:last-child {
            border-right: none;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #102542;
            margin-bottom: 0.3rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #667085;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .action-btn {
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-back {
            background-color: #f0f2f5;
            color: #1d2939;
        }
        
        .btn-edit {
            background-color: #4e8aff;
            color: white;
        }
        
        .btn-delete {
            background-color: #d92d20;
            color: white;
        }
        
        .comments-area {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            padding: 2rem;
        }
        
        .comments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eaecf0;
        }
        
        .comments-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #102542;
            margin: 0;
        }
        
        .comment-item {
            display: flex;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eaecf0;
        }
        
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #4e8aff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .comment-body {
            flex: 1;
        }
        
        .comment-header {
            margin-bottom: 0.5rem;
        }
        
        .comment-author {
            font-weight: 600;
            color: #102542;
            margin-right: 0.5rem;
        }
        
        .comment-date {
            font-size: 0.85rem;
            color: #667085;
        }
        
        .comment-text {
            color: #1d2939;
            line-height: 1.5;
        }
        
        .no-comments-message {
            text-align: center;
            padding: 2rem 0;
            color: #667085;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .notice-stats-area {
                flex-wrap: wrap;
            }
            
            .stat-block {
                min-width: 50%;
                border-bottom: 1px solid #eaecf0;
                border-right: none;
            }
            
            .stat-block:nth-child(odd) {
                border-right: 1px solid #eaecf0;
            }
            
            .stat-block:nth-last-child(-n+2) {
                border-bottom: none;
            }
            
            .action-bar {
                flex-wrap: wrap;
                gap: 0.8rem;
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

    <div class="view-notice-page">
        <div class="notice-container">
            <div class="action-bar">
                <a href="notices.php" class="action-btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Notices
                </a>
                
                <div class="right-actions">
                    <a href="edit_notice.php?id=<?php echo $notice_id; ?>" class="action-btn btn-edit">
                        <i class="fas fa-edit"></i> Edit Notice
                    </a>
                    <a href="notices.php?delete=<?php echo $notice_id; ?>&confirm=1" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this notice?')">
                        <i class="fas fa-trash-alt"></i> Delete
                    </a>
                </div>
            </div>
            
            <div class="notice-paper">
                <div class="notice-header-area">
                    <div class="notice-title-area">
                        <h1 class="notice-title-text"><?php echo $notice['title']; ?></h1>
                        <div class="notice-metadata">
                            <span>Posted by <span class="notice-author-name"><?php echo $notice['author_name']; ?></span></span>
                            <span class="metadata-divider">•</span>
                            <span><?php echo format_datetime($notice['created_at']); ?></span>
                            <span class="metadata-divider">•</span>
                            <span class="notice-category"><?php echo $notice['category']; ?></span>
                            
                            <?php 
                            $priority_class = 'priority-' . $notice['importance'];
                            $priority_icon = '';
                            if ($notice['importance'] == 'high') {
                                $priority_icon = '<i class="fas fa-exclamation-circle priority-icon"></i>';
                            } elseif ($notice['importance'] == 'medium') {
                                $priority_icon = '<i class="fas fa-exclamation priority-icon"></i>';
                            } else {
                                $priority_icon = '<i class="fas fa-info-circle priority-icon"></i>';
                            }
                            ?>
                            <span class="notice-priority <?php echo $priority_class; ?>">
                                <?php echo $priority_icon; ?> <?php echo ucfirst($notice['importance']); ?> Priority
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="notice-body">
                    <div class="notice-content">
                        <?php echo isset($notice['content']) ? nl2br($notice['content']) : ''; ?>
                    </div>
                    
                    <?php if (!empty($notice['attachments'])): ?>
                        <div class="attachments-area">
                            <h3 class="attachments-title">Attachments</h3>
                            <div class="attachments-list">
                                <?php 
                                $attachments = explode(',', $notice['attachments']);
                                foreach ($attachments as $attachment): 
                                    $file_path = '../uploads/' . trim($attachment);
                                    $file_name = basename($file_path);
                                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                                    $file_size = file_exists($file_path) ? get_file_size($file_path) : 'Unknown';
                                    
                                    $icon_class = 'fas fa-file';
                                    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $icon_class = 'fas fa-image';
                                    } elseif (in_array($file_ext, ['pdf'])) {
                                        $icon_class = 'fas fa-file-pdf';
                                    } elseif (in_array($file_ext, ['doc', 'docx'])) {
                                        $icon_class = 'fas fa-file-word';
                                    } elseif (in_array($file_ext, ['xls', 'xlsx'])) {
                                        $icon_class = 'fas fa-file-excel';
                                    } elseif (in_array($file_ext, ['ppt', 'pptx'])) {
                                        $icon_class = 'fas fa-file-powerpoint';
                                    }
                                ?>
                                    <div class="attachment-tile">
                                        <div class="attachment-icon">
                                            <i class="<?php echo $icon_class; ?>"></i>
                                        </div>
                                        <div class="attachment-details">
                                            <div class="attachment-filename"><?php echo $file_name; ?></div>
                                            <div class="attachment-size"><?php echo $file_size; ?></div>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="<?php echo $file_path; ?>" target="_blank" class="attachment-btn btn-view">View</a>
                                            <a href="<?php echo $file_path; ?>" download class="attachment-btn btn-download">Download</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="notice-stats-area">
                    <div class="stat-block">
                        <div class="stat-value"><?php echo $view_count; ?></div>
                        <div class="stat-label">Views</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-value"><?php echo count($comments); ?></div>
                        <div class="stat-label">Comments</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-value"><?php echo isset($notice['visibility']) ? ucfirst($notice['visibility']) : 'All'; ?></div>
                        <div class="stat-label">Visibility</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-value">
                            <i class="fas fa-calendar-alt" style="font-size: 1.2rem; color: #4e8aff;"></i>
                        </div>
                        <div class="stat-label"><?php echo date('M d, Y', strtotime($notice['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="comments-area">
                <div class="comments-header">
                    <h3 class="comments-title">Comments (<?php echo count($comments); ?>)</h3>
                </div>
                
                <?php if (empty($comments)): ?>
                    <div class="no-comments-message">No comments yet.</div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <?php if (!empty($comment['profile_image'])): ?>
                                <img src="<?php echo $comment['profile_image']; ?>" alt="User" class="comment-avatar">
                            <?php else: ?>
                                <div class="comment-avatar"><?php echo substr($comment['author_name'], 0, 1); ?></div>
                            <?php endif; ?>
                            
                            <div class="comment-body">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo $comment['author_name']; ?></span>
                                    <span class="comment-date"><?php echo format_datetime($comment['created_at']); ?></span>
                                </div>
                                <div class="comment-text"><?php echo isset($comment['comment']) ? nl2br($comment['comment']) : ''; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>