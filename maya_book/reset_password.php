<?php
require_once "includes/db.php";
date_default_timezone_set('Asia/Kolkata');

$message = '';
$error = '';
$show_form = false;

// 1. Validate the token immediately
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Check if token exists and is not expired
    $query = "SELECT * FROM students WHERE reset_token = '$token'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Time comparison
        if (strtotime($user['token_expiry']) > time()) {
            $show_form = true;
        } else {
            $error = "This reset link has expired. Please request a new one.";
        }
    } else {
        $error = "Invalid reset link.";
    }
} else {
    header("Location: login.php");
    exit();
}

// 2. Handle the Password Update
if (isset($_POST['update_password']) && $show_form === true) {
    $new_pass = $_POST['new_password'];
    $conf_pass = $_POST['conf_password'];

    if (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_pass !== $conf_pass) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);
        $user_id = $user['id']; 

        // Update password and CLEAR token
        $update = "UPDATE students SET password = '$hashed_password', reset_token = NULL, token_expiry = NULL WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update)) {
            $message = "Password updated successfully! You can now login.";
            $show_form = false; // Hide form after success
        } else {
            $error = "Database error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Set New Password - Maya Book</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5e43f3;
            --bg-dark: #0f0c29;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --success: #2ecc71;
            --error: #e74c3c;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f0c29);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: white;
        }

        .container {
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

        /* Status Messages */
        .status { 
            padding: 15px; 
            border-radius: 15px; 
            margin-bottom: 25px; 
            font-size: 14px; 
            font-weight: 600;
        }
        .error-msg { background: rgba(231, 76, 60, 0.2); color: #ff7675; border: 1px solid var(--error); }
        .success-msg { background: rgba(46, 204, 113, 0.2); color: #55efc4; border: 1px solid var(--success); }

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
            margin-bottom: 15px;
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
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(94, 67, 243, 0.4);
            filter: brightness(1.1);
        }

        .login-link {
            display: inline-block;
            margin-top: 25px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .login-link:hover { color: #fff; }

        @media (max-width: 480px) {
            .container { padding: 35px 20px; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>New Password</h2>
    
    <?php if ($error): ?>
        <div class="status error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="status success-msg"><?php echo $message; ?></div>
        <a href="login.php" class="login-link">Back to Login →</a>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <p class="subtitle">Secure your account by choosing a strong, new password.</p>
        <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
            <input type="password" name="new_password" placeholder="New Password" required minlength="6">
            <input type="password" name="conf_password" placeholder="Confirm Password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>
    <?php endif; ?>

    <div style="margin-top: 40px; opacity: 0.3; font-size: 10px; letter-spacing: 2px; font-weight: 700; text-transform: uppercase;">
        Maya Book Security
    </div>
</div>

</body>
</html>