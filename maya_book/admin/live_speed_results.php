<?php
session_start();
include "../includes/db.php";

// 1. Security Check
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);

// 2. Fetch the Current Active Question
$active_q_res = mysqli_query($conn, "SELECT id, question_text, activated_at FROM quiz_questions WHERE event_id = '$event_id' AND is_active = 1 LIMIT 1");
$active_q = mysqli_fetch_assoc($active_q_res);

// 3. Logic to fetch speed results (Only if a question is live)
$results = false;
if($active_q) {
    $q_id = $active_q['id'];
    $start_time = $active_q['activated_at'];

    // Query calculates difference between 'Push Live' time and 'Student Submit' time
    $results_query = "
        SELECT s.name, s.reg_no, 
               TIMESTAMPDIFF(SECOND, '$start_time', r.submitted_at) as seconds_taken
        FROM quiz_responses r
        JOIN students s ON r.student_id = s.id
        WHERE r.question_id = '$q_id' AND r.is_correct = 1
        ORDER BY seconds_taken ASC, r.submitted_at ASC
        LIMIT 20";
    $results = mysqli_query($conn, $results_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚡ Speed Leaderboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #5e43f3; --accent: #facc15; --bg: #0f172a; --card: #1e293b; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: white; 
            margin: 0; 
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container { width: 100%; max-width: 800px; margin-top: 30px; }

        /* Header Styling */
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { 
            font-size: 2.5rem; 
            margin: 0; 
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .status-bar {
            background: var(--card);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }

        .pulse {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            box-shadow: 0 0 10px #22c55e;
            animation: blink 1.5s infinite;
        }

        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }

        /* Table Design */
        .results-list { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: 20px; }
        .result-row { 
            background: var(--card); 
            transition: 0.3s; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .result-row td { padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
        .result-row td:first-child { 
            border-radius: 15px 0 0 15px; 
            font-weight: 800; 
            font-size: 1.4rem; 
            color: var(--accent);
            text-align: center;
            width: 70px;
        }
        .result-row td:last-child { border-radius: 0 15px 15px 0; text-align: right; }

        .name-box b { display: block; font-size: 1.1rem; }
        .name-box span { font-size: 0.8rem; color: #94a3b8; }

        .time-tag { 
            background: var(--primary); 
            color: white; 
            padding: 6px 15px; 
            border-radius: 50px; 
            font-weight: 700; 
            font-size: 0.9rem;
        }

        .no-data { 
            text-align: center; 
            padding: 60px; 
            background: var(--card); 
            border-radius: 20px; 
            color: #64748b; 
            border: 2px dashed rgba(255,255,255,0.1);
        }

        .btn-back {
            text-decoration: none;
            color: #94a3b8;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="admin_quiz.php?event_id=<?php echo $event_id; ?>" class="btn-back">← Admin Control</a>
    
    <div class="header">
        <h1>⚡ Speed Race</h1>
        <div class="status-bar">
            <?php if($active_q): ?>
                <div style="display: flex; align-items: center; justify-content: center;">
                    <span class="pulse"></span>
                    <span style="font-weight: 600; color: #22c55e;">QUESTION IS LIVE</span>
                </div>
                <p style="margin: 10px 0 0 0; font-size: 1.1rem;"><?php echo htmlspecialchars($active_q['question_text']); ?></p>
            <?php else: ?>
                <span style="color: #ef4444;">🔴 NO ACTIVE QUESTION</span>
                <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #64748b;">Push a question from the admin panel to see results.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if($results && mysqli_num_rows($results) > 0): ?>
        <table class="results-list">
            <?php 
            $rank = 1;
            while($row = mysqli_fetch_assoc($results)): ?>
                <tr class="result-row">
                    <td>#<?php echo $rank++; ?></td>
                    <td>
                        <div class="name-box">
                            <b><?php echo htmlspecialchars($row['name']); ?></b>
                            <span>Reg No: <?php echo htmlspecialchars($row['reg_no']); ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="time-tag"><?php echo $row['seconds_taken']; ?>s</span>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="no-data">
            <div style="font-size: 40px; margin-bottom: 10px;">⏱️</div>
            <p>Waiting for the first student to answer correctly...</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Refresh every 3 seconds to update the leaderboard live
    setTimeout(function(){
       location.reload();
    }, 3000);
</script>

</body>
</html>