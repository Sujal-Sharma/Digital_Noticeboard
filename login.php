<?php
// Include the configuration file
require_once 'includes/config.php';

$debug_mode = false; // Debug disabled in production

// Initialize variables
$username = '';
$error = '';
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['role']      = $user['role'];

                $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)");
                $log_stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);

                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('student/dashboard.php');
                }
            } else {
                $error = 'Invalid password. Please try again.';
            }
        } else {
            $error = 'User not found. Please check your username.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SRMAP Noticeboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #0d0d1a;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(245,158,11,0.10) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(20,184,166,0.08) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        header {
            position: relative;
            z-index: 10;
            background: rgba(13,13,26,0.88);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(245,158,11,0.14);
        }

        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.06);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-logo img {
            height: 52px;
            object-fit: contain;
        }

        .auth-logo .logo-fallback {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -0.02em;
        }

        .auth-logo .logo-fallback span {
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-title {
            font-size: 1.7rem;
            font-family: 'DM Serif Display', serif;
            font-weight: 400;
            color: #ffffff;
            text-align: center;
            margin-bottom: 0.4rem;
            letter-spacing: -0.01em;
        }

        .auth-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.42);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.3);
            color: #86efac;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.68);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        .form-group input::placeholder {
            color: rgba(255,255,255,0.22);
        }

        .form-group input:focus {
            border-color: rgba(245,158,11,0.55);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.35);
            font-size: 1rem;
            pointer-events: none;
        }

        .input-wrapper input {
            padding-left: 2.8rem;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.35);
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: rgba(255,255,255,0.7);
        }

        .btn-auth {
            width: 100%;
            padding: 0.88rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #1a1a2e;
            border: none;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            margin-top: 0.5rem;
            box-shadow: 0 4px 20px rgba(245,158,11,0.35);
            letter-spacing: 0.01em;
        }

        .btn-auth:hover {
            opacity: 0.92;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245,158,11,0.50);
            background: linear-gradient(135deg, #fcd34d, #f59e0b);
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.42);
            font-size: 0.875rem;
        }

        .auth-footer a {
            color: #f59e0b;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .auth-footer a:hover {
            color: #fcd34d;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.08);
        }

        .divider span {
            color: rgba(255,255,255,0.3);
            font-size: 0.8rem;
            white-space: nowrap;
        }

        footer {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="images/logo.png" alt="SRMAP Logo" class="logo" onerror="this.style.display='none'">
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php" class="active login-btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="images/logo.png" alt="SRMAP Logo" onerror="this.style.display='none'">
                <div class="logo-fallback" style="margin-top: 0.25rem;">
                    <span>SRMAP</span> Noticeboard
                </div>
            </div>

            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Sign in to your account to continue</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" id="username" name="username"
                               value="<?php echo htmlspecialchars($username); ?>"
                               placeholder="Enter your username" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Sign In</button>
            </form>

            <div class="auth-footer" style="margin-top: 1.2rem;">
                Don't have an account? <a href="signup.php">Create one</a>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.07);">
                <div style="display: flex; align-items: center; gap: 0.6rem; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.20); border-radius: 12px; padding: 0.85rem 1rem;">
                    <span style="font-size: 1.1rem;">🔐</span>
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 600; color: rgba(252,211,77,0.90); margin-bottom: 0.15rem;">Admin Access</div>
                        <div style="font-size: 0.78rem; color: rgba(255,255,255,0.38); line-height: 1.4;">Admins use the same login form. Contact your system administrator for admin credentials.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <p>SRM University AP</p>
                </div>
                <p>A digital platform for all university notices and announcements.</p>
            </div>
            <div class="footer-section footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
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
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.querySelector('.toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
