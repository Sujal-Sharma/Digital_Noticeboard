<?php
require_once 'includes/config.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize_input($_POST['name']);
    $email   = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        $success_message = "Thank you for your message! We will get back to you shortly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - SRM University AP Notice Board</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0d0d1a; color: #e8e5d8; }

        /* Page Banner */
        .page-banner {
            position: relative;
            padding: 6rem 1.5rem 4rem;
            text-align: center;
            overflow: hidden;
        }

        .page-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 80% at 50% 0%, rgba(245,158,11,0.14) 0%, transparent 70%);
            pointer-events: none;
        }

        .page-banner-content {
            position: relative;
            z-index: 1;
            max-width: 700px;
            margin: 0 auto;
        }

        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(245,158,11,0.10);
            border: 1px solid rgba(245,158,11,0.25);
            color: #fcd34d;
            padding: 0.35rem 1rem;
            border-radius: 50px;
            font-size: 0.80rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .page-banner h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-family: 'DM Serif Display', serif;
            font-weight: 400;
            color: #ffffff;
            letter-spacing: -0.01em;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .page-banner h1 span {
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-banner p {
            color: rgba(255,255,255,0.6);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        /* Contact Layout */
        .contact-section {
            padding: 4rem 1.5rem 5rem;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 2.5rem;
            align-items: start;
        }

        @media (max-width: 768px) {
            .contact-grid { grid-template-columns: 1fr; }
        }

        /* Form Card */
        .form-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
        }

        .form-card h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.03em;
            margin-bottom: 1.75rem;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 10px;
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
            color: rgba(255,255,255,0.65);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255,255,255,0.25);
        }

        .form-group select option {
            background: #1a2035;
            color: #ffffff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: rgba(245,158,11,0.55);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 500px) {
            .form-row { grid-template-columns: 1fr; }
        }

        .btn-submit {
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
        }

        .btn-submit:hover {
            opacity: 0.92;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245,158,11,0.50);
            background: linear-gradient(135deg, #fcd34d, #f59e0b);
        }

        /* Info Sidebar */
        .info-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .info-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 1.5rem;
            transition: border-color 0.3s;
        }

        .info-card:hover {
            border-color: rgba(245,158,11,0.20);
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .info-icon-wrap {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(245,158,11,0.10);
            border: 1px solid rgba(245,158,11,0.20);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .info-text strong {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.25rem;
        }

        .info-text p {
            color: rgba(255,255,255,0.75);
            font-size: 0.9rem;
            line-height: 1.55;
            margin: 0;
        }

        .map-container {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.07);
            height: 220px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            filter: grayscale(30%) invert(90%) hue-rotate(180deg);
        }

        .hours-grid {
            display: grid;
            gap: 0.5rem;
            margin-top: 0;
        }

        .hours-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.55);
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }

        .hours-row:last-child { border-bottom: none; }
        .hours-row .day { font-weight: 500; color: rgba(255,255,255,0.7); }
        .hours-row .closed { color: rgba(239,68,68,0.7); }
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
                <li><a href="contact.php" class="active">Contact</a></li>
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <li><a href="admin/dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="student/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="includes/logout.php" class="login-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="login-btn">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Banner -->
    <div class="page-banner">
        <div class="page-banner-content">
            <div class="page-badge">✦ Contact Us</div>
            <h1>We'd love to <span>hear from you</span></h1>
            <p>Have questions, feedback, or need support? Reach out to us through the form or any of the channels below.</p>
        </div>
    </div>

    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Form -->
                <div class="form-card">
                    <h2>Send Us a Message</h2>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" id="name" name="name"
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       placeholder="Full name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       placeholder="you@srmap.edu.in" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" required>
                                <option value="" disabled <?php echo !isset($_POST['subject']) ? 'selected' : ''; ?>>Select a subject</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Technical Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Report Issue" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Report Issue') ? 'selected' : ''; ?>>Report Issue</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" placeholder="Write your message here..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Send Message →</button>
                    </form>
                </div>

                <!-- Info Sidebar -->
                <div class="info-sidebar">
                    <div class="info-card">
                        <div class="info-item">
                            <div class="info-icon-wrap">📍</div>
                            <div class="info-text">
                                <strong>Address</strong>
                                <p>SRM University AP<br>Amaravati, Andhra Pradesh 522502</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-item">
                            <div class="info-icon-wrap">📱</div>
                            <div class="info-text">
                                <strong>Phone</strong>
                                <p>+91-1234567890</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-item">
                            <div class="info-icon-wrap">✉️</div>
                            <div class="info-text">
                                <strong>Email</strong>
                                <p>info@srmap.edu.in</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon-wrap" style="margin-bottom:1rem;">🕒</div>
                        <strong style="font-size:0.82rem; font-weight:600; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.75rem;">Office Hours</strong>
                        <div class="hours-grid">
                            <div class="hours-row"><span class="day">Monday – Friday</span><span>9:00 AM – 5:00 PM</span></div>
                            <div class="hours-row"><span class="day">Saturday</span><span>9:00 AM – 1:00 PM</span></div>
                            <div class="hours-row"><span class="day">Sunday</span><span class="closed">Closed</span></div>
                        </div>
                    </div>

                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3827.8300010669967!2d80.48519401418823!3d16.3841305356631!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a35fabf32fea9e3%3A0xc8a70f7c1ac8edc8!2sSRM%20University%2C%20Andhra%20Pradesh!5e0!3m2!1sen!2sin!4v1611494943130!5m2!1sen!2sin" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
