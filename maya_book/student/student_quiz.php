<?php
session_start();
include "../includes/db.php";

// 1. Get Event ID
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if($event_id === 0) {
    die("<b style='color:red;'>Error:</b> No Event ID provided. Please join via the event link.");
}

// 2. Fetch Event Details
$stmt = $conn->prepare("SELECT event_name FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_res = $stmt->get_result();
$event = $event_res->fetch_assoc();

if(!$event) { 
    // This triggers if the ID (e.g. 99) doesn't exist in the 'events' table
    die("<b style='color:orange;'>Notice:</b> Event #$event_id does not exist in the database."); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Quiz: <?php echo $event['event_name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #5e43f3; --dark: #0f172a; --glass: rgba(255, 255, 255, 0.05); }
        body { font-family: 'Poppins', sans-serif; background: var(--dark); color: white; margin: 0; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        
        .quiz-card { width: 100%; max-width: 500px; background: var(--glass); backdrop-filter: blur(10px); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); text-align: center; }
        
        #quiz-area { min-height: 200px; display: flex; flex-direction: column; justify-content: center; }

        .opt-btn { 
            display: block; width: 100%; padding: 15px; margin: 10px 0; 
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); 
            border-radius: 12px; color: white; cursor: pointer; font-size: 16px; transition: 0.3s;
        }
        .opt-btn:hover { background: var(--primary); transform: translateY(-2px); }
        .opt-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Waiting Animation */
        .pulse { width: 12px; height: 12px; background: var(--primary); border-radius: 50%; display: inline-block; margin-right: 10px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { transform: scale(0.9); opacity: 1; } 70% { transform: scale(2); opacity: 0; } 100% { transform: scale(0.9); opacity: 0; } }
    </style>
</head>
<body>

<div class="quiz-card">
    <h2 style="margin-top:0; font-size: 1.2rem; color: var(--primary);"><?php echo htmlspecialchars($event['event_name']); ?></h2>
    
    <div id="quiz-area">
        <div class="pulse"></div>
        <p>Waiting for the admin to start the next question...</p>
    </div>
</div>

<script>
let lastQuestionId = 0;

// 1. Function to check for new questions
function checkQuestion() {
    fetch('check_active_question.php?event_id=<?php echo $event_id; ?>')
    .then(response => response.json())
    .then(data => {
        if(data.new_question) {
            // A question is live
            if(data.question_id !== lastQuestionId) {
                lastQuestionId = data.question_id;
                document.getElementById('quiz-area').innerHTML = data.html;
            }
        } else if (data.quiz_ended) {
            // ADMIN ENDED THE SESSION
            lastQuestionId = 0; // Reset so it can detect a new question if admin restarts
            document.getElementById('quiz-area').innerHTML = `
                <div style="padding: 20px;">
                    <h2 style="color: #ef4444;">🔴 Live Session Ended</h2>
                    <p style="color: #94a3b8;">The admin has closed this session. <br> Thank you for participating!</p>
                    <a href="dashboard.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Go to Dashboard</a>
                </div>
            `;
        }
    });
}

// 2. Function to submit answer
function submitAnswer(qId, option) {
    // Disable all buttons immediately after clicking
    const buttons = document.querySelectorAll('.opt-btn');
    buttons.forEach(btn => btn.disabled = true);

    const formData = new FormData();
    formData.append('question_id', qId);
    formData.append('selected_option', option);
    formData.append('event_id', '<?php echo $event_id; ?>');

    fetch('submit_answer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('quiz-area').innerHTML = "<h3>✅ Answer Submitted!</h3><p>Waiting for the next round...</p>";
        } else {
            alert(data.message);
        }
    });
}

// Run the check every 3 seconds
setInterval(checkQuestion, 3000);
</script>

</body>
</html>