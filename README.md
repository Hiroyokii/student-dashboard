# Student Manager - Small PHP CRUD Website

A simple PHP + MySQL/MariaDB student-management website built from the supplied `db_4bsalibay` database.

## Included
- Login and registration
- PHP sessions on every protected page
- Logout destroys the session
- `password_hash()` and `password_verify()` for passwords
- Dashboard with record counts
- Add, Search, Update, Delete for the permitted school records
- One shared `pages/` folder for all record pages
- Reusable CRUD helper for simple tables
- Subjects page with Course / Year Level / Term dropdowns
- Users page with User Type dropdown
- Royal blue styling with edit and delete confirmation modals
- Role-based page access for Admin, Registrar, Dean, Instructor, and Student
- Instructor directory with image uploads
- Responsive styling with no framework required

## Folder structure
```text
student_crud_php/
├─ assets/style.css
├─ config/db.php
├─ includes/auth.php
├─ includes/footer.php
├─ includes/header.php
├─ includes/helpers.php
├─ pages/
│  ├─ course.php
│  ├─ yearlevel.php
│  ├─ term.php
│  ├─ section.php
│  ├─ schoolyear.php
│  ├─ subjects.php
│  ├─ users.php
│  ├─ usertype.php
│  └─ simple_crud.php
├─ dashboard.php
├─ login.php
├─ register.php
└─ logout.php
```

## Setup (XAMPP)
1. Copy the folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import your supplied `db_4bsalibay.sql`.
4. Open `config/db.php` and change the MySQL username/password if your XAMPP setup is different.
5. Visit `http://localhost/student_crud_php/login.php`.
6. Use **Register here** to create a Student login. Admins can create other account types on the Users page.

For an existing installation, import `upgrade_roles_instructors.sql` in phpMyAdmin. Fresh database imports from `db_4bsalibay.sql` already include the update. Seeded demo accounts use the password `password`; change these after signing in.

## Demo accounts
The five accounts from the original dump have unknown passwords. The added accounts (`admin2`, `admin3`, `registrar2`, `registrar3`, `dean2`, `dean3`, `instructor2`, `instructor3`, `student2`, `student3`) use the password `password`.

## Page access
- Admin: all pages
- Registrar: Subjects, Term, Instructor, School Year
- Dean: Subjects, Section, Instructor, Year Level
- Instructor: Subjects, Section
- Student: Subjects
