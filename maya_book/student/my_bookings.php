<?php
session_start();
include "../includes/db.php";

// 1. Security Check: Only logged-in students
if(!isset($_SESSION['student_id'])){
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// 2. Fetch all bookings with the new 'rating' column
$query = "SELECT bookings.id as booking_id, 
                 bookings.status, 
                 bookings.is_present,
                 bookings.rating, 
                 events.id as event_id, 
                 events.event_name, 
                 events.event_date, 
                 events.venue, 
                 events.has_certificate, 
                 bookings.seat_no, 
                 bookings.booking_ref 
          FROM bookings 
          JOIN events ON bookings.event_id = events.id 
          WHERE bookings.student_id = '$student_id'
          ORDER BY events.event_date DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Query Failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MayaBook — My Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=Playfair+Display:wght@500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
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
            --amber:     #f59e0b;
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
        .amb-1 { width:500px; height:500px; background:rgba(201,168,76,0.06); top:-100px; right:-60px; }
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
        .main { flex:1; padding:48px 52px; position:relative; z-index:1; max-width:900px; }

        /* ─── Topbar ─── */
        .topbar { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:40px; animation:fadeUp 0.55s 0.1s cubic-bezier(0.16,1,0.3,1) both; }

        @keyframes fadeUp {
            from { transform:translateY(16px); opacity:0; }
            to   { transform:translateY(0);    opacity:1; }
        }

        .topbar-eyebrow { font-size:11px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:var(--gold); margin-bottom:6px; }
        .topbar-title { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:600; color:#fff; line-height:1.1; letter-spacing:-0.02em; }
        .topbar-sub { margin-top:8px; font-size:0.875rem; color:var(--muted-2); font-weight:300; }

        .count-pill {
            background:rgba(201,168,76,0.1); border:1px solid rgba(201,168,76,0.22);
            border-radius:40px; padding:10px 20px;
            display:flex; align-items:center; gap:8px;
        }
        .count-pill-num { font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:600; color:#fff; }
        .count-pill-label { font-size:11px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--muted); }

        .section-divider { height:1px; background:linear-gradient(to right,var(--gold),transparent); margin-bottom:32px; opacity:0.3; }

        /* ─── Ticket Card ─── */
        .ticket {
            background: var(--ink-2);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            overflow: hidden;
            margin-bottom: 20px;
            display: flex;
            transition: all 0.35s cubic-bezier(0.16,1,0.3,1);
            position: relative;
            animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        .ticket:nth-child(1) { animation-delay:0.18s; }
        .ticket:nth-child(2) { animation-delay:0.25s; }
        .ticket:nth-child(3) { animation-delay:0.32s; }
        .ticket:nth-child(4) { animation-delay:0.39s; }
        .ticket:nth-child(5) { animation-delay:0.46s; }

        .ticket:hover {
            transform: translateY(-4px);
            border-color: rgba(201,168,76,0.2);
            box-shadow: 0 16px 40px rgba(0,0,0,0.4);
        }

        .ticket.pending-ticket {
            border-color: rgba(245,158,11,0.2);
            opacity: 0.9;
        }

        /* Date column */
        .ticket-date {
            width: 88px;
            flex-shrink: 0;
            background: var(--date-bg, linear-gradient(160deg, var(--gold), #a87e2e));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 12px;
            position: relative;
        }

        /* Dashed separator */
        .ticket-date::after {
            content: '';
            position: absolute;
            right: -1px; top: 12%; bottom: 12%;
            width: 0;
            border-right: 2px dashed rgba(255,255,255,0.12);
        }

        .date-month { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.75); }
        .date-day   { font-family:'Playfair Display',serif; font-size: 2.2rem; font-weight: 700; color: #fff; line-height: 1; margin: 4px 0; }
        .date-year  { font-size: 10px; color: rgba(255,255,255,0.6); font-weight: 500; }

        /* Main ticket body */
        .ticket-body {
            flex: 1;
            padding: 22px 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .ticket-info { flex: 1; min-width: 0; }

        .ticket-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem; font-weight: 600; color: #fff;
            margin-bottom: 4px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .ticket-venue { font-size: 12.5px; color: var(--muted-2); margin-bottom: 14px; display:flex; align-items:center; gap:5px; }

        /* Ref code box */
        .ref-box {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 14px;
            display: inline-flex;
            flex-direction: column;
            margin-bottom: 12px;
        }
        .ref-label { font-size: 9px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); margin-bottom: 2px; }
        .ref-code  { font-family: 'DM Mono', monospace; font-size: 13px; font-weight: 500; color: #fff; letter-spacing: 0.05em; }

        /* Star rating */
        .rate-label { font-size: 11px; color: var(--muted); font-weight: 600; display:block; margin-bottom:6px; }
        .star-rating { display:flex; gap:6px; }
        .star-rating i { color: rgba(255,255,255,0.15); cursor:pointer; font-size:20px; transition:all 0.2s; }
        .star-rating i:hover, .star-rating i.hover { color:#f1c40f; transform:scale(1.2); }
        .star-rating i.active { color:#f1c40f; }

        /* Download cert btn */
        .download-btn-style {
            display:inline-flex; align-items:center; gap:7px;
            background: linear-gradient(135deg, var(--teal), #34d399);
            color: var(--ink); padding:10px 18px;
            border-radius:10px; text-decoration:none;
            font-weight:700; font-size:12.5px;
            transition:all 0.25s ease;
            box-shadow: 0 4px 16px rgba(45,212,191,0.25);
            margin-top:4px;
        }
        .download-btn-style:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(45,212,191,0.4); }

        /* Status pills */
        .status-pill {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 14px; border-radius:10px;
            font-size:12px; font-weight:600;
        }
        .status-pending  { background:rgba(245,158,11,0.1);  color:var(--amber); border:1px solid rgba(245,158,11,0.25); }
        .status-rejected { background:rgba(244,63,94,0.1);   color:var(--rose);  border:1px solid rgba(244,63,94,0.25); }
        .status-rated    { background:rgba(45,212,191,0.08); color:var(--teal);  border:1px solid rgba(45,212,191,0.2); font-size:11px; }

        /* Seat box */
        .ticket-seat { display:flex; flex-direction:column; align-items:center; flex-shrink:0; }
        .seat-label-txt { font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
        .seat-num {
            width:52px; height:52px; border-radius:14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--line);
            display:flex; align-items:center; justify-content:center;
            font-family:'Playfair Display',serif;
            font-size:1.3rem; font-weight:700; color:#fff;
        }
        .seat-unknown { color: var(--muted); font-size:1.5rem; }

        /* ─── Empty state ─── */
        .empty-state {
            text-align:center; padding:70px 40px;
            animation:fadeUp 0.5s 0.2s cubic-bezier(0.16,1,0.3,1) both;
        }
        .empty-icon { font-size:52px; display:block; opacity:0.4; margin-bottom:18px; }
        .empty-title { font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:600; color:#fff; margin-bottom:8px; }
        .empty-sub { font-size:0.875rem; color:var(--muted-2); line-height:1.6; margin-bottom:26px; }
        .btn-find {
            display:inline-flex; align-items:center; gap:8px;
            background:linear-gradient(135deg,var(--gold),#a87e2e);
            color:#fff; padding:13px 26px; border-radius:12px;
            text-decoration:none; font-weight:600; font-size:0.875rem;
            transition:all 0.25s ease;
            box-shadow:0 4px 16px rgba(201,168,76,0.3);
        }
        .btn-find:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(201,168,76,0.4); }

        /* ─── Footer ─── */
        .page-footer { margin-top:44px; padding-top:24px; border-top:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; animation:fadeUp 0.5s 0.7s ease both; }
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

        @media (max-width:860px) {
            .sidebar { display:none; }
            .mobile-nav { display:flex; }
            .main { padding:32px 20px 100px; }
            .topbar { flex-direction:column; gap:14px; align-items:flex-start; }
            .topbar-title { font-size:1.8rem; }
            .ticket { flex-direction:column; }
            .ticket-date { flex-direction:row; width:100%; padding:14px 20px; gap:12px; justify-content:flex-start; }
            .ticket-date::after { display:none; }
            .ticket-body { flex-direction:column; align-items:flex-start; }
            .star-rating { justify-content:flex-start; }
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
        <a href="my_bookings.php" class="nav-link active"><span class="nav-icon">🎟️</span> My Bookings</a>
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
            <div class="topbar-eyebrow">🎟️ Registration History</div>
            <h1 class="topbar-title">My Tickets</h1>
            <p class="topbar-sub">All your confirmed event registrations.</p>
        </div>
        <div class="count-pill">
            <span class="count-pill-num"><?php echo mysqli_num_rows($result); ?></span>
            <span class="count-pill-label">Registrations</span>
        </div>
    </div>

    <div class="section-divider"></div>

    <?php if(mysqli_num_rows($result) > 0): ?>

        <?php while($row = mysqli_fetch_assoc($result)): 
            $date   = strtotime($row['event_date']);
            $status = $row['status'];
            $rating = $row['rating'] ?? 0;

            // Date gradient by status
            if ($status == 'pending')  $date_bg = 'linear-gradient(160deg,#f59e0b,#d97706)';
            elseif ($status == 'rejected') $date_bg = 'linear-gradient(160deg,#f43f5e,#be123c)';
            else $date_bg = 'linear-gradient(160deg,var(--gold),#a87e2e)';
        ?>

        <div class="ticket <?php echo ($status=='pending') ? 'pending-ticket' : ''; ?>">

            <!-- Date column -->
            <div class="ticket-date" style="--date-bg: <?php echo $date_bg; ?>; background: <?php echo $date_bg; ?>">
                <div class="date-month"><?php echo date("M", $date); ?></div>
                <div class="date-day"><?php echo date("d", $date); ?></div>
                <div class="date-year"><?php echo date("Y", $date); ?></div>
            </div>

            <!-- Body -->
            <div class="ticket-body">
                <div class="ticket-info">
                    <div class="ticket-name"><?php echo htmlspecialchars($row['event_name']); ?></div>
                    <div class="ticket-venue">📍 <?php echo htmlspecialchars($row['venue']); ?></div>

                    <?php if($status == 'approved'): ?>
                        <div class="ref-box">
                            <span class="ref-label">Reference Code</span>
                            <span class="ref-code"><?php echo htmlspecialchars($row['booking_ref']); ?></span>
                        </div>

                        <?php if($row['is_present'] == 1): ?>
                            <div id="action-area-<?php echo $row['booking_id']; ?>" style="margin-top:10px;">
                                <?php if($rating == 0): ?>
                                    <span class="rate-label">How was the event? Rate it:</span>
                                    <div class="star-rating">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="fa-regular fa-star" onclick="submitRating(<?php echo $row['booking_id']; ?>, <?php echo $i; ?>)"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="status-rated">⭐ Rated <?php echo $rating; ?>/5 — Thank you!</div>

                                    <?php if($row['has_certificate'] == 1): ?>
                                        <a href="certificate.php?id=<?php echo $row['booking_id']; ?>" class="download-btn-style">
                                            🏆 Download Certificate
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:10px; color:var(--muted); font-style:italic; display:block; margin-top:6px;">
                                            Participation certificate not applicable for this event.
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php elseif($status == 'pending'): ?>
                        <div class="status-pill status-pending">⏳ Waiting for Approval</div>

                    <?php else: ?>
                        <div class="status-pill status-rejected">❌ Request Rejected</div>
                    <?php endif; ?>
                </div>

                <!-- Seat -->
                <div class="ticket-seat">
                    <div class="seat-label-txt">Seat</div>
                    <div class="seat-num">
                        <?php if($status == 'approved'): ?>
                            <?php echo htmlspecialchars($row['seat_no']); ?>
                        <?php else: ?>
                            <span class="seat-unknown">?</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php endwhile; ?>

    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">🎟️</span>
            <div class="empty-title">No Bookings Yet</div>
            <p class="empty-sub">You haven't reserved any seats. Browse available events and secure your spot.</p>
            <a href="events.php" class="btn-find">Browse Events →</a>
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
    <a href="live_quiz_list.php">🎮</a>
    <a href="events.php">📅</a>
    <a href="my_bookings.php" class="active">🎟️</a>
    <a href="logout.php">🚪</a>
</nav>

<script>
function submitRating(bookingId, stars) {
    // Send data to save_rating.php using AJAX
    fetch('save_rating.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `booking_id=${bookingId}&stars=${stars}`
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === 'success') {
            const container = document.getElementById(`action-area-${bookingId}`);
            // Replace stars with download button immediately
            container.style.opacity = '0';
            setTimeout(() => {
                container.innerHTML = `
                    <a href="certificate.php?id=${bookingId}" class="download-btn-style" style="animation: slideIn 0.3s ease-out;">
                        🏆 Download Certificate
                    </a>
                    <div style="font-size: 10px; color: #27ae60; margin-top: 5px; font-weight: 600;">Thank you for your ${stars}⭐ rating!</div>
                `;
                container.style.opacity = '1';
                container.style.transition = '0.5s';
            }, 300);
        } else {
            alert("Error saving rating. Please try again.");
        }
    })
    .catch(err => console.error(err));
}
</script>

</body>
</html>