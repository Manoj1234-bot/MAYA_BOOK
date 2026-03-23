<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$event_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

if($event_id == 0) {
    die("Invalid Event ID.");
}

$event_query = mysqli_query($conn, "SELECT event_name, event_date FROM events WHERE id = '$event_id'");
$event = mysqli_fetch_assoc($event_query);

if(!$event){ 
    die("Event not found."); 
}

$stats_query = "SELECT rating, COUNT(*) as count FROM bookings 
                WHERE event_id = '$event_id' AND rating > 0 
                GROUP BY rating ORDER BY rating ASC";
$stats_result = mysqli_query($conn, $stats_query);

$ratings_data = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
$total_ratings = 0;
$sum_ratings = 0;

while($row = mysqli_fetch_assoc($stats_result)){
    $ratings_data[$row['rating']] = (int)$row['count'];
    $total_ratings += $row['count'];
    $sum_ratings += ($row['rating'] * $row['count']);
}

$avg_rating = ($total_ratings > 0) ? round($sum_ratings / $total_ratings, 1) : "No ratings yet";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics - <?php echo htmlspecialchars($event['event_name']); ?></title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root { --primary: #5e43f3; --bg: #f4f7f6; }

body {
    font-family: 'Poppins', sans-serif;
    background: var(--bg);
    margin: 0;
    padding: 40px;
}

.container {
    max-width: 900px;
    margin: auto;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.05);
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 30px;
}
.header h1 {
    color: var(--primary);
    font-size: 24px;
}
.header p {
    color: #777;
    font-size: 13px;
}

/* Stats */
.stats-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 30px;
}
.stat-box {
    background: #f8f9ff;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
}
.stat-val {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary);
}
.stat-label {
    font-size: 11px;
    color: #777;
}

/* SMALL GRAPH */
.chart-box {
    height: 220px;
    max-width: 500px;
    margin: auto;
    padding: 10px;
}

/* Back button */
.back-link {
    display: inline-block;
    margin-top: 25px;
    text-decoration: none;
    color: #777;
    font-weight: 600;
}
.back-link:hover {
    color: var(--primary);
}

/* Mobile */
@media (max-width: 600px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="container">
<div class="card">

    <div class="header">
        <p>EVENT ANALYTICS #<?php echo $event_id; ?></p>
        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
        <p>📅 <?php echo date("d M Y", strtotime($event['event_date'])); ?></p>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-val"><?php echo $avg_rating; ?> <?php echo is_numeric($avg_rating) ? "⭐" : ""; ?></div>
            <div class="stat-label">Average Rating</div>
        </div>
        <div class="stat-box">
            <div class="stat-val"><?php echo $total_ratings; ?></div>
            <div class="stat-label">Total Reviews</div>
        </div>
    </div>

    <?php if($total_ratings > 0): ?>
        <div class="chart-box">
            <canvas id="ratingChart"></canvas>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:30px;">
            <p>No ratings yet</p>
        </div>
    <?php endif; ?>

    <a href="manage_events.php" class="back-link">← Back</a>

</div>
</div>

<script>
<?php if($total_ratings > 0): ?>
const ctx = document.getElementById('ratingChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['1⭐', '2⭐', '3⭐', '4⭐', '5⭐'],
        datasets: [{
            data: [
                <?php echo $ratings_data[1]; ?>,
                <?php echo $ratings_data[2]; ?>,
                <?php echo $ratings_data[3]; ?>,
                <?php echo $ratings_data[4]; ?>,
                <?php echo $ratings_data[5]; ?>
            ],
            backgroundColor: [
                '#ff7675',
                '#fab1a0',
                '#ffeaa7',
                '#55efc4',
                '#5e43f3'
            ],
            borderRadius: 6,
            barThickness: 18
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { grid: { display: false } },
            y: { display: false } // clean small look
        }
    }
});
<?php endif; ?>
</script>

</body>
</html>