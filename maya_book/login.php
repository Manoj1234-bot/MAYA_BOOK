<?php
session_start();
include "includes/db.php";

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, password, status FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['student_id'] = $user['id'];
                $_SESSION['user_status'] = $user['status'];

                if ($user['status'] == "pending") {
                    header("Location: waiting.php");
                    exit();
                } elseif ($user['status'] == "approved") {
                    header("Location: student/dashboard.php");
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Maya Book - Student Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5e43f3;
            --primary-glow: rgba(94, 67, 243, 0.35);
            --bg: #080612;
            --card: rgba(255,255,255,0.04);
            --border: rgba(255,255,255,0.09);
            --text: #f0eeff;
            --muted: rgba(240,238,255,0.45);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        /* ── BACKGROUND MESH ── */
        .bg-mesh {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .bg-mesh::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(94,67,243,0.18) 0%, transparent 65%);
            top: -100px; left: -150px;
            border-radius: 50%;
        }

        .bg-mesh::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(180,100,255,0.12) 0%, transparent 65%);
            bottom: -80px; right: -100px;
            border-radius: 50%;
        }

        /* ── LAYOUT ── */
        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        /* ── CHARACTER ── */
        .char-wrap {
            width: 160px;
            height: 140px;
            position: relative;
            margin-bottom: -10px;
            z-index: 2;
            cursor: default;
            user-select: none;
        }

        #character {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        /* Arms cover eyes when password focused */
        .arm-l, .arm-r {
            transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
            transform-origin: bottom center;
        }

        /* ── CARD ── */
        .card {
            width: 100%;
            background: var(--card);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 36px 32px 32px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.07);
            animation: fadeUp 0.7s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--text);
            text-align: center;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .brand span { color: var(--primary); }

        .subtitle {
            text-align: center;
            color: var(--muted);
            font-size: 13.5px;
            margin-bottom: 28px;
        }

        /* ── ERROR BOX ── */
        .error-box {
            background: rgba(255,80,80,0.12);
            border: 1px solid rgba(255,80,80,0.25);
            color: #ffaaaa;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── FORM ── */
        form { display: flex; flex-direction: column; gap: 14px; }

        .field {
            position: relative;
        }

        .field label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .field input {
            width: 100%;
            padding: 15px 18px;
            background: rgba(255,255,255,0.055);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .field input::placeholder { color: rgba(255,255,255,0.22); }

        .field input:focus {
            border-color: var(--primary);
            background: rgba(94,67,243,0.08);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .field input.shake {
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        .forgot-link {
            text-align: right;
            margin-top: 5px;
        }

        .forgot-link a {
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link a:hover { color: var(--text); }

        /* ── BUTTON ── */
        .login-btn {
            width: 100%;
            padding: 16px;
            margin-top: 6px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(94,67,243,0.4);
        }

        .login-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12), transparent);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(94,67,243,0.55);
        }

        .login-btn:active {
            transform: translateY(0) scale(0.98);
        }

        /* ── DIVIDER ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 0;
        }

        .divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid var(--border);
        }

        .divider span {
            font-size: 12px;
            color: var(--muted);
        }

        /* ── FOOTER LINKS ── */
        .footer-links {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: var(--muted);
        }

        .footer-links a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            padding-bottom: 1px;
            transition: border-color 0.2s;
        }

        .footer-links a:hover { border-color: var(--primary); color: #a08fff; }

        .admin-row {
            margin-top: 14px;
            text-align: center;
        }

        .admin-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
            transition: all 0.25s;
        }

        .admin-link:hover {
            background: rgba(255,255,255,0.08);
            color: var(--text);
            border-color: rgba(255,255,255,0.18);
        }

        /* ── BRAND BADGE ── */
        .brand-badge {
            position: fixed;
            bottom: 22px;
            right: 22px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            backdrop-filter: blur(12px);
            padding: 9px 16px;
            border-radius: 12px;
            text-align: center;
            z-index: 100;
        }

        .brand-badge .name {
            font-family: 'Syne', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #a08fff;
            letter-spacing: 0.5px;
        }

        .brand-badge .by {
            font-size: 10px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        @media (max-width: 480px) {
            .card { padding: 28px 20px 24px; border-radius: 24px; }
            .char-wrap { width: 130px; height: 115px; }
            .brand-badge { display: none; }
        }
    </style>
</head>
<body>

<div class="bg-mesh"></div>

<div class="wrapper">

    <!-- ── ANIMATED CHARACTER ── -->
    <div class="char-wrap">
        <svg id="character" viewBox="0 0 160 140" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="bodyGrad" cx="50%" cy="40%" r="55%">
                    <stop offset="0%" stop-color="#a78bfa"/>
                    <stop offset="100%" stop-color="#5e43f3"/>
                </radialGradient>
                <radialGradient id="faceGrad" cx="50%" cy="35%" r="55%">
                    <stop offset="0%" stop-color="#fde8d8"/>
                    <stop offset="100%" stop-color="#f5c9a8"/>
                </radialGradient>
                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(94,67,243,0.5)"/>
                </filter>
            </defs>

            <!-- Body -->
            <ellipse cx="80" cy="112" rx="36" ry="28" fill="url(#bodyGrad)" filter="url(#shadow)"/>
            <!-- Collar -->
            <path d="M55 97 Q80 108 105 97 L105 103 Q80 115 55 103 Z" fill="#4c33c9" opacity="0.6"/>

            <!-- Head -->
            <circle id="head" cx="80" cy="72" r="34" fill="url(#faceGrad)" filter="url(#shadow)"/>

            <!-- Ears -->
            <circle cx="49" cy="58" r="10" fill="#f5c9a8"/>
            <circle cx="49" cy="58" r="6"  fill="#f0a88a"/>
            <circle cx="111" cy="58" r="10" fill="#f5c9a8"/>
            <circle cx="111" cy="58" r="6"  fill="#f0a88a"/>

            <!-- Hair -->
            <ellipse cx="80" cy="42" rx="28" ry="14" fill="#2d1a6e"/>
            <path d="M52 48 Q44 35 50 28 Q58 22 66 30" fill="#2d1a6e"/>
            <path d="M108 48 Q116 35 110 28 Q102 22 94 30" fill="#2d1a6e"/>
            <path d="M70 30 Q80 18 90 30" fill="#2d1a6e"/>

            <!-- ── EYES GROUP ── -->
            <g id="eyes-group">
                <!-- Left eye whites -->
                <ellipse id="eye-l-white" cx="67" cy="73" rx="9" ry="10" fill="white"/>
                <!-- Right eye whites -->
                <ellipse id="eye-r-white" cx="93" cy="73" rx="9" ry="10" fill="white"/>
                <!-- Left pupil -->
                <circle id="pupil-l" cx="67" cy="74" r="5" fill="#1a0a40"/>
                <!-- Right pupil -->
                <circle id="pupil-r" cx="93" cy="74" r="5" fill="#1a0a40"/>
                <!-- Pupil shine -->
                <circle cx="69" cy="72" r="1.8" fill="white" id="shine-l"/>
                <circle cx="95" cy="72" r="1.8" fill="white" id="shine-r"/>
                <!-- Closed eye lines (hidden by default) -->
                <path id="eye-l-closed" d="M58 73 Q67 68 76 73" stroke="#c9956d" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0"/>
                <path id="eye-r-closed" d="M84 73 Q93 68 102 73" stroke="#c9956d" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0"/>
            </g>

            <!-- Nose -->
            <ellipse cx="80" cy="82" rx="4" ry="2.5" fill="#e8a882"/>

            <!-- Mouth group -->
            <g id="mouth-group">
                <!-- Happy mouth (default) -->
                <path id="mouth-happy" d="M70 90 Q80 98 90 90" stroke="#c9705a" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <!-- Sad/worried mouth (hidden) -->
                <path id="mouth-worried" d="M70 94 Q80 89 90 94" stroke="#c9705a" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0"/>
                <!-- Teeth visible (hidden) -->
                <path id="mouth-open" d="M70 90 Q80 99 90 90" stroke="#c9705a" stroke-width="2.5" fill="#fff" stroke-linecap="round" opacity="0"/>
            </g>

            <!-- Cheek blush -->
            <ellipse id="blush-l" cx="54" cy="82" rx="8" ry="5" fill="#f59a9a" opacity="0"/>
            <ellipse id="blush-r" cx="106" cy="82" rx="8" ry="5" fill="#f59a9a" opacity="0"/>

            <!-- ── ARMS ── -->
            <!-- Left arm (cover eyes) -->
            <g id="arm-l" class="arm-l" style="transform: rotate(0deg); transform-origin: 44px 100px;">
                <ellipse cx="44" cy="100" rx="13" ry="9" fill="url(#bodyGrad)" transform="rotate(-20,44,100)"/>
                <!-- Hand -->
                <circle cx="36" cy="96" r="11" fill="#f5c9a8"/>
                <circle cx="27" cy="92" r="5.5" fill="#f5c9a8"/>
                <circle cx="31" cy="85" r="5.5" fill="#f5c9a8"/>
                <circle cx="39" cy="83" r="5.5" fill="#f5c9a8"/>
                <circle cx="47" cy="84" r="5.5" fill="#f5c9a8"/>
            </g>

            <!-- Right arm (cover eyes) -->
            <g id="arm-r" class="arm-r" style="transform: rotate(0deg); transform-origin: 116px 100px;">
                <ellipse cx="116" cy="100" rx="13" ry="9" fill="url(#bodyGrad)" transform="rotate(20,116,100)"/>
                <!-- Hand -->
                <circle cx="124" cy="96" r="11" fill="#f5c9a8"/>
                <circle cx="133" cy="92" r="5.5" fill="#f5c9a8"/>
                <circle cx="129" cy="85" r="5.5" fill="#f5c9a8"/>
                <circle cx="121" cy="83" r="5.5" fill="#f5c9a8"/>
                <circle cx="113" cy="84" r="5.5" fill="#f5c9a8"/>
            </g>
        </svg>
    </div>

    <!-- ── CARD ── -->
    <div class="card">
        <div class="brand">Maya<span>Book</span></div>
        <p class="subtitle">Student Portal Login</p>

        <?php if ($error): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm" novalidate>
            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" id="emailInput" placeholder="you@college.edu" required autocomplete="email">
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="password" id="passwordInput" placeholder="••••••••" required autocomplete="current-password">
                <div class="forgot-link">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
            </div>

            <button type="submit" name="login" class="login-btn" id="loginBtn">Login Securely</button>
        </form>

        <div class="footer-links">
            New here? <a href="register.php">Create an account</a>
        </div>

        <div class="admin-row">
            <a href="admin/admin_login.php" class="admin-link">🔒 Admin Portal</a>
        </div>
    </div>
</div>

<div class="brand-badge">
    <div class="name">Maya Book</div>
    <div class="by">by Manoj S</div>
</div>

<script>
(function() {
    const emailInput    = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const loginBtn      = document.getElementById('loginBtn');
    const form          = document.getElementById('loginForm');

    // Character elements
    const head      = document.getElementById('head');
    const pupilL    = document.getElementById('pupil-l');
    const pupilR    = document.getElementById('pupil-r');
    const shineL    = document.getElementById('shine-l');
    const shineR    = document.getElementById('shine-r');
    const eyeLW     = document.getElementById('eye-l-white');
    const eyeRW     = document.getElementById('eye-r-white');
    const eyeLClosed= document.getElementById('eye-l-closed');
    const eyeRClosed= document.getElementById('eye-r-closed');
    const mouthHappy= document.getElementById('mouth-happy');
    const mouthWorry= document.getElementById('mouth-worried');
    const mouthOpen = document.getElementById('mouth-open');
    const blushL    = document.getElementById('blush-l');
    const blushR    = document.getElementById('blush-r');
    const armL      = document.getElementById('arm-l');
    const armR      = document.getElementById('arm-r');

    let trackingEmail = false;
    let eyesCovered   = false;
    let animFrame;

    // ── helpers ──
    function lerp(a, b, t) { return a + (b - a) * t; }

    function setAttr(el, attrs) {
        for (const [k, v] of Object.entries(attrs)) el.setAttribute(k, v);
    }

    function setStyle(el, styles) {
        for (const [k, v] of Object.entries(styles)) el.style[k] = v;
    }

    function transitionEl(el, attr, val, duration = 0.35) {
        el.style.transition = `${attr} ${duration}s cubic-bezier(0.34,1.56,0.64,1)`;
        el.setAttribute(attr, val);
    }

    // ── STATE: eyes open, looking center ──
    function resetFace() {
        setAttr(eyeLW,     { opacity: 1 });
        setAttr(eyeRW,     { opacity: 1 });
        setAttr(eyeLClosed,{ opacity: 0 });
        setAttr(eyeRClosed,{ opacity: 0 });
        setAttr(pupilL,    { cx: 67, cy: 74, r: 5 });
        setAttr(pupilR,    { cx: 93, cy: 74, r: 5 });
        setAttr(shineL,    { cx: 69, cy: 72, opacity: 1 });
        setAttr(shineR,    { cx: 95, cy: 72, opacity: 1 });
        setAttr(mouthHappy,{ opacity: 1 });
        setAttr(mouthWorry,{ opacity: 0 });
        setAttr(mouthOpen, { opacity: 0 });
        setAttr(blushL,    { opacity: 0 });
        setAttr(blushR,    { opacity: 0 });
    }

    // ── Arms cover eyes ──
    function coverEyes(cover) {
        if (cover === eyesCovered) return;
        eyesCovered = cover;

        if (cover) {
            // Arms swing up to cover eyes
            armL.style.transition = 'transform 0.45s cubic-bezier(0.34,1.56,0.64,1)';
            armR.style.transition = 'transform 0.45s cubic-bezier(0.34,1.56,0.64,1)';
            armL.style.transform  = 'rotate(-115deg)';
            armR.style.transform  = 'rotate(115deg)';
            // Also hide eyes
            setTimeout(() => {
                setAttr(eyeLW,      { opacity: 0 });
                setAttr(eyeRW,      { opacity: 0 });
                setAttr(eyeLClosed, { opacity: 1 });
                setAttr(eyeRClosed, { opacity: 1 });
                setAttr(pupilL,     { opacity: 0 });
                setAttr(pupilR,     { opacity: 0 });
                setAttr(shineL,     { opacity: 0 });
                setAttr(shineR,     { opacity: 0 });
            }, 200);
        } else {
            // Arms go back down
            armL.style.transition = 'transform 0.4s cubic-bezier(0.34,1.56,0.64,1)';
            armR.style.transition = 'transform 0.4s cubic-bezier(0.34,1.56,0.64,1)';
            armL.style.transform  = 'rotate(0deg)';
            armR.style.transform  = 'rotate(0deg)';
            setTimeout(() => {
                setAttr(eyeLW,      { opacity: 1 });
                setAttr(eyeRW,      { opacity: 1 });
                setAttr(eyeLClosed, { opacity: 0 });
                setAttr(eyeRClosed, { opacity: 0 });
                setAttr(pupilL,     { opacity: 1 });
                setAttr(pupilR,     { opacity: 1 });
                setAttr(shineL,     { opacity: 1 });
                setAttr(shineR,     { opacity: 1 });
            }, 250);
        }
    }

    // ── Track email input: head follows cursor ──
    let targetHeadX = 0, targetHeadY = 0;
    let currentHeadX = 0, currentHeadY = 0;
    let targetPupilX = 67, targetPupilY = 74;
    let currentPupilX = 67, currentPupilY = 74;

    function animateHead() {
        if (!trackingEmail) return;
        currentHeadX = lerp(currentHeadX, targetHeadX, 0.08);
        currentHeadY = lerp(currentHeadY, targetHeadY, 0.08);
        currentPupilX = lerp(currentPupilX, targetPupilX, 0.1);
        currentPupilY = lerp(currentPupilY, targetPupilY, 0.1);

        head.setAttribute('cx', 80 + currentHeadX);
        head.setAttribute('cy', 72 + currentHeadY);
        pupilL.setAttribute('cx', 67 + currentPupilX - 67);
        pupilL.setAttribute('cy', 74 + currentPupilY - 74);
        pupilR.setAttribute('cx', 93 + currentPupilX - 67);
        pupilR.setAttribute('cy', 74 + currentPupilY - 74);
        shineL.setAttribute('cx', 69 + currentPupilX - 67);
        shineL.setAttribute('cy', 72 + currentPupilY - 74);
        shineR.setAttribute('cx', 95 + currentPupilX - 67);
        shineR.setAttribute('cy', 72 + currentPupilY - 74);

        animFrame = requestAnimationFrame(animateHead);
    }

    function getInputBounds(input) {
        const rect = input.getBoundingClientRect();
        return {
            cx: rect.left + rect.width / 2,
            cy: rect.top + rect.height / 2
        };
    }

    function lookAt(targetX, targetY) {
        const charEl = document.getElementById('character');
        const charRect = charEl.getBoundingClientRect();
        const charCX = charRect.left + charRect.width * 0.5;
        const charCY = charRect.top  + charRect.height * 0.35;

        const dx = targetX - charCX;
        const dy = targetY - charCY;
        const dist = Math.sqrt(dx*dx + dy*dy) || 1;
        const maxHead = 4;
        const maxPupil = 3;

        targetHeadX  = Math.max(-maxHead,  Math.min(maxHead,  (dx / dist) * maxHead));
        targetHeadY  = Math.max(-maxHead,  Math.min(maxHead,  (dy / dist) * maxHead * 0.5));
        targetPupilX = 67 + Math.max(-maxPupil, Math.min(maxPupil, (dx / dist) * maxPupil));
        targetPupilY = 74 + Math.max(-maxPupil, Math.min(maxPupil, (dy / dist) * maxPupil * 0.5));
    }

    // ── EVENT LISTENERS ──

    // Email focused — character watches
    emailInput.addEventListener('focus', () => {
        trackingEmail = true;
        coverEyes(false);
        resetFace();
        setAttr(mouthHappy, { opacity: 1 });
        cancelAnimationFrame(animFrame);
        animateHead();

        // Initial look at email field
        const b = getInputBounds(emailInput);
        lookAt(b.cx, b.cy);
    });

    emailInput.addEventListener('input', () => {
        const b = getInputBounds(emailInput);
        lookAt(b.cx, b.cy);
        // Smile bigger when typing
        if (emailInput.value.length > 3) {
            setAttr(blushL, { opacity: 0.4 });
            setAttr(blushR, { opacity: 0.4 });
        } else {
            setAttr(blushL, { opacity: 0 });
            setAttr(blushR, { opacity: 0 });
        }
    });

    emailInput.addEventListener('blur', () => {
        trackingEmail = false;
        cancelAnimationFrame(animFrame);
        // Smoothly return
        targetHeadX = 0; targetHeadY = 0;
        targetPupilX = 67; targetPupilY = 74;
        currentHeadX = 0; currentHeadY = 0;
        currentPupilX = 67; currentPupilY = 74;
        head.setAttribute('cx', 80);
        head.setAttribute('cy', 72);
        setAttr(blushL, { opacity: 0 });
        setAttr(blushR, { opacity: 0 });
    });

    // Password focused — cover eyes!
    passwordInput.addEventListener('focus', () => {
        trackingEmail = false;
        cancelAnimationFrame(animFrame);
        head.setAttribute('cx', 80);
        head.setAttribute('cy', 72);
        coverEyes(true);
        setAttr(mouthHappy, { opacity: 1 });
        setAttr(mouthWorry, { opacity: 0 });
        setAttr(blushL, { opacity: 0 });
        setAttr(blushR, { opacity: 0 });
    });

    passwordInput.addEventListener('blur', () => {
        coverEyes(false);
        setTimeout(() => resetFace(), 300);
    });

    // Submit: character reacts
    form.addEventListener('submit', (e) => {
        const emailVal = emailInput.value.trim();
        const passVal  = passwordInput.value;

        coverEyes(false);

        if (!emailVal || !passVal) {
            e.preventDefault();
            // Worried face
            setAttr(mouthHappy, { opacity: 0 });
            setAttr(mouthWorry, { opacity: 1 });
            setAttr(eyeLW, { rx: 9, ry: 7 });
            setAttr(eyeRW, { rx: 9, ry: 7 });
            // Shake inputs
            if (!emailVal) emailInput.classList.add('shake');
            if (!passVal)  passwordInput.classList.add('shake');
            setTimeout(() => {
                emailInput.classList.remove('shake');
                passwordInput.classList.remove('shake');
            }, 500);
            return;
        }

        // Happy submit face
        setAttr(mouthOpen,  { opacity: 1 });
        setAttr(mouthHappy, { opacity: 0 });
        setAttr(blushL,     { opacity: 0.5 });
        setAttr(blushR,     { opacity: 0.5 });
        loginBtn.textContent = 'Logging in…';
        loginBtn.style.opacity = '0.8';
    });

    // Mouse move over email field to track
    emailInput.addEventListener('mousemove', (e) => {
        if (trackingEmail) lookAt(e.clientX, e.clientY);
    });

    // Keypress in email: eyes squint slightly then open
    emailInput.addEventListener('keydown', () => {
        setAttr(eyeLW, { ry: 8 });
        setAttr(eyeRW, { ry: 8 });
    });
    emailInput.addEventListener('keyup', () => {
        setAttr(eyeLW, { ry: 10 });
        setAttr(eyeRW, { ry: 10 });
    });

    // Init
    resetFace();

    <?php if ($error): ?>
    // PHP error — show worried face on load
    (function() {
        setTimeout(() => {
            setAttr(mouthHappy, { opacity: 0 });
            setAttr(mouthWorry, { opacity: 1 });
            setAttr(eyeLW, { rx: 9, ry: 7 });
            setAttr(eyeRW, { rx: 9, ry: 7 });
            emailInput.classList.add('shake');
            setTimeout(() => emailInput.classList.remove('shake'), 500);
        }, 400);
    })();
    <?php endif; ?>

})();
</script>

</body>
</html>