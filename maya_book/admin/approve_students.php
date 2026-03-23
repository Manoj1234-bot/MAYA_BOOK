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

// Security check
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$message   = "";
$error_msg = "";

// 2. Handle Approval Action with Email
if (isset($_GET['approve_id'])) {
    $approve_id = mysqli_real_escape_string($conn, $_GET['approve_id']);

    // Fetch student details before approving
    $fetch = mysqli_query($conn, "SELECT * FROM students WHERE id = '$approve_id'");
    $student = mysqli_fetch_assoc($fetch);

    if ($student) {
        // Update status to approved
        mysqli_query($conn, "UPDATE students SET status='approved' WHERE id='$approve_id'");
        $message = "✅ " . htmlspecialchars($student['name']) . " has been approved successfully.";

        // 3. Send Welcome Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'manojsm692@gmail.com';
            $mail->Password   = 'eyxevpddpywzzgep'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('manojsm692@gmail.com', 'MayaBook Events');
            $mail->addAddress($student['email'], $student['name']);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to MayaBook — Your Account is Approved';

            $issued_on    = date('d M Y');
            $first_name   = explode(' ', $student['name'])[0];
            $student_dept = htmlspecialchars($student['department']);
            $student_reg  = htmlspecialchars($student['reg_no']);

            $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Welcome to MayaBook</title>
</head>
<body style='margin:0; padding:0; background-color:#f0f2f5; font-family: Georgia, \"Times New Roman\", serif;'>

  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f2f5; padding:40px 0;'>
    <tr>
      <td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px; width:100%;'>

          <!-- ── Header ── -->
          <tr>
            <td style='background:#0d1117; padding:28px 40px; border-radius:16px 16px 0 0; text-align:center; border-bottom:3px solid #c9a84c;'>
              <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td align='left' style='vertical-align:middle;'>
                    <span style='font-family:Georgia,serif; font-size:22px; font-weight:700; color:#ffffff; letter-spacing:0.03em;'>Maya<span style='color:#c9a84c;'>Book</span></span>
                    <span style='display:block; font-size:9px; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; color:rgba(255,255,255,0.35); margin-top:3px;'>Student Event Portal</span>
                  </td>
                  <td align='right' style='vertical-align:middle;'>
                    <span style='display:inline-block; background:rgba(45,212,191,0.12); border:1px solid rgba(45,212,191,0.3); color:#2dd4bf; font-size:10px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; padding:5px 12px; border-radius:20px;'>&#10003; &nbsp;Account Activated</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Hero ── -->
          <tr>
            <td style='background:linear-gradient(160deg,#141924 0%,#1c2433 100%); padding:48px 40px 40px; text-align:center;'>
              <div style='display:inline-block; width:72px; height:72px; background:linear-gradient(135deg,#c9a84c,#a87e2e); border-radius:50%; line-height:72px; font-size:32px; margin-bottom:22px; box-shadow:0 0 0 10px rgba(201,168,76,0.1);'>🎓</div>
              <h1 style='margin:0 0 10px; font-family:Georgia,serif; font-size:30px; font-weight:700; color:#ffffff; letter-spacing:-0.01em;'>Welcome aboard, {$first_name}!</h1>
              <p style='margin:0; font-size:15px; color:rgba(255,255,255,0.5); font-style:italic; font-family:Georgia,serif;'>Your MayaBook account has been officially activated.</p>
            </td>
          </tr>

          <!-- ── Greeting ── -->
          <tr>
            <td style='background:#ffffff; padding:36px 40px 0;'>
              <p style='margin:0 0 10px; font-size:16px; color:#1a1510; font-family:Georgia,serif;'>Dear <strong>" . htmlspecialchars($student['name']) . "</strong>,</p>
              <p style='margin:0; font-size:14px; color:#5c5246; line-height:1.8; font-family:Arial,sans-serif;'>
                We are pleased to inform you that your registration on the <strong style='color:#0d1117;'>MayaBook Student Event Portal</strong> has been reviewed and <strong style='color:#0f6e3a;'>officially approved</strong> by the administration. You now have full access to browse, register, and participate in all upcoming college events.
              </p>
              <div style='height:1px; background:linear-gradient(to right,transparent,#c9a84c,transparent); margin:28px 0 0;'></div>
            </td>
          </tr>

          <!-- ── Account Details ── -->
          <tr>
            <td style='background:#ffffff; padding:28px 40px;'>
              <p style='margin:0 0 14px; font-size:9px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif;'>Your Account Details</p>
              <table width='100%' cellpadding='0' cellspacing='0' style='background:#fdf8ef; border:1px solid #e8d9a8; border-radius:12px; overflow:hidden;'>
                <tr>
                  <td width='50%' style='padding:16px 22px; border-right:1px solid #f0e4bc; border-bottom:1px solid #f0e4bc; vertical-align:top;'>
                    <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:4px;'>Full Name</span>
                    <span style='font-size:14px; font-weight:700; color:#1a1510; font-family:Georgia,serif;'>" . htmlspecialchars($student['name']) . "</span>
                  </td>
                  <td width='50%' style='padding:16px 22px; border-bottom:1px solid #f0e4bc; vertical-align:top;'>
                    <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:4px;'>Registration No.</span>
                    <span style='font-size:14px; font-weight:700; color:#1a1510; font-family:\"Courier New\",monospace;'>{$student_reg}</span>
                  </td>
                </tr>
                <tr>
                  <td colspan='2' style='padding:16px 22px; vertical-align:top;'>
                    <span style='display:block; font-size:9px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif; margin-bottom:4px;'>Department</span>
                    <span style='font-size:14px; color:#3d3020; font-family:Arial,sans-serif;'>{$student_dept}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── What You Can Do ── -->
          <tr>
            <td style='background:#ffffff; padding:0 40px 32px;'>
              <p style='margin:0 0 16px; font-size:9px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; color:#b8972a; font-family:Arial,sans-serif;'>What You Can Do Now</p>

              <!-- Feature 1 -->
              <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:12px;'>
                <tr>
                  <td width='44' style='vertical-align:top; padding-top:2px;'>
                    <div style='width:36px; height:36px; background:linear-gradient(135deg,#818cf8,#a5b4fc); border-radius:10px; text-align:center; line-height:36px; font-size:16px;'>📅</div>
                  </td>
                  <td style='padding-left:14px; vertical-align:top;'>
                    <strong style='font-size:14px; color:#0d1117; font-family:Arial,sans-serif;'>Browse Upcoming Events</strong>
                    <p style='margin:3px 0 0; font-size:13px; color:#6b7280; line-height:1.5; font-family:Arial,sans-serif;'>Explore all seminars, workshops, and guest lectures available for your department.</p>
                  </td>
                </tr>
              </table>

              <!-- Feature 2 -->
              <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:12px;'>
                <tr>
                  <td width='44' style='vertical-align:top; padding-top:2px;'>
                    <div style='width:36px; height:36px; background:linear-gradient(135deg,#2dd4bf,#34d399); border-radius:10px; text-align:center; line-height:36px; font-size:16px;'>🎟️</div>
                  </td>
                  <td style='padding-left:14px; vertical-align:top;'>
                    <strong style='font-size:14px; color:#0d1117; font-family:Arial,sans-serif;'>Book Your Seat Instantly</strong>
                    <p style='margin:3px 0 0; font-size:13px; color:#6b7280; line-height:1.5; font-family:Arial,sans-serif;'>Reserve your place at any event with a single click. Seats are limited — book early.</p>
                  </td>
                </tr>
              </table>

              <!-- Feature 3 -->
              <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:12px;'>
                <tr>
                  <td width='44' style='vertical-align:top; padding-top:2px;'>
                    <div style='width:36px; height:36px; background:linear-gradient(135deg,#f43f5e,#fb7185); border-radius:10px; text-align:center; line-height:36px; font-size:16px;'>🎮</div>
                  </td>
                  <td style='padding-left:14px; vertical-align:top;'>
                    <strong style='font-size:14px; color:#0d1117; font-family:Arial,sans-serif;'>Join Live Quiz Sessions</strong>
                    <p style='margin:3px 0 0; font-size:13px; color:#6b7280; line-height:1.5; font-family:Arial,sans-serif;'>Participate in real-time quizzes during events and compete on the leaderboard.</p>
                  </td>
                </tr>
              </table>

              <!-- Feature 4 -->
              <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td width='44' style='vertical-align:top; padding-top:2px;'>
                    <div style='width:36px; height:36px; background:linear-gradient(135deg,#c9a84c,#a87e2e); border-radius:10px; text-align:center; line-height:36px; font-size:16px;'>🏆</div>
                  </td>
                  <td style='padding-left:14px; vertical-align:top;'>
                    <strong style='font-size:14px; color:#0d1117; font-family:Arial,sans-serif;'>Earn E-Certificates</strong>
                    <p style='margin:3px 0 0; font-size:13px; color:#6b7280; line-height:1.5; font-family:Arial,sans-serif;'>Download verified participation certificates for events you attend — shareable and print-ready.</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── CTA Button ── -->
          <tr>
            <td style='background:#ffffff; padding:0 40px 36px; text-align:center;'>
              <div style='height:1px; background:linear-gradient(to right,transparent,#c9a84c,transparent); margin-bottom:28px;'></div>
              <a href='http://yourdomain.com/student/dashboard.php' style='display:inline-block; background:linear-gradient(135deg,#c9a84c,#a87e2e); color:#0d1117; font-family:Arial,sans-serif; font-size:15px; font-weight:700; padding:16px 42px; border-radius:12px; text-decoration:none; letter-spacing:0.03em; box-shadow:0 6px 20px rgba(201,168,76,0.35);'>
                Go to My Dashboard &nbsp;→
              </a>
              <p style='margin:14px 0 0; font-size:12px; color:#9aa5b4; font-family:Arial,sans-serif;'>Log in with your registered email and password.</p>
            </td>
          </tr>

          <!-- ── Notice ── -->
          <tr>
            <td style='background:#ffffff; padding:0 40px 36px;'>
              <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f7ff; border:1px solid #bfdbfe; border-left:4px solid #3b82f6; border-radius:0 10px 10px 0;'>
                <tr>
                  <td style='padding:16px 20px;'>
                    <p style='margin:0; font-size:13px; color:#1e40af; line-height:1.6; font-family:Arial,sans-serif;'>
                      <strong>📋 Note:</strong> If you face any issues accessing your account or need assistance, please contact your department coordinator or reach out to the event administration team.
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
                This is an automated message issued on {$issued_on}.
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
            $message .= " Welcome email sent to " . htmlspecialchars($student['email']) . ".";
        } catch (Exception $e) {
            $error_msg = "Student approved, but email failed: " . $mail->ErrorInfo;
        }
    }
}

// 3. Fetch pending students
$query  = "SELECT * FROM students WHERE status='pending'";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Students - Maya Admin</title>
    <style>
        :root {
            --primary: #2c3e50;
            --accent:  #3498db;
            --success: #27ae60;
            --light:   #ecf0f1;
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f8f9fa; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 250px; background: var(--primary); color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 1.2rem; text-transform: uppercase; border-bottom: 1px solid #555; padding-bottom: 20px; margin-bottom: 20px; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px; margin: 5px 0; border-radius: 4px; transition: 0.3s; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; }

        /* Content */
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }

        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; background: #f4f7f6; padding: 15px; color: var(--primary); border-bottom: 2px solid #ddd; font-size: 13px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        tr:hover { background-color: #f9f9f9; }

        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; background: #f39c12; color: white; }

        .btn-approve { background: var(--success); color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-size: 14px; transition: 0.2s; display: inline-block; }
        .btn-approve:hover { background: #219150; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        .no-data { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="approve_students.php" class="active">✅ Approve Students</a>
    <a href="create_event.php">➕ Create Event</a>
    <a href="view_bookings.php">📋 View Bookings</a>
    <a href="attendance_list.php">📍 Attendance List</a>
    <a href="manage_approvals.php">Manage approval</a>
    <a href="manage_events.php">Manage events</a>
    <a href="admin_logout.php" style="margin-top:auto; color:#e74c3c;">🚪 Logout</a>
</div>

<div class="main-content">
    <h1>Pending Student Approvals</h1>
    <p>Review the list below to grant access to students. An official welcome email will be dispatched upon approval.</p>

    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if($error_msg): ?>
        <div class="alert alert-error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="table-container">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><span class="badge">Pending</span></td>
                        <td>
                            <a href="?approve_id=<?php echo $row['id']; ?>" class="btn-approve">Approve &amp; Email</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>🎉 All caught up!</h3>
                <p>No pending student approvals at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>