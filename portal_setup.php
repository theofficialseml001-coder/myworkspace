<?php
/**
 * Database Setup Script for Online Class Portal
 * Creates all necessary tables and inserts sample data
 */

require_once 'portal_config.php';

// Create database connection without selecting database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, DB_NAME);

echo "Database created successfully!<br>";

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    user_type ENUM('admin', 'instructor', 'student') NOT NULL DEFAULT 'student',
    phone VARCHAR(20),
    profile_image VARCHAR(255),
    bio TEXT,
    enrollment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'users' created successfully!<br>";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Create courses table
$sql = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(255) NOT NULL,
    description TEXT,
    instructor_id INT NOT NULL,
    credits INT DEFAULT 3,
    semester VARCHAR(50),
    academic_year VARCHAR(20),
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'inactive', 'completed', 'draft') DEFAULT 'draft',
    max_students INT DEFAULT 30,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'courses' created successfully!<br>";
} else {
    echo "Error creating courses table: " . mysqli_error($conn) . "<br>";
}

// Create enrollments table
$sql = "CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'completed', 'dropped', 'failed') DEFAULT 'enrolled',
    final_grade DECIMAL(5,2),
    grade_letter VARCHAR(2),
    attendance_percentage DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'enrollments' created successfully!<br>";
} else {
    echo "Error creating enrollments table: " . mysqli_error($conn) . "<br>";
}

// Create course_materials table
$sql = "CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    file_size INT,
    upload_by INT NOT NULL,
    download_count INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (upload_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'course_materials' created successfully!<br>";
} else {
    echo "Error creating course_materials table: " . mysqli_error($conn) . "<br>";
}

// Create assessments table
$sql = "CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assessment_type ENUM('assignment', 'quiz', 'exam', 'project', 'presentation') NOT NULL,
    total_marks INT NOT NULL DEFAULT 100,
    passing_marks INT,
    due_date DATETIME,
    start_date DATETIME,
    end_date DATETIME,
    instructions TEXT,
    allow_late_submission BOOLEAN DEFAULT FALSE,
    late_penalty_percentage DECIMAL(5,2) DEFAULT 10,
    status ENUM('draft', 'published', 'closed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'assessments' created successfully!<br>";
} else {
    echo "Error creating assessments table: " . mysqli_error($conn) . "<br>";
}

// Create submissions table
$sql = "CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    file_size INT,
    submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_late BOOLEAN DEFAULT FALSE,
    status ENUM('submitted', 'graded', 'resubmitted') DEFAULT 'submitted',
    marks_obtained DECIMAL(5,2),
    grade_letter VARCHAR(2),
    feedback TEXT,
    graded_by INT,
    graded_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_submission (assessment_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'submissions' created successfully!<br>";
} else {
    echo "Error creating submissions table: " . mysqli_error($conn) . "<br>";
}

// Create announcements table
$sql = "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    posted_by INT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    is_pinned BOOLEAN DEFAULT FALSE,
    show_to_students BOOLEAN DEFAULT TRUE,
    expiry_date DATETIME NULL,
    status ENUM('active', 'archived') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'announcements' created successfully!<br>";
} else {
    echo "Error creating announcements table: " . mysqli_error($conn) . "<br>";
}

// Create messages table
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    parent_message_id INT NULL,
    is_deleted_sender BOOLEAN DEFAULT FALSE,
    is_deleted_receiver BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'messages' created successfully!<br>";
} else {
    echo "Error creating messages table: " . mysqli_error($conn) . "<br>";
}

// Create tests/quizzes table
$sql = "CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    time_limit_minutes INT DEFAULT 60,
    total_marks INT NOT NULL DEFAULT 100,
    passing_percentage DECIMAL(5,2) DEFAULT 50,
    start_datetime DATETIME,
    end_datetime DATETIME,
    shuffle_questions BOOLEAN DEFAULT TRUE,
    shuffle_options BOOLEAN DEFAULT TRUE,
    show_results_immediately BOOLEAN DEFAULT FALSE,
    allow_review BOOLEAN DEFAULT FALSE,
    attempts_allowed INT DEFAULT 1,
    status ENUM('draft', 'published', 'closed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'tests' created successfully!<br>";
} else {
    echo "Error creating tests table: " . mysqli_error($conn) . "<br>";
}

// Create test questions table
$sql = "CREATE TABLE IF NOT EXISTS test_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'short_answer', 'essay') NOT NULL,
    marks INT NOT NULL DEFAULT 1,
    option_a VARCHAR(500),
    option_b VARCHAR(500),
    option_c VARCHAR(500),
    option_d VARCHAR(500),
    correct_answer VARCHAR(500),
    explanation TEXT,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'test_questions' created successfully!<br>";
} else {
    echo "Error creating test_questions table: " . mysqli_error($conn) . "<br>";
}

// Create test attempts table
$sql = "CREATE TABLE IF NOT EXISTS test_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    student_id INT NOT NULL,
    start_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME NULL,
    time_taken_seconds INT,
    total_marks_obtained DECIMAL(5,2),
    percentage DECIMAL(5,2),
    status ENUM('in_progress', 'completed', 'timed_out') DEFAULT 'in_progress',
    auto_graded BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'test_attempts' created successfully!<br>";
} else {
    echo "Error creating test_attempts table: " . mysqli_error($conn) . "<br>";
}

// Create test answers table
$sql = "CREATE TABLE IF NOT EXISTS test_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    student_answer VARCHAR(1000),
    is_correct BOOLEAN,
    marks_obtained DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES test_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'test_answers' created successfully!<br>";
} else {
    echo "Error creating test_answers table: " . mysqli_error($conn) . "<br>";
}

// Insert sample admin user
$admin_password = hashPassword('Admin@123');
$sql = "INSERT INTO users (email, password, first_name, last_name, user_type, status) 
        VALUES ('admin@portal.com', '$admin_password', 'System', 'Administrator', 'admin', 'active')";
if (mysqli_query($conn, $sql)) {
    echo "Admin user created successfully!<br>";
} else {
    echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
}

// Insert sample instructor
$instructor_password = hashPassword('Instructor@123');
$sql = "INSERT INTO users (email, password, first_name, last_name, user_type, status) 
        VALUES ('instructor@portal.com', '$instructor_password', 'John', 'Smith', 'instructor', 'active')";
if (mysqli_query($conn, $sql)) {
    echo "Instructor user created successfully!<br>";
} else {
    echo "Error creating instructor user: " . mysqli_error($conn) . "<br>";
}

// Insert sample students
$student_password = hashPassword('Student@123');
$sql = "INSERT INTO users (email, password, first_name, last_name, user_type, status) 
        VALUES ('student1@portal.com', '$student_password', 'Alice', 'Johnson', 'student', 'active'),
               ('student2@portal.com', '$student_password', 'Bob', 'Williams', 'student', 'active'),
               ('student3@portal.com', '$student_password', 'Carol', 'Brown', 'student', 'active')";
if (mysqli_query($conn, $sql)) {
    echo "Student users created successfully!<br>";
} else {
    echo "Error creating student users: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
echo "<p><strong>Sample Users Created:</strong></p>";
echo "<ul>";
echo "<li>Admin: admin@portal.com / Admin@123</li>";
echo "<li>Instructor: instructor@portal.com / Instructor@123</li>";
echo "<li>Student 1: student1@portal.com / Student@123</li>";
echo "<li>Student 2: student2@portal.com / Student@123</li>";
echo "<li>Student 3: student3@portal.com / Student@123</li>";
echo "</ul>";
echo "<p><a href='index.php'>Go to Portal Home</a></p>";

?>
