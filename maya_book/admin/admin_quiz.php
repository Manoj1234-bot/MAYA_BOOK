<?php
session_start();
include "../includes/db.php";

// 1. Security Check
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// 2. Safety Check for Event ID
if(!isset($_GET['event_id']) || empty($_GET['event_id'])){
    die("<div style='padding:20px; color:red; font-family:sans-serif;'>Error: No Event ID provided.</div>");
}

$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);

// Fetch Event Name
$event_res = mysqli_query($conn, "SELECT event_name FROM events WHERE id='$event_id'");
$event_data = mysqli_fetch_assoc($event_res);

// 3. Handle "Next Question" logic + Speed Timestamp
if(isset($_POST['activate_id'])){
    $q_id = mysqli_real_escape_string($conn, $_POST['activate_id']);
    mysqli_query($conn, "UPDATE quiz_questions SET is_active = 0 WHERE event_id = '$event_id'");
    mysqli_query($conn, "UPDATE quiz_questions SET is_active = 1, activated_at = NOW() WHERE id = '$q_id'");
}

// 4. Handle "End Quiz"
if(isset($_POST['end_session'])){
    mysqli_query($conn, "UPDATE quiz_questions SET is_active = 0 WHERE event_id = '$event_id'");
    $session_ended = true; 
}

// 5. Fetch Event-Specific Leaderboard
$leaderboard_query = "
    SELECT s.name, COUNT(r.id) as total_score 
    FROM quiz_responses r
    JOIN students s ON r.student_id = s.id
    JOIN quiz_questions q ON r.question_id = q.id 
    WHERE r.is_correct = 1 AND q.event_id = '$event_id'
    GROUP BY s.id 
    ORDER BY total_score DESC, MAX(r.response_time) ASC 
    LIMIT 10";
$toppers = mysqli_query($conn, $leaderboard_query);

// 6. Count Total Questions
$q_count_res = mysqli_query($conn, "SELECT COUNT(id) as total FROM quiz_questions WHERE event_id = '$event_id'");
$q_count = mysqli_fetch_assoc($q_count_res)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Quiz Control - <?php echo $event_data['event_name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #5e43f3; --danger: #ef4444; --success: #22c55e; --warning: #facc15; --dark: #0f172a; --card: #1e293b; --info: #3b82f6; }
        body { font-family: 'Poppins', sans-serif; background: var(--dark); color: white; padding: 40px; margin: 0; }
        .top-nav { max-width: 1200px; margin: 0 auto 20px auto; display: flex; justify-content: space-between; align-items: center; }
        .back-link { color: #94a3b8; text-decoration: none; font-size: 14px; }
        .grid { display: grid; grid-template-columns: 1fr 350px; gap: 30px; max-width: 1200px; margin: auto; }
        .card { background: var(--card); padding: 25px; border-radius: 15px; border: 1px solid #334155; }
        .btn { padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-block; cursor: pointer; border: none; font-family: inherit; color: white; }
        .btn-add { background: var(--success); }
        .btn-speed { background: var(--warning); color: var(--dark); }
        .btn-results { background: var(--info); }
        .btn-end { background: var(--danger); }
        .q-row { background: var(--dark); padding: 15px; border-radius: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; border: 1px solid transparent; }
        .q-row.active { border-color: var(--primary); background: #1e1b4b; box-shadow: 0 0 15px rgba(94, 67, 243, 0.2); }
        .btn-push { background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .topper-list { background: var(--card); padding: 20px; border-radius: 15px; border: 1px solid var(--primary); }
        .topper-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #334155; }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="dashboard.php" class="back-link">← Back to Admin Dashboard</a>
    <div style="font-size: 12px; color: #94a3b8;">Questions: <b><?php echo $q_count; ?></b></div>
</div>

<div class="grid">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 1.5rem;">Live Control</h2>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="add_question.php?event_id=<?php echo $event_id; ?>" class="btn btn-add">➕ Add</a>
                <a href="live_speed_results.php?event_id=<?php echo $event_id; ?>" target="_blank" class="btn btn-speed">⚡ Speed View</a>
                <a href="event_all_results.php?event_id=<?php echo $event_id; ?>" class="btn btn-results">📊 Full Results</a>

                <form method="POST" onsubmit="return confirm('Stop the quiz for all students?');">
                    <button type="submit" name="end_session" class="btn btn-end">🛑 End</button>
                </form>
            </div>
        </div>
        
        <p style="color: #94a3b8; margin-bottom: 20px;">Event: <b><?php echo htmlspecialchars($event_data['event_name']); ?></b></p>
        
        <?php if(isset($session_ended)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--danger); text-align: center;">
                🔴 Session Closed. Students can no longer see questions.
            </div>
        <?php endif; ?>

        <?php 
        $qs = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE event_id='$event_id'");
        if(mysqli_num_rows($qs) > 0) {
            while($q = mysqli_fetch_assoc($qs)): 
            ?>
                <div class="q-row <?php echo ($q['is_active']) ? 'active' : ''; ?>">
                    <div style="max-width: 70%;">
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($q['question_text']); ?></div>
                        <?php if($q['is_active']): ?> 
                            <span style="color:var(--success); font-size:11px; font-weight:700;">● LIVE NOW</span> 
                        <?php endif; ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="activate_id" value="<?php echo $q['id']; ?>">
                        <button type="submit" class="btn-push">
                            <?php echo ($q['is_active']) ? "Refresh" : "Push Live"; ?>
                        </button>
                    </form>
                </div>
            <?php endwhile; 
        } else {
            echo "<div style='text-align:center; padding:40px; color:#64748b;'>No questions found. Click 'Add' to begin.</div>";
        }
        ?>
    </div>

    <div class="topper-list">
        <h3 style="margin-top: 0; color: var(--warning);">🏆 Top 10 Leaderboard</h3>
        <?php 
        $rank = 1;
        if(mysqli_num_rows($toppers) > 0):
            while($t = mysqli_fetch_assoc($toppers)): ?>
                <div class="topper-item">
                    <span><b style="color:var(--warning); margin-right:5px;">#<?php echo $rank++; ?></b> <?php echo htmlspecialchars($t['name']); ?></span>
                    <span style="color: var(--primary); font-weight: 700;"><?php echo $t['total_score']; ?> pts</span>
                </div>
            <?php endwhile; 
        else: ?>
            <p style="font-size: 13px; color: #64748b;">Waiting for answers...</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>