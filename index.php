<?php
/**
 * StudentHub Portal - Main PHP Application Gateway
 * Practical 15: Full Integration, Session State & Local Deployment
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth_check.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
$userName = $_SESSION['username'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudentHub - Unified Digital Campus Portal for CHARUSAT University FTE">
    <title>StudentHub - Digital Campus Portal | CHARUSAT FTE</title>
    <link rel="icon" type="image/png" href="images/favStudentIcon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <a href="#mainContent" class="skip-link">Skip to main content</a>

    <!-- Top Announcement Banner (Practical 4) -->
    <div id="notification" role="region" aria-label="Campus Announcement">
        <div class="notification-content">
            <span class="notification-badge">ANNOUNCEMENT</span>
            <p><strong>Charusat Innovate 36-Hr Hackathon:</strong> Registrations are now open with prize pools exceeding ₹1,00,000! Register your team before seats fill.</p>
        </div>
        <button id="closeNotification" aria-label="Dismiss Announcement">&times;</button>
    </div>

    <!-- Header & Live Status Bar (Practical 2 & 4) -->
    <header role="banner">
        <div class="header-top">
            <img src="images/StudentHubLogo.png" alt="StudentHub Official Portal Logo" class="header-logo">
            <div class="header-brand">
                <h1>StudentHub Portal</h1>
                <p class="tagline">Faculty of Technology and Engineering (FTE) — CHARUSAT</p>
            </div>
        </div>
        <div class="header-meta">
            <span class="live-badge">PHP 8 &amp; MySQL Connected</span>
            <span id="greeting">👋 Welcome, <?= htmlspecialchars(ucfirst($userName)) ?>!</span>
            <span id="date">📅 Loading Date...</span>
            <span id="clock">⏰ 00:00:00 AM</span>
        </div>
    </header>

    <!-- Navigation Bar (Role-Based Dynamic Menu - Practical 10 & 14) -->
    <nav class="main-nav" role="navigation" aria-label="Main Navigation">
        <div class="nav-brand">
            <img src="images/favStudentIcon.png" alt="StudentHub Icon">
            <span>StudentHub <?= $isLoggedIn ? "<small>[{$userRole}]</small>" : "" ?></span>
        </div>
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation menu" aria-expanded="false">☰</button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="active">🏠 Home</a></li>
            <li><a href="pages/about.html">🏛️ About FTE</a></li>
            <li><a href="pages/events.html">🎯 Events &amp; Workshops</a></li>
            
            <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                <li><a href="admin_dashboard.php">⚙️ Admin Console</a></li>
                <li><a href="manage_students.php">👥 Manage Students</a></li>
                <li><a href="manage_events.php">🎯 Manage Events</a></li>
            <?php elseif ($isLoggedIn && $userRole === 'student'): ?>
                <li><a href="student_dashboard.php">📊 My Dashboard</a></li>
                <li><a href="pages/profile.html">👤 My Profile</a></li>
            <?php else: ?>
                <li><a href="pages/dashboard.html">📊 Dashboard</a></li>
                <li><a href="pages/profile.html">👤 My Profile</a></li>
            <?php endif; ?>

            <li><a href="pages/faq.html">❓ FAQ</a></li>
            <li><a href="pages/contact.html">📞 Contact</a></li>
            <li><a href="pages/feedback.html">💬 Feedback</a></li>

            <?php if ($isLoggedIn): ?>
                <li><a href="php/logout.php" class="btn btn-danger btn-sm" style="color: #fff;">🚪 Logout</a></li>
            <?php else: ?>
                <li><a href="pages/login.html" class="btn btn-secondary btn-sm" style="color: var(--primary);">🔐 Login</a></li>
                <li><a href="pages/register.html" class="btn btn-primary btn-sm" style="color: #fff;">📝 Register</a></li>
            <?php endif; ?>
        </ul>
        <div class="nav-actions">
            <button id="themeBtn" class="theme-toggle-btn" aria-label="Toggle theme">🌙 Dark Theme</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="mainContent" role="main">

        <!-- Welcome Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h2>Empowering B.Tech Innovators at CHARUSAT</h2>
                <p class="hero-lead">
                    Welcome to <strong>StudentHub</strong>, the unified digital campus ecosystem engineered for the 
                    students and faculty of the <strong>Faculty of Technology &amp; Engineering (FTE)</strong> at 
                    <strong>Charotar University of Science and Technology</strong>. Seamlessly manage registrations, 
                    track upcoming hackathons, participate in coding bootcamps, and stay updated with real-time academic announcements.
                </p>
                <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                    <a href="pages/events.html" class="btn btn-primary">🚀 Explore Events</a>
                    <a href="pages/register.html" class="btn btn-secondary">📝 New Student Onboarding</a>
                    <?php if ($userRole === 'admin'): ?>
                        <a href="admin_dashboard.php" class="btn btn-secondary">⚙️ Open Admin Console</a>
                    <?php else: ?>
                        <a href="pages/dashboard.html" class="btn btn-secondary">📊 Student Portal</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Campus Image & Thought Card (User Explicit Request) -->
            <figure class="campus-showcase">
                <div class="campus-image-wrapper">
                    <img src="images/campus.png" alt="CHARUSAT University Campus Panoramic View, Changa">
                    <div class="campus-overlay-badge">
                        <span>🏛️ CHARUSAT University Campus — Changa, Gujarat</span>
                    </div>
                </div>
                <div class="thought-card">
                    <div class="thought-icon">💡</div>
                    <div class="thought-body">
                        <blockquote>
                            "Education is not the learning of facts, but the training of the mind to think."
                        </blockquote>
                        <p class="thought-sub">
                            At CHARUSAT, every challenge is an opportunity to invent, every laboratory is a launchpad for discovery, 
                            and every student is an architect of tomorrow's technological landscape.
                        </p>
                        <cite>— Albert Einstein &bull; Dedicated to B.Tech Engineers</cite>
                    </div>
                </div>
            </figure>
        </section>

        <!-- Dynamic Quick Stats Grid (Practical 3 & 14) -->
        <section class="metrics-grid" aria-label="Campus Statistics">
            <div class="metric-card">
                <div class="metric-icon">🎓</div>
                <div class="metric-info">
                    <h4>Enrolled Engineers</h4>
                    <div class="metric-number">4,850+</div>
                    <span class="metric-trend">Across CSPIT &amp; DEPSTAR</span>
                </div>
            </div>
            <div class="metric-card success">
                <div class="metric-icon">🚀</div>
                <div class="metric-info">
                    <h4>Active Events &amp; Labs</h4>
                    <div class="metric-number">16 Active</div>
                    <span class="metric-trend">Bootcamps &amp; Hackathons</span>
                </div>
            </div>
            <div class="metric-card warning">
                <div class="metric-icon">🏆</div>
                <div class="metric-info">
                    <h4>Placement Record</h4>
                    <div class="metric-number">94.8%</div>
                    <span class="metric-trend">Top Recruiter Offers</span>
                </div>
            </div>
            <div class="metric-card danger">
                <div class="metric-icon">🔬</div>
                <div class="metric-info">
                    <h4>R&amp;D Patents &amp; Papers</h4>
                    <div class="metric-number">320+</div>
                    <span class="metric-trend">Published Innovations</span>
                </div>
            </div>
        </section>

        <!-- Featured Events Slider / Carousel (Practical 4) -->
        <section class="carousel-container" aria-label="Featured Technical Events">
            <div class="carousel-header">
                <h3><span>🔥</span> Flagship Campus Highlights</h3>
                <a href="pages/events.html" class="btn btn-secondary btn-sm">View All 16 Events ›</a>
            </div>
            <div class="carousel-track-wrapper">
                <div class="carousel-track" id="carouselTrack">
                    <!-- Slide 1 -->
                    <div class="carousel-slide">
                        <div class="carousel-slide-image">
                            <img src="images/event-hackathon.svg" alt="Charusat Innovate 36-Hour Hackathon">
                        </div>
                        <div class="carousel-slide-content">
                            <span class="tag">FLAGSHIP HACKATHON</span>
                            <h4>Charusat Innovate 2026 (36-Hour Sprint)</h4>
                            <p>Collaborate with multidisciplinary engineering squads to engineer groundbreaking solutions in Agritech, Healthcare and AI. Over ₹1,00,000 in prizes!</p>
                            <div style="margin-bottom: 16px; font-size: 0.88rem; color: var(--text-muted);">
                                📅 28-30 Nov 2026 &bull; 🏛️ Central Innovation Complex
                            </div>
                            <a href="pages/events.html" class="btn btn-primary btn-sm">Register Team</a>
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="carousel-slide">
                        <div class="carousel-slide-image">
                            <img src="images/event-web.svg" alt="Web Dev & Cloud Bootcamp">
                        </div>
                        <div class="carousel-slide-content">
                            <span class="tag">TECHNICAL WORKSHOP</span>
                            <h4>Full-Stack Web Dev &amp; Cloud Bootcamp</h4>
                            <p>Master modern frontend frameworks, PHP 8 backend APIs, MySQL databases, and cloud deployment pipelines with hands-on lab sessions.</p>
                            <div style="margin-bottom: 16px; font-size: 0.88rem; color: var(--text-muted);">
                                📅 25 Oct 2026 &bull; 🏛️ CSPIT Seminar Hall 1
                            </div>
                            <a href="pages/events.html" class="btn btn-primary btn-sm">Join Bootcamp</a>
                        </div>
                    </div>
                    <!-- Slide 3 -->
                    <div class="carousel-slide">
                        <div class="carousel-slide-image">
                            <img src="images/event-ai.svg" alt="AI & Deep Learning Summit">
                        </div>
                        <div class="carousel-slide-content">
                            <span class="tag">AI &amp; ML SUMMIT</span>
                            <h4>Generative AI &amp; LLM Engineering Summit</h4>
                            <p>Hands-on exploration of transformer architectures, prompt orchestration, computer vision pipelines, and deploying edge AI models.</p>
                            <div style="margin-bottom: 16px; font-size: 0.88rem; color: var(--text-muted);">
                                📅 12 Nov 2026 &bull; 🏛️ FTE Central Auditorium
                            </div>
                            <a href="pages/events.html" class="btn btn-primary btn-sm">Reserve Seat</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-controls">
                    <button class="carousel-btn" id="carouselPrev" aria-label="Previous Slide">‹</button>
                    <button class="carousel-btn" id="carouselNext" aria-label="Next Slide">›</button>
                </div>
            </div>
            <div class="carousel-indicators" id="carouselIndicators"></div>
        </section>

    </main>

    <!-- Footer -->
    <footer role="contentinfo">
        <div class="footer-grid">
            <div class="footer-brand">
                <h4>🎓 StudentHub Portal</h4>
                <p>Centralized digital campus platform for FTE at CHARUSAT University.</p>
            </div>
            <div class="footer-col">
                <h5>Navigation</h5>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="pages/about.html">About FTE</a></li>
                    <li><a href="pages/events.html">Events &amp; Workshops</a></li>
                    <li><a href="pages/dashboard.html">Student Dashboard</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Support</h5>
                <ul>
                    <li><a href="pages/faq.html">FAQ</a></li>
                    <li><a href="pages/contact.html">Contact</a></li>
                    <li><a href="pages/feedback.html">Feedback</a></li>
                    <li><a href="pages/submissions.html">CSV Records</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Deployment</h5>
                <p style="font-size: 0.85rem; line-height: 1.5;">Deployed on Apache/PHP 8 &amp; MySQL &bull; Subject: ITUE203</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Charotar University of Science and Technology (CHARUSAT). All Rights Reserved.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
