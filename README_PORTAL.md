# Online Class Portal - Complete Documentation

A comprehensive Learning Management System (LMS) built with **Advanced Bootstrap 5** for the frontend and **Procedural PHP with MySQLi** for the backend.

## 📁 Complete File Structure

### Core Configuration Files
| File | Description |
|------|-------------|
| `portal_config.php` | Main configuration with database connection, security functions, and helpers |
| `portal_setup.php` | Database setup script (run first to create tables and sample data) |

### Authentication Pages
| File | Description |
|------|-------------|
| `index.php` | Landing page with features overview |
| `login.php` | Secure login page with password verification |
| `register.php` | User registration with validation |
| `logout.php` | Session cleanup and logout |
| `forgot-password.php` | Password reset request |
| `reset-password.php` | Password reset with token |

### Dashboard & Navigation
| File | Description |
|------|-------------|
| `dashboard.php` | Role-based dashboard (Admin/Instructor/Student) |
| `profile.php` | User profile management |
| `settings.php` | User settings (notifications, privacy) |

### Course Management
| File | Description |
|------|-------------|
| `my-courses.php` | View enrolled/teaching courses |
| `course-detail.php` | Course details with materials, assessments, tests, announcements |
| `create-course.php` | Create new courses (Instructor/Admin) |
| `browse-courses.php` | Browse and enroll in available courses (Student) |
| `admin-courses.php` | Admin course management |
| `admin-users.php` | Admin user management |

### Assessments & Tests
| File | Description |
|------|-------------|
| `my-assessments.php` | View all assessments |
| `my-tests.php` | View all tests/quizzes |
| `create-assessment.php` | Create assessments (Instructor/Admin) |
| `create-test.php` | Create tests (Instructor/Admin) |
| `submit-assessment.php` | Submit assessment files |
| `grade-submissions.php` | Grade student submissions (Instructor) |

### Grades
| File | Description |
|------|-------------|
| `grades.php` | Centralized grades view |
| `my-grades.php` | Student grades with letter grades and GPA |

### Communication
| File | Description |
|------|-------------|
| `messages.php` | Full messaging system (inbox, sent, compose) |
| `announcements.php` | Global and course-specific announcements |

### Admin Panel
| File | Description |
|------|-------------|
| `admin.php` | Main admin dashboard |
| `admin-users.php` | User management |
| `admin-courses.php` | Course management |

### Includes (Shared Components)
| File | Description |
|------|-------------|
| `includes/header.php` | Header with sidebar navigation |
| `includes/footer.php` | Footer with JavaScript |
| `includes/functions.php` | Additional helper functions |

### Other Directories
| Directory | Description |
|-----------|-------------|
| `uploads/` | File upload directory for submissions and materials |
| `themes/` | Theme assets (if needed) |

---

## 🔐 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@portal.com | Admin@123 |
| Instructor | instructor@portal.com | Instructor@123 |
| Student | student1@portal.com | Student@123 |

---

## 🚀 Setup Instructions

1. **Configure Database**: Edit `portal_config.php` with your MySQL credentials
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'online_class_portal');
   ```

2. **Run Setup**: Access `portal_setup.php` in your browser to create database tables and sample data

3. **Login**: Navigate to `index.php` and login with default credentials

4. **Explore**: Access all features from the role-based dashboard

---

## ✅ Security Features

- **Bcrypt password hashing** (cost factor 12)
- **CSRF token protection** on all forms
- **SQL injection prevention** (mysqli_real_escape_string)
- **XSS protection** (htmlspecialchars)
- **Session security** (httponly cookies, strict mode)
- **Role-based access control**
- **Input sanitization and validation**
- **Secure password reset tokens**

---

## 🛠 Technology Stack

| Component | Technology |
|-----------|------------|
| Frontend | Bootstrap 5.3.2 (Advanced, mobile-responsive) |
| Backend | Procedural PHP 8.x (strictly procedural, no OOP) |
| Database | MySQL with MySQLi Procedural extension |
| Icons | Bootstrap Icons 1.11.1 |

---

## 📱 Mobile-Friendly Features

- Responsive sidebar navigation (collapses on mobile)
- Touch-friendly buttons and form controls
- Optimized card layouts for small screens
- Mobile-first Bootstrap grid system
- Adaptive table views with horizontal scrolling

---

## 🎯 Key Features by Role

### Admin
- Manage all users (add, edit, delete, change roles)
- Manage all courses
- View system-wide statistics
- Access all administrative functions

### Instructor
- Create and manage courses
- Create assessments and tests
- Grade student submissions
- Post announcements
- Send messages to students
- View course analytics

### Student
- Browse and enroll in courses
- View course materials
- Submit assessments
- Take tests/quizzes
- View grades and performance
- Receive announcements
- Message instructors

---

## 📊 Database Tables Created by portal_setup.php

1. `users` - User accounts and profiles
2. `courses` - Course information
3. `enrollments` - Student-course enrollments
4. `course_materials` - Learning materials/files
5. `assessments` - Assignments and projects
6. `submissions` - Student assessment submissions
7. `tests` - Quizzes and exams
8. `test_questions` - Test question bank
9. `test_results` - Student test attempts
10. `announcements` - Course and global announcements
11. `messages` - Internal messaging system
12. `grades` - Grade records
13. `activity_log` - User activity tracking

---

## 📝 Notes

- All passwords are hashed using bcrypt before storage
- Session timeout is set to 1 hour of inactivity
- Maximum file upload size is 5MB
- Allowed file extensions: pdf, doc, docx, ppt, pptx, xls, xlsx, jpg, jpeg, png, gif
- Error logging is enabled (check error.log for issues)
