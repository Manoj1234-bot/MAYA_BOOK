<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reset Password - Maya Book</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5e43f3;
            --bg-dark: #0f0c29;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f0c29);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: white;
        }

        .card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 40px 30px;
            border-radius: 28px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; color: #fff; }
        p.subtitle { color: rgba(255, 255, 255, 0.5); font-size: 0.9rem; margin-bottom: 30px; line-height: 1.5; }

        .form-group { text-align: left; margin-bottom: 20px; }
        
        input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 15px rgba(94, 67, 243, 0.3);
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(94, 67, 243, 0.4);
            filter: brightness(1.1);
        }

        button:active { transform: translateY(0); }

        .footer-links { margin-top: 25px; }

        .footer-links a {
            font-size: 14px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .footer-links a:hover { color: #fff; }

        /* Mobile specific adjustments */
        @media (max-width: 480px) {
            .card { padding: 35px 20px; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="card">
        <h2>Reset Password</h2>
        <p class="subtitle">Enter your registered email and we'll send you a secure link to reset your password.</p>
        
        <form action="send_reset_link.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <button type="submit" name="reset_request">Send Reset Link</button>
        </form>

        <div class="footer-links">
            <a href="login.php">← Back to Login</a>
        </div>

        <div style="margin-top: 40px; opacity: 0.4; font-size: 10px; letter-spacing: 1.5px; font-weight: 700; text-transform: uppercase;">
            Maya Book • Admin Control
        </div>
    </div>

</body>
</html>