# Digital Noticeboard - SRM University AP

A web-based digital noticeboard system for SRM University AP, allowing admins to post notices and students to view them.

## Features

- **Admin Panel** — Create, edit, delete notices; manage users
- **Student Dashboard** — Browse, search, and filter notices by category and importance
- **Authentication** — Role-based login (Admin / Student)
- **Notice Details** — View full notice with attachments and comments
- **Profile Management** — Update profile info and password

## Tech Stack

- **Backend:** PHP (PDO)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Server:** Apache (XAMPP locally / InfinityFree for hosting)

## Project Structure

```
├── admin/              # Admin panel pages
├── student/            # Student dashboard pages
├── includes/           # Config, DB connection, helper functions
├── css/                # Stylesheets
├── js/                 # JavaScript files
├── images/             # Logo and team images
├── uploads/            # User uploaded files
├── index.php           # Home page
├── login.php           # Login page
└── signup.php          # Registration page
```

## Local Setup (XAMPP)

1. Clone the repo into `C:/xampp/htdocs/final/`
2. Start **Apache** and **MySQL** in XAMPP
3. Open `http://localhost/phpmyadmin` and create a database named `srmap_noticeboard`
4. Update `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'srmap_noticeboard');
   ```
5. Visit `http://localhost/final` — the database tables will be created automatically

## Default Admin Login

After setup, register a new account and manually set `role = 'admin'` in the `users` table via phpMyAdmin.

## Pages

| Page | URL |
|------|-----|
| Home | `/index.php` |
| Login | `/login.php` |
| Sign Up | `/signup.php` |
| Admin Dashboard | `/admin/dashboard.php` |
| Student Dashboard | `/student/dashboard.php` |
