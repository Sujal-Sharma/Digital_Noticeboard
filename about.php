<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SRM University AP Notice Board</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #0d0d1a; color: #e8e5d8; }

        /* ── Page Banner ─────────────────────────── */
        .page-banner {
            position: relative;
            padding: 6rem 1.5rem 4rem;
            text-align: center;
            overflow: hidden;
            background: linear-gradient(160deg, #0d0d1a 0%, #111128 50%, #0a1628 100%);
        }

        .page-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 70% at 30% 50%, rgba(245,158,11,0.14) 0%, transparent 65%),
                radial-gradient(ellipse 60% 70% at 70% 40%, rgba(20,184,166,0.10) 0%, transparent 65%);
            pointer-events: none;
        }

        .page-banner-content {
            position: relative;
            z-index: 1;
            max-width: 720px;
            margin: 0 auto;
        }

        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.28);
            color: #fcd34d;
            padding: 0.35rem 1rem;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .page-banner h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-family: 'DM Serif Display', serif;
            font-weight: 400;
            color: #fdf8f0;
            letter-spacing: -0.01em;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .page-banner h1 span {
            background: linear-gradient(135deg, #f59e0b, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-banner p {
            color: rgba(196,181,253,0.65);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        /* ── General Sections ────────────────────── */
        .section {
            padding: 5rem 1.5rem;
        }

        .section-alt {
            background: rgba(245,158,11,0.04);
            border-top: 1px solid rgba(245,158,11,0.08);
            border-bottom: 1px solid rgba(245,158,11,0.08);
        }

        .container-ab {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3.5rem;
        }

        .section-tag {
            display: inline-block;
            background: rgba(245,158,11,0.12);
            color: #fcd34d;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            margin-bottom: 1rem;
            border: 1px solid rgba(245,158,11,0.22);
        }

        .section-header h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            color: #f0ebff;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
        }

        .section-header p {
            color: rgba(196,181,253,0.55);
            font-size: 1rem;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ── Vision Section ──────────────────────── */
        .vision-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            .vision-grid { grid-template-columns: 1fr; gap: 2.5rem; }
        }

        .vision-text h2 {
            font-size: 1.9rem;
            font-weight: 800;
            color: #f0ebff;
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
        }

        .vision-text p {
            color: rgba(196,181,253,0.60);
            line-height: 1.8;
            margin-bottom: 1.2rem;
            font-size: 0.95rem;
        }

        .vision-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-box {
            background: rgba(124,58,237,0.10);
            border: 1px solid rgba(245,158,11,0.18);
            border-radius: 14px;
            padding: 1.4rem;
            text-align: center;
            transition: transform 0.3s, border-color 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-3px);
            border-color: rgba(245,158,11,0.38);
        }

        .stat-box .num {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.04em;
        }

        .stat-box .lbl {
            font-size: 0.8rem;
            color: rgba(196,181,253,0.50);
            margin-top: 0.3rem;
            font-weight: 500;
        }

        .vision-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(245,158,11,0.12);
            border-radius: 20px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .vision-card::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(245,158,11,0.12), transparent 70%);
            pointer-events: none;
        }

        .vision-card-icon { font-size: 3rem; margin-bottom: 1.5rem; }

        .vision-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #f0ebff;
            margin-bottom: 0.75rem;
        }

        .vision-card p {
            color: rgba(196,181,253,0.55);
            font-size: 0.92rem;
            line-height: 1.7;
        }

        /* ── Features Grid ───────────────────────── */
        .features-grid-ab {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .feature-card-ab {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(245,158,11,0.10);
            border-radius: 18px;
            padding: 2rem 1.8rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, border-color 0.3s, box-shadow 0.3s;
        }

        .feature-card-ab::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #14b8a6);
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 18px 18px 0 0;
        }

        .feature-card-ab:hover {
            transform: translateY(-7px);
            border-color: rgba(245,158,11,0.28);
            box-shadow: 0 20px 40px rgba(0,0,0,0.35), 0 0 0 1px rgba(245,158,11,0.10);
        }

        .feature-card-ab:hover::after { opacity: 1; }

        .feature-icon-ab {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(245,158,11,0.18), rgba(20,184,166,0.10));
            border: 1px solid rgba(245,158,11,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1.3rem;
            transition: transform 0.3s;
        }

        .feature-card-ab:hover .feature-icon-ab { transform: scale(1.1) rotate(-3deg); }

        .feature-card-ab h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #f0ebff;
            margin-bottom: 0.65rem;
        }

        .feature-card-ab p {
            color: rgba(196,181,253,0.55);
            font-size: 0.875rem;
            line-height: 1.7;
        }

        /* ── Team Grid ───────────────────────────── */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .team-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(245,158,11,0.10);
            border-radius: 18px;
            overflow: hidden;
            transition: transform 0.3s, border-color 0.3s, box-shadow 0.3s;
            text-align: center;
        }

        .team-card:hover {
            transform: translateY(-6px);
            border-color: rgba(245,158,11,0.28);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .team-avatar {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .team-avatar-placeholder {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(245,158,11,0.22), rgba(20,184,166,0.15));
            font-size: 4rem;
        }

        .team-body { padding: 1.5rem; }
        .team-name { font-size: 1.05rem; font-weight: 700; color: #f0ebff; margin-bottom: 0.3rem; }

        .team-role {
            font-size: 0.82rem;
            font-weight: 600;
            background: linear-gradient(135deg, #f59e0b, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
            letter-spacing: 0.03em;
        }

        .team-bio { font-size: 0.85rem; color: rgba(196,181,253,0.50); line-height: 1.65; }

        /* ── CTA ─────────────────────────────────── */
        .cta-section {
            padding: 5rem 1.5rem;
            text-align: center;
        }

        .cta-box {
            max-width: 700px;
            margin: 0 auto;
            background: linear-gradient(135deg, rgba(245,158,11,0.10), rgba(6,182,212,0.08));
            border: 1px solid rgba(245,158,11,0.20);
            border-radius: 28px;
            padding: 4rem 3rem;
            position: relative;
            overflow: hidden;
        }

        .cta-box::before {
            content: '';
            position: absolute;
            top: -80px; left: 50%;
            transform: translateX(-50%);
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(245,158,11,0.12), transparent 70%);
            pointer-events: none;
        }

        .cta-box h2 {
            font-size: 1.9rem;
            font-weight: 800;
            color: #f0ebff;
            letter-spacing: -0.04em;
            margin-bottom: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .cta-box p {
            color: rgba(196,181,253,0.60);
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .btn-primary-ab {
            padding: 0.85rem 2.2rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 6px 24px rgba(245,158,11,0.38);
        }

        .btn-primary-ab:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 32px rgba(245,158,11,0.50);
            color: white;
        }

        .btn-outline-ab {
            padding: 0.85rem 2.2rem;
            background: transparent;
            color: rgba(196,181,253,0.85);
            text-decoration: none;
            border: 1.5px solid rgba(245,158,11,0.28);
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .btn-outline-ab:hover {
            border-color: rgba(245,158,11,0.50);
            background: rgba(245,158,11,0.10);
            color: #e9d5ff;
        }

        /* ── Header override for dark bg ─────────── */
        header {
            background-color: rgba(13, 13, 26, 0.92) !important;
            border-bottom: 1px solid rgba(245,158,11,0.14) !important;
        }

        header nav ul li a { color: rgba(232,229,216,0.72) !important; }
        header nav ul li a:hover { color: #fff !important; background: rgba(245,158,11,0.12) !important; }
        header nav ul li a.active { color: #fcd34d !important; background: rgba(245,158,11,0.14) !important; }

        footer { margin-top: 0; }
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
                <li><a href="about.php" class="active">About</a></li>
                <li><a href="contact.php">Contact</a></li>
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

    <!-- ── Banner ── -->
    <div class="page-banner">
        <div class="page-banner-content">
            <div class="page-badge">✦ About Us</div>
            <h1>Built for the <span>SRMAP</span> Community</h1>
            <p>A modern digital noticeboard that keeps every student, faculty, and administrator connected and informed in real time.</p>
        </div>
    </div>

    <!-- ── Vision ── -->
    <section class="section">
        <div class="container-ab">
            <div class="vision-grid">
                <div class="vision-text">
                    <h2>Our Vision</h2>
                    <p>The SRMAP Notice Board is a state-of-the-art digital communication platform designed to bridge the information gap between administrators, faculty, and students.</p>
                    <p>Our platform serves as a centralized hub for all important announcements, academic updates, and administrative notices — ensuring every member of the university community stays informed.</p>
                    <p>With a user-friendly interface and modern features, we make information access seamless and efficient for the entire campus.</p>
                    <div class="vision-stats">
                        <div class="stat-box"><div class="num">150+</div><div class="lbl">Notices Published</div></div>
                        <div class="stat-box"><div class="num">500+</div><div class="lbl">Active Students</div></div>
                        <div class="stat-box"><div class="num">8+</div><div class="lbl">Categories</div></div>
                        <div class="stat-box"><div class="num">24/7</div><div class="lbl">Availability</div></div>
                    </div>
                </div>
                <div>
                    <div class="vision-card">
                        <div class="vision-card-icon">🏛️</div>
                        <h3>SRM University AP</h3>
                        <p>Located in Andhra Pradesh, India, SRM University AP is a leading institution committed to excellence in education, research, and innovation.</p>
                        <p style="margin-top:1rem; color:rgba(167,139,250,0.40); font-size:0.82rem; font-style:italic;">Neerukonda, Mangalagiri, Andhra Pradesh — 522 240</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Key Features ── -->
    <section class="section section-alt">
        <div class="container-ab">
            <div class="section-header">
                <div class="section-tag">Platform Features</div>
                <h2>Key Features</h2>
                <p>Everything you need to stay informed and engaged with the university community.</p>
            </div>
            <div class="features-grid-ab">
                <div class="feature-card-ab">
                    <div class="feature-icon-ab">📢</div>
                    <h3>Instant Announcements</h3>
                    <p>Receive timely updates on important notices, ensuring you never miss critical information from administration or faculty.</p>
                </div>
                <div class="feature-card-ab">
                    <div class="feature-icon-ab">🔍</div>
                    <h3>Smart Filtering</h3>
                    <p>Filter notices by category, importance level, or date. Find exactly what you're looking for in seconds.</p>
                </div>
                <div class="feature-card-ab">
                    <div class="feature-icon-ab">💬</div>
                    <h3>Interactive Comments</h3>
                    <p>Engage with notices through comments. Ask questions and receive clarifications directly on announcements.</p>
                </div>
                <div class="feature-card-ab">
                    <div class="feature-icon-ab">📎</div>
                    <h3>File Attachments</h3>
                    <p>Download forms, schedules, and documents directly attached to notices for offline reference and study.</p>
                </div>
                <div class="feature-card-ab">
                    <div class="feature-icon-ab">📱</div>
                    <h3>Mobile Responsive</h3>
                    <p>Access the notice board from any device — desktop, tablet, or phone — wherever you are on campus.</p>
                </div>
                <div class="feature-card-ab">
                    <div class="feature-icon-ab">🛡️</div>
                    <h3>Role-Based Access</h3>
                    <p>Separate admin and student views with secure login, ensuring proper permissions and data protection for all.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Team ── -->
    <section class="section">
        <div class="container-ab">
            <div class="section-header">
                <div class="section-tag">Our Team</div>
                <h2>Meet the Team</h2>
                <p>The dedicated individuals who built and maintain the SRMAP Noticeboard platform.</p>
            </div>
            <div class="team-grid">
                <div class="team-card">
                    <img src="images/team-member-1.jpg" alt="Sujal Sharma" class="team-avatar"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="team-avatar-placeholder" style="display:none;">🧑‍💻</div>
                    <div class="team-body">
                        <div class="team-name">Sujal Sharma</div>
                        <div class="team-role">Lead Developer</div>
                        <div class="team-bio">Full-stack developer leading the technical development with creative solutions and cutting-edge approaches.</div>
                    </div>
                </div>
                <div class="team-card">
                    <img src="images/team-member-2.jpg" alt="Anjani Devi" class="team-avatar"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="team-avatar-placeholder" style="display:none;">👩‍🎨</div>
                    <div class="team-body">
                        <div class="team-name">Anjani Devi</div>
                        <div class="team-role">UX / UI Designer</div>
                        <div class="team-bio">Creates intuitive interfaces that make the noticeboard accessible and engaging for all university users.</div>
                    </div>
                </div>
                <div class="team-card">
                    <img src="images/team-member-3.jpg" alt="Yeswanth" class="team-avatar"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="team-avatar-placeholder" style="display:none;">🧑‍💻</div>
                    <div class="team-body">
                        <div class="team-name">Yeswanth</div>
                        <div class="team-role">Backend Developer</div>
                        <div class="team-bio">Specializes in database architecture and server optimization, ensuring smooth performance at peak loads.</div>
                    </div>
                </div>
                <div class="team-card">
                    <img src="images/team-member-40.jpg" alt="Chandana" class="team-avatar"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="team-avatar-placeholder" style="display:none;">👩‍🔬</div>
                    <div class="team-body">
                        <div class="team-name">Chandana</div>
                        <div class="team-role">Quality Assurance</div>
                        <div class="team-bio">Ensures the highest standards through comprehensive testing and continuous user feedback integration.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CTA ── -->
    <div class="cta-section">
        <div class="cta-box">
            <h2>Ready to get started?</h2>
            <p>Join hundreds of SRMAP students already using the noticeboard to stay informed and connected with the campus.</p>
            <div class="cta-buttons">
                <a href="signup.php" class="btn-primary-ab">Create Account</a>
                <a href="contact.php" class="btn-outline-ab">Contact Us</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/logo.png" alt="SRMAP Logo" class="small-logo" onerror="this.style.display='none'">
                <p style="color:rgba(255,255,255,0.45); font-size:0.88rem; margin-top:0.8rem; max-width:220px; line-height:1.7;">SRM University AP's official digital noticeboard — keeping campus connected.</p>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p>SRM University AP</p>
                <p>Neerukonda, Andhra Pradesh</p>
                <p style="margin-top:0.5rem;">info@srmap.edu.in</p>
                <p>+91 863 230 2222</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> SRM University AP Noticeboard. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
