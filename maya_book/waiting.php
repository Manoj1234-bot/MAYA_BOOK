<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MayaBook — Approval Pending</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:    #0d1117;
            --ink-2:  #1c2433;
            --gold:   #c9a84c;
            --gold-lt:#e8c96a;
            --amber:  #f59e0b;
            --violet: #818cf8;
            --teal:   #2dd4bf;
            --line:   rgba(255,255,255,0.07);
            --muted:  #6b7a91;
            --muted-2:#9aa5b4;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        html {
            min-height: 100%;
            background: var(--ink);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--ink);
            color: #e2e8f0;
            min-height: 100vh;
            /* flex column so content stacks top-to-bottom and scrolls naturally */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 52px 20px 64px;
            /* NO overflow:hidden — let the page scroll freely */
        }

        /* Noise texture — fixed, never blocks scroll */
        body::before {
            content:''; position:fixed; inset:0; z-index:0; pointer-events:none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity: 0.025;
        }

        /* Ambient blobs — fixed behind everything */
        .amb { position:fixed; border-radius:50%; filter:blur(100px); pointer-events:none; z-index:0; }
        .amb-1 { width:460px; height:460px; background:rgba(201,168,76,0.07); top:-100px; right:-80px; }
        .amb-2 { width:380px; height:380px; background:rgba(129,140,248,0.05); bottom:-60px; left:-80px; }

        /* ── Brand ── */
        .brand {
            position:relative; z-index:1;
            display:flex; align-items:center; gap:10px;
            margin-bottom:32px;
            animation:fadeDown 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes fadeDown {
            from { transform:translateY(-10px); opacity:0; }
            to   { transform:translateY(0);     opacity:1; }
        }
        .brand-icon {
            width:34px; height:34px;
            background:linear-gradient(135deg,var(--gold),var(--gold-lt));
            border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px;
        }
        .brand-name { font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:600; color:#fff; }
        .brand-name span { color:var(--gold); }

        /* ── Card ── */
        .card {
            position:relative; z-index:1;
            background:var(--ink-2);
            border:1px solid var(--line);
            border-radius:24px;
            padding:44px 40px 40px;
            text-align:center;
            width:100%; max-width:460px;
            /* height is auto — grows with content */
            animation:popIn 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }

        /* Amber top strip */
        .card::before {
            content:''; position:absolute;
            top:0; left:12%; right:12%; height:3px;
            background:linear-gradient(to right,transparent,var(--amber),var(--gold-lt),var(--amber),transparent);
            border-radius:0 0 4px 4px;
        }

        @keyframes popIn {
            from { transform:scale(0.93) translateY(14px); opacity:0; }
            to   { transform:scale(1)    translateY(0);    opacity:1; }
        }

        /* ── Hourglass icon ── */
        .icon-wrap {
            width:80px; height:80px; border-radius:50%;
            background:rgba(245,158,11,0.08);
            border:2px solid rgba(245,158,11,0.22);
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 22px; position:relative;
        }
        .icon-wrap::before {
            content:''; position:absolute; inset:-9px; border-radius:50%;
            border:1.5px dashed rgba(245,158,11,0.18);
            animation:spin 12s linear infinite;
        }
        .icon-wrap::after {
            content:''; position:absolute; inset:0; border-radius:50%;
            animation:ringPulse 2.5s ease-in-out infinite;
        }
        @keyframes spin      { to { transform:rotate(360deg); } }
        @keyframes ringPulse {
            0%,100% { box-shadow:0 0 0 0    rgba(245,158,11,0.12); }
            50%      { box-shadow:0 0 0 16px rgba(245,158,11,0); }
        }
        .icon-emoji { font-size:32px; animation:swing 3s ease-in-out infinite; }
        @keyframes swing {
            0%,100% { transform:rotate(-8deg); }
            50%      { transform:rotate(8deg); }
        }

        /* ── Typography ── */
        .eyebrow {
            font-size:10px; font-weight:700; letter-spacing:0.18em;
            text-transform:uppercase; color:var(--amber); margin-bottom:8px;
        }
        .heading {
            font-family:'Playfair Display',serif;
            font-size:1.75rem; font-weight:600; color:#fff;
            letter-spacing:-0.02em; line-height:1.15; margin-bottom:10px;
        }
        .subtext {
            font-size:13.5px; color:var(--muted-2); line-height:1.7;
            font-weight:300; margin-bottom:20px;
        }

        /* ── Status pill ── */
        .status-pill {
            display:inline-flex; align-items:center; gap:7px;
            background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.25);
            border-radius:40px; padding:7px 16px; margin-bottom:22px;
        }
        .status-dot {
            width:7px; height:7px; border-radius:50%; background:var(--amber);
            animation:blink 1.5s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.2;} }
        .status-text { font-size:12px; font-weight:600; color:var(--amber); letter-spacing:0.05em; }

        /* ── Divider ── */
        .divider {
            height:1px;
            background:linear-gradient(to right,transparent,rgba(245,158,11,0.3),transparent);
            margin-bottom:20px;
        }

        /* ── Steps ── */
        .steps { display:flex; flex-direction:column; gap:10px; margin-bottom:26px; text-align:left; }

        .step {
            display:flex; align-items:flex-start; gap:12px;
            background:rgba(255,255,255,0.025);
            border:1px solid var(--line);
            border-radius:13px; padding:13px 14px;
        }
        .step-icon {
            width:34px; height:34px; flex-shrink:0;
            border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px;
        }
        .step-title  { font-size:13px; font-weight:600; color:#fff; margin-bottom:2px; }
        .step-detail { font-size:12px; color:var(--muted-2); line-height:1.5; font-weight:300; }

        /* ── Button ── */
        .btn-back {
            display:inline-flex; align-items:center; gap:8px;
            background:rgba(255,255,255,0.05); border:1px solid var(--line);
            color:var(--muted-2); padding:12px 28px;
            border-radius:12px; text-decoration:none;
            font-weight:500; font-size:14px;
            transition:all 0.25s ease;
            margin-bottom:22px;
        }
        .btn-back:hover { background:rgba(255,255,255,0.09); color:#fff; }

        /* ── Footer note ── */
        .footer-note { font-size:11px; color:var(--muted); letter-spacing:0.04em; }
        .footer-note strong { color:var(--gold); font-weight:600; }

        /* ── Responsive ── */
        @media (max-width: 500px) {
            body  { padding:32px 16px 48px; }
            .card { padding:32px 20px 28px; }
            .heading { font-size:1.45rem; }
        }
    </style>
</head>
<body>

<div class="amb amb-1"></div>
<div class="amb amb-2"></div>

<!-- Brand -->
<div class="brand">
    <div class="brand-icon">📘</div>
    <div class="brand-name">Maya<span>Book</span></div>
</div>

<!-- Card -->
<div class="card">

    <div class="icon-wrap">
        <span class="icon-emoji">⏳</span>
    </div>

    <div class="eyebrow">Account Status &nbsp;·&nbsp; Under Review</div>
    <h1 class="heading">Approval Pending</h1>
    <p class="subtext">
        Your account has been registered and is currently awaiting review by the administration team. You will receive full access once approved.
    </p>

    <div class="status-pill">
        <div class="status-dot"></div>
        <span class="status-text">Awaiting Admin Approval</span>
    </div>

    <div class="divider"></div>

    <div class="steps">
        <div class="step">
            <div class="step-icon" style="background:rgba(45,212,191,0.1);">✅</div>
            <div>
                <div class="step-title">Registration Complete</div>
                <div class="step-detail">Your account details have been received and are on record.</div>
            </div>
        </div>
        <div class="step">
            <div class="step-icon" style="background:rgba(245,158,11,0.1);">🔍</div>
            <div>
                <div class="step-title">Admin Review in Progress</div>
                <div class="step-detail">The administration is verifying your details. This usually takes a short while.</div>
            </div>
        </div>
        <div class="step">
            <div class="step-icon" style="background:rgba(129,140,248,0.1);">🎓</div>
            <div>
                <div class="step-title">Access Granted on Approval</div>
                <div class="step-detail">Once approved, you can log in and start booking events immediately.</div>
            </div>
        </div>
    </div>

    <a href="login.php" class="btn-back">← Back to Login</a>

    <div class="footer-note">
        <strong>MayaBook v3.0</strong> &nbsp;·&nbsp; RNS First Grade College
    </div>

</div>

</body>
</html>