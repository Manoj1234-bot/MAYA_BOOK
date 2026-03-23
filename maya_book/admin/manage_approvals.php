<?php
session_start();
include "../includes/db.php";

// 1. Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

// Security: Check if admin is logged in
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$message = "";
$error_msg = "";

// 2. Handle Approval/Rejection Actions
if(isset($_GET['action']) && isset($_GET['id'])){
    $booking_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if($action == 'approve'){
        // Fetch student and event details for the email
        $info_sql = "SELECT s.email, s.name as student_name, e.event_name, e.event_date, e.venue, b.booking_ref, b.seat_no 
                     FROM bookings b 
                     JOIN students s ON b.student_id = s.id 
                     JOIN events e ON b.event_id = e.id 
                     WHERE b.id = '$booking_id'";
        $info_res = mysqli_query($conn, $info_sql);
        $data = mysqli_fetch_assoc($info_res);

        if($data) {
            // Update Status in Database
            $update = "UPDATE bookings SET status = 'approved' WHERE id = '$booking_id'";
            
            if(mysqli_query($conn, $update)){
                $message = "✅ " . $data['student_name'] . "'s seat is confirmed!";
                
                // 3. SEND CONFIRMATION EMAIL
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'manojsm692@gmail.com';
                    $mail->Password   = 'eyxevpddpywzzgep'; // App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('manojsm692@gmail.com', 'Maya Book Events');
                    $mail->addAddress($data['email']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Seat Confirmed — ' . $data['event_name'] . ' | MayaBook';

                    // ── OFFICIAL EMAIL BODY ──
                    $formatted_date = date('l, F d, Y', strtotime($data['event_date']));
                    $issued_on      = date('d M Y');

                    $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Seat Confirmation</title>
</head>
<body style='margin:0; padding:0; background-color:#f0f2f5; font-family: Georgia, \"Times New Roman\", serif;'>

  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f2f5; padding: 40px 0;'>
    <tr>
      <td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px; width:100%;'>

          <!-- ── Header Bar ── -->
          <tr>
            <td style='background: #0d1117; padding: 28px 40px; border-radius: 16px 16px 0 0; text-align:center; border-bottom: 3px solid #c9a84c;'>
              <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td align='left' style='vertical-align:middle;'>
                    <span style='font-family: Georgia, serif; font-size:22px; font-weight:700; color:#ffffff; letter-spacing:0.03em;'>Maya<span style='color:#c9a84c;'>Book</span></span>
                    <span style='display:block; font-size:9px; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; color:rgba(255,255,255,0.35); margin-top:3px;'>Student Event Portal</span>
                  </td>
                  <td align='right' style='vertical-align:middle;'>
                    <span style='display:inline-block; background:rgba(45,212,191,0.12); border:1px solid rgba(45,212,191,0.3); color:#2dd4bf; font-size:10px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; padding:5px 12px; border-radius:20px;'>&#10003; &nbsp;Confirmed</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Hero Section ── -->
          <tr>
            <td style='background: linear-gradient(160deg, #141924 0%, #1c2433 100%); padding: 44px 40px 36px; text-align:center;'>
              <!-- Gold seal icon -->
              <div style='display:inline-block; width:64px; height:64px; background:linear-gradient(135deg,#c9a84c,#a87e2e); border-radius:50%; line-height:64px; font-size:28px; margin-bottom:20px; box-shadow: 0 0 0 8px rgba(201,168,76,0.12);'>🏛️</div>
              <h1 style='margin:0 0 8px; font-family:Georgia,serif; font-size:28px; font-weight:700; color:#ffffff; letter-spacing:-0.01em;'>Your Seat is Confirmed</h1>
              <p style='margin:0; font-size:15px; color:rgba(255,255,255,0.5); font-style:italic; font-family:Georgia,serif;'>We look forward to your presence at the event.</p>
            </td>
          </tr>

          <!-- ── Greeting ── -->
          <tr>
            <td style='background:#ffffff; padding: 36px 40px 0;'>
              <p style='margin:0 0 6px; font-size:16px; color:#1a1510; font-family:Georgia,serif;'>Dear <strong>{$data['student_name']}</strong>,</p>
              <p style='margin:0; font-size:14px; color:#5c5246; line-height:1.75; font-family: Arial, sans-serif; font-style:normal;'>
                We are pleased to inform you that your registration request has been reviewed and <strong style='color:#0f6e3a;'>officially approved</strong> by the administration. Please find your event details and entry credentials below.
              </p>
              <!-- Gold rule -->
              <div style='height:1px; background:linear-gradient(to right,transparent,#c9a84c,transparent); margin:28px 0 0;'></div>
            </td>
          </tr>

          <!-- ── Event Details Block ── -->
          <tr>
            <td style='background:#ffffff; padding:28px 40px;'>
              <p style='margin:0 0 14px; font-size:9px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif;'>Event Details</p>

              <table width='100%' cellpadding='0' cellspacing='0' style='background:#fdf8ef; border:1px solid #e8d9a8; border-radius:12px; overflow:hidden;'>
                <tr>
                  <td style='padding:20px 24px; border-bottom:1px solid #f0e4bc;'>
                    <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:4px;'>Event Name</span>
                    <span style='font-size:17px; font-weight:700; color:#1a1510; font-family:Georgia,serif;'>{$data['event_name']}</span>
                  </td>
                </tr>
                <tr>
                  <td style='padding:0;'>
                    <table width='100%' cellpadding='0' cellspacing='0'>
                      <tr>
                        <td width='50%' style='padding:16px 24px; border-right:1px solid #f0e4bc; vertical-align:top;'>
                          <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:4px;'>Date</span>
                          <span style='font-size:13px; color:#3d3020; font-family:Arial,sans-serif;'>{$formatted_date}</span>
                        </td>
                        <td width='50%' style='padding:16px 24px; vertical-align:top;'>
                          <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:4px;'>Venue</span>
                          <span style='font-size:13px; color:#3d3020; font-family:Arial,sans-serif;'>{$data['venue']}</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Entry Ticket Block ── -->
          <tr>
            <td style='background:#ffffff; padding:0 40px 32px;'>
              <p style='margin:0 0 14px; font-size:9px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif;'>Your Entry Credentials</p>

              <!-- Dashed ticket -->
              <table width='100%' cellpadding='0' cellspacing='0' style='border:2px dashed #c9a84c; border-radius:14px; background:#fffdf7;'>
                <tr>
                  <td style='padding:28px; text-align:center;'>
                    <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:10px;'>Reference Number</span>
                    <span style='display:block; font-size:36px; font-weight:700; color:#0d1117; letter-spacing:0.12em; font-family: \"Courier New\", Courier, monospace; line-height:1;'>{$data['booking_ref']}</span>
                    <div style='height:1px; background:linear-gradient(to right,transparent,#c9a84c,transparent); margin:18px auto; width:80%;'></div>
                    <span style='display:inline-block; background:#0d1117; color:#c9a84c; font-size:13px; font-weight:700; letter-spacing:0.1em; padding:8px 22px; border-radius:8px; font-family:Arial,sans-serif;'>SEAT &nbsp; {$data['seat_no']}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Notice ── -->
          <tr>
            <td style='background:#ffffff; padding:0 40px 36px;'>
              <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f7ff; border:1px solid #bfdbfe; border-left:4px solid #3b82f6; border-radius:0 10px 10px 0; padding:0;'>
                <tr>
                  <td style='padding:16px 20px;'>
                    <p style='margin:0; font-size:13px; color:#1e40af; line-height:1.6; font-family:Arial,sans-serif;'>
                      <strong>📋 Important:</strong> Please carry a screenshot of this email or log in to your MayaBook dashboard and show your booking reference at the entry desk on the day of the event.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Gold rule ── -->
          <tr>
            <td style='background:#ffffff; padding:0 40px;'>
              <div style='height:1px; background:linear-gradient(to right,transparent,#c9a84c,transparent);'></div>
            </td>
          </tr>

          <!-- ── Sign-off ── -->
          <tr>
            <td style='background:#ffffff; padding:28px 40px 36px;'>
              <p style='margin:0 0 4px; font-size:14px; color:#1a1510; font-family:Georgia,serif;'>With regards,</p>
              <p style='margin:0 0 2px; font-size:15px; font-weight:700; color:#0d1117; font-family:Georgia,serif;'>The MayaBook Events Team</p>
              <p style='margin:0; font-size:12px; color:#9aa5b4; font-family:Arial,sans-serif;'>RNS First Grade College &nbsp;·&nbsp; Student Event Portal</p>
            </td>
          </tr>

          <!-- ── Footer ── -->
          <tr>
            <td style='background:#0d1117; padding:20px 40px; border-radius:0 0 16px 16px; text-align:center;'>
              <p style='margin:0 0 4px; font-size:11px; color:rgba(255,255,255,0.3); font-family:Arial,sans-serif; letter-spacing:0.06em;'>
                This is an automated confirmation issued on {$issued_on}.
              </p>
              <p style='margin:0; font-size:11px; color:rgba(255,255,255,0.2); font-family:Arial,sans-serif;'>
                Sent via <strong style='color:#c9a84c;'>MayaBook v3.0</strong> &nbsp;·&nbsp; Managed by Manoj S &nbsp;·&nbsp; Do not reply to this email.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>";

                    $mail->send();
                    $message .= " Email notification sent successfully.";
                } catch (Exception $e) {
                    $error_msg = "Status updated, but email failed to send. Error: " . $mail->ErrorInfo;
                }
            }
        }
    } elseif ($action == 'reject') {
        $update = "UPDATE bookings SET status = 'rejected' WHERE id = '$booking_id'";
        if(mysqli_query($conn, $update)){
            $message = "❌ Booking request has been rejected.";
        }
    }
}

// 4. Fetch all pending bookings
$pending_query = "SELECT b.id, s.name as student_name, s.department, e.event_name, b.status 
                  FROM bookings b 
                  JOIN students s ON b.student_id = s.id 
                  JOIN events e ON b.event_id = e.id 
                  WHERE b.status = 'pending' 
                  ORDER BY b.id DESC";
$result = mysqli_query($conn, $pending_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Approvals - Maya Admin</title>
    <style>
        :root { --primary: #5e43f3; --dark: #1a1a2e; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); margin: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--dark); color: white; min-height: 100vh; padding: 30px 20px; box-sizing: border-box; }
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 40px; color: var(--primary); }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: var(--primary); color: white; }

        /* Content */
        .main-content { flex-grow: 1; padding: 40px; }
        .header { margin-bottom: 30px; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-size: 13px; text-transform: uppercase; color: #7f8c8d; }
        td { padding: 18px 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; color: #2d3436; }

        .btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px; cursor: pointer; border: none; transition: 0.3s; }
        .btn-approve { background: #e3fcef; color: #00b894; border: 1px solid #00b894; margin-right: 8px; }
        .btn-approve:hover { background: #00b894; color: white; }
        .btn-reject { background: #fff0f0; color: #ff7675; border: 1px solid #ff7675; }
        .btn-reject:hover { background: #ff7675; color: white; }
        
        .empty { padding: 40px; text-align: center; color: #95a5a6; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="approve_students.php">✅ Approve Students</a>
    <a href="create_event.php">➕ Create Event</a>
    <a href="view_bookings.php">📋 View Bookings</a>
    <a href="attendance_list.php">📍 Attendance List</a>
    <a href="manage_approvals.php">Manage approval</a>
    <a href="manage_events.php">Manage events</a>
    <a href="admin_logout.php" style="margin-top:auto; color:var(--danger);">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Pending Approvals</h1>
        <p>Review and confirm student event requests.</p>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if($error_msg): ?>
        <div class="alert alert-error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="card">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Event</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($row['student_name']); ?></b></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                            <td>
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-approve">Approve & Email</a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-reject" onclick="return confirm('Reject this request?')">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">
                <p>All clear! No pending requests at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>