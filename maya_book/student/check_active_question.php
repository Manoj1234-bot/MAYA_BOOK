<?php
include "../includes/db.php";
$event_id = intval($_GET['event_id']);

// Check for the active question
$res = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE event_id = '$event_id' AND is_active = 1 LIMIT 1");
$q = mysqli_fetch_assoc($res);

$response = [];

if ($q) {
    // There is an active question
    $response['new_question'] = true;
    $response['question_id'] = $q['id'];
    $response['html'] = '
        <h3>' . htmlspecialchars($q['question_text']) . '</h3>
        <button class="opt-btn" onclick="submitAnswer('.$q['id'].', 1)">' . htmlspecialchars($q['option1']) . '</button>
        <button class="opt-btn" onclick="submitAnswer('.$q['id'].', 2)">' . htmlspecialchars($q['option2']) . '</button>
        <button class="opt-btn" onclick="submitAnswer('.$q['id'].', 3)">' . htmlspecialchars($q['option3']) . '</button>
        <button class="opt-btn" onclick="submitAnswer('.$q['id'].', 4)">' . htmlspecialchars($q['option4']) . '</button>
    ';
} else {
    // No active question found (Admin clicked "End")
    $response['new_question'] = false;
    $response['quiz_ended'] = true; 
}

echo json_encode($response);