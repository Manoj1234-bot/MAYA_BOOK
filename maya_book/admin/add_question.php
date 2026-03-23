<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['admin'])){ header("Location: admin_login.php"); exit(); }

$message = "";

// Handle Form Submission
if(isset($_POST['submit_question'])){
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $q_text = mysqli_real_escape_string($conn, $_POST['question']);
    $opt_a = mysqli_real_escape_string($conn, $_POST['opt_a']);
    $opt_b = mysqli_real_escape_string($conn, $_POST['opt_b']);
    $opt_c = mysqli_real_escape_string($conn, $_POST['opt_c']);
    $opt_d = mysqli_real_escape_string($conn, $_POST['opt_d']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct']);

    $query = "INSERT INTO quiz_questions (event_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
              VALUES ('$event_id', '$q_text', '$opt_a', '$opt_b', '$opt_c', '$opt_d', '$correct')";
    
    if(mysqli_query($conn, $query)){
        $message = "✅ Question Added Successfully!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Quiz Question</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0f172a; color: white; padding: 40px; }
        .form-card { max-width: 600px; margin: auto; background: #1e293b; padding: 30px; border-radius: 15px; border: 1px solid #334155; }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: white; }
        .btn-save { background: #5e43f3; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-save:hover { background: #4e36d3; }
        label { font-size: 14px; color: #94a3b8; }
    </style>
</head>
<body>

<div class="form-card">
    <h2>Add New Question</h2>
    <?php if($message) echo "<p>$message</p>"; ?>

    <form method="POST">
        <label>Select Event</label>
        <select name="event_id" required>
            <?php 
            $events = mysqli_query($conn, "SELECT id, event_name FROM events");
            while($e = mysqli_fetch_assoc($events)) {
                echo "<option value='".$e['id']."'>".$e['event_name']."</option>";
            }
            ?>
        </select>

        <label>Question Text</label>
        <textarea name="question" placeholder="Enter question here..." required></textarea>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <input type="text" name="opt_a" placeholder="Option A" required>
            <input type="text" name="opt_b" placeholder="Option B" required>
            <input type="text" name="opt_c" placeholder="Option C" required>
            <input type="text" name="opt_d" placeholder="Option D" required>
        </div>

        <label>Correct Option</label>
        <select name="correct" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>

        <button type="submit" name="submit_question" class="btn-save">Save Question</button>
        <a href="admin_quiz.php?event_id=<?php echo $_POST['event_id'] ?? ''; ?>" style="display:block; text-align:center; color:#94a3b8; margin-top:15px; text-decoration:none;">Go to Quiz Control →</a>
    </form>
</div>


</body>
</html>