<?php
/**
 * Registration Page - Online Class Portal
 * Secure user registration with password validation
 */

require_once 'portal_config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$errors = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $user_type = sanitizeInput($_POST['user_type'] ?? 'student');
    
    // Validate input
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Connect to database
        $conn = getDBConnection();
        
        if ($conn) {
            // Check if email already exists
            $email_escaped = mysqli_real_escape_string($conn, $email);
            $check_sql = "SELECT id FROM users WHERE email = '$email_escaped'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error = 'This email address is already registered.';
            } else {
                // Hash password securely
                $hashed_password = hashPassword($password);
                
                // Insert new user
                $insert_sql = "INSERT INTO users (first_name, last_name, email, password, phone, user_type, status) 
                              VALUES ('$first_name', '$last_name', '$email_escaped', '$hashed_password', '$phone', '$user_type', 'active')";
                
                if (mysqli_query($conn, $insert_sql)) {
                    $success = 'Registration successful! You can now <a href="login.php" class="alert-link">login</a> to your account.';
                    // Clear form data
                    $first_name = $last_name = $email = $phone = '';
                } else {
                    $error = 'Registration failed. Please try again later.';
                    error_log("Registration error: " . mysqli_error($conn));
                }
            }
            
            mysqli_close($conn);
        } else {
            $error = 'Database connection error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Class Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .register-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .register-header i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .register-header h2 {
            font-weight: 700;
            margin: 0;
        }
        
        .register-body {
            padding: 40px 30px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #764ba2;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        @media (max-width: 576px) {
            .register-card {
                border-radius: 0;
            }
            
            .register-header {
                padding: 25px 20px;
            }
            
            .register-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <i class="bi bi-person-plus-fill"></i>
                    <h2>Create Account</h2>
                    <p class="mb-0 opacity-75">Join our learning community</p>
                </div>
                
                <div class="register-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name" 
                                       placeholder="John"
                                       value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name" 
                                       placeholder="Doe"
                                       value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="john.doe@example.com"
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="+1 (555) 123-4567"
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="user_type" class="form-label">I am a</label>
                            <select class="form-select" id="user_type" name="user_type">
                                <option value="student" <?php echo ($user_type ?? 'student') === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="instructor" <?php echo ($user_type ?? '') === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Create a strong password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-requirements">
                                <strong>Password Requirements:</strong>
                                <ul>
                                    <li>At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</li>
                                    <li>At least one uppercase letter</li>
                                    <li>At least one lowercase letter</li>
                                    <li>At least one number</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Confirm your password"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and 
                                <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-register btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? 
                            <a href="login.php" class="fw-bold text-decoration-none">Sign In</a>
                        </p>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="back-link">
                            <i class="bi bi-arrow-left me-1"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
