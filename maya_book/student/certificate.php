<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['student_id'])){
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$booking_id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Fetch data including the admin's custom certificate content
$query = "SELECT s.name as student_name, e.event_name, e.event_date, e.has_certificate, e.certificate_content, b.is_present 
          FROM bookings b 
          JOIN students s ON b.student_id = s.id 
          JOIN events e ON b.event_id = e.id 
          WHERE b.id = '$booking_id' AND b.student_id = '$student_id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// 2. Security Check
if(!$data || $data['is_present'] == 0 || $data['has_certificate'] == 0){
    die("<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <link href='https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@600&display=swap' rel='stylesheet'>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0d1117;font-family:'DM Sans',sans-serif;color:#e2e8f0;}
        .box{text-align:center;padding:60px 48px;background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.07);border-radius:24px;max-width:420px;}
        .icon{font-size:48px;margin-bottom:20px;display:block;}
        h2{font-family:'Playfair Display',serif;font-size:1.5rem;color:#fff;margin-bottom:10px;}
        p{color:#9aa5b4;font-size:14px;line-height:1.6;margin-bottom:28px;}
        a{display:inline-block;background:linear-gradient(135deg,#c9a84c,#a87e2e);color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:600;font-size:14px;}
    </style></head><body>
    <div class='box'>
        <span class='icon'>🔒</span>
        <h2>Certificate Not Available</h2>
        <p>Either you were not marked <strong style='color:#fff'>Present</strong> or this event does not provide a certificate.</p>
        <a href='dashboard.php'>← Back to Dashboard</a>
    </div></body></html>");
}

// 3. Replace placeholders in the admin's custom text
$custom_text = $data['certificate_content'];
$placeholders = ['[NAME]', '[EVENT]', '[DATE]'];
$replacements = [
    htmlspecialchars($data['student_name']),
    htmlspecialchars($data['event_name']),
    date("F d, Y", strtotime($data['event_date']))
];
$final_content = str_replace($placeholders, $replacements, $custom_text);

// Fallback if content is empty
if(empty($final_content)) {
    $final_content = "For successfully participating in the event <strong>" . htmlspecialchars($data['event_name']) . "</strong> held on " . date("F d, Y", strtotime($data['event_date'])) . ".";
}

$student_name  = htmlspecialchars($data['student_name']);
$event_name    = htmlspecialchars($data['event_name']);
$event_date    = date("F d, Y", strtotime($data['event_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate — <?php echo $student_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>

        /* ═══════════════════════════════════════════
           SCREEN STYLES — preview wrapper
        ═══════════════════════════════════════════ */
        :root {
            --gold:    #b8972a;
            --gold-lt: #d4af37;
            --gold-dk: #8a6e1e;
            --ink:     #0d0f14;
            --ink-mid: #1e1f2a;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: #111318;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 40px 20px 120px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ─── Top action bar ─── */
        .action-bar {
            width: 100%;
            max-width: 960px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .action-bar-left { display:flex; align-items:center; gap:14px; }

        .back-link {
            display:inline-flex; align-items:center; gap:6px;
            color: rgba(255,255,255,0.45); font-size:13px; text-decoration:none;
            transition:color 0.2s;
        }
        .back-link:hover { color: #d4af37; }

        .cert-label {
            font-size:11px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase;
            color: rgba(255,255,255,0.25);
        }

        .btn-download {
            display:inline-flex; align-items:center; gap:8px;
            background: linear-gradient(135deg, #d4af37, #a87e2e);
            color: #0d0f14; padding:13px 28px;
            border-radius:12px; text-decoration:none;
            font-weight:700; font-size:14px;
            box-shadow: 0 6px 20px rgba(212,175,55,0.3);
            transition: all 0.25s ease;
            cursor: pointer; border:none; font-family:'DM Sans',sans-serif;
        }
        .btn-download:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(212,175,55,0.45); }

        /* ═══════════════════════════════════════════
           CERTIFICATE ITSELF
        ═══════════════════════════════════════════ */
        .cert-wrapper {
            width: 960px;
            aspect-ratio: 1.414 / 1; /* A4 landscape ratio */
            position: relative;
            background: #fdf8ef;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(212,175,55,0.2);
            overflow: hidden;
        }

        /* ── Background watermark ── */
        .cert-watermark {
            position: absolute;
            inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
            z-index: 0;
        }

        .cert-watermark-text {
            font-family: 'Cinzel', serif;
            font-size: 160px;
            font-weight: 700;
            color: rgba(184,151,42,0.045);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            user-select:none;
        }

        /* ── Outer gold border frame ── */
        .cert-frame-outer {
            position: absolute;
            inset: 18px;
            border: 2.5px solid var(--gold-lt);
            pointer-events: none;
            z-index: 1;
        }

        .cert-frame-inner {
            position: absolute;
            inset: 24px;
            border: 1px solid rgba(184,151,42,0.4);
            pointer-events: none;
            z-index: 1;
        }

        /* ── Corner ornaments ── */
        .corner {
            position: absolute;
            width: 60px; height: 60px;
            z-index: 2;
            pointer-events: none;
        }
        .corner svg { width:100%; height:100%; }
        .corner-tl { top:10px;    left:10px; }
        .corner-tr { top:10px;    right:10px;  transform:scaleX(-1); }
        .corner-bl { bottom:10px; left:10px;   transform:scaleY(-1); }
        .corner-br { bottom:10px; right:10px;  transform:scale(-1,-1); }

        /* ── Top/bottom edge ornament lines ── */
        .edge-line {
            position: absolute;
            left: 80px; right: 80px;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--gold), var(--gold-lt), var(--gold), transparent);
            z-index: 2;
        }
        .edge-line-top    { top: 46px; }
        .edge-line-bottom { bottom: 46px; }

        /* ── Certificate content ── */
        .cert-content {
            position: relative;
            z-index: 3;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 52px 80px 44px;
            text-align: center;
        }

        /* Institution name */
        .cert-institution {
            font-family: 'Cinzel', serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: var(--gold-dk);
            margin-bottom: 6px;
        }

        /* Thin gold rule */
        .cert-rule {
            width: 240px;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--gold), transparent);
            margin: 8px auto;
        }

        /* "Certificate of Participation" heading */
        .cert-heading {
            font-family: 'Cinzel', serif;
            font-size: 34px;
            font-weight: 700;
            color: #1a1510;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            line-height: 1.1;
            margin: 10px 0 6px;
        }

        .cert-subheading {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            font-weight: 300;
            font-style: italic;
            color: #6b5c3e;
            letter-spacing: 0.12em;
            margin-bottom: 18px;
        }

        /* Student name */
        .cert-name-wrap {
            position: relative;
            margin: 4px 0 18px;
            padding: 0 40px 12px;
        }

        .cert-name-wrap::after {
            content:'';
            position:absolute; bottom:0; left:10%; right:10%;
            height:1px;
            background:linear-gradient(to right,transparent,var(--gold),transparent);
        }

        .cert-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 52px;
            font-weight: 600;
            font-style: italic;
            color: #1a1510;
            letter-spacing: 0.02em;
            line-height: 1.1;
        }

        /* Body text */
        .cert-body {
            font-family: 'Cormorant Garamond', serif;
            font-size: 17.5px;
            font-weight: 400;
            color: #3d3020;
            line-height: 1.7;
            max-width: 78%;
            margin-bottom: 24px;
        }

        .cert-body strong { font-weight:600; color:#1a1510; }

        /* Footer row */
        .cert-footer {
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 0 20px;
            margin-top: auto;
        }

        .sig-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 160px;
        }

        .sig-line {
            width: 100%;
            height: 1px;
            background: linear-gradient(to right,transparent,#8a6e1e,transparent);
        }

        .sig-name {
            font-family: 'Cinzel', serif;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6b5c3e;
        }

        /* QR code block */
        .cert-qr {
            display:flex; flex-direction:column; align-items:center; gap:5px;
        }
        .cert-qr img { width:62px; height:62px; border:1px solid rgba(184,151,42,0.3); border-radius:6px; }
        .cert-qr-label { font-size:8px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:#b8972a; }

        /* Gold seal decoration */
        .cert-seal {
            position: absolute;
            right: 92px;
            top: 50%;
            transform: translateY(-50%);
            width: 72px; height: 72px;
            z-index: 4;
            opacity: 0.18;
            pointer-events:none;
        }

        /* ── Cert ID strip ── */
        .cert-id {
            position: absolute;
            bottom: 54px; left: 50%;
            transform: translateX(-50%);
            font-family: 'DM Sans', sans-serif;
            font-size: 9px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(107,92,62,0.55);
            z-index: 5;
            white-space:nowrap;
        }

        /* ═══════════════════════════════════════════
           PRINT
        ═══════════════════════════════════════════ */
        @media print {
            @page { size: A4 landscape; margin: 0; }

            body {
                background: white !important;
                padding: 0 !important;
                justify-content: center;
            }

            .action-bar { display:none !important; }

            .cert-wrapper {
                width: 100vw !important;
                height: 100vh !important;
                aspect-ratio: unset !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }

        @media (max-width: 1020px) {
            .cert-wrapper { width: 100%; aspect-ratio: 1.414 / 1; }
            .action-bar { max-width: 100%; }
            .cert-heading { font-size: 26px; }
            .cert-name { font-size: 40px; }
            .cert-body { font-size: 15px; }
            .cert-content { padding: 40px 50px 36px; }
        }
    </style>
</head>
<body>

    <!-- Action bar -->
    <div class="action-bar no-print">
        <div class="action-bar-left">
            <a href="my_bookings.php" class="back-link">← My Bookings</a>
            <span class="cert-label">/ Certificate Preview</span>
        </div>
        <button onclick="window.print()" class="btn-download">
            📄 &nbsp;Download as PDF
        </button>
    </div>

    <!-- ═══ CERTIFICATE ═══ -->
    <div class="cert-wrapper" id="certificate">

        <!-- Watermark -->
        <div class="cert-watermark">
            <span class="cert-watermark-text">FGC</span>
        </div>

        <!-- Frames -->
        <div class="cert-frame-outer"></div>
        <div class="cert-frame-inner"></div>

        <!-- Edge lines -->
        <div class="edge-line edge-line-top"></div>
        <div class="edge-line edge-line-bottom"></div>

        <!-- Corner ornaments -->
        <div class="corner corner-tl">
            <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4 L4 30" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L30 4" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L18 18" stroke="#d4af37" stroke-width="0.8" opacity="0.5"/>
                <circle cx="4" cy="4" r="3" fill="#d4af37"/>
                <circle cx="4" cy="30" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="30" cy="4" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="18" cy="18" r="2" fill="none" stroke="#d4af37" stroke-width="1"/>
                <path d="M10 4 Q12 8 14 4" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
                <path d="M4 10 Q8 12 4 14" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
            </svg>
        </div>
        <div class="corner corner-tr">
            <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4 L4 30" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L30 4" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L18 18" stroke="#d4af37" stroke-width="0.8" opacity="0.5"/>
                <circle cx="4" cy="4" r="3" fill="#d4af37"/>
                <circle cx="4" cy="30" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="30" cy="4" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="18" cy="18" r="2" fill="none" stroke="#d4af37" stroke-width="1"/>
                <path d="M10 4 Q12 8 14 4" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
                <path d="M4 10 Q8 12 4 14" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
            </svg>
        </div>
        <div class="corner corner-bl">
            <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4 L4 30" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L30 4" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L18 18" stroke="#d4af37" stroke-width="0.8" opacity="0.5"/>
                <circle cx="4" cy="4" r="3" fill="#d4af37"/>
                <circle cx="4" cy="30" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="30" cy="4" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="18" cy="18" r="2" fill="none" stroke="#d4af37" stroke-width="1"/>
                <path d="M10 4 Q12 8 14 4" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
                <path d="M4 10 Q8 12 4 14" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
            </svg>
        </div>
        <div class="corner corner-br">
            <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4 L4 30" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L30 4" stroke="#d4af37" stroke-width="1.5"/>
                <path d="M4 4 L18 18" stroke="#d4af37" stroke-width="0.8" opacity="0.5"/>
                <circle cx="4" cy="4" r="3" fill="#d4af37"/>
                <circle cx="4" cy="30" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="30" cy="4" r="1.5" fill="#d4af37" opacity="0.7"/>
                <circle cx="18" cy="18" r="2" fill="none" stroke="#d4af37" stroke-width="1"/>
                <path d="M10 4 Q12 8 14 4" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
                <path d="M4 10 Q8 12 4 14" stroke="#d4af37" stroke-width="0.8" fill="none" opacity="0.6"/>
            </svg>
        </div>

        <!-- Cert ID -->
        <div class="cert-id">
            Certificate ID: MB-<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?> &nbsp;·&nbsp; Issued <?php echo date("d M Y"); ?> &nbsp;·&nbsp; MayaBook v3.0
        </div>

        <!-- Main content -->
        <div class="cert-content">

            <div class="cert-institution">RNS First Grade College &nbsp;·&nbsp; Department of <?php echo htmlspecialchars($data['department'] ?? 'Academic Affairs'); ?></div>

            <div class="cert-rule"></div>

            <div class="cert-heading">Certificate of Participation</div>

            <div class="cert-subheading">This certificate is proudly presented to</div>

            <div class="cert-name-wrap">
                <div class="cert-name"><?php echo $student_name; ?></div>
            </div>

            <div class="cert-body">
                <?php echo nl2br($final_content); ?>
            </div>

            <div class="cert-rule" style="margin-bottom:20px;"></div>

            <!-- Footer signatures + QR -->
            <div class="cert-footer">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-name">Organizing Head</div>
                </div>

                <div class="cert-qr">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo urlencode("http://yourdomain.com/verify.php?id=" . $booking_id); ?>" alt="QR Verification">
                    <div class="cert-qr-label">Scan to Verify</div>
                </div>

                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-name">Event Coordinator</div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>