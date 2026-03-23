# 📘 MayaBook — Student Event Booking Portal

> A full-stack web application for managing college events, student registrations, and automated email notifications — built for RNS First Grade College.

---

## 🌟 Overview

**MayaBook** is a college event management system that allows administrators to create and manage seminars/events, and students to discover and book seats — all in one place. The platform handles everything from seat booking and approval workflows to certificate generation and bulk email notifications.

---

## ✨ Features

### 👨‍💼 Admin Panel
- 🔐 Secure admin login with session management
- ➕ Create events with department targeting
- ✅ Approve or reject student registrations
- 🔔 Manual & auto-approval booking workflows
- 📋 View all bookings per event
- 📍 Mark and manage student attendance
- 📢 Send bulk announcements to students
- 🏆 Issue participation e-certificates
- 📧 Automatic email notifications on event creation

### 🎓 Student Portal
- 📝 Student registration with department selection
- 🔍 Browse available events
- 🎟️ Book seats with real-time availability
- 📄 Download participation certificates
- 📬 Receive email updates on bookings and events

---

## 🛠️ Tech Stack

| Technology | Purpose |
|---|---|
| **PHP** | Backend logic & server-side processing |
| **MySQL** | Database for students, events & bookings |
| **PHPMailer** | SMTP-based email notifications via Gmail |
| **HTML5 / CSS3** | Frontend structure and styling |
| **JavaScript** | Dynamic UI interactions |
| **Google Fonts** | DM Sans & Playfair Display typography |

---

## 📁 Project Structure

```
mayabook/
│
├── admin/
│   ├── admin_login.php
│   ├── dashboard.php
│   ├── create_event.php
│   ├── manage_events.php
│   ├── view_bookings.php
│   ├── approve_students.php
│   ├── manage_approvals.php
│   ├── attendance_list.php
│   ├── bulk_announce.php
│   └── admin_logout.php
│
├── student/
│   ├── index.php
│   ├── register.php
│   ├── login.php
│   ├── events.php
│   ├── my_bookings.php
│   └── logout.php
│
├── includes/
│   ├── db.php
│   └── PHPMailer/
│
└── README.md
```

---

## ⚙️ Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/mayabook.git
cd mayabook
```

### 2. Import the Database
- Open **phpMyAdmin** or your MySQL client
- Create a new database named `mayabook`
- Import the provided `mayabook.sql` file

### 3. Configure Database Connection
Edit `includes/db.php`:
```php
$conn = mysqli_connect("localhost", "your_db_user", "your_db_password", "mayabook");
```

### 4. Configure Email (PHPMailer)
In `admin/create_event.php` and other mailer files, update:
```php
$mail->Username = 'your_email@gmail.com';
$mail->Password = 'your_app_password';   // Use a Gmail App Password
```

> **Note:** Enable 2-Step Verification on your Google account and generate an **App Password** at [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)

### 5. Deploy
- Place the project folder in your `htdocs/` (XAMPP) or `www/` (WAMP) directory
- Start Apache and MySQL
- Visit `http://localhost/mayabook/`

---

## 🔐 Default Admin Access

```
URL:      http://localhost/mayabook/admin/admin_login.php
Username: admin
Password: admin123
```
> ⚠️ Change the default credentials immediately after first login.

---

## 📸 Screenshots
<img width="1240" height="580" alt="image" src="https://github.com/user-attachments/assets/4a21c362-d51e-4ba6-b109-36701b4437a6" />
<img width="1240" height="858" alt="image" src="https://github.com/user-attachments/assets/08a2a032-a302-4da6-b4f5-31c28329d2ba" />


---

## 📧 Email Notification System

When an admin creates a new event, MayaBook automatically:
1. Fetches all **approved students** from the targeted department(s)
2. Sends a beautifully designed **HTML email** with event details
3. Includes booking deadline, seat count, and a direct CTA button
4. Attaches a **certificate badge** notice if applicable

---

## 🏆 Certificate System

- Admin can enable e-certificates while creating an event
- Certificates use dynamic placeholders: `[NAME]`, `[EVENT]`, `[DATE]`
- Students can download their certificate after attending the event

---

## 🚀 Future Enhancements

- [ ] QR code-based attendance marking
- [ ] Student dashboard with booking history
- [ ] Event feedback and ratings
- [ ] WhatsApp notification integration
- [ ] Mobile app (Android/iOS)
- [ ] Export reports to Excel/PDF

---

## 👨‍💻 Developer

**Manoj S**  
- 🌐 Project: MayaBook v3.0  
- 🏫 Institution: RNS First Grade College  
- 📬 Email: manojsm692@gmail.com  

---

## 📄 License

This project is built for academic and institutional use.  
© 2025 MayaBook — Designed & Developed by Manoj S.

---

<p align="center">Built with ❤️ for RNS First Grade College students</p>
