# Student Course Registration System

A web-based application that allows students to browse and register for courses, while administrators manage courses and monitor registrations.

## Team Members
- Hosam Tarade (231102)
- Zaid Fanoun (231087)
- Bahaa Zahedah (231114)

## Technologies Used
- HTML / CSS / JavaScript
- Bootstrap 5
- PHP (Pure)
- MySQL
- jQuery AJAX

## Project Structure
```
frontend-project/
├── Back_End/
│   ├── AJAX/
│   │   ├── register.php
│   │   ├── drop.php
│   │   └── get-seats.php
│   ├── db.php
│   ├── login.php
│   ├── logout.php
│   ├── student-dashboard.php
│   ├── courses.php
│   ├── my-courses.php
│   ├── admin-dashboard.php
│   ├── manage-courses.php
│   ├── manage-prerequisites.php
│   └── registration-overview.php
├── Front_End/
│   └── (CSS and JS files for each page)
└── Back_End/course_registration.sql
```

## Setup Instructions

1. Install [XAMPP](https://www.apachefriends.org/)
2. Clone or copy the project into `C:/xampp/htdocs/frontend-project/`
3. Open phpMyAdmin at `http://localhost/phpmyadmin`
4. Create a database named `course_registration`
5. Import `Back_End/course_registration.sql`
6. Start Apache and MySQL from XAMPP Control Panel
7. Open `http://localhost/frontend-project/Back_End/login.php`

## Features

### Student
- Login and view available courses
- Search courses by code or title
- Register in a course (with prerequisite and capacity validation)
- Drop a registered course
- View registered courses

### Admin
- Manage courses (add, edit, delete)
- Manage prerequisites
- View registration overview
- Dashboard with real-time stats

## Business Rules
- A student cannot register in the same course twice
- A student cannot register if the course is full
- A student cannot register without completing prerequisites
- All database queries use prepared statements
