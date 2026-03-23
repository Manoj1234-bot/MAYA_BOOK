<?php
session_start();
include "../includes/db.php";

// 1. Security: Ensure student is logged in
if(!isset($_SESSION['student_id'])){
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// 2. Fetch Student Profile
$student_q = mysqli_query($conn, "SELECT * FROM students WHERE id = '$student_id'");
$user = mysqli_fetch_assoc($student_q);

// 3. Fetch Event History from 'bookings' table
$history_query = "
    SELECT e.event_name, e.event_date, b.is_present, b.id as booking_id
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.student_id = '$student_id'
    ORDER BY e.event_date DESC";
$history_res = mysqli_query($conn, $history_query);

// Pre-count for stats
$total = mysqli_num_rows($history_res);
mysqli_data_seek($history_res, 0);
$present_count = 0;
$tmp = $history_res;
$rows_cache = [];
while($r = mysqli_fetch_assoc($history_res)) {
    if($r['is_present'] == 1) $present_count++;
    $rows_cache[] = $r;
}
$absent_count = $total - $present_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MayaBook — My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:       #0d1117;
            --ink-2:     #1c2433;
            --muted:     #6b7a91;
            --muted-2:   #9aa5b4;
            --line:      rgba(255,255,255,0.07);
            --gold:      #c9a84c;
            --gold-lt:   #e8c96a;
            --teal:      #2dd4bf;
            --rose:      #f43f5e;
            --violet:    #818cf8;
            --success:   #2dd4bf;
            --danger:    #f43f5e;
            --surface:   rgba(255,255,255,0.035);
            --surface-h: rgba(255,255,255,0.065);
            --radius-lg: 20px;
            --radius-xl: 26px;
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

        body::before {
            content:''; position:fixed; inset:0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity:0.025; pointer-events:none; z-index:0;
        }

        .ambient { position:fixed; pointer-events:none; border-radius:50%; filter:blur(90px); z-index:0; }
        .amb-1 { width:480px; height:480px; background:rgba(201,168,76,0.07); top:-80px; right:-60px; }
        .amb-2 { width:360px; height:360px; background:rgba(129,140,248,0.05); bottom:0; left:-60px; }

        /* ─── Sidebar ─── */
        .sidebar {
            width:260px; flex-shrink:0;
            background:var(--ink-2);
            border-right:1px solid var(--line);
            display:flex; flex-direction:column;
            position:sticky; top:0; height:100vh;
            z-index:10;
            animation: slideInLeft 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes slideInLeft {
            from { transform:translateX(-30px); opacity:0; }
            to   { transform:translateX(0);     opacity:1; }
        }

        .sidebar-brand { padding:36px 28px 28px; border-bottom:1px solid var(--line); }
        .brand-mark { display:flex; align-items:center; gap:10px; }
        .brand-icon { width:36px; height:36px; background:linear-gradient(135deg,var(--gold),var(--gold-lt)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .brand-name { font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:600; color:#fff; letter-spacing:0.01em; }
        .brand-name span { color:var(--gold); }
        .brand-sub { margin-top:14px; font-size:11px; font-weight:500; letter-spacing:0.12em; text-transform:uppercase; color:var(--muted); }

        .nav-section { padding:24px 16px 0; flex:1; }
        .nav-label { font-size:10px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:var(--muted); padding:0 12px; margin-bottom:10px; }

        .nav-link {
            display:flex; align-items:center; gap:12px;
            padding:12px 14px; border-radius:12px; margin-bottom:4px;
            text-decoration:none; color:var(--muted-2); font-size:0.875rem; font-weight:500;
            transition:all 0.25s ease; position:relative;
        }
        .nav-link .nav-icon { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; background:transparent; transition:all 0.25s ease; flex-shrink:0; }
        .nav-link:hover { color:#fff; background:var(--surface); }
        .nav-link:hover .nav-icon { background:var(--surface-h); }
        .nav-link.active {
            color:#fff;
            background:linear-gradient(135deg,rgba(201,168,76,0.15),rgba(201,168,76,0.05));
            border:1px solid rgba(201,168,76,0.22);
        }
        .nav-link.active .nav-icon { background:linear-gradient(135deg,var(--gold),var(--gold-lt)); color:var(--ink); }
        .nav-link.active::before { content:''; position:absolute; left:0; top:25%; bottom:25%; width:3px; background:var(--gold); border-radius:0 3px 3px 0; }

        .sidebar-footer { padding:20px 16px; border-top:1px solid var(--line); }
        .nav-link.danger { color:#ff7675; }
        .nav-link.danger:hover { background:rgba(255,118,117,0.08); color:#ff7675; }

        /* ─── Main ─── */
        .main { flex:1; padding:48px 52px; position:relative; z-index:1; }

        @keyframes fadeUp {
            from { transform:translateY(16px); opacity:0; }
            to   { transform:translateY(0);    opacity:1; }
        }

        /* ─── Profile Hero ─── */
        .profile-hero {
            background: var(--ink-2);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            padding: 36px 40px;
            display: flex;
            align-items: center;
            gap: 28px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.55s 0.1s cubic-bezier(0.16,1,0.3,1) both;
        }

        /* Gold top strip */
        .profile-hero::before {
            content:''; position:absolute;
            top:0; left:0; right:0; height:3px;
            background: linear-gradient(to right, var(--gold), var(--gold-lt), transparent);
        }

        .avatar-wrap { position:relative; flex-shrink:0; }

        .avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #a87e2e);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700; color: var(--ink);
            box-shadow: 0 0 0 4px rgba(201,168,76,0.2), 0 8px 24px rgba(0,0,0,0.4);
        }

        .avatar-ring {
            position:absolute; inset:-5px;
            border-radius:50%;
            border:2px solid rgba(201,168,76,0.3);
            animation: spin 8s linear infinite;
        }

        @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

        .profile-info { flex:1; min-width:0; }

        .profile-eyebrow { font-size:10px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:var(--gold); margin-bottom:6px; }

        .profile-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem; font-weight: 600; color: #fff;
            letter-spacing: -0.02em; line-height:1.1; margin-bottom:8px;
        }

        .profile-meta { display:flex; flex-wrap:wrap; gap:16px; }

        .meta-item {
            display:flex; align-items:center; gap:7px;
            background: var(--surface); border:1px solid var(--line);
            border-radius:8px; padding:6px 12px;
        }
        .meta-item-label { font-size:9px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); }
        .meta-item-val { font-size:12.5px; font-weight:600; color:#fff; }

        /* ─── Stats row ─── */
        .stats-row {
            display:grid; grid-template-columns:repeat(3,1fr); gap:16px;
            margin-bottom:28px;
            animation: fadeUp 0.55s 0.2s cubic-bezier(0.16,1,0.3,1) both;
        }

        .stat-card {
            background:var(--surface); border:1px solid var(--line);
            border-radius:var(--radius-lg); padding:22px 24px;
            display:flex; align-items:center; gap:16px;
        }

        .stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .stat-val { font-family:'Playfair Display',serif; font-size:1.9rem; font-weight:600; color:#fff; line-height:1; }
        .stat-label { font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); margin-top:4px; }

        /* ─── History table card ─── */
        .history-card {
            background: var(--ink-2);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            overflow: hidden;
            animation: fadeUp 0.55s 0.3s cubic-bezier(0.16,1,0.3,1) both;
        }

        .history-head {
            padding: 26px 32px 22px;
            border-bottom: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
        }

        .history-title { font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:600; color:#fff; }
        .history-sub { font-size:12px; color:var(--muted-2); margin-top:3px; font-weight:300; }

        .section-line { height:3px; width:40px; background:linear-gradient(to right,var(--gold),var(--gold-lt)); border-radius:10px; margin-top:10px; }

        /* Table */
        .table-wrap { overflow-x:auto; }

        table { width:100%; border-collapse:collapse; }

        thead tr { background:rgba(255,255,255,0.02); }

        th {
            text-align:left; padding:14px 24px;
            font-size:10px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--muted);
            border-bottom:1px solid var(--line);
            white-space:nowrap;
        }

        tbody tr {
            border-bottom:1px solid var(--line);
            transition: background 0.2s;
        }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:var(--surface); }

        td { padding:16px 24px; font-size:13.5px; vertical-align:middle; }

        .event-name-cell { font-weight:600; color:#fff; }
        .date-cell { color:var(--muted-2); font-size:12.5px; white-space:nowrap; }

        /* Attendance badges */
        .badge {
            display:inline-flex; align-items:center; gap:5px;
            padding:5px 12px; border-radius:8px;
            font-size:10.5px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase;
        }
        .bg-present { background:rgba(45,212,191,0.1);  color:var(--success); border:1px solid rgba(45,212,191,0.25); }
        .bg-absent  { background:rgba(244,63,94,0.1);   color:var(--danger);  border:1px solid rgba(244,63,94,0.25); }

        /* Cert button */
        .btn-cert {
            display:inline-flex; align-items:center; gap:6px;
            background:linear-gradient(135deg,var(--teal),#34d399);
            color:var(--ink); padding:8px 16px;
            border-radius:9px; text-decoration:none;
            font-weight:700; font-size:12px;
            transition:all 0.25s ease;
            box-shadow:0 3px 12px rgba(45,212,191,0.2);
        }
        .btn-cert:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(45,212,191,0.35); }

        .locked { color:var(--muted); font-size:12px; font-style:italic; }

        /* Empty row */
        .empty-row td { text-align:center; padding:52px; color:var(--muted); }
        .empty-row-icon { font-size:32px; display:block; opacity:0.4; margin-bottom:10px; }
        .empty-row-text { font-size:13px; }

        /* ─── Footer ─── */
        .page-footer { margin-top:36px; padding-top:24px; border-top:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; animation:fadeUp 0.5s 0.6s ease both; }
        .footer-brand { font-size:12px; color:var(--muted); }
        .footer-brand strong { color:var(--gold); font-weight:600; }
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
        .mobile-nav a.active { color:var(--gold); filter:drop-shadow(0 0 6px var(--gold)); }

        @media (max-width:900px) {
            .sidebar { display:none; }
            .mobile-nav { display:flex; }
            .main { padding:32px 20px 100px; }
            .profile-hero { flex-direction:column; text-align:center; padding:28px 24px; }
            .profile-meta { justify-content:center; }
            .stats-row { grid-template-columns:1fr 1fr; }
            th, td { padding:12px 16px; }
        }

        @media (max-width:500px) {
            .stats-row { grid-template-columns:1fr; }
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
        <div class="brand-sub">Student Information Portal</div>
    </div>
    <nav class="nav-section">
        <div class="nav-label">Navigation</div>
        <a href="dashboard.php" class="nav-link"><span class="nav-icon">🏠</span> Dashboard</a>
        <a href="live_quiz_list.php" class="nav-link"><span class="nav-icon">🎮</span> Live Quiz</a>
        <a href="events.php" class="nav-link"><span class="nav-icon">📅</span> Available Events</a>
        <a href="my_bookings.php" class="nav-link"><span class="nav-icon">🎟️</span> My Bookings</a>
        <a href="profile.php" class="nav-link active"><span class="nav-icon">👤</span> My Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link danger"><span class="nav-icon">🚪</span> Sign Out</a>
    </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="main">

    <!-- Profile Hero -->
    <div class="profile-hero">
        <div class="avatar-wrap">
            <div class="avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <div class="avatar-ring"></div>
        </div>
        <div class="profile-info">
            <div class="profile-eyebrow">👤 Student Profile</div>
            <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
            <div class="profile-meta">
                <div class="meta-item">
                    <div>
                        <div class="meta-item-label">Reg No.</div>
                        <div class="meta-item-val"><?php echo htmlspecialchars($user['reg_no']); ?></div>
                    </div>
                </div>
                <div class="meta-item">
                    <div>
                        <div class="meta-item-label">Department</div>
                        <div class="meta-item-val"><?php echo htmlspecialchars($user['department']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(201,168,76,0.1);">📋</div>
            <div>
                <div class="stat-val"><?php echo $total; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(45,212,191,0.1);">✅</div>
            <div>
                <div class="stat-val"><?php echo $present_count; ?></div>
                <div class="stat-label">Attended</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(244,63,94,0.1);">❌</div>
            <div>
                <div class="stat-val"><?php echo $absent_count; ?></div>
                <div class="stat-label">Absent</div>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="history-card">
        <div class="history-head">
            <div>
                <div class="history-title">Event Attendance &amp; Certificates</div>
                <div class="history-sub">Your historical record of seminar participation.</div>
                <div class="section-line"></div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Attendance</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($rows_cache) > 0): ?>
                        <?php foreach($rows_cache as $row): ?>
                        <tr>
                            <td class="event-name-cell"><?php echo htmlspecialchars($row['event_name']); ?></td>
                            <td class="date-cell"><?php echo date('d M, Y', strtotime($row['event_date'])); ?></td>
                            <td>
                                <?php if($row['is_present'] == 1): ?>
                                    <span class="badge bg-present">✓ Present</span>
                                <?php else: ?>
                                    <span class="badge bg-absent">✕ Absent</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['is_present'] == 1): ?>
                                    <a href="certificate.php?id=<?php echo $row['booking_id']; ?>" class="btn-cert">📜 Download</a>
                                <?php else: ?>
                                    <span class="locked">Not Available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row">
                            <td colspan="4">
                                <span class="empty-row-icon">📋</span>
                                <span class="empty-row-text">No event records found.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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
    <a href="live_quiz_list.php">🎮</a>
    <a href="events.php">📅</a>
    <a href="my_bookings.php">🎟️</a>
    <a href="profile.php" class="active">👤</a>
</nav>

</body>
</html>