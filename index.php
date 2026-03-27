<?php
// Include the configuration file
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRMAP Noticeboard - Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header id="main-header">
        <div class="logo-container">
            <img src="images/logo.png" alt="SRMAP Logo" class="logo">
        </div>
        <nav id="main-nav">
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="student/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="includes/logout.php" class="btn btn-small" style="color:#fff;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="login-btn">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
    </header>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero-section">
        <canvas id="particles-js"></canvas>
        <!-- Radial gradient overlay for depth -->
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                University Notification System &mdash; SRM AP
            </div>
            <h1 class="hero-title">
                Stay Informed,<br>
                <span class="hero-title-gradient">Stay Ahead</span>
            </h1>
            <p class="hero-subtitle">
                The official digital noticeboard for SRM University AP. Access all university
                announcements, academic updates, and important notices in one place &mdash; anytime, anywhere.
            </p>
            <div class="hero-buttons">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-hero">Get Started</a>
                    <a href="signup.php" class="btn btn-outline btn-hero-outline">Create Account</a>
                <?php else: ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-hero">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="student/dashboard.php" class="btn btn-hero">View My Notices</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="hero-scroll-hint">
                <span>Scroll to explore</span>
                <div class="scroll-chevron">&#8595;</div>
            </div>
        </div>
    </section>

    <!-- ===== STATS BAR ===== -->
    <section class="stats-bar">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">150<span class="stat-plus">+</span></div>
                    <div class="stat-label">Active Notices</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">500<span class="stat-plus">+</span></div>
                    <div class="stat-label">Students Enrolled</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">20<span class="stat-plus">+</span></div>
                    <div class="stat-label">Departments</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">98<span class="stat-plus">%</span></div>
                    <div class="stat-label">Uptime Reliability</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES SECTION ===== -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Why Choose Us</span>
                <h2 class="section-title">Everything You Need in One Place</h2>
                <p class="section-subtitle">A comprehensive platform designed for the modern university experience</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-wrap">
                        <div class="feature-icon">&#128276;</div>
                    </div>
                    <h3>Real-Time Alerts</h3>
                    <p>Receive instant notifications for high-priority announcements and urgent university updates the moment they go live.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrap">
                        <div class="feature-icon">&#128269;</div>
                    </div>
                    <h3>Smart Search</h3>
                    <p>Powerful search and category filtering to quickly find the notices that matter to you, with keyword highlighting.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrap">
                        <div class="feature-icon">&#128100;</div>
                    </div>
                    <h3>Role-Based Access</h3>
                    <p>Separate dashboards for administrators and students with tailored views, permissions, and management tools.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrap">
                        <div class="feature-icon">&#128203;</div>
                    </div>
                    <h3>Notice Management</h3>
                    <p>Admins can create, edit, and archive notices with rich formatting, importance levels, and file attachments.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrap">
                        <div class="feature-icon">&#128241;</div>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Fully responsive design that works flawlessly on any device &mdash; desktop, tablet, or smartphone.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrap">
                        <div class="feature-icon">&#128274;</div>
                    </div>
                    <h3>Secure Platform</h3>
                    <p>Enterprise-grade authentication, encrypted sessions, and role-based security controls to protect your data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="how-it-works-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Simple &amp; Fast</span>
                <h2 class="section-title">How It Works</h2>
                <p class="section-subtitle">Get started in minutes with our straightforward three-step process</p>
            </div>
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <div class="step-icon-wrap">
                        <div class="step-icon">&#128272;</div>
                    </div>
                    <h3>Create Your Account</h3>
                    <p>Sign up with your university credentials to unlock full access to all notices and features.</p>
                </div>
                <div class="step-connector"><span class="connector-arrow">&#8594;</span></div>
                <div class="step-card">
                    <div class="step-number">02</div>
                    <div class="step-icon-wrap">
                        <div class="step-icon">&#128196;</div>
                    </div>
                    <h3>Browse Notices</h3>
                    <p>Explore categorized notices with filters by department, importance level, and date range.</p>
                </div>
                <div class="step-connector"><span class="connector-arrow">&#8594;</span></div>
                <div class="step-card">
                    <div class="step-number">03</div>
                    <div class="step-icon-wrap">
                        <div class="step-icon">&#9989;</div>
                    </div>
                    <h3>Stay Updated</h3>
                    <p>Never miss an important deadline or event. Track and bookmark announcements effortlessly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA SECTION ===== -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-glow"></div>
                <div class="cta-content">
                    <h2>Ready to Stay Informed?</h2>
                    <p>Join hundreds of students already using SRMAP Noticeboard for a smarter university experience.</p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="hero-buttons" style="justify-content:center; margin-top:2rem;">
                            <a href="signup.php" class="btn btn-cta">Create Free Account</a>
                            <a href="login.php" class="btn btn-outline btn-cta-outline">Sign In</a>
                        </div>
                    <?php else: ?>
                        <div class="hero-buttons" style="justify-content:center; margin-top:2rem;">
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <a href="admin/dashboard.php" class="btn btn-cta">Go to Dashboard</a>
                            <?php else: ?>
                                <a href="student/dashboard.php" class="btn btn-cta">View My Notices</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/logo.png" alt="SRMAP Logo" class="small-logo">
                <p style="color: rgba(255,255,255,0.55); font-size: 0.88rem; margin-top: 0.8rem; max-width: 220px; line-height: 1.7;">
                    SRM University AP's official digital noticeboard &mdash; keeping campus connected.
                </p>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact</h3>
                <p>&#128205; Neerukonda, Mangalagiri</p>
                <p>Andhra Pradesh &mdash; 522 240</p>
                <p style="margin-top:0.6rem;">&#128231; info@srmap.edu.in</p>
                <p>&#128222; +91 863 230 2222</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> SRM University AP. All rights reserved. &nbsp;&bull;&nbsp; SRMAP Noticeboard</p>
        </div>
    </footer>

    <!-- ===== SCRIPTS ===== -->
    <script src="js/particles.js"></script>
    <script>
        // Header glassmorphism on scroll
        const header = document.getElementById('main-header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 60);
        });

        // Mobile nav toggle
        const navToggle = document.getElementById('nav-toggle');
        const mainNav = document.getElementById('main-nav');
        if (navToggle) {
            navToggle.addEventListener('click', () => {
                mainNav.classList.toggle('nav-open');
                navToggle.classList.toggle('active');
            });
        }

        // Scroll-reveal for cards
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    entry.target.style.transitionDelay = (entry.target.dataset.delay || 0) + 'ms';
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.feature-card').forEach((el, i) => {
            el.dataset.delay = i * 80;
            revealObserver.observe(el);
        });
        document.querySelectorAll('.step-card, .stat-item, .cta-card').forEach(el => {
            revealObserver.observe(el);
        });
    </script>
</body>
</html>
