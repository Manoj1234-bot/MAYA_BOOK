<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['student_id'])){
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_query = mysqli_query($conn, "SELECT name, department FROM students WHERE id='$student_id'");
$student = mysqli_fetch_assoc($student_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MayaBook — Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:       #0d1117;
            --ink-2:     #1c2433;
            --ink-3:     #2e3a4e;
            --muted:     #6b7a91;
            --muted-2:   #9aa5b4;
            --line:      rgba(255,255,255,0.07);
            --gold:      #c9a84c;
            --gold-lt:   #e8c96a;
            --teal:      #2dd4bf;
            --rose:      #f43f5e;
            --surface:   rgba(255,255,255,0.035);
            --surface-h: rgba(255,255,255,0.065);
            --glow-g:    rgba(201,168,76,0.18);
            --radius-lg: 20px;
            --radius-xl: 28px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--ink);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ─── Noise overlay ─── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity: 0.025;
            pointer-events: none;
            z-index: 0;
        }

        /* ─── Ambient glow blobs ─── */
        .ambient {
            position: fixed; pointer-events: none; border-radius: 50%; filter: blur(80px); z-index: 0;
        }
        .amb-1 { width:500px; height:500px; background: rgba(201,168,76,0.07); top:-100px; right:-80px; }
        .amb-2 { width:400px; height:400px; background: rgba(45,212,191,0.05); bottom:0; left:-100px; }

        /* ─── Sidebar ─── */
        .sidebar {
            width: 260px;
            flex-shrink: 0;
            background: var(--ink-2);
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            padding: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 10;
            animation: slideInLeft 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes slideInLeft {
            from { transform: translateX(-30px); opacity: 0; }
            to   { transform: translateX(0);     opacity: 1; }
        }

        .sidebar-brand {
            padding: 36px 28px 28px;
            border-bottom: 1px solid var(--line);
        }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.01em;
        }

        .brand-name span { color: var(--gold); }

        .brand-sub {
            margin-top: 14px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .nav-section {
            padding: 24px 16px 0;
            flex: 1;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 12px;
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 4px;
            text-decoration: none;
            color: var(--muted-2);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.25s ease;
            position: relative;
        }

        .nav-link .nav-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            background: transparent;
            transition: all 0.25s ease;
            flex-shrink: 0;
        }

        .nav-link:hover {
            color: #fff;
            background: var(--surface);
        }

        .nav-link:hover .nav-icon {
            background: var(--surface-h);
        }

        .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(201,168,76,0.15), rgba(201,168,76,0.05));
            border: 1px solid rgba(201,168,76,0.2);
        }

        .nav-link.active .nav-icon {
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            color: var(--ink);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 25%; bottom: 25%;
            width: 3px;
            background: var(--gold);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-footer {
            padding: 20px 16px;
            border-top: 1px solid var(--line);
        }

        .nav-link.danger { color: #ff7675; }
        .nav-link.danger:hover { background: rgba(255,118,117,0.08); color: #ff7675; }

        /* ─── Main ─── */
        .main {
            flex: 1;
            padding: 48px 52px;
            max-width: 1100px;
            position: relative;
            z-index: 1;
        }

        /* ─── Top bar ─── */
        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 48px;
            animation: fadeUp 0.6s 0.1s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes fadeUp {
            from { transform: translateY(16px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .topbar-left {}

        .topbar-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 6px;
        }

        .topbar-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 600;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .topbar-sub {
            margin-top: 8px;
            font-size: 0.875rem;
            color: var(--muted-2);
            font-weight: 300;
        }

        .topbar-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 40px;
            padding: 10px 18px;
        }

        .badge-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--teal);
            box-shadow: 0 0 8px var(--teal);
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity:1; } 50% { opacity:0.3; }
        }

        .badge-text { font-size: 12px; font-weight: 500; color: var(--muted-2); }
        .badge-dept { color: #fff; font-weight: 600; }

        /* ─── Divider ─── */
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, var(--gold), transparent);
            margin-bottom: 36px;
            opacity: 0.35;
        }

        /* ─── Cards grid ─── */
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            padding: 32px;
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16,1,0.3,1);
            animation: fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }

        .card:nth-child(1) { animation-delay: 0.15s; }
        .card:nth-child(2) { animation-delay: 0.22s; }
        .card:nth-child(3) { animation-delay: 0.29s; }
        .card:nth-child(4) { animation-delay: 0.36s; }

        /* Corner accent */
        .card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            border-radius: 0 var(--radius-xl) 0 80px;
            background: var(--card-accent-bg, transparent);
            transition: all 0.4s ease;
        }

        .card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: var(--radius-xl);
            border: 1px solid transparent;
            transition: border-color 0.4s ease;
        }

        .card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 24px 56px rgba(0,0,0,0.4), 0 0 0 1px var(--card-color, var(--gold));
            background: var(--surface-h);
        }

        .card:hover::after {
            border-color: var(--card-color, var(--gold));
            opacity: 0.3;
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .card-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            background: var(--card-icon-bg);
            border: 1px solid var(--card-icon-border, transparent);
            position: relative;
            z-index: 1;
        }

        .live-pip {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.3);
            border-radius: 20px;
            padding: 4px 10px;
        }

        .live-pip-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--rose);
            animation: blink 1s ease-in-out infinite;
        }

        .live-pip-text {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--rose);
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .card-desc {
            font-size: 0.845rem;
            color: var(--muted-2);
            line-height: 1.65;
            font-weight: 300;
        }

        .card-arrow {
            position: absolute;
            bottom: 28px; right: 28px;
            font-size: 20px;
            opacity: 0;
            transform: translateX(-6px);
            transition: all 0.3s ease;
            color: var(--card-color, var(--gold));
        }

        .card:hover .card-arrow { opacity:1; transform: translateX(0); }

        /* Card themes */
        .card-quiz   { --card-color: var(--rose);  --card-icon-bg: rgba(244,63,94,0.1);  --card-icon-border: rgba(244,63,94,0.2);  --card-accent-bg: rgba(244,63,94,0.04); }
        .card-events { --card-color: #818cf8;       --card-icon-bg: rgba(129,140,248,0.1); --card-icon-border: rgba(129,140,248,0.2); --card-accent-bg: rgba(129,140,248,0.04); }
        .card-reg    { --card-color: var(--teal);   --card-icon-bg: rgba(45,212,191,0.1);  --card-icon-border: rgba(45,212,191,0.2);  --card-accent-bg: rgba(45,212,191,0.04); }
        .card-cert   { --card-color: var(--gold);   --card-icon-bg: rgba(201,168,76,0.1);  --card-icon-border: rgba(201,168,76,0.2);  --card-accent-bg: rgba(201,168,76,0.06); }

        /* ─── Stats row ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 24px;
            animation: fadeUp 0.6s 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        .stat-cell {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 22px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-val {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 600;
            color: #fff;
            line-height: 1;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-top: 4px;
        }

        .stat-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ─── Footer ─── */
        .page-footer {
            margin-top: 44px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeUp 0.5s 0.6s ease both;
        }

        .footer-brand {
            font-size: 12px;
            color: var(--muted);
        }

        .footer-brand strong { color: var(--gold); font-weight: 600; }

        .footer-links {
            display: flex; gap: 20px;
        }

        .footer-links a {
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--gold-lt); }

        /* ─── Mobile nav ─── */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(28,36,51,0.92);
            backdrop-filter: blur(16px);
            padding: 10px 28px;
            border-radius: 50px;
            border: 1px solid var(--line);
            gap: 28px;
            z-index: 1000;
        }

        .mobile-nav a {
            color: var(--muted);
            font-size: 20px;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
        }

        .mobile-nav a.active { color: var(--gold); filter: drop-shadow(0 0 6px var(--gold)); }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .main { padding: 40px 32px; }
        }

        @media (max-width: 860px) {
            .sidebar { display: none; }
            .mobile-nav { display: flex; }
            .main { padding: 32px 20px 100px; }
            .topbar { flex-direction: column; gap: 16px; }
            .topbar-title { font-size: 1.9rem; }
            .cards-grid { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Ambient lighting -->
    <div class="ambient amb-1"></div>
    <div class="ambient amb-2"></div>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark">
                <div class="brand-icon">📘</div>
                <div class="brand-name">Maya<span>Book</span></div>
            </div>
            <div class="brand-sub">EVENT BOOKING PORTAL</div>
        </div>

        <nav class="nav-section">
            <div class="nav-label">Navigation</div>

            <a href="dashboard.php" class="nav-link active">
                <span class="nav-icon">🏠</span>
                Dashboard
            </a>
            <a href="live_quiz_list.php" class="nav-link">
                <span class="nav-icon">🎮</span>
                Live Quiz
            </a>
            <a href="events.php" class="nav-link">
                <span class="nav-icon">📅</span>
                Available Events
            </a>
            <a href="my_bookings.php" class="nav-link">
                <span class="nav-icon">🎟️</span>
                My Bookings
            </a>
            <a href="profile.php" class="nav-link">
                <span class="nav-icon">👤</span>
                My Profile
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-link danger">
                <span class="nav-icon">🚪</span>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="main">

        <!-- Top bar -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-eyebrow">Student Portal • Academic Year 2025–26</div>
                <h1 class="topbar-title">Good day, <?php echo explode(' ', htmlspecialchars($student['name']))[0]; ?>.</h1>
                <p class="topbar-sub">Here's an overview of your portal activity.</p>
            </div>
            <div class="topbar-badge">
                <div class="badge-dot"></div>
                <span class="badge-text">
                    <span class="badge-dept"><?php echo htmlspecialchars($student['department']); ?></span>
                    &nbsp;Dept.
                </span>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- Cards -->
        <div class="cards-grid">

            <!-- Quiz -->
            <a href="live_quiz_list.php" class="card card-quiz">
                <div class="card-header">
                    <div class="card-icon">🎮</div>
                    <div class="live-pip">
                        <div class="live-pip-dot"></div>
                        <span class="live-pip-text">Live Now</span>
                    </div>
                </div>
                <div class="card-title">Live Quiz Session</div>
                <div class="card-desc">Compete with peers in real-time, test your knowledge, and rise up the leaderboard standings.</div>
                <div class="card-arrow">→</div>
            </a>

            <!-- Events -->
            <a href="events.php" class="card card-events">
                <div class="card-header">
                    <div class="card-icon">📅</div>
                </div>
                <div class="card-title">Available Events</div>
                <div class="card-desc">Browse upcoming seminars, technical workshops, and guest lectures curated for your department.</div>
                <div class="card-arrow">→</div>
            </a>

            <!-- Registrations -->
            <a href="my_bookings.php" class="card card-reg">
                <div class="card-header">
                    <div class="card-icon">✅</div>
                </div>
                <div class="card-title">My Registrations</div>
                <div class="card-desc">Manage booked slots, view entry passes, and review your upcoming event schedule at a glance.</div>
                <div class="card-arrow">→</div>
            </a>

            <!-- Certificates -->
            <a href="certificate.php" class="card card-cert">
                <div class="card-header">
                    <div class="card-icon">🎓</div>
                </div>
                <div class="card-title">E-Certificates</div>
                <div class="card-desc">Download verified certificates for events you have completed. Authenticated and blockchain-backed.</div>
                <div class="card-arrow">→</div>
            </a>

        </div>

        <!-- Stats row -->
        <div class="stats-row">
            <div class="stat-cell">
                <div class="stat-icon" style="background:rgba(201,168,76,0.1);">📊</div>
                <div>
                    <div class="stat-val">4</div>
                    <div class="stat-label">Services Available</div>
                </div>
            </div>
            <div class="stat-cell">
                <div class="stat-icon" style="background:rgba(45,212,191,0.1);">🏛️</div>
                <div>
                    <div class="stat-val"><?php echo htmlspecialchars($student['department']); ?></div>
                    <div class="stat-label">Your Department</div>
                </div>
            </div>
            <div class="stat-cell">
                <div class="stat-icon" style="background:rgba(129,140,248,0.1);">🕐</div>
                <div>
                    <div class="stat-val" id="live-time">--:--</div>
                    <div class="stat-label">Current Time</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="page-footer">
            <div class="footer-brand">MayaBook <strong>v3.0</strong> &nbsp;·&nbsp; Designed by Manoj S</div>
            <div class="footer-links">
                <a href="#">Help</a>
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
            </div>
        </footer>

    </main>

    <!-- Mobile nav -->
    <nav class="mobile-nav">
        <a href="dashboard.php" class="active">🏠</a>
        <a href="live_quiz_list.php">🎮</a>
        <a href="events.php">📅</a>
        <a href="my_bookings.php">🎟️</a>
        <a href="logout.php">🚪</a>
    </nav>

    <script>
        // Live clock
        function updateTime() {
            const el = document.getElementById('live-time');
            if (!el) return;
            const now = new Date();
            el.textContent = now.toLocaleTimeString('en-IN', { hour:'2-digit', minute:'2-digit' });
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>

</body>
</html>