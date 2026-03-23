<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MayaBook | Learn & Grow</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6C47FF;
            --accent: #00E5C0;
            --gold: #FFD166;
            --dark: #07080F;
            --dark2: #0D0F1C;
            --glass: rgba(255,255,255,0.04);
            --border: rgba(255,255,255,0.08);
            --text-muted: #5a6480;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: #e2e8f8;
            overflow-x: hidden;
            cursor: none;
        }

        /* Custom Cursor */
        .cursor {
            position: fixed;
            width: 12px; height: 12px;
            background: var(--accent);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: transform 0.1s, width 0.2s, height 0.2s, opacity 0.2s;
            mix-blend-mode: screen;
        }
        .cursor-ring {
            position: fixed;
            width: 40px; height: 40px;
            border: 1px solid rgba(0,229,192,0.4);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9998;
            transform: translate(-50%, -50%);
            transition: transform 0.15s ease, width 0.3s, height 0.3s;
        }

        /* Noise Overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 1000;
            opacity: 0.4;
        }

        /* ─── NAV ─── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 5%;
            background: rgba(7,8,15,0.5);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            animation: slideDown 0.8s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .nav-logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: white;
            text-decoration: none;
        }
        .nav-logo span { color: var(--primary); }
        .nav-logo em {
            display: inline-block;
            width: 8px; height: 8px;
            background: var(--accent);
            border-radius: 50%;
            margin-left: 3px;
            vertical-align: middle;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.6); opacity: 0.5; }
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-btn {
            padding: 10px 22px;
            border-radius: 10px;
            text-decoration: none;
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
            font-size: 0.88rem;
            letter-spacing: 0.2px;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: white;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .nav-btn:hover::after { opacity: 0.07; }

        .nav-btn-login {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 20px rgba(108,71,255,0.35);
        }
        .nav-btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(108,71,255,0.5);
        }

        .nav-btn-register {
            background: var(--glass);
            color: #c0c8e8;
            border: 1px solid var(--border);
        }
        .nav-btn-register:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.2);
            color: white;
        }

        .nav-btn-admin {
            padding: 9px 16px;
            background: transparent;
            color: var(--text-muted);
            border: 1px dashed rgba(255,255,255,0.15);
            font-size: 0.8rem;
        }
        .nav-btn-admin:hover {
            color: var(--gold);
            border-color: rgba(255,209,102,0.3);
        }

        /* ─── HERO ─── */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 120px 5% 80px;
            position: relative;
            text-align: center;
            overflow: hidden;
        }

        /* Animated Background */
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            animation: float 12s ease-in-out infinite;
        }
        .orb-1 { width: 600px; height: 600px; background: var(--primary); top: -10%; left: -10%; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: var(--accent); bottom: 0; right: -5%; animation-delay: -4s; }
        .orb-3 { width: 300px; height: 300px; background: #ff6bac; top: 40%; left: 30%; animation-delay: -8s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -30px) scale(1.05); }
            66%       { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* Grid Lines */
        .grid-lines {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Hero Content */
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 860px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(108,71,255,0.12);
            border: 1px solid rgba(108,71,255,0.3);
            color: #a89bff;
            padding: 7px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-bottom: 32px;
            animation: fadeUp 0.8s cubic-bezier(0.16,1,0.3,1) 0.2s both;
        }
        .hero-badge .dot {
            width: 6px; height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(3rem, 7vw, 6rem);
            font-weight: 800;
            line-height: 1.0;
            letter-spacing: -2px;
            margin-bottom: 24px;
            animation: fadeUp 0.8s cubic-bezier(0.16,1,0.3,1) 0.35s both;
        }

        .hero-title .brand { 
            background: linear-gradient(135deg, #8b72ff, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: clamp(1rem, 1.8vw, 1.2rem);
            color: var(--text-muted);
            line-height: 1.75;
            max-width: 600px;
            margin: 0 auto 50px;
            font-weight: 300;
            animation: fadeUp 0.8s cubic-bezier(0.16,1,0.3,1) 0.5s both;
        }

        /* Stats Row */
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-bottom: 60px;
            animation: fadeUp 0.8s cubic-bezier(0.16,1,0.3,1) 0.65s both;
        }
        .stat { text-align: center; }
        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }
        .stat-num span { color: var(--accent); }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        /* Scroll indicator */
        .scroll-hint {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.72rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: fadeIn 1.5s 1.2s both;
        }
        .scroll-line {
            width: 1px;
            height: 50px;
            background: linear-gradient(to bottom, var(--accent), transparent);
            animation: scrollLine 1.5s ease-in-out infinite;
        }
        @keyframes scrollLine {
            0%   { transform: scaleY(0); transform-origin: top; }
            50%  { transform: scaleY(1); transform-origin: top; }
            51%  { transform: scaleY(1); transform-origin: bottom; }
            100% { transform: scaleY(0); transform-origin: bottom; }
        }

        /* ─── FEATURES ─── */
        .section {
            padding: 100px 7%;
            position: relative;
        }

        .section-label {
            font-size: 0.75rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 16px;
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: -1px;
            color: white;
            margin-bottom: 60px;
            max-width: 500px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feat-card {
            background: var(--dark2);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            position: relative;
            overflow: hidden;
            transition: transform 0.35s cubic-bezier(0.16,1,0.3,1), border-color 0.3s, box-shadow 0.3s;
            cursor: default;
        }

        .feat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--card-color, var(--primary)), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .feat-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255,255,255,0.12);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(108,71,255,0.07);
        }
        .feat-card:hover::before { opacity: 1; }

        .feat-card:nth-child(1) { --card-color: #6C47FF; }
        .feat-card:nth-child(2) { --card-color: #00E5C0; }
        .feat-card:nth-child(3) { --card-color: #FFD166; }
        .feat-card:nth-child(4) { --card-color: #ff6bac; }
        .feat-card:nth-child(5) { --card-color: #60a5fa; }
        .feat-card:nth-child(6) { --card-color: #a3e635; }

        .feat-icon {
            font-size: 2.2rem;
            margin-bottom: 20px;
            display: block;
            animation: bobble 3s ease-in-out infinite;
        }

        @keyframes bobble {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .feat-card:nth-child(2) .feat-icon { animation-delay: -1s; }
        .feat-card:nth-child(3) .feat-icon { animation-delay: -2s; }
        .feat-card:nth-child(4) .feat-icon { animation-delay: -0.5s; }
        .feat-card:nth-child(5) .feat-icon { animation-delay: -1.5s; }
        .feat-card:nth-child(6) .feat-icon { animation-delay: -2.5s; }

        .feat-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }

        .feat-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.65;
        }

        .feat-tag {
            display: inline-block;
            margin-top: 18px;
            padding: 4px 12px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 100px;
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        /* ─── CTA BAND ─── */
        .cta-band {
            margin: 0 7% 80px;
            background: linear-gradient(135deg, #1a1235, #0d1726);
            border: 1px solid rgba(108,71,255,0.25);
            border-radius: 28px;
            padding: 70px 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            position: relative;
            overflow: hidden;
        }

        .cta-band::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(108,71,255,0.2), transparent 70%);
            pointer-events: none;
        }

        .cta-text h2 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.5rem, 3vw, 2.3rem);
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        .cta-text p { color: var(--text-muted); font-size: 0.95rem; }

        .cta-actions {
            display: flex;
            gap: 14px;
            flex-shrink: 0;
        }

        .cta-btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .cta-btn-main {
            background: var(--primary);
            color: white;
            box-shadow: 0 8px 25px rgba(108,71,255,0.4);
        }
        .cta-btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 35px rgba(108,71,255,0.55);
        }

        .cta-btn-sec {
            background: rgba(255,255,255,0.06);
            color: #c0c8e8;
            border: 1px solid var(--border);
        }
        .cta-btn-sec:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* ─── FOOTER ─── */
        footer {
            border-top: 1px solid var(--border);
            padding: 36px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-muted);
            font-size: 0.82rem;
        }

        footer .ft-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            color: white;
            font-size: 1rem;
        }
        footer .ft-logo span { color: var(--primary); }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* Scroll-reveal */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1);
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal:nth-child(2) { transition-delay: 0.08s; }
        .reveal:nth-child(3) { transition-delay: 0.16s; }
        .reveal:nth-child(4) { transition-delay: 0.24s; }
        .reveal:nth-child(5) { transition-delay: 0.32s; }
        .reveal:nth-child(6) { transition-delay: 0.40s; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 900px) {
            .features-grid { grid-template-columns: 1fr 1fr; }
            .cta-band { flex-direction: column; text-align: center; padding: 50px 30px; }
            .cta-actions { justify-content: center; }
            .stats-row { gap: 30px; }
        }

        @media (max-width: 600px) {
            .features-grid { grid-template-columns: 1fr; }
            nav { padding: 16px 4%; }
            .nav-btn-admin { display: none; }
            .hero-title { letter-spacing: -1.5px; }
            footer { flex-direction: column; gap: 12px; text-align: center; }
            .stats-row { gap: 20px; }
            .stat-num { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Custom Cursor -->
    <div class="cursor" id="cursor"></div>
    <div class="cursor-ring" id="cursorRing"></div>

    <!-- ─── NAV ─── -->
    <nav>
        <a href="index.php" class="nav-logo">Maya<span>Book</span><em></em></a>
        <div class="nav-buttons">
            <a href="admin/admin_login.php" class="nav-btn nav-btn-admin">🔒 Admin</a>
            <a href="register.php" class="nav-btn nav-btn-register">Register</a>
            <a href="login.php" class="nav-btn nav-btn-login">Student Login →</a>
        </div>
    </nav>

    <!-- ─── HERO ─── -->
    <section class="hero">
        <div class="hero-bg">
            <div class="grid-lines"></div>
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
        </div>

        <div class="hero-content">
            <div class="hero-badge">
                <span class="dot"></span>
                Event Management & Learning Platform
            </div>

            <h1 class="hero-title">
                Where Students<br>
                <span class="brand">Learn, Compete<br>&amp; Grow</span>
            </h1>

            <p class="hero-sub">
                MayaBook is your all-in-one platform to book seminars, join live quizzes, and earn verified digital certificates — all in one seamless experience.
            </p>

            <div class="stats-row">
                <div class="stat">
                    <div class="stat-num">500<span>+</span></div>
                    <div class="stat-label">Events Hosted</div>
                </div>
                <div class="stat">
                    <div class="stat-num">12k<span>+</span></div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat">
                    <div class="stat-num">98<span>%</span></div>
                    <div class="stat-label">Satisfaction</div>
                </div>
            </div>
        </div>

        <div class="scroll-hint">
            <span>Explore</span>
            <div class="scroll-line"></div>
        </div>
    </section>

    <!-- ─── FEATURES ─── -->
    <section class="section">
        <div class="section-label">What We Offer</div>
        <h2 class="section-title">Everything you need in one platform</h2>

        <div class="features-grid">
            <div class="feat-card reveal">
                <span class="feat-icon">📅</span>
                <div class="feat-title">Event Booking</div>
                <p class="feat-desc">Reserve your seat in upcoming workshops, auditorium talks, and seminars with a single click. No queues, no hassle.</p>
                <span class="feat-tag">Instant Confirmation</span>
            </div>

            <div class="feat-card reveal">
                <span class="feat-icon">⚡</span>
                <div class="feat-title">Live Quizzes</div>
                <p class="feat-desc">Compete in real-time knowledge battles. Speed-based scoring means every second counts — climb to the top of the leaderboard.</p>
                <span class="feat-tag">Real-Time</span>
            </div>

            <div class="feat-card reveal">
                <span class="feat-icon">📜</span>
                <div class="feat-title">Digital Certificates</div>
                <p class="feat-desc">Earn verifiable certificates instantly after attending events. Download, share, and showcase your achievements anywhere.</p>
                <span class="feat-tag">Verified & Shareable</span>
            </div>

            <div class="feat-card reveal">
                <span class="feat-icon">🏆</span>
                <div class="feat-title">Leaderboards</div>
                <p class="feat-desc">Track your progress and compare scores with peers across departments and batches. Friendly competition drives growth.</p>
                <span class="feat-tag">Live Rankings</span>
            </div>

            <div class="feat-card reveal">
                <span class="feat-icon">🔔</span>
                <div class="feat-title">Smart Notifications</div>
                <p class="feat-desc">Never miss an event. Get timely reminders for upcoming seminars, quiz starts, and result announcements.</p>
                <span class="feat-tag">Always Updated</span>
            </div>

            <div class="feat-card reveal">
                <span class="feat-icon">🛡️</span>
                <div class="feat-title">Admin Dashboard</div>
                <p class="feat-desc">Organizers get a powerful control panel to create events, manage attendance, generate reports, and oversee all activity.</p>
                <span class="feat-tag">Full Control</span>
            </div>
        </div>
    </section>

    <!-- ─── CTA BAND ─── -->
    <div class="cta-band reveal">
        <div class="cta-text">
            <h2>Ready to start your journey?</h2>
            <p>Join thousands of students already leveling up on MayaBook.</p>
        </div>
        <div class="cta-actions">
            <a href="register.php" class="cta-btn cta-btn-main">Create Free Account</a>
            <a href="login.php" class="cta-btn cta-btn-sec">Sign In</a>
        </div>
    </div>

    <!-- ─── FOOTER ─── -->
    <footer>
        <div class="ft-logo">Maya<span>Book</span></div>
        <span>&copy; 2026 MayaBook Learning System. All Rights Reserved.</span>
        <span>Made for Students ✦</span>
    </footer>

    <script>
        // Custom cursor
        const cursor = document.getElementById('cursor');
        const ring   = document.getElementById('cursorRing');
        let mx = 0, my = 0, rx = 0, ry = 0;

        document.addEventListener('mousemove', e => {
            mx = e.clientX; my = e.clientY;
            cursor.style.left = mx + 'px';
            cursor.style.top  = my + 'px';
        });

        function animateRing() {
            rx += (mx - rx) * 0.12;
            ry += (my - ry) * 0.12;
            ring.style.left = rx + 'px';
            ring.style.top  = ry + 'px';
            requestAnimationFrame(animateRing);
        }
        animateRing();

        document.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.style.width  = '20px';
                cursor.style.height = '20px';
                cursor.style.opacity = '0.6';
                ring.style.width  = '60px';
                ring.style.height = '60px';
            });
            el.addEventListener('mouseleave', () => {
                cursor.style.width  = '12px';
                cursor.style.height = '12px';
                cursor.style.opacity = '1';
                ring.style.width  = '40px';
                ring.style.height = '40px';
            });
        });

        // Scroll reveal
        const reveals = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });

        reveals.forEach(el => observer.observe(el));

        // Nav glass on scroll
        const nav = document.querySelector('nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                nav.style.background = 'rgba(7,8,15,0.85)';
            } else {
                nav.style.background = 'rgba(7,8,15,0.5)';
            }
        });

        // Counter animation for stats
        function animateCounter(el, target, suffix = '') {
            let start = 0;
            const step = target / 60;
            const timer = setInterval(() => {
                start += step;
                if (start >= target) {
                    clearInterval(timer);
                    start = target;
                }
                el.innerHTML = Math.floor(start) + '<span>' + suffix + '</span>';
            }, 20);
        }

        const statNums = document.querySelectorAll('.stat-num');
        const statsObserver = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const targets = [500, 12, 98];
                    const suffixes = ['+', 'k+', '%'];
                    statNums.forEach((el, i) => animateCounter(el, targets[i], suffixes[i]));
                    statsObserver.disconnect();
                }
            });
        }, { threshold: 0.5 });

        if (statNums.length) statsObserver.observe(statNums[0]);
    </script>
</body>
</html>