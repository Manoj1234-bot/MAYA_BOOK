<?php
session_start();
include "includes/db.php";

$success = $error = '';

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $reg_no = strtoupper(trim($_POST['reg_no']));
    $department = $_POST['department'];
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($reg_no) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE email = ? OR reg_no = ?");
        $check->bind_param("ss", $email, $reg_no);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Email or Reg No already exists!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $status = 'pending';
            $stmt = $conn->prepare("INSERT INTO students (name, email, reg_no, department, password, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $reg_no, $department, $hashed, $status);
            if ($stmt->execute()) {
                $success = "Account created! Waiting for approval.";
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Maya Book - Join Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5e43f3;
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.18);
            --trans: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            min-height: 100vh;
            background: #0f0c29;
            background: linear-gradient(135deg, #24243e, #302b63, #0f0c29);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px;
            padding: 35px 25px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--glass-border);
            text-align: center;
            animation: slideUp 0.8s ease-out both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stagger { opacity: 0; animation: fadeIn 0.5s ease-out forwards; }
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }

        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } from { opacity: 0; transform: translateY(10px); } }

        h2 { color: white; font-size: 26px; font-weight: 700; }
        .subtitle { color: rgba(255, 255, 255, 0.6); font-size: 13px; margin-bottom: 25px; }

        input, select {
            width: 100%;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid transparent;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
            color: white;
            font-size: 16px;
            outline: none;
            transition: var(--trans);
        }

        select { cursor: pointer; color: rgba(255,255,255,0.8); }
        select option { background: #302b63; color: white; }

        input:focus, select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary);
            transform: scale(1.01);
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--trans);
            box-shadow: 0 10px 20px rgba(94, 67, 243, 0.3);
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:active { transform: scale(0.96); opacity: 0.9; }

        .msg { padding: 12px; border-radius: 12px; margin-bottom: 15px; font-size: 13px; border: 1px solid; }
        .err { background: rgba(255, 87, 87, 0.15); color: #ff8b8b; border-color: rgba(255, 87, 87, 0.2); }
        .suc { background: rgba(70, 200, 100, 0.15); color: #a2ffb3; border-color: rgba(70, 200, 100, 0.2); }

        .footer-link { margin-top: 20px; font-size: 14px; color: rgba(255, 255, 255, 0.7); }
        a { color: white; text-decoration: none; font-weight: 600; border-bottom: 1px dashed rgba(255,255,255,0.4); }

        .brand-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 15px;
            border-radius: 10px;
            text-align: right;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        @media (max-width: 480px) {
            .container { padding: 25px 20px; border-radius: 0; background: transparent; box-shadow: none; border: none; }
            .brand-badge { display: none; }
            body { align-items: flex-start; padding-top: 30px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="stagger-1 stagger">
        <h2>Create Account</h2>
        <p class="subtitle">Join the Maya Book Community</p>
    </div>

    <?php if ($error): ?>
        <div class="msg err">⚠️ <?=$error?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="msg suc">✅ <?=$success?></div>
    <?php endif; ?>

    <form method="POST" class="stagger-2 stagger">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="reg_no" placeholder="Registration No (BCA21XXX)" required>
        
        <select name="department" required>
            <option value="" disabled selected>Select Department</option>
            <option value="BCA">BCA</option>
            <option value="BBA">BBA</option>
            <option value="BCom">BCom</option>
            <option value="BSc">BSc Comp Science</option>
        </select>

        <input type="password" name="password" placeholder="Password (min 6 chars)" required>

        <button type="submit" name="register">Register Now</button>
    </form>

    <div class="footer-link stagger-3 stagger">
        Already registered? <a href="login.php">Back to Login</a>
    </div>
</div>

<div class="brand-badge">
    <div style="font-weight: 700; color: var(--primary); font-size: 14px;">Maya Book</div>
    <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">BY MANOJ S</div>
</div>

</body>
</html>