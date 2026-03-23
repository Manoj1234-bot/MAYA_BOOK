<?php
session_start();
include "../includes/db.php";

$student_id = $_SESSION['student_id'];

$query = "SELECT events.id, events.event_name 
          FROM bookings 
          JOIN events ON bookings.event_id = events.id 
          WHERE bookings.student_id = '$student_id' AND bookings.is_present = 1";
$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MayaBook — Live Quiz</title>
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
            --violet:    #818cf8;
            --surface:   rgba(255,255,255,0.035);
            --surface-h: rgba(255,255,255,0.065);
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

        /* Noise */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity: 0.025; pointer-events: none; z-index: 0;
        }

        .ambient { position:fixed; pointer-events:none; border-radius:50%; filter:blur(90px); z-index:0; }
        .amb-1 { width:500px; height:500px; background:rgba(244,63,94,0.06); top:-120px; right:-80px; }
        .amb-2 { width:380px; height:380px; background:rgba(45,212,191,0.05); bottom:0; left:-80px; }

        /* ─── Sidebar ─── */
        .sidebar {
            width: 260px; flex-shrink: 0;
            background: var(--ink-2);
            border-right: 1px solid var(--line);
            display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh;
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

        .brand-mark { display:flex; align-items:center; gap:10px; }

        .brand-icon {
            width:36px; height:36px;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px;
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; font-weight: 600; color: #fff; letter-spacing: 0.01em;
        }
        .brand-name span { color: var(--gold); }

        .brand-sub {
            margin-top:14px; font-size:11px; font-weight:500;
            letter-spacing:0.12em; text-transform:uppercase; color:var(--muted);
        }

        .nav-section { padding: 24px 16px 0; flex: 1; }

        .nav-label {
            font-size:10px; font-weight:600; letter-spacing:0.14em;
            text-transform:uppercase; color:var(--muted);
            padding:0 12px; margin-bottom:10px;
        }

        .nav-link {
            display:flex; align-items:center; gap:12px;
            padding:12px 14px; border-radius:12px; margin-bottom:4px;
            text-decoration:none; color:var(--muted-2);
            font-size:0.875rem; font-weight:500;
            transition:all 0.25s ease; position:relative;
        }

        .nav-link .nav-icon {
            width:34px; height:34px; border-radius:9px;
            display:flex; align-items:center; justify-content:center;
            font-size:16px; background:transparent; transition:all 0.25s ease; flex-shrink:0;
        }

        .nav-link:hover { color:#fff; background:var(--surface); }
        .nav-link:hover .nav-icon { background:var(--surface-h); }

        .nav-link.active {
            color:#fff;
            background: linear-gradient(135deg, rgba(244,63,94,0.15), rgba(244,63,94,0.05));
            border:1px solid rgba(244,63,94,0.2);
        }
        .nav-link.active .nav-icon {
            background: linear-gradient(135deg, var(--rose), #ff6b6b);
            color: #fff;
        }
        .nav-link.active::before {
            content:''; position:absolute; left:0; top:25%; bottom:25%;
            width:3px; background:var(--rose); border-radius:0 3px 3px 0;
        }

        .sidebar-footer { padding:20px 16px; border-top:1px solid var(--line); }
        .nav-link.danger { color:#ff7675; }
        .nav-link.danger:hover { background:rgba(255,118,117,0.08); color:#ff7675; }

        /* ─── Main ─── */
        .main {
            flex:1; padding:48px 52px;
            max-width: 860px;
            position:relative; z-index:1;
        }

        /* ─── Topbar ─── */
        .topbar {
            display:flex; align-items:flex-start; justify-content:space-between;
            margin-bottom:40px;
            animation: fadeUp 0.55s 0.1s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes fadeUp {
            from { transform: translateY(16px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .topbar-eyebrow {
            font-size:11px; font-weight:600; letter-spacing:0.14em;
            text-transform:uppercase; color:var(--rose); margin-bottom:6px;
        }

        .topbar-title {
            font-family:'Playfair Display', serif;
            font-size:2.2rem; font-weight:600; color:#fff;
            line-height:1.1; letter-spacing:-0.02em;
        }

        .topbar-sub { margin-top:8px; font-size:0.875rem; color:var(--muted-2); font-weight:300; }

        .count-badge {
            background: rgba(244,63,94,0.12);
            border: 1px solid rgba(244,63,94,0.25);
            border-radius: 14px;
            padding: 14px 22px;
            text-align: center;
            min-width: 80px;
        }

        .count-num {
            font-family:'Playfair Display', serif;
            font-size:2rem; font-weight:600; color:#fff; line-height:1;
        }

        .count-label { font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); margin-top:4px; }

        .section-divider {
            height:1px;
            background:linear-gradient(to right, var(--rose), transparent);
            margin-bottom:32px; opacity:0.3;
        }

        /* ─── Info banner ─── */
        .info-banner {
            background: rgba(244,63,94,0.06);
            border: 1px solid rgba(244,63,94,0.15);
            border-radius: 14px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            animation: fadeUp 0.5s 0.2s cubic-bezier(0.16,1,0.3,1) both;
        }

        .info-banner-icon { font-size: 18px; flex-shrink: 0; }
        .info-banner-text { font-size: 0.82rem; color: var(--muted-2); line-height: 1.5; }
        .info-banner-text strong { color: #fff; font-weight: 600; }

        /* ─── Quiz list ─── */
        .quiz-list { display: flex; flex-direction: column; gap: 14px; }

        .quiz-item {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 22px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            transition: all 0.35s cubic-bezier(0.16,1,0.3,1);
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        .quiz-item:nth-child(1) { animation-delay: 0.25s; }
        .quiz-item:nth-child(2) { animation-delay: 0.32s; }
        .quiz-item:nth-child(3) { animation-delay: 0.39s; }
        .quiz-item:nth-child(4) { animation-delay: 0.46s; }
        .quiz-item:nth-child(5) { animation-delay: 0.53s; }

        .quiz-item::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--rose);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .quiz-item:hover {
            background: var(--surface-h);
            border-color: rgba(244,63,94,0.3);
            transform: translateX(4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.3);
        }

        .quiz-item:hover::before { opacity: 1; }

        .quiz-left { display: flex; align-items: center; gap: 16px; flex:1; min-width:0; }

        .quiz-index {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 10px;
            background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: var(--rose);
            font-family: 'Playfair Display', serif;
        }

        .quiz-info {}

        .quiz-name {
            font-size: 0.95rem; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 400px;
        }

        .quiz-meta {
            display: flex; align-items: center; gap: 8px; margin-top: 4px;
        }

        .live-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--rose);
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity:1; } 50% { opacity:0.2; }
        }

        .quiz-meta-text { font-size: 11px; color: var(--muted); font-weight: 500; }
        .quiz-meta-sep { color: var(--muted); font-size: 11px; }

        .btn-join {
            background: linear-gradient(135deg, var(--rose), #ff6b6b);
            color: #fff;
            padding: 11px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.845rem;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(244,63,94,0.3);
            display: flex; align-items: center; gap: 7px;
        }

        .btn-join:hover {
            transform: scale(1.04);
            box-shadow: 0 8px 24px rgba(244,63,94,0.45);
        }

        .btn-join-arrow { transition: transform 0.25s ease; }
        .btn-join:hover .btn-join-arrow { transform: translateX(3px); }

        /* ─── Empty state ─── */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            animation: fadeUp 0.5s 0.2s cubic-bezier(0.16,1,0.3,1) both;
        }

        .empty-icon {
            font-size: 52px;
            margin-bottom: 20px;
            display: block;
            opacity: 0.5;
        }

        .empty-title {
            font-family:'Playfair Display', serif;
            font-size: 1.4rem; font-weight: 600; color: #fff;
            margin-bottom: 10px;
        }

        .empty-sub {
            font-size: 0.875rem; color: var(--muted-2); line-height: 1.6;
            max-width: 340px; margin: 0 auto 28px;
        }

        .btn-back {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--surface);
            border: 1px solid var(--line);
            color: var(--muted-2);
            padding: 11px 22px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.875rem; font-weight: 500;
            transition: all 0.25s ease;
        }

        .btn-back:hover { background: var(--surface-h); color: #fff; }

        /* ─── Footer ─── */
        .page-footer {
            margin-top: 44px; padding-top: 24px;
            border-top: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
            animation: fadeUp 0.5s 0.6s ease both;
        }

        .footer-brand { font-size: 12px; color: var(--muted); }
        .footer-brand strong { color: var(--gold); font-weight: 600; }

        .footer-links { display:flex; gap:20px; }
        .footer-links a { font-size:12px; color:var(--muted); text-decoration:none; transition:color 0.2s; }
        .footer-links a:hover { color:var(--gold-lt); }

        /* ─── Mobile nav ─── */
        .mobile-nav {
            display:none; position:fixed; bottom:16px; left:50%; transform:translateX(-50%);
            background:rgba(28,36,51,0.92); backdrop-filter:blur(16px);
            padding:10px 28px; border-radius:50px; border:1px solid var(--line);
            gap:28px; z-index:1000;
        }

        .mobile-nav a { color:var(--muted); font-size:20px; text-decoration:none; transition:all 0.2s; display:flex; }
        .mobile-nav a.active { color:var(--rose); filter:drop-shadow(0 0 6px var(--rose)); }

        /* ─── Responsive ─── */
        @media (max-width: 860px) {
            .sidebar { display: none; }
            .mobile-nav { display: flex; }
            .main { padding: 32px 20px 100px; }
            .topbar { flex-direction: column; gap: 14px; }
            .topbar-title { font-size: 1.8rem; }
            .quiz-name { max-width: 200px; }
        }
    </style>
</head>
<body>

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
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <a href="live_quiz_list.php" class="nav-link active">
                <span class="nav-icon">🎮</span> Live Quiz
            </a>
            <a href="events.php" class="nav-link">
                <span class="nav-icon">📅</span> Available Events
            </a>
            <a href="my_bookings.php" class="nav-link">
                <span class="nav-icon">🎟️</span> My Bookings
            </a>
            <a href="profile.php" class="nav-link">
                <span class="nav-icon">👤</span> My Profile
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-link danger">
                <span class="nav-icon">🚪</span> Sign Out
            </a>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-eyebrow">● Live Now &nbsp;·&nbsp; Real-time Competition</div>
                <h1 class="topbar-title">Active Quizzes</h1>
                <p class="topbar-sub">Select an event below to begin your quiz session.</p>
            </div>
            <?php if($total > 0): ?>
            <div class="count-badge">
                <div class="count-num"><?php echo $total; ?></div>
                <div class="count-label">Available</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="section-divider"></div>

        <?php if($total > 0): ?>

        <div class="info-banner">
            <div class="info-banner-icon">ℹ️</div>
            <div class="info-banner-text">
                Only events where you are marked <strong>Present</strong> by the admin are shown here.
                Contact your event coordinator if an event is missing.
            </div>
        </div>

        <div class="quiz-list">
            <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
            <div class="quiz-item">
                <div class="quiz-left">
                    <div class="quiz-index"><?php echo $i; ?></div>
                    <div class="quiz-info">
                        <div class="quiz-name"><?php echo htmlspecialchars($row['event_name']); ?></div>
                        <div class="quiz-meta">
                            <div class="live-dot"></div>
                            <span class="quiz-meta-text">Quiz Active</span>
                            <span class="quiz-meta-sep">·</span>
                            <span class="quiz-meta-text">Attendance Verified</span>
                        </div>
                    </div>
                </div>
                <a href="student_quiz.php?event_id=<?php echo $row['id']; ?>" class="btn-join">
                    Join Now <span class="btn-join-arrow">→</span>
                </a>
            </div>
            <?php $i++; endwhile; ?>
        </div>

        <?php else: ?>

        <div class="empty-state">
            <span class="empty-icon">🎮</span>
            <div class="empty-title">No Active Quizzes Found</div>
            <p class="empty-sub">
                You are not marked as present for any active event.
                Please check with your admin or event coordinator.
            </p>
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>

        <?php endif; ?>

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
        <a href="dashboard.php">🏠</a>
        <a href="live_quiz_list.php" class="active">🎮</a>
        <a href="events.php">📅</a>
        <a href="my_bookings.php">🎟️</a>
        <a href="logout.php">🚪</a>
    </nav>

</body>
</html>