<?php
session_start();
include "../includes/db.php";

// 1. Security Check: Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
date_default_timezone_set('Asia/Kolkata');

// 2. Fetch Student Info
$student_query = mysqli_query($conn, "SELECT * FROM students WHERE id='$student_id'");
$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    echo "Student record not found.";
    exit();
}

$dept = $student['department'];

// 3. Fetch Events assigned to this student's department (Handles comma-separated values)
$query = "SELECT * FROM events WHERE FIND_IN_SET('$dept', department) OR department='All' ORDER BY event_date ASC";
$result = mysqli_query($conn, $query);
$total_events = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MayaBook — Available Events</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:        #0d1117;
            --ink-2:      #1c2433;
            --muted:      #6b7a91;
            --muted-2:    #9aa5b4;
            --line:       rgba(255,255,255,0.07);
            --gold:       #c9a84c;
            --gold-lt:    #e8c96a;
            --teal:       #2dd4bf;
            --rose:       #f43f5e;
            --violet:     #818cf8;
            --surface:    rgba(255,255,255,0.035);
            --surface-h:  rgba(255,255,255,0.065);
            --success:    #2dd4bf;
            --danger:     #f43f5e;
            --warning:    #f59e0b;
            --info:       #818cf8;
            --radius-lg:  20px;
            --radius-xl:  26px;
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
            content:''; position:fixed; inset:0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity:0.025; pointer-events:none; z-index:0;
        }

        .ambient { position:fixed; pointer-events:none; border-radius:50%; filter:blur(90px); z-index:0; }
        .amb-1 { width:500px; height:500px; background:rgba(129,140,248,0.06); top:-100px; right:-60px; }
        .amb-2 { width:380px; height:380px; background:rgba(45,212,191,0.05); bottom:0; left:-80px; }

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
        .brand-icon {
            width:36px; height:36px;
            background:linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px;
        }
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
            background:linear-gradient(135deg, rgba(129,140,248,0.15), rgba(129,140,248,0.05));
            border:1px solid rgba(129,140,248,0.22);
        }
        .nav-link.active .nav-icon { background:linear-gradient(135deg,#818cf8,#a5b4fc); color:#fff; }
        .nav-link.active::before { content:''; position:absolute; left:0; top:25%; bottom:25%; width:3px; background:var(--violet); border-radius:0 3px 3px 0; }

        .sidebar-footer { padding:20px 16px; border-top:1px solid var(--line); }
        .nav-link.danger { color:#ff7675; }
        .nav-link.danger:hover { background:rgba(255,118,117,0.08); color:#ff7675; }

        /* ─── Main ─── */
        .main { flex:1; padding:48px 52px; position:relative; z-index:1; }

        /* ─── Topbar ─── */
        .topbar {
            display:flex; align-items:flex-start; justify-content:space-between;
            margin-bottom:40px;
            animation:fadeUp 0.55s 0.1s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes fadeUp {
            from { transform:translateY(16px); opacity:0; }
            to   { transform:translateY(0);    opacity:1; }
        }

        .topbar-eyebrow { font-size:11px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:var(--violet); margin-bottom:6px; }
        .topbar-title { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:600; color:#fff; line-height:1.1; letter-spacing:-0.02em; }
        .topbar-sub { margin-top:8px; font-size:0.875rem; color:var(--muted-2); font-weight:300; }

        .dept-badge {
            display:flex; align-items:center; gap:10px;
            background:rgba(129,140,248,0.1);
            border:1px solid rgba(129,140,248,0.22);
            border-radius:14px; padding:14px 20px;
        }
        .dept-badge-icon { font-size:20px; }
        .dept-badge-label { font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); }
        .dept-badge-name { font-size:0.95rem; font-weight:600; color:#fff; }

        .section-divider { height:1px; background:linear-gradient(to right,var(--violet),transparent); margin-bottom:32px; opacity:0.3; }

        /* ─── Event Grid ─── */
        .event-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));
            gap:20px;
        }

        .card {
            background:var(--surface);
            border:1px solid var(--line);
            border-radius:var(--radius-xl);
            display:flex; flex-direction:column;
            position:relative; overflow:hidden;
            transition:all 0.35s cubic-bezier(0.16,1,0.3,1);
            animation:fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        .card:nth-child(1) { animation-delay:0.18s; }
        .card:nth-child(2) { animation-delay:0.24s; }
        .card:nth-child(3) { animation-delay:0.30s; }
        .card:nth-child(4) { animation-delay:0.36s; }
        .card:nth-child(5) { animation-delay:0.42s; }
        .card:nth-child(6) { animation-delay:0.48s; }

        .card:hover {
            transform:translateY(-6px);
            background:var(--surface-h);
            border-color:rgba(129,140,248,0.25);
            box-shadow:0 20px 48px rgba(0,0,0,0.35);
        }

        /* Top color strip */
        .card-strip {
            height:3px;
            background:linear-gradient(to right, var(--strip-a, var(--violet)), var(--strip-b, #a5b4fc));
        }

        .card-body { padding:26px 26px 20px; flex:1; }

        /* Deadline corner badge */
        .deadline-badge {
            position:absolute; top:12px; right:12px;
            background:rgba(244,63,94,0.15);
            border:1px solid rgba(244,63,94,0.3);
            color:var(--rose);
            font-size:9px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase;
            padding:4px 10px; border-radius:20px;
        }

        .card-title { font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:600; color:#fff; margin-bottom:16px; line-height:1.3; padding-right:60px; }

        .info-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; font-size:12.5px; color:var(--muted-2); }
        .info-row .info-icon { font-size:14px; width:20px; text-align:center; flex-shrink:0; }
        .info-row .info-text { font-weight:400; }

        /* Seat bar */
        .seat-bar-wrap { margin-top:14px; }
        .seat-bar-label { display:flex; justify-content:space-between; font-size:11px; color:var(--muted); margin-bottom:5px; }
        .seat-bar-track { height:4px; background:rgba(255,255,255,0.08); border-radius:10px; overflow:hidden; }
        .seat-bar-fill { height:100%; border-radius:10px; background:linear-gradient(to right, var(--teal), #34d399); transition:width 0.6s ease; }
        .seat-bar-fill.low { background:linear-gradient(to right, var(--warning), #fbbf24); }
        .seat-bar-fill.none { background:linear-gradient(to right, var(--rose), #fb7185); }

        /* Action area */
        .card-footer { padding:0 26px 22px; }

        .status-badge {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:12px; border-radius:12px;
            font-weight:600; font-size:12.5px;
        }

        .booked    { background:rgba(45,212,191,0.08);  color:var(--success); border:1px solid rgba(45,212,191,0.25); }
        .pending   { background:rgba(129,140,248,0.08); color:var(--info);    border:1px solid rgba(129,140,248,0.25); }
        .closed    { background:rgba(244,63,94,0.08);   color:var(--danger);  border:1px solid rgba(244,63,94,0.25); }
        .waitlist  { background:rgba(245,158,11,0.08);  color:var(--warning); border:1px solid rgba(245,158,11,0.25); }

        .cancel-link {
            color:rgba(244,63,94,0.6); font-size:11px; text-decoration:none;
            display:block; text-align:center; margin-top:8px;
            transition:color 0.2s;
        }
        .cancel-link:hover { color:var(--rose); }

        .btn-action {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:13px;
            background:linear-gradient(135deg, var(--violet), #a5b4fc);
            color:white; text-decoration:none;
            border-radius:12px; font-weight:600; font-size:13px;
            transition:all 0.25s ease;
            box-shadow:0 4px 16px rgba(129,140,248,0.25);
        }
        .btn-action:hover { transform:scale(1.02); box-shadow:0 8px 24px rgba(129,140,248,0.4); }

        .btn-waitlist {
            background:linear-gradient(135deg, var(--warning), #fbbf24);
            box-shadow:0 4px 16px rgba(245,158,11,0.25);
        }
        .btn-waitlist:hover { box-shadow:0 8px 24px rgba(245,158,11,0.4); }

        /* ─── Empty state ─── */
        .empty-state {
            grid-column:1/-1; text-align:center; padding:70px 40px;
            animation:fadeUp 0.5s 0.2s cubic-bezier(0.16,1,0.3,1) both;
        }
        .empty-icon { font-size:52px; display:block; opacity:0.4; margin-bottom:18px; }
        .empty-title { font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:600; color:#fff; margin-bottom:8px; }
        .empty-sub { font-size:0.875rem; color:var(--muted-2); line-height:1.6; }

        /* ─── Footer ─── */
        .page-footer {
            margin-top:44px; padding-top:24px; border-top:1px solid var(--line);
            display:flex; align-items:center; justify-content:space-between;
            animation:fadeUp 0.5s 0.7s ease both;
        }
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
        .mobile-nav a.active { color:var(--violet); filter:drop-shadow(0 0 6px var(--violet)); }

        @media (max-width:860px) {
            .sidebar { display:none; }
            .mobile-nav { display:flex; }
            .main { padding:32px 20px 100px; }
            .topbar { flex-direction:column; gap:14px; }
            .topbar-title { font-size:1.8rem; }
            .event-grid { grid-template-columns:1fr; }
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
        <a href="events.php" class="nav-link active"><span class="nav-icon">📅</span> Available Events</a>
        <a href="my_bookings.php" class="nav-link"><span class="nav-icon">🎟️</span> My Bookings</a>
        <a href="profile.php" class="nav-link"><span class="nav-icon">👤</span> My Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link danger"><span class="nav-icon">🚪</span> Sign Out</a>
    </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="main">

    <div class="topbar">
        <div>
            <div class="topbar-eyebrow">📅 Seminars &amp; Workshops</div>
            <h1 class="topbar-title">Available Events</h1>
            <p class="topbar-sub">Secure your seat for upcoming auditorium seminars.</p>
        </div>
        <div class="dept-badge">
            <div class="dept-badge-icon">🏛️</div>
            <div>
                <div class="dept-badge-label">Department</div>
                <div class="dept-badge-name"><?php echo htmlspecialchars($dept); ?></div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <div class="event-grid">
    <?php if($total_events > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): 
            $event_id = $row['id'];

            // 1. Check Deadlines
            $current_time = time();
            $deadline_str = $row['booking_deadline'] . ' ' . $row['booking_time'];
            $deadline_time = strtotime($deadline_str);
            $is_expired = ($current_time > $deadline_time);

            // 2. Check Booking Status
            $check_booked = mysqli_query($conn, "SELECT id, status FROM bookings WHERE event_id='$event_id' AND student_id='$student_id'");
            $booking_data = mysqli_fetch_assoc($check_booked);
            $is_booked = mysqli_num_rows($check_booked) > 0;

            // 3. Check Waitlist Status
            $check_waitlist = mysqli_query($conn, "SELECT id FROM waitlist WHERE event_id='$event_id' AND student_id='$student_id'");
            $is_waiting = mysqli_num_rows($check_waitlist) > 0;

            // 4. Calculate Availability
            $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE event_id='$event_id'");
            $count_data = mysqli_fetch_assoc($count_query);
            $remaining = $row['total_seats'] - $count_data['total'];

            // Seat bar calculations
            $filled = $count_data['total'];
            $total_seats = $row['total_seats'];
            $fill_pct = $total_seats > 0 ? round(($filled / $total_seats) * 100) : 0;
            $bar_class = $remaining <= 0 ? 'none' : ($remaining <= 5 ? 'low' : '');

            // Strip color based on status
            if ($is_booked)       { $strip_a = '#2dd4bf'; $strip_b = '#34d399'; }
            elseif ($is_waiting)  { $strip_a = '#f59e0b'; $strip_b = '#fbbf24'; }
            elseif ($is_expired)  { $strip_a = '#f43f5e'; $strip_b = '#fb7185'; }
            elseif ($remaining<=0){ $strip_a = '#f59e0b'; $strip_b = '#fbbf24'; }
            else                  { $strip_a = '#818cf8'; $strip_b = '#a5b4fc'; }
        ?>

        <div class="card" style="--strip-a:<?php echo $strip_a; ?>; --strip-b:<?php echo $strip_b; ?>;">
            <div class="card-strip"></div>

            <?php if($is_expired): ?>
                <div class="deadline-badge">Registration Closed</div>
            <?php endif; ?>

            <div class="card-body">
                <div class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></div>

                <div class="info-row">
                    <span class="info-icon">📅</span>
                    <span class="info-text"><?php echo date("D, M j, Y", strtotime($row['booking_deadline'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">⏰</span>
                    <span class="info-text">Deadline: <?php echo date("h:i A", strtotime($row['booking_time'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">🪑</span>
                    <span class="info-text"><?php echo max(0, $remaining); ?> of <?php echo $total_seats; ?> seats remaining</span>
                </div>

                <div class="seat-bar-wrap">
                    <div class="seat-bar-label">
                        <span>Seat Availability</span>
                        <span><?php echo max(0,100-$fill_pct); ?>% free</span>
                    </div>
                    <div class="seat-bar-track">
                        <div class="seat-bar-fill <?php echo $bar_class; ?>" style="width:<?php echo min(100,$fill_pct); ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <?php if($is_booked): ?>
                    <?php if($booking_data['status'] == 'approved' || $row['approval_required'] == 1): ?>
                        <div class="status-badge booked">✓ &nbsp;Booking Confirmed</div>
                    <?php else: ?>
                        <div class="status-badge pending">⏳ &nbsp;Approval Pending</div>
                    <?php endif; ?>
                    <a href="cancel_booking.php?event_id=<?php echo $event_id; ?>&booking_id=<?php echo $booking_data['id']; ?>"
                       class="cancel-link"
                       onclick="return confirm('Cancel this booking?')">Cancel My Request</a>

                <?php elseif($is_waiting): ?>
                    <div class="status-badge waitlist">📋 &nbsp;You Are on Waitlist</div>

                <?php elseif($is_expired): ?>
                    <div class="status-badge closed">🔒 &nbsp;Registration Closed</div>

                <?php elseif($remaining <= 0): ?>
                    <a href="join_waitlist.php?event_id=<?php echo $event_id; ?>" class="btn-action btn-waitlist">📋 &nbsp;Join Waitlist</a>

                <?php else: ?>
                    <a href="book_seat.php?event_id=<?php echo $event_id; ?>" class="btn-action">Book My Seat &nbsp;→</a>
                <?php endif; ?>
            </div>
        </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">📅</span>
            <div class="empty-title">No Events Available</div>
            <p class="empty-sub">No events are currently listed for your department. Check back later.</p>
        </div>
    <?php endif; ?>
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
    <a href="events.php" class="active">📅</a>
    <a href="my_bookings.php">🎟️</a>
    <a href="logout.php">🚪</a>
</nav>

</body>
</html>