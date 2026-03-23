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

// 3. Count Total Questions for this event
$q_count_res = mysqli_query($conn, "SELECT COUNT(id) as total FROM quiz_questions WHERE event_id = '$event_id'");
$total_questions = mysqli_fetch_assoc($q_count_res)['total'];

// 4. Fetch ONLY students who booked this event and calculate their marks
// 4. Fetch students who have responses for THIS specific event
$results_query = "
    SELECT 
        s.name, 
        s.reg_no, 
        COUNT(CASE WHEN r.is_correct = 1 THEN 1 END) as total_marks
    FROM students s
    JOIN quiz_responses r ON s.id = r.student_id
    JOIN quiz_questions q ON r.question_id = q.id
    WHERE q.event_id = '$event_id'
    GROUP BY s.id
    ORDER BY total_marks DESC, s.name ASC";

$results = mysqli_query($conn, $results_query);

// Check if query was successful
if (!$results) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Final Results - <?php echo htmlspecialchars($event_data['event_name'] ?? 'Results'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #5e43f3; --bg: #f8fafc; --dark: #1e293b; --success: #22c55e; --danger: #ef4444; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--dark); padding: 40px; margin: 0; }
        
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        
        .score-badge { background: #f1f5f9; padding: 5px 12px; border-radius: 20px; font-weight: 700; color: var(--primary); }
        .btn-print { background: var(--dark); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        
        @media print { .no-print { display: none; } .container { box-shadow: none; border: 1px solid #eee; } }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <a href="admin_quiz.php?event_id=<?php echo $event_id; ?>" class="no-print" style="text-decoration:none; color:#64748b; font-size:13px;">← Back to Control</a>
            <h1 style="margin:5px 0 0 0; font-size:1.5rem; color:var(--primary);"><?php echo htmlspecialchars($event_data['event_name'] ?? 'Event Results'); ?></h1>
            <p style="margin:0; font-size:14px; color:#64748b;">Registered Student Marklist</p>
        </div>
        <button onclick="window.print()" class="btn-print no-print">Print Report</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Reg No</th>
                <th>Marks obtained</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($results) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($results)): 
                    $perc = ($total_questions > 0) ? ($row['total_marks'] / $total_questions) * 100 : 0;
                ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($row['name']); ?></b></td>
                    <td><code><?php echo htmlspecialchars($row['reg_no']); ?></code></td>
                    <td><span class="score-badge"><?php echo (int)$row['total_marks']; ?> / <?php echo (int)$total_questions; ?></span></td>
                    <td><b style="color: <?php echo ($perc >= 40) ? 'var(--success)' : 'var(--danger)'; ?>;"><?php echo number_format($perc, 1); ?>%</b></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center; padding:30px; color:#94a3b8;">No registered students found for this event.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>