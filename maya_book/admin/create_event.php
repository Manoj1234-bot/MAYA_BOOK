<?php
session_start();
include "../includes/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

// 1. Security Check
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$message    = "";
$msg_type   = "";
$sent_count = 0;
$fail_count = 0;

// 2. Handle Form Submission
if(isset($_POST['create'])){
    $event_name           = mysqli_real_escape_string($conn, $_POST['event_name']);
    $depts                = isset($_POST['departments']) ? implode(",", $_POST['departments']) : "All";
    $description          = mysqli_real_escape_string($conn, $_POST['description']);
    $total_seats          = intval($_POST['total_seats']);
    $event_date           = $_POST['event_date'];
    $booking_deadline     = $_POST['booking_deadline'];
    $booking_time         = $_POST['booking_time'];
    $approval_required    = intval($_POST['approval_type']);
    $has_certificate      = isset($_POST['has_certificate']) ? 1 : 0;
    $certificate_content  = mysqli_real_escape_string($conn, $_POST['certificate_content'] ?? '');

    $query = "INSERT INTO events(event_name, department, description, total_seats, event_date, booking_deadline, booking_time, approval_required, has_certificate, certificate_content) 
              VALUES('$event_name', '$depts', '$description', '$total_seats', '$event_date', '$booking_deadline', '$booking_time', '$approval_required', '$has_certificate', '$certificate_content')";

    if(mysqli_query($conn, $query)){

        // 3. Send bulk email to relevant students
        $dept_list = isset($_POST['departments']) ? $_POST['departments'] : [];
        $is_all    = in_array('All', $dept_list) || empty($dept_list);

        if($is_all){
            $students_res = mysqli_query($conn, "SELECT name, email FROM students WHERE status='approved'");
        } else {
            $dept_in      = "'" . implode("','", array_map(function($d) use ($conn){ return mysqli_real_escape_string($conn, $d); }, $dept_list)) . "'";
            $students_res = mysqli_query($conn, "SELECT name, email FROM students WHERE status='approved' AND department IN ($dept_in)");
        }

        $formatted_event_date    = date('l, F d, Y', strtotime($event_date));
        $formatted_deadline_date = date('D, M j, Y', strtotime($booking_deadline));
        $formatted_deadline_time = date('h:i A', strtotime($booking_time));
        // FIX: approval_required=1 means Auto-Approve, 0 means Manual
        $approval_label          = ($approval_required == 1) ? 'Auto-Approved' : 'Manual Approval Required';
        $issued_on               = date('d M Y, h:i A');
        $dept_display            = ($depts === 'All') ? 'All Departments' : implode(', ', $_POST['departments']);

        if($students_res && mysqli_num_rows($students_res) > 0){
            while($rec = mysqli_fetch_assoc($students_res)){
                $name_parts = explode(' ', htmlspecialchars($rec['name']));
                $first_name = $name_parts[0];
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'manojsm692@gmail.com';
                    $mail->Password   = 'eyxevpddpywzzgep';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('manojsm692@gmail.com', 'MayaBook Events');
                    $mail->addAddress($rec['email'], $rec['name']);
                    $mail->isHTML(true);
                    $mail->Subject = 'New Event: ' . htmlspecialchars($event_name) . ' — Book Your Seat Now';

                    // Description row (only if not empty)
                    $description_row = '';
                    if(!empty($description)){
                        $description_row = "
                        <tr><td style='padding:14px 24px;border-bottom:1px solid #f0e4bc;'>
                            <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>About</span>
                            <span style='font-size:13px;color:#3d3020;line-height:1.6;font-family:Arial,sans-serif;'>" . htmlspecialchars($description) . "</span>
                        </td></tr>";
                    }

                    // Certificate badge row (only if applicable)
                    $cert_row = '';
                    if($has_certificate){
                        $cert_row = "
                        <tr><td style='background:#fff;padding:0 40px 24px;'>
                            <table width='100%' cellpadding='0' cellspacing='0' style='background:linear-gradient(135deg,rgba(201,168,76,0.08),rgba(201,168,76,0.04));border:1px solid rgba(201,168,76,0.25);border-radius:12px;'>
                                <tr>
                                    <td width='40' style='padding:14px 10px 14px 20px;vertical-align:middle;'>
                                        <span style='font-size:20px;'>&#127942;</span>
                                    </td>
                                    <td style='padding:14px 20px 14px 0;vertical-align:middle;'>
                                        <span style='font-size:13px;color:#8a6e1e;font-family:Arial,sans-serif;'><strong style='color:#6b5010;'>Certificate of Participation</strong> will be issued to all students who attend this event.</span>
                                    </td>
                                </tr>
                            </table>
                        </td></tr>";
                    }

                    $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1.0'></head>
<body style='margin:0;padding:0;background:#f0f2f5;font-family:Georgia,\"Times New Roman\",serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f2f5;padding:40px 0;'>
  <tr><td align='center'>
    <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px;width:100%;'>

      <!-- Header -->
      <tr><td style='background:#0d1117;padding:26px 40px;border-radius:16px 16px 0 0;border-bottom:3px solid #c9a84c;'>
        <table width='100%' cellpadding='0' cellspacing='0'>
          <tr>
            <td align='left' style='vertical-align:middle;'>
              <span style='font-family:Georgia,serif;font-size:21px;font-weight:700;color:#fff;'>Maya<span style='color:#c9a84c;'>Book</span></span>
              <span style='display:block;font-size:9px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-top:3px;'>Student Event Portal</span>
            </td>
            <td align='right' style='vertical-align:middle;'>
              <span style='display:inline-block;background:rgba(201,168,76,0.12);border:1px solid rgba(201,168,76,0.3);color:#e8c96a;font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;padding:5px 12px;border-radius:20px;'>&#128197; New Event</span>
            </td>
          </tr>
        </table>
      </td></tr>

      <!-- Hero -->
      <tr><td style='background:linear-gradient(160deg,#141924 0%,#1c2433 100%);padding:46px 40px 38px;text-align:center;'>
        <div style='display:inline-block;width:68px;height:68px;background:linear-gradient(135deg,#c9a84c,#a87e2e);border-radius:50%;line-height:68px;font-size:28px;margin-bottom:20px;box-shadow:0 0 0 10px rgba(201,168,76,0.1);'>&#127891;</div>
        <h1 style='margin:0 0 8px;font-family:Georgia,serif;font-size:26px;font-weight:700;color:#fff;letter-spacing:-0.01em;'>New Event Just Published!</h1>
        <p style='margin:0;font-size:14px;color:rgba(255,255,255,0.45);font-style:italic;'>A new opportunity awaits you &mdash; seats are limited.</p>
      </td></tr>

      <!-- Greeting -->
      <tr><td style='background:#fff;padding:34px 40px 0;'>
        <p style='margin:0 0 10px;font-size:16px;color:#1a1510;font-family:Georgia,serif;'>Dear <strong>{$first_name}</strong>,</p>
        <p style='margin:0;font-size:14px;color:#5c5246;line-height:1.8;font-family:Arial,sans-serif;'>
          We are excited to announce a new event has been published on the MayaBook portal, specifically for your department.
          Seats are limited &mdash; we encourage you to register at the earliest to secure your place.
        </p>
        <div style='height:1px;background:linear-gradient(to right,transparent,#c9a84c,transparent);margin:26px 0 0;'></div>
      </td></tr>

      <!-- Event detail card -->
      <tr><td style='background:#fff;padding:26px 40px;'>
        <p style='margin:0 0 14px;font-size:9px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;'>Event Details</p>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#fdf8ef;border:1px solid #e8d9a8;border-radius:14px;overflow:hidden;'>
          <!-- Event name -->
          <tr><td style='padding:18px 24px;border-bottom:1px solid #f0e4bc;'>
            <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>Event</span>
            <span style='font-size:18px;font-weight:700;color:#1a1510;font-family:Georgia,serif;'>" . htmlspecialchars($event_name) . "</span>
          </td></tr>
          {$description_row}
          <!-- Date + Seats + Deadline -->
          <tr><td style='padding:0;'>
            <table width='100%' cellpadding='0' cellspacing='0'>
              <tr>
                <td width='33%' style='padding:14px 20px;border-right:1px solid #f0e4bc;vertical-align:top;'>
                  <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>Event Date</span>
                  <span style='font-size:12.5px;color:#3d3020;font-family:Arial,sans-serif;'>{$formatted_event_date}</span>
                </td>
                <td width='33%' style='padding:14px 20px;border-right:1px solid #f0e4bc;vertical-align:top;'>
                  <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>Booking Deadline</span>
                  <span style='font-size:12.5px;color:#3d3020;font-family:Arial,sans-serif;'>{$formatted_deadline_date}<br><strong>{$formatted_deadline_time}</strong></span>
                </td>
                <td width='34%' style='padding:14px 20px;vertical-align:top;'>
                  <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>Seats Available</span>
                  <span style='font-size:17px;font-weight:700;color:#1a1510;font-family:Georgia,serif;'>{$total_seats}</span>
                </td>
              </tr>
            </table>
          </td></tr>
          <!-- Dept + Approval -->
          <tr><td style='padding:0;'>
            <table width='100%' cellpadding='0' cellspacing='0'>
              <tr>
                <td width='50%' style='padding:14px 20px;border-right:1px solid #f0e4bc;border-top:1px solid #f0e4bc;vertical-align:top;'>
                  <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>For Department</span>
                  <span style='font-size:13px;color:#3d3020;font-family:Arial,sans-serif;'>{$dept_display}</span>
                </td>
                <td width='50%' style='padding:14px 20px;border-top:1px solid #f0e4bc;vertical-align:top;'>
                  <span style='display:block;font-size:9px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#b8972a;font-family:Arial,sans-serif;margin-bottom:4px;'>Booking Type</span>
                  <span style='font-size:13px;color:#3d3020;font-family:Arial,sans-serif;'>{$approval_label}</span>
                </td>
              </tr>
            </table>
          </td></tr>
        </table>
      </td></tr>

      {$cert_row}

      <!-- Urgency notice -->
      <tr><td style='background:#fff;padding:0 40px 32px;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#fff8ed;border:1px solid #fde68a;border-left:4px solid #f59e0b;border-radius:0 10px 10px 0;'>
          <tr><td style='padding:14px 20px;'>
            <p style='margin:0;font-size:13px;color:#92400e;line-height:1.6;font-family:Arial,sans-serif;'>
              <strong>&#9203; Act Fast:</strong> Booking closes on <strong>{$formatted_deadline_date} at {$formatted_deadline_time}</strong>. Once seats are filled, you may only join the waitlist.
            </p>
          </td></tr>
        </table>
      </td></tr>

      <!-- CTA -->
      <tr><td style='background:#fff;padding:0 40px 36px;text-align:center;'>
        <div style='height:1px;background:linear-gradient(to right,transparent,#c9a84c,transparent);margin-bottom:28px;'></div>
        <a href='http://yourdomain.com/student/events.php' style='display:inline-block;background:linear-gradient(135deg,#c9a84c,#a87e2e);color:#0d1117;font-family:Arial,sans-serif;font-size:15px;font-weight:700;padding:15px 42px;border-radius:12px;text-decoration:none;letter-spacing:0.03em;box-shadow:0 6px 20px rgba(201,168,76,0.35);'>
          Book My Seat Now &nbsp;&#8594;
        </a>
        <p style='margin:12px 0 0;font-size:12px;color:#9aa5b4;font-family:Arial,sans-serif;'>Log in to your MayaBook dashboard to complete your registration.</p>
      </td></tr>

      <!-- Sign-off -->
      <tr><td style='background:#fff;padding:0 40px 32px;'>
        <div style='height:1px;background:linear-gradient(to right,transparent,rgba(201,168,76,0.3),transparent);margin-bottom:22px;'></div>
        <p style='margin:0 0 3px;font-size:14px;color:#1a1510;font-family:Georgia,serif;'>With regards,</p>
        <p style='margin:0 0 2px;font-size:15px;font-weight:700;color:#0d1117;font-family:Georgia,serif;'>The MayaBook Events Team</p>
        <p style='margin:0;font-size:12px;color:#9aa5b4;font-family:Arial,sans-serif;'>RNS First Grade College &nbsp;&#183;&nbsp; Student Event Portal</p>
      </td></tr>

      <!-- Footer -->
      <tr><td style='background:#0d1117;padding:18px 40px;border-radius:0 0 16px 16px;text-align:center;'>
        <p style='margin:0 0 3px;font-size:11px;color:rgba(255,255,255,0.3);font-family:Arial,sans-serif;'>Published on {$issued_on}. This is an automated event notification.</p>
        <p style='margin:0;font-size:11px;color:rgba(255,255,255,0.2);font-family:Arial,sans-serif;'>Sent via <strong style='color:#c9a84c;'>MayaBook v3.0</strong> &nbsp;&#183;&nbsp; Managed by Manoj S &nbsp;&#183;&nbsp; Do not reply.</p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body></html>";

                    $mail->send();
                    $sent_count++;
                } catch (Exception $e) {
                    $fail_count++;
                }
            }
        }

        if($fail_count === 0 && $sent_count > 0){
            $message  = "Event published successfully! Notification sent to <strong>{$sent_count}</strong> student(s).";
            $msg_type = 'success';
        } elseif($sent_count > 0){
            $message  = "Event published. Sent to <strong>{$sent_count}</strong> student(s). <strong>{$fail_count}</strong> email(s) failed.";
            $msg_type = 'warn';
        } elseif($sent_count === 0 && $fail_count === 0){
            $message  = "Event published successfully! No approved students found in the selected department(s).";
            $msg_type = 'success';
        } else {
            $message  = "Event published, but all <strong>{$fail_count}</strong> notification email(s) failed. Check SMTP settings.";
            $msg_type = 'warn';
        }
    } else {
        $message  = "Database Error: " . mysqli_error($conn);
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MayaBook — Create Event</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:       #0d1117;
            --ink-2:     #1c2433;
            --muted:     #6b7a91;
            --muted-2:   #9aa5b4;
            --line:      rgba(255,255,255,0.07);
            --gold:      #c9a84c;
            --gold-lt:   #e8c96a;
            --teal:      #2dd4bf;
            --rose:      #f43f5e;
            --violet:    #818cf8;
            --violet-lt: #a5b4fc;
            --amber:     #f59e0b;
            --surface:   rgba(255,255,255,0.035);
            --surface-h: rgba(255,255,255,0.065);
            --radius-lg: 20px;
            --radius-xl: 26px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

        body {
            font-family:'DM Sans',sans-serif;
            background:var(--ink); color:#e2e8f0;
            min-height:100vh; display:flex; overflow-x:hidden;
        }

        body::before {
            content:'';position:fixed;inset:0;
            background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
            opacity:0.025;pointer-events:none;z-index:0;
        }

        .amb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;}
        .amb-1{width:480px;height:480px;background:rgba(201,168,76,0.07);top:-80px;right:-60px;}
        .amb-2{width:360px;height:360px;background:rgba(45,212,191,0.04);bottom:0;left:-80px;}

        /* Sidebar */
        .sidebar{width:260px;flex-shrink:0;background:var(--ink-2);border-right:1px solid var(--line);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;z-index:10;animation:slideInLeft 0.6s cubic-bezier(0.16,1,0.3,1) both;}
        @keyframes slideInLeft{from{transform:translateX(-30px);opacity:0;}to{transform:translateX(0);opacity:1;}}

        .sidebar-brand{padding:36px 28px 28px;border-bottom:1px solid var(--line);}
        .brand-mark{display:flex;align-items:center;gap:10px;}
        .brand-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--gold),var(--gold-lt));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
        .brand-name{font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:600;color:#fff;}
        .brand-name span{color:var(--gold);}
        .brand-sub{margin-top:14px;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);}

        .nav-section{padding:24px 16px 0;flex:1;}
        .nav-label{font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);padding:0 12px;margin-bottom:10px;}
        .nav-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;margin-bottom:4px;text-decoration:none;color:var(--muted-2);font-size:0.875rem;font-weight:500;transition:all 0.25s ease;position:relative;}
        .nav-link .nav-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;background:transparent;transition:all 0.25s ease;flex-shrink:0;}
        .nav-link:hover{color:#fff;background:var(--surface);}
        .nav-link:hover .nav-icon{background:var(--surface-h);}
        .nav-link.active{color:#fff;background:linear-gradient(135deg,rgba(201,168,76,0.15),rgba(201,168,76,0.05));border:1px solid rgba(201,168,76,0.22);}
        .nav-link.active .nav-icon{background:linear-gradient(135deg,var(--gold),var(--gold-lt));color:var(--ink);}
        .nav-link.active::before{content:'';position:absolute;left:0;top:25%;bottom:25%;width:3px;background:var(--gold);border-radius:0 3px 3px 0;}
        .sidebar-footer{padding:20px 16px;border-top:1px solid var(--line);}
        .nav-link.danger{color:#ff7675;}
        .nav-link.danger:hover{background:rgba(255,118,117,0.08);color:#ff7675;}

        /* Main */
        .main{flex:1;padding:48px 52px;position:relative;z-index:1;max-width:900px;}

        @keyframes fadeUp{from{transform:translateY(16px);opacity:0;}to{transform:translateY(0);opacity:1;}}

        .topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:40px;animation:fadeUp 0.55s 0.1s cubic-bezier(0.16,1,0.3,1) both;}
        .topbar-eyebrow{font-size:11px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--gold);margin-bottom:6px;}
        .topbar-title{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:600;color:#fff;line-height:1.1;letter-spacing:-0.02em;}
        .topbar-sub{margin-top:8px;font-size:0.875rem;color:var(--muted-2);font-weight:300;}

        .section-divider{height:1px;background:linear-gradient(to right,var(--gold),transparent);margin-bottom:32px;opacity:0.3;}

        /* Alert */
        .alert{border-radius:14px;padding:16px 20px;margin-bottom:28px;display:flex;align-items:flex-start;gap:12px;font-size:14px;line-height:1.6;animation:fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;}
        .alert-success{background:rgba(45,212,191,0.08);border:1px solid rgba(45,212,191,0.25);color:var(--teal);}
        .alert-warn{background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);color:var(--amber);}
        .alert-error{background:rgba(244,63,94,0.08);border:1px solid rgba(244,63,94,0.25);color:var(--rose);}

        /* Form card */
        .form-card{background:var(--ink-2);border:1px solid var(--line);border-radius:var(--radius-xl);overflow:hidden;animation:fadeUp 0.5s 0.18s cubic-bezier(0.16,1,0.3,1) both;}
        .form-card-head{padding:26px 32px 22px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:14px;}
        .form-head-icon{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--gold),var(--gold-lt));display:flex;align-items:center;justify-content:center;font-size:20px;}
        .form-head-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:600;color:#fff;}
        .form-head-sub{font-size:12px;color:var(--muted-2);margin-top:2px;}
        .form-body{padding:30px 32px;}

        /* Fields */
        .field{margin-bottom:22px;}
        .field label{display:block;font-size:11px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
        .field input,
        .field textarea,
        .field select{
            width:100%;background:rgba(255,255,255,0.04);border:1px solid var(--line);
            border-radius:12px;color:#fff;font-family:'DM Sans',sans-serif;
            font-size:14px;padding:13px 16px;outline:none;transition:border-color 0.2s;
            -webkit-appearance:none;
        }
        .field input:focus,.field textarea:focus,.field select:focus{border-color:rgba(201,168,76,0.45);}
        .field input[type="date"]::-webkit-calendar-picker-indicator,
        .field input[type="time"]::-webkit-calendar-picker-indicator{filter:invert(0.6);}
        .field textarea{resize:vertical;min-height:90px;line-height:1.6;}
        .field select option{background:var(--ink-2);color:#fff;}

        .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;}

        /* Department checkboxes */
        .dept-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;padding:16px;background:rgba(255,255,255,0.025);border:1px solid var(--line);border-radius:14px;}
        .dept-chip{position:relative;}
        .dept-chip input[type="checkbox"]{position:absolute;opacity:0;width:0;height:0;}
        .dept-chip label{
            display:flex;align-items:center;justify-content:center;
            padding:10px 8px;border-radius:10px;border:1px solid var(--line);
            font-size:13px;font-weight:500;color:var(--muted-2);
            cursor:pointer;transition:all 0.2s;text-align:center;
            background:rgba(255,255,255,0.02);
        }
        .dept-chip input:checked + label{
            background:rgba(201,168,76,0.12);border-color:rgba(201,168,76,0.35);color:var(--gold-lt);
        }
        .dept-chip label:hover{background:var(--surface-h);color:#fff;}

        /* Approval toggle */
        .approval-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
        .approval-opt{position:relative;}
        .approval-opt input{position:absolute;opacity:0;width:0;height:0;}
        .approval-opt label{
            display:block;padding:16px;border-radius:14px;
            border:1px solid var(--line);cursor:pointer;
            background:rgba(255,255,255,0.02);transition:all 0.25s;
            text-align:center;
        }
        .approval-opt label .opt-icon{font-size:22px;display:block;margin-bottom:6px;}
        .approval-opt label .opt-title{font-size:13px;font-weight:600;color:#fff;display:block;margin-bottom:3px;}
        .approval-opt label .opt-sub{font-size:11px;color:var(--muted-2);}
        .approval-opt input:checked + label{background:rgba(201,168,76,0.1);border-color:rgba(201,168,76,0.35);}
        .approval-opt label:hover{background:var(--surface-h);}

        /* Certificate toggle */
        .cert-toggle-wrap{padding:20px;background:rgba(201,168,76,0.05);border:1px solid rgba(201,168,76,0.18);border-radius:14px;}
        .cert-toggle-label{display:flex;align-items:center;gap:12px;cursor:pointer;margin-bottom:0;}
        .cert-toggle-label input[type="checkbox"]{
            width:18px;height:18px;border-radius:5px;cursor:pointer;
            accent-color:var(--gold);flex-shrink:0;
        }
        .cert-toggle-title{font-size:14px;font-weight:600;color:var(--gold-lt);}
        .cert-toggle-sub{font-size:12px;color:var(--muted-2);margin-top:2px;}

        #cert_content_area{
            margin-top:16px;padding-top:16px;
            border-top:1px solid rgba(201,168,76,0.15);
            display:none;
        }
        #cert_content_area.visible{display:block;}

        /* Email notify banner */
        .notify-banner{
            display:flex;align-items:flex-start;gap:12px;
            background:rgba(45,212,191,0.06);border:1px solid rgba(45,212,191,0.2);
            border-radius:14px;padding:16px 18px;margin-bottom:24px;
            animation:fadeUp 0.5s 0.28s cubic-bezier(0.16,1,0.3,1) both;
        }
        .notify-icon{font-size:20px;flex-shrink:0;}
        .notify-text{font-size:13px;color:var(--muted-2);line-height:1.6;}
        .notify-text strong{color:#fff;font-weight:600;}

        /* Submit button */
        .btn-publish{
            width:100%;display:flex;align-items:center;justify-content:center;gap:10px;
            background:linear-gradient(135deg,var(--gold),#a87e2e);
            color:#0d1117;padding:16px;border-radius:14px;border:none;
            font-family:'DM Sans',sans-serif;font-weight:700;font-size:15px;
            cursor:pointer;transition:all 0.25s ease;
            box-shadow:0 6px 20px rgba(201,168,76,0.25);
            margin-top:8px;
        }
        .btn-publish:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(201,168,76,0.4);}
        .btn-publish:active{transform:scale(0.98);}

        /* Footer */
        .page-footer{margin-top:40px;padding-top:24px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;animation:fadeUp 0.5s 0.6s ease both;}
        .footer-brand{font-size:12px;color:var(--muted);}
        .footer-brand strong{color:var(--gold);font-weight:600;}
        .footer-links{display:flex;gap:20px;}
        .footer-links a{font-size:12px;color:var(--muted);text-decoration:none;transition:color 0.2s;}
        .footer-links a:hover{color:var(--gold-lt);}

        /* Mobile nav */
        .mobile-nav{display:none;position:fixed;bottom:16px;left:50%;transform:translateX(-50%);background:rgba(28,36,51,0.92);backdrop-filter:blur(16px);padding:10px 28px;border-radius:50px;border:1px solid var(--line);gap:28px;z-index:1000;}
        .mobile-nav a{color:var(--muted);font-size:20px;text-decoration:none;transition:all 0.2s;display:flex;}
        .mobile-nav a.active{color:var(--gold);filter:drop-shadow(0 0 6px var(--gold));}

        @media(max-width:860px){
            .sidebar{display:none;}
            .mobile-nav{display:flex;}
            .main{padding:32px 20px 100px;}
            .topbar{flex-direction:column;gap:14px;}
            .topbar-title{font-size:1.8rem;}
            .two-col{grid-template-columns:1fr;}
            .approval-row{grid-template-columns:1fr 1fr;}
        }
    </style>
</head>
<body>

<div class="amb amb-1"></div>
<div class="amb amb-2"></div>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-mark">
            <div class="brand-icon">📘</div>
            <div class="brand-name">Maya<span>Book</span></div>
        </div>
        <div class="brand-sub">Admin Panel</div>
    </div>
    <nav class="nav-section">
        <div class="nav-label">Management</div>
        <a href="dashboard.php"        class="nav-link"><span class="nav-icon">🏠</span> Dashboard</a>
        <a href="approve_students.php" class="nav-link"><span class="nav-icon">✅</span> Approve Students</a>
        <a href="create_event.php"     class="nav-link active"><span class="nav-icon">➕</span> Create Event</a>
        <a href="view_bookings.php"    class="nav-link"><span class="nav-icon">📋</span> View Bookings</a>
        <a href="attendance_list.php"  class="nav-link"><span class="nav-icon">📍</span> Attendance List</a>
        <a href="manage_approvals.php" class="nav-link"><span class="nav-icon">🔔</span> Manage Approvals</a>
        <a href="manage_events.php"    class="nav-link"><span class="nav-icon">🗂️</span> Manage Events</a>
        <a href="bulk_announce.php"    class="nav-link"><span class="nav-icon">📢</span> Announcements</a>
    </nav>
    <div class="sidebar-footer">
        <a href="admin_logout.php" class="nav-link danger"><span class="nav-icon">🚪</span> Sign Out</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main">

    <div class="topbar">
        <div>
            <div class="topbar-eyebrow">➕ Admin &nbsp;·&nbsp; Event Management</div>
            <h1 class="topbar-title">Create New Event</h1>
            <p class="topbar-sub">Publish a seminar and automatically notify target department students.</p>
        </div>
    </div>

    <div class="section-divider"></div>

    <?php if(!empty($message)): ?>
    <div class="alert alert-<?php echo htmlspecialchars($msg_type); ?>">
        <span style="font-size:18px;"><?php echo $msg_type==='success' ? '🎉' : ($msg_type==='warn' ? '⚠️' : '❌'); ?></span>
        <span><?php echo $message; ?></span>
    </div>
    <?php endif; ?>

    <div class="notify-banner">
        <span class="notify-icon">📧</span>
        <div class="notify-text">
            <strong>Auto Email Notification:</strong> Once published, an official event announcement email will be sent automatically to all approved students in the selected department(s).
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-head">
            <div class="form-head-icon">📅</div>
            <div>
                <div class="form-head-title">Event Details</div>
                <div class="form-head-sub">All required fields must be filled before publishing.</div>
            </div>
        </div>

        <div class="form-body">
            <form method="POST" action="">

                <!-- Event name -->
                <div class="field">
                    <label>Event Name</label>
                    <input type="text" name="event_name" placeholder="e.g. Advanced Web Development Workshop" required>
                </div>

                <!-- Target departments -->
                <div class="field">
                    <label>Target Departments</label>
                    <div class="dept-grid">
                        <div class="dept-chip">
                            <input type="checkbox" name="departments[]" value="BCA" id="d_bca" checked>
                            <label for="d_bca">🖥️ BCA</label>
                        </div>
                        <div class="dept-chip">
                            <input type="checkbox" name="departments[]" value="BBA" id="d_bba">
                            <label for="d_bba">💼 BBA</label>
                        </div>
                        <div class="dept-chip">
                            <input type="checkbox" name="departments[]" value="B.Com" id="d_bcom">
                            <label for="d_bcom">📊 B.Com</label>
                        </div>
                        <div class="dept-chip">
                            <input type="checkbox" name="departments[]" value="BSc" id="d_bsc">
                            <label for="d_bsc">🔬 BSc</label>
                        </div>
                        <div class="dept-chip">
                            <input type="checkbox" name="departments[]" value="MBA" id="d_mba">
                            <label for="d_mba">🎓 MBA</label>
                        </div>
                        <div class="dept-chip">
                            <input type="checkbox" name="departments[]" value="All" id="d_all">
                            <label for="d_all">🌐 All</label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="field">
                    <label>Description</label>
                    <textarea name="description" placeholder="Briefly describe the seminar goals, topics, and what students will learn…"></textarea>
                </div>

                <!-- Seats + Event date -->
                <div class="two-col">
                    <div class="field">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" value="100" min="1" required>
                    </div>
                    <div class="field">
                        <label>Event Date</label>
                        <input type="date" name="event_date" required>
                    </div>
                </div>

                <!-- Deadline date + time -->
                <div class="two-col">
                    <div class="field">
                        <label>Booking Deadline Date</label>
                        <input type="date" name="booking_deadline" required>
                    </div>
                    <div class="field">
                        <label>Booking Deadline Time</label>
                        <input type="time" name="booking_time" value="23:59" required>
                    </div>
                </div>

                <!-- Approval type -->
                <div class="field">
                    <label>Approval Strategy</label>
                    <div class="approval-row">
                        <div class="approval-opt">
                            <input type="radio" name="approval_type" value="1" id="auto_approve" checked>
                            <label for="auto_approve">
                                <span class="opt-icon">⚡</span>
                                <span class="opt-title">Auto-Approve</span>
                                <span class="opt-sub">Bookings confirmed instantly</span>
                            </label>
                        </div>
                        <div class="approval-opt">
                            <input type="radio" name="approval_type" value="0" id="manual_approve">
                            <label for="manual_approve">
                                <span class="opt-icon">🛡️</span>
                                <span class="opt-title">Manual Approval</span>
                                <span class="opt-sub">Admin reviews each request</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Certificate -->
                <div class="field">
                    <label>Certificate</label>
                    <div class="cert-toggle-wrap">
                        <label class="cert-toggle-label">
                            <input type="checkbox" name="has_certificate" id="cert_check" onchange="toggleCert()">
                            <div>
                                <div class="cert-toggle-title">🏆 Issue Participation Certificate</div>
                                <div class="cert-toggle-sub">Students who attend will receive a downloadable e-certificate.</div>
                            </div>
                        </label>
                        <div id="cert_content_area">
                            <div class="field" style="margin-bottom:0;">
                                <label>Certificate Text</label>
                                <textarea name="certificate_content" rows="3" placeholder="e.g. This is to certify that [NAME] has participated in [EVENT] held on [DATE]."></textarea>
                                <div style="font-size:11px;color:var(--muted);margin-top:6px;">Use <code style="background:rgba(255,255,255,0.07);padding:1px 6px;border-radius:5px;font-size:11px;color:var(--violet-lt);">[NAME]</code>, <code style="background:rgba(255,255,255,0.07);padding:1px 6px;border-radius:5px;font-size:11px;color:var(--violet-lt);">[EVENT]</code>, <code style="background:rgba(255,255,255,0.07);padding:1px 6px;border-radius:5px;font-size:11px;color:var(--violet-lt);">[DATE]</code> as placeholders.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="create" class="btn-publish">
                    📤 &nbsp; Publish Event &amp; Notify Students
                </button>

            </form>
        </div>
    </div>

    <footer class="page-footer">
        <div class="footer-brand">MayaBook <strong>v3.0</strong> &nbsp;·&nbsp; Designed by Manoj S</div>
        <div class="footer-links">
            <a href="#">Help</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </div>
    </footer>

</main>

<!-- Mobile nav -->
<nav class="mobile-nav">
    <a href="dashboard.php">🏠</a>
    <a href="create_event.php" class="active">➕</a>
    <a href="manage_events.php">🗂️</a>
    <a href="admin_logout.php">🚪</a>
</nav>

<script>
function toggleCert() {
    const check = document.getElementById('cert_check');
    const area  = document.getElementById('cert_content_area');
    area.classList.toggle('visible', check.checked);
}
</script>

</body>
</html>